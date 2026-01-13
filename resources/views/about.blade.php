<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>About Us - {{ config('app.name') }}</title>

    <!-- Font -->
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=figtree:400,500,700&display=swap" rel="stylesheet">

    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>

<body
    class="min-h-screen font-sans text-white bg-gradient-to-br from-indigo-900 via-purple-900 to-black overflow-x-hidden">

    <!-- Animated Background -->
    <canvas id="particles-canvas" class="fixed inset-0 w-full h-full -z-10"></canvas>
    <div class="fixed inset-0 bg-black/50 backdrop-blur-sm -z-10"></div>

    <!-- NAVBAR -->
    <nav class="flex justify-between items-center px-10 py-6">
        <a href="/" class="text-2xl font-extrabold tracking-wide">
            Dev<span class="text-pink-400">Team</span>
        </a>
        <div class="space-x-6 text-sm">
            <a href="/" class="opacity-80 hover:opacity-100">{{ __('menu.home') }}</a>
            <span class="border-b-2 border-white pb-1">{{ __('menu.about_us') }}</span>
            <a href="{{ route('contact') }}" class="opacity-80 hover:opacity-100">{{ __('menu.contact') }}</a>
        </div>
    </nav>

    <!-- HERO -->
    <section class="text-center px-6 pt-16 pb-20">
        <h1 class="text-5xl md:text-7xl font-extrabold mb-6">
            {{ __('general.about._') }}
        </h1>

        <p class="max-w-3xl mx-auto text-xl opacity-90">
            {{ __('general.about.description') }}
        </p>
    </section>

    <!-- ABOUT DESCRIPTION -->
    <section class="max-w-5xl mx-auto px-6 py-12">
        <div class="glass-box text-center">
            <h2 class="text-3xl font-bold mb-4">{{ __('general.about.who_we_are') }}</h2>
            <p class="opacity-85 leading-relaxed">
                {{ __('general.about.who_we_are_desc') }}
            </p>
        </div>
    </section>

    <!-- CORE VALUES -->
    <section class="max-w-6xl mx-auto px-6 py-16 grid md:grid-cols-3 gap-8">
        <div class="glass-card">
            <div class="text-3xl mb-4">🚀</div>
            <h3 class="text-xl font-bold mb-2">{{ __('general.about.innovation') }}</h3>
            <p class="opacity-80 text-sm">{{ __('general.about.innovation_desc') }}</p>
        </div>

        <div class="glass-card">
            <div class="text-3xl mb-4">👥</div>
            <h3 class="text-xl font-bold mb-2">{{ __('general.about.user_focus') }}</h3>
            <p class="opacity-80 text-sm">{{ __('general.about.user_focus_desc') }}</p>
        </div>

        <div class="glass-card">
            <div class="text-3xl mb-4">🔐</div>
            <h3 class="text-xl font-bold mb-2">{{ __('general.about.security') }}</h3>
            <p class="opacity-80 text-sm">{{ __('general.about.security_desc') }}</p>
        </div>
    </section>

    <!-- MISSION / VISION -->
    <section class="max-w-6xl mx-auto px-6 py-16 grid md:grid-cols-2 gap-8">
        <div class="glass-box">
            <h3 class="text-2xl font-bold mb-3">🎯 {{ __('general.about.our_mission') }}</h3>
            <p class="opacity-85 text-sm leading-relaxed">{{ __('general.about.our_mission_desc') }}</p>
        </div>

        <div class="glass-box">
            <h3 class="text-2xl font-bold mb-3">👁️ {{ __('general.about.our_vision') }}</h3>
            <p class="opacity-85 text-sm leading-relaxed">{{ __('general.about.our_vision_desc') }}</p>
        </div>
    </section>

    <!-- TEAM -->
    <section class="max-w-6xl mx-auto px-6 py-20">
        <h2 class="text-3xl font-bold text-center mb-12">{{ __('general.about.meet_the_developers') }}</h2>

        <div class="grid sm:grid-cols-2 lg:grid-cols-4 gap-8">

            <!-- Member 1 -->
            <div class="team-card">
                <img src="images/devTeam/Team-Lead.jpg" alt="Team Member"
                    class="w-24 h-24 rounded-full mx-auto object-cover mb-4">
                <h4 class="team-name">{{ __('general.dev_team.pov_muny') }}</h4>
                <h4 class="team-name">{{ __('general.about.backend_developer') }}</h4>
                <p class="team-role">{{ __('general.about.backend_developer_desc') }}</p>
            </div>

            <!-- Member 2 -->
            <div class="team-card">
                <img src="images/devTeam/Nak.png" alt="Team Member"
                    class="w-24 h-24 rounded-full mx-auto object-cover mb-4">
                <h4 class="team-name">{{ __('general.dev_team.phea_nak') }}</h4>
                <h4 class="team-name">{{ __('general.about.full_stack_developer') }}</h4>
                <p class="team-role">{{ __('general.about.full_stack_developer_desc') }}</p>
            </div>

            <!-- Member 3 -->
            <div class="team-card">
                <img src="images/devTeam/dataAnalyze.jpg" class="w-20 h-20 rounded-full mx-auto object-cover mb-4">
                <h4 class="team-name">{{ __('general.dev_team.mao_sophanich') }}</h4>
                <h4 class="team-name">{{ __('general.about.data_analyst') }}</h4>
                <p class="team-role">{{ __('general.about.data_analyst_desc') }}</p>
            </div>

            <!-- Member 4 -->
            <div class="team-card">
                <img src="images/devTeam/DevOp.jpg" class="w-20 h-20 rounded-full mx-auto object-cover mb-4">
                <h4 class="team-name">{{ __('general.dev_team.min_samoun') }}</h4>
                <h4 class="team-name">{{ __('general.about.devops_engineer') }}</h4>
                <p class="team-role">{{ __('general.about.devops_engineer_desc') }}</p>
            </div>

        </div>
    </section>


    <!-- FOOTER -->
     <!-- Footer -->
     @include('layouts.footer')
    {{-- <footer class="text-center text-sm opacity-60 py-8">
        © {{ date('Y') }} Independent Developer Team. All rights reserved.
    </footer> --}}

    <!-- STYLES -->
    <style>
        .glass-box {
            background: rgba(255, 255, 255, 0.08);
            backdrop-filter: blur(20px);
            border-radius: 1.5rem;
            padding: 2.5rem;
        }

        .glass-card {
            background: rgba(255, 255, 255, 0.08);
            backdrop-filter: blur(20px);
            border-radius: 1.5rem;
            padding: 2rem;
            transition: transform .3s;
        }

        .glass-card:hover {
            transform: scale(1.05);
        }

        .team-card {
            background: rgba(255, 255, 255, 0.07);
            backdrop-filter: blur(18px);
            border-radius: 1.5rem;
            padding: 2rem;
            text-align: center;
        }

        .team-card .card-bg {
            position: absolute;
            inset: 0;
            background: rgba(255, 255, 255, 0.08);
            backdrop-filter: blur(16px);
            -webkit-backdrop-filter: blur(16px);
            z-index: 0;
        }

        .avatar {
            font-size: 3rem;
            margin-bottom: 1rem;
        }

        .team-name {
            font-weight: bold;
            font-size: 1.2rem;
        }

        .team-role {
            font-size: .85rem;
            opacity: .8;
        }
    </style>

</body>

</html>
