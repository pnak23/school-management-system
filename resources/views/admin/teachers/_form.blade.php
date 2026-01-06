{{-- Create/Edit Teacher Modal --}}
<div class="modal fade" id="teacherModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header bg-primary text-white">
                <h5 class="modal-title" id="modalTitle">Add Teacher</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <form id="teacherForm" enctype="multipart/form-data">
                    @csrf
                    <input type="hidden" id="teacherId" name="teacher_id">
                    
                    <div id="formErrors" class="alert alert-danger d-none"></div>

                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="khmer_name" class="form-label">Khmer Name <span class="text-danger">*</span></label>
                            <input type="text" id="khmer_name" name="khmer_name" class="form-control" required>
                            <p id="khmer_nameError" class="text-danger small mt-1 d-none"></p>
                        </div>

                        <div class="col-md-6 mb-3">
                            <label for="english_name" class="form-label">English Name</label>
                            <input type="text" id="english_name" name="english_name" class="form-control">
                            <p id="english_nameError" class="text-danger small mt-1 d-none"></p>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-4 mb-3">
                            <label for="teacher_code" class="form-label">Teacher Code</label>
                            <input type="text" id="teacher_code" name="teacher_code" class="form-control">
                            <p id="teacher_codeError" class="text-danger small mt-1 d-none"></p>
                        </div>

                        <div class="col-md-4 mb-3">
                            <label for="dob" class="form-label">Date of Birth</label>
                            <input type="date" id="dob" name="dob" class="form-control">
                            <p id="dobError" class="text-danger small mt-1 d-none"></p>
                        </div>

                        <div class="col-md-4 mb-3">
                            <label for="sex" class="form-label">Sex <span class="text-danger">*</span></label>
                            <select id="sex" name="sex" class="form-select" required>
                                <option value="">Select Sex</option>
                                <option value="M">Male</option>
                                <option value="F">Female</option>
                            </select>
                            <p id="sexError" class="text-danger small mt-1 d-none"></p>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="phone" class="form-label">Phone Number <span class="text-danger">*</span></label>
                            <div class="input-group">
                                <input type="text" id="phone" name="phone" class="form-control" required>
                                <button type="button" id="managePhonesBtn" class="btn btn-info d-none" onclick="openManagePhonesModal()">
                                    <i class="fas fa-phone"></i> Manage
                                </button>
                            </div>
                            <small class="text-muted">Primary phone (required). Click "Manage" after saving to add more.</small>
                            <p id="phoneError" class="text-danger small mt-1 d-none"></p>
                        </div>

                        <div class="col-md-6 mb-3">
                            <label for="phone_note" class="form-label">Phone Note</label>
                            <input type="text" id="phone_note" name="phone_note" class="form-control">
                            <p id="phone_noteError" class="text-danger small mt-1 d-none"></p>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-4 mb-3">
                            <label for="department_id" class="form-label">Department</label>
                            <select id="department_id" name="department_id" class="form-select">
                                <option value="">Select Department</option>
                                @foreach($departments as $dept)
                                    <option value="{{ $dept->id }}">{{ $dept->name }}</option>
                                @endforeach
                            </select>
                            <p id="department_idError" class="text-danger small mt-1 d-none"></p>
                        </div>

                        <div class="col-md-4 mb-3">
                            <label for="position_id" class="form-label">Position</label>
                            <select id="position_id" name="position_id" class="form-select">
                                <option value="">Select Position</option>
                                @foreach($positions as $pos)
                                    <option value="{{ $pos->id }}">{{ $pos->name }}</option>
                                @endforeach
                            </select>
                            <p id="position_idError" class="text-danger small mt-1 d-none"></p>
                        </div>

                        <div class="col-md-4 mb-3">
                            <label for="employment_type_id" class="form-label">Employment Type</label>
                            <select id="employment_type_id" name="employment_type_id" class="form-select">
                                <option value="">Select Employment Type</option>
                                @foreach($employmentTypes as $type)
                                    <option value="{{ $type->id }}">{{ $type->name }}</option>
                                @endforeach
                            </select>
                            <p id="employment_type_idError" class="text-danger small mt-1 d-none"></p>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="user_id" class="form-label">Linked User</label>
                            <select id="user_id" name="user_id" class="form-select">
                                <option value="">None</option>
                            </select>
                            <small class="text-muted">Search for a user by name or email</small>
                            <p id="user_idError" class="text-danger small mt-1 d-none"></p>
                        </div>

                        <div class="col-md-6 mb-3">
                            <label for="photo" class="form-label">Photo</label>
                            <input type="file" id="photo" name="photo" class="form-control" accept="image/*" onchange="previewPhoto(event)">
                            <p id="photoError" class="text-danger small mt-1 d-none"></p>
                            <div id="photoPreview" class="mt-2"></div>
                        </div>
                    </div>

                    <div class="mb-3">
                        <label for="note" class="form-label">Note</label>
                        <textarea id="note" name="note" class="form-control" rows="3"></textarea>
                        <p id="noteError" class="text-danger small mt-1 d-none"></p>
                    </div>

                    <hr class="my-4">
                    <h6 class="mb-3"><i class="fas fa-map-marker-alt"></i> Birthplace</h6>
                    
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="birthplace_province_id" class="form-label">Birthplace Province (KH)</label>
                            <select name="birthplace_province_id" id="birthplace_province_id" class="form-select">
                                <option value="">Select province</option>
                            </select>
                            <small class="text-muted">Search for a province</small>
                            <p id="birthplace_province_idError" class="text-danger small mt-1 d-none"></p>
                        </div>

                        <div class="col-md-6 mb-3">
                            <label for="birthplace_district_id" class="form-label">Birthplace District (KH)</label>
                            <select name="birthplace_district_id" id="birthplace_district_id" class="form-select">
                                <option value="">Select district</option>
                            </select>
                            <p id="birthplace_district_idError" class="text-danger small mt-1 d-none"></p>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="birthplace_commune_id" class="form-label">Birthplace Commune (KH)</label>
                            <select name="birthplace_commune_id" id="birthplace_commune_id" class="form-select">
                                <option value="">Select commune</option>
                            </select>
                            <p id="birthplace_commune_idError" class="text-danger small mt-1 d-none"></p>
                        </div>

                        <div class="col-md-6 mb-3">
                            <label for="birthplace_village_id" class="form-label">Birthplace Village (KH)</label>
                            <select name="birthplace_village_id" id="birthplace_village_id" class="form-select">
                                <option value="">Select village</option>
                            </select>
                            <p id="birthplace_village_idError" class="text-danger small mt-1 d-none"></p>
                        </div>
                    </div>

                    <hr class="my-4">
                    <h6 class="mb-3"><i class="fas fa-home"></i> Current Address</h6>
                    
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="current_province_id" class="form-label">Current Province (KH)</label>
                            <select name="current_province_id" id="current_province_id" class="form-select">
                                <option value="">Select province</option>
                            </select>
                            <small class="text-muted">Search for a province</small>
                            <p id="current_province_idError" class="text-danger small mt-1 d-none"></p>
                        </div>

                        <div class="col-md-6 mb-3">
                            <label for="current_district_id" class="form-label">Current District (KH)</label>
                            <select name="current_district_id" id="current_district_id" class="form-select">
                                <option value="">Select district</option>
                            </select>
                            <p id="current_district_idError" class="text-danger small mt-1 d-none"></p>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="current_commune_id" class="form-label">Current Commune (KH)</label>
                            <select name="current_commune_id" id="current_commune_id" class="form-select">
                                <option value="">Select commune</option>
                            </select>
                            <p id="current_commune_idError" class="text-danger small mt-1 d-none"></p>
                        </div>

                        <div class="col-md-6 mb-3">
                            <label for="current_village_id" class="form-label">Current Village (KH)</label>
                            <select name="current_village_id" id="current_village_id" class="form-select">
                                <option value="">Select village</option>
                            </select>
                            <p id="current_village_idError" class="text-danger small mt-1 d-none"></p>
                        </div>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-primary" onclick="saveTeacher()">
                    <i class="fas fa-save"></i> Save Teacher
                </button>
            </div>
        </div>
    </div>
</div>

<script>
// Photo preview function
function previewPhoto(event) {
    const file = event.target.files[0];
    if (file) {
        const reader = new FileReader();
        reader.onload = function(e) {
            $('#photoPreview').html(`<img src="${e.target.result}" alt="Preview" class="img-thumbnail" style="max-width: 150px;">`);
        };
        reader.readAsDataURL(file);
    } else {
        $('#photoPreview').html('');
    }
}

// Open manage phones modal
function openManagePhonesModal() {
    const teacherId = $('#teacherId').val();
    if (!teacherId) {
        Swal.fire('Info', 'Please save the teacher first before managing phones.', 'info');
        return;
    }
    
    // This function is defined in index.blade.php
    if (typeof window.openManagePhonesModalInternal === 'function') {
        window.openManagePhonesModalInternal(teacherId);
    }
}
</script>
