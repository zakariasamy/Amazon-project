@php
    $isRtl = app()->getLocale() === 'ar';
    $currentLang = app()->getLocale();
@endphp
<!DOCTYPE html>
<html lang="{{ $currentLang }}" dir="{{ $isRtl ? 'rtl' : 'ltr' }}">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $isRtl ? 'منتجات الجملة' : 'Wholesale Products' }} - Amazon Product Analyzer</title>
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
            max-width: 1400px;
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

        .header-actions { display: flex; gap: 1rem; align-items: center; }

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
            padding: 2.5rem 2rem;
            text-align: center;
            border-bottom: 1px solid rgba(0,0,0,0.08);
        }

        .hero h1 {
            font-size: 2rem;
            font-weight: 800;
            margin-bottom: 0.5rem;
            background: var(--gradient);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
        }

        .hero p { color: var(--gray-light); }

        .main { max-width: 1400px; margin: 0 auto; padding: 2.5rem 2rem; }

        .filters-bar {
            background: var(--dark-light);
            border: 1px solid rgba(0,0,0,0.08);
            border-radius: 12px;
            padding: 1.25rem;
            margin-bottom: 2.5rem;
            display: flex;
            gap: 1rem;
            flex-wrap: wrap;
            align-items: center;
            box-shadow: 0 4px 6px -1px rgba(0,0,0,0.02);
        }

        .filter-group { display: flex; flex-direction: column; gap: 0.25rem; }
        .filter-label { font-size: 0.75rem; color: var(--gray); font-weight: 600; }

        .filter-select, .filter-input {
            background: var(--dark);
            border: 1px solid rgba(0,0,0,0.08);
            color: var(--white);
            padding: 0.6rem 1rem;
            border-radius: 8px;
            font-size: 0.85rem;
            font-family: inherit;
            min-width: 150px;
            outline: none;
            transition: all 0.2s;
        }
        .filter-select:focus, .filter-input:focus {
            border-color: var(--primary);
            box-shadow: 0 0 0 3px rgba(99, 102, 241, 0.15);
        }

        .filter-input { flex: 1; min-width: 200px; }

        .products-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(280px, 1fr));
            gap: 2rem;
            margin-bottom: 2.5rem;
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
            position: relative;
        }

        .product-image img {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }

        .supplier-badge {
            position: absolute;
            bottom: 8px;
            {{ $isRtl ? 'right' : 'left' }}: 8px;
            background: rgba(15, 23, 42, 0.85);
            color: #ffffff;
            padding: 0.25rem 0.6rem;
            border-radius: 6px;
            font-size: 0.725rem;
            display: flex;
            align-items: center;
            gap: 0.25rem;
            font-weight: 500;
        }

        .product-content { padding: 1.25rem; }

        .product-name {
            font-size: 1.05rem;
            font-weight: 700;
            margin-bottom: 0.5rem;
            line-height: 1.4;
        }

        .product-supplier {
            font-size: 0.8rem;
            color: var(--primary);
            margin-bottom: 0.75rem;
            display: flex;
            align-items: center;
            gap: 0.3rem;
            font-weight: 600;
        }

        .product-supplier a {
            color: var(--primary);
            text-decoration: none;
        }

        .product-supplier a:hover { text-decoration: underline; }

        .product-moq {
            font-size: 0.8rem;
            color: var(--warning);
            margin-bottom: 0.5rem;
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

        .pagination {
            display: flex;
            justify-content: center;
            gap: 0.5rem;
            margin-top: 2.5rem;
        }

        .pagination a, .pagination span {
            padding: 0.5rem 1rem;
            border-radius: 8px;
            text-decoration: none;
            color: var(--gray-light);
            background: var(--dark-light);
            border: 1px solid rgba(0,0,0,0.08);
            font-weight: 600;
            transition: all 0.2s;
        }

        .pagination a:hover { border-color: var(--primary); color: var(--primary); }
        .pagination .active { background: var(--primary); border-color: var(--primary); color: #ffffff; }

        .no-results {
            text-align: center;
            padding: 4rem 2rem;
            color: var(--gray-light);
        }

        .no-results h3 {
            font-size: 1.35rem;
            margin-bottom: 0.5rem;
            color: var(--white);
            font-weight: 700;
        }

        .tabs {
            display: flex;
            gap: 0.5rem;
            margin-bottom: 1.5rem;
        }

        .tab {
            padding: 0.75rem 1.5rem;
            border-radius: 10px;
            text-decoration: none;
            color: var(--gray-light);
            background: var(--dark-light);
            border: 1px solid rgba(0,0,0,0.08);
            transition: all 0.3s;
            font-weight: 600;
            box-shadow: 0 1px 2px rgba(0,0,0,0.01);
        }

        .tab:hover { border-color: var(--primary); color: var(--primary); }
        .tab.active { background: var(--primary); border-color: var(--primary); color: #ffffff; }

        @media (max-width: 768px) {
            .filters-bar { flex-direction: column; }
            .filter-select, .filter-input { width: 100%; }
            .products-grid { grid-template-columns: 1fr; }
            .tabs { overflow-x: auto; }
        }
    </style>
</head>
<body>
    <header class="header">
        <div class="header-content">
            <a href="/suppliers" class="logo">
                🏪 {{ $isRtl ? 'منتجات الجملة' : 'Wholesale Products' }}
            </a>
            <div class="header-actions">
                <a href="?lang={{ $currentLang === 'ar' ? 'en' : 'ar' }}" class="lang-switch">
                    {{ $currentLang === 'ar' ? 'English' : 'عربي' }}
                </a>
                <a href="/suppliers" class="btn btn-outline">{{ $isRtl ? 'دليل التجار' : 'Suppliers Directory' }}</a>
            </div>
        </div>
    </header>

    <section class="hero">
        <h1>{{ $isRtl ? 'تصفح منتجات الجملة' : 'Browse Wholesale Products' }}</h1>
        <p>{{ $isRtl ? 'اعثر على منتجات من تجار الجملة والمستوردين الموثقين' : 'Find products from verified wholesalers and importers' }}</p>
    </section>

    <main class="main">
        <!-- Tabs -->
        <div class="tabs">
            <a href="/suppliers" class="tab">{{ $isRtl ? 'التجار' : 'Suppliers' }}</a>
            <a href="/suppliers/products" class="tab active">{{ $isRtl ? 'المنتجات' : 'Products' }}</a>
        </div>

        <!-- Filters -->
        <form class="filters-bar" method="GET">
            <div class="filter-group">
                <span class="filter-label">{{ $isRtl ? 'الفئة' : 'Category' }}</span>
                <select name="category" class="filter-select">
                    <option value="all">{{ $isRtl ? 'جميع الفئات' : 'All Categories' }}</option>
                    @foreach($categories as $key => $names)
                        <option value="{{ $key }}" {{ $currentCategory === $key ? 'selected' : '' }}>
                            {{ $names[$currentLang] }}
                        </option>
                    @endforeach
                </select>
            </div>
            <div class="filter-group">
                <span class="filter-label">{{ $isRtl ? 'التاجر' : 'Supplier' }}</span>
                <select name="supplier" class="filter-select">
                    <option value="">{{ $isRtl ? 'جميع التجار' : 'All Suppliers' }}</option>
                    @foreach($suppliers as $supplier)
                        <option value="{{ $supplier->id }}" {{ $currentSupplier == $supplier->id ? 'selected' : '' }}>
                            {{ $isRtl ? $supplier->name_ar : $supplier->name }}
                        </option>
                    @endforeach
                </select>
            </div>
            <div class="filter-group">
                <span class="filter-label">{{ $isRtl ? 'الترتيب' : 'Sort By' }}</span>
                <select name="sort" class="filter-select">
                    <option value="newest" {{ $sort === 'newest' ? 'selected' : '' }}>{{ $isRtl ? 'الأحدث' : 'Newest' }}</option>
                    <option value="price_low" {{ $sort === 'price_low' ? 'selected' : '' }}>{{ $isRtl ? 'السعر: من الأقل' : 'Price: Low to High' }}</option>
                    <option value="price_high" {{ $sort === 'price_high' ? 'selected' : '' }}>{{ $isRtl ? 'السعر: من الأعلى' : 'Price: High to Low' }}</option>
                    <option value="moq_low" {{ $sort === 'moq_low' ? 'selected' : '' }}>{{ $isRtl ? 'أقل حد طلب' : 'Lowest MOQ' }}</option>
                </select>
            </div>
            <div class="filter-group" style="flex: 1;">
                <span class="filter-label">{{ $isRtl ? 'البحث' : 'Search' }}</span>
                <input type="text" name="search" class="filter-input" 
                       placeholder="{{ $isRtl ? 'ابحث عن منتج...' : 'Search products...' }}" 
                       value="{{ $search }}">
            </div>
            <button type="submit" class="btn btn-primary">{{ $isRtl ? 'بحث' : 'Search' }}</button>
        </form>

        @if($products->count() > 0)
            <div class="products-grid">
                @foreach($products as $product)
                    <a href="{{ route('suppliers.products.show', $product->id) }}" class="product-card" style="text-decoration: none; color: inherit;">
                        <div class="product-image">
                            @if($product->images && count($product->images) > 0)
                                <img src="{{ asset('storage/' . $product->images[0]) }}" alt="{{ $product->name }}">
                            @else
                                📦
                            @endif
                            <span class="supplier-badge">
                                🏪 {{ $isRtl ? $product->supplier->name_ar : $product->supplier->name }}
                            </span>
                        </div>
                        <div class="product-content">
                            <h3 class="product-name">{{ $isRtl ? $product->name_ar : $product->name }}</h3>
                            <div class="product-supplier">
                                {{ $isRtl ? 'انقر لعرض التفاصيل' : 'Click to view details' }}
                            </div>
                            <div class="product-moq">
                                📦 {{ $isRtl ? 'الحد الأدنى:' : 'Min Order:' }} 
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
                    </a>
                @endforeach
            </div>

            <!-- Pagination -->
            @if($products->hasPages())
                <div class="pagination">
                    {{ $products->appends(request()->query())->links('pagination::simple-default') }}
                </div>
            @endif
        @else
            <div class="no-results">
                <h3>{{ $isRtl ? 'لم يتم العثور على منتجات' : 'No products found' }}</h3>
                <p>{{ $isRtl ? 'جرب تغيير الفلاتر أو مصطلح البحث' : 'Try changing your filters or search term' }}</p>
            </div>
        @endif
    </main>
</body>
</html>
