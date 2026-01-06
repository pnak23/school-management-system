<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Contact Us - {{ config('app.name') }}</title>

    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=figtree:400,500,700&display=swap" rel="stylesheet">

    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>

<body class="h-screen overflow-hidden font-sans text-white bg-gradient-to-br from-indigo-900 via-purple-900 to-black">

<!-- Animated Network -->
<canvas id="particles-canvas" class="absolute inset-0 w-full h-full -z-10"></canvas>

<!-- Dark glass overlay -->
<div class="absolute inset-0 bg-black/50 backdrop-blur-sm"></div>

<div class="relative z-10 h-full flex flex-col">

    <!-- NAV -->
    <nav class="flex justify-between items-center px-10 py-6">
        <a href="/" class="text-2xl font-extrabold tracking-wide">SMS</a>
        <div class="space-x-6 text-sm">
            <a href="/" class="opacity-80 hover:opacity-100">Home</a>
            <a href="{{ route('about') }}" class="opacity-80 hover:opacity-100">About</a>
            <span class="border-b-2 border-white pb-1">Contact</span>
        </div>
    </nav>

    <!-- CONTENT -->
    <main class="flex-1 flex items-center justify-center px-6">
        <div class="max-w-6xl w-full grid md:grid-cols-2 gap-10">

            <!-- CONTACT FORM -->
            <div class="bg-white/10 backdrop-blur-xl rounded-2xl p-8 shadow-2xl">
                <h2 class="text-3xl font-bold mb-6">Send Us a Message</h2>

                @if(session('success'))
                    <div class="mb-4 p-4 rounded-lg bg-green-500/20 text-green-300">
                        {{ session('success') }}
                    </div>
                @endif

                <form action="{{ route('contact.submit') }}" method="POST" class="space-y-4">
                    @csrf

                    <input type="text" name="name" placeholder="Your name"
                        class="w-full bg-white/20 border border-white/20 rounded-lg px-4 py-3 focus:ring-2 focus:ring-indigo-400 outline-none">

                    <input type="email" name="email" placeholder="Your email"
                        class="w-full bg-white/20 border border-white/20 rounded-lg px-4 py-3 focus:ring-2 focus:ring-indigo-400 outline-none">

                    <input type="text" name="subject" placeholder="Subject"
                        class="w-full bg-white/20 border border-white/20 rounded-lg px-4 py-3 focus:ring-2 focus:ring-indigo-400 outline-none">

                    <textarea name="message" rows="4" placeholder="Your message..."
                        class="w-full bg-white/20 border border-white/20 rounded-lg px-4 py-3 focus:ring-2 focus:ring-indigo-400 outline-none"></textarea>

                    <button
                        class="w-full bg-gradient-to-r from-indigo-500 to-purple-600 py-3 rounded-lg font-semibold hover:scale-[1.02] transition shadow-lg">
                        Send Message
                    </button>
                </form>
            </div>

            <!-- CONTACT INFO -->
            <div class="bg-white/10 backdrop-blur-xl rounded-2xl p-8 shadow-2xl flex flex-col justify-between">
                <div>
                    <h2 class="text-3xl font-bold mb-6">Contact Information</h2>

                    <ul class="space-y-5 text-sm opacity-90">
                        <li class="flex gap-4">
                            📍 <span>123 Education Street, City, Country</span>
                        </li>
                        <li class="flex gap-4">
                            ✉️ <span>info@schoolmanagement.com</span>
                        </li>
                        <li class="flex gap-4">
                            📞 <span>+855 21 000 000</span>
                        </li>
                        <li class="flex gap-4">
                            ⏰ <span>Mon – Fri: 9AM – 6PM</span>
                        </li>
                    </ul>
                </div>

                <div class="mt-10">
                    <p class="text-xs opacity-60">
                        We usually reply within 24 hours.
                    </p>
                </div>
            </div>

        </div>
    </main>

    <!-- FOOTER -->
    <footer class="text-center text-xs opacity-60 pb-6">
        © {{ date('Y') }} School Management System
    </footer>

</div>

</body>
</html>
