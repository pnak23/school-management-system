<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Student;
use App\Models\StudentPhone;
use App\Traits\LogsActivity;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use Yajra\DataTables\Facades\DataTables;
use Illuminate\Support\Facades\Log;

class StudentControllerDataTable extends Controller
{
    use LogsActivity;
    /**
     * Check if the authenticated user has permission to read.
     */
    private function canRead(): bool
    {
        return Auth::user()->hasAnyRole(['admin', 'manager', 'principal', 'staff']);
    }

    /**
     * Check if the authenticated user has permission to write (create/update).
     */
    private function canWrite(): bool
    {
        return Auth::user()->hasAnyRole(['admin', 'manager', 'staff']);
    }

    /**
     * Check if the authenticated user has permission to delete.
     */
    private function canDelete(): bool
    {
        return Auth::user()->hasAnyRole(['admin', 'manager']);
    }

    /**
     * Display students page with DataTable
     * If Ajax: return DataTables JSON
     * Otherwise: return Blade view
     */
    public function index(Request $request)
    {
        // Check read permission
        if (!$this->canRead()) {
            if ($request->ajax()) {
                return response()->json(['error' => 'Unauthorized'], 403);
            }
            abort(403, 'You do not have permission to view students.');
        }

        // Handle DataTables Ajax request
        if ($request->ajax()) {
            $query = Student::with(['user', 'phones' => function($q) {
                $q->where('is_primary', 1)->where('is_active', 1);
            }])->select('students.*');

            // Filter by status
            if ($request->filled('status') && $request->status !== 'all') {
                if ($request->status === 'active') {
                    $query->where('is_active', 1);
                } elseif ($request->status === 'inactive') {
                    $query->where('is_active', 0);
                }
            }

            // Filter by sex
            if ($request->filled('sex') && $request->sex !== 'all') {
                $query->where('sex', $request->sex);
            }

            // Filter by date range
            if ($request->filled('date_from')) {
                $query->whereDate('created_at', '>=', $request->date_from);
            }
            if ($request->filled('date_to')) {
                $query->whereDate('created_at', '<=', $request->date_to);
            }

            return DataTables::of($query)
                ->addIndexColumn()
                ->filter(function ($query) use ($request) {
                    if ($request->has('search') && !empty($request->search['value'])) {
                        $search = $request->search['value'];
                        $query->where(function ($q) use ($search) {
                            $q->where('khmer_name', 'like', "%{$search}%")
                              ->orWhere('english_name', 'like', "%{$search}%")
                              ->orWhere('code', 'like', "%{$search}%");
                        });
                    }
                })
                ->addColumn('photo_display', function ($student) {
                    if ($student->photo) {
                        $photoUrl = asset('storage/' . $student->photo);
                        return '<img src="' . $photoUrl . '" alt="Photo" class="rounded-circle" style="width: 40px; height: 40px; object-fit: cover;">';
                    }
                    return '<div class="rounded-circle bg-secondary d-inline-flex align-items-center justify-content-center" style="width: 40px; height: 40px;"><i class="fas fa-user text-white"></i></div>';
                })
                ->addColumn('phone', function ($student) {
                    $primaryPhone = $student->phones->first();
                    return $primaryPhone ? e($primaryPhone->phone) : '-';
                })
                ->addColumn('status_badge', function ($student) {
                    if ($student->is_active) {
                        return '<span class="badge bg-success">Active</span>';
                    }
                    return '<span class="badge bg-danger">Inactive</span>';
                })
                ->addColumn('sex_display', function ($student) {
                    return $student->sex === 'M' ? 'Male' : 'Female';
                })
                ->editColumn('dob', function ($student) {
                    return $student->dob ? \Carbon\Carbon::parse($student->dob)->format('Y-m-d') : '-';
                })
                ->editColumn('created_at', function ($student) {
                    return $student->created_at ? $student->created_at->format('Y-m-d') : '-';
                })
                ->addColumn('actions', function ($student) {
                    $buttons = '';
                    
                    // View button
                    $buttons .= '<button class="btn btn-sm btn-info btn-view-student me-1" data-id="'.$student->id.'" title="View">
                        <i class="fas fa-eye"></i>
                    </button>';
                    
                    if (Auth::user()->hasAnyRole(['admin', 'manager', 'staff'])) {
                        // Edit button
                        $buttons .= '<button class="btn btn-sm btn-primary btn-edit-student me-1" data-id="'.$student->id.'" title="Edit">
                            <i class="fas fa-edit"></i>
                        </button>';
                        
                        // Toggle status button
                        if ($student->is_active) {
                            $buttons .= '<button class="btn btn-sm btn-warning btn-toggle-status me-1" data-id="'.$student->id.'" title="Deactivate">
                                <i class="fas fa-ban"></i>
                            </button>';
                        } else {
                            $buttons .= '<button class="btn btn-sm btn-success btn-toggle-status me-1" data-id="'.$student->id.'" title="Activate">
                                <i class="fas fa-check"></i>
                            </button>';
                        }
                    }
                    
                    if (Auth::user()->hasAnyRole(['admin', 'manager'])) {
                        // Delete button (hard delete - admin only)
                        $buttons .= '<button class="btn btn-sm btn-danger btn-delete-student" data-id="'.$student->id.'" title="Delete">
                            <i class="fas fa-trash"></i>
                        </button>';
                    }
                    
                    return $buttons;
                })
                ->rawColumns(['photo_display', 'status_badge', 'actions'])
                ->make(true);
        }

        // Return Blade view
        $provinces = \App\Models\Province::orderBy('name_en')->get(['id', 'name_en', 'name_km']);
        return view('admin.students.index', compact('provinces'));
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
            $total = Student::count();
            $active = Student::where('is_active', 1)->count();
            $inactive = Student::where('is_active', 0)->count();
            $male = Student::where('sex', 'M')->count();
            $female = Student::where('sex', 'F')->count();

            return response()->json([
                'success' => true,
                'data' => [
                    'total' => $total,
                    'active' => $active,
                    'inactive' => $inactive,
                    'male' => $male,
                    'female' => $female,
                ]
            ]);
        } catch (\Exception $e) {
            Log::error('Error fetching student stats: ' . $e->getMessage());
            
            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch statistics.'
            ], 500);
        }
    }

    /**
     * Get student details for edit
     */
    public function show($id)
    {
        if (!$this->canRead()) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        $student = Student::with(['user', 'phones', 'creator', 'updater'])->find($id);

        if (!$student) {
            return response()->json([
                'success' => false,
                'message' => 'Student not found.'
            ], 404);
        }

        return response()->json([
            'success' => true,
            'data' => $student
        ]);
    }

    /**
     * Store a newly created student
     */
    public function store(Request $request)
    {
        if (!$this->canWrite()) {
            return response()->json(['error' => 'Unauthorized. You do not have permission to create students.'], 403);
        }

        $validator = Validator::make($request->all(), [
            'khmer_name' => 'required|string|max:255',
            'english_name' => 'nullable|string|max:255',
            'dob' => 'nullable|date|before_or_equal:today',
            'sex' => 'required|in:M,F',
            'code' => 'nullable|string|max:100|unique:students,code',
            'note' => 'nullable|string',
            'photo' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
            'phone' => 'nullable|string|max:30',
            'user_id' => 'nullable|exists:users,id',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed. Please check the form fields.',
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            DB::beginTransaction();

            $data = $validator->validated();
            
            // Handle photo upload
            if ($request->hasFile('photo')) {
                $photoPath = $request->file('photo')->store('student_photos', 'public');
                $data['photo'] = $photoPath;
            }

            // Set audit fields
            $data['created_by'] = Auth::id();
            $data['is_active'] = 1;

            // Remove phone from student data
            $phoneNumber = $data['phone'] ?? null;
            unset($data['phone']);

            // Create student
            $student = Student::create($data);

            // Create primary phone if provided
            if ($phoneNumber) {
                StudentPhone::create([
                    'student_id' => $student->id,
                    'phone' => $phoneNumber,
                    'is_primary' => 1,
                    'created_by' => Auth::id(),
                    'is_active' => 1,
                ]);
            }

            // Log activity
            $this->logActivity(
                "Student created: {$student->khmer_name}" . ($student->english_name ? " ({$student->english_name})" : "") . " - Code: {$student->code}",
                $student,
                [
                    'khmer_name' => $student->khmer_name,
                    'english_name' => $student->english_name,
                    'code' => $student->code,
                    'dob' => $student->dob,
                    'sex' => $student->sex,
                    'phone' => $phoneNumber,
                    'photo' => $student->photo ? 'Uploaded' : null,
                    'created_by' => Auth::id()
                ],
                'students'
            );

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Student created successfully.',
                'data' => $student
            ], 201);

        } catch (\Exception $e) {
            DB::rollBack();
            
            if (isset($photoPath)) {
                Storage::disk('public')->delete($photoPath);
            }

            Log::error('Student creation failed', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to create student: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Update the specified student
     */
    public function update(Request $request, $id)
    {
        if (!$this->canWrite()) {
            return response()->json(['error' => 'Unauthorized. You do not have permission to update students.'], 403);
        }

        $student = Student::find($id);

        if (!$student) {
            return response()->json([
                'success' => false,
                'message' => 'Student not found.'
            ], 404);
        }

        $validator = Validator::make($request->all(), [
            'khmer_name' => 'required|string|max:255',
            'english_name' => 'nullable|string|max:255',
            'dob' => 'nullable|date|before_or_equal:today',
            'sex' => 'required|in:M,F',
            'code' => 'nullable|string|max:100|unique:students,code,' . $id,
            'note' => 'nullable|string',
            'photo' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
            'phone' => 'nullable|string|max:30',
            'user_id' => 'nullable|exists:users,id',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed. Please check the form fields.',
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            DB::beginTransaction();

            $data = $validator->validated();
            
            // Handle photo upload
            if ($request->hasFile('photo')) {
                if ($student->photo) {
                    Storage::disk('public')->delete($student->photo);
                }
                $photoPath = $request->file('photo')->store('student_photos', 'public');
                $data['photo'] = $photoPath;
            }

            $data['updated_by'] = Auth::id();

            // Store old values for logging
            $oldAttributes = [
                'khmer_name' => $student->khmer_name,
                'english_name' => $student->english_name,
                'code' => $student->code,
                'dob' => $student->dob,
                'sex' => $student->sex,
                'note' => $student->note,
                'photo' => $student->photo,
                'is_active' => $student->is_active
            ];

            // Get old phone
            $oldPhone = $student->phones()->where('is_primary', 1)->first();
            $oldPhoneNumber = $oldPhone ? $oldPhone->phone : null;

            // Remove phone from student data
            $phoneNumber = $data['phone'] ?? null;
            unset($data['phone']);

            // Update student
            $student->update($data);

            // Update or create primary phone
            if ($phoneNumber) {
                $primaryPhone = $student->phones()->where('is_primary', 1)->first();
                if ($primaryPhone) {
                    $primaryPhone->update([
                        'phone' => $phoneNumber,
                        'updated_by' => Auth::id(),
                    ]);
                } else {
                    StudentPhone::create([
                        'student_id' => $student->id,
                        'phone' => $phoneNumber,
                        'is_primary' => 1,
                        'created_by' => Auth::id(),
                        'is_active' => 1,
                    ]);
                }
            }

            // Prepare new attributes for logging
            $newAttributes = [
                'khmer_name' => $student->khmer_name,
                'english_name' => $student->english_name,
                'code' => $student->code,
                'dob' => $student->dob,
                'sex' => $student->sex,
                'note' => $student->note,
                'photo' => $student->photo,
                'is_active' => $student->is_active,
                'phone' => $phoneNumber
            ];

            // Add phone change to old attributes if changed
            if ($oldPhoneNumber !== $phoneNumber) {
                $oldAttributes['phone'] = $oldPhoneNumber;
            }

            // Log activity with old and new values
            $this->logActivityUpdate(
                "Student updated: {$student->khmer_name}" . ($student->english_name ? " ({$student->english_name})" : "") . " - Code: {$student->code}",
                $student,
                $oldAttributes,
                $newAttributes,
                'students'
            );

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Student updated successfully.',
                'data' => $student
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            
            if (isset($photoPath) && $photoPath !== $student->photo) {
                Storage::disk('public')->delete($photoPath);
            }

            Log::error('Student update failed', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to update student: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Deactivate student (soft delete)
     */
    public function deactivate($id)
    {
        if (!$this->canWrite()) {
            return response()->json(['error' => 'Unauthorized. You do not have permission to deactivate students.'], 403);
        }

        $student = Student::find($id);

        if (!$student) {
            return response()->json([
                'success' => false,
                'message' => 'Student not found.'
            ], 404);
        }

        try {
            // Store old value
            $oldIsActive = $student->is_active;
            
            // Toggle status
            $student->is_active = !$student->is_active;
            $student->updated_by = Auth::id();
            $student->save();

            // Log activity
            $action = $student->is_active ? 'activated' : 'deactivated';
            $this->logActivity(
                "Student {$action}: {$student->khmer_name}" . ($student->english_name ? " ({$student->english_name})" : "") . " - Code: {$student->code}",
                $student,
                [
                    'old_status' => $oldIsActive ? 'Active' : 'Inactive',
                    'new_status' => $student->is_active ? 'Active' : 'Inactive',
                    'student_id' => $student->id,
                    'code' => $student->code
                ],
                'students'
            );

            return response()->json([
                'success' => true,
                'message' => 'Student ' . $action . ' successfully.'
            ]);

        } catch (\Exception $e) {
            Log::error('Student status toggle failed', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to update student status: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Remove the specified student from storage (hard delete - admin only)
     */
    public function destroy($id)
    {
        if (!Auth::user()->hasRole('admin')) {
            return response()->json(['error' => 'Only admins can permanently delete students.'], 403);
        }

        $student = Student::find($id);

        if (!$student) {
            return response()->json([
                'success' => false,
                'message' => 'Student not found.'
            ], 404);
        }

        try {
            DB::beginTransaction();

            // Store student info for logging before deletion
            $studentInfo = [
                'id' => $student->id,
                'khmer_name' => $student->khmer_name,
                'english_name' => $student->english_name,
                'code' => $student->code,
                'dob' => $student->dob,
                'sex' => $student->sex,
                'photo' => $student->photo ? 'Had photo' : null
            ];

            // Delete phones (cascade)
            $student->phones()->delete();
            
            // Delete photo
            if ($student->photo) {
                Storage::disk('public')->delete($student->photo);
            }
            
            // Delete student
            $student->delete();

            // Log activity (after deletion, so we can't use $student as subject)
            $this->logActivity(
                "Student permanently deleted: {$studentInfo['khmer_name']}" . ($studentInfo['english_name'] ? " ({$studentInfo['english_name']})" : "") . " - Code: {$studentInfo['code']}",
                null, // No subject since it's deleted
                [
                    'deleted_student' => $studentInfo,
                    'deleted_by' => Auth::id(),
                    'deleted_at' => now()->toDateTimeString()
                ],
                'students'
            );

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Student permanently deleted successfully.'
            ]);

        } catch (\Exception $e) {
            DB::rollBack();

            Log::error('Student deletion failed', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to delete student: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Search users for Select2 dropdown (AJAX)
     */
    public function searchUsers(Request $request)
    {
        if (!$this->canRead()) {
            return response()->json(['success' => false, 'message' => 'Unauthorized.'], 403);
        }

        $query = $request->get('q', '');
        $page = $request->get('page', 1);
        $perPage = 20;

        try {
            $usersQuery = \App\Models\User::where('is_active', 1);

            // Filter by search term
            if (!empty($query)) {
                $usersQuery->where(function ($q) use ($query) {
                    $q->where('name', 'like', "%{$query}%")
                      ->orWhere('email', 'like', "%{$query}%");
                });
            }

            // Paginate results
            $users = $usersQuery->orderBy('name')
                ->skip(($page - 1) * $perPage)
                ->take($perPage + 1) // Take one extra to check if there are more
                ->get();

            // Check if there are more results
            $hasMore = $users->count() > $perPage;
            if ($hasMore) {
                $users = $users->take($perPage);
            }

            // Format results for Select2
            $results = $users->map(function ($user) {
                return [
                    'id' => $user->id,
                    'text' => $user->name . ' (' . $user->email . ')',
                    'name' => $user->name,
                    'email' => $user->email
                ];
            });

            return response()->json([
                'success' => true,
                'results' => $results,
                'pagination' => [
                    'more' => $hasMore
                ]
            ]);
        } catch (\Exception $e) {
            Log::error('Error searching users: ' . $e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Failed to search users.',
                'results' => []
            ], 500);
        }
    }
}
