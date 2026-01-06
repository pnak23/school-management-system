<?php

namespace App\Http\Controllers\Admin\Library;

use App\Http\Controllers\Controller;
use App\Models\LibraryCopy;
use App\Models\LibraryLoan;
use App\Models\LibraryGuest;
use App\Models\Staff;
use App\Models\Student;
use App\Models\Teacher;
use App\Traits\LogsActivity;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Artisan;
use Yajra\DataTables\Facades\DataTables;
use Carbon\Carbon;

class LibraryLoanController extends Controller
{
    use LogsActivity;
    // Permission helper methods
    private function canRead()
    {
        $user = Auth::user();
        return $user->hasAnyRole(['admin', 'manager', 'staff', 'principal']);
    }

    private function canWrite()
    {
        $user = Auth::user();
        return $user->hasAnyRole(['admin', 'manager', 'staff']);
    }

    private function canReturn()
    {
        $user = Auth::user();
        return $user->hasAnyRole(['admin', 'manager', 'staff']);
    }

    private function canDelete()
    {
        return Auth::user()->hasRole('admin');
    }

    // Get current staff ID from auth user
    private function getCurrentStaffId()
    {
        $user = Auth::user();
        $staff = Staff::where('user_id', $user->id)->first();
        return $staff ? $staff->id : null;
    }

