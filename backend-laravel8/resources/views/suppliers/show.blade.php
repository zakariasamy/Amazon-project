@php
    $isRtl = app()->getLocale() === 'ar';
    $currentLang = app()->getLocale();
@endphp
<!DOCTYPE html>
<html lang="{{ $currentLang }}" dir="{{ $isRtl ? 'rtl' : 'ltr' }}">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $isRtl ? $supplier->name_ar : $supplier->name }} - {{ __('suppliers.title') }}</title>
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
            --dark-medium: #cbd5e1;
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

        .btn-success { background: var(--success); color: white; }
        .btn-telegram { background: #0088cc; color: white; }
        .btn-whatsapp { background: #25d366; color: white; }

        .main { max-width: 1200px; margin: 0 auto; padding: 2.5rem 2rem; }

        /* Supplier Profile Header */
        .profile-header {
            background: var(--dark-light);
            border: 1px solid rgba(0,0,0,0.08);
            border-radius: 20px;
            padding: 2rem;
            margin-bottom: 2.5rem;
            box-shadow: 0 4px 6px -1px rgba(0,0,0,0.02);
        }

        .profile-top {
            display: flex;
            gap: 2rem;
            align-items: flex-start;
            margin-bottom: 2rem;
        }

        .profile-logo {
            width: 120px;
            height: 120px;
            background: var(--gradient);
            border-radius: 20px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 3rem;
            flex-shrink: 0;
            color: #ffffff;
        }

        .profile-info { flex: 1; }

        .profile-name {
            font-size: 1.85rem;
            font-weight: 800;
            margin-bottom: 0.5rem;
            display: flex;
            align-items: center;
            gap: 0.75rem;
        }

        .verified-badge {
            background: rgba(16, 185, 129, 0.08);
            color: var(--success);
            padding: 0.25rem 0.75rem;
            border-radius: 20px;
            font-size: 0.75rem;
            font-weight: 600;
        }

        .profile-category {
            color: var(--primary);
            font-size: 0.9rem;
            margin-bottom: 1rem;
            font-weight: 600;
        }

        .profile-description {
            color: var(--gray-light);
            font-size: 0.95rem;
            line-height: 1.7;
            margin-bottom: 1rem;
        }

        .profile-stats {
            display: flex;
            gap: 2rem;
            padding: 1.25rem 0;
            border-top: 1px solid rgba(0,0,0,0.08);
            border-bottom: 1px solid rgba(0,0,0,0.08);
            margin-bottom: 1.5rem;
        }

        .stat-item { text-align: {{ $isRtl ? 'right' : 'left' }}; }
        .stat-value { font-size: 1.65rem; font-weight: 800; color: var(--primary); }
        .stat-label { font-size: 0.8rem; color: var(--gray); font-weight: 600; }

        .profile-location {
            display: flex;
            align-items: center;
            gap: 0.5rem;
            color: var(--gray-light);
            font-size: 0.9rem;
        }

        .contact-buttons {
            display: flex;
            gap: 0.75rem;
            flex-wrap: wrap;
            margin-top: 1.5rem;
        }

        /* Products Section */
        .section-title {
            font-size: 1.5rem;
            font-weight: 800;
            margin-bottom: 1.5rem;
            display: flex;
            align-items: center;
            gap: 0.75rem;
        }

        .products-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(280px, 1fr));
            gap: 2rem;
        }

        .product-card {
            background: var(--dark-light);
            border: 1px solid rgba(0,0,0,0.08);
            border-radius: 16px;
            overflow: hidden;
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
            box-shadow: 0 4px 6px -1px rgba(0,0,0,0.02);
        }

        .product-card:hover {
            transform: translateY(-4px);
            border-color: var(--primary);
            box-shadow: 0 12px 20px -5px rgba(99, 102, 241, 0.1), 0 8px 16px -8px rgba(99, 102, 241, 0.1);
        }

        .product-image {
            height: 180px;
            background: #f1f5f9;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 3rem;
            color: var(--gray);
        }

        .product-image img {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }

        .product-content { padding: 1.25rem; }

        .product-name {
            font-size: 1.05rem;
            font-weight: 700;
            margin-bottom: 0.5rem;
            line-height: 1.4;
        }

        .product-moq {
            font-size: 0.8rem;
            color: var(--warning);
            margin-bottom: 0.75rem;
            display: flex;
            align-items: center;
            gap: 0.3rem;
            font-weight: 600;
        }

        .product-price {
            font-size: 1.35rem;
            font-weight: 800;
            color: var(--success);
            margin-bottom: 0.5rem;
        }

        .price-tiers {
            background: var(--dark);
            border-radius: 8px;
            padding: 0.75rem;
            margin-top: 0.75rem;
        }

        .tier-row {
            display: flex;
            justify-content: space-between;
            padding: 0.35rem 0;
            font-size: 0.8rem;
            color: var(--gray-light);
        }

        .tier-row:not(:last-child) {
            border-bottom: 1px solid rgba(0,0,0,0.06);
        }

        .tier-qty { color: var(--gray-light); font-weight: 500; }
        .tier-price { color: var(--success); font-weight: 700; }

        .no-products {
            text-align: center;
            padding: 4rem;
            color: var(--gray-light);
            font-size: 1.05rem;
        }

        @media (max-width: 768px) {
            .profile-top { flex-direction: column; align-items: center; text-align: center; }
            .profile-stats { justify-content: center; }
            .products-grid { grid-template-columns: 1fr; }
        }
    </style>
