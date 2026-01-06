<?php

namespace App\Http\Controllers\Admin\Library\Reports;

use App\Http\Controllers\Controller;
use App\Models\LibraryItem;
use App\Models\LibraryCategory;
use App\Models\LibraryPublisher;
use App\Models\LibraryAuthor;
use App\Models\LibraryCopy;
use App\Models\LibraryReservation;
use App\Models\LibraryShelf;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Auth;

class LibraryBooksReportController extends Controller
{
    /**
     * Display the books report page
     */
    public function index(Request $request)
    {
        // Load active categories for filter
        $categories = LibraryCategory::where('is_active', true)
            ->orderBy('name')
            ->get(['id', 'name']);
        
        // Load active shelves for filter
        $shelves = LibraryShelf::where('is_active', true)
            ->orderBy('code')
            ->get(['id', 'code', 'location']);
        
        // Load publishers and authors for edit modal
        $publishers = LibraryPublisher::where('is_active', true)
            ->orderBy('name')
            ->get(['id', 'name']);
        
        $authors = LibraryAuthor::where('is_active', true)
            ->orderBy('name')
            ->get(['id', 'name']);

        return view('admin.library.reports.books_report.index', compact('categories', 'shelves', 'publishers', 'authors'));
    }

    /**
     * Get books data for grid (JSON)
     */
    public function data(Request $request)
    {
        $categoryId = $request->get('category_id');
        $shelfId = $request->get('shelf_id');
        $searchQuery = $request->get('q', '');
        $page = (int) $request->get('page', 1);
        $perPage = (int) $request->get('per_page', 12);

        // Base query - only active items
        $query = LibraryItem::where('library_items.is_active', true)
            ->with(['category' => function($q) {
                $q->where('is_active', true);
            }])
            ->with(['activeAuthors' => function($q) {
                $q->where('library_authors.is_active', true);
            }]);

        // Filter by category
        if ($categoryId) {
            $query->where('library_items.category_id', $categoryId);
        }

        // Filter by shelf (through copies)
        if ($shelfId) {
            $query->whereHas('copies', function($q) use ($shelfId) {
                $q->where('library_copies.shelf_id', $shelfId)
                  ->where('library_copies.is_active', true);
            });
        }

        // Search functionality
        if ($searchQuery) {
            $query->where(function ($q) use ($searchQuery) {
                // Search by title
                $q->where('library_items.title', 'LIKE', '%' . $searchQuery . '%')
                    // Search by ISBN
                    ->orWhere('library_items.isbn', 'LIKE', '%' . $searchQuery . '%')
                    // Search by category name
                    ->orWhereHas('category', function ($catQuery) use ($searchQuery) {
                        $catQuery->where('name', 'LIKE', '%' . $searchQuery . '%');
                    })
                    // Search by author name
                    ->orWhereHas('activeAuthors', function ($authorQuery) use ($searchQuery) {
                        $authorQuery->where('name', 'LIKE', '%' . $searchQuery . '%');
                    });
            });
        }

        // Get total count before pagination
        $total = $query->count();

        // Paginate
        $items = $query->orderBy('library_items.title')
            ->skip(($page - 1) * $perPage)
            ->take($perPage)
            ->get();

        // Process items with availability data
        $data = $items->map(function ($item) {
            // Get copy counts using subqueries to avoid N+1
            $totalCopies = LibraryCopy::where('library_item_id', $item->id)
                ->where('is_active', true)
                ->count();

            $availableCopies = LibraryCopy::where('library_item_id', $item->id)
                ->where('is_active', true)
                ->where('status', 'available')
                ->count();

            $borrowedCopies = LibraryCopy::where('library_item_id', $item->id)
                ->where('is_active', true)
                ->where('status', 'borrowed')
                ->count();

            // Determine availability status
            $availabilityStatus = 'No Copies';
            if ($totalCopies > 0) {
                if ($availableCopies > 0) {
                    $availabilityStatus = 'Available';
                } else {
                    $availabilityStatus = 'Borrowed';
                }
            }

            // Get authors string (filter active authors)
            $authors = $item->activeAuthors()
                ->where('library_authors.is_active', true)
                ->get()
                ->pluck('name')
                ->join(', ') ?: 'Unknown Author';

            // Get cover image URL
            $coverUrl = null;
            if ($item->cover_image) {
                if (Storage::disk('public')->exists($item->cover_image)) {
                    $coverUrl = asset('storage/' . $item->cover_image);
                }
            }
            
            // Default placeholder if no cover
            if (!$coverUrl) {
                // Try SVG first, fallback to PNG
                $svgPath = public_path('images/default-book.svg');
                $pngPath = public_path('images/default-book.png');
                if (file_exists($svgPath)) {
                    $coverUrl = asset('images/default-book.svg');
                } elseif (file_exists($pngPath)) {
                    $coverUrl = asset('images/default-book.png');
                } else {
                    // Fallback to a data URI for a simple gray placeholder
                    $coverUrl = 'data:image/svg+xml;base64,' . base64_encode('<svg width="200" height="300" xmlns="http://www.w3.org/2000/svg"><rect width="200" height="300" fill="#e0e0e0"/><text x="100" y="150" text-anchor="middle" fill="#999" font-family="Arial" font-size="14">No Cover</text></svg>');
                }
            }

            // Can reserve: true when available_copies == 0 AND total_copies > 0
            $canReserve = ($availableCopies == 0 && $totalCopies > 0);

            // Get shelf information for copies
            $shelves = LibraryCopy::where('library_item_id', $item->id)
                ->where('is_active', true)
                ->with('shelf:id,code,location')
                ->get()
                ->pluck('shelf')
                ->filter()
                ->unique('id')
                ->map(function($shelf) {
                    return [
                        'id' => $shelf->id,
                        'code' => $shelf->code,
                        'location' => $shelf->location,
                    ];
                })
                ->values();

            // Get shelf codes as string for display
            $shelfCodes = $shelves->pluck('code')->join(', ') ?: 'No Shelf';

            return [
                'id' => $item->id,
                'title' => $item->title,
                'isbn' => $item->isbn ?? '-',
                'category' => $item->category ? $item->category->name : 'Uncategorized',
                'authors' => $authors,
                'published_year' => $item->published_year ?? null,
                'language' => $item->language ?? '-',
                'cover_url' => $coverUrl,
                'total_copies' => $totalCopies,
                'available_copies' => $availableCopies,
                'borrowed_copies' => $borrowedCopies,
                'availability_status' => $availabilityStatus,
                'can_reserve' => $canReserve,
                'shelves' => $shelves,
                'shelf_codes' => $shelfCodes,
            ];
        });

        $lastPage = ceil($total / $perPage);

        return response()->json([
            'success' => true,
            'meta' => [
                'page' => $page,
                'per_page' => $perPage,
                'total' => $total,
                'last_page' => $lastPage,
            ],
            'data' => $data,
        ]);
    }

