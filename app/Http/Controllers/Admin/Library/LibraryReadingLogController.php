<?php

namespace App\Http\Controllers\Admin\Library;

use App\Http\Controllers\Controller;
use App\Models\LibraryReadingLog;
use App\Models\LibraryVisit;
use App\Models\LibraryItem;
use App\Models\LibraryCopy;
use App\Traits\LogsActivity;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Yajra\DataTables\Facades\DataTables;
use Carbon\Carbon;

/**
 * LibraryReadingLogController
 * 
 * Manages in-library reading activity logs.
 * Tracks what books visitors read during their library visits.
 * 
 * Features:
 * - CRUD operations
 * - Start/Stop reading tracking
 * - DataTables with server-side processing
 * - Statistics per visit (unique books count, total minutes)
 * - Role-based permissions
 */
class LibraryReadingLogController extends Controller
{
    use LogsActivity;
    /**
     * Check if user can write (create/edit)
     */
    private function canWrite()
    {
        return Auth::user()->hasAnyRole(['admin', 'manager', 'staff']);
    }

    /**
     * Check if user can delete
     */
    private function canDelete()
    {
        return Auth::user()->hasAnyRole(['admin', 'manager']);
    }

    /**
     * Check if user is admin
     */
    private function isAdmin()
    {
        return Auth::user()->hasRole('admin');
    }

    /**
     * Display reading logs list (DataTables)
     */
    public function index(Request $request)
    {
        // If Ajax request, return DataTables JSON
        if ($request->ajax()) {
            $query = LibraryReadingLog::with([
                'visit.user',
                'visit.guest',
                'item',
                'copy',
                'creator',
                'updater'
            ]);

            // Apply filters
            if ($request->filled('status')) {
                if ($request->status === 'active') {
                    $query->active();
                } elseif ($request->status === 'inactive') {
                    $query->inactive();
                }
            } else {
                // Default: active only
                $query->active();
            }

            // Date range filter (via visit_date)
            if ($request->filled('date_from') && $request->filled('date_to')) {
                $query->dateRange($request->date_from, $request->date_to);
            }

            // Session filter
            if ($request->filled('session') && $request->session !== 'all') {
                $query->bySession($request->session);
            }

            // Visitor type filter
            if ($request->filled('visitor_type') && $request->visitor_type !== 'all') {
                $query->byVisitorType($request->visitor_type);
            }

            // Item filter
            if ($request->filled('library_item_id')) {
                $query->byItem($request->library_item_id);
            }

            // Running status filter
            if ($request->filled('running_status')) {
                if ($request->running_status === 'running') {
                    $query->running();
                } elseif ($request->running_status === 'completed') {
                    $query->completed();
                }
            }

            return DataTables::of($query)
                ->addIndexColumn()
                ->addColumn('visitor', function ($row) {
                    if ($row->visit) {
                        if ($row->visit->user) {
                            return $row->visit->user->name ?? 'N/A';
                        } elseif ($row->visit->guest) {
                            return $row->visit->guest->full_name ?? 'N/A';
                        }
                    }
                    return 'N/A';
                })
                ->addColumn('visit_date', function ($row) {
                    if ($row->visit && $row->visit->visit_date) {
                        return $row->visit->visit_date->format('Y-m-d');
                    }
                    return 'N/A';
                })
                ->addColumn('session', function ($row) {
                    if (!$row->visit || !$row->visit->session) {
                        return 'N/A';
                    }
                    
                    $session = $row->visit->session;
                    $badges = [
                        'morning' => '<span class="badge bg-info">Morning</span>',
                        'afternoon' => '<span class="badge bg-warning">Afternoon</span>',
                        'evening' => '<span class="badge bg-primary">Evening</span>',
                    ];
                    
                    return $badges[$session] ?? ucfirst($session);
                })
                ->addColumn('book_title', function ($row) {
                    $title = $row->item?->title ?? 'N/A';
                    $barcode = $row->copy?->barcode;
                    
                    if ($barcode) {
                        return $title . '<br><small class="text-muted">Copy: ' . $barcode . '</small>';
                    }
                    
                    return $title;
                })
                ->addColumn('start_time', function ($row) {
                    return $row->start_time ? $row->start_time->format('Y-m-d H:i') : '-';
                })
                ->addColumn('end_time', function ($row) {
                    return $row->end_time ? $row->end_time->format('Y-m-d H:i') : '-';
                })
                ->addColumn('duration', function ($row) {
                    if ($row->is_running) {
                        return '<span class="badge bg-success"><i class="fas fa-play"></i> Running</span>';
                    }
                    
                    $minutes = $row->minutes_read;
                    if ($minutes === null) {
                        return '<span class="badge bg-secondary">Not Started</span>';
                    }
                    
                    return '<span class="badge bg-info">' . $row->duration . '</span>';
                })
                ->addColumn('books_in_visit', function ($row) {
                    $count = LibraryReadingLog::countBooksReadInVisit($row->visit_id);
                    return '<span class="badge bg-primary">' . $count . ' book(s)</span>';
                })
                ->addColumn('status', function ($row) {
                    if ($row->is_active) {
                        return '<span class="badge bg-success">Active</span>';
                    }
                    return '<span class="badge bg-danger">Inactive</span>';
                })
                ->addColumn('actions', function ($row) {
                    $actions = '<div class="btn-group btn-group-sm">';
                    
                    // View button
                    $actions .= '<button type="button" class="btn btn-info btn-sm" onclick="viewLog(' . $row->id . ')" title="View">
                        <i class="fas fa-eye"></i>
                    </button>';
                    
                    // Start button (if not started)
                    if (!$row->start_time && $this->canWrite()) {
                        $actions .= '<button type="button" class="btn btn-success btn-sm" onclick="startReading(' . $row->id . ')" title="Start Reading">
                            <i class="fas fa-play"></i>
                        </button>';
                    }
                    
                    // Stop button (if running)
                    if ($row->is_running && $this->canWrite()) {
                        $actions .= '<button type="button" class="btn btn-warning btn-sm" onclick="stopReading(' . $row->id . ')" title="Stop Reading">
                            <i class="fas fa-stop"></i>
                        </button>';
                    }
                    
                    // Edit button (if can write)
                    if ($this->canWrite()) {
                        $actions .= '<button type="button" class="btn btn-primary btn-sm" onclick="editLog(' . $row->id . ')" title="Edit">
                            <i class="fas fa-edit"></i>
                        </button>';
                    }
                    
                    // Delete button (if can delete)
                    if ($this->canDelete()) {
                        $actions .= '<button type="button" class="btn btn-danger btn-sm" onclick="deleteLog(' . $row->id . ')" title="Delete">
                            <i class="fas fa-trash"></i>
                        </button>';
                    }
                    
                    $actions .= '</div>';
                    
                    return $actions;
                })
                ->rawColumns(['session', 'book_title', 'duration', 'books_in_visit', 'status', 'actions'])
                ->make(true);
        }

        // Return view
        return view('admin.library.reading_logs.index');
    }

