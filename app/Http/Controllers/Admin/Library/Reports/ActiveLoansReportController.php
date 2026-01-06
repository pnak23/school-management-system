<?php

namespace App\Http\Controllers\Admin\Library\Reports;

use App\Http\Controllers\Controller;
use App\Models\LibraryLoan;
use App\Models\LibraryCopy;
use App\Models\LibraryItem;
use App\Models\LibraryCategory;
use App\Models\LibraryShelf;
use App\Models\Student;
use App\Models\Teacher;
use App\Models\Staff;
use App\Models\LibraryGuest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Yajra\DataTables\Facades\DataTables;
use Carbon\Carbon;

class ActiveLoansReportController extends Controller
{
    /**
     * Permission helper methods
     */
    private function canRead()
    {
        return Auth::user()->hasAnyRole(['admin', 'manager']);
    }

    /**
     * Display active loans report page
     */
    public function index(Request $request)
    {
        if (!$this->canRead()) {
            abort(403, 'Unauthorized. Only Admin and Manager can view active loans report.');
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

        return view('admin.library.reports.active_loans.index', compact('categories', 'shelves'));
    }

    /**
     * Get DataTables JSON data for active loans
     */
    public function data(Request $request)
    {
        if (!$this->canRead()) {
            return response()->json(['error' => 'Unauthorized.'], 403);
        }

        // Build base query for active loans
        $query = $this->buildActiveLoansQuery($request);

        return DataTables::of($query)
            ->addIndexColumn()
            ->addColumn('loan_id', function ($loan) {
                return '#' . $loan->id;
            })
            ->addColumn('borrower_info', function ($loan) {
                $borrowerName = $this->getBorrowerName($loan);
                $borrowerType = ucfirst($loan->borrower_type);
                
                $badgeColors = [
                    'student' => 'primary',
                    'teacher' => 'info',
                    'staff' => 'success',
                    'guest' => 'warning'
                ];
                $color = $badgeColors[$loan->borrower_type] ?? 'secondary';
                
                return '<div>' .
                       '<strong>' . e($borrowerName) . '</strong><br>' .
                       '<span class="badge bg-' . $color . '">' . $borrowerType . '</span>' .
                       '</div>';
            })
            ->addColumn('book_info', function ($loan) {
                if ($loan->copy && $loan->copy->item) {
                    $title = e($loan->copy->item->title);
                    $isbn = $loan->copy->item->isbn ? ' (ISBN: ' . e($loan->copy->item->isbn) . ')' : '';
                    return $title . $isbn;
                }
                return '<span class="text-muted">N/A</span>';
            })
            ->addColumn('copy_info', function ($loan) {
                if ($loan->copy) {
                    $barcode = e($loan->copy->barcode);
                    $callNumber = $loan->copy->call_number ? ' / ' . e($loan->copy->call_number) : '';
                    return '<div>' .
                           '<strong>Barcode:</strong> ' . $barcode . '<br>' .
                           ($loan->copy->call_number ? '<small>Call: ' . e($loan->copy->call_number) . '</small>' : '') .
                           '</div>';
                }
                return '<span class="text-muted">N/A</span>';
            })
            ->editColumn('borrowed_at', function ($loan) {
                if ($loan->borrowed_at) {
                    return Carbon::parse($loan->borrowed_at)->format('Y-m-d H:i');
                }
                return 'N/A';
            })
            ->editColumn('due_date', function ($loan) {
                if ($loan->due_date) {
                    $date = Carbon::parse($loan->due_date)->format('Y-m-d');
                    $isOverdue = Carbon::parse($loan->due_date)->isPast();
                    $isDueSoon = $this->isDueSoon($loan->due_date);
                    $class = $isOverdue ? 'text-danger' : ($isDueSoon ? 'text-warning' : '');
                    return '<span class="' . $class . '">' . $date . '</span>';
                }
                return 'N/A';
            })
            ->addColumn('remaining_days', function ($loan) {
                if ($loan->due_date) {
                    $today = Carbon::today();
                    $dueDate = Carbon::parse($loan->due_date)->startOfDay();
                    
                    // Calculate remaining days (positive if future, negative if past)
                    $days = $today->diffInDays($dueDate, false);
                    
                    if ($days < 0) {
                        // Overdue
                        $daysOverdue = abs($days);
                        $badge = $daysOverdue > 30 ? 'danger' : ($daysOverdue > 14 ? 'warning' : 'info');
                        return '<span class="badge bg-' . $badge . '">' . $daysOverdue . ' days overdue</span>';
                    } elseif ($days <= 3) {
                        // Due soon
                        return '<span class="badge bg-warning">' . $days . ' day' . ($days !== 1 ? 's' : '') . ' left</span>';
                    } else {
                        // Normal
                        return '<span class="badge bg-success">' . $days . ' day' . ($days !== 1 ? 's' : '') . ' left</span>';
                    }
                }
                return 'N/A';
            })
            ->addColumn('status_badge', function ($loan) {
                if ($loan->due_date) {
                    $today = Carbon::today();
                    $dueDate = Carbon::parse($loan->due_date)->startOfDay();
                    $days = $today->diffInDays($dueDate, false);
                    
                    if ($days < 0) {
                        return '<span class="badge bg-danger">Overdue</span>';
                    } elseif ($days <= 3) {
                        return '<span class="badge bg-warning">Due Soon</span>';
                    } else {
                        return '<span class="badge bg-success">Normal</span>';
                    }
                }
                return '<span class="badge bg-secondary">Unknown</span>';
            })
            ->addColumn('processed_by', function ($loan) {
                if ($loan->processedByStaff) {
                    $name = $loan->processedByStaff->english_name ?? $loan->processedByStaff->khmer_name ?? 'N/A';
                    $code = $loan->processedByStaff->staff_code ?? '';
                    return $name . ($code ? ' (' . $code . ')' : '');
                }
                return '<span class="text-muted">N/A</span>';
            })
            ->addColumn('actions', function ($loan) {
                $actions = '<div class="btn-group btn-group-sm" role="group">';
                
                // View Loan button
                $actions .= '<button type="button" class="btn btn-info" onclick="viewLoan(' . $loan->id . ')" title="View Loan Details">
                                <i class="fas fa-eye"></i>
                            </button>';
                
                // Mark Returned button (shows SweetAlert)
                $actions .= '<button type="button" class="btn btn-success" onclick="markReturned(' . $loan->id . ')" title="Mark Returned">
                                <i class="fas fa-check"></i>
                            </button>';
                
                $actions .= '</div>';
                return $actions;
            })
            ->rawColumns(['borrower_info', 'book_info', 'copy_info', 'borrowed_at', 'due_date', 'remaining_days', 'status_badge', 'processed_by', 'actions'])
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
            // Build query without eager loading for better performance on counts
            $query = $this->buildActiveLoansQuery($request, false);

            $totalActiveLoans = $query->count();
            
            // Count due soon (due within 3 days, configurable)
            $dueSoonDays = 3; // Can be made configurable
            $today = Carbon::today();
            $dueSoonDate = $today->copy()->addDays($dueSoonDays);
            
            $dueSoonCount = (clone $query)
                ->whereNull('returned_at')
                ->whereBetween('due_date', [$today->format('Y-m-d'), $dueSoonDate->format('Y-m-d')])
                ->count();
            
            // Count overdue
            $overdueCount = (clone $query)
                ->whereNull('returned_at')
                ->where('due_date', '<', $today->format('Y-m-d'))
                ->count();
            
            // Count distinct borrowers
            $uniqueBorrowers = (clone $query)
                ->select('borrower_type', 'borrower_id')
                ->groupBy('borrower_type', 'borrower_id')
                ->get()
                ->count();
            
            // Calculate average days remaining (or to due)
            $avgDaysRemaining = 0;
            if ($totalActiveLoans > 0) {
                $avgDaysQuery = (clone $query)
                    ->whereNotNull('due_date')
                    ->selectRaw('AVG(DATEDIFF(due_date, CURDATE())) as avg_days')
                    ->value('avg_days');
                
                $avgDaysRemaining = $avgDaysQuery ? round($avgDaysQuery, 1) : 0;
            }

            return response()->json([
                'success' => true,
                'data' => [
                    'total_active_loans' => $totalActiveLoans,
                    'due_soon_count' => $dueSoonCount,
                    'overdue_count' => $overdueCount,
                    'unique_borrowers' => $uniqueBorrowers,
                    'avg_days_remaining' => $avgDaysRemaining
                ]
            ]);
        } catch (\Exception $e) {
            Log::error('Active Loans Summary Error: ' . $e->getMessage(), [
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
     * Build base query for active loans
     * 
     * @param Request $request
     * @param bool $withRelations Whether to eager load relations (default: true)
     * @return \Illuminate\Database\Eloquent\Builder
     */
    private function buildActiveLoansQuery(Request $request, $withRelations = true)
    {
        // Base query: active loans (returned_at IS NULL)
        $query = LibraryLoan::query();
        
        // Only eager load relations if needed (for DataTables, not for counts)
        if ($withRelations) {
            $query->with([
                'copy.item.category',
                'copy.shelf',
                'processedByStaff'
            ]);
        }
        
        // Active loans: returned_at IS NULL
        $query->whereNull('returned_at');
        
        // Optional: filter by status if status field exists
        $query->where(function ($q) {
            $q->where(function ($subQ) {
                $subQ->where('status', 'borrowed')
                     ->orWhere('status', 'active');
            })->orWhereNull('status');
        });
        
        // Filter by date range (borrowed_from, borrowed_to)
        if ($request->filled('borrowed_from')) {
            $borrowedFrom = Carbon::parse($request->borrowed_from)->startOfDay();
            $query->where('borrowed_at', '>=', $borrowedFrom);
        } else {
            // Default: last 30 days
            $defaultFrom = Carbon::today()->subDays(30)->startOfDay();
            $query->where('borrowed_at', '>=', $defaultFrom);
        }
        
        if ($request->filled('borrowed_to')) {
            $borrowedTo = Carbon::parse($request->borrowed_to)->endOfDay();
            $query->where('borrowed_at', '<=', $borrowedTo);
        }

        // Filter by borrower type
        if ($request->filled('borrower_type') && $request->borrower_type !== 'all') {
            $query->where('borrower_type', $request->borrower_type);
        }

        // Filter by status bucket: all / due_soon / overdue / normal
        if ($request->filled('status_bucket') && $request->status_bucket !== 'all') {
            $today = Carbon::today();
            $dueSoonDate = $today->copy()->addDays(3);
            
            switch ($request->status_bucket) {
                case 'due_soon':
                    // Due within next 3 days
                    $query->whereBetween('due_date', [$today->format('Y-m-d'), $dueSoonDate->format('Y-m-d')]);
                    break;
                    
                case 'overdue':
                    // Overdue (due_date < today)
                    $query->where('due_date', '<', $today->format('Y-m-d'));
                    break;
                    
                case 'normal':
                    // Normal (due_date > 3 days from now)
                    $query->where('due_date', '>', $dueSoonDate->format('Y-m-d'));
                    break;
            }
        }

        // Filter by category
        if ($request->filled('category_id') && $request->category_id !== 'all') {
            $query->whereHas('copy', function ($copyQuery) use ($request) {
                $copyQuery->where('is_active', 1)
                          ->whereHas('item', function ($itemQuery) use ($request) {
                              $itemQuery->where('category_id', $request->category_id)
                                        ->where('is_active', 1);
                          });
            });
        } else {
            // Always filter active items and copies
            $query->whereHas('copy', function ($copyQuery) {
                $copyQuery->where('is_active', 1)
                          ->whereHas('item', function ($itemQuery) {
                              $itemQuery->where('is_active', 1);
                          });
            });
        }

        // Filter by shelf
        if ($request->filled('shelf_id') && $request->shelf_id !== 'all') {
            $query->whereHas('copy', function ($q) use ($request) {
                $q->where('shelf_id', $request->shelf_id)
                  ->where('is_active', 1);
            });
        } else {
            // Always filter active copies
            $query->whereHas('copy', function ($q) {
                $q->where('is_active', 1);
            });
        }

        // Search filter
        if ($request->filled('search') && !empty($request->search['value'])) {
            $search = $request->search['value'];
            $query->where(function ($q) use ($search) {
                // Search in loan ID
                $q->where('id', 'like', "%{$search}%")
                // Search in book title
                ->orWhereHas('copy', function ($copyQuery) use ($search) {
                    $copyQuery->whereHas('item', function ($itemQuery) use ($search) {
                        $itemQuery->where('title', 'like', "%{$search}%")
                                  ->orWhere('isbn', 'like', "%{$search}%");
                    });
                })
                // Search in copy barcode/call number
                ->orWhereHas('copy', function ($copyQuery) use ($search) {
                    $copyQuery->where('barcode', 'like', "%{$search}%")
                              ->orWhere('call_number', 'like', "%{$search}%");
                })
                // Search in borrower name (polymorphic) - using subqueries
                ->orWhere(function ($borrowerQuery) use ($search) {
                    $borrowerQuery->where(function ($q) use ($search) {
                        $q->where('borrower_type', 'student')
                          ->whereIn('borrower_id', function ($subQuery) use ($search) {
                              $subQuery->select('id')
                                  ->from('students')
                                  ->where('khmer_name', 'like', "%{$search}%")
                                  ->orWhere('english_name', 'like', "%{$search}%");
                          });
                    })
                    ->orWhere(function ($q) use ($search) {
                        $q->where('borrower_type', 'teacher')
                          ->whereIn('borrower_id', function ($subQuery) use ($search) {
                              $subQuery->select('id')
                                  ->from('teachers')
                                  ->where('khmer_name', 'like', "%{$search}%")
                                  ->orWhere('english_name', 'like', "%{$search}%");
                          });
                    })
                    ->orWhere(function ($q) use ($search) {
                        $q->where('borrower_type', 'staff')
                          ->whereIn('borrower_id', function ($subQuery) use ($search) {
                              $subQuery->select('id')
                                  ->from('staff')
                                  ->where('khmer_name', 'like', "%{$search}%")
                                  ->orWhere('english_name', 'like', "%{$search}%");
                          });
                    })
                    ->orWhere(function ($q) use ($search) {
                        $q->where('borrower_type', 'guest')
                          ->whereIn('borrower_id', function ($subQuery) use ($search) {
                              $subQuery->select('id')
                                  ->from('library_guests')
                                  ->where('full_name', 'like', "%{$search}%");
                          });
                    });
                });
            });
        }

        return $query;
    }

    /**
     * Check if due date is "due soon" (within 3 days)
     */
    private function isDueSoon($dueDate, $days = 3)
    {
        if (!$dueDate) return false;
        
        $today = Carbon::today();
        $due = Carbon::parse($dueDate)->startOfDay();
        $daysDiff = $today->diffInDays($due, false);
        
        // Due soon: between today and 3 days from now (inclusive)
        return $daysDiff >= 0 && $daysDiff <= $days;
    }

    /**
     * Get borrower name based on borrower_type and borrower_id
     */
    private function getBorrowerName($loan)
    {
        switch ($loan->borrower_type) {
            case 'student':
                $student = Student::find($loan->borrower_id);
                if ($student) {
                    return $student->english_name ?? $student->khmer_name ?? 'Unknown Student';
                }
                break;

            case 'teacher':
                $teacher = Teacher::find($loan->borrower_id);
                if ($teacher) {
                    return $teacher->english_name ?? $teacher->khmer_name ?? 'Unknown Teacher';
                }
                break;

            case 'staff':
                $staff = Staff::find($loan->borrower_id);
                if ($staff) {
                    return $staff->english_name ?? $staff->khmer_name ?? 'Unknown Staff';
                }
                break;

            case 'guest':
                $guest = LibraryGuest::find($loan->borrower_id);
                if ($guest) {
                    return $guest->full_name ?? 'Unknown Guest';
                }
                break;
        }

        return 'Unknown Borrower (#' . $loan->borrower_id . ')';
    }
}