</head>
<body>
    <header class="header">
        <div class="header-content">
            <a href="/suppliers" class="logo">🏪 {{ __('suppliers.title') }}</a>
            <a href="/suppliers" class="btn btn-outline">{{ __('suppliers.back_to_suppliers') }}</a>
        </div>
    </header>

    <main class="main">
        <!-- Supplier Profile Header -->
        <section class="profile-header">
            <div class="profile-top">
                <div class="profile-logo">
                    @if($supplier->logo)
                        <img src="{{ asset('storage/' . $supplier->logo) }}" alt="{{ $supplier->name }}" style="width: 100%; height: 100%; object-fit: cover; border-radius: 20px;">
                    @else
                        🏭
                    @endif
                </div>
                <div class="profile-info">
                    <h1 class="profile-name">
                        {{ $isRtl ? $supplier->name_ar : $supplier->name }}
                        @if($supplier->is_verified)
                            <span class="verified-badge">✓ {{ __('suppliers.verified') }}</span>
                        @endif
                    </h1>
                    <div class="profile-category">{{ $supplier->category_name }}</div>
                    <p class="profile-description">
                        {{ $isRtl ? ($supplier->description_ar ?: $supplier->description) : ($supplier->description ?: $supplier->description_ar) }}
                    </p>
                    
                    @if($supplier->location || $supplier->location_ar)
                        <div class="profile-location">
                            📍 {{ $isRtl ? ($supplier->location_ar ?: $supplier->location) : ($supplier->location ?: $supplier->location_ar) }}
                        </div>
                    @endif
                </div>
            </div>

            <div class="profile-stats">
                <div class="stat-item">
                    <div class="stat-value">{{ $supplier->products()->count() }}</div>
                    <div class="stat-label">{{ $isRtl ? 'المنتجات' : 'Products' }}</div>
                </div>
                <div class="stat-item">
                    <div class="stat-value">{{ $supplier->created_at->diffForHumans() }}</div>
                    <div class="stat-label">{{ $isRtl ? 'تاريخ الانضمام' : 'Member Since' }}</div>
                </div>
                @if($supplier->is_verified)
                <div class="stat-item">
                    <div class="stat-value">✓</div>
                    <div class="stat-label">{{ $isRtl ? 'تاجر موثق' : 'Verified Supplier' }}</div>
                </div>
                @endif
            </div>

            <div class="contact-buttons">
                @if($supplier->telegram_group_link)
                    <a href="{{ $supplier->telegram_group_link }}" target="_blank" class="btn btn-telegram">
                        📱 {{ __('suppliers.telegram_group') }}
                    </a>
                @endif
                @if($supplier->whatsapp)
                    <a href="https://wa.me/{{ $supplier->whatsapp }}" target="_blank" class="btn btn-whatsapp">
                        💬 {{ __('suppliers.whatsapp') }}
                    </a>
                @endif
                @if($supplier->website)
                    <a href="{{ $supplier->website }}" target="_blank" class="btn btn-primary">
                        🌐 {{ __('suppliers.visit_website') }}
                    </a>
                @endif
                @if($supplier->phone)
                    <a href="tel:{{ $supplier->phone }}" class="btn btn-outline">
                        📞 {{ __('suppliers.call') }}
                    </a>
                @endif
            </div>
        </section>

        <!-- Products Section -->
        <section>
            <h2 class="section-title">📦 {{ $isRtl ? 'منتجات التاجر' : 'Supplier Products' }}</h2>
            
            @if($supplier->products()->available()->count() > 0)
                <div class="products-grid">
                    @foreach($supplier->products()->available()->get() as $product)
                        <div class="product-card">
                            <div class="product-image">
                                @if($product->images && count($product->images) > 0)
                                    <img src="{{ asset('storage/' . $product->images[0]) }}" alt="{{ $product->name }}">
                                @else
                                    📦
                                @endif
                            </div>
                            <div class="product-content">
                                <h3 class="product-name">{{ $isRtl ? $product->name_ar : $product->name }}</h3>
                                <div class="product-moq">
                                    📦 {{ $isRtl ? 'الحد الأدنى للطلب:' : 'Min Order:' }} 
                                    {{ $product->min_order_quantity }} {{ $isRtl ? $product->unit_ar : $product->unit }}
                                </div>
                                <div class="product-price">
                                    {{ number_format($product->base_price, 2) }} EGP
                                </div>
                                
                                @if($product->price_tiers && count($product->price_tiers) > 0)
                                    <div class="price-tiers">
                                        <div style="font-size: 0.75rem; color: var(--gray); margin-bottom: 0.5rem;">
                                            {{ $isRtl ? 'أسعار الجملة:' : 'Bulk Prices:' }}
                                        </div>
                                        @foreach($product->price_tiers as $tier)
                                            <div class="tier-row">
                                                <span class="tier-qty">{{ $tier['min_qty'] }}{{ isset($tier['max_qty']) ? '-'.$tier['max_qty'] : '+' }} {{ $isRtl ? $product->unit_ar : $product->unit }}</span>
                                                <span class="tier-price">{{ number_format($tier['price'], 2) }} EGP</span>
                                            </div>
                                        @endforeach
                                    </div>
                                @endif
                            </div>
                        </div>
                    @endforeach
                </div>
            @else
                <div class="no-products">
                    <p>{{ $isRtl ? 'لا توجد منتجات متاحة حالياً' : 'No products available yet' }}</p>
                </div>
            @endif
        </section>
    </main>
</body>
</html>
