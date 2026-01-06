{{-- Create/Edit Shelf Modal --}}
<div id="shelf-modal" class="hidden fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full z-50">
    <div class="relative top-10 mx-auto p-5 border w-11/12 md:w-3/4 lg:w-1/2 shadow-lg rounded-lg bg-white mb-10">
        {{-- Modal Header --}}
        <div class="flex justify-between items-center pb-3 border-b">
            <h3 id="modal-title" class="text-2xl font-bold text-gray-900">Add Shelf</h3>
            <button onclick="closeModal()" class="text-gray-400 hover:text-gray-600">
                <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                </svg>
            </button>
        </div>

        {{-- Modal Body --}}
        <form id="shelf-form" class="mt-4">
            <input type="hidden" id="shelf-id" name="shelf_id">
            
            {{-- Error Display --}}
            <div id="form-errors" class="hidden mb-4 p-4 bg-red-50 border border-red-200 rounded-lg">
                <ul id="error-list" class="list-disc list-inside text-red-600 text-sm"></ul>
            </div>

            <div class="space-y-4">
                {{-- Shelf Code --}}
                <div>
                    <label for="code" class="block text-sm font-medium text-gray-700 mb-1">
                        Shelf Code <span class="text-red-500">*</span>
                    </label>
                    <input type="text" id="code" name="code" required maxlength="50"
                        class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-teal-500 focus:border-transparent"
                        placeholder="e.g., A1, B2-S3, ROOM1-R1">
                    <p class="text-xs text-gray-500 mt-1">Unique shelf identifier (max 50 characters)</p>
                </div>

                {{-- Location --}}
                <div>
                    <label for="location" class="block text-sm font-medium text-gray-700 mb-1">
                        Location / Room Name
                    </label>
                    <input type="text" id="location" name="location" maxlength="150"
                        class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-teal-500 focus:border-transparent"
                        placeholder="e.g., Main Library - 1st Floor, Reading Room A">
                    <p class="text-xs text-gray-500 mt-1">Physical location (max 150 characters, optional)</p>
                </div>

                {{-- Description --}}
                <div>
                    <label for="description" class="block text-sm font-medium text-gray-700 mb-1">
                        Description / Notes
                    </label>
                    <textarea id="description" name="description" rows="4" maxlength="1000"
                        class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-teal-500 focus:border-transparent"
                        placeholder="Additional notes about this shelf location (optional)"></textarea>
                    <p class="text-xs text-gray-500 mt-1">Maximum 1000 characters</p>
                </div>
            </div>

            {{-- Modal Footer --}}
            <div class="mt-6 flex justify-end gap-3 border-t pt-4">
                <button type="button" onclick="closeModal()" class="px-4 py-2 bg-gray-300 text-gray-700 rounded-lg hover:bg-gray-400 transition">
                    Cancel
                </button>
                <button type="submit" id="submit-btn" class="px-4 py-2 bg-teal-600 text-white rounded-lg hover:bg-teal-700 transition">
                    <span id="submit-text">Save Shelf</span>
                    <span id="submit-spinner" class="hidden">
                        <svg class="inline animate-spin h-5 w-5 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                        </svg>
                        Saving...
                    </span>
                </button>
            </div>
        </form>
    </div>
</div>