    /**
     * Reserve a book
     */
    public function reserve(Request $request, LibraryItem $libraryItem)
    {
        // Require authentication
        if (!auth()->check()) {
            return response()->json([
                'success' => false,
                'message' => 'Please login first to reserve a book.',
            ], 401);
        }

        $userId = auth()->id();

        // Check if item is active
        if (!$libraryItem->is_active) {
            return response()->json([
                'success' => false,
                'message' => 'Book not found.',
            ], 404);
        }

        $libraryItemId = $libraryItem->id;

        // Check availability
        $availableCopies = LibraryCopy::where('library_item_id', $libraryItemId)
            ->where('is_active', true)
            ->where('status', 'available')
            ->count();

        if ($availableCopies > 0) {
            return response()->json([
                'success' => false,
                'message' => 'Book is available, please borrow instead.',
            ], 400);
        }

        // Check total copies
        $totalCopies = LibraryCopy::where('library_item_id', $libraryItemId)
            ->where('is_active', true)
            ->count();

        if ($totalCopies == 0) {
            return response()->json([
                'success' => false,
                'message' => 'No copies available for this book.',
            ], 400);
        }

        // Prevent duplicate active reservation
        $existingReservation = LibraryReservation::where('user_id', $userId)
            ->where('library_item_id', $libraryItemId)
            ->whereIn('status', ['pending', 'ready'])
            ->where('is_active', true)
            ->first();

        if ($existingReservation) {
            return response()->json([
                'success' => false,
                'message' => 'You already reserved this book.',
            ], 400);
        }

        // Get next queue number
        $queueNo = LibraryReservation::getNextQueueNumber($libraryItemId);

        // Create reservation
        try {
            $reservation = LibraryReservation::create([
                'user_id' => $userId,
                'library_item_id' => $libraryItemId,
                'status' => 'pending',
                'reserved_at' => now(),
                'queue_no' => $queueNo,
                'assigned_copy_id' => null,
                'note' => $request->get('note'),
                'created_by' => $userId,
                'updated_by' => $userId,
                'is_active' => true,
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Book reserved successfully. Queue number: ' . $queueNo,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to reserve book: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Show QR Code Generator for Books Report
     * 
     * This page allows admin/staff to generate a QR code that links to the books report page.
     * Users can scan the QR code to quickly access the library books browsing page.
     */
    public function showQRGenerator()
    {
        // Check permissions (admin, manager, staff only)
        $user = Auth::user();
        
        if (!$user->hasAnyRole(['admin', 'manager', 'staff'])) {
            abort(403, 'You do not have permission to access QR Code Generator.');
        }

        // Generate the URL that the QR code will point to
        $qrUrl = route('admin.library.books_report.index');

        return view('admin.library.reports.books_report.qr_generator', [
            'qrUrl' => $qrUrl,
            'title' => 'Library Books Report QR Generator',
            'description' => 'Scan this QR code to quickly browse and search library books. Reserve unavailable books and view book details.',
        ]);
    }
}

