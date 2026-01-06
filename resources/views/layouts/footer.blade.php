
<!-- System Footer -->
<footer class="bg-gray-800 text-gray-300 text-sm h-12 flex items-center justify-between px-6 shadow-inner">
    <!-- Left: Copyright -->
    <div>
        &copy; {{ date('Y') }} School Management System
    </div>

    <!-- Right: Quick Links or Version -->
    <div class="flex items-center space-x-4">
        <span class="hidden md:inline">v1.0.0</span>
        <a href="{{ route('about') }}" class="hover:text-white transition">About</a>
        <a href="{{ route('contact') }}" class="hover:text-white transition">Contact</a>
        <a href="#" class="text-gray-300 hover:text-white text-sm">Privacy Policy</a>
        <a href="#" class="text-gray-300 hover:text-white text-sm">Terms</a>
    </div>
</footer>
