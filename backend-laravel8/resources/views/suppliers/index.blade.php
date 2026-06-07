@php
    $isRtl = app()->getLocale() === 'ar';
    $currentLang = app()->getLocale();
@endphp
<!DOCTYPE html>
<html lang="{{ $currentLang }}" dir="{{ $isRtl ? 'rtl' : 'ltr' }}">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ __('suppliers.title') }} - Amazon Product Analyzer</title>
    <meta name="description" content="{{ __('suppliers.subtitle') }}">
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
            --dark: #f8fafc;
            --dark-light: #ffffff;
            --gray: #64748b;
            --gray-light: #475569;
            --white: #0f172a;
            --gradient: linear-gradient(135deg, var(--primary) 0%, var(--secondary) 100%);
        }

        * { margin: 0; padding: 0; box-sizing: border-box; }

        body {
            font-family: {{ $isRtl ? "'Cairo'" : "'Inter'" }}, -apple-system, BlinkMacSystemFont, sans-serif;
            background: var(--dark);
            color: var(--white);
            min-height: 100vh;
            line-height: 1.6;
        }

        .header {
            background: var(--dark-light);
            border-bottom: 1px solid rgba(0,0,0,0.08);
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
            color: #ffffff;
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
            border: 2px solid rgba(0,0,0,0.08);
            color: var(--white);
        }

        .btn-outline:hover {
            border-color: var(--primary);
            color: var(--primary);
        }

        .btn-primary {
            background: var(--gradient);
            color: #ffffff;
        }

        .btn-primary:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 15px rgba(99, 102, 241, 0.4);
        }

        .lang-switch {
            background: rgba(0,0,0,0.05);
            color: var(--white);
            padding: 0.5rem 1rem;
            border-radius: 8px;
            font-weight: 500;
            text-decoration: none;
        }

        .hero {
            background: linear-gradient(135deg, rgba(99, 102, 241, 0.1) 0%, rgba(14, 165, 233, 0.1) 100%);
            padding: 3.5rem 2rem;
            text-align: center;
            border-bottom: 1px solid rgba(0,0,0,0.08);
        }

        .hero h1 {
            font-size: 2.25rem;
            font-weight: 800;
            margin-bottom: 0.75rem;
            background: var(--gradient);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
        }

        .hero p {
            color: var(--gray-light);
            font-size: 1.1rem;
        }

        .main {
            max-width: 1200px;
            margin: 0 auto;
            padding: 2.5rem 2rem;
        }

        .filters {
            display: flex;
            gap: 1rem;
            margin-bottom: 2.5rem;
            flex-wrap: wrap;
            align-items: center;
        }

        .filter-select, .filter-input {
            background: var(--dark-light);
            border: 1px solid rgba(0,0,0,0.08);
            color: var(--white);
            padding: 0.75rem 1rem;
            border-radius: 10px;
            font-size: 0.9rem;
            font-family: inherit;
            box-shadow: 0 1px 2px 0 rgba(0, 0, 0, 0.02);
            outline: none;
            transition: all 0.2s;
        }

        .filter-select:focus, .filter-input:focus {
            border-color: var(--primary);
            box-shadow: 0 0 0 3px rgba(99, 102, 241, 0.15);
        }

        .filter-select { min-width: 180px; }
        .filter-input { flex: 1; min-width: 200px; }

        .suppliers-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(320px, 1fr));
            gap: 2rem;
            margin-bottom: 2.5rem;
        }

        .supplier-card {
            background: var(--dark-light);
            border: 1px solid rgba(0,0,0,0.08);
            border-radius: 16px;
            padding: 1.75rem;
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
            box-shadow: 0 4px 6px -1px rgba(0,0,0,0.02), 0 2px 4px -2px rgba(0,0,0,0.02);
        }

        .supplier-card:hover {
            transform: translateY(-4px);
            border-color: var(--primary);
            box-shadow: 0 12px 20px -5px rgba(99, 102, 241, 0.1), 0 8px 16px -8px rgba(99, 102, 241, 0.1);
        }

        .supplier-header {
            display: flex;
            align-items: center;
            gap: 1rem;
            margin-bottom: 1.25rem;
        }

        .supplier-logo {
            width: 60px;
            height: 60px;
            background: var(--gradient);
            border-radius: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.5rem;
            color: #ffffff;
        }

        .supplier-info h3 {
            font-size: 1.15rem;
            font-weight: 700;
            margin-bottom: 0.25rem;
        }

        .supplier-category {
            font-size: 0.75rem;
            color: var(--primary);
            background: rgba(99, 102, 241, 0.08);
            padding: 0.25rem 0.75rem;
            border-radius: 20px;
            display: inline-block;
            font-weight: 600;
        }

        .verified-badge {
            display: inline-flex;
            align-items: center;
            gap: 0.3rem;
            font-size: 0.75rem;
            color: var(--success);
            background: rgba(16, 185, 129, 0.08);
            padding: 0.25rem 0.6rem;
            border-radius: 20px;
            margin-{{ $isRtl ? 'right' : 'left' }}: 0.5rem;
            font-weight: 600;
        }

        .supplier-description {
            color: var(--gray-light);
            font-size: 0.925rem;
            margin-bottom: 1.25rem;
            line-height: 1.6;
        }

        .supplier-location {
            display: flex;
            align-items: center;
            gap: 0.5rem;
            font-size: 0.85rem;
            color: var(--gray);
            margin-bottom: 1.25rem;
        }

        .supplier-actions {
            display: flex;
            gap: 0.5rem;
            flex-wrap: wrap;
        }

        .contact-btn {
            padding: 0.5rem 0.75rem;
            border-radius: 8px;
            font-size: 0.8rem;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            gap: 0.3rem;
            transition: all 0.3s;
            font-weight: 600;
        }

        .contact-telegram {
            background: rgba(0, 136, 204, 0.08);
            color: #0088cc;
        }
        .contact-telegram:hover {
            background: rgba(0, 136, 204, 0.15);
        }

        .contact-whatsapp {
            background: rgba(37, 211, 102, 0.08);
            color: #25d366;
        }
        .contact-whatsapp:hover {
            background: rgba(37, 211, 102, 0.15);
        }

        .contact-website {
            background: rgba(99, 102, 241, 0.08);
            color: var(--primary);
        }
        .contact-website:hover {
            background: rgba(99, 102, 241, 0.15);
        }

        .contact-phone {
            background: rgba(0,0,0,0.05);
            color: var(--white);
        }
        .contact-phone:hover {
            background: rgba(0,0,0,0.1);
        }

        .cta-section {
            background: var(--dark-light);
            border: 1px solid rgba(0,0,0,0.08);
            border-radius: 16px;
            padding: 2.5rem;
            text-align: center;
            margin-top: 3rem;
            box-shadow: 0 4px 6px -1px rgba(0,0,0,0.02);
        }

        .cta-section h2 {
            font-size: 1.35rem;
            margin-bottom: 0.75rem;
            font-weight: 700;
        }

        .cta-section p {
            color: var(--gray-light);
            margin-bottom: 1.5rem;
        }

        .no-results {
            text-align: center;
            padding: 4rem;
            color: var(--gray-light);
        }

        .no-results h3 {
            font-size: 1.35rem;
            margin-bottom: 0.5rem;
            color: var(--white);
            font-weight: 700;
        }

        @media (max-width: 768px) {
            .hero h1 { font-size: 1.75rem; }
            .filters { flex-direction: column; }
            .filter-input, .filter-select { width: 100%; }
            .suppliers-grid { grid-template-columns: 1fr; }
        }
    </style>
