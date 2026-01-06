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
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use Barryvdh\DomPDF\Facade\Pdf;

class OverdueLoansReportController extends Controller
{
    /**
     * Permission helper methods
     */
    private function canRead()
    {
        return Auth::user()->hasAnyRole(['admin', 'manager']);
    }

    /**
     * Display overdue loans report page
     */
    public function index(Request $request)
    {
        if (!$this->canRead()) {
            abort(403, 'Unauthorized. Only Admin and Manager can view overdue loans report.');
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

        return view('admin.library.reports.overdue_loans.index', compact('categories', 'shelves'));
    }

    /**
     * Get DataTables JSON data for overdue loans
     */
    public function data(Request $request)
    {
        if (!$this->canRead()) {
            return response()->json(['error' => 'Unauthorized.'], 403);
        }

        // Build base query for overdue loans
        $query = $this->buildOverdueQuery($request);

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
            ->editColumn('due_date', function ($loan) {
                if ($loan->due_date) {
                    $date = Carbon::parse($loan->due_date)->format('Y-m-d');
                    $isOverdue = Carbon::parse($loan->due_date)->isPast();
                    $class = $isOverdue ? 'text-danger' : '';
                    return '<span class="' . $class . '">' . $date . '</span>';
                }
                return 'N/A';
            })
            ->addColumn('days_overdue', function ($loan) use ($request) {
                if ($loan->due_date) {
                    // Use overdue_as_of date from request, default to today
                    $overdueAsOf = $request->get('overdue_as_of', Carbon::today()->format('Y-m-d'));
                    $overdueAsOfDate = Carbon::parse($overdueAsOf)->endOfDay();
                    $dueDate = Carbon::parse($loan->due_date)->startOfDay();
                    
                    // Calculate days overdue: DATEDIFF(overdue_as_of, due_date)
                    // This matches SQL: DATEDIFF(overdue_as_of, due_date)
                    // Returns positive if overdue_as_of > due_date (overdue)
                    $days = $overdueAsOfDate->diffInDays($dueDate, false);
                    
                    // Ensure positive (should always be positive for overdue loans)
                    if ($days > 0) {
                        $badge = $days > 30 ? 'danger' : ($days > 14 ? 'warning' : 'info');
                        return '<span class="badge bg-' . $badge . '">' . $days . ' days</span>';
                    }
                    return '<span class="badge bg-secondary">0 days</span>';
                }
                return 'N/A';
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
                
                // Send Reminder button (dummy for now)
                $actions .= '<button type="button" class="btn btn-warning" onclick="sendReminder(' . $loan->id . ')" title="Send Reminder">
                                <i class="fas fa-bell"></i>
                            </button>';
                
                $actions .= '</div>';
                return $actions;
            })
            ->rawColumns(['borrower_info', 'book_info', 'copy_info', 'due_date', 'days_overdue', 'processed_by', 'actions'])
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
            $query = $this->buildOverdueQuery($request, false);

            $totalOverdue = $query->count();
            
            // Count distinct borrowers (borrower_type + borrower_id combination)
            $totalBorrowers = (clone $query)
                ->select('borrower_type', 'borrower_id')
                ->groupBy('borrower_type', 'borrower_id')
                ->get()
                ->count();
            
            // Count distinct copies
            $totalCopies = (clone $query)
                ->select('library_copy_id')
                ->groupBy('library_copy_id')
                ->get()
                ->count();
            
            // Calculate max days overdue
            $maxDaysOverdue = 0;
            if ($totalOverdue > 0) {
                $overdueAsOf = $request->get('overdue_as_of', Carbon::today()->format('Y-m-d'));
                $overdueAsOfDate = Carbon::parse($overdueAsOf)->endOfDay();
                
                // Build a fresh query for max days calculation (without relations)
                $maxDaysQuery = $this->buildOverdueQuery($request, false);
                // DATEDIFF(overdue_as_of, due_date) - MySQL syntax
                $maxDaysOverdue = $maxDaysQuery->selectRaw('MAX(DATEDIFF(?, DATE(due_date))) as max_days', [$overdueAsOfDate->format('Y-m-d')])
                    ->value('max_days') ?? 0;
            }
            
            // Calculate estimated fine (optional - if fine calculation exists)
            $estimatedFine = 0; // Placeholder - can be calculated based on fine rules

            return response()->json([
                'success' => true,
                'data' => [
                    'total_overdue' => $totalOverdue,
                    'total_borrowers' => $totalBorrowers,
                    'total_copies' => $totalCopies,
                    'max_days_overdue' => (int) $maxDaysOverdue,
                    'estimated_fine' => $estimatedFine
                ]
            ]);
        } catch (\Exception $e) {
            Log::error('Overdue Loans Summary Error: ' . $e->getMessage(), [
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
     * Build base query for overdue loans
     * 
     * @param Request $request
     * @param bool $withRelations Whether to eager load relations (default: true)
     * @return \Illuminate\Database\Eloquent\Builder
     */
    private function buildOverdueQuery(Request $request, $withRelations = true)
    {
        // Base query: loans that are overdue
        $overdueAsOf = $request->get('overdue_as_of', Carbon::today()->format('Y-m-d'));
        $overdueAsOfDate = Carbon::parse($overdueAsOf)->endOfDay();

        $query = LibraryLoan::query();
        
        // Only eager load relations if needed (for DataTables, not for counts)
        if ($withRelations) {
            $query->with([
                'copy.item.category',
                'copy.shelf',
                'processedByStaff'
            ]);
        }
        
        $query->whereNull('returned_at')
            ->where('due_date', '<', $overdueAsOfDate)
            ->where(function ($q) {
                // Exclude returned/cancelled if status field exists
                $q->where(function ($subQ) {
                    $subQ->where('status', '!=', 'returned')
                         ->where('status', '!=', 'cancelled');
                })->orWhereNull('status');
            });

        // Filter by borrower type
        if ($request->filled('borrower_type') && $request->borrower_type !== 'all') {
            $query->where('borrower_type', $request->borrower_type);
        }

        // Filter by category
        if ($request->filled('category_id') && $request->category_id !== 'all') {
            $query->whereHas('copy.item', function ($q) use ($request) {
                $q->where('category_id', $request->category_id);
            });
        }

        // Filter by shelf
        if ($request->filled('shelf_id') && $request->shelf_id !== 'all') {
            $query->whereHas('copy', function ($q) use ($request) {
                $q->where('shelf_id', $request->shelf_id);
            });
        }

        // Search filter
        if ($request->filled('search') && !empty($request->search['value'])) {
            $search = $request->search['value'];
            $query->where(function ($q) use ($search) {
                // Search in book title
                $q->whereHas('copy.item', function ($itemQuery) use ($search) {
                    $itemQuery->where('title', 'like', "%{$search}%")
                              ->orWhere('isbn', 'like', "%{$search}%");
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

