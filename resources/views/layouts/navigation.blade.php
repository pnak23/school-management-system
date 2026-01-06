<nav x-data="{ open: false }" class="bg-gradient-to-r from-indigo-600 via-purple-600 to-indigo-700 shadow-lg sticky top-0 z-50">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="flex justify-between h-16">

            <!-- LEFT -->
            <div class="flex items-center gap-10">

                <!-- LOGO -->
                <a href="{{ route('dashboard') }}" class="flex items-center gap-3 group">
                    <div class="w-10 h-10 rounded-xl bg-white/20 flex items-center justify-center shadow-md group-hover:scale-105 transition">
                        <x-application-logo class="h-6 w-auto fill-current text-white" />
                    </div>
                    <span class="text-white font-extrabold text-lg tracking-wide">
                        School Management
                    </span>
                </a>

                <!-- DESKTOP MENU -->
                <div class="hidden md:flex items-center gap-2">
                    <a href="{{ route('dashboard') }}"
                       class="nav-link {{ request()->routeIs('dashboard') ? 'active' : '' }}">
                        Dashboard
                    </a>

                    <a href="{{ route('admin.users.index') }}"
                       class="nav-link {{ request()->routeIs('admin.users.*') ? 'active' : '' }}">
                        Users
                    </a>

                    <a href="{{ route('admin.roles.index') }}"
                       class="nav-link {{ request()->routeIs('admin.roles.*') ? 'active' : '' }}">
                        Roles
                    </a>
                </div>
            </div>

            <!-- RIGHT -->
            <div class="hidden md:flex items-center gap-4">

                <!-- USER DROPDOWN -->
                <div x-data="{ show: false }" class="relative">
                    <button @click="show = !show"
                            class="flex items-center gap-3 bg-white/20 hover:bg-white/30 px-3 py-2 rounded-xl text-white transition">
                        <div class="w-9 h-9 rounded-full bg-white text-indigo-600 font-bold flex items-center justify-center shadow">
                            {{ strtoupper(substr(Auth::user()->name,0,1)) }}
                        </div>
                        <span class="font-medium">{{ Auth::user()->name }}</span>
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                  d="M19 9l-7 7-7-7"/>
                        </svg>
                    </button>

                    <!-- DROPDOWN -->
                    <div x-show="show" @click.outside="show=false"
                         x-transition
                         class="absolute right-0 mt-3 w-48 bg-white rounded-xl shadow-xl overflow-hidden">

                        <a href="{{ route('profile.edit') }}"
                           class="block px-4 py-3 text-sm text-gray-700 hover:bg-indigo-50">
                            Profile
                        </a>

                        <form method="POST" action="{{ route('logout') }}">
                            @csrf
                            <button type="submit"
                                    class="w-full text-left px-4 py-3 text-sm text-red-600 hover:bg-red-50">
                                Log Out
                            </button>
                        </form>
                    </div>
                </div>
            </div>

            <!-- MOBILE BUTTON -->
            <div class="flex items-center md:hidden">
                <button @click="open = !open"
                        class="p-2 rounded-lg bg-white/20 text-white hover:bg-white/30 transition">
                    <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path :class="{ 'hidden': open }" stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                              d="M4 6h16M4 12h16M4 18h16"/>
                        <path :class="{ 'hidden': !open }" stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                              d="M6 18L18 6M6 6l12 12"/>
                    </svg>
                </button>
            </div>
        </div>
    </div>

    <!-- MOBILE MENU -->
    <div x-show="open" x-transition class="md:hidden bg-white shadow-xl">
        <div class="px-4 pt-4 pb-3 space-y-2">

            <a href="{{ route('dashboard') }}" class="mobile-link">
                Dashboard
            </a>

            <a href="{{ route('admin.users.index') }}" class="mobile-link">
                Users
            </a>

            <a href="{{ route('admin.roles.index') }}" class="mobile-link">
                Roles
            </a>

            <div class="border-t pt-3">
                <a href="{{ route('profile.edit') }}" class="mobile-link">
                    Profile
                </a>

                <form method="POST" action="{{ route('logout') }}">
                    @csrf
                    <button type="submit" class="mobile-link text-red-600">
                        Log Out
                    </button>
                </form>
            </div>
        </div>
    </div>
</nav>
