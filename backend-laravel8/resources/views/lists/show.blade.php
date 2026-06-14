@php
    $descData = null;
    $avgVolume = 0;
    if ($list->type === 'competitor_keyword_analyzer') {
        if (!empty($list->description)) {
            $descData = json_decode($list->description, true);
        }
        $volumes = [];
        foreach($items as $item) {
            if (isset($item->data['search_volume'])) {
                $volumes[] = (int)$item->data['search_volume'];
            }
        }
        if (count($volumes) > 0) {
            $avgVolume = array_sum($volumes) / count($volumes);
        }
    }
@endphp
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $list->name }} — {{ $list->typeLabel() }}</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <style>
        :root{
            --primary:#6366f1;--secondary:#0ea5e9;--success:#10b981;
            --warning:#f59e0b;--danger:#ef4444;
            --bg:#f8fafc;--surface:#ffffff;--border:rgba(0,0,0,.08);
            --text:#0f172a;--muted:#64748b;--muted-light:#475569;
            --gradient:linear-gradient(135deg,#6366f1 0%,#0ea5e9 100%);
        }
        *{margin:0;padding:0;box-sizing:border-box;}
        body{font-family:'Inter',sans-serif;background:var(--bg);color:var(--text);min-height:100vh;}

        .sidebar{position:fixed;left:0;top:0;bottom:0;width:260px;background:var(--surface);border-right:1px solid var(--border);padding:1.5rem;display:flex;flex-direction:column;}
        .sidebar-logo{display:flex;align-items:center;gap:.75rem;font-size:1.25rem;font-weight:700;color:var(--text);text-decoration:none;margin-bottom:2rem;padding-bottom:1.5rem;border-bottom:1px solid var(--border);}
        .logo-icon{width:40px;height:40px;background:var(--gradient);border-radius:10px;display:flex;align-items:center;justify-content:center;color:#fff;}
        .nav-section{margin-bottom:1.5rem;}
        .nav-section-title{font-size:.7rem;font-weight:600;text-transform:uppercase;letter-spacing:.1em;color:var(--muted);margin-bottom:.75rem;padding-left:.75rem;}
        .nav-item{display:flex;align-items:center;gap:.75rem;padding:.75rem;color:var(--muted-light);text-decoration:none;border-radius:10px;transition:all .3s;margin-bottom:.25rem;font-size:.875rem;}
        .nav-item:hover{background:rgba(0,0,0,.04);color:var(--text);}
        .nav-item.active{background:var(--primary);color:#fff;}
        .sidebar-footer{margin-top:auto;padding-top:1rem;border-top:1px solid var(--border);}
        .user-card{display:flex;align-items:center;gap:.75rem;padding:.75rem;background:var(--bg);border-radius:12px;}
        .user-avatar{width:40px;height:40px;background:var(--gradient);border-radius:10px;display:flex;align-items:center;justify-content:center;font-weight:600;color:#fff;}
        .user-name{font-weight:600;font-size:.875rem;}
        .user-plan{font-size:.75rem;color:var(--muted);}

        .main{margin-left:260px;padding:2rem;}

        /* Breadcrumb */
        .breadcrumb{display:flex;align-items:center;gap:.5rem;font-size:.875rem;color:var(--muted);margin-bottom:1.5rem;flex-wrap:wrap;}
        .breadcrumb a{color:var(--primary);text-decoration:none;}
        .breadcrumb a:hover{text-decoration:underline;}
        .breadcrumb .sep{opacity:.4;}

        /* Alert */
        .alert{padding:.875rem 1.25rem;border-radius:10px;margin-bottom:1.5rem;font-size:.875rem;font-weight:500;}
        .alert-success{background:rgba(16,185,129,.1);color:#059669;border:1px solid rgba(16,185,129,.2);}

        /* Page header */
        .page-header{background:var(--surface);border:1px solid var(--border);border-radius:20px;padding:1.75rem 2rem;margin-bottom:2rem;display:flex;justify-content:space-between;align-items:center;}
        .page-header-left{display:flex;align-items:center;gap:1.25rem;}
        .list-big-icon{font-size:2.5rem;}
        .list-title{font-size:1.5rem;font-weight:700;margin-bottom:.25rem;}
        .list-meta{display:flex;align-items:center;gap:.75rem;}
        .type-badge{display:inline-flex;align-items:center;gap:.35rem;padding:.35rem .875rem;border-radius:20px;font-size:.8rem;font-weight:600;}
        .badge-products{background:rgba(16,185,129,.12);color:#059669;}
        .badge-keyword_magnet{background:rgba(99,102,241,.12);color:#4f46e5;}
        .badge-competitor_keyword_analyzer{background:rgba(14,165,233,.12);color:#0284c7;}
        .badge-reverse_asin{background:rgba(245,158,11,.12);color:#b45309;}
        .item-count-badge{font-size:.8rem;color:var(--muted);}

        /* Toolbar */
        .toolbar{display:flex;justify-content:space-between;align-items:center;margin-bottom:1rem;}
        .toolbar-left{display:flex;align-items:center;gap:.75rem;}
        .selection-info{font-size:.875rem;color:var(--muted);}

        /* Buttons */
        .btn{display:inline-flex;align-items:center;gap:.5rem;padding:.75rem 1.25rem;border-radius:10px;font-weight:600;font-size:.875rem;font-family:inherit;cursor:pointer;border:none;text-decoration:none;transition:all .3s;}
        .btn-primary{background:var(--gradient);color:#fff;}
        .btn-primary:hover{transform:translateY(-2px);box-shadow:0 4px 15px rgba(99,102,241,.4);}
        .btn-outline{background:transparent;border:2px solid var(--border);color:var(--text);}
        .btn-outline:hover{border-color:var(--primary);color:var(--primary);}
        .btn-danger{background:rgba(239,68,68,.1);color:#dc2626;border:1px solid rgba(239,68,68,.2);}
        .btn-danger:hover{background:#ef4444;color:#fff;}
        .btn-sm{padding:.45rem .875rem;font-size:.8rem;}
        .btn:disabled{opacity:.4;cursor:not-allowed;transform:none!important;}

        /* Table */
        .card{background:var(--surface);border:1px solid var(--border);border-radius:16px;overflow:hidden;}
        .table-wrapper{overflow-x:auto;}
        .table{width:100%;border-collapse:collapse;}
        .table th,.table td{padding:.875rem 1rem;text-align:left;border-bottom:1px solid var(--border);}
        .table th{font-size:.72rem;font-weight:700;text-transform:uppercase;letter-spacing:.06em;color:var(--muted);background:var(--bg);}
        .table td{font-size:.875rem;}
        .table tbody tr:hover{background:rgba(99,102,241,.02);}
        .table tbody tr.selected-row{background:rgba(99,102,241,.06);}
        .table .cb-col{width:40px;text-align:center;}
        .table .action-col{width:60px;text-align:center;}

        /* Checkbox */
        input[type="checkbox"]{width:16px;height:16px;accent-color:var(--primary);cursor:pointer;}

        /* Keyword / ASIN pill */
        .keyword-pill{font-weight:600;color:var(--text);}
        .asin-pill{font-family:monospace;font-size:.8rem;background:var(--bg);padding:.2rem .5rem;border-radius:6px;color:var(--primary);font-weight:600;}

        /* Volume/score badge */
        .vol-badge{display:inline-flex;align-items:center;padding:.2rem .6rem;border-radius:20px;font-size:.78rem;font-weight:600;background:rgba(16,185,129,.1);color:#059669;}
        .score-badge{display:inline-flex;align-items:center;padding:.2rem .6rem;border-radius:20px;font-size:.78rem;font-weight:600;background:rgba(99,102,241,.1);color:var(--primary);}

        /* Difficulty bar */
        .diff-wrap{display:flex;align-items:center;gap:.5rem;}
        .diff-bar{width:60px;height:6px;background:rgba(0,0,0,.08);border-radius:3px;overflow:hidden;}
        .diff-fill{height:100%;border-radius:3px;}
        .diff-fill.easy{background:#10b981;}
        .diff-fill.medium{background:#f59e0b;}
        .diff-fill.hard{background:#ef4444;}

        /* Row delete btn */
        .row-delete-btn{background:none;border:none;font-size:1rem;cursor:pointer;color:var(--muted);padding:.25rem;border-radius:6px;transition:all .2s;}
        .row-delete-btn:hover{color:#ef4444;background:rgba(239,68,68,.1);}

        /* Empty state */
        .empty-state{text-align:center;padding:5rem 2rem;color:var(--muted);}
        .empty-state-icon{font-size:4rem;margin-bottom:1.25rem;}
        .empty-state h3{font-size:1.25rem;font-weight:700;margin-bottom:.5rem;color:var(--text);}
        .empty-state p{max-width:380px;margin:0 auto;line-height:1.6;}

        /* Pagination */
        .pagination{display:flex;justify-content:center;gap:.5rem;padding:1.25rem;}
        .pagination a,.pagination span{padding:.5rem .875rem;border-radius:8px;font-size:.875rem;text-decoration:none;font-weight:500;}
        .pagination a{color:var(--primary);border:1px solid var(--border);}
        .pagination a:hover{background:var(--primary);color:#fff;}
        .pagination .active span{background:var(--primary);color:#fff;border-radius:8px;}
        .pagination .disabled span{color:var(--muted);}

        /* Modal */
        .modal-overlay{position:fixed;inset:0;background:rgba(0,0,0,.5);backdrop-filter:blur(4px);z-index:1000;display:none;align-items:center;justify-content:center;}
        .modal-overlay.open{display:flex;}
        .modal{background:var(--surface);border-radius:20px;padding:2rem;width:100%;max-width:420px;box-shadow:0 25px 50px rgba(0,0,0,.15);}
        .modal-header{display:flex;justify-content:space-between;align-items:center;margin-bottom:1.25rem;}
        .modal-title{font-size:1.125rem;font-weight:700;}
        .modal-close{background:none;border:none;font-size:1.5rem;cursor:pointer;color:var(--muted);}
        .form-actions{display:flex;gap:.75rem;justify-content:flex-end;}

        @media(max-width:768px){.sidebar{display:none;}.main{margin-left:0;}}
    </style>
</head>
<body>

<!-- Sidebar -->
<aside class="sidebar">
    <a href="/dashboard" class="sidebar-logo">
        <div class="logo-icon">📊</div>
        Amazon Analyzer
    </a>
    <nav>
        <div class="nav-section">
            <div class="nav-section-title">Main</div>
            <a href="/dashboard" class="nav-item">🏠 Dashboard</a>
            <a href="/folders" class="nav-item active">📁 My Folders</a>
        </div>
        <div class="nav-section">
            <div class="nav-section-title">Account</div>
            <a href="/settings" class="nav-item">⚙️ Settings</a>
        </div>
    </nav>
    <div class="sidebar-footer">
        <div class="user-card">
            <div class="user-avatar">{{ substr(Auth::user()->name ?? 'U', 0, 1) }}</div>
            <div>
                <div class="user-name">{{ Auth::user()->name ?? 'User' }}</div>
                <div class="user-plan">Free Plan</div>
            </div>
        </div>
    </div>
</aside>

<!-- Main -->
<main class="main">

    <!-- Breadcrumb -->
    <div class="breadcrumb">
        <a href="/dashboard">Dashboard</a>
        <span class="sep">›</span>
        <a href="/folders">My Folders</a>
        @foreach($breadcrumb as $crumb)
            <span class="sep">›</span>
            <a href="/folders/{{ $crumb['id'] }}">{{ $crumb['name'] }}</a>
        @endforeach
        <span class="sep">›</span>
        <span>{{ $list->name }}</span>
    </div>

    @if(session('success'))
        <div class="alert alert-success">✓ {{ session('success') }}</div>
    @endif

    <!-- Page Header -->
    <div class="page-header">
        <div class="page-header-left">
            <span class="list-big-icon">{{ \App\Models\DashboardList::TYPE_ICONS[$list->type] ?? '📋' }}</span>
            <div>
                <div class="list-title">{{ $list->name }}</div>
                <div class="list-meta">
                    <span class="type-badge badge-{{ $list->type }}">{{ $list->typeLabel() }}</span>
                    <span class="item-count-badge">{{ $list->item_count }} item{{ $list->item_count != 1 ? 's' : '' }}</span>
                </div>
            </div>
        </div>
        <div style="display:flex;gap:.75rem;">
            @if($list->folder)
                <a href="/folders/{{ $list->folder_id }}" class="btn btn-outline btn-sm">← Back to Folder</a>
            @endif
            <button class="btn btn-danger btn-sm" onclick="openModal('delete-list-modal')">🗑️ Delete List</button>
        </div>
    </div>

    @if($list->type === 'competitor_keyword_analyzer')
        @if(!$descData)
            <!-- DEBUG: description is empty or failed to decode -->
            <div style="margin-bottom: 1.5rem; background: rgba(245,158,11,.08); border: 1px solid rgba(245,158,11,.3); border-radius: 12px; padding: 14px 18px; font-size: 13px; color: #b45309;">
                <strong>⚠️ No analyzed product data found.</strong>
                @if(!empty($list->description))
                    Description exists but could not be decoded as JSON.
                    <details style="margin-top:8px;">
                        <summary style="cursor:pointer;font-size:11px;">Show raw description (debug)</summary>
                        <pre style="margin-top:8px;font-size:10px;white-space:pre-wrap;word-break:break-all;background:rgba(0,0,0,.05);padding:8px;border-radius:6px;">{{ htmlspecialchars(Str::limit($list->description, 2000)) }}</pre>
                    </details>
                @else
                    No description stored. Save the analysis again from the extension to store product data.
                @endif
            </div>
        @else
            <!-- Cerebro Run Summary & Analyzed Products -->
            <div style="margin-bottom: 2rem; display: grid; grid-template-columns: 280px 1fr; gap: 20px;">
                <!-- Summary stats card -->
                <div style="background: var(--surface); border: 1px solid var(--border); border-radius: 16px; padding: 20px; display: flex; flex-direction: column; justify-content: center; gap: 16px;">
                    <h4 style="margin: 0; font-size: 11px; font-weight: 700; color: var(--muted); text-transform: uppercase; letter-spacing: 0.5px;">Run Summary</h4>
                    <div style="display: flex; flex-direction: column; gap: 12px;">
                        <div>
                            <div style="font-size: 20px; font-weight: 700; color: var(--text);">{{ count($descData['asins'] ?? []) }}</div>
                            <div style="font-size: 11px; color: var(--muted-light);">ASINs Analyzed</div>
                        </div>
                        <div>
                            <div style="font-size: 20px; font-weight: 700; color: var(--text);">{{ $list->item_count }}</div>
                            <div style="font-size: 11px; color: var(--muted-light);">Keywords Found</div>
                        </div>
                        <div>
                            <div style="font-size: 20px; font-weight: 700; color: var(--primary);">{{ number_format($avgVolume) }}</div>
                            <div style="font-size: 11px; color: var(--muted-light);">Avg Volume</div>
                        </div>
                    </div>
                </div>

                <!-- Analyzed Products List -->
                <div style="background: var(--surface); border: 1px solid var(--border); border-radius: 16px; padding: 20px; display: flex; flex-direction: column;">
                    <h4 style="margin: 0 0 14px 0; font-size: 11px; font-weight: 700; color: var(--muted); text-transform: uppercase; letter-spacing: 0.5px;">Analyzed Products</h4>
                    <div style="display: flex; gap: 16px; overflow-x: auto; padding-bottom: 8px; flex: 1; align-items: center;">
                        @if(isset($descData['analyzed_products']) && is_array($descData['analyzed_products']) && count($descData['analyzed_products']) > 0)
                            @foreach($descData['analyzed_products'] as $p)
                                @php
                                    $pAsin = $p['asin'] ?? '';
                                    $pUrl  = $p['url'] ?? ($pAsin ? 'https://www.amazon.com/dp/' . $pAsin : '#');
                                    $pImg  = $p['image'] ?? '';
                                    $pTitle = $p['title'] ?? $pAsin ?: 'Unknown';
                                @endphp
                                <div style="display: flex; align-items: center; gap: 10px; background: var(--bg); padding: 8px 14px; border: 1px solid var(--border); border-radius: 10px; min-width: 250px; max-width: 300px; flex-shrink: 0;">
                                    @if($pImg)
                                        <img src="{{ $pImg }}" alt="" style="width: 36px; height: 36px; object-fit: contain; border-radius: 4px; background: #fff; border: 1px solid var(--border); flex-shrink: 0;" onerror="this.style.display='none'"/>
                                    @else
                                        <div style="width:36px;height:36px;background:var(--bg);border:1px solid var(--border);border-radius:4px;display:flex;align-items:center;justify-content:center;flex-shrink:0;">📦</div>
                                    @endif
                                    <div style="overflow: hidden; text-overflow: ellipsis; white-space: nowrap; font-size: 12px; font-weight: 500; flex: 1;">
                                        <div style="font-weight: 700; color: var(--primary); margin-bottom: 2px;">
                                            <a href="{{ $pUrl }}" target="_blank" style="color: inherit; text-decoration: none;">
                                                {{ $pAsin }}
                                            </a>
                                        </div>
                                        <div style="color: var(--text); overflow: hidden; text-overflow: ellipsis; white-space: nowrap; width: 100%;" title="{{ $pTitle }}">
                                            {{ $pTitle }}
                                        </div>
                                    </div>
                                </div>
                            @endforeach
                        @elseif(isset($descData['asins']) && is_array($descData['asins']) && count($descData['asins']) > 0)
                            {{-- Fallback: render from plain ASIN list when analyzed_products not stored --}}
                            @foreach($descData['asins'] as $asinEntry)
                                <div style="display: flex; align-items: center; gap: 10px; background: var(--bg); padding: 8px 14px; border: 1px solid var(--border); border-radius: 10px; min-width: 200px; flex-shrink: 0;">
                                    <div style="width:36px;height:36px;background:rgba(99,102,241,.1);border:1px solid rgba(99,102,241,.2);border-radius:4px;display:flex;align-items:center;justify-content:center;flex-shrink:0;">📦</div>
                                    <div>
                                        <a href="https://www.amazon.com/dp/{{ $asinEntry }}" target="_blank" style="font-weight:700;color:var(--primary);font-size:12px;text-decoration:none;">{{ $asinEntry }}</a>
                                    </div>
                                </div>
                            @endforeach
                        @else
                            <span style="color: var(--muted-light); font-size: 13px;">No product details saved. Re-run analysis and save again.</span>
                        @endif
                    </div>
                </div>
            </div>
        @endif
    @endif

    <!-- Toolbar -->
    <div class="toolbar">
        <div class="toolbar-left">
            <span class="selection-info" id="selection-info">0 selected</span>
            <button class="btn btn-danger btn-sm" id="remove-selected-btn" disabled onclick="removeSelected()">
                🗑️ Remove Selected
            </button>
        </div>
        <div>
            <label style="display:flex;align-items:center;gap:.5rem;font-size:.875rem;cursor:pointer;">
                <input type="checkbox" id="select-all-cb" onchange="toggleAll(this)"> Select all
            </label>
        </div>
    </div>

    <!-- Items Table -->
    <div class="card">
        @if($items->count() > 0)
            <div class="table-wrapper">
                <table class="table" id="items-table">
                    <thead>
                        <tr>
                            <th class="cb-col"><input type="checkbox" id="thead-cb" onchange="toggleAll(this)"></th>
                            @if($list->type === 'products')
                                <th>ASIN</th>
                                <th>Title</th>
                                <th>Price</th>
                                <th>BSR</th>
                                <th>Rating</th>
                            @elseif($list->type === 'keyword_magnet')
                                <th>Keyword</th>
                                <th>Seed</th>
                                <th style="text-align:right;">Volume</th>
                                <th style="text-align:right;">Difficulty</th>
                                <th style="text-align:right;">Est. Daily Sales</th>
                                <th style="text-align:right;">Sales</th>
                                <th style="text-align:right;">Avg Price</th>
                                <th style="text-align:right;">Words</th>
                                <th style="text-align:center;">Type</th>
                            @elseif($list->type === 'competitor_keyword_analyzer')
                                <th>Keyword</th>
                                <th style="text-align: right;">Searches</th>
                                <th style="text-align: right;">Top 3 Sales Share</th>
                                <th style="text-align: right;">Sales</th>
                                <th style="text-align: right;">AD Density</th>
                                <th style="text-align: right;">Difficulty</th>
                                <th style="text-align: right;">Sponsored</th>
                                <th style="text-align: right;">Words</th>
                                <th style="text-align: center;">Ranking</th>
                                @if(isset($descData['asins']) && is_array($descData['asins']))
                                    @foreach($descData['asins'] as $i => $asin)
                                        <th style="text-align: center;" title="{{ $asin }}">#{{ $i + 1 }}</th>
                                    @endforeach
                                @endif
                            @elseif($list->type === 'reverse_asin')
                                <th>Keyword</th>
                                <th>Rank</th>
                                <th>Search Volume</th>
                                <th>Difficulty</th>
                                <th>Sales</th>
                                <th>Ads</th>
                                <th>Avg Price</th>
                                <th>ASIN</th>
                            @elseif($list->type === 'market_analysis')
                                <th>Keyword</th>
                                <th>Search Volume</th>
                                <th>Difficulty</th>
                                <th>Total Sales</th>
                                <th>Avg Revenue</th>
                                <th>Avg Price</th>
                                <th>Avg BSR</th>
                                <th>Avg Reviews</th>
                                <th>Ads Density</th>
                            @endif
                            <th class="action-col"></th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($items as $item)
                            @php
                                $d = $item->data;

                                // Determine the currency
                                $currency = 'USD';
                                if (isset($d['currency'])) {
                                    $currency = $d['currency'];
                                } elseif (isset($d['marketplace']) && str_contains($d['marketplace'], '.eg')) {
                                    $currency = 'EGP';
                                }

                                $priceFormatted = '—';
                                if (isset($d['price'])) {
                                    if ($currency === 'EGP') {
                                        $priceFormatted = number_format($d['price'], 2) . ' EGP';
                                    } else {
                                        $priceFormatted = '$' . number_format($d['price'], 2);
                                    }
                                }

                                // Determine the product URL (preserving saved language and marketplace)
                                if (isset($d['url'])) {
                                    $productUrl = $d['url'];
                                } else {
                                    $marketplace = $d['marketplace'] ?? 'www.amazon.com';
                                    if (!str_contains($marketplace, 'amazon.')) {
                                        $marketplace = 'www.amazon.com';
                                    }
                                    $asin = $d['asin'] ?? '';

                                    // Fallback language detection for existing items
                                    $hasArabicTitle = isset($d['title']) && preg_match('/\p{Arabic}/u', $d['title']);
                                    if (str_contains($marketplace, '.eg')) {
                                        $langParam = $hasArabicTitle ? 'language=ar_EG' : 'language=en_EG';
                                        $productUrl = "https://{$marketplace}/dp/{$asin}?{$langParam}";
                                    } else {
                                        $langParam = $hasArabicTitle ? 'language=ar_US' : '';
                                        $productUrl = "https://{$marketplace}/dp/{$asin}" . ($langParam ? "?{$langParam}" : "");
                                    }
                                }
                            @endphp
                            <tr id="row-{{ $item->id }}">
                                <td class="cb-col">
                                    <input type="checkbox" class="row-cb" value="{{ $item->id }}" onchange="updateSelection()">
                                </td>

                                @if($list->type === 'products')
                                    <td>
                                        @if(isset($d['asin']))
                                            <a href="{{ $productUrl }}" target="_blank" class="asin-pill" style="text-decoration:none;">{{ $d['asin'] }}</a>
                                        @else
                                            —
                                        @endif
                                    </td>
                                    <td style="max-width:260px;overflow:hidden;text-overflow:ellipsis;white-space:nowrap;" title="{{ $d['title'] ?? '' }}">
                                        @if(isset($d['asin']))
                                            <a href="{{ $productUrl }}" target="_blank" style="color:inherit;text-decoration:none;font-weight:600;" onmouseover="this.style.color='var(--primary)'" onmouseout="this.style.color='inherit'">{{ $d['title'] ?? '—' }}</a>
                                        @else
                                            {{ $d['title'] ?? '—' }}
                                        @endif
                                    </td>
                                    <td>{{ $priceFormatted }}</td>
                                    <td>{{ isset($d['bsr']) ? '#'.number_format($d['bsr']) : '—' }}</td>
                                    <td>{{ $d['rating'] ?? '—' }} {{ isset($d['rating_count']) ? '('.number_format($d['rating_count']).')' : '' }}</td>

                                @elseif($list->type === 'keyword_magnet')
                                    <td><span class="keyword-pill">{{ $d['keyword'] ?? '—' }}</span></td>
                                    <td style="max-width:180px;">
                                        @if(!empty($d['seed_keyword']))
                                            <span style="display:inline-block;background:rgba(251,191,36,.12);color:#fbbf24;padding:2px 7px;border-radius:5px;font-size:11px;font-weight:600;white-space:normal;word-break:break-word;">{{ $d['seed_keyword'] }}</span>
                                        @else
                                            <span style="color:var(--muted);">—</span>
                                        @endif
                                    </td>
                                    <td style="text-align:right;"><span class="vol-badge">{{ isset($d['search_volume']) ? number_format($d['search_volume']) : '—' }}</span></td>
                                    <td style="text-align:right;">
                                        @if(isset($d['difficulty']))
                                            @php
                                                $diff = (int)$d['difficulty'];
                                                $cls = $diff < 40 ? 'easy' : ($diff < 70 ? 'medium' : 'hard');
                                            @endphp
                                            <div class="diff-wrap" style="justify-content:flex-end;">
                                                <div class="diff-bar"><div class="diff-fill {{ $cls }}" style="width:{{ $diff }}%"></div></div>
                                                <span style="font-size:.78rem;">{{ $diff }}</span>
                                            </div>
                                        @else —
                                        @endif
                                    </td>
                                    <td style="text-align:right;color:#f59e0b;font-weight:600;">
                                        {{ isset($d['cpr_8day']) && $d['cpr_8day'] !== '' && $d['cpr_8day'] != 0 ? $d['cpr_8day'].'/day' : '—' }}
                                    </td>
                                    <td style="text-align:right;color:#10b981;font-weight:600;">{{ isset($d['keyword_sales']) ? number_format($d['keyword_sales']) : '—' }}</td>
                                    <td style="text-align:right;">
                                        @if(isset($d['avg_price']) && $d['avg_price'] > 0)
                                            <span style="color:#6b7280;font-size:10px;">{{ $d['currency'] ?? 'USD' }}</span>
                                            <span style="color:#e5e7eb;">{{ number_format($d['avg_price'], 2) }}</span>
                                        @else
                                            —
                                        @endif
                                    </td>
                                    <td style="text-align:right;color:var(--muted-light);">{{ $d['word_count'] ?? '—' }}</td>
                                    <td style="text-align:center;">
                                        @if(!empty($d['match_type']))
                                            @foreach(explode(',', $d['match_type']) as $mtype)
                                                @php $mtype = trim($mtype); @endphp
                                                @if($mtype)
                                                    <span style="
                                                        background:rgba(167,139,250,.12);color:#a78bfa;
                                                        padding:2px 5px;border-radius:4px;font-size:9px;
                                                        font-weight:700;text-transform:uppercase;
                                                        display:inline-block;margin:1px;
                                                    ">{{ $mtype }}</span>
                                                @endif
                                            @endforeach
                                        @else
                                            —
                                        @endif
                                    </td>

                                @elseif($list->type === 'competitor_keyword_analyzer')
                                    <td><span class="keyword-pill">{{ $d['keyword'] ?? '—' }}</span></td>
                                    <td style="text-align: right;"><span class="vol-badge">{{ isset($d['search_volume']) ? number_format($d['search_volume']) : '—' }}</span></td>
                                    <td style="text-align: right; color: #10b981; font-weight: 600;">{{ isset($d['total_click_share']) ? round($d['total_click_share']) . '%' : '—' }}</td>
                                    <td style="text-align: right; color: #0ea5e9; font-weight: 600;">{{ isset($d['total_keyword_sales']) ? number_format($d['total_keyword_sales']) : '—' }}</td>
                                    <td style="text-align: right; color: #a855f7; font-weight: 600;">
                                        @php
                                            $sponsoredCount = $d['sponsored_count'] ?? 0;
                                            $totalProducts = 48; // default to page size
                                            $adDensity = $totalProducts > 0 ? round(($sponsoredCount / $totalProducts) * 100) : 0;
                                        @endphp
                                        {{ $adDensity }}%
                                    </td>
                                    <td style="text-align: right;">
                                        @if(isset($d['difficulty_score']))
                                            @php
                                                $diff = (int)$d['difficulty_score'];
                                                $cls = $diff < 40 ? 'easy' : ($diff < 70 ? 'medium' : 'hard');
                                            @endphp
                                            <div class="diff-wrap" style="justify-content: flex-end;">
                                                <div class="diff-bar"><div class="diff-fill {{ $cls }}" style="width:{{ $diff }}%"></div></div>
                                                <span style="font-size:.78rem;">{{ $diff }}</span>
                                            </div>
                                        @else
                                            —
                                        @endif
                                    </td>
                                    <td style="text-align: right; color: #eab308; font-weight: 600;">{{ $d['sponsored_count'] ?? '0' }}</td>
                                    <td style="text-align: right; color: var(--muted-light);">{{ $d['word_count'] ?? '—' }}</td>
                                    <td style="text-align: center;">
                                        @php
                                            $asinsRanking = $d['asins_ranking'] ?? 0;
                                            $totalAsins = isset($descData['asins']) ? count($descData['asins']) : 1;
                                        @endphp
                                        <span style="background:{{ $asinsRanking > 0 ? 'rgba(16,185,129,.12)' : 'rgba(148,163,184,.12)' }}; color:{{ $asinsRanking > 0 ? '#059669' : '#64748b' }}; padding:.2rem .5rem; border-radius:6px; font-weight:600;">
                                            {{ $asinsRanking }}/{{ $totalAsins }}
                                        </span>
                                    </td>
                                    @if(isset($descData['asins']) && is_array($descData['asins']))
                                        @foreach($descData['asins'] as $asin)
                                            @php
                                                $rank = $d['organic_ranks'][$asin] ?? null;
                                            @endphp
                                            <td style="text-align: center; color: {{ $rank ? '#059669' : 'var(--muted)' }}; font-weight: {{ $rank ? '600' : '400' }};">
                                                {{ $rank ? '#' . $rank : '-' }}
                                            </td>
                                        @endforeach
                                    @endif

                                @elseif($list->type === 'reverse_asin')
                                    <td><span class="keyword-pill">{{ $d['keyword'] ?? '—' }}</span></td>
                                    <td>
                                        @if(isset($d['position']) && $d['position'] > 0)
                                            <span style="background:rgba(16,185,129,.12);color:#059669;padding:.2rem .5rem;border-radius:6px;font-weight:600;">#{{ $d['position'] }}</span>
                                        @elseif(isset($d['ranking_products']) && $d['ranking_products'] > 0)
                                            <span style="background:rgba(16,185,129,.12);color:#059669;padding:.2rem .5rem;border-radius:6px;font-weight:600;">#{{ $d['ranking_products'] }}</span>
                                        @else
                                            —
                                        @endif
                                    </td>
                                    <td><span class="vol-badge">{{ isset($d['search_volume']) ? number_format($d['search_volume']) : (isset($d['estimated_volume']) ? number_format($d['estimated_volume']) : '—') }}</span></td>
                                    <td>
                                        @if(isset($d['difficulty_score']))
                                            @php
                                                $diff = (int)$d['difficulty_score'];
                                                $cls = $diff < 40 ? 'easy' : ($diff < 70 ? 'medium' : 'hard');
                                            @endphp
                                            <div class="diff-wrap">
                                                <div class="diff-bar"><div class="diff-fill {{ $cls }}" style="width:{{ $diff }}%"></div></div>
                                                <span style="font-size:.78rem;">{{ $diff }}</span>
                                            </div>
                                        @else
                                            —
                                        @endif
                                    </td>
                                    <td><span style="color:var(--secondary);font-weight:600;">{{ isset($d['total_sales']) ? number_format($d['total_sales']) : '—' }}</span></td>
                                    <td><span style="color:var(--warning);font-weight:600;">{{ $d['sponsored_count'] ?? '—' }}</span></td>
                                    <td>
                                        @if(isset($d['avg_price']))
                                            @if($currency === 'EGP')
                                                {{ number_format($d['avg_price']) }} EGP
                                            @else
                                                ${{ number_format($d['avg_price']) }}
                                            @endif
                                        @else
                                            —
                                        @endif
                                    </td>
                                    <td>
                                        @if(isset($d['asin']))
                                            <a href="{{ $productUrl }}" target="_blank" class="asin-pill" style="text-decoration:none;">{{ $d['asin'] }}</a>
                                        @else
                                            —
                                        @endif
                                    </td>
                                @elseif($list->type === 'market_analysis')
                                    <td>
                                        @if(isset($d['products']) && count($d['products']) > 0)
                                            <button class="btn-keyword-details" style="background:none;border:none;color:var(--primary);font-weight:600;cursor:pointer;padding:0;font-family:inherit;text-decoration:underline;" onclick="showMarketAnalysisProducts({{ $item->id }})">
                                                {{ $d['keyword'] ?? '—' }}
                                            </button>
                                        @else
                                            <span class="keyword-pill">{{ $d['keyword'] ?? '—' }}</span>
                                        @endif
                                    </td>
                                    <td><span class="vol-badge">{{ isset($d['search_volume']) ? number_format($d['search_volume']) : '—' }}</span></td>
                                    <td>
                                        @if(isset($d['difficulty']))
                                            @php
                                                $diff = (int)$d['difficulty'];
                                                $cls = $diff < 40 ? 'easy' : ($diff < 70 ? 'medium' : 'hard');
                                            @endphp
                                            <div class="diff-wrap">
                                                <div class="diff-bar"><div class="diff-fill {{ $cls }}" style="width:{{ $diff }}%"></div></div>
                                                <span style="font-size:.78rem;">{{ $diff }}</span>
                                            </div>
                                        @else
                                            —
                                        @endif
                                    </td>
                                    <td><span style="color:var(--secondary);font-weight:600;">{{ isset($d['total_sales']) ? number_format($d['total_sales']) : '—' }}</span></td>
                                    <td>
                                        @if(isset($d['avg_revenue']))
                                            @if($currency === 'EGP')
                                                {{ number_format($d['avg_revenue']) }} EGP
                                            @else
                                                ${{ number_format($d['avg_revenue']) }}
                                            @endif
                                        @else
                                            —
                                        @endif
                                    </td>
                                    <td>
                                        @if(isset($d['avg_price']))
                                            @if($currency === 'EGP')
                                                {{ number_format($d['avg_price'], 2) }} EGP
                                            @else
                                                ${{ number_format($d['avg_price'], 2) }}
                                            @endif
                                        @else
                                            —
                                        @endif
                                    </td>
                                    <td>{{ isset($d['avg_bsr']) ? '#'.number_format($d['avg_bsr']) : '—' }}</td>
                                    <td>{{ isset($d['avg_reviews']) ? number_format($d['avg_reviews']) : '—' }}</td>
                                    <td>
                                        @if(isset($d['ads_density']))
                                            {{ $d['ads_density'] }}%
                                        @else
                                            —
                                        @endif
                                    </td>
                                @endif

                                <td class="action-col">
                                    <button class="row-delete-btn" onclick="deleteItem({{ $item->id }})" title="Remove this item">🗑</button>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            <!-- Pagination -->
            @if($items->hasPages())
                <div class="pagination">
                    {{ $items->links() }}
                </div>
            @endif

        @else
            <div class="empty-state">
                <div class="empty-state-icon">{{ \App\Models\DashboardList::TYPE_ICONS[$list->type] ?? '📋' }}</div>
                <h3>No items saved yet</h3>
                <p>Use the Chrome extension to save {{ $list->typeLabel() }} results directly into this list.</p>
            </div>
        @endif
    </div>

</main>

<!-- ── Delete List Modal ─────────────────────────────── -->
<div id="delete-list-modal" class="modal-overlay">
    <div class="modal">
        <div class="modal-header">
            <span class="modal-title">🗑️ Delete List</span>
            <button class="modal-close" onclick="closeModal('delete-list-modal')">×</button>
        </div>
        <p style="margin-bottom:1.5rem;line-height:1.6;color:var(--muted-light)">
            Delete "<strong>{{ $list->name }}</strong>"? All {{ $list->item_count }} saved items will be permanently removed.
        </p>
        <form method="POST" action="/lists/{{ $list->id }}">
            @csrf
            @method('DELETE')
            <div class="form-actions">
                <button type="button" class="btn btn-outline" onclick="closeModal('delete-list-modal')">Cancel</button>
                <button type="submit" class="btn btn-danger">Yes, Delete</button>
            </div>
        </form>
    </div>
</div>

<!-- ── Market Analysis Products Modal ────────────────── -->
<div id="ma-products-modal" class="modal-overlay" style="z-index: 1001;">
    <div class="modal" style="max-width: 90%; width: 1200px;">
        <div class="modal-header">
            <span class="modal-title" id="ma-modal-title">Products Analyzed</span>
            <button class="modal-close" onclick="closeModal('ma-products-modal')">×</button>
        </div>
        <div class="table-wrapper" style="max-height: 60vh; overflow-y: auto; margin-bottom: 1.5rem; border-radius: 8px; border: 1px solid var(--border);">
            <table class="table" style="font-size: 0.825rem; min-width: 1100px;">
                <thead>
                    <tr style="position: sticky; top: 0; background: var(--bg); z-index: 2;">
                        <th style="text-align: center; width: 50px;">#</th>
                        <th style="width: 100px;">ASIN</th>
                        <th>Product Details</th>
                        <th>Brand</th>
                        <th style="text-align: right;">Price</th>
                        <th style="text-align: right;">Sales / mo</th>
                        <th style="text-align: right;">Revenue / mo</th>
                        <th style="text-align: right;">BSR</th>
                        <th>Category</th>
                        <th style="text-align: center;">Sellers</th>
                        <th style="text-align: right;">Reviews</th>
                        <th style="text-align: center;">Rating</th>
                    </tr>
                </thead>
                <tbody id="ma-modal-tbody">
                    <!-- Dynamic rows via JavaScript -->
                </tbody>
            </table>
        </div>
        <div class="form-actions">
            <button type="button" class="btn btn-outline" onclick="closeModal('ma-products-modal')">Close</button>
        </div>
    </div>
</div>

<script>
const CSRF = document.querySelector('meta[name="csrf-token"]').content;

// Serialize market analysis products to client side
const marketAnalysisProductsMap = {
    @foreach($items as $item)
        @if($list->type === 'market_analysis' && isset($item->data['products']))
            "{{ $item->id }}": @json($item->data['products']),
        @endif
    @endforeach
};

function openModal(id)  { document.getElementById(id).classList.add('open'); }
function closeModal(id) { document.getElementById(id).classList.remove('open'); }
document.querySelectorAll('.modal-overlay').forEach(o => {
    o.addEventListener('click', e => { if (e.target === o) o.classList.remove('open'); });
});

// Show market analysis products modal
function showMarketAnalysisProducts(itemId) {
    const products = marketAnalysisProductsMap[itemId];
    const tbody = document.getElementById('ma-modal-tbody');
    tbody.innerHTML = '';
    
    // Find the keyword for this item
    const row = document.getElementById('row-' + itemId);
    const keywordText = row ? row.querySelector('.btn-keyword-details').textContent.trim() : 'Unknown';
    document.getElementById('ma-modal-title').textContent = `Products Analyzed: "${keywordText}"`;

    if (!products || products.length === 0) {
        tbody.innerHTML = '<tr><td colspan="12" style="text-align:center;padding:2rem;color:var(--muted)">No product data saved for this analysis.</td></tr>';
    } else {
        products.forEach((p, idx) => {
            const tr = document.createElement('tr');
            
            const priceVal = parseFloat(p.price) || 0;
            const priceFormatted = priceVal > 0 ? (p.currency === 'EGP' ? `${priceVal.toLocaleString(undefined, {minimumFractionDigits: 2, maximumFractionDigits: 2})} EGP` : `$${priceVal.toLocaleString(undefined, {minimumFractionDigits: 2, maximumFractionDigits: 2})}`) : '—';
            
            const salesVal = parseInt(p.monthly_sales) || 0;
            const salesFormatted = salesVal > 0 ? salesVal.toLocaleString() : '—';
            
            const revVal = parseFloat(p.revenue) || 0;
            const revFormatted = revVal > 0 ? (p.currency === 'EGP' ? `${revVal.toLocaleString(undefined, {maximumFractionDigits: 0})} EGP` : `$${revVal.toLocaleString(undefined, {maximumFractionDigits: 0})}`) : '—';
            
            const bsrVal = parseInt(p.bsr) || 0;
            const bsrFormatted = bsrVal > 0 ? `#${bsrVal.toLocaleString()}` : '—';
            
            const reviewsVal = parseInt(p.reviews) || 0;
            const reviewsFormatted = reviewsVal > 0 ? reviewsVal.toLocaleString() : '—';

            const ratingVal = parseFloat(p.rating) || 0;
            const ratingFormatted = ratingVal > 0 ? `⭐ ${ratingVal}` : '—';

            const titleEscaped = (p.title || 'Unknown').replace(/"/g, '&quot;');
            const productUrl = p.url || `https://www.amazon.com/dp/${p.asin}`;

            tr.innerHTML = `
                <td style="text-align: center; color: var(--muted); font-weight: 600;">${p.position || idx + 1}</td>
                <td><a href="${productUrl}" target="_blank" class="asin-pill" style="text-decoration:none;">${p.asin}</a></td>
                <td style="max-width:300px; overflow:hidden; text-overflow:ellipsis; white-space:nowrap;">
                    <div style="display:flex;align-items:center;gap:10px;">
                        <img src="${p.image || 'https://via.placeholder.com/40'}" alt="" style="width:36px;height:36px;object-fit:contain;border-radius:4px;border:1px solid var(--border);flex-shrink:0;background:#fff;" onerror="this.src='https://via.placeholder.com/40'"/>
                        <div style="overflow:hidden;text-overflow:ellipsis;white-space:nowrap;font-weight:500;">
                            <a href="${productUrl}" target="_blank" style="color:inherit;text-decoration:none;font-weight:600;" title="${titleEscaped}" onmouseover="this.style.color='var(--primary)'" onmouseout="this.style.color='inherit'">
                                ${p.title}
                            </a>
                            ${p.is_sponsored ? '<span style="display:inline-block;background:rgba(245,158,11,.1);color:#b45309;font-size:9px;padding:1px 3px;border-radius:4px;margin-left:5px;font-weight:600;">Ad</span>' : ''}
                        </div>
                    </div>
                </td>
                <td style="color:var(--muted-light);">${p.brand || '-'}</td>
                <td style="font-weight:700;color:var(--success);text-align:right;">${priceFormatted}</td>
                <td style="font-weight:700;color:var(--warning);text-align:right;">${salesFormatted}</td>
                <td style="font-weight:700;color:var(--primary);text-align:right;">${revFormatted}</td>
                <td style="text-align:right;">${bsrFormatted}</td>
                <td style="color:var(--muted-light);max-width:120px;overflow:hidden;text-overflow:ellipsis;white-space:nowrap;" title="${p.category || ''}">${p.category || '-'}</td>
                <td style="text-align:center;">${p.seller_count || 1}</td>
                <td style="text-align:right;">${reviewsFormatted}</td>
                <td style="text-align:center;">${ratingFormatted}</td>
            `;
            tbody.appendChild(tr);
        });
    }
    openModal('ma-products-modal');
}

// ── Selection ────────────────────────────────────────
function getChecked() {
    return [...document.querySelectorAll('.row-cb:checked')].map(c => parseInt(c.value));
}
function updateSelection() {
    const checked = getChecked();
    document.getElementById('selection-info').textContent = checked.length + ' selected';
    document.getElementById('remove-selected-btn').disabled = checked.length === 0;

    const all = document.querySelectorAll('.row-cb');
    document.getElementById('select-all-cb').checked = all.length > 0 && checked.length === all.length;
    document.getElementById('thead-cb').checked     = all.length > 0 && checked.length === all.length;

    document.querySelectorAll('.row-cb').forEach(cb => {
        cb.closest('tr').classList.toggle('selected-row', cb.checked);
    });
}
function toggleAll(masterCb) {
    document.querySelectorAll('.row-cb').forEach(cb => { cb.checked = masterCb.checked; });
    // sync the twin checkbox
    ['select-all-cb', 'thead-cb'].forEach(id => {
        const el = document.getElementById(id);
        if (el && el !== masterCb) el.checked = masterCb.checked;
    });
    updateSelection();
}

// ── Delete single item ────────────────────────────────
function deleteItem(itemId) {
    if (!confirm('Remove this item?')) return;
    fetch('/lists/{{ $list->id }}/items/' + itemId, {
        method: 'DELETE',
        headers: { 'X-CSRF-TOKEN': CSRF, 'Accept': 'application/json' }
    }).then(r => r.json()).then(data => {
        if (data.success) {
            document.getElementById('row-' + itemId)?.remove();
            updateItemCount();
        }
    });
}

// ── Remove selected (bulk) ─────────────────────────────
function removeSelected() {
    const ids = getChecked();
    if (!ids.length) return;
    if (!confirm('Remove ' + ids.length + ' selected item(s)?')) return;
    fetch('/lists/{{ $list->id }}/items', {
        method: 'DELETE',
        headers: { 'X-CSRF-TOKEN': CSRF, 'Content-Type': 'application/json', 'Accept': 'application/json' },
        body: JSON.stringify({ item_ids: ids })
    }).then(r => r.json()).then(data => {
        if (data.success) {
            ids.forEach(id => document.getElementById('row-' + id)?.remove());
            updateSelection();
            updateItemCount();
        }
    });
}

function updateItemCount() {
    const remaining = document.querySelectorAll('tbody tr').length;
    document.querySelector('.item-count-badge').textContent = remaining + ' item' + (remaining !== 1 ? 's' : '');
    if (remaining === 0) location.reload();
}
</script>

</body>
</html>
