<?php

namespace App\Http\Controllers\Admin\Library\Reports;

use App\Http\Controllers\Controller;
use App\Models\LibraryItem;
use App\Models\LibraryCopy;
use App\Models\LibraryCategory;
use App\Models\LibraryShelf;
use App\Models\LibraryPublisher;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Yajra\DataTables\Facades\DataTables;
use Carbon\Carbon;

class CollectionSummaryReportController extends Controller
{
    /**
     * Permission helper methods
     */
    private function canRead()
    {
        return Auth::user()->hasAnyRole(['admin', 'manager']);
    }

    /**
     * Display collection summary report page
     */
    public function index(Request $request)
    {
        if (!$this->canRead()) {
            abort(403, 'Unauthorized. Only Admin and Manager can view collection summary report.');
        }

        // Get filter options
        $categories = LibraryCategory::where('is_active', 1)
            ->select('id', 'name')
            ->orderBy('name')
            ->get();

        $shelves = LibraryShelf::where('is_active', 1)
            ->select('id', 'code', 'location')
            ->orderBy('code')
            ->get();

        $publishers = LibraryPublisher::where('is_active', 1)
            ->select('id', 'name')
            ->orderBy('name')
            ->get();

        // Get unique languages
        $languages = LibraryItem::where('is_active', 1)
            ->select('language')
            ->distinct()
            ->whereNotNull('language')
            ->orderBy('language')
            ->pluck('language');

        return view('admin.library.reports.collection_summary.index', compact('categories', 'shelves', 'publishers', 'languages'));
    }

    /**
     * Get DataTables JSON data for collection summary (per title)
     */
    public function data(Request $request)
    {
        if (!$this->canRead()) {
            return response()->json(['error' => 'Unauthorized.'], 403);
        }

        // Build base query grouped by library_items
        $query = $this->buildCollectionSummaryQuery($request);

        return DataTables::of($query)
            ->addIndexColumn()
            ->addColumn('title_id', function ($item) {
                return '#' . $item->id;
            })
            ->addColumn('title', function ($item) {
                return '<strong>' . e($item->title) . '</strong>';
            })
            ->addColumn('isbn', function ($item) {
                return $item->isbn ? e($item->isbn) : '<span class="text-muted">N/A</span>';
            })
            ->addColumn('category', function ($item) {
                return $item->category_name ?? '<span class="text-muted">N/A</span>';
            })
            ->addColumn('publisher', function ($item) {
                return $item->publisher_name ?? '<span class="text-muted">N/A</span>';
            })
            ->addColumn('language', function ($item) {
                return $item->language ?? '<span class="text-muted">N/A</span>';
            })
            ->addColumn('published_year', function ($item) {
                return $item->published_year ?? '<span class="text-muted">N/A</span>';
            })
            ->addColumn('total_copies', function ($item) {
                return '<strong>' . ($item->total_copies ?? 0) . '</strong>';
            })
            ->addColumn('available', function ($item) {
                $count = $item->available_copies ?? 0;
                $badge = $count > 0 ? 'success' : 'secondary';
                return '<span class="badge bg-' . $badge . '">' . $count . '</span>';
            })
            ->addColumn('borrowed', function ($item) {
                $count = $item->borrowed_copies ?? 0;
                $badge = $count > 0 ? 'warning' : 'secondary';
                return '<span class="badge bg-' . $badge . '">' . $count . '</span>';
            })
            ->addColumn('lost', function ($item) {
                $count = $item->lost_copies ?? 0;
                $badge = $count > 0 ? 'danger' : 'secondary';
                return '<span class="badge bg-' . $badge . '">' . $count . '</span>';
            })
            ->addColumn('damaged', function ($item) {
                $count = $item->damaged_copies ?? 0;
                $badge = $count > 0 ? 'warning' : 'secondary';
                return '<span class="badge bg-' . $badge . '">' . $count . '</span>';
            })
            ->addColumn('availability_rate', function ($item) {
                $total = $item->total_copies ?? 0;
                $available = $item->available_copies ?? 0;
                
                if ($total > 0) {
                    $rate = round(($available / $total) * 100, 2); // 2 decimals as per requirement
                    $badge = $rate >= 50 ? 'success' : ($rate >= 25 ? 'warning' : 'danger');
                    return '<span class="badge bg-' . $badge . '">' . number_format($rate, 2) . '%</span>';
                }
                return '<span class="badge bg-secondary">N/A</span>';
            })
            ->addColumn('primary_shelf', function ($item) {
                if ($item->shelf_count > 1) {
                    return '<span class="badge bg-info">Mixed</span>';
                } elseif ($item->shelf_code) {
                    return e($item->shelf_code) . ($item->shelf_location ? ' (' . e($item->shelf_location) . ')' : '');
                }
                return '<span class="text-muted">N/A</span>';
            })
            ->addColumn('actions', function ($item) {
                $actions = '<div class="btn-group btn-group-sm" role="group">';
                
                // View Item button
                $actions .= '<button type="button" class="btn btn-info" onclick="viewItem(' . $item->id . ')" title="View Item Details">
                                <i class="fas fa-eye"></i>
                            </button>';
                
                // View Copies button
                $actions .= '<a href="' . route('admin.library.copies.index', ['item_id' => $item->id]) . '" 
                                class="btn btn-primary" title="View Copies">
                                <i class="fas fa-book"></i>
                            </a>';
                
                $actions .= '</div>';
                return $actions;
            })
            ->rawColumns(['title', 'isbn', 'category', 'publisher', 'language', 'published_year', 'total_copies', 'available', 'borrowed', 'lost', 'damaged', 'availability_rate', 'primary_shelf', 'actions'])
            ->make(true);
    }

