<!-- Sidebar -->
<aside class="w-64 bg-white shadow-lg h-full">
    <div class="p-4">
        <!-- Sidebar Header -->
        <div class="mb-6">
            <h2 class="text-lg font-bold text-gray-800">Menu</h2>
        </div>

        <!-- Sidebar Navigation -->
        <nav class="space-y-2">
            <!-- Dashboard -->
            <a href="{{ route('dashboard') }}" class="flex items-center px-4 py-3 text-gray-700 hover:bg-indigo-50 hover:text-indigo-600 rounded-lg transition {{ request()->routeIs('dashboard') ? 'bg-indigo-50 text-indigo-600' : '' }}">
                <svg class="h-5 w-5 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6" />
                </svg>
                Dashboard
            </a>

            <!-- Users -->
            <a href="{{ route('admin.users.index') }}" class="flex items-center px-4 py-3 text-gray-700 hover:bg-indigo-50 hover:text-indigo-600 rounded-lg transition {{ request()->routeIs('admin.users.*') ? 'bg-indigo-50 text-indigo-600' : '' }}">
                <svg class="h-5 w-5 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z" />
                </svg>
                Users
            </a>

            <!-- Roles -->
            <a href="{{ route('admin.roles.index') }}" class="flex items-center px-4 py-3 text-gray-700 hover:bg-indigo-50 hover:text-indigo-600 rounded-lg transition {{ request()->routeIs('admin.roles.*') ? 'bg-indigo-50 text-indigo-600' : '' }}">
                <svg class="h-5 w-5 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z" />
                </svg>
                Roles
            </a>



       

            <!-- Divider -->
            <hr class="my-4 border-gray-200">

            <!-- DataTables Demo Section -->
            <div class="mb-2">
                <h3 class="px-4 text-xs font-semibold text-gray-500 uppercase tracking-wider">DataTables Demo</h3>
            </div>


            <!-- Divider -->
            <hr class="my-4 border-gray-200">

            <!-- HR Management Section -->
            <div class="mb-2">
                <h3 class="px-4 text-xs font-semibold text-gray-500 uppercase tracking-wider">HR Management</h3>
            </div>

            <!-- Teachers -->
            <a href="{{ route('admin.teachers.index') }}" class="flex items-center px-4 py-3 text-gray-700 hover:bg-blue-50 hover:text-blue-600 rounded-lg transition {{ request()->routeIs('admin.teachers.*') ? 'bg-blue-50 text-blue-600' : '' }}">
                <svg class="h-5 w-5 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z" />
                </svg>
                Teachers
            </a>

                        <!-- Students (Original) -->
            <a href="{{ route('admin.students.index') }}" class="flex items-center px-4 py-3 text-gray-700 hover:bg-blue-50 hover:text-blue-600 rounded-lg transition {{ request()->routeIs('admin.students.index') ? 'bg-blue-50 text-blue-600' : '' }}">
                <svg class="h-5 w-5 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 14l9-5-9-5-9 5 9 5z"/>
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 14l6.16-3.422a12.083 12.083 0 01.665 6.479A11.952 11.952 0 0012 20.055a11.952 11.952 0 00-6.824-2.998 12.078 12.078 0 01.665-6.479L12 14z"/>
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 14l9-5-9-5-9 5 9 5zm0 0l6.16-3.422a12.083 12.083 0 01.665 6.479A11.952 11.952 0 0012 20.055a11.952 11.952 0 00-6.824-2.998 12.078 12.078 0 01.665-6.479L12 14zm-4 6v-7.5l4-2.222"/>
                </svg>
                Students 
            </a>

            <!-- Staff -->
            <a href="{{ route('admin.staff.index') }}" class="flex items-center px-4 py-3 text-gray-700 hover:bg-cyan-50 hover:text-cyan-600 rounded-lg transition {{ request()->routeIs('admin.staff.*') ? 'bg-cyan-50 text-cyan-600' : '' }}">
                <svg class="h-5 w-5 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 13.255A23.931 23.931 0 0112 15c-3.183 0-6.22-.62-9-1.745M16 6V4a2 2 0 00-2-2h-4a2 2 0 00-2 2v2m4 6h.01M5 20h14a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z" />
                </svg>
                Staff
            </a>

                     <a href="{{ route('admin.library.guests.index') }}" class="flex items-center px-4 py-3 text-gray-700 hover:bg-lime-50 hover:text-lime-600 rounded-lg transition {{ request()->routeIs('admin.library.guests.*') ? 'bg-lime-50 text-lime-600' : '' }}">
                <svg class="h-5 w-5 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z" />
                </svg>
                Guests
            </a>


            <!-- Departments -->
            <a href="{{ route('admin.departments.index') }}" class="flex items-center px-4 py-3 text-gray-700 hover:bg-teal-50 hover:text-teal-600 rounded-lg transition {{ request()->routeIs('admin.departments.*') ? 'bg-teal-50 text-teal-600' : '' }}">
                <svg class="h-5 w-5 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4" />
                </svg>
                Departments
            </a>

            <!-- Positions -->
            <a href="{{ route('admin.positions.index') }}" class="flex items-center px-4 py-3 text-gray-700 hover:bg-amber-50 hover:text-amber-600 rounded-lg transition {{ request()->routeIs('admin.positions.*') ? 'bg-amber-50 text-amber-600' : '' }}">
                <svg class="h-5 w-5 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                </svg>
                Positions
            </a>

            <!-- Employment Types -->
            <a href="{{ route('admin.employment-types.index') }}" class="flex items-center px-4 py-3 text-gray-700 hover:bg-rose-50 hover:text-rose-600 rounded-lg transition {{ request()->routeIs('admin.employment-types.*') ? 'bg-rose-50 text-rose-600' : '' }}">
                <svg class="h-5 w-5 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01" />
                </svg>
                Employment Types
            </a>

            <!-- Divider -->
            <hr class="my-4 border-gray-200">

            <!-- Library Management Section -->
            <div class="mb-2">
                <h3 class="px-4 text-xs font-semibold text-gray-500 uppercase tracking-wider">Library Management</h3>
            </div>

            <!-- Book Categories -->
            <a href="{{ route('admin.library.categories.index') }}" class="flex items-center px-4 py-3 text-gray-700 hover:bg-pink-50 hover:text-pink-600 rounded-lg transition {{ request()->routeIs('admin.library.categories.*') ? 'bg-pink-50 text-pink-600' : '' }}">
                <svg class="h-5 w-5 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z" />
                </svg>
                Book Categories
            </a>

            <!-- Publishers -->
            <a href="{{ route('admin.library.publishers.index') }}" class="flex items-center px-4 py-3 text-gray-700 hover:bg-purple-50 hover:text-purple-600 rounded-lg transition {{ request()->routeIs('admin.library.publishers.*') ? 'bg-purple-50 text-purple-600' : '' }}">
                <svg class="h-5 w-5 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4" />
                </svg>
                Publishers
            </a>

            <!-- Authors -->
            <a href="{{ route('admin.library.authors.index') }}" class="flex items-center px-4 py-3 text-gray-700 hover:bg-indigo-50 hover:text-indigo-600 rounded-lg transition {{ request()->routeIs('admin.library.authors.*') ? 'bg-indigo-50 text-indigo-600' : '' }}">
                <svg class="h-5 w-5 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                </svg>
                Authors
            </a>

            <!-- Shelves -->
            <a href="{{ route('admin.library.shelves.index') }}" class="flex items-center px-4 py-3 text-gray-700 hover:bg-teal-50 hover:text-teal-600 rounded-lg transition {{ request()->routeIs('admin.library.shelves.*') ? 'bg-teal-50 text-teal-600' : '' }}">
                <svg class="h-5 w-5 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 8h14M5 8a2 2 0 110-4h14a2 2 0 110 4M5 8v10a2 2 0 002 2h10a2 2 0 002-2V8m-9 4h4" />
                </svg>
                Shelves
            </a>

            <!-- Library Items / Books -->
            <a href="{{ route('admin.library.items.index') }}" class="flex items-center px-4 py-3 text-gray-700 hover:bg-blue-50 hover:text-blue-600 rounded-lg transition {{ request()->routeIs('admin.library.items.*') ? 'bg-blue-50 text-blue-600' : '' }}">
                <svg class="h-5 w-5 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253" />
                </svg>
                Books / Items
            </a>

            <!-- Book Copies (Barcode) -->
            <a href="{{ route('admin.library.copies.index') }}" class="flex items-center px-4 py-3 text-gray-700 hover:bg-green-50 hover:text-green-600 rounded-lg transition {{ request()->routeIs('admin.library.copies.*') ? 'bg-green-50 text-green-600' : '' }}">
                <svg class="h-5 w-5 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01" />
                </svg>
                Book Copies (Barcode)
            </a>

            <!-- Loans (Borrow/Return) -->
            <a href="{{ route('admin.library.loans.index') }}" class="flex items-center px-4 py-3 text-gray-700 hover:bg-orange-50 hover:text-orange-600 rounded-lg transition {{ request()->routeIs('admin.library.loans.*') ? 'bg-orange-50 text-orange-600' : '' }}">
                <svg class="h-5 w-5 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7h12m0 0l-4-4m4 4l-4 4m0 6H4m0 0l4 4m-4-4l4-4" />
                </svg>
                Loans (Borrow/Return)
            </a>

            <!-- Fines -->
            <a href="{{ route('admin.library.fines.index') }}" class="flex items-center px-4 py-3 text-gray-700 hover:bg-yellow-50 hover:text-yellow-600 rounded-lg transition {{ request()->routeIs('admin.library.fines.*') ? 'bg-yellow-50 text-yellow-600' : '' }}">
                <svg class="h-5 w-5 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                </svg>
                Fines
            </a>
   
            <!-- Visits (Entry/Exit) -->
            <a href="{{ route('admin.library.visits.index') }}" class="flex items-center px-4 py-3 text-gray-700 hover:bg-cyan-50 hover:text-cyan-600 rounded-lg transition {{ request()->routeIs('admin.library.visits.*') ? 'bg-cyan-50 text-cyan-600' : '' }}">
                <svg class="h-5 w-5 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 16l-4-4m0 0l4-4m-4 4h14m-5 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h7a3 3 0 013 3v1" />
                </svg>
                Visits (Entry/Exit)
            </a>

            <!-- Reading Logs (In-Library) -->
            <a href="{{ route('admin.library.reading-logs.index') }}" class="flex items-center px-4 py-3 text-gray-700 hover:bg-indigo-50 hover:text-indigo-600 rounded-lg transition {{ request()->routeIs('admin.library.reading-logs.*') && !request()->routeIs('admin.library.reading-dashboard.*') ? 'bg-indigo-50 text-indigo-600' : '' }}">
                <svg class="h-5 w-5 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253" />
                </svg>
                Reading Logs (In-Library)
            </a>

            <!-- Reading Dashboard (Analytics) NEW -->
            <a href="{{ route('admin.library.reading-dashboard.index') }}" class="flex items-center px-4 py-3 text-gray-700 hover:bg-teal-50 hover:text-teal-600 rounded-lg transition {{ request()->routeIs('admin.library.reading-dashboard.*') ? 'bg-teal-50 text-teal-600' : '' }}">
                <svg class="h-5 w-5 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z" />
                </svg>
                Reading Dashboard
                <span class="ml-auto px-2 py-1 text-xs font-semibold rounded-full bg-teal-100 text-teal-800">Analytics</span>
            </a>

            <!-- QR Code Generator (Check-in/Check-out) -->
            <a href="{{ route('qr.library.qr-generator') }}" class="flex items-center px-4 py-3 text-gray-700 hover:bg-purple-50 hover:text-purple-600 rounded-lg transition {{ request()->routeIs('qr.library.qr-generator') ? 'bg-purple-50 text-purple-600' : '' }}">
                <svg class="h-5 w-5 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v1m6 11h2m-6 0h-2v4m0-11v3m0 0h.01M12 12h4.01M16 20h4M4 12h4m12 0h.01M5 8h2a1 1 0 001-1V5a1 1 0 00-1-1H5a1 1 0 00-1 1v2a1 1 0 001 1zm12 0h2a1 1 0 001-1V5a1 1 0 00-1-1h-2a1 1 0 00-1 1v2a1 1 0 001 1zM5 20h2a1 1 0 001-1v-2a1 1 0 00-1-1H5a1 1 0 00-1 1v2a1 1 0 001 1z" />
                </svg>
                QR: Check-in/Check-out
                <span class="ml-auto px-2 py-1 text-xs font-semibold rounded-full bg-purple-100 text-purple-800">Entry</span>
            </a>

            <!-- QR Code Generator (Start Reading) NEW -->
            <a href="{{ route('qr.library.start-reading.qr-generator') }}" class="flex items-center px-4 py-3 text-gray-700 hover:bg-green-50 hover:text-green-600 rounded-lg transition {{ request()->routeIs('qr.library.start-reading.qr-generator') ? 'bg-green-50 text-green-600' : '' }}">
                <svg class="h-5 w-5 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253" />
                </svg>
                QR: Start Reading
                <span class="ml-auto px-2 py-1 text-xs font-semibold rounded-full bg-green-100 text-green-800">NEW</span>
            </a>

            <!-- QR Code Generator (Books Report) NEW -->
            @if(auth()->check() && auth()->user()->hasAnyRole(['admin', 'manager', 'staff']))
            <a href="{{ route('qr.library.books-report.qr-generator') }}" class="flex items-center px-4 py-3 text-gray-700 hover:bg-emerald-50 hover:text-emerald-600 rounded-lg transition {{ request()->routeIs('qr.library.books-report.qr-generator') ? 'bg-emerald-50 text-emerald-600' : '' }}">
                <svg class="h-5 w-5 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v1m6 11h2m-6 0h-2v4m0-11v3m0 0h.01M12 12h4.01M16 20h4M4 12h4m12 0h.01M5 8h2a1 1 0 001-1V5a1 1 0 00-1-1H5a1 1 0 00-1 1v2a1 1 0 001 1zm12 0h2a1 1 0 001-1V5a1 1 0 00-1-1h-2a1 1 0 00-1 1v2a1 1 0 001 1zM5 20h2a1 1 0 001-1v-2a1 1 0 00-1-1H5a1 1 0 00-1 1v2a1 1 0 001 1z" />
                </svg>
                QR: Books Report
                <span class="ml-auto px-2 py-1 text-xs font-semibold rounded-full bg-emerald-100 text-emerald-800">NEW</span>
            </a>
            @endif

            <!-- User Page (All Users) -->
            <a href="{{ route('user_page.index') }}" class="flex items-center px-4 py-3 text-gray-700 hover:bg-indigo-50 hover:text-indigo-600 rounded-lg transition {{ request()->routeIs('user_page.index') ? 'bg-indigo-50 text-indigo-600' : '' }}">
                <svg class="h-5 w-5 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                </svg>
                User Page
                <span class="ml-auto px-2 py-1 text-xs font-semibold rounded-full bg-indigo-100 text-indigo-800">Services</span>
            </a>

            <!-- QR Code Generator (User Page) NEW -->
            @if(auth()->check() && auth()->user()->hasAnyRole(['admin', 'manager', 'staff']))
            <a href="{{ route('qr.library.user_page.qr-generator') }}" class="flex items-center px-4 py-3 text-gray-700 hover:bg-purple-50 hover:text-purple-600 rounded-lg transition {{ request()->routeIs('qr.library.user_page.qr-generator') ? 'bg-purple-50 text-purple-600' : '' }}">
                <svg class="h-5 w-5 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v1m6 11h2m-6 0h-2v4m0-11v3m0 0h.01M12 12h4.01M16 20h4M4 12h4m12 0h.01M5 8h2a1 1 0 001-1V5a1 1 0 00-1-1H5a1 1 0 00-1 1v2a1 1 0 001 1zm12 0h2a1 1 0 001-1V5a1 1 0 00-1-1h-2a1 1 0 00-1 1v2a1 1 0 001 1zM5 20h2a1 1 0 001-1v-2a1 1 0 00-1-1H5a1 1 0 00-1 1v2a1 1 0 001 1z" />
                </svg>
                QR: User Page
                <span class="ml-auto px-2 py-1 text-xs font-semibold rounded-full bg-purple-100 text-purple-800">NEW</span>
            </a>
            @endif

            <!-- Stock Taking (Inventory Audit) -->
            @if(auth()->check() && auth()->user()->hasAnyRole(['admin', 'manager', 'staff']))
            <a href="{{ route('admin.library.stock-takings.index') }}" class="flex items-center px-4 py-3 text-gray-700 hover:bg-amber-50 hover:text-amber-600 rounded-lg transition {{ request()->routeIs('admin.library.stock-takings.*') ? 'bg-amber-50 text-amber-600' : '' }}">
                <svg class="h-5 w-5 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4" />
                </svg>
                Stock Taking
                <span class="ml-auto px-2 py-1 text-xs font-semibold rounded-full bg-amber-100 text-amber-800">Inventory</span>
            </a>
            @endif

            <!-- Reservations (Book Hold/Queue) -->
            @if(auth()->check() && auth()->user()->hasAnyRole(['admin', 'manager', 'staff', 'principal', 'user']))
            <a href="{{ route('admin.library.reservations.index') }}" class="flex items-center px-4 py-3 text-gray-700 hover:bg-purple-50 hover:text-purple-600 rounded-lg transition {{ request()->routeIs('admin.library.reservations.*') ? 'bg-purple-50 text-purple-600' : '' }}">
                <svg class="h-5 w-5 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 5a2 2 0 012-2h10a2 2 0 012 2v16l-7-3.5L5 21V5z" />
                </svg>
                Reservations
                <span class="ml-auto px-2 py-1 text-xs font-semibold rounded-full bg-purple-100 text-purple-800">Queue</span>
            </a>
            @endif

            <!-- Reports Section -->
            @if(auth()->check() && auth()->user()->hasAnyRole(['admin', 'manager']))
            <div class="mb-2">
                <h3 class="px-4 text-xs font-semibold text-gray-500 uppercase tracking-wider">Reports</h3>
            </div>

            <!-- Overdue Loans Report -->
            <a href="{{ route('admin.library.reports.overdue_loans.index') }}" class="flex items-center px-4 py-3 text-gray-700 hover:bg-red-50 hover:text-red-600 rounded-lg transition {{ request()->routeIs('admin.library.reports.overdue_loans.*') ? 'bg-red-50 text-red-600' : '' }}">
                <svg class="h-5 w-5 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                </svg>
                Overdue Loans
                <span class="ml-auto px-2 py-1 text-xs font-semibold rounded-full bg-red-100 text-red-800">Report</span>
            </a>

            <!-- Active Loans Report -->
            <a href="{{ route('admin.library.reports.active_loans.index') }}" class="flex items-center px-4 py-3 text-gray-700 hover:bg-blue-50 hover:text-blue-600 rounded-lg transition {{ request()->routeIs('admin.library.reports.active_loans.*') ? 'bg-blue-50 text-blue-600' : '' }}">
                <svg class="h-5 w-5 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253" />
                </svg>
                Active Loans
                <span class="ml-auto px-2 py-1 text-xs font-semibold rounded-full bg-blue-100 text-blue-800">Report</span>
            </a>

            <!-- Outstanding Fines Report -->
            <a href="{{ route('admin.library.reports.outstanding_fines.index') }}" class="flex items-center px-4 py-3 text-gray-700 hover:bg-orange-50 hover:text-orange-600 rounded-lg transition {{ request()->routeIs('admin.library.reports.outstanding_fines.*') ? 'bg-orange-50 text-orange-600' : '' }}">
                <svg class="h-5 w-5 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                </svg>
                Outstanding Fines
                <span class="ml-auto px-2 py-1 text-xs font-semibold rounded-full bg-orange-100 text-orange-800">Report</span>
            </a>

            <!-- Collection Summary / Availability Report -->
            <a href="{{ route('admin.library.reports.collection_summary.index') }}" class="flex items-center px-4 py-3 text-gray-700 hover:bg-teal-50 hover:text-teal-600 rounded-lg transition {{ request()->routeIs('admin.library.reports.collection_summary.*') ? 'bg-teal-50 text-teal-600' : '' }}">
                <svg class="h-5 w-5 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z" />
                </svg>
                Collection Summary
                <span class="ml-auto px-2 py-1 text-xs font-semibold rounded-full bg-teal-100 text-teal-800">Report</span>
            </a>

            <!-- Daily Visit Statistics Report -->
            <a href="{{ route('admin.library.reports.daily_visits.index') }}" class="flex items-center px-4 py-3 text-gray-700 hover:bg-purple-50 hover:text-purple-600 rounded-lg transition {{ request()->routeIs('admin.library.reports.daily_visits.*') ? 'bg-purple-50 text-purple-600' : '' }}">
                <svg class="h-5 w-5 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                </svg>
                Daily Visit Statistics
                <span class="ml-auto px-2 py-1 text-xs font-semibold rounded-full bg-purple-100 text-purple-800">Report</span>
            </a>

            <!-- Books Report (Grid View) -->
            @if(auth()->check() && auth()->user()->hasAnyRole(['admin', 'manager', 'staff', 'teacher', 'student']))
            <a href="{{ route('admin.library.books_report.index') }}" class="flex items-center px-4 py-3 text-gray-700 hover:bg-emerald-50 hover:text-emerald-600 rounded-lg transition {{ request()->routeIs('admin.library.books_report.*') ? 'bg-emerald-50 text-emerald-600' : '' }}">
                <svg class="h-5 w-5 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253" />
                </svg>
                Books Report
                <span class="ml-auto px-2 py-1 text-xs font-semibold rounded-full bg-emerald-100 text-emerald-800">Grid</span>
            </a>
            @endif

            <!-- Divider -->
            <hr class="my-4 border-gray-200">
            @endif

            <!-- System Section -->
            <div class="mb-2">
                <h3 class="px-4 text-xs font-semibold text-gray-500 uppercase tracking-wider">System</h3>
            </div>

            <!-- Activity Logs -->
            <a href="{{ route('logs.index') }}" class="flex items-center px-4 py-3 text-gray-700 hover:bg-gray-50 hover:text-gray-900 rounded-lg transition {{ request()->routeIs('logs.*') ? 'bg-gray-50 text-gray-900' : '' }}">
                <svg class="h-5 w-5 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                </svg>
                Activity Logs
            </a>

            <!-- Location Management Section -->
            <div class="mb-2">
                <h3 class="px-4 text-xs font-semibold text-gray-500 uppercase tracking-wider">Locations</h3>
            </div>

            <!-- Provinces -->
            <a href="{{ route('provinces.index') }}" class="flex items-center px-4 py-3 text-gray-700 hover:bg-purple-50 hover:text-purple-600 rounded-lg transition {{ request()->routeIs('provinces.*') ? 'bg-purple-50 text-purple-600' : '' }}">
                <svg class="h-5 w-5 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 20l-5.447-2.724A1 1 0 013 16.382V5.618a1 1 0 011.447-.894L9 7m0 13l6-3m-6 3V7m6 10l4.553 2.276A1 1 0 0021 18.382V7.618a1 1 0 00-.553-.894L15 4m0 13V4m0 0L9 7" />
                </svg>
                Provinces
            </a>

            <!-- Districts -->
            <a href="{{ route('districts.index') }}" class="flex items-center px-4 py-3 text-gray-700 hover:bg-indigo-50 hover:text-indigo-600 rounded-lg transition {{ request()->routeIs('districts.*') ? 'bg-indigo-50 text-indigo-600' : '' }}">
                <svg class="h-5 w-5 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4" />
                </svg>
                Districts
            </a>

            <!-- Communes -->
            <a href="{{ route('communes.index') }}" class="flex items-center px-4 py-3 text-gray-700 hover:bg-green-50 hover:text-green-600 rounded-lg transition {{ request()->routeIs('communes.*') ? 'bg-green-50 text-green-600' : '' }}">
                <svg class="h-5 w-5 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z" />
                </svg>
                Communes
            </a>

            <!-- Villages -->
            <a href="{{ route('villages.index') }}" class="flex items-center px-4 py-3 text-gray-700 hover:bg-orange-50 hover:text-orange-600 rounded-lg transition {{ request()->routeIs('villages.*') ? 'bg-orange-50 text-orange-600' : '' }}">
                <svg class="h-5 w-5 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6" />
                </svg>
                Villages
            </a>

            <!-- Divider -->
            <hr class="my-4 border-gray-200">

            <!-- Reports -->
            <a href="#" class="flex items-center px-4 py-3 text-gray-700 hover:bg-indigo-50 hover:text-indigo-600 rounded-lg transition">
                <svg class="h-5 w-5 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z" />
                </svg>
                Reports
            </a>

            <!-- Backup & Restore (Admin only) -->
            @auth
                @if(auth()->user()->hasRole('admin'))
                    <a href="{{ route('backup.index') }}" class="flex items-center px-4 py-3 text-gray-700 hover:bg-purple-50 hover:text-purple-600 rounded-lg transition {{ request()->routeIs('backup.*') ? 'bg-purple-50 text-purple-600' : '' }}">
                        <svg class="h-5 w-5 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4" />
                        </svg>
                        Backup & Restore
                    </a>
                @endif
            @endauth

            <!-- Settings -->
            <a href="{{ route('profile.edit') }}" class="flex items-center px-4 py-3 text-gray-700 hover:bg-indigo-50 hover:text-indigo-600 rounded-lg transition {{ request()->routeIs('profile.*') ? 'bg-indigo-50 text-indigo-600' : '' }}">
                <svg class="h-5 w-5 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z" />
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                </svg>
                Settings
            </a>

            <!-- Divider -->
            <hr class="my-4">

            <!-- Logout -->
            <form method="POST" action="{{ route('logout') }}">
                @csrf
                <button type="submit" class="flex items-center w-full px-4 py-3 text-gray-700 hover:bg-red-50 hover:text-red-600 rounded-lg transition">
                    <svg class="h-5 w-5 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1" />
                    </svg>
                    Logout
                </button>
            </form>
        </nav>
    </div>
</aside>
