<?php

namespace App\Http\Controllers\Admin\Library;

use App\Http\Controllers\Controller;
use App\Models\LibraryReadingLog;
use App\Models\LibraryVisit;
use App\Models\LibraryItem;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;

/**
 * Library Reading Dashboard Controller
 * 
 * Provides analytics and statistics for reading logs:
 * - Total reading minutes
 * - Sessions count
 * - Currently reading (open sessions)
 * - Top books read
 * - Charts (daily/monthly)
 * 
 * All statistics can be filtered by:
 * - Date range
 * - Visitor type (user/guest)
 * - Session (morning/afternoon/evening)
 * - Purpose
 */
class LibraryReadingDashboardController extends Controller
{
    /**
     * Check if user has permission to view dashboard
     * Roles: admin, manager, staff, principal (read-only)
     */
    private function canView(): bool
    {
        $user = Auth::user();
        return $user && $user->hasAnyRole(['admin', 'manager', 'staff', 'principal']);
    }

    /**
     * Show dashboard page
     * 
     * @return \Illuminate\View\View
     */
    public function index()
    {
        if (!$this->canView()) {
            abort(403, 'You do not have permission to view the reading dashboard.');
        }

        return view('admin.library.reading_dashboard.index');
    }

    /**
     * Get dashboard summary statistics
     * 
     * Query parameters:
     * - date_from (Y-m-d, default: today)
     * - date_to (Y-m-d, default: today)
     * - visitor_type (all|user|guest, default: all)
     * - session (all|morning|afternoon|evening, default: all)
     * - purpose (all|read|study|borrow|return|other, default: all)
     * 
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function summary(Request $request)
    {
        if (!$this->canView()) {
            return response()->json(['success' => false, 'message' => 'Unauthorized'], 403);
        }

        // Parse filters
        $dateFrom = $request->input('date_from', Carbon::today()->format('Y-m-d'));
        $dateTo = $request->input('date_to', Carbon::today()->format('Y-m-d'));
        $visitorType = $request->input('visitor_type', 'all');
        $session = $request->input('session', 'all');
        $purpose = $request->input('purpose', 'all');

        try {
            // Build base query
            $baseQuery = $this->buildBaseQuery($dateFrom, $dateTo, $visitorType, $session, $purpose);

            // Calculate total minutes and sessions
            $summary = DB::table('library_reading_logs as rl')
                ->join('library_visits as v', 'v.id', '=', 'rl.visit_id')
                ->where('rl.is_active', 1)
                ->where('v.is_active', 1)
                ->whereBetween('v.visit_date', [$dateFrom, $dateTo])
                ->when($visitorType !== 'all', function ($q) use ($visitorType) {
                    if ($visitorType === 'user') {
                        $q->whereNotNull('v.user_id');
                    } elseif ($visitorType === 'guest') {
                        $q->whereNotNull('v.guest_id');
                    }
                })
                ->when($session !== 'all', function ($q) use ($session) {
                    $q->where('v.session', $session);
                })
                ->when($purpose !== 'all', function ($q) use ($purpose) {
                    $q->where('v.purpose', $purpose);
                })
                ->selectRaw('
                    COUNT(rl.id) as sessions_count,
                    SUM(CASE WHEN rl.end_time IS NULL THEN 1 ELSE 0 END) as open_sessions_now,
                    SUM(
                        GREATEST(0,
                            TIMESTAMPDIFF(MINUTE, rl.start_time, COALESCE(rl.end_time, NOW()))
                        )
                    ) as total_minutes
                ')
                ->first();

            // Get top books
            $topBooks = DB::table('library_reading_logs as rl')
                ->join('library_visits as v', 'v.id', '=', 'rl.visit_id')
                ->join('library_items as i', 'i.id', '=', 'rl.library_item_id')
                ->where('rl.is_active', 1)
                ->where('v.is_active', 1)
                ->whereBetween('v.visit_date', [$dateFrom, $dateTo])
                ->when($visitorType !== 'all', function ($q) use ($visitorType) {
                    if ($visitorType === 'user') {
                        $q->whereNotNull('v.user_id');
                    } elseif ($visitorType === 'guest') {
                        $q->whereNotNull('v.guest_id');
                    }
                })
                ->when($session !== 'all', function ($q) use ($session) {
                    $q->where('v.session', $session);
                })
                ->when($purpose !== 'all', function ($q) use ($purpose) {
                    $q->where('v.purpose', $purpose);
                })
                ->selectRaw('
                    i.id as item_id,
                    i.title,
                    i.isbn,
                    SUM(
                        GREATEST(0,
                            TIMESTAMPDIFF(MINUTE, rl.start_time, COALESCE(rl.end_time, NOW()))
                        )
                    ) as total_minutes,
                    COUNT(rl.id) as sessions_count
                ')
                ->groupBy('i.id', 'i.title', 'i.isbn')
                ->orderByDesc('total_minutes')
                ->orderByDesc('sessions_count')
                ->limit(10)
                ->get()
                ->map(function ($book) {
                    return [
                        'item_id' => $book->item_id,
                        'title' => $book->title,
                        'isbn' => $book->isbn ?? 'N/A',
                        'total_minutes' => (int)$book->total_minutes,
                        'total_hours' => round($book->total_minutes / 60, 1),
                        'sessions_count' => (int)$book->sessions_count,
                    ];
                });

            return response()->json([
                'success' => true,
                'data' => [
                    'total_minutes' => (int)($summary->total_minutes ?? 0),
                    'total_hours' => round(($summary->total_minutes ?? 0) / 60, 1),
                    'sessions_count' => (int)($summary->sessions_count ?? 0),
                    'open_sessions_now' => (int)($summary->open_sessions_now ?? 0),
                    'top_books' => $topBooks,
                ]
            ]);

        } catch (\Exception $e) {
            \Log::error('Reading Dashboard Summary Error: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Failed to load dashboard summary.'
            ], 500);
        }
    }

    /**
     * Get chart data (daily or monthly)
     * 
     * Query parameters:
     * - mode (daily|monthly, default: daily)
     * - date_from (for daily: start date, for monthly: ignored)
     * - date_to (for daily: end date, for monthly: ignored)
     * - visitor_type, session, purpose (same as summary)
     * 
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function chart(Request $request)
    {
        if (!$this->canView()) {
            return response()->json(['success' => false, 'message' => 'Unauthorized'], 403);
        }

        $mode = $request->input('mode', 'daily');
        $visitorType = $request->input('visitor_type', 'all');
        $session = $request->input('session', 'all');
        $purpose = $request->input('purpose', 'all');

        try {
            if ($mode === 'daily') {
                // Last 7 days including today
                $dateFrom = Carbon::today()->subDays(6)->format('Y-m-d');
                $dateTo = Carbon::today()->format('Y-m-d');

                $data = DB::table('library_reading_logs as rl')
                    ->join('library_visits as v', 'v.id', '=', 'rl.visit_id')
                    ->where('rl.is_active', 1)
                    ->where('v.is_active', 1)
                    ->whereBetween('v.visit_date', [$dateFrom, $dateTo])
                    ->when($visitorType !== 'all', function ($q) use ($visitorType) {
                        if ($visitorType === 'user') {
                            $q->whereNotNull('v.user_id');
                        } elseif ($visitorType === 'guest') {
                            $q->whereNotNull('v.guest_id');
                        }
                    })
                    ->when($session !== 'all', function ($q) use ($session) {
                        $q->where('v.session', $session);
                    })
                    ->when($purpose !== 'all', function ($q) use ($purpose) {
                        $q->where('v.purpose', $purpose);
                    })
                    ->selectRaw('
                        DATE(v.visit_date) as date,
                        SUM(
                            GREATEST(0,
                                TIMESTAMPDIFF(MINUTE, rl.start_time, COALESCE(rl.end_time, NOW()))
                            )
                        ) as total_minutes,
                        COUNT(rl.id) as sessions_count
                    ')
                    ->groupBy('date')
                    ->orderBy('date')
                    ->get()
                    ->keyBy('date');

                // Generate labels for all 7 days (fill missing days with 0)
                $labels = [];
                $minutes = [];
                $sessions = [];

                for ($i = 6; $i >= 0; $i--) {
                    $date = Carbon::today()->subDays($i);
                    $dateStr = $date->format('Y-m-d');
                    $dayLabel = $date->format('D, M d'); // e.g., "Mon, Dec 15"

                    $labels[] = $dayLabel;
                    $minutes[] = isset($data[$dateStr]) ? (int)$data[$dateStr]->total_minutes : 0;
                    $sessions[] = isset($data[$dateStr]) ? (int)$data[$dateStr]->sessions_count : 0;
                }

                return response()->json([
                    'success' => true,
                    'data' => [
                        'labels' => $labels,
                        'minutes' => $minutes,
                        'sessions' => $sessions,
                    ]
                ]);

            } elseif ($mode === 'monthly') {
                // This month (day by day)
                $dateFrom = Carbon::now()->startOfMonth()->format('Y-m-d');
                $dateTo = Carbon::now()->endOfMonth()->format('Y-m-d');

                $data = DB::table('library_reading_logs as rl')
                    ->join('library_visits as v', 'v.id', '=', 'rl.visit_id')
                    ->where('rl.is_active', 1)
                    ->where('v.is_active', 1)
                    ->whereBetween('v.visit_date', [$dateFrom, $dateTo])
                    ->when($visitorType !== 'all', function ($q) use ($visitorType) {
                        if ($visitorType === 'user') {
                            $q->whereNotNull('v.user_id');
                        } elseif ($visitorType === 'guest') {
                            $q->whereNotNull('v.guest_id');
                        }
                    })
                    ->when($session !== 'all', function ($q) use ($session) {
                        $q->where('v.session', $session);
                    })
                    ->when($purpose !== 'all', function ($q) use ($purpose) {
                        $q->where('v.purpose', $purpose);
                    })
                    ->selectRaw('
                        DATE(v.visit_date) as date,
                        SUM(
                            GREATEST(0,
                                TIMESTAMPDIFF(MINUTE, rl.start_time, COALESCE(rl.end_time, NOW()))
                            )
                        ) as total_minutes,
                        COUNT(rl.id) as sessions_count
                    ')
                    ->groupBy('date')
                    ->orderBy('date')
                    ->get()
                    ->keyBy('date');

                // Generate labels for this month
                $labels = [];
                $minutes = [];
                $sessions = [];

                $start = Carbon::now()->startOfMonth();
                $end = Carbon::now()->endOfMonth();

                while ($start->lte($end)) {
                    $dateStr = $start->format('Y-m-d');
                    $dayLabel = $start->format('M d'); // e.g., "Dec 15"

                    $labels[] = $dayLabel;
                    $minutes[] = isset($data[$dateStr]) ? (int)$data[$dateStr]->total_minutes : 0;
                    $sessions[] = isset($data[$dateStr]) ? (int)$data[$dateStr]->sessions_count : 0;

                    $start->addDay();
                }

                return response()->json([
                    'success' => true,
                    'data' => [
                        'labels' => $labels,
                        'minutes' => $minutes,
                        'sessions' => $sessions,
                    ]
                ]);
            }

        } catch (\Exception $e) {
            \Log::error('Reading Dashboard Chart Error: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Failed to load chart data.'
            ], 500);
        }
    }

    /**
     * Build base query (helper method)
     * Not used currently but kept for future expansion
     */
    private function buildBaseQuery($dateFrom, $dateTo, $visitorType, $session, $purpose)
    {
        return LibraryReadingLog::query()
            ->join('library_visits as v', 'v.id', '=', 'library_reading_logs.visit_id')
            ->where('library_reading_logs.is_active', 1)
            ->where('v.is_active', 1)
            ->whereBetween('v.visit_date', [$dateFrom, $dateTo])
            ->when($visitorType !== 'all', function ($q) use ($visitorType) {
                if ($visitorType === 'user') {
                    $q->whereNotNull('v.user_id');
                } elseif ($visitorType === 'guest') {
                    $q->whereNotNull('v.guest_id');
                }
            })
            ->when($session !== 'all', function ($q) use ($session) {
                $q->where('v.session', $session);
            })
            ->when($purpose !== 'all', function ($q) use ($purpose) {
                $q->where('v.purpose', $purpose);
            });
    }
}