    /**
     * Get summary/KPI data
     */
    public function summary(Request $request)
    {
        if (!$this->canRead()) {
            return response()->json(['error' => 'Unauthorized.'], 403);
        }

        try {
            // Build base query for filtering
            $baseQuery = $this->buildBaseFilterQuery($request);

            // Total titles (distinct items)
            $totalTitles = (clone $baseQuery)
                ->select('library_items.id')
                ->distinct()
                ->count('library_items.id');

            // Total copies and status counts
            $summary = (clone $baseQuery)
                ->selectRaw('
                    COUNT(library_copies.id) as total_copies,
                    SUM(CASE WHEN library_copies.status = "available" THEN 1 ELSE 0 END) as available_copies,
                    SUM(CASE WHEN library_copies.status IN ("borrowed", "on_loan") THEN 1 ELSE 0 END) as borrowed_copies,
                    SUM(CASE WHEN library_copies.status = "lost" THEN 1 ELSE 0 END) as lost_copies,
                    SUM(CASE WHEN library_copies.status = "damaged" THEN 1 ELSE 0 END) as damaged_copies
                ')
                ->first();

            $totalCopies = $summary->total_copies ?? 0;
            $availableCopies = $summary->available_copies ?? 0;
            $borrowedCopies = $summary->borrowed_copies ?? 0;
            $lostCopies = $summary->lost_copies ?? 0;
            $damagedCopies = $summary->damaged_copies ?? 0;

            // Calculate availability rate
            $availabilityRatePercent = $totalCopies > 0 
                ? round(($availableCopies / $totalCopies) * 100, 2) 
                : 0;

            return response()->json([
                'success' => true,
                'data' => [
                    'total_titles' => $totalTitles,
                    'total_copies' => $totalCopies,
                    'available_copies' => $availableCopies,
                    'borrowed_copies' => $borrowedCopies,
                    'lost_copies' => $lostCopies,
                    'damaged_copies' => $damagedCopies,
                    'availability_rate_percent' => $availabilityRatePercent
                ]
            ]);
        } catch (\Exception $e) {
            Log::error('Collection Summary Error: ' . $e->getMessage(), [
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
     * Get status distribution (optional, for charts)
     */
    public function byStatus(Request $request)
    {
        if (!$this->canRead()) {
            return response()->json(['error' => 'Unauthorized.'], 403);
        }

        try {
            $baseQuery = $this->buildBaseFilterQuery($request);

            $statusDistribution = (clone $baseQuery)
                ->selectRaw('
                    CASE 
                        WHEN library_copies.status = "on_loan" THEN "borrowed"
                        ELSE library_copies.status
                    END AS status,
                    COUNT(*) as count
                ')
                ->groupBy(DB::raw('
                    CASE 
                        WHEN library_copies.status = "on_loan" THEN "borrowed"
                        ELSE library_copies.status
                    END
                '))
                ->get()
                ->mapWithKeys(function ($item) {
                    return [$item->status => $item->count];
                });

            return response()->json([
                'success' => true,
                'data' => $statusDistribution
            ]);
        } catch (\Exception $e) {
            Log::error('Collection Summary byStatus Error: ' . $e->getMessage(), [
                'trace' => $e->getTraceAsString()
            ]);
            
            return response()->json([
                'success' => false,
                'error' => 'Failed to load status distribution.',
                'message' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Dashboard widget API endpoint
     * Returns simplified KPI data + status distribution for admin dashboard
     * 
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function dashboardWidget(Request $request)
    {
        // Allow dashboard access for admin/manager (same as canRead)
        if (!$this->canRead()) {
            return response()->json(['error' => 'Unauthorized.'], 403);
        }

        try {
            // Get KPI summary (no filters for dashboard widget)
            $baseQuery = DB::table('library_items')
                ->leftJoin('library_copies', function ($join) {
                    $join->on('library_copies.library_item_id', '=', 'library_items.id')
                         ->where('library_copies.is_active', 1);
                })
                ->where('library_items.is_active', 1);

            // Total titles (distinct items)
            $totalTitles = (clone $baseQuery)
                ->select('library_items.id')
                ->distinct()
                ->count('library_items.id');

            // Total copies and status counts
            $summary = (clone $baseQuery)
                ->selectRaw('
                    COUNT(library_copies.id) as total_copies,
                    SUM(CASE WHEN library_copies.status = "available" THEN 1 ELSE 0 END) as available_copies,
                    SUM(CASE WHEN library_copies.status IN ("borrowed", "on_loan") THEN 1 ELSE 0 END) as borrowed_copies,
                    SUM(CASE WHEN library_copies.status = "lost" THEN 1 ELSE 0 END) as lost_copies,
                    SUM(CASE WHEN library_copies.status = "damaged" THEN 1 ELSE 0 END) as damaged_copies
                ')
                ->first();

            $totalCopies = $summary->total_copies ?? 0;
            $availableCopies = $summary->available_copies ?? 0;
            $borrowedCopies = $summary->borrowed_copies ?? 0;
            $lostCopies = $summary->lost_copies ?? 0;
            $damagedCopies = $summary->damaged_copies ?? 0;

            // Calculate availability rate
            $availabilityRatePercent = $totalCopies > 0 
                ? round(($availableCopies / $totalCopies) * 100, 2) 
                : 0;

            // Get status distribution for chart (donut/pie)
            $statusDistribution = DB::table('library_copies')
                ->join('library_items', 'library_items.id', '=', 'library_copies.library_item_id')
                ->where('library_copies.is_active', 1)
                ->where('library_items.is_active', 1)
                ->selectRaw('
                    CASE 
                        WHEN library_copies.status = "on_loan" THEN "borrowed"
                        WHEN library_copies.status = "available" THEN "available"
                        WHEN library_copies.status = "lost" THEN "lost"
                        WHEN library_copies.status = "damaged" THEN "damaged"
                        ELSE "other"
                    END AS status,
                    COUNT(*) as count,
                    ROUND(
                        (COUNT(*) * 100.0) / 
                        (SELECT COUNT(*) FROM library_copies WHERE is_active = 1), 
                        2
                    ) AS percentage
                ')
                ->groupBy(DB::raw('
                    CASE 
                        WHEN library_copies.status = "on_loan" THEN "borrowed"
                        WHEN library_copies.status = "available" THEN "available"
                        WHEN library_copies.status = "lost" THEN "lost"
                        WHEN library_copies.status = "damaged" THEN "damaged"
                        ELSE "other"
                    END
                '))
                ->orderBy('count', 'desc')
                ->get();

            // Format status distribution for chart.js (donut/pie)
            $chartLabels = $statusDistribution->pluck('status')->map(function ($status) {
                return ucfirst($status);
            })->toArray();
            
            $chartData = $statusDistribution->pluck('count')->toArray();
            
            $chartColors = [
                'available' => '#28a745',  // Green
                'borrowed' => '#ffc107',   // Yellow/Warning
                'lost' => '#dc3545',      // Red/Danger
                'damaged' => '#6c757d',   // Gray/Secondary
                'other' => '#17a2b8'      // Info/Cyan
            ];
            
            $chartBackgroundColors = $statusDistribution->map(function ($item) use ($chartColors) {
                return $chartColors[$item->status] ?? '#6c757d';
            })->toArray();

            return response()->json([
                'success' => true,
                'data' => [
                    // KPI values
                    'total_titles' => $totalTitles,
                    'total_copies' => $totalCopies,
                    'available' => $availableCopies,
                    'borrowed' => $borrowedCopies,
                    'lost' => $lostCopies,
                    'damaged' => $damagedCopies,
                    'availability_rate_percent' => $availabilityRatePercent,
                    
                    // Chart data for donut/pie chart (Chart.js format)
                    'chart' => [
                        'labels' => $chartLabels,
                        'datasets' => [
                            [
                                'label' => 'Copies by Status',
                                'data' => $chartData,
                                'backgroundColor' => $chartBackgroundColors,
                                'borderColor' => '#ffffff',
                                'borderWidth' => 2
                            ]
                        ]
                    ],
                    
                    // Status distribution details
                    'status_distribution' => $statusDistribution->map(function ($item) {
                        return [
                            'status' => $item->status,
                            'count' => $item->count,
                            'percentage' => $item->percentage
                        ];
                    })->values()
                ]
            ]);
        } catch (\Exception $e) {
            Log::error('Collection Summary Dashboard Widget Error: ' . $e->getMessage(), [
                'trace' => $e->getTraceAsString()
            ]);
            
            return response()->json([
                'success' => false,
                'error' => 'Failed to load dashboard widget data.',
                'message' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Build base filter query (for summary and byStatus)
     * Uses LEFT JOIN to include items without copies when no copy-specific filters are applied
     */
    private function buildBaseFilterQuery(Request $request)
    {
        // Check if we have copy-specific filters
        $hasCopyFilters = $request->filled('shelf_id') || 
                         $request->filled('acquired_from') || 
                         $request->filled('acquired_to') || 
                         ($request->filled('status') && $request->status !== 'all');

        if ($hasCopyFilters) {
            // Use INNER JOIN when filtering by copy attributes
            $query = DB::table('library_items')
                ->join('library_copies', 'library_copies.library_item_id', '=', 'library_items.id')
                ->where('library_items.is_active', 1)
                ->where('library_copies.is_active', 1);
        } else {
            // Use LEFT JOIN to include all items (even without copies)
            $query = DB::table('library_items')
                ->leftJoin('library_copies', function ($join) {
                    $join->on('library_copies.library_item_id', '=', 'library_items.id')
                         ->where('library_copies.is_active', 1);
                })
                ->where('library_items.is_active', 1);
        }

        // Apply filters
        $this->applyFilters($query, $request);

        return $query;
    }

    /**
     * Build collection summary query (grouped by items)
     */
    private function buildCollectionSummaryQuery(Request $request)
    {
        $query = DB::table('library_items')
            ->leftJoin('library_copies', function ($join) {
                $join->on('library_copies.library_item_id', '=', 'library_items.id')
                     ->where('library_copies.is_active', 1);
            })
            ->leftJoin('library_categories', 'library_categories.id', '=', 'library_items.category_id')
            ->leftJoin('library_publishers', 'library_publishers.id', '=', 'library_items.publisher_id')
            ->leftJoin('library_shelves', 'library_shelves.id', '=', 'library_copies.shelf_id')
            ->where('library_items.is_active', 1)
            ->select([
                'library_items.id',
                'library_items.title',
                'library_items.isbn',
                'library_items.language',
                'library_items.published_year',
                'library_categories.name as category_name',
                'library_publishers.name as publisher_name',
                // Copy counts by status
                // Note: Database uses 'on_loan' but we map it to 'borrowed' for reporting
                DB::raw('COUNT(library_copies.id) as total_copies'),
                DB::raw('SUM(CASE WHEN library_copies.status = "available" THEN 1 ELSE 0 END) as available_copies'),
                DB::raw('SUM(CASE WHEN library_copies.status IN ("borrowed", "on_loan") THEN 1 ELSE 0 END) as borrowed_copies'),
                DB::raw('SUM(CASE WHEN library_copies.status = "lost" THEN 1 ELSE 0 END) as lost_copies'),
                DB::raw('SUM(CASE WHEN library_copies.status = "damaged" THEN 1 ELSE 0 END) as damaged_copies'),
                // Shelf information
                DB::raw('COUNT(DISTINCT library_copies.shelf_id) as shelf_count'),
                DB::raw('MAX(library_shelves.code) as shelf_code'),
                DB::raw('MAX(library_shelves.location) as shelf_location')
            ])
            ->groupBy([
                'library_items.id',
                'library_items.title',
                'library_items.isbn',
                'library_items.language',
                'library_items.published_year',
                'library_categories.name',
                'library_publishers.name'
            ]);

        // Apply filters
        $this->applyFilters($query, $request);

        return $query;
    }

    /**
     * Apply filters to query
     */
    private function applyFilters($query, Request $request)
    {
        // Filter by category
        if ($request->filled('category_id') && $request->category_id !== 'all') {
            $query->where('library_items.category_id', $request->category_id);
        }

        // Filter by shelf (through copies)
        if ($request->filled('shelf_id') && $request->shelf_id !== 'all') {
            $query->where('library_copies.shelf_id', $request->shelf_id);
        }

        // Filter by publisher
        if ($request->filled('publisher_id') && $request->publisher_id !== 'all') {
            $query->where('library_items.publisher_id', $request->publisher_id);
        }

        // Filter by language
        if ($request->filled('language') && $request->language !== 'all') {
            $query->where('library_items.language', $request->language);
        }

        // Filter by published year range
        if ($request->filled('published_year_from')) {
            $query->where('library_items.published_year', '>=', $request->published_year_from);
        }
        if ($request->filled('published_year_to')) {
            $query->where('library_items.published_year', '<=', $request->published_year_to);
        }

        // Filter by acquired date range
        if ($request->filled('acquired_from')) {
            $acquiredFrom = Carbon::parse($request->acquired_from)->startOfDay();
            $query->where('library_copies.acquired_date', '>=', $acquiredFrom);
        }
        if ($request->filled('acquired_to')) {
            $acquiredTo = Carbon::parse($request->acquired_to)->endOfDay();
            $query->where('library_copies.acquired_date', '<=', $acquiredTo);
        }

        // Filter by status (titles that have at least 1 copy in that status)
        // Map 'borrowed' to 'on_loan' for database query
        if ($request->filled('status') && $request->status !== 'all') {
            $status = $request->status;
            if ($status === 'borrowed') {
                $query->whereIn('library_copies.status', ['borrowed', 'on_loan']);
            } else {
                $query->where('library_copies.status', $status);
            }
        }

        // Search filter
        if ($request->filled('search') && !empty($request->search['value'])) {
            $search = $request->search['value'];
            $query->where(function ($q) use ($search) {
                $q->where('library_items.title', 'like', "%{$search}%")
                  ->orWhere('library_items.isbn', 'like', "%{$search}%")
                  ->orWhere('library_copies.call_number', 'like', "%{$search}%")
                  ->orWhere('library_copies.barcode', 'like', "%{$search}%")
                  ->orWhere('library_categories.name', 'like', "%{$search}%")
                  ->orWhere('library_shelves.code', 'like', "%{$search}%")
                  ->orWhere('library_shelves.location', 'like', "%{$search}%")
                  ->orWhere('library_publishers.name', 'like', "%{$search}%");
            });
        }
    }
}

