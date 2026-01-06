<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Student;
use App\Models\StudentPhone;
use App\Models\User;
use App\Models\District;
use App\Models\Commune;
use App\Models\Village;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;

class StudentController extends Controller
{
    /**
     * Check if the authenticated user has permission to write (create/update/delete).
     */
    private function canWrite(): bool
    {
        return auth()->user()->hasAnyRole(['admin', 'manager', 'staff']);
    }

    /**
     * Check if the authenticated user has permission to read.
     */
    private function canRead(): bool
    {
        return auth()->user()->hasAnyRole(['admin', 'manager', 'principal', 'staff']);
    }

    /**
     * Check if the authenticated user has permission to delete.
     */
    private function canDelete(): bool
    {
        return auth()->user()->hasAnyRole(['admin', 'manager']);
    }

    /**
     * Display a listing of students.
     * If Ajax request: return JSON
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

        // Handle Ajax request
        if ($request->ajax()) {
            $query = Student::with(['user', 'phones' => function($q) {
                $q->where('is_active', 1);
            }]);

            // Apply active/inactive filter
            if ($request->has('status')) {
                if ($request->status === 'active') {
                    $query->where('is_active', 1);
                } elseif ($request->status === 'inactive') {
                    $query->where('is_active', 0);
                }
                // If status is 'all', don't filter by is_active (show all students)
            }
            // No default filter - show all students by default

            // Search functionality
            if ($request->has('search') && !empty($request->search)) {
                $search = $request->search;
                $query->where(function($q) use ($search) {
                    $q->where('khmer_name', 'like', "%{$search}%")
                      ->orWhere('english_name', 'like', "%{$search}%")
                      ->orWhere('code', 'like', "%{$search}%");
                });
            }

            // Sorting
            $sortBy = $request->get('sort_by', 'created_at');
            $sortOrder = $request->get('sort_order', 'desc');
            $query->orderBy($sortBy, $sortOrder);

            // Pagination
            $perPage = $request->get('per_page', 15);
            $students = $query->paginate($perPage);

            return response()->json([
                'success' => true,
                'data' => $students->items(),
                'pagination' => [
                    'current_page' => $students->currentPage(),
                    'last_page' => $students->lastPage(),
                    'per_page' => $students->perPage(),
                    'total' => $students->total(),
                ],
            ]);
        }

        // Return Blade view for non-Ajax requests
        $provinces = \App\Models\Province::orderBy('name_en')->get(['id', 'name_en', 'name_km']);
        return view('admin.students.index', compact('provinces'));
    }

    /**
     * Store a newly created student in storage.
     */
    public function store(Request $request)
    {
        // Check write permission
        if (!$this->canWrite()) {
            return response()->json(['error' => 'Unauthorized. You do not have permission to create students.'], 403);
        }

        // Log incoming request for debugging
        \Log::info('Student store request', [
            'all_data' => $request->except(['photo']),
            'has_photo' => $request->hasFile('photo')
        ]);

        // Validate input
        $validator = Validator::make($request->all(), [
            'khmer_name' => 'required|string|max:255',
            'english_name' => 'required|string|max:255',
            'dob' => 'required|date|before_or_equal:today',
            'sex' => 'required|in:M,F',
            'code' => 'nullable|string|max:100|unique:students,code',
            'note' => 'nullable|string',
            'photo' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
            'user_id' => 'nullable|exists:users,id',
            
            // Location fields
            'birthplace_province_id' => 'nullable|integer',
            'birthplace_district_id' => 'nullable|integer',
            'birthplace_commune_id' => 'nullable|integer',
            'birthplace_village_id' => 'nullable|integer',
            'current_province_id' => 'nullable|integer',
            'current_district_id' => 'nullable|integer',
            'current_commune_id' => 'nullable|integer',
            'current_village_id' => 'nullable|integer',
            
            // Phone validation
            'phone' => 'required|string|max:30',
            'phone_note' => 'nullable|string|max:255',
        ]);

        if ($validator->fails()) {
            \Log::error('Student validation failed', [
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
            
            // Handle photo upload
            if ($request->hasFile('photo')) {
                $photoPath = $request->file('photo')->store('student_photos', 'public');
                $data['photo'] = $photoPath;
            }

            // Set audit fields
            $data['created_by'] = auth()->id();
            $data['is_active'] = 1;

            // Remove phone fields from student data
            $phoneNumber = $data['phone'];
            $phoneNote = $data['phone_note'] ?? null;
            unset($data['phone'], $data['phone_note']);

            // Create student
            $student = Student::create($data);

            // Create primary phone
            StudentPhone::create([
                'student_id' => $student->id,
                'phone' => $phoneNumber,
                'is_primary' => 1,
                'note' => $phoneNote,
                'created_by' => auth()->id(),
                'is_active' => 1,
            ]);

            DB::commit();

            // Load relationships for response
            $student->load(['user', 'phones']);

            return response()->json([
                'success' => true,
                'message' => 'Student created successfully.',
                'data' => $student
            ], 201);

        } catch (\Exception $e) {
            DB::rollBack();
            
            // Delete uploaded photo if exists
            if (isset($photoPath)) {
                Storage::disk('public')->delete($photoPath);
            }

            \Log::error('Student creation failed', [
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
     * Display the specified student.
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
     * Update the specified student in storage.
     */
    public function update(Request $request, $id)
    {
        // Check write permission
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

        // Validate input
        $validator = Validator::make($request->all(), [
            'khmer_name' => 'required|string|max:255',
            'english_name' => 'required|string|max:255',
            'dob' => 'required|date|before_or_equal:today',
            'sex' => 'required|in:M,F',
            'code' => 'nullable|string|max:100|unique:students,code,' . $id,
            'note' => 'nullable|string',
            'photo' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
            'user_id' => 'nullable|exists:users,id',
            
            // Location fields
            'birthplace_province_id' => 'nullable|integer',
            'birthplace_district_id' => 'nullable|integer',
            'birthplace_commune_id' => 'nullable|integer',
            'birthplace_village_id' => 'nullable|integer',
            'current_province_id' => 'nullable|integer',
            'current_district_id' => 'nullable|integer',
            'current_commune_id' => 'nullable|integer',
            'current_village_id' => 'nullable|integer',
            
            // Phone validation
            'phone' => 'required|string|max:30',
            'phone_note' => 'nullable|string|max:255',
            
            // Status
            'is_active' => 'sometimes|boolean',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            DB::beginTransaction();

            $data = $validator->validated();
            
            // Handle photo upload
            if ($request->hasFile('photo')) {
                // Delete old photo
                if ($student->photo) {
                    Storage::disk('public')->delete($student->photo);
                }
                
                $photoPath = $request->file('photo')->store('student_photos', 'public');
                $data['photo'] = $photoPath;
            }

            // Set audit field
            $data['updated_by'] = auth()->id();

            // Remove phone fields from student data
            $phoneNumber = $data['phone'];
            $phoneNote = $data['phone_note'] ?? null;
            unset($data['phone'], $data['phone_note']);

            // Update student
            $student->update($data);

            // Update or create primary phone
            $primaryPhone = $student->phones()->where('is_primary', 1)->first();
            
            if ($primaryPhone) {
                $primaryPhone->update([
                    'phone' => $phoneNumber,
                    'note' => $phoneNote,
                    'updated_by' => auth()->id(),
                ]);
            } else {
                StudentPhone::create([
                    'student_id' => $student->id,
                    'phone' => $phoneNumber,
                    'is_primary' => 1,
                    'note' => $phoneNote,
                    'created_by' => auth()->id(),
                    'is_active' => 1,
                ]);
            }

            DB::commit();

            // Load relationships for response
            $student->load(['user', 'phones']);

            return response()->json([
                'success' => true,
                'message' => 'Student updated successfully.',
                'data' => $student
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            
            return response()->json([
                'success' => false,
                'message' => 'Failed to update student: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Soft delete the specified student (set is_active = 0).
     */
    public function destroy($id)
    {
        // Check delete permission
        if (!$this->canDelete()) {
            return response()->json(['error' => 'Unauthorized. Only admin and manager can delete students.'], 403);
        }

        $student = Student::find($id);

        if (!$student) {
            return response()->json([
                'success' => false,
                'message' => 'Student not found.'
            ], 404);
        }

        try {
            // Soft delete by setting is_active = 0
            $student->update([
                'is_active' => 0,
                'updated_by' => auth()->id(),
            ]);

            // Also deactivate all phone numbers
            $student->phones()->update([
                'is_active' => 0,
                'updated_by' => auth()->id(),
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Student deactivated successfully.'
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to deactivate student: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Restore a soft-deleted student (set is_active = 1).
     */
    public function restore($id)
    {
        // Check write permission
        if (!$this->canWrite()) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        $student = Student::find($id);

        if (!$student) {
            return response()->json([
                'success' => false,
                'message' => 'Student not found.'
            ], 404);
        }

        try {
            $student->update([
                'is_active' => 1,
                'updated_by' => auth()->id(),
            ]);

            // Also restore phone numbers
            $student->phones()->update([
                'is_active' => 1,
                'updated_by' => auth()->id(),
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Student restored successfully.'
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to restore student: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Toggle student status (is_active).
     */
    public function toggleStatus($id)
    {
        if (!$this->canWrite()) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        $student = Student::find($id);

        if (!$student) {
            return response()->json([
                'success' => false,
                'message' => 'Student not found.'
            ], 404);
        }

        try {
            $newStatus = $student->is_active ? 0 : 1;
            
            $student->update([
                'is_active' => $newStatus,
                'updated_by' => auth()->id(),
            ]);

            return response()->json([
                'success' => true,
                'message' => $newStatus ? 'Student activated successfully.' : 'Student deactivated successfully.',
                'is_active' => $newStatus
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to toggle status: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Permanently delete student from database (Hard Delete).
     * ADMIN ONLY - This action cannot be undone.
     */
    public function forceDelete($id)
    {
        // Check if user is ADMIN (only admin can hard delete)
        if (!auth()->user()->hasRole('admin')) {
            return response()->json([
                'error' => 'Unauthorized. Only administrators can permanently delete students.'
            ], 403);
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

            // Store student info for logging
            $studentName = $student->english_name;
            $studentCode = $student->code;

            // Delete associated photo if exists
            if ($student->photo) {
                Storage::disk('public')->delete($student->photo);
            }

            // Delete all phone numbers (cascade will handle this, but explicit for clarity)
            $student->phones()->delete();

            // Permanently delete the student record from database
            $student->delete();

            DB::commit();

            \Log::warning('Student permanently deleted', [
                'student_id' => $id,
                'student_name' => $studentName,
                'student_code' => $studentCode,
                'deleted_by' => auth()->id(),
                'deleted_by_name' => auth()->user()->name,
                'deleted_at' => now()
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Student permanently deleted from database.'
            ]);

        } catch (\Exception $e) {
            DB::rollBack();

            \Log::error('Failed to permanently delete student', [
                'student_id' => $id,
                'error' => $e->getMessage(),
                'admin_id' => auth()->id()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to permanently delete student: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get all phone numbers for a student.
     */
    public function getPhones($id)
    {
        if (!$this->canRead()) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        $student = Student::with(['phones' => function($q) {
            $q->orderBy('is_primary', 'desc')->orderBy('created_at', 'asc');
        }])->find($id);

        if (!$student) {
            return response()->json([
                'success' => false,
                'message' => 'Student not found.'
            ], 404);
        }

        return response()->json([
            'success' => true,
            'data' => $student->phones
        ]);
    }

    /**
     * Add a new phone number for a student.
     */
    public function addPhone(Request $request, $id)
    {
        if (!$this->canWrite()) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        $student = Student::find($id);

        if (!$student) {
            return response()->json([
                'success' => false,
                'message' => 'Student not found.'
            ], 404);
        }

        $validator = Validator::make($request->all(), [
            'phone' => 'required|string|max:30',
            'note' => 'nullable|string|max:255',
            'is_primary' => 'boolean',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            DB::beginTransaction();

            $data = $validator->validated();
            $data['student_id'] = $student->id;
            $data['created_by'] = auth()->id();
            $data['is_active'] = 1;

            // If setting as primary, unset other primary phones
            if (!empty($data['is_primary'])) {
                $student->phones()->update(['is_primary' => 0, 'updated_by' => auth()->id()]);
            }

            $phone = StudentPhone::create($data);

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Phone number added successfully.',
                'data' => $phone
            ], 201);

        } catch (\Exception $e) {
            DB::rollBack();

            return response()->json([
                'success' => false,
                'message' => 'Failed to add phone: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Update a phone number.
     */
    public function updatePhone(Request $request, $studentId, $phoneId)
    {
        if (!$this->canWrite()) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        $phone = StudentPhone::where('student_id', $studentId)->find($phoneId);

        if (!$phone) {
            return response()->json([
                'success' => false,
                'message' => 'Phone number not found.'
            ], 404);
        }

        $validator = Validator::make($request->all(), [
            'phone' => 'required|string|max:30',
            'note' => 'nullable|string|max:255',
            'is_primary' => 'boolean',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            DB::beginTransaction();

            $data = $validator->validated();
            $data['updated_by'] = auth()->id();

            // If setting as primary, unset other primary phones
            if (!empty($data['is_primary']) && !$phone->is_primary) {
                StudentPhone::where('student_id', $studentId)
                    ->where('id', '!=', $phoneId)
                    ->update(['is_primary' => 0, 'updated_by' => auth()->id()]);
            }

            $phone->update($data);

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Phone number updated successfully.',
                'data' => $phone
            ]);

        } catch (\Exception $e) {
            DB::rollBack();

            return response()->json([
                'success' => false,
                'message' => 'Failed to update phone: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Delete a phone number.
     */
    public function deletePhone($studentId, $phoneId)
    {
        if (!$this->canWrite()) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        $phone = StudentPhone::where('student_id', $studentId)->find($phoneId);

        if (!$phone) {
            return response()->json([
                'success' => false,
                'message' => 'Phone number not found.'
            ], 404);
        }

        try {
            // Soft delete by setting is_active = 0
            $phone->update([
                'is_active' => 0,
                'updated_by' => auth()->id(),
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Phone number deleted successfully.'
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to delete phone: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Set a phone number as primary.
     */
    public function setPrimaryPhone($studentId, $phoneId)
    {
        if (!$this->canWrite()) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        $phone = StudentPhone::where('student_id', $studentId)->find($phoneId);

        if (!$phone) {
            return response()->json([
                'success' => false,
                'message' => 'Phone number not found.'
            ], 404);
        }

        try {
            DB::beginTransaction();

            // Unset all primary phones for this student
            StudentPhone::where('student_id', $studentId)
                ->update(['is_primary' => 0, 'updated_by' => auth()->id()]);

            // Set this phone as primary
            $phone->update([
                'is_primary' => 1,
                'updated_by' => auth()->id(),
            ]);

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Primary phone set successfully.',
                'data' => $phone
            ]);

        } catch (\Exception $e) {
            DB::rollBack();

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
            \Illuminate\Support\Facades\Log::error('Error searching provinces: ' . $e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Failed to search provinces.',
                'results' => []
            ], 500);
        }
    }

    /**
     * Get districts by province (for student form)
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
     * Get communes by district (for student form)
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
     * Get villages by commune (for student form)
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
}
