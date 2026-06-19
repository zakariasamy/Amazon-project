<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Complete Payment - SelaaScout</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <style>
        :root {
            --primary: #f08804;
            --primary-dark: #cc7203;
            --primary-light: #febd69;
            --secondary: #007185;
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
            --line: rgba(0, 0, 0, 0.08);
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Inter', -apple-system, BlinkMacSystemFont, sans-serif;
            background: var(--dark);
            color: var(--white);
            min-height: 100vh;
        }

        /* Sidebar */
        .sidebar {
            position: fixed;
            left: 0;
            top: 0;
            bottom: 0;
            width: 260px;
            background: var(--dark-light);
            border-right: 1px solid var(--line);
            padding: 1.5rem;
            display: flex;
            flex-direction: column;
            z-index: 100;
        }

        .sidebar-logo {
            display: flex;
            align-items: center;
            gap: 0.75rem;
            font-size: 1.25rem;
            font-weight: 700;
            color: var(--white);
            text-decoration: none;
            margin-bottom: 2rem;
            padding-bottom: 1.5rem;
            border-bottom: 1px solid var(--line);
        }

        .sidebar-logo .icon {
            width: 40px;
            height: 40px;
            background: var(--gradient);
            border-radius: 10px;
            display: flex;
            align-items: center;
            justify-content: center;
            color: #ffffff;
        }

        .nav-section {
            margin-bottom: 1.5rem;
        }

        .nav-section-title {
            font-size: 0.7rem;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 0.1em;
            color: var(--gray);
            margin-bottom: 0.75rem;
            padding-left: 0.75rem;
        }

        .nav-item {
            display: flex;
            align-items: center;
            gap: 0.75rem;
            padding: 0.75rem;
            color: var(--gray-light);
            text-decoration: none;
            border-radius: 10px;
            transition: all 0.3s;
            margin-bottom: 0.25rem;
            font-weight: 500;
            font-size: 14px;
        }

        .nav-item:hover, .nav-item.active {
            background: #f1f5f9;
            color: var(--primary);
        }

        .nav-item-icon {
            font-size: 1.1rem;
        }

        .sidebar-footer {
            margin-top: auto;
            padding-top: 1rem;
            border-top: 1px solid var(--line);
        }

        .user-card {
            display: flex;
            align-items: center;
            gap: 0.75rem;
        }

        .user-avatar {
            width: 40px;
            height: 40px;
            background: #e2e8f0;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: 600;
            color: var(--gray-light);
        }

        .user-info {
            overflow: hidden;
        }

        .user-name {
            font-size: 0.9rem;
            font-weight: 600;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
        }

        .user-plan {
            font-size: 0.75rem;
            color: var(--gray);
        }

        /* Main Content */
        .main {
            margin-left: 260px;
            padding: 2.5rem;
            max-width: 800px;
        }

        .header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 2.5rem;
        }

        .header h1 {
            font-size: 1.75rem;
            font-weight: 800;
        }

        .btn {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            padding: 0.75rem 1.5rem;
            border-radius: 10px;
            font-weight: 600;
            font-size: 0.9rem;
            text-decoration: none;
            transition: all 0.3s;
            cursor: pointer;
            border: none;
        }

        .btn-outline {
            background: transparent;
            border: 1px solid rgba(0,0,0,0.15);
            color: var(--white);
        }

        .btn-outline:hover {
            background: rgba(0,0,0,0.04);
        }

        .btn-primary {
            background: var(--primary);
            color: #ffffff;
        }

        .btn-primary:hover {
            background: var(--primary-dark);
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(99, 102, 241, 0.2);
        }

        .card {
            background: var(--dark-light);
            border: 1px solid var(--line);
            border-radius: 20px;
            padding: 2.5rem;
            box-shadow: 0 4px 6px -1px rgba(0,0,0,0.05);
            margin-bottom: 2rem;
        }

        .card-title {
            font-size: 1.25rem;
            font-weight: 700;
            margin-bottom: 1.5rem;
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .plan-summary {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 1.25rem;
            background: #f1f5f9;
            border-radius: 14px;
            margin-bottom: 1.5rem;
        }

        .plan-summary-info h3 {
            font-size: 1.1rem;
            font-weight: 700;
        }

        .plan-summary-info p {
            font-size: 0.85rem;
            color: var(--gray);
            margin-top: 2px;
        }

        .plan-summary-price {
            font-size: 1.5rem;
            font-weight: 800;
            color: var(--primary);
        }

        .instapay-details {
            border-left: 4px solid var(--primary);
            padding-left: 1rem;
            margin-bottom: 2rem;
        }

        .instapay-row {
            margin-bottom: 0.75rem;
        }

        .instapay-label {
            font-size: 0.8rem;
            font-weight: 600;
            color: var(--gray);
            text-transform: uppercase;
        }

        .instapay-value {
            font-size: 1.1rem;
            font-weight: 700;
            color: var(--white);
            user-select: all;
        }

        .instapay-instructions {
            font-size: 0.95rem;
            color: var(--gray-light);
            line-height: 1.5;
            background: #f8fafc;
            padding: 1rem;
            border-radius: 10px;
            border: 1px solid var(--line);
        }

        .form-group {
            margin-bottom: 1.5rem;
        }

        label {
            display: block;
            font-size: 0.9rem;
            font-weight: 600;
            margin-bottom: 0.5rem;
        }

        .file-upload-box {
            border: 2px dashed rgba(0,0,0,0.15);
            border-radius: 14px;
            padding: 2.5rem;
            text-align: center;
            cursor: pointer;
            transition: all 0.3s;
            background: #f8fafc;
        }

        .file-upload-box:hover {
            border-color: var(--primary);
            background: rgba(99, 102, 241, 0.02);
        }

        .file-upload-icon {
            font-size: 2.5rem;
            margin-bottom: 1rem;
        }

        .file-upload-text {
            font-size: 0.95rem;
            color: var(--gray-light);
            font-weight: 500;
        }

        .file-upload-hint {
            font-size: 0.8rem;
            color: var(--gray);
            margin-top: 4px;
        }

        .preview-container {
            margin-top: 1.5rem;
            display: none;
            text-align: center;
        }

        .preview-img {
            max-width: 100%;
            max-height: 300px;
            border-radius: 12px;
            border: 1px solid var(--line);
            box-shadow: 0 4px 10px rgba(0,0,0,0.05);
        }

        .alert-error {
            background: #fee2e2;
            color: #991b1b;
            border: 1px solid #fecaca;
            border-radius: 10px;
            padding: 12px 16px;
            margin-bottom: 1.5rem;
            font-size: 0.9rem;
        }

        @media (max-width: 768px) {
            .sidebar {
                display: none;
            }
            .main {
                margin-left: 0;
                padding: 1.5rem;
            }
        }

        /* ── My Folders marketplace dropdown ─────────────────────────── */
        .nav-item-group {
            position: relative;
        }
        .nav-item-group:hover .folders-dropdown,
        .folders-dropdown:hover {
            display: block;
        }
        .folders-chevron {
            margin-left: auto;
            font-size: 16px;
            opacity: 0.5;
            transition: transform 0.2s;
        }
        .nav-item-group:hover .folders-chevron {
            transform: rotate(90deg);
            opacity: 1;
        }
        .folders-dropdown {
            display: none;
            position: absolute;
            left: calc(100% + 4px);
            top: 0;
            background: #fff;
            border: 1px solid rgba(0,0,0,0.10);
            border-radius: 12px;
            box-shadow: 0 8px 32px rgba(0,0,0,0.12);
            min-width: 230px;
            z-index: 9999;
            overflow: hidden;
            animation: fadeInScale 0.15s ease;
        }
        @keyframes fadeInScale {
            from { opacity: 0; transform: scale(0.96); }
            to   { opacity: 1; transform: scale(1); }
        }
        .folders-dropdown-header {
            padding: 10px 14px 8px;
            font-size: 11px;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.08em;
            color: #94a3b8;
            border-bottom: 1px solid rgba(0,0,0,0.07);
        }
        .folders-mp-item {
            display: flex;
            align-items: center;
            gap: 10px;
            padding: 10px 14px;
            cursor: pointer;
            transition: background 0.15s;
            text-decoration: none;
            color: #0f172a;
            font-size: 14px;
            font-weight: 500;
        }
        .folders-mp-item:hover {
            background: rgba(240,136,4,0.07);
        }
        .folders-mp-item .mp-flag { font-size: 20px; }
        .folders-mp-item .mp-name { flex: 1; }
        .folders-mp-item .mp-currency {
            font-size: 11px;
            color: #64748b;
            background: rgba(0,0,0,0.06);
            border-radius: 5px;
            padding: 2px 6px;
        }
        .folders-mp-item .mp-pin {
            font-size: 14px;
            opacity: 0.3;
            transition: opacity 0.2s;
            cursor: pointer;
            padding: 2px 4px;
        }
        .folders-mp-item .mp-pin:hover,
        .folders-mp-item.pinned .mp-pin {
            opacity: 1;
        }
        .folders-mp-item.pinned {
            background: rgba(240,136,4,0.05);
        }
        .folders-mp-item.pinned .mp-name::after {
            content: ' (pinned)';
            font-size: 10px;
            color: #f08804;
            font-weight: 600;
        }
    </style>
</head>
<body>
    <!-- Sidebar -->
    <aside class="sidebar">
        <a href="/" class="sidebar-logo">
            <img src="{{ asset('images/logo.png') }}" alt="SelaaScout Logo" style="height: 40px; width: 40px; border-radius: 10px; object-fit: contain;">
            SelaaScout
        </a>

        <nav>
            <div class="nav-section">
                <div class="nav-section-title">Main</div>
                <a href="/dashboard" class="nav-item">
                    <span class="nav-item-icon">🏠</span>
                    Dashboard
                </a>
                <!-- My Folders: hover to pick marketplace -->
                <div class="nav-item-group" id="folders-nav-group">
                    <a href="#" class="nav-item" id="folders-main-link" onclick="openFoldersPinned(event)">
                        <span class="nav-item-icon">📁</span>
                        My Folders
                        <span class="folders-chevron">›</span>
                    </a>
                    <div class="folders-dropdown" id="folders-dropdown">
                        <div class="folders-dropdown-header">Open Folders For</div>
                        <div id="folders-dropdown-items">
                            <!-- Populated by JS based on pinned marketplace -->
                        </div>
                    </div>
                </div>
                <a href="/subscription/upgrade" class="nav-item active">
                    <span class="nav-item-icon">💳</span>
                    Upgrade Plan
                </a>
            </div>

            @if(Auth::user()->isAdmin())
            <div class="nav-section">
                <div class="nav-section-title">Admin</div>
                <a href="/admin/settings" class="nav-item">
                    <span class="nav-item-icon">⚙️</span>
                    Admin Tools Settings
                </a>
                <a href="{{ route('admin.pricing.index') }}" class="nav-item">
                    <span class="nav-item-icon">💳</span>
                    Pricing Plans
                </a>
                <a href="{{ route('admin.pricing.subscriptions') }}" class="nav-item">
                    <span class="nav-item-icon">📋</span>
                    Subscriptions
                </a>
                <a href="{{ route('admin.users.index') }}" class="nav-item">
                    <span class="nav-item-icon">👥</span>
                    Manage Users
                </a>
            </div>
            @endif
        </nav>

        <div class="sidebar-footer">
            @php $activeSub = Auth::user()->activeSubscription(); @endphp
            <div class="user-card">
                <div class="user-avatar">{{ substr(Auth::user()->name ?? 'U', 0, 1) }}</div>
                <div class="user-info">
                    <div class="user-name">{{ Auth::user()->name ?? 'User' }}</div>
                    <div class="user-plan">{{ $activeSub ? $activeSub->plan->name . ' Plan' : 'Free Plan' }}</div>
                </div>
            </div>
        </div>
    </aside>

    <!-- Main Content -->
    <main class="main">
        <div class="header">
            <h1>Complete Checkout 🧾</h1>
            <a href="/subscription/upgrade" class="btn btn-outline">← Back to Plans</a>
        </div>

        @if($errors->any())
            <div class="alert-error">
                <ul>
                    @foreach ($errors->all() as $error)
                        <li>❌ {{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <div class="card">
            <div class="card-title">🛒 Selected Plan Summary</div>
            <div class="plan-summary">
                <div class="plan-summary-info">
                    <h3>{{ $plan->name }}</h3>
                    <p>{{ $plan->description }}</p>
                </div>
                <div class="plan-summary-price">${{ number_format($plan->effectivePrice(), 0) }}</div>
            </div>

            <div class="card-title">💳 Payment via InstaPay</div>
            <div class="instapay-details">
                <div class="instapay-row">
                    <div class="instapay-label">InstaPay Address</div>
                    <div class="instapay-value" title="Click to copy">{{ $instapay['username'] }}</div>
                </div>
                @if($instapay['phone'])
                <div class="instapay-row">
                    <div class="instapay-label">InstaPay Phone (secondary)</div>
                    <div class="instapay-value" title="Click to copy">{{ $instapay['phone'] }}</div>
                </div>
                @endif
            </div>

            <div class="instapay-instructions">
                <strong>Instructions:</strong><br>
                {{ $instapay['instructions'] }}
            </div>
        </div>

        <div class="card">
            <div class="card-title">📤 Upload Payment Proof Screenshot</div>
            
            <form method="POST" action="{{ route('subscription.pay.submit') }}" enctype="multipart/form-data">
                @csrf
                <input type="hidden" name="plan_id" value="{{ $plan->id }}">
                
                <div class="form-group">
                    <label for="proof_image">Select Screenshot</label>
                    <div class="file-upload-box" onclick="document.getElementById('proof_image').click()">
                        <div class="file-upload-icon">📸</div>
                        <div class="file-upload-text">Click to choose image or drag & drop here</div>
                        <div class="file-upload-hint">Supports JPEG, PNG, JPG, WEBP (Max 5MB)</div>
                    </div>
                    <input type="file" id="proof_image" name="proof_image" accept="image/*" style="display:none;" required onchange="handleFileSelect(event)">
                </div>

                <div class="preview-container" id="previewContainer">
                    <p style="font-weight:600; margin-bottom: 8px;">Selected Image Preview:</p>
                    <img src="" id="imagePreview" class="preview-img" alt="Screenshot Preview">
                </div>

                <div style="display:flex; justify-content: flex-end; margin-top: 2rem;">
                    <button type="submit" class="btn btn-primary" style="padding:0.9rem 2rem;">Submit Payment Proof</button>
                </div>
            </form>
        </div>
    </main>

    <script>
        function handleFileSelect(event) {
            const file = event.target.files[0];
            if (file) {
                const reader = new FileReader();
                reader.onload = function(e) {
                    const preview = document.getElementById('imagePreview');
                    preview.src = e.target.result;
                    document.getElementById('previewContainer').style.display = 'block';
                }
                reader.readAsDataURL(file);
            }
        }
    </script>
</body>
</html>
<script>
(function() {
    // ── My Folders marketplace dropdown with pinning ─────────────────
    const STORAGE_KEY = 'sela_pinned_marketplace';
    const MARKETPLACES = [
        { code: 'amazon.eg', flag: '\ud83c\uddea\ud83c\uddec', name: 'Egypt',        currency: 'EGP' },
        { code: 'amazon.sa', flag: '\ud83c\uddf8\ud83c\udde6', name: 'Saudi Arabia', currency: 'SAR' },
        { code: 'amazon.ae', flag: '\ud83c\udde6\ud83c\uddea', name: 'UAE',          currency: 'AED' },
        { code: 'amazon.com',flag: '\ud83c\uddfa\ud83c\uddf8', name: 'USA',          currency: 'USD' },
    ];

    function getPinned() {
        return localStorage.getItem(STORAGE_KEY) || 'amazon.eg';
    }
    function setPinned(code) {
        localStorage.setItem(STORAGE_KEY, code);
    }
    function foldersUrl(code) {
        return '/dashboard/folders?marketplace=' + encodeURIComponent(code);
    }
    function renderDropdown() {
        const pinned = getPinned();
        const container = document.getElementById('folders-dropdown-items');
        if (!container) return;
        // Sort: pinned first
        const sorted = [...MARKETPLACES].sort((a, b) =>
            a.code === pinned ? -1 : b.code === pinned ? 1 : 0
        );
        container.innerHTML = sorted.map(mp => `
            <a class="folders-mp-item ${mp.code === pinned ? 'pinned' : ''}"
               href="${foldersUrl(mp.code)}"
               data-code="${mp.code}">
                <span class="mp-flag">${mp.flag}</span>
                <span class="mp-name">${mp.name}</span>
                <span class="mp-currency">${mp.currency}</span>
                <span class="mp-pin" title="Pin this marketplace"
                      onclick="event.preventDefault();event.stopPropagation();pinMarketplace('${mp.code}')">
                    ${mp.code === pinned ? '\ud83d\udccc' : '\ud83d\udccd'}
                </span>
            </a>
        `).join('');
    }
    window.pinMarketplace = function(code) {
        setPinned(code);
        renderDropdown();
    };
    window.openFoldersPinned = function(e) {
        e.preventDefault();
        window.location.href = foldersUrl(getPinned());
    };
    document.addEventListener('DOMContentLoaded', renderDropdown);
})();
</script>
