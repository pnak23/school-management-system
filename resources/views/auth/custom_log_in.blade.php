<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Login - School Management System</title>
@vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="relative min-h-screen bg-gradient-to-br from-indigo-800 via-purple-700 to-pink-600 flex items-center justify-center overflow-hidden">

<!-- Animated particles -->
<canvas id="particles-canvas" class="absolute inset-0 w-full h-full -z-10"></canvas>
<!-- Glass Overlay -->
<div class="absolute inset-0 bg-black/40 backdrop-blur-sm"></div>

<!-- Login Container -->
<div class="relative z-10 w-full max-w-md p-6 rounded-3xl bg-white/20 backdrop-blur-lg shadow-2xl">
    
    <!-- Logo -->
    <div class="text-center mb-8">
        <div class="inline-flex items-center justify-center w-16 h-16 bg-gradient-to-r from-yellow-400 to-pink-500 rounded-2xl shadow-lg mb-4 animate-pulse">
            <svg class="w-10 h-10 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253" />
            </svg>
        </div>
        <h1 class="text-3xl font-bold text-white drop-shadow-lg">Welcome Back</h1>
        <p class="text-white/90 mt-2">Sign in to your account</p>
    </div>

    <!-- Form -->
    <form method="POST" action="{{ route('login') }}">
        @csrf

        <div class="mb-5">
            <label for="login" class="block text-sm font-semibold text-white/90 mb-2">Name or Email</label>
            <div class="relative">
                <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                    <svg class="h-5 w-5 text-white/60" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                    </svg>
                </div>
                <input 
                    id="login" 
                    type="text" 
                    name="login" 
                    value="{{ old('login') }}" 
                    required autofocus
                    class="w-full pl-10 pr-4 py-3 rounded-lg border border-white/30 bg-white/20 text-white placeholder-white/70 focus:outline-none focus:ring-2 focus:ring-yellow-400 focus:border-yellow-400 transition"
                    placeholder="Enter your name or email">
            </div>
        </div>

        <div class="mb-5">
            <label for="password" class="block text-sm font-semibold text-white/90 mb-2">Password</label>
            <div class="relative">
                <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                    <svg class="h-5 w-5 text-white/60" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z" />
                    </svg>
                </div>
                <input 
                    id="password" 
                    type="password" 
                    name="password" 
                    required
                    class="w-full pl-10 pr-4 py-3 rounded-lg border border-white/30 bg-white/20 text-white placeholder-white/70 focus:outline-none focus:ring-2 focus:ring-yellow-400 focus:border-yellow-400 transition"
                    placeholder="••••••••">
            </div>
        </div>

        <div class="flex items-center justify-between mb-6 text-white/90">
            <label class="flex items-center">
                <input type="checkbox" name="remember" class="w-4 h-4 text-yellow-400 border-white rounded focus:ring-yellow-400">
                <span class="ml-2 text-sm">Remember me</span>
            </label>
            @if (Route::has('password.request'))
                <a href="{{ route('password.request') }}" class="text-sm hover:text-yellow-300 font-medium">Forgot password?</a>
            @endif
        </div>

        <button type="submit" class="w-full py-3 rounded-lg bg-gradient-to-r from-yellow-400 to-pink-500 text-white font-bold shadow-xl hover:scale-105 transition transform">
            Sign In
        </button>
    </form>

    <div class="mt-6 text-center text-white/80">
        <span>Don't have an account?</span> <a href="{{ route('register') }}" class="text-yellow-400 hover:text-yellow-300 font-semibold">Create one →</a>
    </div>

</div>

</body>
</html>
