<?php

namespace App\Http\Controllers\Admin\Library;

use App\Http\Controllers\Controller;
use App\Models\LibraryVisit;
use App\Models\LibraryReadingLog;
use App\Models\LibraryItem;
use App\Models\LibraryCopy;
use App\Models\User;
use App\Models\LibraryGuest;
use App\Models\Staff;
use App\Traits\LogsActivity;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Yajra\DataTables\Facades\DataTables;
use Carbon\Carbon;

class LibraryVisitController extends Controller
{
    use LogsActivity;
    /**
     * Display a listing of visits (DataTables JSON or view)
     */
    public function index(Request $request)
    {
        if ($request->ajax()) {
            $query = LibraryVisit::with([
                'user', 'guest', 
                'checkedInByStaff.user', 
                'checkedOutByStaff.user'
            ]);

            // Apply filters
            $status = $request->get('status', 'active');
            if ($status === 'active') {
                $query->where('is_active', true);
            } elseif ($status === 'inactive') {
                $query->where('is_active', false);
            }

            // Date range filter
            if ($dateFrom = $request->get('date_from')) {
                $query->whereDate('visit_date', '>=', $dateFrom);
            }
            if ($dateTo = $request->get('date_to')) {
                $query->whereDate('visit_date', '<=', $dateTo);
            }

            // Session filter
            if ($session = $request->get('session')) {
                if ($session !== 'all') {
                    $query->where('session', $session);
                }
            }

            // Purpose filter
            if ($purpose = $request->get('purpose')) {
                if ($purpose !== 'all') {
                    $query->where('purpose', $purpose);
                }
            }

            // Visitor type filter
            if ($visitorType = $request->get('visitor_type')) {
                if ($visitorType === 'user') {
                    $query->whereNotNull('user_id');
                } elseif ($visitorType === 'guest') {
                    $query->whereNotNull('guest_id');
                }
            }

            // Currently inside filter
            if ($currentlyInside = $request->get('currently_inside')) {
                if ($currentlyInside === 'open') {
                    $query->whereNull('check_out_time');
                } elseif ($currentlyInside === 'closed') {
                    $query->whereNotNull('check_out_time');
                }
            }

            return DataTables::of($query)
                ->addIndexColumn()
                ->addColumn('visitor', function ($visit) {
                    $name = e($visit->visitor_name);
                    $type = $visit->visitor_type === 'user' ? 'User' : 'Guest';
                    $badge = $visit->visitor_type === 'user' 
                        ? '<span class="badge bg-primary">User</span>' 
                        : '<span class="badge bg-info">Guest</span>';
                    
                    return '<div>' . $name . '<br>' . $badge . '</div>';
                })
                ->addColumn('visit_date', function ($visit) {
                    return $visit->visit_date ? $visit->visit_date->format('Y-m-d') : '';
                })
                ->addColumn('check_in_time', function ($visit) {
                    return $visit->check_in_time ? $visit->check_in_time->format('H:i') : '<span class="text-muted">N/A</span>';
                })
                ->addColumn('check_out_time', function ($visit) {
                    return $visit->check_out_time ? $visit->check_out_time->format('H:i') : '<span class="text-muted">Still Inside</span>';
                })
                ->addColumn('session', function ($visit) {
                    $colors = [
                        'morning' => 'warning',
                        'afternoon' => 'info',
                        'evening' => 'dark'
                    ];
                    $color = $colors[$visit->session] ?? 'secondary';
                    return '<span class="badge bg-' . $color . '">' . ucfirst($visit->session) . '</span>';
                })
                ->addColumn('purpose', function ($visit) {
                    return '<span class="badge bg-light text-dark">' . ucfirst($visit->purpose) . '</span>';
                })
                ->addColumn('checked_in_by', function ($visit) {
                    if ($visit->checkedInByStaff && $visit->checkedInByStaff->user) {
                        return e($visit->checkedInByStaff->user->name);
                    } elseif ($visit->creator) {
                        return e($visit->creator->name) . ' <span class="badge bg-secondary text-xs">Admin</span>';
                    }
                    return '<span class="text-muted">System</span>';
                })
                ->addColumn('checked_out_by', function ($visit) {
                    if ($visit->checkedOutByStaff && $visit->checkedOutByStaff->user) {
                        return e($visit->checkedOutByStaff->user->name);
                    } elseif ($visit->updater && $visit->check_out_time) {
                        return e($visit->updater->name) . ' <span class="badge bg-secondary text-xs">Admin</span>';
                    }
                    return '<span class="text-muted">N/A</span>';
                })
                ->addColumn('status_badge', function ($visit) {
                    if ($visit->is_open) {
                        return '<span class="badge bg-success">Open</span>';
                    }
                    return '<span class="badge bg-secondary">Closed</span>';
                })
                ->addColumn('is_active', function ($visit) {
                    $checked = $visit->is_active ? 'checked' : '';
                    $canToggle = $this->canWrite();
                    
                    if ($canToggle) {
                        return '<div class="form-check form-switch">
                            <input class="form-check-input" type="checkbox" ' . $checked . ' 
                                onchange="toggleVisitActive(' . $visit->id . ')" 
                                title="Toggle Active Status">
                        </div>';
                    } else {
                        return '<span class="badge bg-' . ($visit->is_active ? 'success' : 'secondary') . '">'
                            . ($visit->is_active ? 'Active' : 'Inactive') . '</span>';
                    }
                })
                ->addColumn('actions', function ($visit) {
                    $actions = '<div class="btn-group btn-group-sm" role="group">';
                    
                    // View button (all roles)
                    $actions .= '<button type="button" class="btn btn-info" onclick="viewVisit(' . $visit->id . ')" title="View Details">
                        <i class="fas fa-eye"></i>
                    </button>';
                    
                    // Edit button (not for principal)
                    if ($this->canWrite()) {
                        $actions .= '<button type="button" class="btn btn-primary" onclick="openEditModal(' . $visit->id . ')" title="Edit">
                            <i class="fas fa-edit"></i>
                        </button>';
                    }
                    
                    // Delete button (admin/manager only)
                    if ($this->canDelete()) {
                        $actions .= '<button type="button" class="btn btn-danger" onclick="deleteVisit(' . $visit->id . ')" title="Delete">
                            <i class="fas fa-trash"></i>
                        </button>';
                    }
                    
                    $actions .= '</div>';
                    return $actions;
                })
                ->filter(function ($query) use ($request) {
                    if ($search = $request->get('search')['value']) {
                        $query->where(function ($q) use ($search) {
                            $q->whereHas('user', function ($subQ) use ($search) {
                                $subQ->where('name', 'like', "%{$search}%");
                            })
                            ->orWhereHas('guest', function ($subQ) use ($search) {
                                $subQ->where('full_name', 'like', "%{$search}%");
                            })
                            ->orWhere('note', 'like', "%{$search}%");
                        });
                    }
                })
                ->rawColumns(['visitor', 'check_in_time', 'check_out_time', 'session', 'purpose', 'checked_in_by', 'checked_out_by', 'status_badge', 'is_active', 'actions'])
                ->make(true);
        }

        return view('admin.library.visits.index');
    }

