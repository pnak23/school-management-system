{{-- Create/Edit Publisher Modal --}}
<div id="publisher-modal" class="hidden fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full z-50">
    <div class="relative top-20 mx-auto p-5 border w-11/12 md:w-2/3 lg:w-1/2 shadow-lg rounded-lg bg-white mb-10">
        {{-- Modal Header --}}
        <div class="flex justify-between items-center pb-3 border-b">
            <h3 id="modal-title" class="text-2xl font-bold text-gray-900">Add Publisher</h3>
            <button onclick="closeModal()" class="text-gray-400 hover:text-gray-600">
                <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                </svg>
            </button>
        </div>

        {{-- Modal Body --}}
        <form id="publisher-form" class="mt-4">
            <input type="hidden" id="publisher-id" name="publisher_id">
            
            {{-- Error Display --}}
            <div id="form-errors" class="hidden mb-4 p-4 bg-red-50 border border-red-200 rounded-lg">
                <ul id="error-list" class="list-disc list-inside text-red-600 text-sm"></ul>
            </div>

            <div class="space-y-4">
                {{-- Name --}}
                <div>
                    <label for="name" class="block text-sm font-medium text-gray-700 mb-1">
                        Publisher Name <span class="text-red-500">*</span>
                    </label>
                    <input type="text" id="name" name="name" required maxlength="150"
                        class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-transparent"
                        placeholder="Enter publisher name">
                    <p class="text-xs text-gray-500 mt-1">Maximum 150 characters</p>
                </div>

                {{-- Address --}}
                <div>
                    <label for="address" class="block text-sm font-medium text-gray-700 mb-1">
                        Address
                    </label>
                    <input type="text" id="address" name="address" maxlength="255"
                        class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-transparent"
                        placeholder="Enter address (optional)">
                    <p class="text-xs text-gray-500 mt-1">Maximum 255 characters</p>
                </div>

                {{-- Phone --}}
                <div>
                    <label for="phone" class="block text-sm font-medium text-gray-700 mb-1">
                        Phone
                    </label>
                    <input type="text" id="phone" name="phone" maxlength="30"
                        class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-transparent"
                        placeholder="Enter phone number (optional)">
                    <p class="text-xs text-gray-500 mt-1">Maximum 30 characters</p>
                </div>

                {{-- Email --}}
                <div>
                    <label for="email" class="block text-sm font-medium text-gray-700 mb-1">
                        Email
                    </label>
                    <input type="email" id="email" name="email" maxlength="100"
                        class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-transparent"
                        placeholder="Enter email address (optional)">
                    <p class="text-xs text-gray-500 mt-1">Maximum 100 characters</p>
                </div>

                {{-- Website --}}
                <div>
                    <label for="website" class="block text-sm font-medium text-gray-700 mb-1">
                        Website
                    </label>
                    <input type="url" id="website" name="website" maxlength="255"
                        class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-transparent"
                        placeholder="https://example.com (optional)">
                    <p class="text-xs text-gray-500 mt-1">Must be a valid URL</p>
                </div>
            </div>

            {{-- Modal Footer --}}
            <div class="mt-6 flex justify-end gap-3 border-t pt-4">
                <button type="button" onclick="closeModal()" class="px-4 py-2 bg-gray-300 text-gray-700 rounded-lg hover:bg-gray-400 transition">
                    Cancel
                </button>
                <button type="submit" id="submit-btn" class="px-4 py-2 bg-indigo-600 text-white rounded-lg hover:bg-indigo-700 transition">
                    <span id="submit-text">Save Publisher</span>
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


