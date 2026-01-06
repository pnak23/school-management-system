<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>About Us - {{ config('app.name') }}</title>

    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=figtree:400,500,700&display=swap" rel="stylesheet">

    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>

<body class="h-screen overflow-hidden font-sans bg-gradient-to-br from-indigo-900 via-purple-900 to-black text-white">

<!-- Network Animation -->
<canvas id="particles-canvas" class="absolute inset-0 w-full h-full -z-10"></canvas>

<!-- Glass Overlay -->
<div class="absolute inset-0 bg-black/40 backdrop-blur-sm"></div>

<!-- CONTENT -->
<div class="relative z-10 h-full flex flex-col">

    <!-- NAV -->
    <nav class="flex justify-between items-center px-10 py-6">
        <a href="/" class="text-2xl font-extrabold tracking-wide">SMS</a>
        <div class="space-x-6 text-sm">
            <a href="/" class="opacity-80 hover:opacity-100">Home</a>
            <span class="border-b-2 border-white pb-1">About</span>
            <a href="{{ route('contact') }}" class="opacity-80 hover:opacity-100">Contact</a>
        </div>
    </nav>

    <!-- CENTER -->
    <main class="flex-1 flex items-center justify-center text-center px-6">
        <div class="max-w-4xl">
            <h1 class="text-5xl md:text-7xl font-extrabold mb-6 leading-tight">
                About Our
                <span class="bg-gradient-to-r from-yellow-300 to-pink-400 bg-clip-text text-transparent">
                    System
                </span>
            </h1>

            <p class="text-xl md:text-2xl opacity-90 mb-10">
                Empowering education through modern technology, security, and innovation.
            </p>

            <div class="grid md:grid-cols-3 gap-8 mt-12">
                <div class="bg-white/10 backdrop-blur-xl rounded-2xl p-8 hover:scale-105 transition">
                    <div class="text-3xl mb-3">🚀</div>
                    <h3 class="text-xl font-bold mb-2">Innovation</h3>
                    <p class="text-sm opacity-80">Smart solutions for modern schools</p>
                </div>

                <div class="bg-white/10 backdrop-blur-xl rounded-2xl p-8 hover:scale-105 transition">
                    <div class="text-3xl mb-3">👥</div>
                    <h3 class="text-xl font-bold mb-2">User Focus</h3>
                    <p class="text-sm opacity-80">Designed for students & staff</p>
                </div>

                <div class="bg-white/10 backdrop-blur-xl rounded-2xl p-8 hover:scale-105 transition">
                    <div class="text-3xl mb-3">🔐</div>
                    <h3 class="text-xl font-bold mb-2">Security</h3>
                    <p class="text-sm opacity-80">Protected & role-based access</p>
                </div>
            </div>
        </div>
    </main>

    <!-- FOOTER -->
    <footer class="text-center text-sm opacity-60 pb-6">
        © {{ date('Y') }} School Management System
    </footer>
</div>

</body>
</html>
