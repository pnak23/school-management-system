<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Register - School Management System</title>
@vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="relative min-h-screen bg-gradient-to-br from-indigo-800 via-purple-700 to-pink-600 flex items-center justify-center overflow-hidden">

<!-- Animated particles / network -->
<canvas id="particles-canvas" class="absolute inset-0 w-full h-full -z-10"></canvas>
<!-- Glass Overlay -->
<div class="absolute inset-0 bg-black/40 backdrop-blur-sm"></div>

<!-- Register Container -->
<div class="relative z-10 w-full max-w-md p-6 rounded-3xl bg-white/20 backdrop-blur-lg shadow-2xl">
    
    <!-- Logo -->
    <div class="text-center mb-8">
        <div class="inline-flex items-center justify-center w-16 h-16 bg-gradient-to-r from-yellow-400 to-pink-500 rounded-2xl shadow-lg animate-pulse mb-4">
            <svg class="w-10 h-10 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18 9v3m0 0v3m0-3h3m-3 0h-3m-2-5a4 4 0 11-8 0 4 4 0 018 0zM3 20a6 6 0 0112 0v1H3v-1z" />
            </svg>
        </div>
        <h1 class="text-3xl font-bold text-white drop-shadow-lg">Create Account</h1>
        <p class="text-white/90 mt-2">Join our school management system</p>
    </div>

    <!-- Form -->
    <form method="POST" action="{{ route('register') }}">
        @csrf

        <!-- Name -->
        <div class="mb-5">
            <label for="name" class="block text-sm font-semibold text-white/90 mb-2">Full Name</label>
            <div class="relative">
                <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                    <svg class="h-5 w-5 text-white/60" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                    </svg>
                </div>
                <input id="name" type="text" name="name" value="{{ old('name') }}" required autofocus
                    class="w-full pl-10 pr-4 py-3 rounded-lg border border-white/30 bg-white/20 text-white placeholder-white/70 focus:outline-none focus:ring-2 focus:ring-yellow-400 focus:border-yellow-400 transition"
                    placeholder="Pov Muny">
            </div>
        </div>

        <!-- Email -->
        <div class="mb-5">
            <label for="email" class="block text-sm font-semibold text-white/90 mb-2">Email Address</label>
            <div class="relative">
                <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                    <svg class="h-5 w-5 text-white/60" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 12a4 4 0 10-8 0 4 4 0 008 0zm0 0v1.5a2.5 2.5 0 005 0V12a9 9 0 10-9 9m4.5-1.206a8.959 8.959 0 01-4.5 1.207" />
                    </svg>
                </div>
                <input id="email" type="email" name="email" value="{{ old('email') }}" required
                    class="w-full pl-10 pr-4 py-3 rounded-lg border border-white/30 bg-white/20 text-white placeholder-white/70 focus:outline-none focus:ring-2 focus:ring-yellow-400 focus:border-yellow-400 transition"
                    placeholder="povmuny@email.com">
            </div>
        </div>

        <!-- Password -->
        <div class="mb-5">
            <label for="password" class="block text-sm font-semibold text-white/90 mb-2">Password</label>
            <div class="relative">
                <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                    <svg class="h-5 w-5 text-white/60" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z" />
                    </svg>
                </div>
                <input id="password" type="password" name="password" required
                    class="w-full pl-10 pr-4 py-3 rounded-lg border border-white/30 bg-white/20 text-white placeholder-white/70 focus:outline-none focus:ring-2 focus:ring-yellow-400 focus:border-yellow-400 transition"
                    placeholder="••••••••">
            </div>
            <p class="mt-1 text-xs text-white/70">Must be at least 8 characters</p>
        </div>

        <!-- Confirm Password -->
        <div class="mb-6">
            <label for="password_confirmation" class="block text-sm font-semibold text-white/90 mb-2">Confirm Password</label>
            <div class="relative">
                <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                    <svg class="h-5 w-5 text-white/60" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                </div>
                <input id="password_confirmation" type="password" name="password_confirmation" required
                    class="w-full pl-10 pr-4 py-3 rounded-lg border border-white/30 bg-white/20 text-white placeholder-white/70 focus:outline-none focus:ring-2 focus:ring-yellow-400 focus:border-yellow-400 transition"
                    placeholder="••••••••">
            </div>
        </div>

        <!-- Submit -->
        <button type="submit" class="w-full py-3 rounded-lg bg-gradient-to-r from-yellow-400 to-pink-500 text-white font-bold shadow-xl hover:scale-105 transition transform">
            Create Account
        </button>
    </form>

    <div class="mt-6 text-center text-white/80">
        Already have an account? <a href="{{ route('login') }}" class="text-yellow-400 hover:text-yellow-300 font-semibold">Sign In →</a>
    </div>

</div>
</body>
</html>
