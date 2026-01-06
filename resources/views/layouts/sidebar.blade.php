@php
    $sidebarMenu = [
        [
            'title' => 'MAIN',
            'items' => [
                [
                    'label' => 'Dashboard',
                    'route' => 'dashboard',
                    'icon' => 'home',
                    'badge' => null,
                    'color' => 'indigo',
                ],
                [
                    'label' => 'Reading Dashboard',
                    'route' => 'admin.library.reading-dashboard.index',
                    'icon' => 'home',
                    'badge' => 'Analytics',
                    'color' => 'indigo',
                ],
            ],
        ],

        [
            'title' => 'USER MANAGEMENT',
            'items' => [
                [
                    'label' => 'Users',
                    'route' => 'admin.users.index',
                    'icon' => 'users',
                    'badge' => null,
                    'color' => 'blue',
                    'roles' => ['admin', 'manager', 'staff'],
                ],
                [
                    'label' => 'Roles',
                    'route' => 'admin.roles.index',
                    'icon' => 'shield-check',
                    'badge' => null,
                    'color' => 'blue',
                ],
            ],
        ],

        [
            'title' => 'HR MANAGEMENT',
            'items' => [
                [
                    'label' => 'User Page',
                    'route' => 'user_page.index',
                    'icon' => 'academic-cap',
                    'badge' => null,
                    'color' => 'blue',
                ],
                [
                    'label' => 'Teachers',
                    'route' => 'admin.teachers.index',
                    'icon' => 'academic-cap',
                    'badge' => null,
                    'color' => 'blue',
                ],
                [
                    'label' => 'Students',
                    'route' => 'admin.students.index',
                    'icon' => 'user',
                    'badge' => null,
                    'color' => 'blue',
                ],
                [
                    'label' => 'Staff',
                    'route' => 'admin.staff.index',
                    'icon' => 'identification',
                    'badge' => null,
                    'color' => 'cyan',
                ],
                [
                    'label' => 'Guests',
                    'route' => 'admin.library.guests.index',
                    'icon' => 'identification',
                    'badge' => null,
                    'color' => 'cyan',
                ],
                [
                    'label' => 'Departments',
                    'route' => 'admin.departments.index',
                    'icon' => 'office-building',
                    'badge' => null,
                    'color' => 'cyan',
                ],
                [
                    'label' => 'Positions',
                    'route' => 'admin.positions.index',
                    'icon' => 'briefcase',
                    'badge' => null,
                    'color' => 'cyan',
                ],
                [
                    'label' => 'Employment Types',
                    'route' => 'admin.employment-types.index',
                    'icon' => 'briefcase',
                    'badge' => null,
                    'color' => 'cyan',
                ],
                [
                    'label' => 'QR: User Page',
                    'route' => 'qr.library.user_page.qr-generator',
                    'icon' => 'briefcase',
                    'badge' => 'Services',
                    'color' => 'cyan',
                    'roles' => ['admin', 'manager', 'staff'],
                ],
            ],
        ],
        [
            'title' => 'LOCATION',
            'items' => [
                [
                    'label' => 'Provinces',
                    'route' => 'provinces.index',
                    'icon' => 'location',
                    'badge' => null,
                    'color' => 'cyan',
                    'roles' => ['admin', 'manager', 'staff'],
                ],
                [
                    'label' => 'Districts',
                    'route' => 'districts.index',
                    'icon' => 'location',
                    'badge' => null,
                    'color' => 'cyan',
                    'roles' => ['admin', 'manager', 'staff'],
                ],
                [
                    'label' => 'Communes',
                    'route' => 'communes.index',
                    'icon' => 'location',
                    'badge' => null,
                    'color' => 'cyan',
                    'roles' => ['admin', 'manager', 'staff'],
                ],
                [
                    'label' => 'Villagese',
                    'route' => 'villages.index',
                    'icon' => 'location',
                    'badge' => null,
                    'color' => 'cyan',
                    'roles' => ['admin', 'manager', 'staff'],
                ],
            ],
        ],
        [
            'title' => 'LIBRARY MANAGEMENT',
            'items' => [
                [
                    'label' => 'Book Categories',
                    'route' => 'admin.library.categories.index',
                    'icon' => 'book-open',
                    'badge' => null,
                    'color' => 'blue',
                ],
                [
                    'label' => 'Publishers',
                    'route' => 'admin.library.publishers.index',
                    'icon' => 'book-open',
                    'badge' => null,
                    'color' => 'blue',
                ],
                [
                    'label' => 'Authors',
                    'route' => 'admin.library.authors.index',
                    'icon' => 'book-open',
                    'badge' => null,
                    'color' => 'blue',
                ],
                [
                    'label' => 'Shelves',
                    'route' => 'admin.library.shelves.index',
                    'icon' => 'book-open',
                    'badge' => null,
                    'color' => 'blue',
                ],
                [
                    'label' => 'Books / Items',
                    'route' => 'admin.library.items.index',
                    'icon' => 'book-open',
                    'badge' => null,
                    'color' => 'blue',
                    'roles' => ['admin', 'manager', 'staff'],
                ],
                [
                    'label' => 'Book Copies',
                    'route' => 'admin.library.copies.index',
                    'icon' => 'barcode',
                    'badge' => null,
                    'color' => 'blue',
                ],
                [
                    'label' => 'Loans',
                    'route' => 'admin.library.loans.index',
                    'icon' => 'arrow-path',
                    'badge' => 'New',
                    'color' => 'orange',
                ],
                [
                    'label' => 'Fines',
                    'route' => 'admin.library.fines.index',
                    'icon' => 'currency-dollar',
                    'badge' => 'New',
                    'color' => 'orange',
                ],
                [
                    'label' => 'Visits',
                    'route' => 'admin.library.visits.index',
                    'icon' => 'currency-dollar',
                    'badge' => 'New',
                    'color' => 'orange',
                ],
                [
                    'label' => 'QR Check-in / Check-out',
                    'route' => 'qr.library.qr-generator',
                    'icon' => 'qr-code',
                    'badge' => 'Entry',
                    'color' => 'purple',
                ],
                [
                    'label' => 'QR: Start Reading',
                    'route' => 'qr.library.start-reading.qr-generator',
                    'icon' => 'qr-code',
                    'badge' => 'New',
                    'color' => 'purple',
                ],
                [
                    'label' => 'QR: Books Report',
                    'route' => 'qr.library.books-report.qr-generator',
                    'icon' => 'qr-code',
                    'badge' => 'QR',
                    'color' => 'purple',
                    'roles' => ['admin', 'manager', 'staff'],
                ],
                [
                    'label' => 'Stock Taking',
                    'route' => 'admin.library.stock-takings.index',
                    'icon' => 'qr-code',
                    'badge' => 'Inventory',
                    'color' => 'purple',
                    'roles' => ['admin', 'manager', 'staff'],
                ],
                [
                    'label' => 'Reservations',
                    'route' => 'admin.library.reservations.index',
                    'icon' => 'qr-code',
                    'badge' => 'Queue',
                    'color' => 'purple',
                    'roles' => ['admin', 'manager', 'staff', 'principal', 'user'],
                ],
            ],
        ],

        [
            'title' => 'REPORTS',
            'items' => [
                [
                    'label' => 'Overdue Loans',
                    'route' => 'admin.library.reports.overdue_loans.index',
                    'icon' => 'exclamation-circle',
                    'badge' => 'Report',
                    'color' => 'red',
                    'roles' => ['admin', 'manager'],
                ],
                [
                    'label' => 'Active Loans',
                    'route' => 'admin.library.reports.active_loans.index',
                    'icon' => 'book-open',
                    'badge' => 'Report',
                    'color' => 'green',
                    'roles' => ['admin', 'manager'],
                ],
                [
                    'label' => 'Outstanding Fines',
                    'route' => 'admin.library.reports.outstanding_fines.index',
                    'icon' => 'book-open',
                    'badge' => 'Report',
                    'color' => 'green',
                    'roles' => ['admin', 'manager'],
                ],
                [
                    'label' => 'Collection Summary',
                    'route' => 'admin.library.reports.collection_summary.index',
                    'icon' => 'book-open',
                    'badge' => 'Report',
                    'color' => 'green',
                    'roles' => ['admin', 'manager'],
                ],
                [
                    'label' => 'Daily Visit Statistics',
                    'route' => 'admin.library.reports.daily_visits.index',
                    'icon' => 'book-open',
                    'badge' => 'Report',
                    'color' => 'green',
                    'roles' => ['admin', 'manager'],
                ],
                [
                    'label' => 'Books Report',
                    'route' => 'admin.library.books_report.index',
                    'icon' => 'book-open',
                    'badge' => 'Report',
                    'color' => 'indigo',
                    'roles' => ['admin', 'manager', 'staff', 'teacher', 'student'],
                ],
            ],
        ],

        [
            'title' => 'SYSTEM',
            'items' => [
                [
                    'label' => 'Settings',
                    'route' => 'profile.edit',
                    'icon' => 'cog',
                    'badge' => null,
                    'color' => 'slate',
                ],
                [
                    'label' => 'Backup & Restore',
                    'route' => 'backup.index',
                    'icon' => 'cog',
                    'badge' => null,
                    'color' => 'slate',
                    'roles' => ['admin'],
                ],
                [
                    'label' => 'Activity Logs',
                    'route' => 'logs.index',
                    'icon' => 'clock',
                    'badge' => null,
                    'color' => 'slate',
                ],
                [
                    'label' => 'Reading Logs',
                    'route' => 'admin.library.reading-dashboard.index',
                    'icon' => 'book-open',
                    'badge' => null,
                    'color' => 'indigo',
                ],
                [
                    'label' => 'Logout',
                    'route' => 'logout',
                    'icon' => 'arrow-left-on-rectangle',
                    'badge' => null,
                    'color' => 'red',
                ],
            ],
        ],
    ];

    $icons = [
        'home' => '
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
            d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6" />
    ',

        'users' => '
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
            d="M17 20h5v-2a4 4 0 00-4-4h-1
               M9 20H4v-2a4 4 0 014-4h1
               m4-4a4 4 0 11-8 0 4 4 0 018 0
               m6 4a3 3 0 100-6 3 3 0 000 6" />
    ',

        'user' => '
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
            d="M12 12a4 4 0 100-8 4 4 0 000 8z
               m6 8H6a6 6 0 0112 0z" />
    ',

        'shield-check' => '
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
            d="M12 3l8 4v5c0 5-3.5 9-8 9s-8-4-8-9V7l8-4z
               m-2 9l2 2 4-4" />
    ',

        'academic-cap' => '
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
            d="M12 3l9 5-9 5-9-5 9-5z
               m0 10l6-3v4a6 6 0 11-12 0v-4l6 3z" />
    ',

        'identification' => '
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
            d="M6 7h12M6 11h12M6 15h6
               M4 5h16v14H4z" />
    ',

        'office-building' => '
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
            d="M3 21h18M6 3h12v18H6z
               M9 7h2m2 0h2M9 11h2m2 0h2M9 15h2m2 0h2" />
    ',

        'briefcase' => '
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
            d="M6 7V5a2 2 0 012-2h8a2 2 0 012 2v2
               M4 7h16v11H4z" />
    ',

        'book-open' => '
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
            d="M12 6c-2-1.5-5-2-8-2v12c3 0 6 .5 8 2
               m0-14c2-1.5 5-2 8-2v12c-3 0-6 .5-8 2" />
    ',

        'barcode' => '
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
            d="M4 6v12M7 6v12M10 6v12M14 6v12M17 6v12M20 6v12" />
    ',

        'arrow-path' => '
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
            d="M4 4v6h6M20 20v-6h-6
               M5 19a9 9 0 0114-14" />
    ',

        'currency-dollar' => '
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
            d="M12 2v20m5-16H9a3 3 0 000 6h6a3 3 0 010 6H6" />
    ',

        'qr-code' => '
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
            d="M4 4h6v6H4V4zm10 0h6v6h-6V4z
               M4 14h6v6H4v-6zm10 4h2m4 0h-2m-4-4h6" />
    ',

        'exclamation-circle' => '
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
            d="M12 8v4m0 4h.01
               M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
    ',

        'check-circle' => '
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
            d="M9 12l2 2 4-4
               M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
    ',

        'clock' => '
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
            d="M12 6v6l4 2
               m6-2a10 10 0 11-20 0 10 10 0 0120 0z" />
    ',

        'cog' => '
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
            d="M11.983 13.987a2 2 0 100-4 2 2 0 000 4z
               M19.4 15a1.65 1.65 0 00.33 1.82l.06.06-2 3.46-2.49-1
               a6.04 6.04 0 01-2.37 1.37L13 22h-4l-.11-2.16
               a6.04 6.04 0 01-2.37-1.37l-2.49 1-2-3.46.06-.06
               A1.65 1.65 0 004.6 15a6.002 6.002 0 010-6
               A1.65 1.65 0 004.27 7.18l-.06-.06 2-3.46 2.49 1
               a6.04 6.04 0 012.37-1.37L9 2h4l.11 2.16
               a6.04 6.04 0 012.37 1.37l2.49-1 2 3.46-.06.06
               A1.65 1.65 0 0019.4 9a6.002 6.002 0 010 6z" />
    ',

        'arrow-left-on-rectangle' => '
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
            d="M17 16l4-4m0 0l-4-4m4 4H7
               m6 4v1a2 2 0 01-2 2H5a2 2 0 01-2-2V7a2 2 0 012-2h6a2 2 0 012 2v1" />
    ',
        'location' => '
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
            d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4" />
    ',
    ];

    $colorClasses = [
        'indigo' => 'bg-indigo-500',
        'blue' => 'bg-blue-500',
        'cyan' => 'bg-cyan-500',
        'orange' => 'bg-orange-500',
        'purple' => 'bg-purple-500',
        'green' => 'bg-green-500',
        'red' => 'bg-red-500',
    ];
