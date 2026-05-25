<!DOCTYPE html>
<html lang="{{ $currentLang }}" dir="{{ $isRtl ? 'rtl' : 'ltr' }}">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ __('guide.title') }} - Amazon Product Analyzer</title>
    <meta name="description" content="{{ __('guide.subtitle') }}">
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
            --success: #10b981;
            --warning: #f59e0b;
            --danger: #ef4444;
            --dark: #0f172a;
            --dark-light: #1e293b;
            --dark-medium: #334155;
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
            min-height: 100vh;
            line-height: 1.7;
        }

        /* Header */
        .header {
            background: var(--dark-light);
            border-bottom: 1px solid rgba(255,255,255,0.1);
            padding: 1rem 2rem;
            position: sticky;
            top: 0;
            z-index: 100;
        }

        .header-content {
            max-width: 1200px;
            margin: 0 auto;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .logo {
            display: flex;
            align-items: center;
            gap: 0.75rem;
            text-decoration: none;
            color: var(--white);
            font-weight: 700;
            font-size: 1.25rem;
        }

        .logo-icon {
            width: 40px;
            height: 40px;
            background: var(--gradient);
            border-radius: 10px;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .header-actions {
            display: flex;
            gap: 1rem;
            align-items: center;
        }

        .btn {
            padding: 0.75rem 1.25rem;
            border-radius: 10px;
            font-weight: 600;
            text-decoration: none;
            transition: all 0.3s;
            cursor: pointer;
            border: none;
            font-size: 0.875rem;
            font-family: inherit;
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
        }

        .btn-outline {
            background: transparent;
            border: 2px solid rgba(255,255,255,0.2);
            color: var(--white);
        }

        .btn-outline:hover {
            border-color: var(--primary);
            color: var(--primary);
        }

        .btn-primary {
            background: var(--gradient);
            color: var(--white);
        }

        .btn-primary:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 15px rgba(99, 102, 241, 0.4);
        }

        .lang-switch {
            background: rgba(255,255,255,0.1);
            color: var(--white);
            padding: 0.5rem 1rem;
            border-radius: 8px;
            font-weight: 500;
            text-decoration: none;
            transition: all 0.3s;
        }

        .lang-switch:hover {
            background: rgba(255,255,255,0.2);
        }

        /* Hero Section */
        .hero {
            background: linear-gradient(135deg, rgba(99, 102, 241, 0.15) 0%, rgba(14, 165, 233, 0.15) 100%);
            padding: 4rem 2rem;
            text-align: center;
            border-bottom: 1px solid rgba(255,255,255,0.1);
        }

        .hero h1 {
            font-size: 2.5rem;
            font-weight: 800;
            margin-bottom: 1rem;
            background: var(--gradient);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
        }

        .hero p {
            font-size: 1.25rem;
            color: var(--gray-light);
            max-width: 700px;
            margin: 0 auto;
        }

        /* Main Content */
        .main {
            max-width: 1200px;
            margin: 0 auto;
            padding: 3rem 2rem;
        }

        /* Methods Grid */
        .methods-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(350px, 1fr));
            gap: 2rem;
            margin-bottom: 3rem;
        }

        .method-card {
            background: var(--dark-light);
            border: 1px solid rgba(255,255,255,0.1);
            border-radius: 20px;
            padding: 2rem;
            transition: all 0.3s;
        }

        .method-card:hover {
            transform: translateY(-5px);
            border-color: var(--primary);
            box-shadow: 0 10px 40px rgba(99, 102, 241, 0.2);
        }

        .method-number {
            width: 50px;
            height: 50px;
            background: var(--gradient);
            border-radius: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.5rem;
            font-weight: 700;
            margin-bottom: 1.5rem;
        }

        .method-title {
            font-size: 1.25rem;
            font-weight: 700;
            margin-bottom: 0.75rem;
        }

        .method-description {
            color: var(--gray-light);
            margin-bottom: 1.5rem;
            font-size: 0.95rem;
        }

        .method-steps {
            list-style: none;
            margin-bottom: 1.5rem;
        }

        .method-steps li {
            position: relative;
            padding-{{ $isRtl ? 'right' : 'left' }}: 1.5rem;
            margin-bottom: 0.75rem;
            font-size: 0.9rem;
            color: var(--gray-light);
        }

        .method-steps li::before {
            content: "→";
            position: absolute;
            {{ $isRtl ? 'right' : 'left' }}: 0;
            color: var(--primary);
            font-weight: bold;
        }

        .pro-tip {
            background: rgba(16, 185, 129, 0.1);
            border: 1px solid rgba(16, 185, 129, 0.3);
            border-radius: 12px;
            padding: 1rem;
            font-size: 0.875rem;
        }

        .pro-tip-label {
            color: var(--success);
            font-weight: 600;
            margin-bottom: 0.5rem;
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }

        .pro-tip-text {
            color: var(--gray-light);
        }

        /* AI Prompt Card */
        .ai-prompt-box {
            background: var(--dark);
            border-radius: 12px;
            padding: 1.25rem;
            margin: 1rem 0;
            font-family: monospace;
            font-size: 0.8rem;
            color: var(--gray-light);
            max-height: 150px;
            overflow-y: auto;
            direction: ltr;
            text-align: left;
        }

        /* Info Sections */
        .info-section {
            background: var(--dark-light);
            border: 1px solid rgba(255,255,255,0.1);
            border-radius: 20px;
            padding: 2rem;
            margin-bottom: 2rem;
        }

        .info-section-title {
            font-size: 1.5rem;
            font-weight: 700;
            margin-bottom: 1.5rem;
            display: flex;
            align-items: center;
            gap: 0.75rem;
        }

        .info-section-title::before {
            font-size: 1.5rem;
        }

        /* Tips Grid */
        .tips-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
            gap: 1rem;
        }

        .tip-item {
            display: flex;
            align-items: flex-start;
            gap: 0.75rem;
            padding: 1rem;
            background: var(--dark);
            border-radius: 12px;
        }

        .tip-icon {
            font-size: 1.25rem;
            flex-shrink: 0;
        }

        .tip-text {
            font-size: 0.9rem;
            color: var(--gray-light);
        }

        /* Volume Guide */
        .volume-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(180px, 1fr));
            gap: 1rem;
        }

        .volume-item {
            background: var(--dark);
            border-radius: 12px;
            padding: 1rem;
            text-align: center;
        }

        .volume-label {
            font-size: 1.25rem;
            font-weight: 700;
            color: var(--primary-light);
            margin-bottom: 0.5rem;
        }

        .volume-value {
            font-size: 0.85rem;
            color: var(--gray-light);
        }

        /* Markets */
        .markets-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 1rem;
        }

        .market-item {
            display: flex;
            align-items: center;
            gap: 0.75rem;
            padding: 1rem;
            background: var(--dark);
            border-radius: 12px;
        }

        .market-icon {
            font-size: 1.5rem;
        }

        .market-name {
            font-size: 0.9rem;
            color: var(--gray-light);
        }

        /* CTA Section */
        .cta-section {
            background: var(--gradient);
            border-radius: 20px;
            padding: 3rem;
            text-align: center;
            margin-top: 3rem;
        }

        .cta-section h2 {
            font-size: 1.75rem;
            margin-bottom: 1rem;
        }

        .cta-section p {
            opacity: 0.9;
            margin-bottom: 2rem;
            font-size: 1.1rem;
        }

        .btn-white {
            background: var(--white);
            color: var(--primary);
            padding: 1rem 2rem;
            font-size: 1rem;
        }

        .btn-white:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 15px rgba(255,255,255,0.3);
        }

        /* Footer */
        .footer {
            text-align: center;
            padding: 2rem;
            color: var(--gray);
            font-size: 0.875rem;
            border-top: 1px solid rgba(255,255,255,0.1);
        }

        /* Responsive */
        @media (max-width: 768px) {
            .hero h1 {
                font-size: 1.75rem;
            }
            
            .hero p {
                font-size: 1rem;
            }
            
            .methods-grid {
                grid-template-columns: 1fr;
            }
            
            .main {
                padding: 2rem 1rem;
            }
        }
    </style>
