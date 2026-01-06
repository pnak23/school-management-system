<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Teacher;
use App\Models\TeacherPhone;
use App\Models\User;
use App\Models\Department;
use App\Models\Position;
use App\Models\EmploymentType;
use App\Models\District;
use App\Models\Commune;
use App\Models\Village;
use App\Traits\LogsActivity;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use Yajra\DataTables\Facades\DataTables;
use Illuminate\Support\Facades\Log;

class TeacherController extends Controller
{
    use LogsActivity;
    private function canWrite(): bool
    {
        return auth()->user()->hasAnyRole(['admin', 'manager', 'staff']);
    }

    private function canRead(): bool
    {
        return auth()->user()->hasAnyRole(['admin', 'manager', 'principal', 'staff']);
    }

    private function canDelete(): bool
    {
        return auth()->user()->hasAnyRole(['admin', 'manager']);
    }

    public function index(Request $request)
    {
        if (!$this->canRead()) {
            if ($request->ajax()) {
                return response()->json(['error' => 'Unauthorized'], 403);
            }
            abort(403, 'You do not have permission to view teachers.');
        }

        // Handle DataTables Ajax request
        if ($request->ajax()) {
            $query = Teacher::with([
                'user', 
                'department', 
                'position', 
                'employmentType',
                'phones' => function($q) {
                    $q->where('is_primary', 1)->where('is_active', 1);
                }
            ])->select('teachers.*');

            // Filter by status
            if ($request->filled('status') && $request->status !== 'all') {
                if ($request->status === 'active') {
                    $query->where('is_active', 1);
                } elseif ($request->status === 'inactive') {
                    $query->where('is_active', 0);
                }
            }

            // Filter by department
            if ($request->filled('department') && $request->department !== 'all') {
                $query->where('department_id', $request->department);
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
                      ->orWhere('teacher_code', 'like', "%{$search}%");
                });
            }
                })
                ->addColumn('photo_display', function ($teacher) {
                    if ($teacher->photo) {
                        $photoUrl = asset('storage/' . $teacher->photo);
                        return '<img src="' . $photoUrl . '" alt="Photo" class="rounded-circle" style="width: 40px; height: 40px; object-fit: cover;">';
                    }
                    return '<div class="rounded-circle bg-secondary d-inline-flex align-items-center justify-content-center" style="width: 40px; height: 40px;"><i class="fas fa-user text-white"></i></div>';
                })
                ->addColumn('phone', function ($teacher) {
                    $primaryPhone = $teacher->phones->first();
                    return $primaryPhone ? e($primaryPhone->phone) : '-';
                })
                ->addColumn('department_name', function ($teacher) {
                    return $teacher->department ? e($teacher->department->name) : '-';
                })
                ->addColumn('position_name', function ($teacher) {
                    return $teacher->position ? e($teacher->position->name) : '-';
                })
                ->addColumn('status_badge', function ($teacher) {
                    if ($teacher->is_active) {
                        return '<span class="badge bg-success">Active</span>';
                    }
                    return '<span class="badge bg-danger">Inactive</span>';
                })
                ->addColumn('sex_display', function ($teacher) {
                    return $teacher->sex === 'M' ? 'Male' : 'Female';
                })
                ->editColumn('created_at', function ($teacher) {
                    return $teacher->created_at ? $teacher->created_at->format('Y-m-d') : '-';
                })
                ->addColumn('actions', function ($teacher) {
                    $buttons = '';
                    
                    // View button
                    $buttons .= '<button class="btn btn-sm btn-info btn-view-teacher me-1" data-id="'.$teacher->id.'" title="View">
                        <i class="fas fa-eye"></i>
                    </button>';
                    
                    if (auth()->user()->hasAnyRole(['admin', 'manager', 'staff'])) {
                        // Edit button
                        $buttons .= '<button class="btn btn-sm btn-primary btn-edit-teacher me-1" data-id="'.$teacher->id.'" title="Edit">
                            <i class="fas fa-edit"></i>
                        </button>';
                        
                        // Toggle status button
                        if ($teacher->is_active) {
                            $buttons .= '<button class="btn btn-sm btn-warning btn-toggle-status me-1" data-id="'.$teacher->id.'" title="Deactivate">
                                <i class="fas fa-ban"></i>
                            </button>';
                        } else {
                            $buttons .= '<button class="btn btn-sm btn-success btn-toggle-status me-1" data-id="'.$teacher->id.'" title="Activate">
                                <i class="fas fa-check"></i>
                            </button>';
                        }
                    }
                    
                    if (auth()->user()->hasAnyRole(['admin', 'manager'])) {
                        // Delete button (hard delete - admin only)
                        $buttons .= '<button class="btn btn-sm btn-danger btn-delete-teacher" data-id="'.$teacher->id.'" title="Delete">
                            <i class="fas fa-trash"></i>
                        </button>';
                    }
                    
                    return $buttons;
                })
                ->rawColumns(['photo_display', 'status_badge', 'actions'])
                ->make(true);
        }

        $departments = Department::active()->orderBy('name')->get(['id', 'name']);
        $positions = Position::active()->orderBy('name')->get(['id', 'name']);
        $employmentTypes = EmploymentType::active()->orderBy('name')->get(['id', 'name']);
        $provinces = \App\Models\Province::orderBy('name_en')->get(['id', 'name_en', 'name_km']);
        
        return view('admin.teachers.index', compact('departments', 'positions', 'employmentTypes', 'provinces'));
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
            $total = Teacher::count();
            $active = Teacher::where('is_active', 1)->count();
            $inactive = Teacher::where('is_active', 0)->count();
            $male = Teacher::where('sex', 'M')->count();
            $female = Teacher::where('sex', 'F')->count();

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
            Log::error('Error fetching teacher stats: ' . $e->getMessage());
            
            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch statistics.'
            ], 500);
        }
    }

    public function store(Request $request)
    {
        if (!$this->canWrite()) {
            return response()->json(['error' => 'Unauthorized. You do not have permission to create teachers.'], 403);
        }

        \Log::info('Teacher store request', [
            'all_data' => $request->except(['photo']),
            'has_photo' => $request->hasFile('photo')
        ]);

        $validator = Validator::make($request->all(), [
            'khmer_name' => 'required|string|max:150',
            'english_name' => 'nullable|string|max:150',
            'dob' => 'nullable|date|before_or_equal:today',
            'sex' => 'required|in:M,F',
            'teacher_code' => 'nullable|string|max:50|unique:teachers,teacher_code',
            'photo' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
            'user_id' => 'nullable|exists:users,id',
            'department_id' => 'nullable|exists:departments,id',
            'position_id' => 'nullable|exists:positions,id',
            'employment_type_id' => 'nullable|exists:employment_types,id',
            'phone' => 'required|string|max:30',
            'phone_note' => 'nullable|string|max:255',
            'birthplace_province_id' => 'nullable|integer|exists:provinces,id',
            'birthplace_district_id' => 'nullable|integer|exists:districts,id',
            'birthplace_commune_id' => 'nullable|integer|exists:communes,id',
            'birthplace_village_id' => 'nullable|integer|exists:villages,id',
            'current_province_id' => 'nullable|integer|exists:provinces,id',
            'current_district_id' => 'nullable|integer|exists:districts,id',
            'current_commune_id' => 'nullable|integer|exists:communes,id',
            'current_village_id' => 'nullable|integer|exists:villages,id',
        ]);

        if ($validator->fails()) {
            \Log::error('Teacher validation failed', [
                'errors' => $validator->errors()->toArray()
            ]);
            
            return response()->json([
                'success' => false,
                'message' => 'Validation failed. Please check the form fields.',
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            DB::beginTransaction();

            $data = $validator->validated();
            
            if ($request->hasFile('photo')) {
                $photoPath = $request->file('photo')->store('teacher_photos', 'public');
                $data['photo'] = $photoPath;
            }

            $data['created_by'] = auth()->id();
            $data['is_active'] = 1;

            $phoneNumber = $data['phone'];
            $phoneNote = $data['phone_note'] ?? null;
            unset($data['phone'], $data['phone_note']);

            $teacher = Teacher::create($data);

            TeacherPhone::create([
                'teacher_id' => $teacher->id,
                'phone' => $phoneNumber,
                'is_primary' => 1,
                'note' => $phoneNote,
                'created_by' => auth()->id(),
                'is_active' => 1,
            ]);

            // Log activity
            $this->logActivity(
                "Teacher created: {$teacher->khmer_name}" . ($teacher->english_name ? " ({$teacher->english_name})" : "") . " - Code: {$teacher->teacher_code}",
                $teacher,
                [
                    'khmer_name' => $teacher->khmer_name,
                    'english_name' => $teacher->english_name,
                    'teacher_code' => $teacher->teacher_code,
                    'dob' => $teacher->dob,
                    'sex' => $teacher->sex,
                    'phone' => $phoneNumber,
                    'department_id' => $teacher->department_id,
                    'position_id' => $teacher->position_id,
                    'employment_type_id' => $teacher->employment_type_id,
                    'photo' => $teacher->photo ? 'Uploaded' : null,
                    'created_by' => auth()->id()
                ],
                'teachers'
            );

            DB::commit();

            $teacher->load(['user', 'department', 'position', 'employmentType', 'phones']);

            return response()->json([
                'success' => true,
                'message' => 'Teacher created successfully.',
                'data' => $teacher
            ], 201);

        } catch (\Exception $e) {
            DB::rollBack();
            
            if (isset($photoPath)) {
                Storage::disk('public')->delete($photoPath);
            }

            \Log::error('Teacher creation failed', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to create teacher: ' . $e->getMessage()
            ], 500);
        }
    }

    public function show($id)
    {
        if (!$this->canRead()) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        $teacher = Teacher::with([
            'user', 
            'department', 
            'position', 
            'employmentType',
            'phones', 
            'creator', 
            'updater'
        ])->find($id);

        if (!$teacher) {
            return response()->json([
                'success' => false,
                'message' => 'Teacher not found.'
            ], 404);
        }

        return response()->json([
            'success' => true,
            'data' => $teacher
        ]);
    }

    public function update(Request $request, $id)
    {
        if (!$this->canWrite()) {
            return response()->json(['error' => 'Unauthorized. You do not have permission to update teachers.'], 403);
        }

        $teacher = Teacher::find($id);

        if (!$teacher) {
            return response()->json([
                'success' => false,
                'message' => 'Teacher not found.'
            ], 404);
        }

        \Log::info('Teacher update request', [
            'id' => $id,
            'all_data' => $request->except(['photo']),
            'has_photo' => $request->hasFile('photo')
        ]);

        $validator = Validator::make($request->all(), [
            'khmer_name' => 'required|string|max:150',
            'english_name' => 'nullable|string|max:150',
            'dob' => 'nullable|date|before_or_equal:today',
            'sex' => 'required|in:M,F',
            'teacher_code' => 'nullable|string|max:50|unique:teachers,teacher_code,' . $id,
            'photo' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
            'user_id' => 'nullable|exists:users,id',
            'department_id' => 'nullable|exists:departments,id',
            'position_id' => 'nullable|exists:positions,id',
            'employment_type_id' => 'nullable|exists:employment_types,id',
            'phone' => 'required|string|max:30',
            'phone_note' => 'nullable|string|max:255',
            'birthplace_province_id' => 'nullable|integer|exists:provinces,id',
            'birthplace_district_id' => 'nullable|integer|exists:districts,id',
            'birthplace_commune_id' => 'nullable|integer|exists:communes,id',
            'birthplace_village_id' => 'nullable|integer|exists:villages,id',
            'current_province_id' => 'nullable|integer|exists:provinces,id',
            'current_district_id' => 'nullable|integer|exists:districts,id',
            'current_commune_id' => 'nullable|integer|exists:communes,id',
            'current_village_id' => 'nullable|integer|exists:villages,id',
        ]);

        if ($validator->fails()) {
            \Log::error('Teacher validation failed', [
                'errors' => $validator->errors()->toArray()
            ]);
            
            return response()->json([
                'success' => false,
                'message' => 'Validation failed. Please check the form fields.',
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            DB::beginTransaction();

            $data = $validator->validated();
            
            if ($request->hasFile('photo')) {
                if ($teacher->photo) {
                    Storage::disk('public')->delete($teacher->photo);
                }
                $photoPath = $request->file('photo')->store('teacher_photos', 'public');
                $data['photo'] = $photoPath;
            }

            // Store old values for logging
            $oldAttributes = [
                'khmer_name' => $teacher->khmer_name,
                'english_name' => $teacher->english_name,
                'teacher_code' => $teacher->teacher_code,
                'dob' => $teacher->dob,
                'sex' => $teacher->sex,
                'user_id' => $teacher->user_id,
                'department_id' => $teacher->department_id,
                'position_id' => $teacher->position_id,
                'employment_type_id' => $teacher->employment_type_id,
                'photo' => $teacher->photo,
                'is_active' => $teacher->is_active
            ];

            // Get old phone
            $oldPhone = $teacher->phones()->where('is_primary', 1)->first();
            $oldPhoneNumber = $oldPhone ? $oldPhone->phone : null;

            $data['updated_by'] = auth()->id();

            $phoneNumber = $data['phone'];
            $phoneNote = $data['phone_note'] ?? null;
            unset($data['phone'], $data['phone_note']);

            $teacher->update($data);

            $primaryPhone = $teacher->phones()->where('is_primary', 1)->first();
            if ($primaryPhone) {
                $primaryPhone->update([
                    'phone' => $phoneNumber,
                    'note' => $phoneNote,
                    'updated_by' => auth()->id(),
                ]);
            } else {
                TeacherPhone::create([
                    'teacher_id' => $teacher->id,
                    'phone' => $phoneNumber,
                    'is_primary' => 1,
                    'note' => $phoneNote,
                    'created_by' => auth()->id(),
                    'is_active' => 1,
                ]);
            }

            // Prepare new attributes for logging
            $newAttributes = [
                'khmer_name' => $teacher->khmer_name,
                'english_name' => $teacher->english_name,
                'teacher_code' => $teacher->teacher_code,
                'dob' => $teacher->dob,
                'sex' => $teacher->sex,
                'user_id' => $teacher->user_id,
                'department_id' => $teacher->department_id,
                'position_id' => $teacher->position_id,
                'employment_type_id' => $teacher->employment_type_id,
                'photo' => $teacher->photo,
                'is_active' => $teacher->is_active,
                'phone' => $phoneNumber
            ];

            // Add phone change to old attributes if changed
            if ($oldPhoneNumber !== $phoneNumber) {
                $oldAttributes['phone'] = $oldPhoneNumber;
            }

            // Log activity with old and new values
            $this->logActivityUpdate(
                "Teacher updated: {$teacher->khmer_name}" . ($teacher->english_name ? " ({$teacher->english_name})" : "") . " - Code: {$teacher->teacher_code}",
                $teacher,
                $oldAttributes,
                $newAttributes,
                'teachers'
            );

            DB::commit();

            $teacher->load(['user', 'department', 'position', 'employmentType', 'phones']);

            return response()->json([
                'success' => true,
                'message' => 'Teacher updated successfully.',
                'data' => $teacher
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            
            if (isset($photoPath) && $photoPath !== $teacher->photo) {
                Storage::disk('public')->delete($photoPath);
            }

            \Log::error('Teacher update failed', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to update teacher: ' . $e->getMessage()
            ], 500);
        }
    }

    public function destroy($id)
    {
        if (!$this->canDelete()) {
            return response()->json(['error' => 'Unauthorized. You do not have permission to delete teachers.'], 403);
        }

        $teacher = Teacher::find($id);

        if (!$teacher) {
            return response()->json([
                'success' => false,
                'message' => 'Teacher not found.'
            ], 404);
        }

        try {
            DB::beginTransaction();

            // Store old status
            $oldIsActive = $teacher->is_active;

            $teacher->phones()->update(['is_active' => 0, 'updated_by' => auth()->id()]);
            $teacher->update(['is_active' => 0, 'updated_by' => auth()->id()]);

            // Log activity
            $this->logActivity(
                "Teacher deactivated: {$teacher->khmer_name}" . ($teacher->english_name ? " ({$teacher->english_name})" : "") . " - Code: {$teacher->teacher_code}",
                $teacher,
                [
                    'old_status' => $oldIsActive ? 'Active' : 'Inactive',
                    'new_status' => 'Inactive',
                    'teacher_id' => $teacher->id,
                    'teacher_code' => $teacher->teacher_code
                ],
                'teachers'
            );

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Teacher deactivated successfully.'
            ]);

        } catch (\Exception $e) {
            DB::rollBack();

            \Log::error('Teacher deletion failed', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to deactivate teacher: ' . $e->getMessage()
            ], 500);
        }
    }

    public function toggleStatus($id)
    {
        if (!$this->canWrite()) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        $teacher = Teacher::find($id);

        if (!$teacher) {
            return response()->json([
                'success' => false,
                'message' => 'Teacher not found.'
            ], 404);
        }

        try {
            $teacher->is_active = !$teacher->is_active;
            $teacher->updated_by = auth()->id();
            $teacher->save();

            return response()->json([
                'success' => true,
                'message' => 'Teacher status updated successfully.',
                'data' => $teacher
            ]);

        } catch (\Exception $e) {
            \Log::error('Teacher status toggle failed', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to update teacher status: ' . $e->getMessage()
            ], 500);
        }
    }

    public function forceDelete($id)
    {
        if (!auth()->user()->hasRole('admin')) {
            return response()->json(['error' => 'Only admins can permanently delete teachers.'], 403);
        }

        $teacher = Teacher::find($id);

        if (!$teacher) {
            return response()->json([
                'success' => false,
                'message' => 'Teacher not found.'
            ], 404);
        }

        try {
            DB::beginTransaction();

            // Store teacher info for logging before deletion
            $teacherInfo = [
                'id' => $teacher->id,
                'khmer_name' => $teacher->khmer_name,
                'english_name' => $teacher->english_name,
                'teacher_code' => $teacher->teacher_code,
                'dob' => $teacher->dob,
                'sex' => $teacher->sex,
                'photo' => $teacher->photo ? 'Had photo' : null
            ];

            $teacher->phones()->delete();
            
            if ($teacher->photo) {
                Storage::disk('public')->delete($teacher->photo);
            }
            
            $teacher->delete();

            // Log activity (after deletion, so we can't use $teacher as subject)
            $this->logActivity(
                "Teacher permanently deleted: {$teacherInfo['khmer_name']}" . ($teacherInfo['english_name'] ? " ({$teacherInfo['english_name']})" : "") . " - Code: {$teacherInfo['teacher_code']}",
                null, // No subject since it's deleted
                [
                    'deleted_teacher' => $teacherInfo,
                    'deleted_by' => auth()->id(),
                    'deleted_at' => now()->toDateTimeString()
                ],
                'teachers'
            );

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Teacher permanently deleted successfully.'
            ]);

        } catch (\Exception $e) {
            DB::rollBack();

            \Log::error('Teacher force delete failed', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to permanently delete teacher: ' . $e->getMessage()
            ], 500);
        }
    }

    public function getPhones($id)
    {
        if (!$this->canRead()) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        $teacher = Teacher::find($id);

        if (!$teacher) {
            return response()->json([
                'success' => false,
                'message' => 'Teacher not found.'
            ], 404);
        }

        $phones = $teacher->phones()->orderBy('is_primary', 'desc')->get();

        return response()->json([
            'success' => true,
            'data' => $phones
        ]);
    }

    public function addPhone(Request $request, $id)
    {
        if (!$this->canWrite()) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        $teacher = Teacher::find($id);

        if (!$teacher) {
            return response()->json([
                'success' => false,
                'message' => 'Teacher not found.'
            ], 404);
        }

        $validator = Validator::make($request->all(), [
            'phone' => 'required|string|max:30',
            'note' => 'nullable|string|max:255',
            'is_primary' => 'boolean'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            DB::beginTransaction();

            $isPrimary = $request->input('is_primary', false);

            if ($isPrimary) {
                $teacher->phones()->update(['is_primary' => 0]);
            }

            TeacherPhone::create([
                'teacher_id' => $teacher->id,
                'phone' => $request->phone,
                'note' => $request->note,
                'is_primary' => $isPrimary,
                'created_by' => auth()->id(),
                'is_active' => 1
            ]);

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Phone number added successfully.'
            ]);

        } catch (\Exception $e) {
            DB::rollBack();

            \Log::error('Add phone failed', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to add phone: ' . $e->getMessage()
            ], 500);
        }
    }

    public function updatePhone(Request $request, $teacherId, $phoneId)
    {
        if (!$this->canWrite()) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        $teacher = Teacher::find($teacherId);

        if (!$teacher) {
            return response()->json([
                'success' => false,
                'message' => 'Teacher not found.'
            ], 404);
        }

        $phone = TeacherPhone::where('teacher_id', $teacherId)->where('id', $phoneId)->first();

        if (!$phone) {
            return response()->json([
                'success' => false,
                'message' => 'Phone not found.'
            ], 404);
        }

        $validator = Validator::make($request->all(), [
            'phone' => 'required|string|max:30',
            'note' => 'nullable|string|max:255',
            'is_primary' => 'boolean'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            DB::beginTransaction();

            $isPrimary = $request->input('is_primary', false);

            if ($isPrimary && !$phone->is_primary) {
                $teacher->phones()->update(['is_primary' => 0]);
            }

            $phone->update([
                'phone' => $request->phone,
                'note' => $request->note,
                'is_primary' => $isPrimary,
                'updated_by' => auth()->id()
            ]);

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Phone number updated successfully.'
            ]);

        } catch (\Exception $e) {
            DB::rollBack();

            \Log::error('Update phone failed', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to update phone: ' . $e->getMessage()
            ], 500);
        }
    }

    public function deletePhone($teacherId, $phoneId)
    {
        if (!$this->canWrite()) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        $teacher = Teacher::find($teacherId);

        if (!$teacher) {
            return response()->json([
                'success' => false,
                'message' => 'Teacher not found.'
            ], 404);
        }

        $phone = TeacherPhone::where('teacher_id', $teacherId)->where('id', $phoneId)->first();

        if (!$phone) {
            return response()->json([
                'success' => false,
                'message' => 'Phone not found.'
            ], 404);
        }

        if ($phone->is_primary) {
            return response()->json([
                'success' => false,
                'message' => 'Cannot delete primary phone. Please set another phone as primary first.'
            ], 400);
        }

        try {
            $phone->delete();

            return response()->json([
                'success' => true,
                'message' => 'Phone number deleted successfully.'
            ]);

        } catch (\Exception $e) {
            \Log::error('Delete phone failed', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to delete phone: ' . $e->getMessage()
            ], 500);
        }
    }

    public function setPrimaryPhone($teacherId, $phoneId)
    {
        if (!$this->canWrite()) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        $teacher = Teacher::find($teacherId);

        if (!$teacher) {
            return response()->json([
                'success' => false,
                'message' => 'Teacher not found.'
            ], 404);
        }

        $phone = TeacherPhone::where('teacher_id', $teacherId)->where('id', $phoneId)->first();

        if (!$phone) {
            return response()->json([
                'success' => false,
                'message' => 'Phone not found.'
            ], 404);
        }

        try {
            DB::beginTransaction();

            $teacher->phones()->update(['is_primary' => 0]);
            $phone->update([
                'is_primary' => 1,
                'updated_by' => auth()->id()
            ]);

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Primary phone updated successfully.'
            ]);

        } catch (\Exception $e) {
            DB::rollBack();

            \Log::error('Set primary phone failed', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to set primary phone: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Search provinces for Select2 dropdown (AJAX)
     */
    public function searchProvinces(Request $request)
    {
        $query = $request->get('q', '');
        $page = $request->get('page', 1);
        $perPage = 20;

        try {
            $provincesQuery = \App\Models\Province::query();

            // Filter by search term
            if (!empty($query)) {
                $provincesQuery->where(function ($q) use ($query) {
                    $q->where('name_en', 'like', "%{$query}%")
                      ->orWhere('name_km', 'like', "%{$query}%");
                });
            }

            // Paginate results
            $provinces = $provincesQuery->orderBy('name_en')
                ->skip(($page - 1) * $perPage)
                ->take($perPage + 1) // Take one extra to check if there are more
                ->get(['id', 'name_en', 'name_km']);

            // Check if there are more results
            $hasMore = $provinces->count() > $perPage;
            if ($hasMore) {
                $provinces = $provinces->take($perPage);
            }

            // Format results for Select2
            $results = $provinces->map(function ($province) {
                $label = $province->name_km ?? $province->name_en;
                return [
                    'id' => $province->id,
                    'text' => $label,
                    'name_en' => $province->name_en,
                    'name_km' => $province->name_km
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
            Log::error('Error searching provinces: ' . $e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Failed to search provinces.',
                'results' => []
            ], 500);
        }
    }

    /**
     * Get districts by province (for teacher form)
     */
    public function getDistricts(Request $request)
    {
        $provinceId = $request->get('province_id');
        
        if (!$provinceId) {
            return response()->json([
                'ok' => false,
                'message' => 'Province ID is required'
            ], 400);
        }

        $districts = District::where('province_id', $provinceId)
            ->orderBy('name_en')
            ->get(['id', 'name_en', 'name_km']);

        return response()->json([
            'ok' => true,
            'data' => $districts
        ]);
    }

    /**
     * Get communes by district (for teacher form)
     */
    public function getCommunes(Request $request)
    {
        $districtId = $request->get('district_id');
        
        if (!$districtId) {
            return response()->json([
                'ok' => false,
                'message' => 'District ID is required'
            ], 400);
        }

        $communes = Commune::where('district_id', $districtId)
            ->orderBy('name_en')
            ->get(['id', 'name_en', 'name_km']);

        return response()->json([
            'ok' => true,
            'data' => $communes
        ]);
    }

    /**
     * Get villages by commune (for teacher form)
     */
    public function getVillages(Request $request)
    {
        $communeId = $request->get('commune_id');
        
        if (!$communeId) {
            return response()->json([
                'ok' => false,
                'message' => 'Commune ID is required'
            ], 400);
        }

        $villages = Village::where('commune_id', $communeId)
            ->orderBy('name_en')
            ->get(['id', 'name_en', 'name_km']);

        return response()->json([
            'ok' => true,
            'data' => $villages
        ]);
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
            $usersQuery = User::where('is_active', 1);

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
