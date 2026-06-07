<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $analysis->seed_keyword }} - Keyword Magnet Results</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            font-family: 'Inter', -apple-system, system-ui, sans-serif;
            background: #f8fafc;
            color: #0f172a;
            min-height: 100vh;
        }
        
        .nav {
            background: #ffffff;
            border-bottom: 1px solid #e2e8f0;
            padding: 16px 24px;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        
        .nav-logo {
            display: flex;
            align-items: center;
            gap: 12px;
            font-size: 18px;
            font-weight: 700;
            color: #0f172a;
        }
        
        .nav-links {
            display: flex;
            gap: 24px;
        }
        
        .nav-links a {
            color: #475569;
            text-decoration: none;
            font-size: 14px;
            font-weight: 500;
            transition: color 0.2s;
        }
        
        .nav-links a:hover {
            color: #d97706;
        }
        
        .container {
            max-width: 1600px;
            margin: 0 auto;
            padding: 24px;
        }
        
        .breadcrumb {
            display: flex;
            align-items: center;
            gap: 8px;
            margin-bottom: 16px;
            font-size: 14px;
        }
        
        .breadcrumb a {
            color: #d97706;
            text-decoration: none;
        }
        
        .breadcrumb span {
            color: #64748b;
        }
        
        .page-header {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            margin-bottom: 24px;
        }
        
        .page-title {
            font-size: 24px;
            font-weight: 700;
            color: #0f172a;
        }
        
        .page-subtitle {
            color: #475569;
            font-size: 14px;
            margin-top: 4px;
        }
        
        .seed-highlight {
            color: #d97706;
            background: rgba(245, 158, 11, 0.1);
            padding: 2px 8px;
            border-radius: 4px;
        }
        
        .header-actions {
            display: flex;
            gap: 12px;
        }
        
        .btn {
            padding: 10px 20px;
            border-radius: 8px;
            font-size: 14px;
            font-weight: 600;
            cursor: pointer;
            border: none;
            transition: all 0.2s;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            gap: 8px;
        }
        
        .btn-primary {
            background: linear-gradient(135deg, #f59e0b, #d97706);
            color: white;
        }
        
        .btn-secondary {
            background: #ffffff;
            border: 1px solid #e2e8f0;
            color: #475569;
        }
        .btn-secondary:hover {
            background: #f1f5f9;
            color: #0f172a;
        }
        
        .btn-success {
            background: #10b981;
            color: white;
        }
        
        /* Analysis Info Card */
        .info-card {
            background: #ffffff;
            border: 1px solid #e2e8f0;
            border-radius: 12px;
            padding: 20px;
            margin-bottom: 24px;
            box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.05), 0 2px 4px -1px rgba(0, 0, 0, 0.03);
        }
        
        .info-grid {
            display: grid;
            grid-template-columns: repeat(6, 1fr);
            gap: 24px;
        }
        
        .info-item {
            text-align: center;
        }
        
        .info-label {
            font-size: 11px;
            color: #64748b;
            text-transform: uppercase;
            font-weight: 600;
            letter-spacing: 0.5px;
            margin-bottom: 6px;
        }
        
        .info-value {
            font-size: 22px;
            font-weight: 700;
            color: #0f172a;
        }
        
        .info-value.amber { color: #d97706; }
        .info-value.blue { color: #2563eb; }
        .info-value.green { color: #10b981; }
        .info-value.orange { color: #d97706; }
        .info-value.cyan { color: #0ea5e9; }
        
        /* Filters */
        .filters-bar {
            background: #ffffff;
            border: 1px solid #e2e8f0;
            border-radius: 12px;
            padding: 16px 20px;
            margin-bottom: 24px;
            display: flex;
            align-items: center;
            gap: 16px;
            flex-wrap: wrap;
            box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.05);
        }
        
        .filter-label {
            color: #475569;
            font-size: 13px;
            font-weight: 600;
        }
        
        .filter-btn {
            padding: 8px 14px;
            border-radius: 6px;
            font-size: 12px;
            font-weight: 500;
            cursor: pointer;
            border: none;
            background: #f1f5f9;
            color: #475569;
            transition: all 0.2s;
            text-decoration: none;
        }
        
        .filter-btn:hover, .filter-btn.active {
            background: #f59e0b;
            color: white;
        }
        
        .filter-input {
            padding: 8px 12px;
            border-radius: 6px;
            border: 1px solid #e2e8f0;
            background: #ffffff;
            color: #0f172a;
            font-size: 13px;
            width: 150px;
        }
        
        .filter-input::placeholder {
            color: #94a3b8;
        }
        
        .filter-select {
            padding: 8px 12px;
            border-radius: 6px;
            border: 1px solid #e2e8f0;
            background: #ffffff;
            color: #0f172a;
            font-size: 13px;
            cursor: pointer;
        }
        
        /* Keywords Table */
        .keywords-table-container {
            background: #ffffff;
            border: 1px solid #e2e8f0;
            border-radius: 12px;
            overflow: hidden;
            box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.05);
        }
        
        .table-header {
            background: #f8fafc;
            padding: 16px 20px;
            border-bottom: 1px solid #e2e8f0;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        
        .table-header h2 {
            font-size: 16px;
            font-weight: 600;
            color: #0f172a;
        }
        
        .keywords-table {
            width: 100%;
            border-collapse: collapse;
            font-size: 13px;
        }
        
        .keywords-table th {
            background: #f8fafc;
            padding: 12px 14px;
            text-align: left;
            font-size: 10px;
            text-transform: uppercase;
            color: #475569;
            font-weight: 600;
            letter-spacing: 0.5px;
            border-bottom: 2px solid #e2e8f0;
            position: sticky;
            top: 0;
        }
        
        .keywords-table th a {
            color: inherit;
            text-decoration: none;
        }
        
        .keywords-table th:hover {
            color: #f59e0b;
        }
        
        .keywords-table td {
            padding: 12px 14px;
            border-bottom: 1px solid #e2e8f0;
            color: #0f172a;
        }
        
        .keywords-table tr:hover {
            background: #f8fafc;
        }
        
        .keyword-text {
            font-weight: 600;
            color: #0f172a;
        }
        
        .iq-score {
            font-weight: 700;
            padding: 4px 10px;
            border-radius: 4px;
        }
        
        .iq-excellent { background: rgba(16, 185, 129, 0.1); color: #10b981; }
        .iq-good { background: rgba(245, 158, 11, 0.1); color: #f59e0b; }
        .iq-moderate { background: rgba(251, 146, 60, 0.1); color: #fb923c; }
        .iq-poor { background: rgba(239, 68, 68, 0.1); color: #ef4444; }
        
        .match-badge {
            padding: 4px 8px;
            border-radius: 4px;
            font-size: 10px;
            font-weight: 600;
            text-transform: uppercase;
        }
        
        .match-seed { background: rgba(245, 158, 11, 0.1); color: #d97706; }
        .match-autocomplete { background: rgba(99, 102, 241, 0.1); color: #4f46e5; }
        .match-related { background: rgba(16, 185, 129, 0.1); color: #10b981; }
        .match-title { background: rgba(96, 165, 250, 0.1); color: #2563eb; }
        .match-suggestion { background: rgba(244, 114, 182, 0.1); color: #db2777; }
        .match-attribute { background: rgba(20, 184, 166, 0.1); color: #0d9488; }
        .match-google { background: rgba(234, 67, 53, 0.1); color: #dc2626; }
        .match-bing { background: rgba(0, 120, 212, 0.1); color: #0284c7; }
        .match-youtube { background: rgba(255, 0, 0, 0.1); color: #dc2626; }
        
        .table-scroll {
            max-height: 600px;
            overflow: auto;
        }
        
        .pagination {
            display: flex;
            justify-content: center;
            gap: 8px;
            padding: 20px;
        }
        
        .pagination a, .pagination span {
            padding: 8px 14px;
            background: #ffffff;
            border: 1px solid #e2e8f0;
            color: #475569;
            border-radius: 6px;
            text-decoration: none;
            font-size: 13px;
        }
        
        .pagination a:hover, .pagination span.current {
            background: #f59e0b;
            color: white;
            border-color: #f59e0b;
        }
 
        .pagination span.disabled {
            opacity: 0.5;
            cursor: not-allowed;
            pointer-events: none;
        }
        
        .copy-btn {
            background: none;
            border: none;
            color: #94a3b8;
            cursor: pointer;
            font-size: 14px;
            padding: 4px;
        }
        
        .copy-btn:hover {
            color: #f59e0b;
        }
        
        .currency {
            color: #64748b;
            font-size: 11px;
        }
    </style>
</head>
<body>
    <nav class="nav">
        <div class="nav-logo">
            <span>🧲</span>
            <span>Keyword Magnet</span>
        </div>
        <div class="nav-links">
            <a href="{{ route('dashboard') }}">Dashboard</a>
            <a href="{{ route('cerebro.folders') }}">Folders</a>
            <a href="/settings">Settings</a>
        </div>
    </nav>
    
    <div class="container">
        <!-- Breadcrumb -->
        <div class="breadcrumb">
            <a href="{{ route('cerebro.folders') }}">Folders</a>
            <span>/</span>
            <span>{{ $analysis->seed_keyword }}</span>
        </div>
        
        <!-- Page Header -->
        <div class="page-header">
            <div>
                <h1 class="page-title">
                    🔍 Keywords for <span class="seed-highlight">{{ $analysis->seed_keyword }}</span>
                </h1>
                <p class="page-subtitle">
                    {{ $marketplace['flag'] ?? '🌐' }} {{ $marketplace['name'] ?? $analysis->marketplace }} • 
                    Created {{ \Carbon\Carbon::parse($analysis->created_at)->format('M d, Y \a\t h:i A') }}
                </p>
            </div>
            <div class="header-actions">
                <a href="{{ route('cerebro.folders') }}" class="btn btn-secondary">
                    ← Back to Folders
                </a>
            </div>
        </div>
        
        <!-- Analysis Summary -->
        <div class="info-card">
            <div class="info-grid">
                <div class="info-item">
                    <div class="info-label">Marketplace</div>
                    <div class="info-value amber">{{ $marketplace['flag'] ?? '🌐' }} {{ strtoupper(str_replace('amazon.', '', $analysis->marketplace)) }}</div>
                </div>
                <div class="info-item">
                    <div class="info-label">Keywords Found</div>
                    <div class="info-value green">{{ number_format($analysis->total_keywords) }}</div>
                </div>
                <div class="info-item">
                    <div class="info-label">Avg Volume</div>
                    <div class="info-value blue">{{ number_format($stats->avg_volume ?? 0) }}</div>
                </div>
                <div class="info-item">
                    <div class="info-label">Top IQ Score</div>
                    <div class="info-value orange">{{ number_format($stats->top_iq_score ?? 0, 1) }}</div>
                </div>
                <div class="info-item">
                    <div class="info-label">Total Sales</div>
                    <div class="info-value cyan">{{ number_format($stats->total_sales ?? 0) }}</div>
                </div>
                <div class="info-item">
                    <div class="info-label">Avg Price</div>
                    <div class="info-value">{{ $marketplace['currency'] ?? 'USD' }} {{ number_format($stats->avg_price ?? 0, 2) }}</div>
                </div>
            </div>
        </div>
        
        <!-- Quick Filters -->
        <div class="filters-bar">
            <span class="filter-label">Quick Filters:</span>
            <a href="{{ route('magnet.show', $analysis->id) }}" class="filter-btn {{ !request('quick_filter') ? 'active' : '' }}">All ({{ $analysis->total_keywords }})</a>
            <a href="{{ route('magnet.show', ['id' => $analysis->id, 'quick_filter' => 'high_volume']) }}" class="filter-btn {{ request('quick_filter') == 'high_volume' ? 'active' : '' }}">🔥 High Volume</a>
            <a href="{{ route('magnet.show', ['id' => $analysis->id, 'quick_filter' => 'low_competition']) }}" class="filter-btn {{ request('quick_filter') == 'low_competition' ? 'active' : '' }}">✅ Low Competition</a>
            <a href="{{ route('magnet.show', ['id' => $analysis->id, 'quick_filter' => 'long_tail']) }}" class="filter-btn {{ request('quick_filter') == 'long_tail' ? 'active' : '' }}">📝 Long Tail</a>
        </div>
        
        <!-- Advanced Filters (Helium 10 Style) -->
        <form method="GET" action="{{ route('magnet.show', $analysis->id) }}" id="advancedFilters">
            <div class="filters-bar" style="flex-direction: column; align-items: stretch;">
                <div style="display: flex; align-items: center; gap: 16px; margin-bottom: 12px;">
                    <span class="filter-label" style="font-size: 14px;">🔧 Advanced Filters</span>
                    <button type="button" onclick="toggleAdvancedFilters()" class="filter-btn" id="toggleFiltersBtn">
                        {{ request()->hasAny(['volume_min', 'volume_max', 'iq_min', 'iq_max', 'words_min', 'words_max', 'competing_max', 'cpr_max', 'sales_min', 'exclude_phrase']) ? '▼ Hide' : '▶ Show' }}
                    </button>
                    @if(request()->hasAny(['volume_min', 'volume_max', 'iq_min', 'iq_max', 'words_min', 'words_max', 'competing_max', 'cpr_max', 'sales_min', 'include_phrase', 'exclude_phrase']))
                    <a href="{{ route('magnet.show', $analysis->id) }}" class="filter-btn" style="background: #ef444420; color: #ef4444;">✕ Clear All</a>
                    @endif
                </div>
                
                <div id="advancedFiltersPanel" style="display: {{ request()->hasAny(['volume_min', 'volume_max', 'iq_min', 'iq_max', 'words_min', 'words_max', 'competing_max', 'cpr_max', 'sales_min', 'exclude_phrase']) ? 'block' : 'none' }};">
                    <!-- Row 1: Volume & IQ Score -->
                    <div style="display: grid; grid-template-columns: repeat(3, 1fr); gap: 16px; margin-bottom: 16px;">
                        <div>
                            <label class="filter-label" style="display: block; margin-bottom: 6px;">Search Volume</label>
                            <div style="display: flex; gap: 8px;">
                                <input type="number" name="volume_min" class="filter-input" style="width: 100%;" placeholder="Min" value="{{ request('volume_min') }}">
                                <input type="number" name="volume_max" class="filter-input" style="width: 100%;" placeholder="Max" value="{{ request('volume_max') }}">
                            </div>
                        </div>
                        <div>
                            <label class="filter-label" style="display: block; margin-bottom: 6px;">Magnet IQ Score</label>
                            <div style="display: flex; gap: 8px;">
                                <input type="number" name="iq_min" class="filter-input" style="width: 100%;" placeholder="Min" value="{{ request('iq_min') }}" step="0.1">
                                <input type="number" name="iq_max" class="filter-input" style="width: 100%;" placeholder="Max" value="{{ request('iq_max') }}" step="0.1">
                            </div>
                        </div>
                        <div>
                            <label class="filter-label" style="display: block; margin-bottom: 6px;">Word Count</label>
                            <div style="display: flex; gap: 8px;">
                                <input type="number" name="words_min" class="filter-input" style="width: 100%;" placeholder="Min" value="{{ request('words_min') }}">
                                <input type="number" name="words_max" class="filter-input" style="width: 100%;" placeholder="Max" value="{{ request('words_max') }}">
                            </div>
                        </div>
                    </div>
                    
                    <!-- Row 2: Competition, Sales, CPR -->
                    <div style="display: grid; grid-template-columns: repeat(4, 1fr); gap: 16px; margin-bottom: 16px;">
                        <div>
                            <label class="filter-label" style="display: block; margin-bottom: 6px;">Competing Products (Max)</label>
                            <input type="number" name="competing_max" class="filter-input" style="width: 100%;" placeholder="e.g. 50000" value="{{ request('competing_max') }}">
                        </div>
                        <div>
                            <label class="filter-label" style="display: block; margin-bottom: 6px;">Keyword Sales (Min)</label>
                            <input type="number" name="sales_min" class="filter-input" style="width: 100%;" placeholder="e.g. 100" value="{{ request('sales_min') }}">
                        </div>
                        <div>
                            <label class="filter-label" style="display: block; margin-bottom: 6px;">CPR 8-Day (Max)</label>
                            <input type="number" name="cpr_max" class="filter-input" style="width: 100%;" placeholder="e.g. 50" value="{{ request('cpr_max') }}">
                        </div>
                        <div>
                            <label class="filter-label" style="display: block; margin-bottom: 6px;">Avg Price (Min)</label>
                            <input type="number" name="price_min" class="filter-input" style="width: 100%;" placeholder="e.g. 10" value="{{ request('price_min') }}" step="0.01">
                        </div>
                    </div>
                    
                    <!-- Row 3: Phrase Filters -->
                    <div style="display: grid; grid-template-columns: 1fr 1fr 150px; gap: 16px; margin-bottom: 16px;">
                        <div>
                            <label class="filter-label" style="display: block; margin-bottom: 6px;">Include Phrase (comma-separated)</label>
                            <input type="text" name="include_phrase" class="filter-input" style="width: 100%;" placeholder="e.g. wireless, bluetooth" value="{{ request('include_phrase') }}">
                        </div>
                        <div>
                            <label class="filter-label" style="display: block; margin-bottom: 6px;">Exclude Phrase (comma-separated)</label>
                            <input type="text" name="exclude_phrase" class="filter-input" style="width: 100%;" placeholder="e.g. used, refurbished" value="{{ request('exclude_phrase') }}">
                        </div>
                        <div style="display: flex; align-items: flex-end;">
                            <button type="submit" class="btn btn-primary" style="width: 100%;">🔍 Apply Filters</button>
                        </div>
                    </div>
                </div>
            </div>
        </form>
        
        <script>
            function toggleAdvancedFilters() {
                const panel = document.getElementById('advancedFiltersPanel');
                const btn = document.getElementById('toggleFiltersBtn');
                if (panel.style.display === 'none') {
                    panel.style.display = 'block';
                    btn.textContent = '▼ Hide';
                } else {
                    panel.style.display = 'none';
                    btn.textContent = '▶ Show';
                }
            }
        </script>
        
        <!-- Keywords Table -->
        <div class="keywords-table-container">
            <div class="table-header">
                <h2>🔑 Keywords ({{ $keywords->total() }})</h2>
                <span style="color: #6b7280; font-size: 12px;">Click column header to sort</span>
            </div>
            
            <div class="table-scroll">
                <table class="keywords-table">
                    <thead>
                        <tr>
                            <th>Keyword</th>
                            <th><a href="{{ route('magnet.show', array_merge(['id' => $analysis->id], request()->except(['sort', 'dir']), ['sort' => 'search_volume', 'dir' => request('dir') == 'desc' ? 'asc' : 'desc'])) }}">Volume ↕</a></th>
                            <th><a href="{{ route('magnet.show', array_merge(['id' => $analysis->id], request()->except(['sort', 'dir']), ['sort' => 'magnet_iq_score', 'dir' => request('dir') == 'desc' ? 'asc' : 'desc'])) }}">IQ Score ↕</a></th>
                            <th>Est. Daily Sales</th>
                            <th>Sales</th>
                            <th>Avg Price</th>
                            <th>Words</th>
                            <th>Type</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($keywords as $kw)
                        @php
                            $iqClass = $kw->magnet_iq_score >= 5 ? 'iq-excellent' : ($kw->magnet_iq_score >= 3 ? 'iq-good' : ($kw->magnet_iq_score >= 1 ? 'iq-moderate' : 'iq-poor'));
                        @endphp
                        <tr>
                            <td>
                                <span class="keyword-text">{{ $kw->keyword }}</span>
                                <button class="copy-btn" onclick="navigator.clipboard.writeText('{{ $kw->keyword }}')" title="Copy keyword">📋</button>
                            </td>
                            <td style="color: #60a5fa; font-weight: 600;">{{ number_format($kw->search_volume) }}</td>
                            <td><span class="iq-score {{ $iqClass }}">{{ number_format($kw->magnet_iq_score, 1) }}</span></td>
                            <td style="color: #f59e0b;">{{ $kw->cpr_8day }}/day</td>
                            <td style="color: #10b981;">{{ number_format($kw->keyword_sales) }}</td>
                            <td>
                                <span class="currency">{{ $marketplace['currency'] ?? 'USD' }}</span>
                                {{ number_format($kw->avg_price, 2) }}
                            </td>
                            <td style="color: #9ca3af;">{{ $kw->word_count }}</td>
                            <td>
                                <span class="match-badge match-{{ $kw->match_type }}">{{ $kw->match_type }}</span>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            
            @if($keywords->hasPages())
            <div class="pagination">
                {{ $keywords->appends(request()->query())->links('magnet.pagination') }}
            </div>
            @endif
        </div>
    </div>
</body>
</html>