    /**
     * Display listing (View + Ajax DataTables)
     */
    public function index(Request $request)
    {
        if (!$this->canRead()) {
            abort(403, 'Unauthorized action.');
        }

        if ($request->ajax()) {
            $query = LibraryLoan::with(['copy.item', 'processedByStaff', 'receivedByStaff'])
                ->select('library_loans.*');

            // Filter by status
            if ($request->filled('status') && $request->status !== 'all') {
                if ($request->status === 'overdue') {
                    $query->where('status', 'borrowed')
                        ->whereNull('returned_at')
                        ->where('due_date', '<', Carbon::today());
                } else {
                    $query->where('status', $request->status);
                }
            }

            // Filter by borrower type
            if ($request->filled('borrower_type') && $request->borrower_type !== 'all') {
                $query->where('borrower_type', $request->borrower_type);
            }

            // Filter by date range
            if ($request->filled('date_from')) {
                $query->where('borrowed_at', '>=', $request->date_from);
            }
            if ($request->filled('date_to')) {
                $query->where('borrowed_at', '<=', $request->date_to . ' 23:59:59');
            }

            return DataTables::of($query)
                ->addIndexColumn()
                ->filter(function ($query) use ($request) {
                    if ($request->has('search') && !empty($request->search['value'])) {
                        $search = $request->search['value'];
                        $query->where(function ($q) use ($search) {
                            $q->whereHas('copy', function ($copyQuery) use ($search) {
                                $copyQuery->where('barcode', 'like', "%{$search}%")
                                    ->orWhereHas('item', function ($itemQuery) use ($search) {
                                        $itemQuery->where('title', 'like', "%{$search}%");
                                    });
                            });
                            // Can't easily search borrower name in polymorphic relation
                            // Would need to add separate searches for each borrower type
                        });
                    }
                })
                ->editColumn('borrowed_at', function ($loan) {
                    return $loan->borrowed_at ? $loan->borrowed_at->format('Y-m-d H:i') : '-';
                })
                ->editColumn('due_date', function ($loan) {
                    return $loan->due_date ? Carbon::parse($loan->due_date)->format('Y-m-d') : '-';
                })
                ->editColumn('returned_at', function ($loan) {
                    return $loan->returned_at ? $loan->returned_at->format('Y-m-d H:i') : '-';
                })
                ->addColumn('barcode', function ($loan) {
                    return $loan->copy ? e($loan->copy->barcode) : 'N/A';
                })
                ->addColumn('book_title', function ($loan) {
                    if ($loan->copy && $loan->copy->item) {
                        return e($loan->copy->item->title);
                    }
                    return '<span class="text-muted">N/A</span>';
                })
                ->addColumn('borrower_info', function ($loan) {
                    $type = ucfirst($loan->borrower_type);
                    $name = e($loan->borrower_name);
                    $identifier = $loan->borrower_identifier ? '<br><small class="text-muted">' . e($loan->borrower_identifier) . '</small>' : '';
                    return '<strong>' . $type . ':</strong> ' . $name . $identifier;
                })
                ->addColumn('status_badge', function ($loan) {
                    $status = $loan->computed_status;
                    $badges = [
                        'requested' => 'warning',
                        'borrowed' => 'primary',
                        'returned' => 'success',
                        'overdue' => 'danger',
                        'cancelled' => 'secondary'
                    ];
                    $color = $badges[$status] ?? 'secondary';
                    $text = ucfirst($status);
                    
                    if ($status === 'overdue') {
                        $daysOverdue = (int) $loan->days_overdue;
                        $text .= ' (' . $daysOverdue . ' ' . ($daysOverdue === 1 ? 'day' : 'days') . ')';
                    }
                    
                    return '<span class="badge bg-' . $color . '">' . $text . '</span>';
                })
                ->addColumn('processed_by', function ($loan) {
                    if ($loan->processedByStaff) {
                        return e($loan->processedByStaff->khmer_name ?? $loan->processedByStaff->english_name ?? 'Staff');
                    }
                    return '<span class="text-muted">-</span>';
                })
                ->addColumn('received_by', function ($loan) {
                    if ($loan->receivedByStaff) {
                        return e($loan->receivedByStaff->khmer_name ?? $loan->receivedByStaff->english_name ?? 'Staff');
                    }
                    return '<span class="text-muted">-</span>';
                })
                ->addColumn('actions', function ($loan) {
                    $actions = '<div class="btn-group btn-group-sm" role="group">';
                    
                    // View
                    $actions .= '<button type="button" class="btn btn-info" onclick="viewLoan(' . $loan->id . ')" title="View">
                                    <i class="fas fa-eye"></i>
                                </button>';
                    
                    // Approve/Confirm (only if requested status)
                    if ($loan->status === 'requested' && $this->canWrite()) {
                        $actions .= '<button type="button" class="btn btn-success" onclick="approveLoan(' . $loan->id . ')" title="Approve Loan">
                                        <i class="fas fa-check-circle"></i>
                                    </button>';
                    }
                    
                    // Return (only if borrowed)
                    if ($loan->status === 'borrowed' && !$loan->returned_at && $this->canReturn()) {
                        $actions .= '<button type="button" class="btn btn-success" onclick="openReturnModal(' . $loan->id . ')" title="Return Book">
                                        <i class="fas fa-check"></i>
                                    </button>';
                    }
                    
                    // Edit (due date/note)
                    if ($this->canWrite() && $loan->status === 'borrowed') {
                        $actions .= '<button type="button" class="btn btn-primary" onclick="openEditModal(' . $loan->id . ')" title="Edit">
                                        <i class="fas fa-edit"></i>
                                    </button>';
                    }
                    
                    // Delete (admin only)
                    if ($this->canDelete()) {
                        $actions .= '<button type="button" class="btn btn-danger" onclick="deleteLoan(' . $loan->id . ')" title="Delete">
                                        <i class="fas fa-trash"></i>
                                    </button>';
                    }
                    
                    $actions .= '</div>';
                    return $actions;
                })
                ->rawColumns(['book_title', 'borrower_info', 'status_badge', 'processed_by', 'received_by', 'actions'])
                ->make(true);
        }

        // Get current staff and all staff list for form
        $authStaff = Staff::where('user_id', Auth::id())->first();
        $staffList = Staff::where('is_active', true)
            ->select('id', 'khmer_name', 'english_name', 'staff_code')
            ->orderBy('khmer_name')
            ->get();

        return view('admin.library.loans.index', compact('authStaff', 'staffList'));
    }

