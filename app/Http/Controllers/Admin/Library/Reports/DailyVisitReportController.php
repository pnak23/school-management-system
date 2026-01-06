<?php

namespace App\Http\Controllers\Admin\Library\Reports;

use App\Http\Controllers\Controller;
use App\Models\LibraryVisit;
use App\Models\Staff;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Yajra\DataTables\Facades\DataTables;
use Carbon\Carbon;

class DailyVisitReportController extends Controller
{
  
    private function canRead()
    {
        return Auth::user()->hasAnyRole(['admin', 'manager', 'staff']);
    }

 
    public function index(Request $request)
    {
        if (!$this->canRead()) {
            abort(403, 'Unauthorized. Only Admin, Manager, and Library Staff can view daily visit statistics.');
        }

       
        $staffList = Staff::where('is_active', 1)
            ->select('id', 'khmer_name', 'english_name', 'staff_code')
            ->orderBy('english_name')
            ->orderBy('khmer_name')
            ->get();

        return view('admin.library.reports.daily_visits.index', compact('staffList'));
    }

  
    public function summary(Request $request)
    {
        if (!$this->canRead()) {
            return response()->json(['error' => 'Unauthorized.'], 403);
        }

        try {
            $baseQuery = $this->buildBaseQuery($request);

            // Total visits
            $totalVisits = (clone $baseQuery)->count();

            // Visits by session
            $visitsMorning = (clone $baseQuery)
                ->where('library_visits.session', 'morning')
                ->count();

            $visitsAfternoon = (clone $baseQuery)
                ->where('library_visits.session', 'afternoon')
                ->count();

            // Visits by visitor type
            $totalUsersVisits = (clone $baseQuery)
                ->whereNotNull('library_visits.user_id')
                ->count();

            $totalGuestVisits = (clone $baseQuery)
                ->whereNotNull('library_visits.guest_id')
                ->count();

            // Open sessions count
            $openSessionsCount = (clone $baseQuery)
                ->whereNotNull('library_visits.check_in_time')
                ->whereNull('library_visits.check_out_time')
                ->count();

            // Checked out count
            $checkedOutCount = (clone $baseQuery)
                ->whereNotNull('library_visits.check_out_time')
                ->count();

            // Average session minutes (for closed sessions only)
            $avgSessionMinutes = (clone $baseQuery)
                ->whereNotNull('library_visits.check_in_time')
                ->whereNotNull('library_visits.check_out_time')
                ->selectRaw('
                    AVG(TIMESTAMPDIFF(MINUTE, library_visits.check_in_time, library_visits.check_out_time)) as avg_minutes
                ')
                ->value('avg_minutes');

            return response()->json([
                'success' => true,
                'data' => [
                    'total_visits' => $totalVisits,
                    'visits_morning' => $visitsMorning,
                    'visits_afternoon' => $visitsAfternoon,
                    'total_users_visits' => $totalUsersVisits,
                    'total_guest_visits' => $totalGuestVisits,
                    'open_sessions_count' => $openSessionsCount,
                    'checked_out_count' => $checkedOutCount,
                    'avg_session_minutes' => $avgSessionMinutes ? round($avgSessionMinutes, 2) : 0
                ]
            ]);
        } catch (\Exception $e) {
            Log::error('Daily Visit Report Summary Error: ' . $e->getMessage(), [
                'trace' => $e->getTraceAsString(),
                'request' => $request->all()
            ]);
            
            return response()->json([
                'success' => false,
                'error' => 'Failed to load summary data.',
                'message' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get DataTables JSON data for visits list
     */
    public function data(Request $request)
    {
        if (!$this->canRead()) {
            return response()->json(['error' => 'Unauthorized.'], 403);
        }

        try {
            $query = $this->buildVisitsQuery($request);

            return DataTables::of($query)
                ->addIndexColumn()
                ->addColumn('visit_id', function ($visit) {
                    return '#' . $visit->id;
                })
                ->addColumn('visit_date', function ($visit) {
                    return $visit->visit_date ? Carbon::parse($visit->visit_date)->format('Y-m-d') : 'N/A';
                })
                ->addColumn('check_in_time', function ($visit) {
                    return $visit->check_in_time ? Carbon::parse($visit->check_in_time)->format('H:i:s') : 'N/A';
                })
                ->addColumn('check_out_time', function ($visit) {
                    return $visit->check_out_time ? Carbon::parse($visit->check_out_time)->format('H:i:s') : 'N/A';
                })
                ->addColumn('session', function ($visit) {
                    $badgeColors = [
                        'morning' => 'info',
                        'afternoon' => 'warning',
                        'evening' => 'secondary'
                    ];
                    $color = $badgeColors[$visit->session] ?? 'secondary';
                    return '<span class="badge bg-' . $color . '">' . ucfirst($visit->session) . '</span>';
                })
                ->addColumn('visitor_type', function ($visit) {
                    if ($visit->user_id) {
                        return '<span class="badge bg-primary">User</span>';
                    } elseif ($visit->guest_id) {
                        return '<span class="badge bg-warning">Guest</span>';
                    }
                    return '<span class="badge bg-secondary">Unknown</span>';
                })
                ->addColumn('visitor_name', function ($visit) {
                    if ($visit->user_id && $visit->user_name) {
                        return e($visit->user_name);
                    } elseif ($visit->guest_id && $visit->guest_full_name) {
                        return e($visit->guest_full_name);
                    }
                    return '<span class="text-muted">N/A</span>';
                })
                ->addColumn('phone', function ($visit) {
                    if ($visit->guest_phone) {
                        return e($visit->guest_phone);
                    }
                    return '<span class="text-muted">-</span>';
                })
                ->addColumn('purpose', function ($visit) {
                    return ucfirst($visit->purpose ?? 'N/A');
                })
                ->addColumn('checked_in_by', function ($visit) {
                    if ($visit->checked_in_staff_name) {
                        return e($visit->checked_in_staff_name);
                    }
                    return '<span class="text-muted">N/A</span>';
                })
                ->addColumn('checked_out_by', function ($visit) {
                    if ($visit->checked_out_staff_name) {
                        return e($visit->checked_out_staff_name);
                    }
                    return '<span class="text-muted">-</span>';
                })
                ->addColumn('status', function ($visit) {
                    if ($visit->check_out_time) {
                        return '<span class="badge bg-success">Closed</span>';
                    } else {
                        return '<span class="badge bg-warning">Open</span>';
                    }
                })
                ->addColumn('actions', function ($visit) {
                    $actions = '<div class="btn-group btn-group-sm" role="group">';
                    
                    // View button
                    $actions .= '<button type="button" class="btn btn-info" onclick="viewVisit(' . $visit->id . ')" title="View Details">
                                    <i class="fas fa-eye"></i>
                                </button>';
                    
                    // Force checkout button (only if open session)
                    if (!$visit->check_out_time) {
                        $actions .= '<button type="button" class="btn btn-danger" onclick="forceCheckout(' . $visit->id . ')" title="Force Checkout">
                                        <i class="fas fa-sign-out-alt"></i>
                                    </button>';
                    }
                    
                    $actions .= '</div>';
                    return $actions;
                })
                ->rawColumns(['session', 'visitor_type', 'visitor_name', 'phone', 'checked_in_by', 'checked_out_by', 'status', 'actions'])
                ->make(true);
        } catch (\Exception $e) {
            Log::error('Daily Visit Report Data Error: ' . $e->getMessage(), [
                'trace' => $e->getTraceAsString()
            ]);
            
            return response()->json([
                'error' => 'Failed to load visits data.',
                'message' => $e->getMessage()
            ], 500);
        }
    }

  
    public function openSessionsData(Request $request)
    {
        if (!$this->canRead()) {
            return response()->json(['error' => 'Unauthorized.'], 403);
        }

        try {
            $query = $this->buildVisitsQuery($request)
                ->whereNotNull('library_visits.check_in_time')
                ->whereNull('library_visits.check_out_time');

            return DataTables::of($query)
                ->addIndexColumn()
                ->addColumn('visit_id', function ($visit) {
                    return '#' . $visit->id;
                })
                ->addColumn('check_in_time', function ($visit) {
                    return $visit->check_in_time ? Carbon::parse($visit->check_in_time)->format('Y-m-d H:i:s') : 'N/A';
                })
                ->addColumn('session', function ($visit) {
                    $badgeColors = [
                        'morning' => 'info',
                        'afternoon' => 'warning',
                        'evening' => 'secondary'
                    ];
                    $color = $badgeColors[$visit->session] ?? 'secondary';
                    return '<span class="badge bg-' . $color . '">' . ucfirst($visit->session) . '</span>';
                })
                ->addColumn('visitor_type', function ($visit) {
                    if ($visit->user_id) {
                        return '<span class="badge bg-primary">User</span>';
                    } elseif ($visit->guest_id) {
                        return '<span class="badge bg-warning">Guest</span>';
                    }
                    return '<span class="badge bg-secondary">Unknown</span>';
                })
                ->addColumn('visitor_name', function ($visit) {
                    if ($visit->user_id && $visit->user_name) {
                        return e($visit->user_name);
                    } elseif ($visit->guest_id && $visit->guest_full_name) {
                        return e($visit->guest_full_name);
                    }
                    return '<span class="text-muted">N/A</span>';
                })
                ->addColumn('purpose', function ($visit) {
                    return ucfirst($visit->purpose ?? 'N/A');
                })
                ->addColumn('checked_in_by', function ($visit) {
                    if ($visit->checked_in_staff_name) {
                        return e($visit->checked_in_staff_name);
                    }
                    return '<span class="text-muted">N/A</span>';
                })
                ->addColumn('duration', function ($visit) {
                    if ($visit->check_in_time) {
                        $checkIn = Carbon::parse($visit->check_in_time);
                        $now = Carbon::now();
                        $minutes = $checkIn->diffInMinutes($now);
                        $hours = floor($minutes / 60);
                        $mins = $minutes % 60;
                        
                        if ($hours > 0) {
                            return $hours . 'h ' . $mins . 'm';
                        }
                        return $minutes . 'm';
                    }
                    return 'N/A';
                })
                ->addColumn('actions', function ($visit) {
                    return '<button type="button" class="btn btn-sm btn-danger" onclick="forceCheckout(' . $visit->id . ')" title="Force Checkout">
                                <i class="fas fa-sign-out-alt"></i> Force Checkout
                            </button>';
                })
                ->rawColumns(['session', 'visitor_type', 'visitor_name', 'checked_in_by', 'actions'])
                ->make(true);
        } catch (\Exception $e) {
            Log::error('Daily Visit Report Open Sessions Error: ' . $e->getMessage(), [
                'trace' => $e->getTraceAsString()
            ]);
            
            return response()->json([
                'error' => 'Failed to load open sessions data.',
                'message' => $e->getMessage()
            ], 500);
        }
    }

 
    public function todayOperations(Request $request)
    {
        if (!$this->canRead()) {
            return response()->json(['error' => 'Unauthorized.'], 403);
        }

        try {
            $today = Carbon::today();

            // Open sessions count (today)
            $openSessionsCount = DB::table('library_visits')
                ->where('is_active', 1)
                ->whereDate('visit_date', $today)
                ->whereNotNull('check_in_time')
                ->whereNull('check_out_time')
                ->count();

            // Last 10 check-ins (today)
            $lastCheckIns = DB::table('library_visits as lv')
                ->leftJoin('users as u', 'u.id', '=', 'lv.user_id')
                ->leftJoin('library_guests as lg', 'lg.id', '=', 'lv.guest_id')
                ->where('lv.is_active', 1)
                ->whereDate('lv.visit_date', $today)
                ->whereNotNull('lv.check_in_time')
                ->select([
                    'lv.id',
                    'lv.check_in_time',
                    DB::raw('COALESCE(u.name, lg.full_name) as visitor_name'),
                    DB::raw('CASE 
                        WHEN lv.user_id IS NOT NULL THEN "User"
                        WHEN lv.guest_id IS NOT NULL THEN "Guest"
                        ELSE "Unknown"
                    END as visitor_type')
                ])
                ->orderBy('lv.check_in_time', 'desc')
                ->limit(10)
                ->get()
                ->map(function ($item) {
                    return [
                        'id' => $item->id,
                        'time' => Carbon::parse($item->check_in_time)->format('H:i:s'),
                        'name' => $item->visitor_name ?? 'Unknown',
                        'type' => $item->visitor_type
                    ];
                });

            // Last 10 check-outs (today)
            $lastCheckOuts = DB::table('library_visits as lv')
                ->leftJoin('users as u', 'u.id', '=', 'lv.user_id')
                ->leftJoin('library_guests as lg', 'lg.id', '=', 'lv.guest_id')
                ->where('lv.is_active', 1)
                ->whereDate('lv.visit_date', $today)
                ->whereNotNull('lv.check_out_time')
                ->select([
                    'lv.id',
                    'lv.check_out_time',
                    DB::raw('COALESCE(u.name, lg.full_name) as visitor_name'),
                    DB::raw('CASE 
                        WHEN lv.user_id IS NOT NULL THEN "User"
                        WHEN lv.guest_id IS NOT NULL THEN "Guest"
                        ELSE "Unknown"
                    END as visitor_type')
                ])
                ->orderBy('lv.check_out_time', 'desc')
                ->limit(10)
                ->get()
                ->map(function ($item) {
                    return [
                        'id' => $item->id,
                        'time' => Carbon::parse($item->check_out_time)->format('H:i:s'),
                        'name' => $item->visitor_name ?? 'Unknown',
                        'type' => $item->visitor_type
                    ];
                });

            return response()->json([
                'success' => true,
                'data' => [
                    'open_sessions_count' => $openSessionsCount,
                    'last_check_ins' => $lastCheckIns,
                    'last_check_outs' => $lastCheckOuts
                ]
            ]);
        } catch (\Exception $e) {
            Log::error('Today Operations Panel Error: ' . $e->getMessage(), [
                'trace' => $e->getTraceAsString()
            ]);
            
            return response()->json([
                'success' => false,
                'error' => 'Failed to load operations data.',
                'message' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Force checkout a visit (set check_out_time to now)
     */
    public function forceCheckout(Request $request, $id)
    {
        if (!$this->canRead()) {
            return response()->json(['error' => 'Unauthorized.'], 403);
        }

        try {
            $visit = LibraryVisit::where('id', $id)
                ->where('is_active', 1)
                ->whereNull('check_out_time')
                ->first();

            if (!$visit) {
                return response()->json([
                    'success' => false,
                    'message' => 'Visit not found or already checked out.'
                ], 404);
            }

       
            $currentStaff = Staff::where('user_id', Auth::id())
                ->where('is_active', 1)
                ->first();

            $visit->check_out_time = Carbon::now();
            $visit->checked_out_by_staff_id = $currentStaff ? $currentStaff->id : null;
            $visit->note = ($visit->note ? $visit->note . "\n" : '') . 'Forced checkout at ' . Carbon::now()->format('Y-m-d H:i:s');
            $visit->updated_by = Auth::id();
            $visit->save();

            return response()->json([
                'success' => true,
                'message' => 'Visit checked out successfully.'
            ]);
        } catch (\Exception $e) {
            Log::error('Force Checkout Error: ' . $e->getMessage(), [
                'trace' => $e->getTraceAsString(),
                'visit_id' => $id
            ]);
            
            return response()->json([
                'success' => false,
                'message' => 'Failed to force checkout. ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Build base query for summary and filtering
     */
    private function buildBaseQuery(Request $request)
    {
        $query = DB::table('library_visits')
            ->where('library_visits.is_active', 1);

        // Date filter
        if ($request->filled('date')) {
            $date = Carbon::parse($request->date)->format('Y-m-d');
            $query->whereDate('library_visits.visit_date', $date);
        } elseif ($request->filled('date_from') || $request->filled('date_to')) {
            if ($request->filled('date_from')) {
                $dateFrom = Carbon::parse($request->date_from)->startOfDay();
                $query->where('library_visits.visit_date', '>=', $dateFrom);
            }
            if ($request->filled('date_to')) {
                $dateTo = Carbon::parse($request->date_to)->endOfDay();
                $query->where('library_visits.visit_date', '<=', $dateTo);
            }
        } else {
            // Default to today
            $query->whereDate('library_visits.visit_date', Carbon::today());
        }

        // Session filter
        if ($request->filled('session') && $request->session !== 'all') {
            $query->where('library_visits.session', $request->session);
        }

        // Visitor type filter
        if ($request->filled('visitor_type') && $request->visitor_type !== 'all') {
            if ($request->visitor_type === 'user') {
                $query->whereNotNull('library_visits.user_id');
            } elseif ($request->visitor_type === 'guest') {
                $query->whereNotNull('library_visits.guest_id');
            }
        }

        // Checked in by staff filter
        if ($request->filled('checked_in_by_staff_id') && $request->checked_in_by_staff_id !== 'all') {
            $query->where('library_visits.checked_in_by_staff_id', $request->checked_in_by_staff_id);
        }

        // Search filter
        if ($request->filled('search') && !empty($request->search['value'])) {
            $search = $request->search['value'];
            $query->where(function ($q) use ($search) {
                $q->where('library_visits.id', 'like', "%{$search}%")
                  ->orWhere('library_visits.purpose', 'like', "%{$search}%")
                  ->orWhere('library_visits.note', 'like', "%{$search}%")
                  ->orWhereExists(function ($subQuery) use ($search) {
                      $subQuery->select(DB::raw(1))
                          ->from('users')
                          ->whereColumn('users.id', 'library_visits.user_id')
                          ->where(function ($userQuery) use ($search) {
                              $userQuery->where('users.name', 'like', "%{$search}%")
                                        ->orWhere('users.email', 'like', "%{$search}%");
                          });
                  })
                  ->orWhereExists(function ($subQuery) use ($search) {
                      $subQuery->select(DB::raw(1))
                          ->from('library_guests')
                          ->whereColumn('library_guests.id', 'library_visits.guest_id')
                          ->where(function ($guestQuery) use ($search) {
                              $guestQuery->where('library_guests.full_name', 'like', "%{$search}%")
                                        ->orWhere('library_guests.phone', 'like', "%{$search}%")
                                        ->orWhere('library_guests.id_card_no', 'like', "%{$search}%");
                          });
                  });
            });
        }

        return $query;
    }


    private function buildVisitsQuery(Request $request)
    {
        $query = DB::table('library_visits')
            ->leftJoin('users', 'users.id', '=', 'library_visits.user_id')
            ->leftJoin('library_guests', 'library_guests.id', '=', 'library_visits.guest_id')
            ->leftJoin('staff as checked_in_staff', 'checked_in_staff.id', '=', 'library_visits.checked_in_by_staff_id')
            ->leftJoin('staff as checked_out_staff', 'checked_out_staff.id', '=', 'library_visits.checked_out_by_staff_id')
            ->where('library_visits.is_active', 1)
            ->select([
                'library_visits.id',
                'library_visits.user_id',
                'library_visits.guest_id',
                'library_visits.visit_date',
                'library_visits.check_in_time',
                'library_visits.check_out_time',
                'library_visits.session',
                'library_visits.purpose',
                'library_visits.note',
                // User name
                DB::raw('users.name as user_name'),
                // Guest info
                DB::raw('library_guests.full_name as guest_full_name'),
                DB::raw('library_guests.phone as guest_phone'),
                // Staff names
                DB::raw('COALESCE(checked_in_staff.english_name, checked_in_staff.khmer_name) as checked_in_staff_name'),
                DB::raw('COALESCE(checked_out_staff.english_name, checked_out_staff.khmer_name) as checked_out_staff_name')
            ]);

        // Apply same filters as buildBaseQuery
        $this->applyFiltersToQuery($query, $request);

        return $query;
    }


    private function applyFiltersToQuery($query, Request $request)
    {
        // Date filter
        if ($request->filled('date')) {
            $date = Carbon::parse($request->date)->format('Y-m-d');
            $query->whereDate('library_visits.visit_date', $date);
        } elseif ($request->filled('date_from') || $request->filled('date_to')) {
            if ($request->filled('date_from')) {
                $dateFrom = Carbon::parse($request->date_from)->startOfDay();
                $query->where('library_visits.visit_date', '>=', $dateFrom);
            }
            if ($request->filled('date_to')) {
                $dateTo = Carbon::parse($request->date_to)->endOfDay();
                $query->where('library_visits.visit_date', '<=', $dateTo);
            }
        } else {
            // Default to today
            $query->whereDate('library_visits.visit_date', Carbon::today());
        }

        // Session filter
        if ($request->filled('session') && $request->session !== 'all') {
            $query->where('library_visits.session', $request->session);
        }

        // Visitor type filter
        if ($request->filled('visitor_type') && $request->visitor_type !== 'all') {
            if ($request->visitor_type === 'user') {
                $query->whereNotNull('library_visits.user_id');
            } elseif ($request->visitor_type === 'guest') {
                $query->whereNotNull('library_visits.guest_id');
            }
        }

        // Checked in by staff filter
        if ($request->filled('checked_in_by_staff_id') && $request->checked_in_by_staff_id !== 'all') {
            $query->where('library_visits.checked_in_by_staff_id', $request->checked_in_by_staff_id);
        }

        // Search filter
        if ($request->filled('search') && !empty($request->search['value'])) {
            $search = $request->search['value'];
            $query->where(function ($q) use ($search) {
                $q->where('library_visits.id', 'like', "%{$search}%")
                  ->orWhere('library_visits.purpose', 'like', "%{$search}%")
                  ->orWhere('library_visits.note', 'like', "%{$search}%")
                  ->orWhere('users.name', 'like', "%{$search}%")
                  ->orWhere('users.email', 'like', "%{$search}%")
                  ->orWhere('library_guests.full_name', 'like', "%{$search}%")
                  ->orWhere('library_guests.phone', 'like', "%{$search}%")
                  ->orWhere('library_guests.id_card_no', 'like', "%{$search}%");
            });
        }
    }
}