    /**
     * Show visit details (JSON)
     */
    public function show($id)
    {
        try {
            $visit = LibraryVisit::with([
                'user', 'guest', 
                'checkedInByStaff.user', 
                'checkedOutByStaff.user',
                'creator', 'updater'
            ])->findOrFail($id);

            return response()->json([
                'success' => true,
                'data' => [
                    'id' => $visit->id,
                    'visitor_name' => $visit->visitor_name,
                    'visitor_type' => $visit->visitor_type,
                    'user_id' => $visit->user_id,
                    'guest_id' => $visit->guest_id,
                    'visit_date' => $visit->visit_date ? $visit->visit_date->format('Y-m-d') : '',
                    'check_in_time' => $visit->check_in_time ? $visit->check_in_time->format('Y-m-d H:i:s') : '',
                    'check_out_time' => $visit->check_out_time ? $visit->check_out_time->format('Y-m-d H:i:s') : null,
                    'session' => $visit->session,
                    'purpose' => $visit->purpose,
                    'checked_in_by' => $visit->checkedInByStaff && $visit->checkedInByStaff->user 
                        ? $visit->checkedInByStaff->user->name 
                        : ($visit->creator ? $visit->creator->name . ' (Admin)' : 'System'),
                    'checked_out_by' => $visit->checkedOutByStaff && $visit->checkedOutByStaff->user 
                        ? $visit->checkedOutByStaff->user->name 
                        : ($visit->updater && $visit->check_out_time ? $visit->updater->name . ' (Admin)' : 'N/A'),
                    'note' => $visit->note,
                    'is_active' => $visit->is_active,
                    'is_open' => $visit->is_open,
                    'duration' => $visit->duration,
                    'created_by' => $visit->creator ? $visit->creator->name : 'N/A',
                    'updated_by' => $visit->updater ? $visit->updater->name : 'N/A',
                    'created_at' => $visit->created_at ? $visit->created_at->format('Y-m-d H:i:s') : '',
                    'updated_at' => $visit->updated_at ? $visit->updated_at->format('Y-m-d H:i:s') : '',
                ]
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Visit not found.'
            ], 404);
        }
    }

