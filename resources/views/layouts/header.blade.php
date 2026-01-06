@php
    use Illuminate\Support\Facades\Storage;
@endphp

<nav class="navbar-glass">
    <div class="container- mx-auto px-6">
        <div class="flex items-center justify-between h-16">

            <!-- LOGO -->
            <a href="{{ url('/') }}" class="flex items-center gap-3 group">
                <div class="logo-box">
                    <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13
                              C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13
                              C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13
                              C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253" />
                    </svg>
                </div>
                <span class="logo-text">School Management</span>
            </a>

            <!-- MENU -->
            <div class="hidden md:flex items-center gap-2">
                <a href="{{ route('dashboard') }}"
                    class="nav-item {{ request()->routeIs('dashboard') ? 'active' : '' }}">
                    Dashboard
                </a>

                <a href="{{ route('admin.users.index') }}"
                    class="nav-item {{ request()->routeIs('admin.users.*') ? 'active' : '' }}">
                    Users
                </a>

                <a href="{{ route('admin.roles.index') }}"
                    class="nav-item {{ request()->routeIs('admin.roles.*') ? 'active' : '' }}">
                    Roles
                </a>
                
                <select onchange="window.location.href=this.value"
                    class="text-sm rounded-md px-3 py-2 border-0">
                    <option value="{{ route('lang.switch', 'en') }}"
                        {{ app()->getLocale() === 'en' ? 'selected' : '' }}>
                        English
                    </option>

                    <option value="{{ route('lang.switch', 'km') }}"
                        {{ app()->getLocale() === 'km' ? 'selected' : '' }}>
                        ខ្មែរ
                    </option>
                </select>




            </div>

            <!-- RIGHT SIDE -->
            <div class="flex items-center gap-4">

                <!-- NOTIFICATIONS -->
                @if (auth()->check() &&
                        auth()->user()->hasAnyRole(['admin', 'manager', 'staff']))
                    <div class="flex items-center gap-2">

                        <a href="{{ route('admin.library.loans.index') }}" class="icon-circle">
                            <i class="fas fa-bell"></i>
                            <span id="headerPendingRequestsBadge" class="notify-badge hidden">0</span>
                        </a>

                        <a href="{{ route('admin.library.loans.index') }}" class="icon-circle danger">
                            <i class="fas fa-exclamation-triangle"></i>
                            <span id="headerOverdueBadge" class="notify-badge hidden">0</span>
                        </a>

                    </div>
                @endif

                <!-- USER -->
                <div class="flex items-center gap-3 cursor-pointer group">
                    @if (Auth::user()->profile_picture)
                        <img src="{{ Storage::url(Auth::user()->profile_picture) }}" class="avatar-img"
                            onerror="this.style.display='none'; this.nextElementSibling.style.display='flex';">
                        <div class="avatar-fallback hidden">
                            {{ strtoupper(substr(Auth::user()->name, 0, 1)) }}
                        </div>
                    @else
                        <div class="avatar-fallback">
                            {{ strtoupper(substr(Auth::user()->name, 0, 1)) }}
                        </div>
                    @endif

                    <span class="user-name">{{ Auth::user()->name }}</span>

                    <svg class="w-4 h-4 text-gray-500 group-hover:text-indigo-600 transition" fill="none"
                        stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                    </svg>
                </div>

            </div>
        </div>
    </div>
</nav>
