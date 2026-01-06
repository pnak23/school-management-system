<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ config('app.name', 'School Management System') }}</title>
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=figtree:400,500,600,700&display=swap" rel="stylesheet" />
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>

<body class="font-sans antialiased min-h-screen bg-gradient-to-br from-indigo-800 via-purple-700 to-pink-600">

    <!-- Animated network background -->
    <canvas id="particles-canvas" class="absolute inset-0 w-full h-full -z-10"></canvas>
    <!-- Glass Overlay -->
    <div class="absolute inset-0 bg-black/40 backdrop-blur-sm"></div>

    <!-- NAVBAR -->
    <nav class="fixed top-0 inset-x-0 z-50 bg-white/20 backdrop-blur-xl shadow-lg">
        <div class="max-w-7xl mx-auto px-6">
            <div class="flex justify-between items-center h-16">
                <a href="/"
                    class="text-3xl font-extrabold bg-gradient-to-r from-yellow-300 to-pink-300 bg-clip-text text-transparent">
                    SMS
                </a>
                <div class="flex items-center gap-4">
                    <a href="{{ route('about') }}" class="text-white hover:text-yellow-300 font-semibold">About</a>
                    <a href="{{ route('contact') }}" class="text-white hover:text-yellow-300 font-semibold">Contact</a>
                    @auth
                        <a href="{{ url('/home') }}"
                            class="px-6 py-2 rounded-full bg-yellow-400 text-indigo-800 font-bold hover:bg-yellow-500 transition">Dashboard</a>
                    @else
                        <a href="{{ route('login') }}"
                            class="px-6 py-2 rounded-full border border-white text-white font-bold hover:bg-white hover:text-indigo-800 transition">Login</a>
                        <a href="{{ route('register') }}"
                            class="px-6 py-2 rounded-full bg-pink-500 text-white font-bold hover:bg-pink-600 transition">Register</a>
                    @endauth
                </div>
            </div>
        </div>
    </nav>

    <!-- HERO + NETWORK ANIMATION -->
    <section
        class="relative min-h-screen flex flex-col items-center justify-center text-center text-white overflow-hidden">

        <!-- Hero text -->
        <div class="relative z-10 max-w-5xl px-6">
            <h1 class="text-6xl md:text-7xl font-extrabold leading-tight mb-6 drop-shadow-lg">
                Welcome to<br>
                <span class="bg-gradient-to-r from-yellow-300 to-pink-300 bg-clip-text text-transparent">
                    Library Management System
                </span>
            </h1>
            <p class="text-2xl md:text-3xl opacity-90 mb-10">
                សាខាពុទ្ធិកសកលវិទ្យាល័យព្រះសីហនុរាជ
            </p>

            <!-- CTA Buttons -->
            <div class="flex flex-wrap justify-center gap-6 mb-16">
                @auth
                    <a href="{{ url('/home') }}"
                        class="px-10 py-4 rounded-full bg-white text-indigo-800 font-bold text-lg shadow-2xl hover:scale-105 transition">
                        Go to Dashboard
                    </a>
                @else
                    <a href="{{ route('login') }}"
                        class="px-10 py-4 rounded-full bg-white text-indigo-800 font-bold text-lg shadow-2xl hover:scale-105 transition">
                        Login
                    </a>
                    <a href="{{ route('register') }}"
                        class="px-10 py-4 rounded-full bg-gradient-to-r from-yellow-400 to-pink-500 text-white font-bold text-lg shadow-2xl hover:scale-105 transition">
                        Register
                    </a>
                @endauth
            </div>

            <!-- Feature Cards -->
            <div class="flex flex-wrap justify-center gap-8">
                <div
                    class="group bg-white/20 backdrop-blur-lg rounded-3xl p-8 w-64 hover:scale-105 transition shadow-xl">
                    <div class="text-4xl mb-4">👥</div>
                    <h3 class="text-xl font-bold mb-2">User Management</h3>
                    <p>Manage students, teachers, and staff easily</p>
                </div>
                <div
                    class="group bg-white/20 backdrop-blur-lg rounded-3xl p-8 w-64 hover:scale-105 transition shadow-xl">
                    <div class="text-4xl mb-4">📚</div>
                    <h3 class="text-xl font-bold mb-2">Library Records</h3>
                    <p>Track books, borrowing, and returns</p>
                </div>
                <div
                    class="group bg-white/20 backdrop-blur-lg rounded-3xl p-8 w-64 hover:scale-105 transition shadow-xl">
                    <div class="text-4xl mb-4">🔐</div>
                    <h3 class="text-xl font-bold mb-2">Secure System</h3>
                    <p>Role-based access with full security</p>
                </div>
            </div>
        </div>

        <!-- Footer -->
        <footer class="absolute bottom-4 text-white opacity-80">
            <p>© {{ date('Y') }} School Management System. All rights reserved.</p>
        </footer>
    </section>
</body>

</html>
