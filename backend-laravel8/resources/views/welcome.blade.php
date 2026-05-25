@php
    $isRtl = app()->getLocale() === 'ar';
    $currentLang = app()->getLocale();
@endphp
<!DOCTYPE html>
<html lang="{{ $currentLang }}" dir="{{ $isRtl ? 'rtl' : 'ltr' }}">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Amazon Product Analyzer - Professional Amazon Analytics</title>
    <meta name="description" content="Helium 10-level analytics for Amazon sellers. Analyze keywords, track BSR, estimate sales, and discover profitable opportunities.">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    @if($isRtl)
    <link href="https://fonts.googleapis.com/css2?family=Cairo:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    @endif
    <style>
        :root {
            --primary: #6366f1;
            --primary-dark: #4f46e5;
            --primary-light: #818cf8;
            --secondary: #0ea5e9;
            --accent: #f59e0b;
            --success: #10b981;
            --danger: #ef4444;
            --dark: #0f172a;
            --dark-light: #1e293b;
            --gray: #64748b;
            --gray-light: #94a3b8;
            --white: #ffffff;
            --gradient: linear-gradient(135deg, var(--primary) 0%, var(--secondary) 100%);
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: {{ $isRtl ? "'Cairo'" : "'Inter'" }}, -apple-system, BlinkMacSystemFont, sans-serif;
            background: var(--dark);
            color: var(--white);
            line-height: 1.6;
            overflow-x: hidden;
        }

        /* Navbar */
        .navbar {
            position: fixed;
            top: 0;
            left: 0;
            right: 0;
            z-index: 1000;
            padding: 1rem 2rem;
            display: flex;
            justify-content: space-between;
            align-items: center;
            background: rgba(15, 23, 42, 0.9);
            backdrop-filter: blur(20px);
            border-bottom: 1px solid rgba(255,255,255,0.1);
        }

        .logo {
            display: flex;
            align-items: center;
            gap: 0.75rem;
            font-size: 1.5rem;
            font-weight: 700;
            color: var(--white);
            text-decoration: none;
        }

        .logo-icon {
            width: 40px;
            height: 40px;
            background: var(--gradient);
            border-radius: 10px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.25rem;
        }

        .nav-links {
            display: flex;
            gap: 2rem;
            align-items: center;
        }

        .nav-links a {
            color: var(--gray-light);
            text-decoration: none;
            font-weight: 500;
            transition: color 0.3s;
        }

        .nav-links a:hover {
            color: var(--white);
        }

        .nav-buttons {
            display: flex;
            gap: 1rem;
        }

        .btn {
            padding: 0.75rem 1.5rem;
            border-radius: 10px;
            font-weight: 600;
            text-decoration: none;
            transition: all 0.3s;
            cursor: pointer;
            border: none;
            font-size: 0.95rem;
            font-family: inherit;
        }

        .btn-outline {
            background: transparent;
            border: 2px solid var(--gray);
            color: var(--white);
        }

        .btn-outline:hover {
            border-color: var(--primary);
            color: var(--primary);
        }

        .btn-primary {
            background: var(--gradient);
            color: var(--white);
            box-shadow: 0 4px 15px rgba(99, 102, 241, 0.4);
        }

        .btn-primary:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 20px rgba(99, 102, 241, 0.5);
        }

        .lang-btn {
            padding: 0.5rem 1rem;
            font-size: 0.85rem;
            background: rgba(255,255,255,0.1);
            border: none;
        }

        .lang-btn:hover {
            background: rgba(255,255,255,0.2);
        }

        /* Hero Section */
        .hero {
            min-height: 100vh;
            padding: 8rem 2rem 4rem;
            display: flex;
            align-items: center;
            position: relative;
            overflow: hidden;
        }

        .hero::before {
            content: '';
            position: absolute;
            top: -50%;
            {{ $isRtl ? 'left' : 'right' }}: -20%;
            width: 80%;
            height: 200%;
            background: radial-gradient(ellipse, rgba(99, 102, 241, 0.15) 0%, transparent 70%);
            pointer-events: none;
        }

        .hero::after {
            content: '';
            position: absolute;
            bottom: -30%;
            {{ $isRtl ? 'right' : 'left' }}: -10%;
            width: 60%;
            height: 100%;
            background: radial-gradient(ellipse, rgba(14, 165, 233, 0.1) 0%, transparent 70%);
            pointer-events: none;
        }

        .hero-container {
            max-width: 1400px;
            margin: 0 auto;
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 4rem;
            align-items: center;
            position: relative;
            z-index: 1;
        }

        .hero-badge {
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
            padding: 0.5rem 1rem;
            background: rgba(99, 102, 241, 0.1);
            border: 1px solid rgba(99, 102, 241, 0.3);
            border-radius: 50px;
            font-size: 0.875rem;
            color: var(--primary-light);
            margin-bottom: 1.5rem;
        }

        .hero h1 {
            font-size: 3.5rem;
            font-weight: 800;
            line-height: 1.1;
            margin-bottom: 1.5rem;
        }

        .hero h1 span {
            background: var(--gradient);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
        }

        .hero p {
            font-size: 1.25rem;
            color: var(--gray-light);
            margin-bottom: 2rem;
            max-width: 500px;
        }

        .hero-buttons {
            display: flex;
            gap: 1rem;
            margin-bottom: 3rem;
        }

        .hero-stats {
            display: flex;
            gap: 3rem;
        }

        .stat {
            text-align: {{ $isRtl ? 'right' : 'left' }};
        }

        .stat-value {
            font-size: 2rem;
            font-weight: 700;
            color: var(--white);
        }

        .stat-label {
            font-size: 0.875rem;
            color: var(--gray);
        }

        .hero-image {
            position: relative;
        }

        .hero-dashboard {
            background: var(--dark-light);
            border-radius: 20px;
            padding: 1.5rem;
            border: 1px solid rgba(255,255,255,0.1);
            box-shadow: 0 25px 50px rgba(0,0,0,0.5);
        }

        .dashboard-header {
            display: flex;
            gap: 0.5rem;
            margin-bottom: 1rem;
        }

        .dashboard-dot {
            width: 12px;
            height: 12px;
            border-radius: 50%;
        }

        .dashboard-content {
            background: var(--dark);
            border-radius: 12px;
            padding: 1.5rem;
        }

        .dashboard-row {
            display: flex;
            justify-content: space-between;
            padding: 0.75rem 0;
            border-bottom: 1px solid rgba(255,255,255,0.05);
        }

        .dashboard-row:last-child {
            border-bottom: none;
        }

        .dashboard-keyword {
            color: var(--white);
            font-weight: 500;
        }

        .dashboard-volume {
            color: var(--success);
            font-weight: 600;
        }

        /* Features Section */
        .features {
            padding: 6rem 2rem;
            background: linear-gradient(180deg, var(--dark) 0%, var(--dark-light) 100%);
        }

        .section-title {
            text-align: center;
            margin-bottom: 4rem;
        }

        .section-title h2 {
            font-size: 2.5rem;
            font-weight: 700;
            margin-bottom: 1rem;
        }

        .section-title p {
            color: var(--gray-light);
            font-size: 1.125rem;
            max-width: 600px;
            margin: 0 auto;
        }

        .features-grid {
            max-width: 1200px;
            margin: 0 auto;
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 2rem;
        }

        .feature-card {
            background: var(--dark);
            border: 1px solid rgba(255,255,255,0.1);
            border-radius: 20px;
            padding: 2rem;
            transition: all 0.3s;
            text-align: {{ $isRtl ? 'right' : 'left' }};
        }

        .feature-card:hover {
            transform: translateY(-5px);
            border-color: var(--primary);
            box-shadow: 0 10px 40px rgba(99, 102, 241, 0.2);
        }

        .feature-icon {
            width: 60px;
            height: 60px;
            background: var(--gradient);
            border-radius: 15px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.5rem;
            margin-bottom: 1.5rem;
        }

        .feature-card h3 {
            font-size: 1.25rem;
            font-weight: 600;
            margin-bottom: 0.75rem;
        }

        .feature-card p {
            color: var(--gray-light);
            font-size: 0.95rem;
        }

        /* Pricing Section */
        .pricing {
            padding: 6rem 2rem;
            background: var(--dark);
        }

        .pricing-grid {
            max-width: 900px;
            margin: 0 auto;
            display: grid;
            grid-template-columns: repeat(2, 1fr);
            gap: 2rem;
        }

        .pricing-card {
            background: var(--dark-light);
            border: 1px solid rgba(255,255,255,0.1);
            border-radius: 20px;
            padding: 2.5rem;
            position: relative;
            overflow: hidden;
            text-align: {{ $isRtl ? 'right' : 'left' }};
        }

        .pricing-card.featured {
            border-color: var(--primary);
            box-shadow: 0 10px 40px rgba(99, 102, 241, 0.3);
        }

        .pricing-card.featured::before {
            content: 'POPULAR';
            position: absolute;
            top: 20px;
            {{ $isRtl ? 'left' : 'right' }}: -35px;
            background: var(--gradient);
            padding: 0.25rem 2.5rem;
            font-size: 0.75rem;
            font-weight: 600;
            transform: rotate({{ $isRtl ? '-45deg' : '45deg' }});
        }

        .pricing-name {
            font-size: 1.25rem;
            font-weight: 600;
            margin-bottom: 0.5rem;
        }

        .pricing-price {
            font-size: 3rem;
            font-weight: 800;
            margin-bottom: 1.5rem;
        }

        .pricing-price span {
            font-size: 1rem;
            font-weight: 400;
            color: var(--gray);
        }

        .pricing-features {
            list-style: none;
            margin-bottom: 2rem;
        }

        .pricing-features li {
            padding: 0.5rem 0;
            color: var(--gray-light);
            display: flex;
            align-items: center;
            gap: 0.75rem;
        }

        .pricing-features li::before {
            content: '✓';
            color: var(--success);
            font-weight: 600;
        }

        /* Footer */
        .footer {
            padding: 3rem 2rem;
            background: var(--dark-light);
            border-top: 1px solid rgba(255,255,255,0.1);
            text-align: center;
        }

        .footer p {
            color: var(--gray);
        }

        /* Responsive */
        @media (max-width: 968px) {
            .hero-container {
                grid-template-columns: 1fr;
                text-align: center;
            }

            .hero h1 {
                font-size: 2.5rem;
            }

            .hero p {
                margin: 0 auto 2rem;
            }

            .hero-buttons {
                justify-content: center;
            }

            .hero-stats {
                justify-content: center;
            }

            .hero-image {
                display: none;
            }

            .features-grid {
                grid-template-columns: 1fr;
            }

            .pricing-grid {
                grid-template-columns: 1fr;
            }

            .nav-links {
                display: none;
            }
        }
    </style>