    /**
     * Get dashboard statistics (JSON)
     */
    public function stats()
    {
        if (!$this->canRead()) {
            return response()->json(['success' => false, 'message' => 'Unauthorized.'], 403);
        }

        try {
            $today = Carbon::today();

            // Borrowed Today: loans created today that are still active
            $borrowedToday = LibraryLoan::whereDate('borrowed_at', $today)
                ->whereNull('returned_at')
                ->whereIn('status', ['borrowed', 'overdue'])
                ->count();

            // Returned Today: loans returned today
            $returnedToday = LibraryLoan::whereDate('returned_at', $today)
                ->count();

            // Currently Borrowed (Active): all unreturned loans
            $borrowedActive = LibraryLoan::whereNull('returned_at')
                ->whereIn('status', ['borrowed', 'overdue'])
                ->count();

            // Overdue Now: unreturned loans past due date
            $overdueActive = LibraryLoan::whereNull('returned_at')
                ->where('due_date', '<', $today)
                ->whereIn('status', ['borrowed', 'overdue'])
                ->count();

            // Pending Requests (status = 'requested')
            $pendingRequests = LibraryLoan::where('status', 'requested')
                ->count();

            // Overdue Loans (already calculated above, but separate for clarity)
            $overdueCount = $overdueActive;

            return response()->json([
                'success' => true,
                'data' => [
                    'borrowed_today' => $borrowedToday,
                    'returned_today' => $returnedToday,
                    'borrowed_active' => $borrowedActive,
                    'overdue_active' => $overdueActive,
                    'pending_requests' => $pendingRequests,
                    'overdue_count' => $overdueCount,
                ]
            ]);
        } catch (\Exception $e) {
            Log::error('Error fetching loan stats: ' . $e->getMessage());
            
            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch statistics.'
            ], 500);
        }
    }

    /**
     * Get trend statistics for charts (JSON)
     */
    public function trendStats(Request $request)
    {
        if (!$this->canRead()) {
            return response()->json(['success' => false, 'message' => 'Unauthorized.'], 403);
        }

        try {
            $period = $request->get('period', 'week'); // week or month
            $borrowerType = $request->get('borrower_type', null);

            $labels = [];
            $borrowed = [];
            $returned = [];
            $overdue = [];

            if ($period === 'week') {
                // Last 8 weeks
                for ($i = 7; $i >= 0; $i--) {
                    $startOfWeek = Carbon::now()->subWeeks($i)->startOfWeek();
                    $endOfWeek = Carbon::now()->subWeeks($i)->endOfWeek();
                    
                    $weekLabel = $startOfWeek->format('Y-\WW'); // e.g., "2025-W01"
                    $labels[] = $weekLabel;

                    // Borrowed in this week
                    $borrowedQuery = LibraryLoan::whereBetween('borrowed_at', [$startOfWeek, $endOfWeek]);
                    if ($borrowerType) {
                        $borrowedQuery->where('borrower_type', $borrowerType);
                    }
                    $borrowed[] = $borrowedQuery->count();

                    // Returned in this week
                    $returnedQuery = LibraryLoan::whereBetween('returned_at', [$startOfWeek, $endOfWeek]);
                    if ($borrowerType) {
                        $returnedQuery->where('borrower_type', $borrowerType);
                    }
                    $returned[] = $returnedQuery->count();

                    // Overdue at end of this week
                    $overdueQuery = LibraryLoan::whereNull('returned_at')
                        ->where('due_date', '<', $endOfWeek);
                    if ($borrowerType) {
                        $overdueQuery->where('borrower_type', $borrowerType);
                    }
                    $overdue[] = $overdueQuery->count();
                }
            } else {
                // Last 12 months
                for ($i = 11; $i >= 0; $i--) {
                    $startOfMonth = Carbon::now()->subMonths($i)->startOfMonth();
                    $endOfMonth = Carbon::now()->subMonths($i)->endOfMonth();
                    
                    $monthLabel = $startOfMonth->format('Y-m'); // e.g., "2025-01"
                    $labels[] = $monthLabel;

                    // Borrowed in this month
                    $borrowedQuery = LibraryLoan::whereBetween('borrowed_at', [$startOfMonth, $endOfMonth]);
                    if ($borrowerType) {
                        $borrowedQuery->where('borrower_type', $borrowerType);
                    }
                    $borrowed[] = $borrowedQuery->count();

                    // Returned in this month
                    $returnedQuery = LibraryLoan::whereBetween('returned_at', [$startOfMonth, $endOfMonth]);
                    if ($borrowerType) {
                        $returnedQuery->where('borrower_type', $borrowerType);
                    }
                    $returned[] = $returnedQuery->count();

                    // Overdue at end of this month
                    $overdueQuery = LibraryLoan::whereNull('returned_at')
                        ->where('due_date', '<', $endOfMonth);
                    if ($borrowerType) {
                        $overdueQuery->where('borrower_type', $borrowerType);
                    }
                    $overdue[] = $overdueQuery->count();
                }
            }

            return response()->json([
                'success' => true,
                'data' => [
                    'labels' => $labels,
                    'borrowed' => $borrowed,
                    'returned' => $returned,
                    'overdue' => $overdue,
                ]
            ]);
        } catch (\Exception $e) {
            Log::error('Error fetching loan trends: ' . $e->getMessage());
            
            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch trend data.'
            ], 500);
        }
    }

    /**
     * Trigger loan notifications manually (admin/manager/staff)
     */
    public function triggerNotifications(Request $request)
    {
        if (!$this->canWrite()) {
            return response()->json(['success' => false, 'message' => 'Unauthorized.'], 403);
        }

        try {
            // Run the notification command in the background
            Artisan::call('library:notify-due-dates', [
                '--due-soon-days' => 3
            ]);

            $output = Artisan::output();
            
            // Parse output for counts
            preg_match('/Sent (\d+) due-soon and (\d+) overdue/', $output, $matches);
            $dueSoon = $matches[1] ?? 0;
            $overdue = $matches[2] ?? 0;
            
            return response()->json([
                'success' => true,
                'message' => "Notifications sent: {$dueSoon} due-soon, {$overdue} overdue.",
                'data' => [
                    'due_soon' => $dueSoon,
                    'overdue' => $overdue,
                    'output' => $output
                ]
            ]);
        } catch (\Exception $e) {
            Log::error('Error triggering notifications: ' . $e->getMessage());
            
            return response()->json([
                'success' => false,
                'message' => 'Failed to send notifications.'
            ], 500);
        }
    }

    /**
     * Search library items for borrowing (JSON)
     */
    public function searchBooks(Request $request)
    {
        if (!$this->canRead()) {
            return response()->json(['success' => false, 'message' => 'Unauthorized.'], 403);
        }

        try {
            $query = $request->get('q', '');
            
            if (strlen($query) < 2) {
                return response()->json([
                    'success' => true,
                    'results' => []
                ]);
            }

            // Search library items by multiple fields
            $items = \App\Models\LibraryItem::where('is_active', true)
                ->where(function($q) use ($query) {
                    $q->where('title', 'like', "%{$query}%")
                      ->orWhere('isbn', 'like', "%{$query}%")
                      ->orWhere('edition', 'like', "%{$query}%")
                      ->orWhere('published_year', 'like', "%{$query}%")
                      ->orWhere('language', 'like', "%{$query}%");
                })
                ->with(['copies' => function($query) {
                    // Get ALL active copies, not just available ones
                    $query->where('is_active', true)
                          ->select('id', 'library_item_id', 'barcode', 'status', 'condition');
                }])
                ->limit(50) // Increased from 20 to 50
                ->get(['id', 'title', 'isbn', 'edition', 'published_year', 'language']);

            $results = [];
            foreach ($items as $item) {
                // Count available copies specifically
                $availableCopies = $item->copies->where('status', 'available')->count();
                $totalCopies = $item->copies->count();
                
                // Show the book even if no available copies (user can see status)
                $statusText = $availableCopies > 0 
                    ? $availableCopies . ' available' 
                    : ($totalCopies > 0 ? 'All on loan' : 'No copies');
                
                $results[] = [
                    'id' => $item->id,
                    'text' => $item->title . 
                             ($item->isbn ? ' | ISBN: ' . $item->isbn : '') .
                             ($item->edition ? ' | Ed: ' . $item->edition : '') .
                             ' | ' . $statusText,
                    'title' => $item->title,
                    'isbn' => $item->isbn,
                    'edition' => $item->edition,
                    'published_year' => $item->published_year,
                    'language' => $item->language,
                    'available_copies' => $availableCopies,
                    'total_copies' => $totalCopies,
                    'copies' => $item->copies->map(function($copy) {
                        return [
                            'id' => $copy->id,
                            'barcode' => $copy->barcode,
                            'status' => $copy->status,
                            'condition' => $copy->condition
                        ];
                    })
                ];
            }

            return response()->json([
                'success' => true,
                'results' => $results
            ]);
        } catch (\Exception $e) {
            Log::error('Error searching books: ' . $e->getMessage());
            
            return response()->json([
                'success' => false,
                'message' => 'Failed to search books.'
            ], 500);
        }
    }

    /**
     * Show single loan (JSON)
     */
    public function show($id)
    {
        if (!$this->canRead()) {
            return response()->json(['success' => false, 'message' => 'Unauthorized.'], 403);
        }

        try {
            $loan = LibraryLoan::with(['copy.item', 'copy.shelf', 'processedByStaff', 'receivedByStaff'])->findOrFail($id);
            
            return response()->json([
                'success' => true,
                'data' => [
                    'id' => $loan->id,
                    'barcode' => $loan->copy->barcode ?? 'N/A',
                    'book_title' => $loan->copy->item->title ?? 'N/A',
                    'borrower_type' => $loan->borrower_type,
                    'borrower_name' => $loan->borrower_name,
                    'borrower_identifier' => $loan->borrower_identifier,
                    'borrowed_at' => $loan->borrowed_at ? $loan->borrowed_at->format('Y-m-d H:i') : null,
                    'due_date' => $loan->due_date ? Carbon::parse($loan->due_date)->format('Y-m-d') : null,
                    'returned_at' => $loan->returned_at ? $loan->returned_at->format('Y-m-d H:i') : null,
                    'status' => $loan->computed_status,
                    'is_overdue' => $loan->is_overdue,
                    'days_overdue' => $loan->days_overdue,
                    'note' => $loan->note,
                    'processed_by' => $loan->processedByStaff ? ($loan->processedByStaff->khmer_name ?? $loan->processedByStaff->english_name) : null,
                    'received_by' => $loan->receivedByStaff ? ($loan->receivedByStaff->khmer_name ?? $loan->receivedByStaff->english_name) : null,
                ]
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Loan not found.'
            ], 404);
        }
    }

    /**
     * Store (Borrow book)
     */
    public function store(Request $request)
    {
        if (!$this->canWrite()) {
            return response()->json(['success' => false, 'message' => 'Unauthorized.'], 403);
        }

        $validated = $request->validate([
            'borrower_type' => 'required|in:student,teacher,staff,guest',
            'borrower_id' => 'required|integer',
            'barcode' => 'required|string|max:100',
            'borrowed_at' => 'nullable|date',
            'due_date' => 'required|date|after_or_equal:today',
            'note' => 'nullable|string|max:1000',
            'processed_by_staff_id' => 'nullable|exists:staff,id' // Optional override for admin/manager
        ]);

        try {
            DB::beginTransaction();

            // Validate borrower exists
            $borrowerExists = $this->validateBorrower($validated['borrower_type'], $validated['borrower_id']);
            if (!$borrowerExists) {
                return response()->json([
                    'success' => false,
                    'message' => 'Borrower not found.'
                ], 422);
            }

            // Find copy by barcode
            $copy = LibraryCopy::where('barcode', $validated['barcode'])
                ->where('is_active', true)
                ->first();

            if (!$copy) {
                return response()->json([
                    'success' => false,
                    'message' => 'Copy not found or inactive. Barcode: ' . $validated['barcode']
                ], 422);
            }

            // Check copy status
            if (in_array($copy->status, ['on_loan', 'lost', 'damaged', 'withdrawn'])) {
                return response()->json([
                    'success' => false,
                    'message' => 'Copy cannot be borrowed. Current status: ' . $copy->status
                ], 422);
            }

            // Create loan
            $loan = LibraryLoan::create([
                'borrower_type' => $validated['borrower_type'],
                'borrower_id' => $validated['borrower_id'],
                'library_copy_id' => $copy->id,
                'borrowed_at' => $validated['borrowed_at'] ?? now(),
                'due_date' => $validated['due_date'],
                'returned_at' => null,
                'processed_by_staff_id' => $validated['processed_by_staff_id'] ?? $this->getCurrentStaffId(),
                'received_by_staff_id' => null,
                'status' => 'borrowed',
                'note' => $validated['note'] ?? null
            ]);

            // Update copy status
            $copy->update([
                'status' => 'on_loan',
                'updated_by' => Auth::id()
            ]);

            // Log activity
            $this->logActivity(
                "Book borrowed: {$copy->item->title} (Barcode: {$copy->barcode}) by {$validated['borrower_type']} #{$validated['borrower_id']}",
                $loan,
                [
                    'borrower_type' => $validated['borrower_type'],
                    'borrower_id' => $validated['borrower_id'],
                    'barcode' => $copy->barcode,
                    'book_title' => $copy->item->title,
                    'due_date' => $validated['due_date'],
                    'processed_by_staff_id' => $loan->processed_by_staff_id
                ],
                'library_loans'
            );

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Book borrowed successfully.',
                'data' => $loan
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error creating loan: ' . $e->getMessage());
            
            return response()->json([
                'success' => false,
                'message' => 'Failed to borrow book. Please try again.'
            ], 500);
        }
    }

    /**
     * Return book
     */
    public function returnCopy(Request $request, $id)
    {
        if (!$this->canReturn()) {
            return response()->json(['success' => false, 'message' => 'Unauthorized.'], 403);
        }

        $validated = $request->validate([
            'return_note' => 'nullable|string|max:500',
            'received_by_staff_id' => 'nullable|exists:staff,id' // Optional override for admin/manager
        ]);

        try {
            DB::beginTransaction();

            $loan = LibraryLoan::findOrFail($id);

            // Validate loan can be returned
            if ($loan->status !== 'borrowed' || $loan->returned_at) {
                return response()->json([
                    'success' => false,
                    'message' => 'Loan has already been returned or is not in borrowed status.'
                ], 422);
            }

            // Update loan
            $loan->update([
                'returned_at' => now(),
                'status' => 'returned',
                'received_by_staff_id' => $validated['received_by_staff_id'] ?? $this->getCurrentStaffId(),
                'note' => $validated['return_note'] ? ($loan->note ? $loan->note . ' | RETURN: ' . $validated['return_note'] : 'RETURN: ' . $validated['return_note']) : $loan->note
            ]);

            // Update copy status back to available
            $copy = $loan->copy;
            if ($copy) {
                $copy->update([
                    'status' => 'available', // Could check for reservations here
                    'updated_by' => Auth::id()
                ]);
            }

            // Log activity
            $this->logActivity(
                "Book returned: {$copy->item->title} (Barcode: {$copy->barcode}) by {$loan->borrower_type} #{$loan->borrower_id}",
                $loan,
                [
                    'borrower_type' => $loan->borrower_type,
                    'borrower_id' => $loan->borrower_id,
                    'barcode' => $copy->barcode,
                    'book_title' => $copy->item->title,
                    'returned_at' => now()->toDateTimeString(),
                    'received_by_staff_id' => $loan->received_by_staff_id,
                    'return_note' => $validated['return_note'] ?? null
                ],
                'library_loans'
            );

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Book returned successfully.'
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error returning book: ' . $e->getMessage());
            
            return response()->json([
                'success' => false,
                'message' => 'Failed to return book. Please try again.'
            ], 500);
        }
    }

    /**
     * Update loan (due_date, note only)
     */
    public function update(Request $request, $id)
    {
        if (!$this->canWrite()) {
            return response()->json(['success' => false, 'message' => 'Unauthorized.'], 403);
        }

        $validated = $request->validate([
            'due_date' => 'required|date',
            'note' => 'nullable|string|max:1000'
        ]);

        try {
            $loan = LibraryLoan::findOrFail($id);

            // Only allow updating borrowed loans
            if ($loan->status !== 'borrowed') {
                return response()->json([
                    'success' => false,
                    'message' => 'Can only update borrowed loans.'
                ], 422);
            }

            // Store old values for logging
            $oldAttributes = [
                'due_date' => $loan->due_date,
                'note' => $loan->note
            ];

            $loan->update($validated);

            // Log activity with old and new values
            $this->logActivityUpdate(
                "Loan updated: Loan #{$loan->id} (Barcode: {$loan->copy->barcode})",
                $loan,
                $oldAttributes,
                $validated,
                'library_loans'
            );

            return response()->json([
                'success' => true,
                'message' => 'Loan updated successfully.',
                'data' => $loan
            ]);
        } catch (\Exception $e) {
            Log::error('Error updating loan: ' . $e->getMessage());
            
            return response()->json([
                'success' => false,
                'message' => 'Failed to update loan.'
            ], 500);
        }
    }

    /**
     * Approve loan request (change status from 'requested' to 'borrowed')
     */
    public function approve(Request $request, $id)
    {
        if (!$this->canWrite()) {
            return response()->json(['success' => false, 'message' => 'Unauthorized.'], 403);
        }

        try {
            DB::beginTransaction();

            $loan = LibraryLoan::with('copy.item')->findOrFail($id);

            // Validate loan can be approved
            if ($loan->status !== 'requested') {
                return response()->json([
                    'success' => false,
                    'message' => 'Only loan requests with status "requested" can be approved.'
                ], 422);
            }

            // Check if copy is still available
            $copy = $loan->copy;
            if (!$copy) {
                return response()->json([
                    'success' => false,
                    'message' => 'Copy not found for this loan.'
                ], 422);
            }

            if ($copy->status !== 'available') {
                return response()->json([
                    'success' => false,
                    'message' => 'Copy is not available. Current status: ' . $copy->status
                ], 422);
            }

            // Update loan status to 'borrowed' and set processed_by_staff_id
            $loan->update([
                'status' => 'borrowed',
                'processed_by_staff_id' => $this->getCurrentStaffId(),
                'borrowed_at' => $loan->borrowed_at ?? now(), // Use existing or set to now
            ]);

            // Update copy status to 'on_loan'
            $copy->update([
                'status' => 'on_loan',
                'updated_by' => Auth::id()
            ]);

            // Log activity
            $this->logActivity(
                "Loan approved: {$copy->item->title} (Barcode: {$copy->barcode}) by {$loan->borrower_type} #{$loan->borrower_id}",
                $loan,
                [
                    'borrower_type' => $loan->borrower_type,
                    'borrower_id' => $loan->borrower_id,
                    'barcode' => $copy->barcode,
                    'book_title' => $copy->item->title,
                    'due_date' => $loan->due_date,
                    'processed_by_staff_id' => $loan->processed_by_staff_id,
                    'approved_at' => now()->toDateTimeString()
                ],
                'library_loans'
            );

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Loan request approved successfully. Book is now borrowed.',
                'data' => $loan
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error approving loan: ' . $e->getMessage());
            
            return response()->json([
                'success' => false,
                'message' => 'Failed to approve loan. Please try again.'
            ], 500);
        }
    }

    /**
     * Delete loan (admin only, for test data)
     */
    public function destroy($id)
    {
        if (!$this->canDelete()) {
            return response()->json(['success' => false, 'message' => 'Unauthorized. Admin only.'], 403);
        }

        try {
            DB::beginTransaction();

            $loan = LibraryLoan::with('copy.item')->findOrFail($id);

            // Store loan info for logging before deletion
            $loanInfo = [
                'id' => $loan->id,
                'borrower_type' => $loan->borrower_type,
                'borrower_id' => $loan->borrower_id,
                'barcode' => $loan->copy ? $loan->copy->barcode : null,
                'book_title' => $loan->copy && $loan->copy->item ? $loan->copy->item->title : null,
                'borrowed_at' => $loan->borrowed_at,
                'due_date' => $loan->due_date,
                'returned_at' => $loan->returned_at,
                'status' => $loan->status
            ];

            // If borrowed, restore copy status
            if ($loan->status === 'borrowed' && !$loan->returned_at) {
                $copy = $loan->copy;
                if ($copy) {
                    $copy->update([
                        'status' => 'available',
                        'updated_by' => Auth::id()
                    ]);
                }
            }

            $loan->delete();

            // Log activity (after deletion, so we can't use $loan as subject)
            $this->logActivity(
                "Loan deleted: Loan #{$loanInfo['id']}" . ($loanInfo['barcode'] ? " (Barcode: {$loanInfo['barcode']})" : ""),
                null, // No subject since it's deleted
                [
                    'deleted_loan' => $loanInfo,
                    'deleted_by' => Auth::id(),
                    'deleted_at' => now()->toDateTimeString()
                ],
                'library_loans'
            );

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Loan deleted successfully.'
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error deleting loan: ' . $e->getMessage());
            
            return response()->json([
                'success' => false,
                'message' => 'Failed to delete loan.'
            ], 500);
        }
    }

    /**
     * Find copy by barcode (helper)
     */
    public function findCopyByBarcode($barcode)
    {
        if (!$this->canRead()) {
            return response()->json(['success' => false, 'message' => 'Unauthorized.'], 403);
        }

        try {
            $copy = LibraryCopy::with(['item', 'shelf'])
                ->where('barcode', $barcode)
                ->where('is_active', true)
                ->firstOrFail();

            return response()->json([
                'success' => true,
                'data' => [
                    'id' => $copy->id,
                    'barcode' => $copy->barcode,
                    'status' => $copy->status,
                    'condition' => $copy->condition,
                    'shelf_id' => $copy->shelf_id,
                    'shelf' => $copy->shelf ? ($copy->shelf->code ?? $copy->shelf->location) : null,
                    'item' => [
                        'id' => $copy->item->id,
                        'title' => $copy->item->title,
                        'isbn' => $copy->item->isbn,
                        'edition' => $copy->item->edition,
                    ],
                    'can_borrow' => !in_array($copy->status, ['on_loan', 'lost', 'damaged', 'withdrawn'])
                ]
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Copy not found with barcode: ' . $barcode
            ], 404);
        }
    }

    /**
     * Search borrowers (helper)
     */
    public function searchBorrowers(Request $request)
    {
        if (!$this->canRead()) {
            return response()->json(['success' => false, 'message' => 'Unauthorized.'], 403);
        }

        // Accept both 'type' and 'borrower_type' parameters for flexibility
        $type = $request->get('type') ?? $request->get('borrower_type');
        $query = $request->get('q', '');

        // If no type specified, return empty results
        if (!$type) {
            return response()->json([]);
        }

        $results = [];

        try {
            switch ($type) {
                case 'student':
                    $students = Student::where('is_active', true);
                    
                    // If query provided, filter results
                    if (!empty($query)) {
                        $students->where(function ($q) use ($query) {
                            $q->where('khmer_name', 'like', "%{$query}%")
                                ->orWhere('english_name', 'like', "%{$query}%")
                                ->orWhere('code', 'like', "%{$query}%")
                                ->orWhere('sex', 'like', "%{$query}%");
                        });
                    }
                    
                    $results = $students->limit(100)
                        ->orderBy('english_name')
                        ->get()
                        ->map(function ($student) {
                            $displayName = $student->english_name ?? $student->khmer_name;
                            if ($student->khmer_name && $student->english_name) {
                                $displayName = $student->khmer_name . ' / ' . $student->english_name;
                            }
                            return [
                                'id' => $student->id,
                                'user_id' => $student->user_id, // For polymorphic relation
                                'text' => $displayName . ' (' . ($student->code ?? 'N/A') . ') - ' . ($student->sex ?? ''),
                                'code' => $student->code,
                                'sex' => $student->sex
                            ];
                        });
                    break;

                case 'teacher':
                    $teachers = Teacher::where('is_active', true);
                    
                    if (!empty($query)) {
                        $teachers->where(function ($q) use ($query) {
                            $q->where('khmer_name', 'like', "%{$query}%")
                                ->orWhere('english_name', 'like', "%{$query}%")
                                ->orWhere('teacher_code', 'like', "%{$query}%")
                                ->orWhere('sex', 'like', "%{$query}%");
                        });
                    }
                    
                    $results = $teachers->limit(100)
                        ->orderBy('khmer_name')
                        ->get()
                        ->map(function ($teacher) {
                            $displayName = $teacher->khmer_name ?? $teacher->english_name;
                            if ($teacher->khmer_name && $teacher->english_name) {
                                $displayName = $teacher->khmer_name . ' / ' . $teacher->english_name;
                            }
                            return [
                                'id' => $teacher->id,
                                'user_id' => $teacher->user_id, // For polymorphic relation
                                'text' => $displayName . ' (' . ($teacher->teacher_code ?? 'N/A') . ') - ' . ($teacher->sex ?? ''),
                                'code' => $teacher->teacher_code,
                                'sex' => $teacher->sex
                            ];
                        });
                    break;

                case 'staff':
                    $staff = Staff::where('is_active', true);
                    
                    if (!empty($query)) {
                        $staff->where(function ($q) use ($query) {
                            $q->where('khmer_name', 'like', "%{$query}%")
                                ->orWhere('english_name', 'like', "%{$query}%")
                                ->orWhere('staff_code', 'like', "%{$query}%")
                                ->orWhere('sex', 'like', "%{$query}%");
                        });
                    }
                    
                    $results = $staff->limit(100)
                        ->orderBy('khmer_name')
                        ->get()
                        ->map(function ($staff) {
                            $displayName = $staff->khmer_name ?? $staff->english_name;
                            if ($staff->khmer_name && $staff->english_name) {
                                $displayName = $staff->khmer_name . ' / ' . $staff->english_name;
                            }
                            return [
                                'id' => $staff->id,
                                'user_id' => $staff->user_id, // For polymorphic relation
                                'text' => $displayName . ' (' . ($staff->staff_code ?? 'N/A') . ') - ' . ($staff->sex ?? ''),
                                'code' => $staff->staff_code,
                                'sex' => $staff->sex
                            ];
                        });
                    break;

                case 'guest':
                    $guests = LibraryGuest::where('is_active', true);
                    
                    if (!empty($query)) {
                        $guests->where(function ($q) use ($query) {
                            $q->where('full_name', 'like', "%{$query}%")
                                ->orWhere('phone', 'like', "%{$query}%")
                                ->orWhere('id_card_no', 'like', "%{$query}%");
                        });
                    }
                    
                    $results = $guests->limit(100)
                        ->orderBy('full_name')
                        ->get()
                        ->map(function ($guest) {
                            return [
                                'id' => $guest->id,
                                'text' => $guest->full_name . ' (' . ($guest->phone ?? 'N/A') . ')',
                                'phone' => $guest->phone
                            ];
                        });
                    break;

                default:
                    return response()->json([]);
            }

            // Return in Select2 format
            return response()->json($results);
        } catch (\Exception $e) {
            Log::error('Error searching borrowers: ' . $e->getMessage());
            
            return response()->json([
                'success' => false,
                'message' => 'Failed to search borrowers.'
            ], 500);
        }
    }

    /**
     * Validate borrower exists
     */
    private function validateBorrower($type, $id)
    {
        switch ($type) {
            case 'student':
                return Student::where('id', $id)->where('is_active', true)->exists();
            case 'teacher':
                return Teacher::where('id', $id)->where('is_active', true)->exists();
            case 'staff':
                return Staff::where('id', $id)->where('is_active', true)->exists();
            case 'guest':
                return LibraryGuest::where('id', $id)->where('is_active', true)->exists();
            default:
                return false;
        }
    }
}