    /**
     * Show single reading log details (JSON)
     */
    public function show($id)
    {
        $log = LibraryReadingLog::with([
            'visit.user',
            'visit.guest',
            'item',
            'copy',
            'creator',
            'updater'
        ])->findOrFail($id);

        // Get visitor name
        $visitorName = 'N/A';
        if ($log->visit) {
            if ($log->visit->user) {
                $visitorName = $log->visit->user->name ?? 'N/A';
            } elseif ($log->visit->guest) {
                $visitorName = $log->visit->guest->full_name ?? 'N/A';
            }
        }

        return response()->json([
            'success' => true,
            'data' => [
                'id' => $log->id,
                'visit_id' => $log->visit_id,
                'visitor_name' => $visitorName,
                'visit_date' => $log->visit && $log->visit->visit_date ? $log->visit->visit_date->format('Y-m-d') : 'N/A',
                'session' => $log->visit && $log->visit->session ? ucfirst($log->visit->session) : 'N/A',
                'library_item_id' => $log->library_item_id,
                'book_title' => $log->item?->title ?? 'N/A',
                'copy_id' => $log->copy_id,
                'barcode' => $log->copy?->barcode ?? 'N/A',
                'start_time' => $log->start_time ? $log->start_time->format('Y-m-d H:i:s') : null,
                'end_time' => $log->end_time ? $log->end_time->format('Y-m-d H:i:s') : null,
                'duration' => $log->duration,
                'minutes_read' => $log->minutes_read,
                'is_running' => $log->is_running,
                'reading_type' => $log->reading_type,
                'note' => $log->note,
                'books_in_visit' => LibraryReadingLog::countBooksReadInVisit($log->visit_id),
                'is_active' => $log->is_active,
                'created_by' => $log->creator?->name,
                'updated_by' => $log->updater?->name,
                'created_at' => $log->created_at?->format('Y-m-d H:i:s'),
                'updated_at' => $log->updated_at?->format('Y-m-d H:i:s'),
            ]
        ]);
    }

