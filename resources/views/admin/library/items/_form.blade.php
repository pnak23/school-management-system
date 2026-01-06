{{-- Create/Edit Library Item Modal --}}
<div class="modal fade" id="itemModal" tabindex="-1" aria-labelledby="itemModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header bg-primary text-white">
                <h5 class="modal-title" id="modalTitle">Add Book</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form id="itemForm" enctype="multipart/form-data">
                <div class="modal-body">
                    <input type="hidden" id="itemId" name="item_id">
                    
                    {{-- Error Display --}}
                    <div id="formErrors" class="alert alert-danger d-none" role="alert">
                        <ul class="mb-0"></ul>
                    </div>

                    <div class="row">
                        {{-- Title --}}
                        <div class="col-md-12 mb-3">
                            <label for="title" class="form-label">
                                Title <span class="text-danger">*</span>
                            </label>
                            <input type="text" id="title" name="title" class="form-control" required maxlength="255" placeholder="Enter book title">
                            <p id="titleError" class="text-danger small mt-1 d-none"></p>
                        </div>

                        {{-- ISBN --}}
                        <div class="col-md-6 mb-3">
                            <label for="isbn" class="form-label">ISBN</label>
                            <input type="text" id="isbn" name="isbn" class="form-control" maxlength="20" placeholder="e.g., 978-3-16-148410-0">
                            <small class="text-muted">International Standard Book Number</small>
                            <p id="isbnError" class="text-danger small mt-1 d-none"></p>
                        </div>

                        {{-- Edition --}}
                        <div class="col-md-6 mb-3">
                            <label for="edition" class="form-label">Edition</label>
                            <input type="text" id="edition" name="edition" class="form-control" maxlength="50" placeholder="e.g., 1st, 2nd, Revised">
                            <p id="editionError" class="text-danger small mt-1 d-none"></p>
                        </div>

                        {{-- Published Year --}}
                        <div class="col-md-6 mb-3">
                            <label for="published_year" class="form-label">Published Year</label>
                            <input type="number" id="published_year" name="published_year" class="form-control" min="1000" max="2155" placeholder="YYYY (1000-2155)">
                            <small class="text-muted">Valid range: 1000-2155</small>
                            <p id="published_yearError" class="text-danger small mt-1 d-none"></p>
                        </div>

                        {{-- Language --}}
                        <div class="col-md-6 mb-3">
                            <label for="language" class="form-label">Language</label>
                            <input type="text" id="language" name="language" class="form-control" maxlength="50" placeholder="e.g., English, Khmer">
                            <p id="languageError" class="text-danger small mt-1 d-none"></p>
                        </div>

                        {{-- Category --}}
                        <div class="col-md-6 mb-3">
                            <label for="category_id" class="form-label">
                                Category <span class="text-danger">*</span>
                            </label>
                            <select id="category_id" name="category_id" class="form-select" required>
                                <option value="">Select Category</option>
                                @foreach($categories as $category)
                                <option value="{{ $category->id }}">{{ $category->name }}</option>
                                @endforeach
                            </select>
                            <p id="category_idError" class="text-danger small mt-1 d-none"></p>
                        </div>

                        {{-- Publisher --}}
                        <div class="col-md-6 mb-3">
                            <label for="publisher_id" class="form-label">Publisher</label>
                            <select id="publisher_id" name="publisher_id" class="form-select">
                                <option value="">Select Publisher (optional)</option>
                                @foreach($publishers as $publisher)
                                <option value="{{ $publisher->id }}">{{ $publisher->name }}</option>
                                @endforeach
                            </select>
                            <p id="publisher_idError" class="text-danger small mt-1 d-none"></p>
                        </div>

                        {{-- Authors (Multi-select) --}}
                        <div class="col-md-12 mb-3">
                            <label for="author_ids" class="form-label">Authors (Select Multiple)</label>
                            <select id="author_ids" name="author_ids[]" class="form-select" multiple size="5">
                                @foreach($authors as $author)
                                <option value="{{ $author->id }}">{{ $author->name }}</option>
                                @endforeach
                            </select>
                            <small class="text-muted">Hold Ctrl (Windows) or Cmd (Mac) to select multiple authors</small>
                            <p id="author_idsError" class="text-danger small mt-1 d-none"></p>
                        </div>

                        {{-- Cover Image --}}
                        <div class="col-md-12 mb-3">
                            <label for="cover_image" class="form-label">Cover Image</label>
                            <input type="file" id="cover_image" name="cover_image" class="form-control" accept="image/*" onchange="previewCoverImage(this)">
                            <small class="text-muted">Max 2MB, formats: JPEG, PNG, GIF</small>
                            
                            {{-- Cover Preview --}}
                            <div id="coverPreview" class="mt-3"></div>
                        </div>

                        {{-- Description --}}
                        <div class="col-md-12 mb-3">
                            <label for="description" class="form-label">Description / Synopsis</label>
                            <textarea id="description" name="description" class="form-control" rows="5" placeholder="Enter book description, synopsis, or summary"></textarea>
                            <p id="descriptionError" class="text-danger small mt-1 d-none"></p>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="button" class="btn btn-primary" onclick="saveItem()">
                        <span id="submitText">Save Item</span>
                        <span id="submitSpinner" class="spinner-border spinner-border-sm d-none" role="status" aria-hidden="true"></span>
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
// Preview cover image
function previewCoverImage(input) {
    const preview = document.getElementById('coverPreview');
    if (input.files && input.files[0]) {
        const reader = new FileReader();
        reader.onload = function(e) {
            preview.innerHTML = '<p class="text-sm font-medium text-gray-700 mb-2">Preview:</p><img src="' + e.target.result + '" alt="Cover Preview" class="img-thumbnail" style="max-width: 200px; max-height: 300px;">';
        };
        reader.readAsDataURL(input.files[0]);
    } else {
        preview.innerHTML = '';
    }
}
</script>
