@php
    $isRtl = app()->getLocale() === 'ar';
    $currentLang = app()->getLocale();
@endphp
<!DOCTYPE html>
<html lang="{{ $currentLang }}" dir="{{ $isRtl ? 'rtl' : 'ltr' }}">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $isRtl ? $product->name_ar : $product->name }} - {{ $isRtl ? 'منتجات الجملة' : 'Wholesale Products' }}</title>
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

        .btn-primary { background: var(--gradient); color: #ffffff; }
        .btn-primary:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 15px rgba(99, 102, 241, 0.4);
        }
        .btn-success { background: var(--success); color: white; }
        .btn-telegram { background: #0088cc; color: white; }
        .btn-whatsapp { background: #25d366; color: white; }
        .btn-lg { padding: 1rem 2rem; font-size: 1rem; }

        .main { max-width: 1200px; margin: 0 auto; padding: 2.5rem 2rem; }

        .breadcrumb {
            display: flex;
            align-items: center;
            gap: 0.5rem;
            font-size: 0.85rem;
            color: var(--gray);
            margin-bottom: 2rem;
        }

        .breadcrumb a {
            color: var(--primary);
            text-decoration: none;
            font-weight: 500;
        }

        .breadcrumb a:hover { text-decoration: underline; }

        .product-layout {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 3rem;
            margin-bottom: 3rem;
        }

        /* Gallery */
        .gallery { position: relative; }

        .main-image {
            width: 100%;
            aspect-ratio: 1;
            background: #f1f5f9;
            border-radius: 20px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 5rem;
            color: var(--gray);
            overflow: hidden;
            margin-bottom: 1rem;
            border: 1px solid rgba(0,0,0,0.06);
        }

        .main-image img {
            width: 100%;
            height: 100%;
            object-fit: contain;
        }

        .thumbnails {
            display: flex;
            gap: 0.75rem;
            flex-wrap: wrap;
        }

        .thumbnail {
            width: 80px;
            height: 80px;
            background: #f1f5f9;
            border: 2px solid rgba(0,0,0,0.06);
            border-radius: 12px;
            cursor: pointer;
            overflow: hidden;
            display: flex;
            align-items: center;
            justify-content: center;
            transition: all 0.3s;
        }

        .thumbnail:hover, .thumbnail.active {
            border-color: var(--primary);
        }

        .thumbnail img {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }

        .thumbnail-video {
            position: relative;
        }

        .thumbnail-video::after {
            content: '▶';
            position: absolute;
            background: rgba(0,0,0,0.7);
            color: white;
            width: 30px;
            height: 30px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 0.7rem;
        }

        .video-player {
            width: 100%;
            aspect-ratio: 16/9;
            background: var(--dark-light);
            border-radius: 16px;
            display: none;
        }

        .video-player.active {
            display: block;
        }

        /* Product Details */
        .product-details h1 {
            font-size: 2rem;
            font-weight: 800;
            margin-bottom: 1rem;
            line-height: 1.3;
        }

        .supplier-link {
            display: inline-flex;
            align-items: center;
            gap: 0.75rem;
            background: var(--dark-light);
            border: 1px solid rgba(0,0,0,0.08);
            padding: 0.75rem 1.25rem;
            border-radius: 12px;
            margin-bottom: 1.5rem;
            text-decoration: none;
            color: var(--white);
            transition: all 0.3s;
            box-shadow: 0 1px 3px rgba(0,0,0,0.02);
        }

        .supplier-link:hover {
            border-color: var(--primary);
            background: rgba(99, 102, 241, 0.05);
        }

        .supplier-avatar {
            width: 40px;
            height: 40px;
            background: var(--gradient);
            border-radius: 10px;
            display: flex;
            align-items: center;
            justify-content: center;
            color: #ffffff;
        }

        .verified-badge {
            background: rgba(16, 185, 129, 0.08);
            color: var(--success);
            padding: 0.25rem 0.6rem;
            border-radius: 20px;
            font-size: 0.75rem;
            font-weight: 600;
        }

        .price-section {
            background: var(--dark-light);
            border: 1px solid rgba(0,0,0,0.08);
            border-radius: 16px;
            padding: 1.75rem;
            margin-bottom: 1.5rem;
            box-shadow: 0 4px 6px -1px rgba(0,0,0,0.02);
        }

        .price-main {
            font-size: 2.25rem;
            font-weight: 800;
            color: var(--success);
            margin-bottom: 0.5rem;
        }

        .price-unit {
            font-size: 1rem;
            color: var(--gray-light);
            font-weight: normal;
        }

        .moq-badge {
            display: inline-flex;
            align-items: center;
            gap: 0.3rem;
            background: rgba(245, 158, 11, 0.08);
            color: var(--warning);
            padding: 0.5rem 1rem;
            border-radius: 20px;
            font-size: 0.9rem;
            font-weight: 600;
            margin-bottom: 1.25rem;
        }

        .price-tiers {
            margin-top: 1.25rem;
            border-top: 1px solid rgba(0,0,0,0.08);
            padding-top: 1.25rem;
        }

        .price-tiers-title {
            font-size: 0.85rem;
            color: var(--gray);
            margin-bottom: 0.75rem;
            font-weight: 600;
        }

        .tier-row {
            display: flex;
            justify-content: space-between;
            padding: 0.5rem 0;
            font-size: 0.9rem;
        }

        .tier-row:not(:last-child) {
            border-bottom: 1px solid rgba(0,0,0,0.06);
        }

        .tier-qty { color: var(--gray-light); font-weight: 500; }
        .tier-price { color: var(--success); font-weight: 700; }

        .product-description {
            margin-bottom: 1.75rem;
        }

        .product-description h3 {
            font-size: 1.05rem;
            margin-bottom: 0.75rem;
            color: var(--white);
            font-weight: 700;
        }

        .product-description p {
            color: var(--gray-light);
            line-height: 1.7;
        }

        .contact-buttons {
            display: flex;
            gap: 0.75rem;
            flex-wrap: wrap;
        }

        /* Related Products */
        .related-section {
            border-top: 1px solid rgba(0,0,0,0.08);
            padding-top: 2.5rem;
            margin-top: 2.5rem;
        }

        .section-title {
            font-size: 1.35rem;
            font-weight: 750;
            margin-bottom: 1.5rem;
        }

        .related-grid {
            display: grid;
            grid-template-columns: repeat(4, 1fr);
            gap: 1.5rem;
        }

        .related-card {
            background: var(--dark-light);
            border: 1px solid rgba(0,0,0,0.08);
            border-radius: 12px;
            overflow: hidden;
            text-decoration: none;
            color: var(--white);
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
            box-shadow: 0 4px 6px -1px rgba(0,0,0,0.02);
        }

        .related-card:hover {
            transform: translateY(-4px);
            border-color: var(--primary);
            box-shadow: 0 10px 15px -3px rgba(99, 102, 241, 0.1);
        }

        .related-image {
            height: 120px;
            background: #f1f5f9;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 2rem;
            color: var(--gray);
        }

        .related-image img {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }

        .related-content {
            padding: 1rem;
        }

        .related-name {
            font-size: 0.9rem;
            font-weight: 600;
            margin-bottom: 0.5rem;
            line-height: 1.3;
        }

        .related-price {
            color: var(--success);
            font-weight: 700;
        }

        @media (max-width: 768px) {
            .product-layout { grid-template-columns: 1fr; gap: 2rem; }
            .related-grid { grid-template-columns: repeat(2, 1fr); }
        }
    </style>
</head>
<body>
    <header class="header">
        <div class="header-content">
            <a href="/suppliers/products" class="logo">📦 {{ $isRtl ? 'منتجات الجملة' : 'Wholesale Products' }}</a>
            <a href="/suppliers/products" class="btn btn-outline">{{ $isRtl ? 'عودة للمنتجات' : 'Back to Products' }}</a>
        </div>
    </header>

    <main class="main">
        <!-- Breadcrumb -->
        <nav class="breadcrumb">
            <a href="/suppliers">{{ $isRtl ? 'التجار' : 'Suppliers' }}</a>
            <span>/</span>
            <a href="/suppliers/products">{{ $isRtl ? 'المنتجات' : 'Products' }}</a>
            <span>/</span>
            <span>{{ $isRtl ? $product->name_ar : $product->name }}</span>
        </nav>

        <div class="product-layout">
            <!-- Gallery -->
            <div class="gallery">
                <div class="main-image" id="mainImage">
                    @if($product->images && count($product->images) > 0)
                        <img src="{{ asset('storage/' . $product->images[0]) }}" alt="{{ $product->name }}" id="mainImg">
                    @else
                        📦
                    @endif
                </div>
                
                @if(($product->images && count($product->images) > 0) || $product->video)
                    <div class="thumbnails">
                        @if($product->images)
                            @foreach($product->images as $index => $image)
                                <div class="thumbnail {{ $index === 0 ? 'active' : '' }}" onclick="showImage('{{ asset('storage/' . $image) }}', this)">
                                    <img src="{{ asset('storage/' . $image) }}" alt="">
                                </div>
                            @endforeach
                        @endif
                        @if($product->video)
                            <div class="thumbnail thumbnail-video" onclick="showVideo()">
                                🎥
                            </div>
                        @endif
                    </div>
                @endif
                
                @if($product->video)
                    <video class="video-player" id="videoPlayer" controls>
                        <source src="{{ asset('storage/' . $product->video) }}" type="video/mp4">
                    </video>
                @endif
            </div>

            <!-- Product Details -->
            <div class="product-details">
                <h1>{{ $isRtl ? $product->name_ar : $product->name }}</h1>
                
                <!-- Supplier Link -->
                <a href="{{ route('suppliers.show', $product->supplier_id) }}" class="supplier-link">
                    <div class="supplier-avatar">🏭</div>
                    <div>
                        <div style="font-weight: 600;">{{ $isRtl ? $product->supplier->name_ar : $product->supplier->name }}</div>
                        <div style="font-size: 0.8rem; color: var(--gray);">{{ $isRtl ? 'عرض صفحة التاجر' : 'View Supplier Page' }}</div>
                    </div>
                    @if($product->supplier->is_verified)
                        <span class="verified-badge">✓ {{ $isRtl ? 'موثق' : 'Verified' }}</span>
                    @endif
                </a>

                <!-- Price Section -->
                <div class="price-section">
                    <div class="moq-badge">
                        📦 {{ $isRtl ? 'الحد الأدنى للطلب:' : 'Min Order:' }} 
                        {{ $product->min_order_quantity }} {{ $isRtl ? $product->unit_ar : $product->unit }}
                    </div>
                    
                    <div class="price-main">
                        {{ number_format($product->base_price, 2) }} EGP
                        <span class="price-unit">/ {{ $isRtl ? $product->unit_ar : $product->unit }}</span>
                    </div>
                    
                    @if($product->price_tiers && count($product->price_tiers) > 0)
                        <div class="price-tiers">
                            <div class="price-tiers-title">{{ $isRtl ? 'أسعار الجملة حسب الكمية:' : 'Bulk Prices by Quantity:' }}</div>
                            @foreach($product->price_tiers as $tier)
                                <div class="tier-row">
                                    <span class="tier-qty">{{ $tier['min_qty'] }}{{ isset($tier['max_qty']) ? ' - '.$tier['max_qty'] : '+' }} {{ $isRtl ? $product->unit_ar : $product->unit }}</span>
                                    <span class="tier-price">{{ number_format($tier['price'], 2) }} EGP</span>
                                </div>
                            @endforeach
                        </div>
                    @endif
                </div>

                <!-- Description -->
                @if($product->description || $product->description_ar)
                    <div class="product-description">
                        <h3>{{ $isRtl ? 'وصف المنتج' : 'Product Description' }}</h3>
                        <p>{{ $isRtl ? ($product->description_ar ?: $product->description) : ($product->description ?: $product->description_ar) }}</p>
                    </div>
                @endif

                <!-- Specifications -->
                @if($product->specifications || $product->specifications_ar)
                    <div class="product-description">
                        <h3>{{ $isRtl ? 'المواصفات' : 'Specifications' }}</h3>
                        <p>{{ $isRtl ? ($product->specifications_ar ?: $product->specifications) : ($product->specifications ?: $product->specifications_ar) }}</p>
                    </div>
                @endif

                <!-- Origin -->
                @if($product->origin_country || $product->origin_country_ar)
                    <div class="product-description">
                        <h3>{{ $isRtl ? 'بلد المنشأ' : 'Country of Origin' }}</h3>
                        <p>🌍 {{ $isRtl ? ($product->origin_country_ar ?: $product->origin_country) : ($product->origin_country ?: $product->origin_country_ar) }}</p>
                    </div>
                @endif

                <!-- Contact Buttons -->
                <div class="contact-buttons">
                    @if($product->supplier->telegram_group_link)
                        <a href="{{ $product->supplier->telegram_group_link }}" target="_blank" class="btn btn-telegram btn-lg">
                            📱 {{ $isRtl ? 'تواصل عبر تليجرام' : 'Contact on Telegram' }}
                        </a>
                    @endif
                    @if($product->supplier->whatsapp)
                        <a href="https://wa.me/{{ $product->supplier->whatsapp }}?text={{ urlencode(($isRtl ? 'مرحباً، أنا مهتم بمنتج: ' : 'Hi, I am interested in: ') . $product->name) }}" target="_blank" class="btn btn-whatsapp btn-lg">
                            💬 {{ $isRtl ? 'تواصل عبر واتساب' : 'WhatsApp' }}
                        </a>
                    @endif
                </div>
            </div>
        </div>

        <!-- Related Products -->
        @if($relatedProducts->count() > 0)
            <section class="related-section">
                <h2 class="section-title">{{ $isRtl ? 'منتجات أخرى من نفس التاجر' : 'More from this Supplier' }}</h2>
                <div class="related-grid">
                    @foreach($relatedProducts as $related)
                        <a href="{{ route('suppliers.products.show', $related->id) }}" class="related-card">
                            <div class="related-image">
                                @if($related->images && count($related->images) > 0)
                                    <img src="{{ asset('storage/' . $related->images[0]) }}" alt="{{ $related->name }}">
                                @else
                                    📦
                                @endif
                            </div>
                            <div class="related-content">
                                <div class="related-name">{{ $isRtl ? $related->name_ar : $related->name }}</div>
                                <div class="related-price">{{ number_format($related->base_price, 2) }} EGP</div>
                            </div>
                        </a>
                    @endforeach
                </div>
            </section>
        @endif
    </main>

    <script>
        function showImage(src, thumbnail) {
            document.getElementById('mainImg').src = src;
            document.getElementById('mainImage').style.display = 'flex';
            document.getElementById('videoPlayer')?.classList.remove('active');
            
            document.querySelectorAll('.thumbnail').forEach(t => t.classList.remove('active'));
            thumbnail.classList.add('active');
        }

        function showVideo() {
            document.getElementById('mainImage').style.display = 'none';
            document.getElementById('videoPlayer').classList.add('active');
            document.querySelectorAll('.thumbnail').forEach(t => t.classList.remove('active'));
            document.querySelector('.thumbnail-video')?.classList.add('active');
        }
    </script>
</body>
</html>