    /**
     * Store a new visit (manual create)
     */
    public function store(Request $request)
    {
        if (!$this->canWrite()) {
            return response()->json(['success' => false, 'message' => 'Unauthorized.'], 403);
        }

        $rules = [
            'visitor_type' => 'required|in:user,guest',
            'visit_date' => 'required|date',
            'check_in_time' => 'required|date',
            'check_out_time' => 'nullable|date|after:check_in_time',
            'session' => 'required|in:morning,afternoon,evening',
            'purpose' => 'required|in:read,study,borrow,return,other',
            'note' => 'nullable|string|max:1000',
        ];

        if ($request->visitor_type === 'user') {
            $rules['user_id'] = 'required|exists:users,id';
        } else {
            $rules['guest_id'] = 'required|exists:library_guests,id';
        }

        $validated = $request->validate($rules, [
            'visitor_type.required' => 'Please select visitor type.',
            'user_id.required' => 'Please select a user.',
            'user_id.exists' => 'Selected user does not exist.',
            'guest_id.required' => 'Please select a guest.',
            'guest_id.exists' => 'Selected guest does not exist.',
            'visit_date.required' => 'Visit date is required.',
            'check_in_time.required' => 'Check-in time is required.',
            'check_out_time.after' => 'Check-out time must be after check-in time.',
            'session.required' => 'Session is required.',
            'purpose.required' => 'Purpose is required.',
        ]);

        try {
            DB::beginTransaction();

            $data = [
                'user_id' => $request->visitor_type === 'user' ? $validated['user_id'] : null,
                'guest_id' => $request->visitor_type === 'guest' ? $validated['guest_id'] : null,
                'visit_date' => $validated['visit_date'],
                'check_in_time' => $validated['check_in_time'],
                'check_out_time' => $validated['check_out_time'] ?? null,
                'session' => $validated['session'],
                'purpose' => $validated['purpose'],
                'note' => $validated['note'] ?? null,
                'checked_in_by_staff_id' => $this->currentStaffId(),
                'checked_out_by_staff_id' => $validated['check_out_time'] ? $this->currentStaffId() : null,
                'created_by' => Auth::id(),
                'updated_by' => Auth::id(),
                'is_active' => true,
            ];

            $visit = LibraryVisit::create($data);

            // Get visitor name for logging
            $visitorName = $visit->visitor_name;

            // Log activity
            $this->logActivity(
                "Library visit created: {$visitorName} - {$validated['session']} session on {$validated['visit_date']}",
                $visit,
                [
                    'visitor_type' => $request->visitor_type,
                    'user_id' => $visit->user_id,
                    'guest_id' => $visit->guest_id,
                    'visit_date' => $visit->visit_date,
                    'check_in_time' => $visit->check_in_time,
                    'check_out_time' => $visit->check_out_time,
                    'session' => $visit->session,
                    'purpose' => $visit->purpose,
                    'checked_in_by_staff_id' => $visit->checked_in_by_staff_id,
                    'created_by' => Auth::id()
                ],
                'library_visits'
            );

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Visit created successfully.',
                'data' => $visit
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error creating visit: ' . $e->getMessage());
            
            return response()->json([
                'success' => false,
                'message' => 'Failed to create visit: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Update an existing visit
     */
    public function update(Request $request, $id)
    {
        if (!$this->canWrite()) {
            return response()->json(['success' => false, 'message' => 'Unauthorized.'], 403);
        }

        $visit = LibraryVisit::find($id);
        if (!$visit) {
            return response()->json(['success' => false, 'message' => 'Visit not found.'], 404);
        }

        $validated = $request->validate([
            'session' => 'required|in:morning,afternoon,evening',
            'purpose' => 'required|in:read,study,borrow,return,other',
            'note' => 'nullable|string|max:1000',
        ], [
            'session.required' => 'Session is required.',
            'purpose.required' => 'Purpose is required.',
        ]);

        try {
            DB::beginTransaction();

            // Store old values for logging
            $oldAttributes = [
                'session' => $visit->session,
                'purpose' => $visit->purpose,
                'note' => $visit->note
            ];

            $validated['updated_by'] = Auth::id();
            $visit->update($validated);

            // Log activity with old and new values
            $this->logActivityUpdate(
                "Library visit updated: {$visit->visitor_name} - Visit #{$visit->id}",
                $visit,
                $oldAttributes,
                $validated,
                'library_visits'
            );

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Visit updated successfully.',
                'data' => $visit
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            
            return response()->json([
                'success' => false,
                'message' => 'Failed to update visit: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Soft delete (set is_active = 0)
     */
    public function destroy($id)
    {
        if (!$this->canDelete()) {
            return response()->json(['success' => false, 'message' => 'Unauthorized.'], 403);
        }

        try {
            $visit = LibraryVisit::findOrFail($id);
            
            // Store old status
            $oldIsActive = $visit->is_active;
            
            $visit->is_active = false;
            $visit->updated_by = Auth::id();
            $visit->save();

            // Log activity
            $this->logActivity(
                "Library visit deactivated: {$visit->visitor_name} - Visit #{$visit->id}",
                $visit,
                [
                    'old_status' => $oldIsActive ? 'Active' : 'Inactive',
                    'new_status' => 'Inactive',
                    'visit_id' => $visit->id,
                    'visit_date' => $visit->visit_date,
                    'visitor_name' => $visit->visitor_name
                ],
                'library_visits'
            );

            return response()->json([
                'success' => true,
                'message' => 'Visit deactivated successfully.'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to deactivate visit.'
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
            $visit = LibraryVisit::findOrFail($id);
            $visit->is_active = !$visit->is_active;
            $visit->updated_by = Auth::id();
            $visit->save();

            return response()->json([
                'success' => true,
                'message' => 'Visit status updated successfully.',
                'is_active' => $visit->is_active
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to update status.'
            ], 500);
        }
    }

    /**
     * Permanent delete (admin only)
     */
    public function forceDelete($id)
    {
        if (!$this->canForceDelete()) {
            return response()->json(['success' => false, 'message' => 'Unauthorized. Admin only.'], 403);
        }

        try {
            $visit = LibraryVisit::with(['user', 'guest'])->findOrFail($id);
            
            // Store visit info for logging before deletion
            $visitInfo = [
                'id' => $visit->id,
                'visitor_name' => $visit->visitor_name,
                'visitor_type' => $visit->visitor_type,
                'user_id' => $visit->user_id,
                'guest_id' => $visit->guest_id,
                'visit_date' => $visit->visit_date,
                'check_in_time' => $visit->check_in_time,
                'check_out_time' => $visit->check_out_time,
                'session' => $visit->session,
                'purpose' => $visit->purpose
            ];
            
            $visit->delete(); // Permanent delete

            // Log activity (after deletion, so we can't use $visit as subject)
            $this->logActivity(
                "Library visit permanently deleted: {$visitInfo['visitor_name']} - Visit #{$visitInfo['id']}",
                null, // No subject since it's deleted
                [
                    'deleted_visit' => $visitInfo,
                    'deleted_by' => Auth::id(),
                    'deleted_at' => now()->toDateTimeString()
                ],
                'library_visits'
            );

            return response()->json([
                'success' => true,
                'message' => 'Visit permanently deleted.'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to delete visit.'
            ], 500);
        }
    }

    /**
     * Check-in workflow: Create new visit with check-in timestamp
     * Optional: Auto-start reading log
     */
    public function checkIn(Request $request)
    {
        if (!$this->canWrite()) {
            return response()->json(['success' => false, 'message' => 'Unauthorized.'], 403);
        }

        // Get staff ID if available (optional for admin/manager)
        $staffId = $this->currentStaffId();

        $rules = [
            'visitor_type' => 'required|in:user,guest',
            'session' => 'required|in:morning,afternoon,evening',
            'purpose' => 'required|in:read,study,borrow,return,other',
            'note' => 'nullable|string|max:1000',
            'start_reading_now' => 'nullable|boolean', // NEW: Optional quick-start reading
        ];

        if ($request->visitor_type === 'user') {
            $rules['user_id'] = 'required|exists:users,id';
        } else {
            $rules['guest_id'] = 'required|exists:library_guests,id';
        }

        // NEW: If starting reading immediately, require book selection
        if ($request->input('start_reading_now')) {
            $rules['library_item_id'] = 'required|exists:library_items,id';
            $rules['copy_id'] = 'nullable|exists:library_copies,id';
        }

        $validated = $request->validate($rules, [
            'visitor_type.required' => 'Please select visitor type.',
            'user_id.required' => 'Please select a user.',
            'user_id.exists' => 'Selected user does not exist.',
            'guest_id.required' => 'Please select a guest.',
            'guest_id.exists' => 'Selected guest does not exist.',
            'session.required' => 'Session is required.',
            'purpose.required' => 'Purpose is required.',
            'library_item_id.required' => 'Please select a book to start reading.',
            'library_item_id.exists' => 'Selected book does not exist.',
        ]);

        try {
            DB::beginTransaction();

            // Check for duplicate open session today
            $existingOpen = LibraryVisit::with(['checkedInByStaff.user', 'creator'])
                ->whereDate('visit_date', Carbon::today())
                ->whereNull('check_out_time')
                ->where(function ($q) use ($request, $validated) {
                    if ($request->visitor_type === 'user') {
                        $q->where('user_id', $validated['user_id']);
                    } else {
                        $q->where('guest_id', $validated['guest_id']);
                    }
                })
                ->first();

            if ($existingOpen) {
                $checkedInBy = $existingOpen->checkedInByStaff && $existingOpen->checkedInByStaff->user 
                    ? $existingOpen->checkedInByStaff->user->name 
                    : ($existingOpen->creator ? $existingOpen->creator->name : 'System');
                
                return response()->json([
                    'success' => false,
                    'message' => 'This visitor already has an open session today. Please check-out first!',
                    'data' => [
                        'existing_session' => [
                            'check_in_time' => $existingOpen->check_in_time->format('H:i'),
                            'session' => ucfirst($existingOpen->session),
                            'purpose' => ucfirst($existingOpen->purpose),
                            'checked_in_by' => $checkedInBy,
                            'duration' => $existingOpen->duration
                        ]
                    ]
                ], 422);
            }

            // Create visit record
            $data = [
                'user_id' => $request->visitor_type === 'user' ? $validated['user_id'] : null,
                'guest_id' => $request->visitor_type === 'guest' ? $validated['guest_id'] : null,
                'visit_date' => Carbon::today(),
                'check_in_time' => Carbon::now(),
                'check_out_time' => null,
                'session' => $validated['session'],
                'purpose' => $validated['purpose'],
                'note' => $validated['note'] ?? null,
                'checked_in_by_staff_id' => $staffId, // Can be null if admin/manager without staff record
                'checked_out_by_staff_id' => null,
                'created_by' => Auth::id(),
                'updated_by' => Auth::id(),
                'is_active' => true,
            ];

            $visit = LibraryVisit::create($data);

            // Get visitor name for logging
            $visitorName = $visit->visitor_name;

            // NEW: Auto-create reading log if requested
            $readingLog = null;
            $bookTitle = null;
            
            if ($request->input('start_reading_now') && isset($validated['library_item_id'])) {
                // Get book title for response message
                $item = LibraryItem::find($validated['library_item_id']);
                $bookTitle = $item ? $item->title : 'Selected book';
                
                // Build note
                $readingNote = 'Auto-start reading from check-in';
                if (!empty($validated['note'])) {
                    $readingNote = $validated['note'] . ' | ' . $readingNote;
                }
                
                // Create reading log
                $readingLog = LibraryReadingLog::create([
                    'visit_id' => $visit->id,
                    'library_item_id' => $validated['library_item_id'],
                    'copy_id' => $validated['copy_id'] ?? null,
                    'start_time' => Carbon::now(),
                    'end_time' => null,
                    'reading_type' => 'in_library',
                    'note' => $readingNote,
                    'created_by' => Auth::id(),
                    'updated_by' => Auth::id(),
                    'is_active' => true,
                ]);
            }

            // Log activity
            $this->logActivity(
                "Visitor checked-in: {$visitorName} - {$validated['session']} session" . ($bookTitle ? " (Reading: {$bookTitle})" : ""),
                $visit,
                [
                    'visitor_type' => $request->visitor_type,
                    'user_id' => $visit->user_id,
                    'guest_id' => $visit->guest_id,
                    'visit_date' => $visit->visit_date,
                    'check_in_time' => $visit->check_in_time,
                    'session' => $visit->session,
                    'purpose' => $visit->purpose,
                    'checked_in_by_staff_id' => $visit->checked_in_by_staff_id,
                    'reading_started' => (bool)$readingLog,
                    'book_title' => $bookTitle,
                    'created_by' => Auth::id()
                ],
                'library_visits'
            );

            DB::commit();

            // Build success message
            $message = 'Visitor checked-in successfully!';
            if ($readingLog) {
                $message .= ' Reading started for: ' . $bookTitle;
            }

            return response()->json([
                'success' => true,
                'message' => $message,
                'data' => [
                    'visit' => $visit,
                    'reading_log' => $readingLog,
                    'reading_started' => (bool)$readingLog,
                    'book_title' => $bookTitle,
                ]
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error during check-in: ' . $e->getMessage());
            
            return response()->json([
                'success' => false,
                'message' => 'Check-in failed: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Check-out workflow: Update existing open visit with check-out timestamp
     */
    public function checkOut(Request $request)
    {
        if (!$this->canWrite()) {
            return response()->json(['success' => false, 'message' => 'Unauthorized.'], 403);
        }

        // Get staff ID if available (optional for admin/manager)
        $staffId = $this->currentStaffId();

        $rules = [
            'visitor_type' => 'required|in:user,guest',
        ];

        if ($request->visitor_type === 'user') {
            $rules['user_id'] = 'required|exists:users,id';
        } else {
            $rules['guest_id'] = 'required|exists:library_guests,id';
        }

        $validated = $request->validate($rules, [
            'visitor_type.required' => 'Please select visitor type.',
            'user_id.required' => 'Please select a user.',
            'guest_id.required' => 'Please select a guest.',
        ]);

        try {
            DB::beginTransaction();

            // Find the most recent open visit for this visitor today
            $openVisit = LibraryVisit::whereDate('visit_date', Carbon::today())
                ->whereNull('check_out_time')
                ->where(function ($q) use ($request, $validated) {
                    if ($request->visitor_type === 'user') {
                        $q->where('user_id', $validated['user_id']);
                    } else {
                        $q->where('guest_id', $validated['guest_id']);
                    }
                })
                ->orderBy('check_in_time', 'desc')
                ->first();

            if (!$openVisit) {
                return response()->json([
                    'success' => false,
                    'message' => 'No open visit found for this visitor today.'
                ], 422);
            }

            $openVisit->check_out_time = Carbon::now();
            $openVisit->checked_out_by_staff_id = $staffId; // Can be null if admin/manager without staff record
            $openVisit->updated_by = Auth::id();
            $openVisit->save();

            // Get visitor name for logging
            $visitorName = $openVisit->visitor_name;

            // Log activity
            $this->logActivity(
                "Visitor checked-out: {$visitorName} - Visit #{$openVisit->id}",
                $openVisit,
                [
                    'visitor_type' => $openVisit->visitor_type,
                    'user_id' => $openVisit->user_id,
                    'guest_id' => $openVisit->guest_id,
                    'visit_date' => $openVisit->visit_date,
                    'check_in_time' => $openVisit->check_in_time,
                    'check_out_time' => $openVisit->check_out_time,
                    'session' => $openVisit->session,
                    'duration' => $openVisit->duration,
                    'checked_out_by_staff_id' => $openVisit->checked_out_by_staff_id,
                    'updated_by' => Auth::id()
                ],
                'library_visits'
            );

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Visitor checked-out successfully!',
                'data' => $openVisit
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error during check-out: ' . $e->getMessage());
            
            return response()->json([
                'success' => false,
                'message' => 'Check-out failed: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Find open visit for a visitor (helper for check-out modal)
     */
    public function findOpenVisit(Request $request)
    {
        $rules = [
            'visitor_type' => 'required|in:user,guest',
        ];

        if ($request->visitor_type === 'user') {
            $rules['user_id'] = 'required|exists:users,id';
        } else {
            $rules['guest_id'] = 'required|exists:library_guests,id';
        }

        $validated = $request->validate($rules);

        try {
            $openVisit = LibraryVisit::with(['user', 'guest', 'checkedInByStaff.user', 'creator'])
                ->whereDate('visit_date', Carbon::today())
                ->whereNull('check_out_time')
                ->where(function ($q) use ($request, $validated) {
                    if ($request->visitor_type === 'user') {
                        $q->where('user_id', $validated['user_id']);
                    } else {
                        $q->where('guest_id', $validated['guest_id']);
                    }
                })
                ->orderBy('check_in_time', 'desc')
                ->first();

            if (!$openVisit) {
                return response()->json([
                    'success' => false,
                    'message' => 'No open visit found for this visitor today.'
                ], 404);
            }

            return response()->json([
                'success' => true,
                'data' => [
                    'id' => $openVisit->id,
                    'visitor_name' => $openVisit->visitor_name,
                    'visit_date' => $openVisit->visit_date->format('Y-m-d'),
                    'check_in_time' => $openVisit->check_in_time->format('Y-m-d H:i:s'),
                    'session' => ucfirst($openVisit->session),
                    'purpose' => ucfirst($openVisit->purpose),
                    'checked_in_by' => $openVisit->checkedInByStaff && $openVisit->checkedInByStaff->user 
                        ? $openVisit->checkedInByStaff->user->name 
                        : ($openVisit->creator ? $openVisit->creator->name . ' (Admin)' : 'System'),
                    'duration' => $openVisit->duration,
                ]
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error finding open visit.'
            ], 500);
        }
    }

    // Permission helpers
    protected function canWrite()
    {
        return Auth::user()->hasAnyRole(['admin', 'manager', 'staff']);
    }

    protected function canDelete()
    {
        return Auth::user()->hasAnyRole(['admin', 'manager']);
    }

    protected function canForceDelete()
    {
        return Auth::user()->hasRole('admin');
    }

    /**
     * Get current staff ID from logged-in user
     */
    private function currentStaffId(): ?int
    {
        $staff = Staff::where('user_id', Auth::id())->first();
        return $staff ? $staff->id : null;
    }
}