@endphp

<aside class="relative w-64 bg-white shadow-xl h-full overflow-y-auto">
    <div class="p-4 space-y-2">

        <h2 class="text-lg font-bold text-gray-800 mb-4">
            Menu
        </h2>

        <nav class="space-y-4">

            @foreach ($sidebarMenu as $section)
                {{-- Section Title --}}
                <h3 class="px-4 text-xs font-semibold text-gray-500 uppercase tracking-wider">
                    {{ $section['title'] }}
                </h3>

                {{-- Section Items --}}
                <div class="space-y-1">
                    @foreach ($section['items'] as $item)
                        @php
                            $isActive = request()->routeIs($item['route'] . '*');
                            $roles = $item['roles'] ?? null; // get allowed roles
                            $canSee = !$roles || (auth()->check() && auth()->user()->hasAnyRole($roles));
                        @endphp

                        @if ($canSee)
                            @if ($item['route'] === 'logout')
                                <form method="POST" action="{{ route('logout') }}">
                                    @csrf
                                    <button type="submit"
                                        class="flex items-center w-full px-4 py-3 text-gray-700 hover:bg-red-50 hover:text-red-600 rounded-lg transition">
                                        <svg class="h-5 w-5 mr-3" fill="none" stroke="currentColor"
                                            viewBox="0 0 24 24">
                                            {!! $icons[$item['icon']] ?? $icons['arrow-left-on-rectangle'] !!}
                                        </svg>
                                        {{ $item['label'] }}
                                    </button>
                                </form>
                            @else
                                <a href="{{ route($item['route']) }}"
                                    class="group flex items-center gap-3 px-4 py-3 rounded-lg
                                   text-gray-700 hover:bg-indigo-50 hover:text-indigo-600
                                   relative overflow-visible {{ request()->routeIs($item['route'] . '*') ? 'bg-indigo-50 text-indigo-600' : '' }}">
                                    <svg class="h-5 w-5 shrink-0 text-gray-500 group-hover:text-indigo-600"
                                        fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        {!! $icons[$item['icon']] ?? $icons['home'] !!}
                                    </svg>
                                    <span class="relative flex-1 min-w-0 truncate">
                                        {{ $item['label'] }}
                                    </span>
                                </a>
                            @endif
                        @endif
                    @endforeach

                </div>

                <hr class="my-4 border-gray-200">
            @endforeach

        </nav>
    </div>
</aside>
