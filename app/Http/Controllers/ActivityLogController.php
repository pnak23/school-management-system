<?php

namespace App\Http\Controllers;

use App\Models\ActivityLog;
use App\Models\User;
use Illuminate\Http\Request;
use Yajra\DataTables\Facades\DataTables;
use Carbon\Carbon;

class ActivityLogController extends Controller
{
    /**
     * Check if user can delete logs (admin only)
     */
    private function canDelete()
    {
        return auth()->check() && auth()->user()->hasRole('admin');
    }
    /**
     * Display a listing of activity logs.
     */
    public function index(Request $request)
    {
        if ($request->ajax()) {
            $query = ActivityLog::with('user')
                ->select('activity_log.*');

            return DataTables::of($query)
                ->addIndexColumn()
                ->addColumn('DT_RowIndex', function ($row) {
                    return '';
                })
                ->addColumn('user_name', function ($row) {
                    if ($row->causer_type === 'App\Models\User' && $row->causer_id) {
                        $user = User::find($row->causer_id);
                        return $user ? $user->name : 'System';
                    }
                    return 'System';
                })
                ->addColumn('subject', function ($row) {
                    if ($row->subject_type && $row->subject_id) {
                        $subjectClass = class_basename($row->subject_type);
                        return $subjectClass . ' #' . $row->subject_id;
                    }
                    return 'N/A';
                })
                ->addColumn('properties', function ($row) {
                    if ($row->properties && !empty($row->properties)) {
                        return '<button class="btn btn-sm btn-info viewProperties" data-id="' . $row->id . '">
                                    <i class="fas fa-eye"></i> View
                                </button>';
                    }
                    return 'N/A';
                })
                ->addColumn('date', function ($row) {
                    return Carbon::parse($row->created_at)->format('Y-m-d H:i:s');
                })
                ->rawColumns(['properties'])
                ->orderColumn('date', 'activity_log.created_at $1')
                ->make(true);
        }

        return view('activity_logs.index');
    }

    /**
     * Show the properties of a specific activity log.
     */
    public function show($id)
    {
        $activityLog = ActivityLog::findOrFail($id);
        
        $properties = [];
        if ($activityLog->properties) {
            foreach ($activityLog->properties as $key => $value) {
                $properties[$key] = $value;
            }
        }

        return response()->json($properties);
    }

    /**
     * Delete logs older than specified period
     * 
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function deleteByPeriod(Request $request)
    {
        if (!$this->canDelete()) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized. Only administrators can delete logs.'
            ], 403);
        }

        $validated = $request->validate([
            'period' => 'required|in:week,month,3months,6months,year,all'
        ]);

        try {
            $period = $validated['period'];
            $cutoffDate = null;
            $deletedCount = 0;

            switch ($period) {
                case 'week':
                    $cutoffDate = Carbon::now()->subWeek();
                    break;
                case 'month':
                    $cutoffDate = Carbon::now()->subMonth();
                    break;
                case '3months':
                    $cutoffDate = Carbon::now()->subMonths(3);
                    break;
                case '6months':
                    $cutoffDate = Carbon::now()->subMonths(6);
                    break;
                case 'year':
                    $cutoffDate = Carbon::now()->subYear();
                    break;
                case 'all':
                    // Delete all logs
                    $deletedCount = ActivityLog::count();
                    ActivityLog::truncate();
                    break;
            }

            if ($period !== 'all' && $cutoffDate) {
                $deletedCount = ActivityLog::where('created_at', '<', $cutoffDate)->count();
                ActivityLog::where('created_at', '<', $cutoffDate)->delete();
            }

            return response()->json([
                'success' => true,
                'message' => "Successfully deleted {$deletedCount} log(s).",
                'deleted_count' => $deletedCount
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to delete logs: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get statistics about activity logs
     * 
     * @return \Illuminate\Http\JsonResponse
     */
    public function getStats()
    {
        if (!$this->canDelete()) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized.'
            ], 403);
        }

        try {
            $stats = [
                'total' => ActivityLog::count(),
                'last_week' => ActivityLog::where('created_at', '>=', Carbon::now()->subWeek())->count(),
                'last_month' => ActivityLog::where('created_at', '>=', Carbon::now()->subMonth())->count(),
                'last_3months' => ActivityLog::where('created_at', '>=', Carbon::now()->subMonths(3))->count(),
                'last_6months' => ActivityLog::where('created_at', '>=', Carbon::now()->subMonths(6))->count(),
                'last_year' => ActivityLog::where('created_at', '>=', Carbon::now()->subYear())->count(),
                'older_than_week' => ActivityLog::where('created_at', '<', Carbon::now()->subWeek())->count(),
                'older_than_month' => ActivityLog::where('created_at', '<', Carbon::now()->subMonth())->count(),
                'older_than_3months' => ActivityLog::where('created_at', '<', Carbon::now()->subMonths(3))->count(),
                'older_than_6months' => ActivityLog::where('created_at', '<', Carbon::now()->subMonths(6))->count(),
                'older_than_year' => ActivityLog::where('created_at', '<', Carbon::now()->subYear())->count(),
            ];

            return response()->json([
                'success' => true,
                'data' => $stats
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to get statistics: ' . $e->getMessage()
            ], 500);
        }
    }
}