</head>
<body>
    <header class="header">
        <div class="header-content">
            <a href="/" class="logo">
                <div class="logo-icon">🏪</div>
                {{ __('suppliers.title') }}
            </a>
            <div class="header-actions">
                <a href="?lang={{ $currentLang === 'ar' ? 'en' : 'ar' }}" class="lang-switch">
                    {{ __('suppliers.switch_language') }}
                </a>
                <a href="/guide" class="btn btn-outline">{{ __('suppliers.back_to_guide') }}</a>
            </div>
        </div>
    </header>

    <section class="hero">
        <h1>{{ __('suppliers.title') }}</h1>
        <p>{{ __('suppliers.subtitle') }}</p>
    </section>

    <main class="main">
        <!-- Tabs Navigation -->
        <div style="display: flex; gap: 0.5rem; margin-bottom: 1.5rem;">
            <a href="/suppliers" style="padding: 0.75rem 1.5rem; border-radius: 10px; text-decoration: none; color: var(--white); background: var(--primary); border: 1px solid var(--primary);">
                {{ $isRtl ? 'التجار' : 'Suppliers' }}
            </a>
            <a href="/suppliers/products" style="padding: 0.75rem 1.5rem; border-radius: 10px; text-decoration: none; color: var(--gray-light); background: var(--dark-light); border: 1px solid rgba(255,255,255,0.1);">
                {{ $isRtl ? 'المنتجات' : 'Products' }}
            </a>
        </div>

        <form class="filters" method="GET">
            <select name="category" class="filter-select" onchange="this.form.submit()">
                <option value="all">{{ __('suppliers.all_categories') }}</option>
                @foreach($categories as $key => $names)
                    <option value="{{ $key }}" {{ $currentCategory === $key ? 'selected' : '' }}>
                        {{ $names[$currentLang] }}
                    </option>
                @endforeach
            </select>
            <input type="text" name="search" class="filter-input" 
                   placeholder="{{ __('suppliers.search_placeholder') }}" 
                   value="{{ $search }}">
            <button type="submit" class="btn btn-primary">{{ __('suppliers.filter') }}</button>
        </form>

        @if($suppliers->count() > 0)
            <div class="suppliers-grid">
                @foreach($suppliers as $supplier)
                    <div class="supplier-card">
                        <div class="supplier-header">
                            <div class="supplier-logo">
                                @if($supplier->logo)
                                    <img src="{{ asset('storage/' . $supplier->logo) }}" alt="{{ $supplier->name }}" style="width: 100%; height: 100%; object-fit: cover; border-radius: 12px;">
                                @else
                                    🏭
                                @endif
                            </div>
                            <div class="supplier-info">
                                <h3>{{ $isRtl ? $supplier->name_ar : $supplier->name }}</h3>
                                <span class="supplier-category">{{ $supplier->category_name }}</span>
                                @if($supplier->is_verified)
                                    <span class="verified-badge">✓ {{ __('suppliers.verified') }}</span>
                                @endif
                            </div>
                        </div>
                        
                        @if($supplier->description || $supplier->description_ar)
                            <p class="supplier-description">
                                {{ $isRtl ? ($supplier->description_ar ?: $supplier->description) : ($supplier->description ?: $supplier->description_ar) }}
                            </p>
                        @endif
                        
                        @if($supplier->location || $supplier->location_ar)
                            <div class="supplier-location">
                                📍 {{ $isRtl ? ($supplier->location_ar ?: $supplier->location) : ($supplier->location ?: $supplier->location_ar) }}
                            </div>
                        @endif
                        
                        <div class="supplier-actions">
                            @if($supplier->telegram_group_link)
                                <a href="{{ $supplier->telegram_group_link }}" target="_blank" class="contact-btn contact-telegram">
                                    📱 {{ __('suppliers.telegram_group') }}
                                </a>
                            @endif
                            @if($supplier->whatsapp)
                                <a href="https://wa.me/{{ $supplier->whatsapp }}" target="_blank" class="contact-btn contact-whatsapp">
                                    💬 {{ __('suppliers.whatsapp') }}
                                </a>
                            @endif
                            @if($supplier->website)
                                <a href="{{ $supplier->website }}" target="_blank" class="contact-btn contact-website">
                                    🌐 {{ __('suppliers.visit_website') }}
                                </a>
                            @endif
                            @if($supplier->phone)
                                <a href="tel:{{ $supplier->phone }}" class="contact-btn contact-phone">
                                    📞 {{ __('suppliers.call') }}
                                </a>
                            @endif
                        </div>
                    </div>
                @endforeach
            </div>
        @else
            <div class="no-results">
                <h3>{{ __('suppliers.no_results') }}</h3>
                <p>{{ __('suppliers.no_results_hint') }}</p>
            </div>
        @endif

        <section class="cta-section">
            <h2>{{ __('suppliers.apply_cta') }}</h2>
            <p>{{ __('suppliers.requirement_1') }}</p>
            <a href="{{ route('suppliers.apply') }}" class="btn btn-primary">{{ __('suppliers.apply_button') }}</a>
        </section>
    </main>
</body>
</html>