    /**
     * Store new reading log
     */
    public function store(Request $request)
    {
        if (!$this->canWrite()) {
            return response()->json(['success' => false, 'message' => 'Unauthorized.'], 403);
        }

        $rules = [
            'visit_id' => 'required|exists:library_visits,id',
            'library_item_id' => 'required|exists:library_items,id',
            'copy_id' => 'nullable|exists:library_copies,id',
            'start_time' => 'nullable|date',
            'end_time' => 'nullable|date|after:start_time',
            'reading_type' => 'nullable|in:in_library',
            'note' => 'nullable|string|max:1000',
        ];

        $validated = $request->validate($rules);

        try {
            DB::beginTransaction();

            // Verify visit is active
            $visit = LibraryVisit::find($validated['visit_id']);
            if (!$visit || !$visit->is_active) {
                return response()->json([
                    'success' => false,
                    'message' => 'Selected visit is not active or does not exist.'
                ], 422);
            }

            $data = [
                'visit_id' => $validated['visit_id'],
                'library_item_id' => $validated['library_item_id'],
                'copy_id' => $validated['copy_id'] ?? null,
                'start_time' => $validated['start_time'] ?? null,
                'end_time' => $validated['end_time'] ?? null,
                'reading_type' => $validated['reading_type'] ?? 'in_library',
                'note' => $validated['note'] ?? null,
                'created_by' => Auth::id(),
                'updated_by' => Auth::id(),
                'is_active' => true,
            ];

            $log = LibraryReadingLog::with(['item', 'visit.user', 'visit.guest'])->create($data);
            
            // Get visitor and book info for logging
            $visitorName = 'N/A';
            if ($log->visit) {
                $visitorName = $log->visit->user ? $log->visit->user->name : ($log->visit->guest ? $log->visit->guest->full_name : 'N/A');
            }
            $bookTitle = $log->item ? $log->item->title : 'N/A';

            // Log activity
            $this->logActivity(
                "Reading log created: {$visitorName} reading '{$bookTitle}'",
                $log,
                [
                    'visit_id' => $log->visit_id,
                    'library_item_id' => $log->library_item_id,
                    'copy_id' => $log->copy_id,
                    'book_title' => $bookTitle,
                    'barcode' => $log->copy ? $log->copy->barcode : null,
                    'start_time' => $log->start_time,
                    'end_time' => $log->end_time,
                    'reading_type' => $log->reading_type,
                    'created_by' => Auth::id()
                ],
                'library_reading_logs'
            );

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Reading log created successfully.',
                'data' => $log
            ]);

        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed.',
                'errors' => $e->errors()
            ], 422);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error creating reading log: ' . $e->getMessage());
            
            return response()->json([
                'success' => false,
                'message' => 'Failed to create reading log: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Update reading log
     */
    public function update(Request $request, $id)
    {
        if (!$this->canWrite()) {
            return response()->json(['success' => false, 'message' => 'Unauthorized.'], 403);
        }

        $log = LibraryReadingLog::findOrFail($id);

        $rules = [
            'visit_id' => 'required|exists:library_visits,id',
            'library_item_id' => 'required|exists:library_items,id',
            'copy_id' => 'nullable|exists:library_copies,id',
            'start_time' => 'nullable|date',
            'end_time' => 'nullable|date|after:start_time',
            'reading_type' => 'nullable|in:in_library',
            'note' => 'nullable|string|max:1000',
        ];

        $validated = $request->validate($rules);

        try {
            DB::beginTransaction();

            // Store old values for logging
            $oldAttributes = [
                'visit_id' => $log->visit_id,
                'library_item_id' => $log->library_item_id,
                'copy_id' => $log->copy_id,
                'start_time' => $log->start_time,
                'end_time' => $log->end_time,
                'reading_type' => $log->reading_type,
                'note' => $log->note
            ];

            // Prevent changing visit_id if reading already started (business rule)
            if ($log->start_time && $validated['visit_id'] != $log->visit_id) {
                return response()->json([
                    'success' => false,
                    'message' => 'Cannot change visit after reading has started.'
                ], 422);
            }

            $log->visit_id = $validated['visit_id'];
            $log->library_item_id = $validated['library_item_id'];
            $log->copy_id = $validated['copy_id'] ?? null;
            $log->start_time = $validated['start_time'] ?? $log->start_time;
            $log->end_time = $validated['end_time'] ?? $log->end_time;
            $log->reading_type = $validated['reading_type'] ?? $log->reading_type;
            $log->note = $validated['note'] ?? $log->note;
            $log->updated_by = Auth::id();
            $log->save();

            // Refresh to get updated relationships
            $log->refresh();
            $bookTitle = $log->item ? $log->item->title : 'N/A';

            // Prepare new attributes for logging
            $newAttributes = [
                'visit_id' => $log->visit_id,
                'library_item_id' => $log->library_item_id,
                'copy_id' => $log->copy_id,
                'start_time' => $log->start_time,
                'end_time' => $log->end_time,
                'reading_type' => $log->reading_type,
                'note' => $log->note
            ];

            // Log activity with old and new values
            $this->logActivityUpdate(
                "Reading log updated: Log #{$log->id} - '{$bookTitle}'",
                $log,
                $oldAttributes,
                $newAttributes,
                'library_reading_logs'
            );

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Reading log updated successfully.',
                'data' => $log
            ]);

        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed.',
                'errors' => $e->errors()
            ], 422);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error updating reading log: ' . $e->getMessage());
            
            return response()->json([
                'success' => false,
                'message' => 'Failed to update reading log: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Soft delete reading log (set is_active = 0)
     */
    public function destroy($id)
    {
        if (!$this->canDelete()) {
            return response()->json(['success' => false, 'message' => 'Unauthorized.'], 403);
        }

        try {
            $log = LibraryReadingLog::with(['item', 'visit.user', 'visit.guest'])->findOrFail($id);
            
            // Store old status
            $oldIsActive = $log->is_active;
            
            $log->is_active = false;
            $log->updated_by = Auth::id();
            $log->save();

            // Get visitor and book info for logging
            $visitorName = 'N/A';
            if ($log->visit) {
                $visitorName = $log->visit->user ? $log->visit->user->name : ($log->visit->guest ? $log->visit->guest->full_name : 'N/A');
            }
            $bookTitle = $log->item ? $log->item->title : 'N/A';

            // Log activity
            $this->logActivity(
                "Reading log deactivated: {$visitorName} - '{$bookTitle}'",
                $log,
                [
                    'old_status' => $oldIsActive ? 'Active' : 'Inactive',
                    'new_status' => 'Inactive',
                    'log_id' => $log->id,
                    'book_title' => $bookTitle,
                    'visitor_name' => $visitorName
                ],
                'library_reading_logs'
            );

            return response()->json([
                'success' => true,
                'message' => 'Reading log deleted successfully.'
            ]);

        } catch (\Exception $e) {
            Log::error('Error deleting reading log: ' . $e->getMessage());
            
            return response()->json([
                'success' => false,
                'message' => 'Failed to delete reading log.'
            ], 500);
        }
    }

    /**
     * Toggle active status
     */
    public function toggleStatus($id)
    {
        if (!$this->canWrite()) {
            return response()->json(['success' => false, 'message' => 'Unauthorized.'], 403);
        }

        try {
            $log = LibraryReadingLog::findOrFail($id);
            $log->is_active = !$log->is_active;
            $log->updated_by = Auth::id();
            $log->save();

            return response()->json([
                'success' => true,
                'message' => 'Status updated successfully.',
                'is_active' => $log->is_active
            ]);

        } catch (\Exception $e) {
            Log::error('Error toggling status: ' . $e->getMessage());
            
            return response()->json([
                'success' => false,
                'message' => 'Failed to toggle status.'
            ], 500);
        }
    }

    /**
     * Permanent delete (admin only)
     */
    public function forceDelete($id)
    {
        if (!$this->isAdmin()) {
            return response()->json(['success' => false, 'message' => 'Unauthorized.'], 403);
        }

        try {
            $log = LibraryReadingLog::with(['item', 'visit.user', 'visit.guest'])->findOrFail($id);
            
            // Store log info for logging before deletion
            $logInfo = [
                'id' => $log->id,
                'visit_id' => $log->visit_id,
                'library_item_id' => $log->library_item_id,
                'copy_id' => $log->copy_id,
                'book_title' => $log->item ? $log->item->title : 'N/A',
                'barcode' => $log->copy ? $log->copy->barcode : null,
                'start_time' => $log->start_time,
                'end_time' => $log->end_time,
                'reading_type' => $log->reading_type
            ];
            
            // Get visitor name
            $visitorName = 'N/A';
            if ($log->visit) {
                $visitorName = $log->visit->user ? $log->visit->user->name : ($log->visit->guest ? $log->visit->guest->full_name : 'N/A');
            }
            $logInfo['visitor_name'] = $visitorName;
            
            $log->delete();

            // Log activity (after deletion, so we can't use $log as subject)
            $this->logActivity(
                "Reading log permanently deleted: {$visitorName} - '{$logInfo['book_title']}'",
                null, // No subject since it's deleted
                [
                    'deleted_log' => $logInfo,
                    'deleted_by' => Auth::id(),
                    'deleted_at' => now()->toDateTimeString()
                ],
                'library_reading_logs'
            );

            return response()->json([
                'success' => true,
                'message' => 'Reading log permanently deleted.'
            ]);

        } catch (\Exception $e) {
            Log::error('Error permanently deleting reading log: ' . $e->getMessage());
            
            return response()->json([
                'success' => false,
                'message' => 'Failed to permanently delete reading log.'
            ], 500);
        }
    }

    /**
     * Start reading (set start_time to now)
     */
    public function start($id)
    {
        if (!$this->canWrite()) {
            return response()->json(['success' => false, 'message' => 'Unauthorized.'], 403);
        }

        try {
            DB::beginTransaction();

            $log = LibraryReadingLog::findOrFail($id);

            // Validate: cannot start if already started
            if ($log->start_time) {
                return response()->json([
                    'success' => false,
                    'message' => 'Reading already started at ' . $log->start_time->format('Y-m-d H:i:s')
                ], 422);
            }

            $log->start_time = Carbon::now();
            $log->updated_by = Auth::id();
            $log->save();

            // Refresh to get relationships
            $log->refresh();
            $bookTitle = $log->item ? $log->item->title : 'N/A';
            $visitorName = 'N/A';
            if ($log->visit) {
                $visitorName = $log->visit->user ? $log->visit->user->name : ($log->visit->guest ? $log->visit->guest->full_name : 'N/A');
            }

            // Log activity
            $this->logActivity(
                "Reading started: {$visitorName} - '{$bookTitle}'",
                $log,
                [
                    'visit_id' => $log->visit_id,
                    'library_item_id' => $log->library_item_id,
                    'book_title' => $bookTitle,
                    'start_time' => $log->start_time->toDateTimeString(),
                    'updated_by' => Auth::id()
                ],
                'library_reading_logs'
            );

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Reading started successfully.',
                'start_time' => $log->start_time->format('Y-m-d H:i:s')
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error starting reading: ' . $e->getMessage());
            
            return response()->json([
                'success' => false,
                'message' => 'Failed to start reading.'
            ], 500);
        }
    }

    /**
     * Stop reading (set end_time to now)
     */
    public function stop($id)
    {
        if (!$this->canWrite()) {
            return response()->json(['success' => false, 'message' => 'Unauthorized.'], 403);
        }

        try {
            DB::beginTransaction();

            $log = LibraryReadingLog::findOrFail($id);

            // Validate: must be started first
            if (!$log->start_time) {
                return response()->json([
                    'success' => false,
                    'message' => 'Cannot stop reading that has not been started.'
                ], 422);
            }

            // Validate: cannot stop if already stopped
            if ($log->end_time) {
                return response()->json([
                    'success' => false,
                    'message' => 'Reading already stopped at ' . $log->end_time->format('Y-m-d H:i:s')
                ], 422);
            }

            $log->end_time = Carbon::now();
            $log->updated_by = Auth::id();
            $log->save();

            // Refresh to get calculated attributes
            $log->refresh();
            
            // Get visitor and book info for logging
            $bookTitle = $log->item ? $log->item->title : 'N/A';
            $visitorName = 'N/A';
            if ($log->visit) {
                $visitorName = $log->visit->user ? $log->visit->user->name : ($log->visit->guest ? $log->visit->guest->full_name : 'N/A');
            }

            // Log activity
            $this->logActivity(
                "Reading stopped: {$visitorName} - '{$bookTitle}' (Duration: {$log->duration})",
                $log,
                [
                    'visit_id' => $log->visit_id,
                    'library_item_id' => $log->library_item_id,
                    'book_title' => $bookTitle,
                    'start_time' => $log->start_time ? $log->start_time->toDateTimeString() : null,
                    'end_time' => $log->end_time->toDateTimeString(),
                    'duration' => $log->duration,
                    'minutes_read' => $log->minutes_read,
                    'updated_by' => Auth::id()
                ],
                'library_reading_logs'
            );

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Reading stopped successfully.',
                'end_time' => $log->end_time->format('Y-m-d H:i:s'),
                'duration' => $log->duration,
                'minutes_read' => $log->minutes_read
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error stopping reading: ' . $e->getMessage());
            
            return response()->json([
                'success' => false,
                'message' => 'Failed to stop reading.'
            ], 500);
        }
    }

    /**
     * Get visit statistics
     * Returns: unique books count, logs count, total minutes
     */
    public function getVisitStats($visitId)
    {
        try {
            $booksCount = LibraryReadingLog::countBooksReadInVisit($visitId);
            $logsCount = LibraryReadingLog::where('visit_id', $visitId)
                ->where('is_active', true)
                ->count();
            $totalMinutes = LibraryReadingLog::totalMinutesInVisit($visitId);

            return response()->json([
                'success' => true,
                'data' => [
                    'visit_id' => $visitId,
                    'books_read_count_unique' => $booksCount,
                    'logs_count' => $logsCount,
                    'total_minutes' => $totalMinutes,
                    'total_duration' => $this->formatDuration($totalMinutes),
                ]
            ]);

        } catch (\Exception $e) {
            Log::error('Error getting visit stats: ' . $e->getMessage());
            
            return response()->json([
                'success' => false,
                'message' => 'Failed to get visit statistics.'
            ], 500);
        }
    }

    /**
     * Helper: Format duration in minutes to human-readable
     */
    private function formatDuration($minutes)
    {
        if ($minutes < 60) {
            return $minutes . ' min';
        }

        $hours = floor($minutes / 60);
        $mins = $minutes % 60;

        return $hours . 'h ' . $mins . 'm';
    }

    /**
     * Search visits for select2
     */
    public function searchVisits(Request $request)
    {
        $q = $request->get('q');
        
        $visits = LibraryVisit::with(['user', 'guest'])
            ->where('is_active', true)
            ->where(function($query) use ($q) {
                $query->whereHas('user', function($q2) use ($q) {
                    $q2->where('name', 'like', "%{$q}%");
                })
                ->orWhereHas('guest', function($q2) use ($q) {
                    $q2->where('full_name', 'like', "%{$q}%");
                })
                ->orWhere('visit_date', 'like', "%{$q}%");
            })
            ->limit(20)
            ->get();

        $results = $visits->map(function($visit) {
            $visitorName = $visit->user ? $visit->user->name : ($visit->guest ? $visit->guest->full_name : 'Unknown');
            $date = $visit->visit_date->format('Y-m-d');
            $session = ucfirst($visit->session);
            
            return [
                'id' => $visit->id,
                'text' => "{$visitorName} - {$date} ({$session})"
            ];
        });

        return response()->json(['results' => $results]);
    }

    /**
     * Search library items for select2
     */
    public function searchItems(Request $request)
    {
        $q = $request->get('q');
        
        $items = LibraryItem::where('is_active', true)
            ->where(function($query) use ($q) {
                $query->where('title', 'like', "%{$q}%")
                    ->orWhere('isbn', 'like', "%{$q}%");
            })
            ->limit(20)
            ->get();

        $results = $items->map(function($item) {
            return [
                'id' => $item->id,
                'text' => $item->title . ($item->isbn ? " (ISBN: {$item->isbn})" : '')
            ];
        });

        return response()->json(['results' => $results]);
    }

    /**
     * Search library copies for select2 (filtered by item_id)
     */
    public function searchCopies(Request $request)
    {
        $q = $request->get('q');
        $itemId = $request->get('item_id'); // Filter by selected book
        
        $query = LibraryCopy::where('is_active', true);
        
        // Filter by item_id if provided
        if ($itemId) {
            $query->where('library_item_id', $itemId);
        }
        
        // Search by barcode or call_number
        if ($q) {
            $query->where(function($subQuery) use ($q) {
                $subQuery->where('barcode', 'like', "%{$q}%")
                    ->orWhere('call_number', 'like', "%{$q}%");
            });
        }
        
        $copies = $query->with('item')->limit(20)->get();

        $results = $copies->map(function($copy) {
            $text = "Barcode: {$copy->barcode}";
            if ($copy->call_number) {
                $text .= " (Call #: {$copy->call_number})";
            }
            if ($copy->item) {
                $text .= " - {$copy->item->title}";
            }
            $text .= " [{$copy->status}]";
            
            return [
                'id' => $copy->id,
                'text' => $text
            ];
        });

        return response()->json(['results' => $results]);
    }
}


