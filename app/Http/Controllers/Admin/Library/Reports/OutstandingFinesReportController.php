<?php

namespace App\Http\Controllers\Admin\Library\Reports;

use App\Http\Controllers\Controller;
use App\Models\LibraryFine;
use App\Models\LibraryLoan;
use App\Models\Student;
use App\Models\Teacher;
use App\Models\Staff;
use App\Models\LibraryGuest;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Yajra\DataTables\Facades\DataTables;
use Carbon\Carbon;

class OutstandingFinesReportController extends Controller
{
    /**
     * Permission helper methods
     */
    private function canRead()
    {
        return Auth::user()->hasAnyRole(['admin', 'manager']);
    }

    private function canWrite()
    {
        return Auth::user()->hasRole('admin');
    }

    /**
     * Display outstanding fines report page
     */
    public function index(Request $request)
    {
        if (!$this->canRead()) {
            abort(403, 'Unauthorized. Only Admin and Manager can view outstanding fines report.');
        }

        // Get unique fine types for filter
        $fineTypes = LibraryFine::where('is_active', 1)
            ->select('fine_type')
            ->distinct()
            ->whereNotNull('fine_type')
            ->orderBy('fine_type')
            ->pluck('fine_type');

        return view('admin.library.reports.outstanding_fines.index', compact('fineTypes'));
    }

