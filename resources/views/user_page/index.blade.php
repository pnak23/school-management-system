<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Library Services - {{ config('app.name', 'School Management System') }}</title>

    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=figtree:400,500,600,700&display=swap" rel="stylesheet" />

    <!-- Bootstrap 5 -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">

    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">

    <style>
        /* ===== Variables ===== */
        :root {
            --primary: #4f46e5;
            --primary-dark: #4338ca;
            --secondary: #7c3aed;
            --success: #10b981;
            --info: #06b6d4;
            --warning: #f59e0b;
            --danger: #ef4444;
            --glass-bg: rgba(255, 255, 255, 0.1);
            --glass-blur: 15px;
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Figtree', sans-serif;
            background: linear-gradient(135deg, #667eea 0%, #06b6d4 100%);
            min-height: 100vh;
            padding: 20px;
            color: white;
        }

        /* ===== Container ===== */
        .user-page-container {
            max-width: 1400px;
            margin: 0 auto;
        }

        /* ===== Header ===== */
        .header-section {
            text-align: center;
            margin-bottom: 50px;
        }

        .header-section h1 {
            font-size: 3rem;
            font-weight: 700;
            text-shadow: 2px 2px 10px rgba(0, 0, 0, 0.2);
        }

        .header-section p {
            font-size: 1.2rem;
            opacity: 0.85;
        }

        /* ===== Services Grid ===== */
        .services-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
            gap: 30px;
        }

        .service-card {
            background: var(--glass-bg);
            backdrop-filter: blur(var(--glass-blur));
            border-radius: 25px;
            padding: 30px;
            box-shadow: 0 20px 40px rgba(0, 0, 0, 0.25);
            transition: all 0.4s ease;
            text-decoration: none;
            color: white;
            display: flex;
            flex-direction: column;
            align-items: center;
            position: relative;
            overflow: hidden;
        }

        .service-card:hover {
            transform: translateY(-15px);
            box-shadow: 0 30px 60px rgba(0, 0, 0, 0.35);
        }

        .service-card::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 5px;
            background: linear-gradient(90deg, var(--primary), var(--secondary));
            transform: scaleX(0);
            transition: transform 0.4s ease;
        }

        .service-card:hover::before {
            transform: scaleX(1);
        }

        .service-icon {
            width: 80px;
            height: 80px;
            border-radius: 20px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 2.5rem;
            margin-bottom: 20px;
            transition: transform 0.3s ease;
        }

        .service-card:hover .service-icon {
            transform: scale(1.15) rotate(5deg);
        }

        .service-icon.primary {
            background: linear-gradient(135deg, var(--primary), var(--primary-dark));
        }

        .service-icon.success {
            background: linear-gradient(135deg, var(--success), #059669);
        }

        .service-icon.info {
            background: linear-gradient(135deg, var(--info), #0891b2);
        }

        .service-icon.warning {
            background: linear-gradient(135deg, var(--warning), #d97706);
        }

        .service-icon.secondary {
            background: linear-gradient(135deg, var(--secondary), #6d28d9);
        }

        .service-icon.danger {
            background: linear-gradient(135deg, var(--danger), #dc2626);
        }

        .service-title {
            font-size: 1.5rem;
            font-weight: 700;
            margin-bottom: 12px;
        }

        .service-description {
            font-size: 1rem;
            text-align: center;
            color: rgba(255, 255, 255, 0.85);
            line-height: 1.5;
        }

        /* ===== Footer ===== */
        .footer-section {
            margin-top: 60px;
            text-align: center;
        }

        .footer-section h4 {
            font-size: 1.5rem;
            margin-bottom: 10px;
        }

        .footer-section p {
            color: rgba(255, 255, 255, 0.7);
        }

        .footer-links {
            display: flex;
            justify-content: center;
            gap: 25px;
            flex-wrap: wrap;
            margin-top: 20px;
        }

        .footer-link {
            color: white;
            text-decoration: none;
            font-weight: 500;
            transition: all 0.3s ease;
        }

        .footer-link:hover {
            color: var(--primary);
            text-decoration: underline;
        }

        /* ===== Responsive ===== */
        @media (max-width: 768px) {
            .header-section h1 {
                font-size: 2.5rem;
            }

            .service-card {
                padding: 25px;
            }
        }

        @media (max-width: 480px) {
            body {
                padding: 10px;
            }

            .header-section h1 {
                font-size: 2rem;
            }

            .service-icon {
                width: 70px;
                height: 70px;
                font-size: 2rem;
            }
        }
    </style>
</head>

<body>
    <div class="user-page-container">
        <!-- Header -->
        <div class="header-section">
            <h1><i class="fas fa-book-reader"></i> បណ្ណាល័យ</h1>
            <p>សាខាពុទ្ធិកវិទ្យាល័យព្រះសីហនុរាជបាត់ដំបង</p>
        </div>

        <!-- Services Grid -->
        <div class="services-grid">
            <!-- Check-in/Out -->
            <a href="{{ route('qr.library.visits.index') }}" class="service-card">
                <div class="service-icon primary">
                    <i class="fas fa-door-open"></i>
                </div>
                <h3 class="service-title">Library Check-in/Out</h3>
                <p class="service-description">Check in when entering the library and check out when leaving. Track your
                    library visits.</p>
            </a>

            <!-- Start Reading -->
            <a href="{{ route('qr.library.start-reading.form') }}" class="service-card">
                <div class="service-icon success">
                    <i class="fas fa-book-open"></i>
                </div>
                <h3 class="service-title">Start Reading</h3>
                <p class="service-description">Begin a reading session for any book. Track your reading time and
                    activities.</p>
            </a>

            <!-- Browse Books -->
            <a href="{{ route('admin.library.books_report.index') }}" class="service-card">
                <div class="service-icon info">
                    <i class="fas fa-book"></i>
                </div>
                <h3 class="service-title">Browse Books</h3>
                <p class="service-description">Search and browse our library collection. Reserve unavailable books or
                    borrow available ones.</p>
            </a>

            <!-- Reports -->
            <a href="{{ route('admin.library.reports.collection_summary.index') }}" class="service-card">
                <div class="service-icon warning">
                    <i class="fas fa-chart-bar"></i>
                </div>
                <h3 class="service-title">Library Reports</h3>
                <p class="service-description">View library statistics, collection summaries, overdue loans, and various
                    reports.</p>
            </a>

            <!-- About -->
            <a href="{{ route('about') }}" class="service-card">
                <div class="service-icon secondary">
                    <i class="fas fa-info-circle"></i>
                </div>
                <h3 class="service-title">About Us</h3>
                <p class="service-description">Learn more about our library system, mission, and values.</p>
            </a>

            <!-- Contact -->
            <a href="{{ route('contact') }}" class="service-card">
                <div class="service-icon danger">
                    <i class="fas fa-envelope"></i>
                </div>
                <h3 class="service-title">Contact Us</h3>
                <p class="service-description">Get in touch with us. Send us a message or find our contact information.
                </p>
            </a>
        </div>

        <!-- Footer -->
        <div class="footer-section">
            <h4><i class="fas fa-graduation-cap"></i> {{ config('app.name', 'School Management System') }}</h4>
            <p>Your gateway to library services and resources</p>
            <div class="footer-links">
                <a href="{{ route('about') }}" class="footer-link"><i class="fas fa-info-circle"></i> About</a>
                <a href="{{ route('contact') }}" class="footer-link"><i class="fas fa-envelope"></i> Contact</a>
                @auth
                    <a href="{{ route('dashboard') }}" class="footer-link"><i class="fas fa-tachometer-alt"></i>
                        Dashboard</a>
                @else
                    <a href="{{ route('login') }}" class="footer-link"><i class="fas fa-sign-in-alt"></i> Login</a>
                @endauth
            </div>
        </div>
    </div>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>

</html>