</head>
<body>
    <!-- Navbar -->
    <nav class="navbar">
        <a href="/" class="logo">
            <div class="logo-icon">📊</div>
            Amazon Analyzer
        </a>
        <div class="nav-links">
            <a href="#features">{{ __('site.features') }}</a>
            <a href="#pricing">{{ __('site.pricing') }}</a>
            <a href="/guide">📚 {{ __('site.guide') }}</a>
            <a href="/suppliers">🏪 {{ __('site.suppliers') }}</a>
            <a href="/suppliers/products">📦 {{ __('site.products') }}</a>
        </div>
        <div class="nav-buttons">
            <a href="?lang={{ $currentLang === 'ar' ? 'en' : 'ar' }}" class="btn btn-outline lang-btn">
                {{ __('site.switch_lang') }}
            </a>
            <a href="/login" class="btn btn-outline">{{ __('site.login') }}</a>
            <a href="/register" class="btn btn-primary">{{ __('site.get_started') }}</a>
        </div>
    </nav>

    <!-- Hero Section -->
    <section class="hero">
        <div class="hero-container">
            <div class="hero-content">
                <div class="hero-badge">
                    {{ __('site.hero_badge') }}
                </div>
                <h1>
                    {{ __('site.hero_title_1') }}<br>
                    <span>{{ __('site.hero_title_2') }}</span>
                </h1>
                <p>{{ __('site.hero_description') }}</p>
                <div class="hero-buttons">
                    <a href="/register" class="btn btn-primary">{{ __('site.start_free_trial') }}</a>
                    <a href="#features" class="btn btn-outline">{{ __('site.see_features') }}</a>
                </div>
                <div class="hero-stats">
                    <div class="stat">
                        <div class="stat-value">50K+</div>
                        <div class="stat-label">{{ __('site.stat_keywords') }}</div>
                    </div>
                    <div class="stat">
                        <div class="stat-value">98%</div>
                        <div class="stat-label">{{ __('site.stat_accuracy') }}</div>
                    </div>
                    <div class="stat">
                        <div class="stat-value">2min</div>
                        <div class="stat-label">{{ __('site.stat_setup') }}</div>
                    </div>
                </div>
            </div>
            <div class="hero-image">
                <div class="hero-dashboard">
                    <div class="dashboard-header">
                        <div class="dashboard-dot" style="background: #ef4444;"></div>
                        <div class="dashboard-dot" style="background: #f59e0b;"></div>
                        <div class="dashboard-dot" style="background: #10b981;"></div>
                    </div>
                    <div class="dashboard-content">
                        <div class="dashboard-row">
                            <span class="dashboard-keyword">office chair</span>
                            <span class="dashboard-volume">45,000 /mo</span>
                        </div>
                        <div class="dashboard-row">
                            <span class="dashboard-keyword">ergonomic desk chair</span>
                            <span class="dashboard-volume">28,500 /mo</span>
                        </div>
                        <div class="dashboard-row">
                            <span class="dashboard-keyword">gaming chair</span>
                            <span class="dashboard-volume">62,000 /mo</span>
                        </div>
                        <div class="dashboard-row">
                            <span class="dashboard-keyword">mesh office chair</span>
                            <span class="dashboard-volume">18,200 /mo</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Features Section -->
    <section class="features" id="features">
        <div class="section-title">
            <h2>{{ __('site.features_title') }}</h2>
            <p>{{ __('site.features_subtitle') }}</p>
        </div>
        <div class="features-grid">
            <div class="feature-card">
                <div class="feature-icon">🔍</div>
                <h3>{{ __('site.feature1_title') }}</h3>
                <p>{{ __('site.feature1_desc') }}</p>
            </div>
            <div class="feature-card">
                <div class="feature-icon">📈</div>
                <h3>{{ __('site.feature2_title') }}</h3>
                <p>{{ __('site.feature2_desc') }}</p>
            </div>
            <div class="feature-card">
                <div class="feature-icon">🔄</div>
                <h3>{{ __('site.feature3_title') }}</h3>
                <p>{{ __('site.feature3_desc') }}</p>
            </div>
            <div class="feature-card">
                <div class="feature-icon">💰</div>
                <h3>{{ __('site.feature4_title') }}</h3>
                <p>{{ __('site.feature4_desc') }}</p>
            </div>
            <div class="feature-card">
                <div class="feature-icon">🎯</div>
                <h3>{{ __('site.feature5_title') }}</h3>
                <p>{{ __('site.feature5_desc') }}</p>
            </div>
            <div class="feature-card">
                <div class="feature-icon">🌍</div>
                <h3>{{ __('site.feature6_title') }}</h3>
                <p>{{ __('site.feature6_desc') }}</p>
            </div>
        </div>
    </section>

    <!-- Pricing Section -->
    <section class="pricing" id="pricing">
        <div class="section-title">
            <h2>{{ __('site.pricing_title') }}</h2>
            <p>{{ __('site.pricing_subtitle') }}</p>
        </div>
        <div class="pricing-grid">
            <div class="pricing-card">
                <div class="pricing-name">{{ __('site.free') }}</div>
                <div class="pricing-price">$0 <span>{{ __('site.month') }}</span></div>
                <ul class="pricing-features">
                    <li>{{ __('site.free_feature1') }}</li>
                    <li>{{ __('site.free_feature2') }}</li>
                    <li>{{ __('site.free_feature3') }}</li>
                    <li>{{ __('site.free_feature4') }}</li>
                </ul>
                <a href="/register" class="btn btn-outline" style="width: 100%; text-align: center;">{{ __('site.get_started') }}</a>
            </div>
            <div class="pricing-card featured">
                <div class="pricing-name">{{ __('site.pro') }}</div>
                <div class="pricing-price">$29 <span>{{ __('site.month') }}</span></div>
                <ul class="pricing-features">
                    <li>{{ __('site.pro_feature1') }}</li>
                    <li>{{ __('site.pro_feature2') }}</li>
                    <li>{{ __('site.pro_feature3') }}</li>
                    <li>{{ __('site.pro_feature4') }}</li>
                    <li>{{ __('site.pro_feature5') }}</li>
                    <li>{{ __('site.pro_feature6') }}</li>
                </ul>
                <a href="/register?plan=pro" class="btn btn-primary" style="width: 100%; text-align: center;">{{ __('site.start_free_trial') }}</a>
            </div>
        </div>
    </section>

    <!-- Footer -->
    <footer class="footer">
        <p>{{ __('site.copyright') }}</p>
    </footer>
</body>
</html>