</head>
<body>
    <!-- Header -->
    <header class="header">
        <div class="header-content">
            <a href="/" class="logo">
                <div class="logo-icon">📊</div>
                Amazon Analyzer
            </a>
            <div class="header-actions">
                <a href="?lang={{ $currentLang === 'en' ? 'ar' : 'en' }}" class="lang-switch">
                    {{ __('guide.switch_language') }}
                </a>
                @auth
                <a href="/dashboard" class="btn btn-outline">{{ __('guide.back_to_dashboard') }}</a>
                @else
                <a href="/login" class="btn btn-primary">Login</a>
                @endauth
            </div>
        </div>
    </header>

    <!-- Hero Section -->
    <section class="hero">
        <h1>{{ __('guide.title') }}</h1>
        <p>{{ __('guide.subtitle') }}</p>
    </section>

    <!-- Main Content -->
    <main class="main">
        <!-- Methods Grid -->
        <div class="methods-grid">
            <!-- Method 1 -->
            <article class="method-card">
                <div class="method-number">1</div>
                <h2 class="method-title">{{ __('guide.method1_title') }}</h2>
                <p class="method-description">{{ __('guide.method1_description') }}</p>
                <ul class="method-steps">
                    <li>{{ __('guide.method1_step1') }}</li>
                    <li>{{ __('guide.method1_step2') }}</li>
                    <li>{{ __('guide.method1_step3') }}</li>
                    <li>{{ __('guide.method1_step4') }}</li>
                </ul>
                <div class="pro-tip">
                    <div class="pro-tip-label">💡 {{ __('guide.pro_tip') }}</div>
                    <div class="pro-tip-text">{{ __('guide.method1_tip') }}</div>
                </div>
            </article>

            <!-- Method 2 -->
            <article class="method-card">
                <div class="method-number">2</div>
                <h2 class="method-title">{{ __('guide.method2_title') }}</h2>
                <p class="method-description">{{ __('guide.method2_description') }}</p>
                <ul class="method-steps">
                    <li>{{ __('guide.method2_step1') }}</li>
                    <li>{{ __('guide.method2_step2') }}</li>
                    <li>{{ __('guide.method2_step3') }}</li>
                    <li>{{ __('guide.method2_step4') }}</li>
                    <li>{{ __('guide.method2_step5') }}</li>
                </ul>
                <div class="pro-tip">
                    <div class="pro-tip-label">💡 {{ __('guide.pro_tip') }}</div>
                    <div class="pro-tip-text">{{ __('guide.method2_tip') }}</div>
                </div>
            </article>

            <!-- Method 3 (AI-Powered) -->
            <article class="method-card">
                <div class="method-number">3</div>
                <h2 class="method-title">{{ __('guide.method3_title') }}</h2>
                <p class="method-description">{{ __('guide.method3_description') }}</p>
                <p style="font-size: 0.9rem; color: var(--gray-light); margin-bottom: 0.5rem;">{{ __('guide.method3_prompt_label') }}</p>
                <div class="ai-prompt-box">{{ __('guide.method3_prompt') }}</div>
                <div class="pro-tip">
                    <div class="pro-tip-label">💡 {{ __('guide.pro_tip') }}</div>
                    <div class="pro-tip-text">{{ __('guide.method3_tip') }}</div>
                </div>
            </article>

            <!-- Method 4 (Amazon Suggestions) -->
            <article class="method-card">
                <div class="method-number">4</div>
                <h2 class="method-title">{{ __('guide.method4_title') }}</h2>
                <p class="method-description">{{ __('guide.method4_description') }}</p>
                <ul class="method-steps">
                    <li>{{ __('guide.method4_step1') }}</li>
                    <li>{{ __('guide.method4_step2') }}</li>
                    <li>{{ __('guide.method4_step3') }}</li>
                    <li>{{ __('guide.method4_step4') }}</li>
                </ul>
                <div class="pro-tip">
                    <div class="pro-tip-label">💡 {{ __('guide.pro_tip') }}</div>
                    <div class="pro-tip-text">{{ __('guide.method4_tip') }}</div>
                </div>
            </article>

            <!-- Method 5 (Best Seller Analysis) -->
            <article class="method-card">
                <div class="method-number">5</div>
                <h2 class="method-title">{{ __('guide.method5_title') }}</h2>
                <p class="method-description">{{ __('guide.method5_description') }}</p>
                <ul class="method-steps">
                    <li>{{ __('guide.method5_step1') }}</li>
                    <li>{{ __('guide.method5_step2') }}</li>
                    <li>{{ __('guide.method5_step3') }}</li>
                    <li>{{ __('guide.method5_step4') }}</li>
                    <li>{{ __('guide.method5_step5') }}</li>
                </ul>
                <div class="pro-tip">
                    <div class="pro-tip-label">💡 {{ __('guide.pro_tip') }}</div>
                    <div class="pro-tip-text">{{ __('guide.method5_tip') }}</div>
                </div>
            </article>

            <!-- Method 6 (Telegram Groups & Google Lens) -->
            <article class="method-card">
                <div class="method-number">6</div>
                <h2 class="method-title">{{ __('guide.method6_title') }}</h2>
                <p class="method-description">{{ __('guide.method6_description') }}</p>
                <ul class="method-steps">
                    <li>{{ __('guide.method6_step1') }}</li>
                    <li>{{ __('guide.method6_step2') }}</li>
                    <li>{{ __('guide.method6_step3') }}</li>
                    <li>{{ __('guide.method6_step4') }}</li>
                    <li>{{ __('guide.method6_step5') }}</li>
                    <li>{{ __('guide.method6_step6') }}</li>
                    <li>{{ __('guide.method6_step7') }}</li>
                </ul>
                <div class="pro-tip">
                    <div class="pro-tip-label">💡 {{ __('guide.pro_tip') }}</div>
                    <div class="pro-tip-text">{{ __('guide.method6_tip') }}</div>
                </div>
            </article>
        </div>

        <!-- Product Expansion Strategies Section -->
        <section class="info-section" style="background: linear-gradient(135deg, rgba(245, 158, 11, 0.1) 0%, rgba(239, 68, 68, 0.05) 100%); border-color: rgba(245, 158, 11, 0.3);">
            <h2 class="info-section-title">🚀 {{ __('guide.expansion_title') }}</h2>
            <p style="color: var(--gray-light); margin-bottom: 2rem;">{{ __('guide.expansion_subtitle') }}</p>
            
            <!-- Expansion Strategy 1: ASIN Mining -->
            <div style="background: var(--dark); border-radius: 16px; padding: 1.5rem; margin-bottom: 1rem;">
                <h3 style="font-size: 1.1rem; font-weight: 600; margin-bottom: 0.75rem; color: var(--warning);">{{ __('guide.expansion1_title') }}</h3>
                <p style="color: var(--gray-light); margin-bottom: 1rem; font-size: 0.9rem;">{{ __('guide.expansion1_description') }}</p>
                <ul class="method-steps" style="margin-bottom: 1rem;">
                    <li>{{ __('guide.expansion1_step1') }}</li>
                    <li>{{ __('guide.expansion1_step2') }}</li>
                    <li>{{ __('guide.expansion1_step3') }}</li>
                    <li>{{ __('guide.expansion1_step4') }}</li>
                    <li>{{ __('guide.expansion1_step5') }}</li>
                </ul>
                <div class="pro-tip" style="margin-bottom: 1rem;">
                    <div class="pro-tip-label">💡 {{ __('guide.pro_tip') }}</div>
                    <div class="pro-tip-text">{{ __('guide.expansion1_tip') }}</div>
                </div>
                <!-- Warning Box -->
                <div style="background: rgba(239, 68, 68, 0.1); border: 1px solid rgba(239, 68, 68, 0.3); border-radius: 12px; padding: 1rem;">
                    <div style="color: var(--danger); font-weight: 600; margin-bottom: 0.5rem; display: flex; align-items: center; gap: 0.5rem;">
                        ⚠️ {{ __('guide.warning') }}
                    </div>
                    <div style="color: var(--gray-light); font-size: 0.875rem;">{{ __('guide.expansion1_warning') }}</div>
                </div>
            </div>
            
            <!-- Expansion Strategy 2: Multi-Category Listing -->
            <div style="background: var(--dark); border-radius: 16px; padding: 1.5rem; margin-bottom: 1rem;">
                <h3 style="font-size: 1.1rem; font-weight: 600; margin-bottom: 0.75rem; color: var(--warning);">{{ __('guide.expansion2_title') }}</h3>
                <p style="color: var(--gray-light); margin-bottom: 1rem; font-size: 0.9rem;">{{ __('guide.expansion2_description') }}</p>
                <ul class="method-steps" style="margin-bottom: 1rem;">
                    <li>{{ __('guide.expansion2_step1') }}</li>
                    <li>{{ __('guide.expansion2_step2') }}</li>
                    <li>{{ __('guide.expansion2_step3') }}</li>
                    <li>{{ __('guide.expansion2_step4') }}</li>
                    <li>{{ __('guide.expansion2_step5') }}</li>
                </ul>
                <div class="pro-tip" style="margin-bottom: 1rem;">
                    <div class="pro-tip-label">💡 {{ __('guide.pro_tip') }}</div>
                    <div class="pro-tip-text">{{ __('guide.expansion2_tip') }}</div>
                </div>
                <!-- Warning Box -->
                <div style="background: rgba(239, 68, 68, 0.1); border: 1px solid rgba(239, 68, 68, 0.3); border-radius: 12px; padding: 1rem;">
                    <div style="color: var(--danger); font-weight: 600; margin-bottom: 0.5rem; display: flex; align-items: center; gap: 0.5rem;">
                        ⚠️ {{ __('guide.warning') }}
                    </div>
                    <div style="color: var(--gray-light); font-size: 0.875rem;">{{ __('guide.expansion2_warning') }}</div>
                </div>
            </div>
        </section>

        <!-- Quick Tips Section -->
        <section class="info-section">
            <h2 class="info-section-title">🎯 {{ __('guide.quick_tips_title') }}</h2>
            <div class="tips-grid">
                <div class="tip-item">
                    <span class="tip-icon">💰</span>
                    <span class="tip-text">{{ __('guide.tip1') }}</span>
                </div>
                <div class="tip-item">
                    <span class="tip-icon">📈</span>
                    <span class="tip-text">{{ __('guide.tip2') }}</span>
                </div>
                <div class="tip-item">
                    <span class="tip-icon">🏷️</span>
                    <span class="tip-text">{{ __('guide.tip3') }}</span>
                </div>
                <div class="tip-item">
                    <span class="tip-icon">✅</span>
                    <span class="tip-text">{{ __('guide.tip4') }}</span>
                </div>
                <div class="tip-item">
                    <span class="tip-icon">⚠️</span>
                    <span class="tip-text">{{ __('guide.tip5') }}</span>
                </div>
                <div class="tip-item">
                    <span class="tip-icon">📦</span>
                    <span class="tip-text">{{ __('guide.tip6') }}</span>
                </div>
                <div class="tip-item">
                    <span class="tip-icon">🚀</span>
                    <span class="tip-text">{{ __('guide.tip7') }}</span>
                </div>
            </div>
        </section>

        <!-- Volume Guide -->
        <section class="info-section">
            <h2 class="info-section-title">📊 {{ __('guide.volume_guide_title') }}</h2>
            <p style="color: var(--gray-light); margin-bottom: 1.5rem;">{{ __('guide.volume_guide_description') }}</p>
            <div class="volume-grid">
                <div class="volume-item">
                    <div class="volume-label">+10</div>
                    <div class="volume-value">{{ __('guide.volume_10') }}</div>
                </div>
                <div class="volume-item">
                    <div class="volume-label">+50</div>
                    <div class="volume-value">{{ __('guide.volume_50') }}</div>
                </div>
                <div class="volume-item">
                    <div class="volume-label">+100</div>
                    <div class="volume-value">{{ __('guide.volume_100') }}</div>
                </div>
                <div class="volume-item">
                    <div class="volume-label">+500</div>
                    <div class="volume-value">{{ __('guide.volume_500') }}</div>
                </div>
                <div class="volume-item">
                    <div class="volume-label">+1K</div>
                    <div class="volume-value">{{ __('guide.volume_1k') }}</div>
                </div>
            </div>
        </section>

        <!-- Wholesale Markets -->
        <section class="info-section">
            <h2 class="info-section-title">🏪 {{ __('guide.markets_title') }}</h2>
            <div class="markets-grid">
                <div class="market-item">
                    <span class="market-icon">📍</span>
                    <span class="market-name">{{ __('guide.market1') }}</span>
                </div>
                <div class="market-item">
                    <span class="market-icon">📍</span>
                    <span class="market-name">{{ __('guide.market2') }}</span>
                </div>
                <div class="market-item">
                    <span class="market-icon">📍</span>
                    <span class="market-name">{{ __('guide.market3') }}</span>
                </div>
                <div class="market-item">
                    <span class="market-icon">📍</span>
                    <span class="market-name">{{ __('guide.market4') }}</span>
                </div>
            </div>
        </section>

        <!-- CTA Section -->
        <section class="cta-section">
            <h2>{{ __('guide.cta_title') }}</h2>
            <p>{{ __('guide.cta_description') }}</p>
            <a href="#" class="btn btn-white">{{ __('guide.cta_button') }}</a>
        </section>
    </main>

    <!-- Footer -->
    <footer class="footer">
        <p>&copy; {{ date('Y') }} Amazon Analyzer. All rights reserved.</p>
    </footer>
</body>
</html>