    /**
     * Get DataTables JSON data for outstanding fines
     */
    public function data(Request $request)
    {
        if (!$this->canRead()) {
            return response()->json(['error' => 'Unauthorized.'], 403);
        }

        // Build base query for outstanding fines
        $query = $this->buildOutstandingFinesQuery($request);

        return DataTables::of($query)
            ->addIndexColumn()
            ->addColumn('fine_id', function ($fine) {
                return '#' . $fine->id;
            })
            ->addColumn('loan_id', function ($fine) {
                if ($fine->loan_id) {
                    return '#' . $fine->loan_id;
                }
                return '<span class="text-muted">N/A</span>';
            })
            ->addColumn('payer_user', function ($fine) {
                if ($fine->user) {
                    $name = e($fine->user->name ?? 'N/A');
                    $email = $fine->user->email ? ' (' . e($fine->user->email) . ')' : '';
                    return $name . $email;
                }
                return '<span class="text-muted">N/A</span>';
            })
            ->addColumn('borrower_info', function ($fine) {
                $borrowerName = $this->getBorrowerName($fine);
                $borrowerType = $fine->loan ? ucfirst($fine->loan->borrower_type) : 'N/A';
                
                $badgeColors = [
                    'student' => 'primary',
                    'teacher' => 'info',
                    'staff' => 'success',
                    'guest' => 'warning'
                ];
                $color = $fine->loan ? ($badgeColors[$fine->loan->borrower_type] ?? 'secondary') : 'secondary';
                
                return '<div>' .
                       '<strong>' . e($borrowerName) . '</strong><br>' .
                       ($fine->loan ? '<span class="badge bg-' . $color . '">' . $borrowerType . '</span>' : '') .
                       '</div>';
            })
            ->addColumn('book_info', function ($fine) {
                if ($fine->loan && $fine->loan->copy && $fine->loan->copy->item) {
                    $title = e($fine->loan->copy->item->title);
                    $isbn = $fine->loan->copy->item->isbn ? ' (ISBN: ' . e($fine->loan->copy->item->isbn) . ')' : '';
                    return $title . $isbn;
                }
                return '<span class="text-muted">N/A</span>';
            })
            ->addColumn('copy_info', function ($fine) {
                if ($fine->loan && $fine->loan->copy) {
                    $barcode = e($fine->loan->copy->barcode);
                    $callNumber = $fine->loan->copy->call_number ? ' / ' . e($fine->loan->copy->call_number) : '';
                    return '<div>' .
                           '<strong>Barcode:</strong> ' . $barcode . '<br>' .
                           ($fine->loan->copy->call_number ? '<small>Call: ' . e($fine->loan->copy->call_number) . '</small>' : '') .
                           '</div>';
                }
                return '<span class="text-muted">N/A</span>';
            })
            ->addColumn('fine_type', function ($fine) {
                $type = $fine->fine_type ?? 'N/A';
                $badgeColors = [
                    'overdue' => 'danger',
                    'damage' => 'warning',
                    'lost' => 'dark',
                    'late_return' => 'info'
                ];
                $color = $badgeColors[strtolower($type)] ?? 'secondary';
                return '<span class="badge bg-' . $color . '">' . e(ucfirst($type)) . '</span>';
            })
            ->editColumn('amount', function ($fine) {
                return number_format($fine->amount, 2) . ' $';
            })
            ->editColumn('paid_amount', function ($fine) {
                $paid = $fine->paid_amount ?? 0;
                return number_format($paid, 2) . ' $';
            })
            ->addColumn('outstanding_amount', function ($fine) {
                $outstanding = ($fine->amount ?? 0) - ($fine->paid_amount ?? 0);
                $outstanding = max(0, $outstanding); // Never negative
                $class = $outstanding > 0 ? 'text-danger fw-bold' : 'text-success';
                return '<span class="' . $class . '">' . number_format($outstanding, 2) . ' $</span>';
            })
            ->addColumn('status_badge', function ($fine) {
                $outstanding = ($fine->amount ?? 0) - ($fine->paid_amount ?? 0);
                $outstanding = max(0, $outstanding);
                
                // Derive status from outstanding amount if status is not reliable
                if ($outstanding <= 0) {
                    return '<span class="badge bg-success">Paid</span>';
                } elseif (($fine->paid_amount ?? 0) > 0) {
                    return '<span class="badge bg-warning">Partial</span>';
                } else {
                    return '<span class="badge bg-danger">Unpaid</span>';
                }
            })
            ->editColumn('assessed_at', function ($fine) {
                if ($fine->assessed_at) {
                    return Carbon::parse($fine->assessed_at)->format('Y-m-d H:i');
                }
                return 'N/A';
            })
            ->editColumn('paid_at', function ($fine) {
                if ($fine->paid_at) {
                    return Carbon::parse($fine->paid_at)->format('Y-m-d H:i');
                }
                return '<span class="text-muted">-</span>';
            })
            ->addColumn('actions', function ($fine) {
                $actions = '<div class="btn-group btn-group-sm" role="group">';
                
                // View Loan button (if loan exists)
                if ($fine->loan_id) {
                    $actions .= '<button type="button" class="btn btn-info" onclick="viewLoan(' . $fine->loan_id . ')" title="View Loan Details">
                                    <i class="fas fa-eye"></i>
                                </button>';
                }
                
                // Mark as Paid button (Admin only)
                $outstanding = ($fine->amount ?? 0) - ($fine->paid_amount ?? 0);
                if ($this->canWrite() && $outstanding > 0) {
                    $actions .= '<button type="button" class="btn btn-success" onclick="markAsPaid(' . $fine->id . ')" title="Mark as Paid">
                                    <i class="fas fa-check"></i>
                                </button>';
                }
                
                $actions .= '</div>';
                return $actions;
            })
            ->rawColumns(['loan_id', 'payer_user', 'borrower_info', 'book_info', 'copy_info', 'fine_type', 'outstanding_amount', 'status_badge', 'paid_at', 'actions'])
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
            $query = $this->buildOutstandingFinesQuery($request, false);

            $totalFines = $query->count();
            
            // Calculate totals
            $totals = $query->selectRaw('
                SUM(amount) as total_amount,
                SUM(COALESCE(paid_amount, 0)) as total_paid_amount,
                SUM(amount - COALESCE(paid_amount, 0)) as total_outstanding_amount
            ')->first();
            
            $totalAmount = $totals->total_amount ?? 0;
            $totalPaidAmount = $totals->total_paid_amount ?? 0;
            $totalOutstandingAmount = $totals->total_outstanding_amount ?? 0;
            
            // Count by status (derive from outstanding amount)
            $unpaidCount = (clone $query)
                ->whereRaw('COALESCE(paid_amount, 0) = 0')
                ->whereRaw('amount > COALESCE(paid_amount, 0)')
                ->count();
            
            $partialCount = (clone $query)
                ->whereRaw('COALESCE(paid_amount, 0) > 0')
                ->whereRaw('amount > COALESCE(paid_amount, 0)')
                ->count();
            
            $paidCount = (clone $query)
                ->whereRaw('amount <= COALESCE(paid_amount, 0)')
                ->count();
            
            // Calculate rates (handle divide-by-zero)
            $collectionRatePercent = $totalAmount > 0 
                ? round(($totalPaidAmount / $totalAmount) * 100, 2) 
                : 0;
            
            $outstandingRatePercent = $totalAmount > 0 
                ? round(($totalOutstandingAmount / $totalAmount) * 100, 2) 
                : 0;

            return response()->json([
                'success' => true,
                'data' => [
                    'total_fines' => $totalFines,
                    'total_amount' => round($totalAmount, 2),
                    'total_paid_amount' => round($totalPaidAmount, 2),
                    'total_outstanding_amount' => round($totalOutstandingAmount, 2),
                    'unpaid_count' => $unpaidCount,
                    'partial_count' => $partialCount,
                    'paid_count' => $paidCount,
                    'collection_rate_percent' => $collectionRatePercent,
                    'outstanding_rate_percent' => $outstandingRatePercent
                ]
            ]);
        } catch (\Exception $e) {
            Log::error('Outstanding Fines Summary Error: ' . $e->getMessage(), [
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
     * Mark fine as paid (Admin only)
     */
    public function markAsPaid(Request $request, $id)
    {
        if (!$this->canWrite()) {
            return response()->json(['error' => 'Unauthorized. Only Admin can mark fines as paid.'], 403);
        }

        try {
            $fine = LibraryFine::findOrFail($id);
            
            // Check if already fully paid
            $outstanding = $fine->amount - ($fine->paid_amount ?? 0);
            if ($outstanding <= 0) {
                return response()->json([
                    'success' => false,
                    'message' => 'This fine is already fully paid.'
                ], 400);
            }
            
            // Update fine
            $fine->paid_amount = $fine->amount;
            $fine->status = 'paid';
            $fine->paid_at = Carbon::now();
            $fine->updated_by = Auth::id();
            $fine->save();
            
            return response()->json([
                'success' => true,
                'message' => 'Fine marked as paid successfully.',
                'data' => [
                    'id' => $fine->id,
                    'paid_amount' => $fine->paid_amount,
                    'status' => $fine->status,
                    'paid_at' => $fine->paid_at->format('Y-m-d H:i:s')
                ]
            ]);
        } catch (\Exception $e) {
            Log::error('Mark Fine as Paid Error: ' . $e->getMessage(), [
                'trace' => $e->getTraceAsString(),
                'fine_id' => $id
            ]);
            
            return response()->json([
                'success' => false,
                'error' => 'Failed to mark fine as paid.',
                'message' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Build base query for outstanding fines
     * 
     * @param Request $request
     * @param bool $withRelations Whether to eager load relations (default: true)
     * @return \Illuminate\Database\Eloquent\Builder
     */
    private function buildOutstandingFinesQuery(Request $request, $withRelations = true)
    {
        // Base query: outstanding fines
        // Outstanding = is_active = 1 AND amount > COALESCE(paid_amount, 0)
        $query = LibraryFine::query();
        
        // Only eager load relations if needed (for DataTables, not for counts)
        if ($withRelations) {
            $query->with([
                'loan.copy.item',
                'user'
            ]);
        }
        
        // Outstanding fines: is_active = 1 AND amount > paid_amount
        $query->where('is_active', 1)
              ->whereRaw('amount > COALESCE(paid_amount, 0)');
        
        // Filter by date range (assessed_from, assessed_to)
        if ($request->filled('assessed_from')) {
            $assessedFrom = Carbon::parse($request->assessed_from)->startOfDay();
            $query->where('assessed_at', '>=', $assessedFrom);
        } else {
            // Default: last 30 days
            $defaultFrom = Carbon::today()->subDays(30)->startOfDay();
            $query->where('assessed_at', '>=', $defaultFrom);
        }
        
        if ($request->filled('assessed_to')) {
            $assessedTo = Carbon::parse($request->assessed_to)->endOfDay();
            $query->where('assessed_at', '<=', $assessedTo);
        }

        // Filter by status
        if ($request->filled('status') && $request->status !== 'all') {
            if ($request->status === 'unpaid') {
                $query->whereRaw('COALESCE(paid_amount, 0) = 0')
                      ->whereRaw('amount > COALESCE(paid_amount, 0)');
            } elseif ($request->status === 'partial') {
                $query->whereRaw('COALESCE(paid_amount, 0) > 0')
                      ->whereRaw('amount > COALESCE(paid_amount, 0)');
            } elseif ($request->status === 'paid') {
                $query->whereRaw('amount <= COALESCE(paid_amount, 0)');
            }
        }

        // Filter by fine_type
        if ($request->filled('fine_type') && $request->fine_type !== 'all') {
            $query->where('fine_type', $request->fine_type);
        }

        // Filter by borrower_type (through loan)
        if ($request->filled('borrower_type') && $request->borrower_type !== 'all') {
            $query->whereHas('loan', function ($q) use ($request) {
                $q->where('borrower_type', $request->borrower_type);
            });
        }

        // Filter by min_outstanding
        if ($request->filled('min_outstanding')) {
            $minOutstanding = (float) $request->min_outstanding;
            $query->whereRaw('(amount - COALESCE(paid_amount, 0)) >= ?', [$minOutstanding]);
        }

        // Filter by max_outstanding
        if ($request->filled('max_outstanding')) {
            $maxOutstanding = (float) $request->max_outstanding;
            $query->whereRaw('(amount - COALESCE(paid_amount, 0)) <= ?', [$maxOutstanding]);
        }

        // Search filter
        if ($request->filled('search') && !empty($request->search['value'])) {
            $search = $request->search['value'];
            $query->where(function ($q) use ($search) {
                // Search in fine ID
                $q->where('id', 'like', "%{$search}%")
                // Search in loan ID
                ->orWhere('loan_id', 'like', "%{$search}%")
                // Search in user name/email
                ->orWhereHas('user', function ($userQuery) use ($search) {
                    $userQuery->where('name', 'like', "%{$search}%")
                              ->orWhere('email', 'like', "%{$search}%");
                })
                // Search in borrower name (through loan)
                ->orWhereHas('loan', function ($loanQuery) use ($search) {
                    $loanQuery->where(function ($borrowerQuery) use ($search) {
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
                })
                // Search in book title (through loan->copy->item)
                ->orWhereHas('loan.copy.item', function ($itemQuery) use ($search) {
                    $itemQuery->where('title', 'like', "%{$search}%")
                              ->orWhere('isbn', 'like', "%{$search}%");
                })
                // Search in copy barcode/call number (through loan->copy)
                ->orWhereHas('loan.copy', function ($copyQuery) use ($search) {
                    $copyQuery->where('barcode', 'like', "%{$search}%")
                              ->orWhere('call_number', 'like', "%{$search}%");
                })
                // Search in note
                ->orWhere('note', 'like', "%{$search}%");
            });
        }

        return $query;
    }

    /**
     * Get borrower name based on loan's borrower_type and borrower_id
     */
    private function getBorrowerName($fine)
    {
        if (!$fine->loan) {
            return 'Unknown Borrower (No Loan)';
        }

        $loan = $fine->loan;
        
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
















