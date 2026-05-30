<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $analysis->name ?? 'Analysis Results' }} - Competitor Keyword Analyzer</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            font-family: 'Inter', -apple-system, system-ui, sans-serif;
            background: #0f172a;
            color: #e5e7eb;
            min-height: 100vh;
        }
        
        .nav {
            background: #1e293b;
            border-bottom: 1px solid #374151;
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
            color: #fff;
        }
        
        .nav-links {
            display: flex;
            gap: 24px;
        }
        
        .nav-links a {
            color: #9ca3af;
            text-decoration: none;
            font-size: 14px;
            font-weight: 500;
            transition: color 0.2s;
        }
        
        .nav-links a:hover {
            color: #6366f1;
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
            color: #6366f1;
            text-decoration: none;
        }
        
        .breadcrumb span {
            color: #6b7280;
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
            color: #fff;
        }
        
        .page-subtitle {
            color: #9ca3af;
            font-size: 14px;
            margin-top: 4px;
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
            background: linear-gradient(135deg, #6366f1, #4f46e5);
            color: white;
        }
        
        .btn-secondary {
            background: #374151;
            color: #e5e7eb;
        }
        
        .btn-success {
            background: #10b981;
            color: white;
        }
        
        /* Analysis Info Card */
        .info-card {
            background: #1e293b;
            border: 1px solid #374151;
            border-radius: 12px;
            padding: 20px;
            margin-bottom: 24px;
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
            color: #9ca3af;
            text-transform: uppercase;
            font-weight: 600;
            letter-spacing: 0.5px;
            margin-bottom: 6px;
        }
        
        .info-value {
            font-size: 22px;
            font-weight: 700;
        }
        
        .info-value.purple { color: #a78bfa; }
        .info-value.blue { color: #60a5fa; }
        .info-value.green { color: #10b981; }
        .info-value.orange { color: #f59e0b; }
        .info-value.cyan { color: #22d3ee; }
        
        /* ASINs Section */
        .asins-section {
            background: #1e293b;
            border: 1px solid #374151;
            border-radius: 12px;
            padding: 20px;
            margin-bottom: 24px;
        }
        
        .asins-title {
            font-size: 14px;
            font-weight: 600;
            color: #fff;
            margin-bottom: 12px;
        }
        
        .asins-grid {
            display: flex;
            flex-wrap: wrap;
            gap: 10px;
        }
        
        .asin-card {
            background: #0f172a;
            border: 1px solid #374151;
            border-radius: 8px;
            padding: 12px 16px;
            display: flex;
            align-items: center;
            gap: 10px;
        }
        
        .asin-number {
            background: linear-gradient(135deg, #6366f1, #0ea5e9);
            color: white;
            width: 24px;
            height: 24px;
            border-radius: 6px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 11px;
            font-weight: 700;
        }
        
        .asin-code {
            font-family: monospace;
            font-size: 13px;
            color: #e5e7eb;
            font-weight: 600;
        }
        
        .asin-link {
            color: #6366f1;
            text-decoration: none;
            font-size: 11px;
        }
        
        /* Filters */
        .filters-bar {
            background: #1e293b;
            border: 1px solid #374151;
            border-radius: 12px;
            padding: 16px 20px;
            margin-bottom: 24px;
            display: flex;
            align-items: center;
            gap: 16px;
            flex-wrap: wrap;
        }
        
        .filter-label {
            color: #9ca3af;
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
            background: #374151;
            color: #e5e7eb;
            transition: all 0.2s;
        }
        
        .filter-btn:hover, .filter-btn.active {
            background: #6366f1;
            color: white;
        }
        
        .filter-input {
            padding: 8px 12px;
            border-radius: 6px;
            border: 1px solid #374151;
            background: #0f172a;
            color: #e5e7eb;
            font-size: 13px;
            width: 150px;
        }
        
        .filter-input::placeholder {
            color: #6b7280;
        }
        
        /* Keywords Table */
        .keywords-table-container {
            background: #1e293b;
            border: 1px solid #374151;
            border-radius: 12px;
            overflow: hidden;
        }
        
        .table-header {
            background: #0f172a;
            padding: 16px 20px;
            border-bottom: 1px solid #374151;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        
        .table-header h2 {
            font-size: 16px;
            font-weight: 600;
            color: #fff;
        }
        
        .keywords-table {
            width: 100%;
            border-collapse: collapse;
            font-size: 13px;
        }
        
        .keywords-table th {
            background: #0f172a;
            padding: 12px 14px;
            text-align: left;
            font-size: 10px;
            text-transform: uppercase;
            color: #9ca3af;
            font-weight: 600;
            letter-spacing: 0.5px;
            border-bottom: 2px solid #374151;
            position: sticky;
            top: 0;
            cursor: pointer;
        }
        
        .keywords-table th:hover {
            color: #6366f1;
        }
        
        .keywords-table td {
            padding: 12px 14px;
            border-bottom: 1px solid #374151;
        }
        
        .keywords-table tr:hover {
            background: #0f172a;
        }
        
        .keyword-text {
            font-weight: 600;
            color: #fff;
        }
        
        .iq-score {
            font-weight: 700;
            padding: 4px 10px;
            border-radius: 4px;
        }
        
        .iq-excellent { background: #10b98120; color: #10b981; }
        .iq-good { background: #f59e0b20; color: #f59e0b; }
        .iq-moderate { background: #fb923c20; color: #fb923c; }
        .iq-poor { background: #ef444420; color: #ef4444; }
        
        .rank-cell {
            text-align: center;
            font-weight: 600;
            font-size: 12px;
        }
        
        .rank-value {
            color: #10b981;
        }
        
        .rank-none {
            color: #374151;
        }
        
        .ranking-badge {
            background: #10b98120;
            color: #10b981;
            padding: 4px 8px;
            border-radius: 4px;
            font-size: 11px;
            font-weight: 600;
        }
        
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
            background: #374151;
            color: #e5e7eb;
            border-radius: 6px;
            text-decoration: none;
            font-size: 13px;
        }
        
        .pagination a:hover, .pagination span.current {
            background: #6366f1;
        }
        
        .copy-btn {
            background: none;
            border: none;
            color: #6b7280;
            cursor: pointer;
            font-size: 14px;
            padding: 4px;
        }
        
        .copy-btn:hover {
            color: #6366f1;
        }
    </style>
</head>
<body>
    <nav class="nav">
        <div class="nav-logo">
            <span>🧠</span>
            <span>Competitor Keyword Analyzer</span>
        </div>
        <div class="nav-links">
            <a href="{{ route('dashboard') }}">Dashboard</a>
            <a href="{{ route('cerebro.index') }}">Keyword Analyzer</a>
            <a href="#">Settings</a>
            <a href="{{ route('logout') }}">Logout</a>
        </div>
    </nav>
    
    <div class="container">
        <!-- Breadcrumb -->
        <div class="breadcrumb">
            <a href="{{ route('cerebro.index') }}">Keyword Analyzer</a>
            <span>/</span>
            <span>{{ $analysis->name ?? 'Analysis #' . $analysis->id }}</span>
        </div>
        
        <!-- Page Header -->
        <div class="page-header">
            <div>
                <h1 class="page-title">{{ $analysis->name ?? 'Analysis Results' }}</h1>
                <p class="page-subtitle">Created {{ \Carbon\Carbon::parse($analysis->created_at)->format('M d, Y \a\t h:i A') }}</p>
            </div>
            <div class="header-actions">
                <a href="{{ route('cerebro.export', $analysis->id) }}" class="btn btn-success">
                    📥 Export CSV
                </a>
                <a href="{{ route('cerebro.index') }}" class="btn btn-secondary">
                    ← Back to List
                </a>
            </div>
        </div>
        
        <!-- Analysis Summary -->
        <div class="info-card">
            <div class="info-grid">
                <div class="info-item">
                    <div class="info-label">Marketplace</div>
                    <div class="info-value purple">{{ strtoupper(str_replace('www.', '', str_replace('amazon.', '', $analysis->marketplace))) }}</div>
                </div>
                <div class="info-item">
                    <div class="info-label">ASINs Analyzed</div>
                    <div class="info-value blue">{{ $analysis->asin_count }}</div>
                </div>
                <div class="info-item">
                    <div class="info-label">Keywords Found</div>
                    <div class="info-value green">{{ number_format($analysis->total_keywords) }}</div>
                </div>
                <div class="info-item">
                    <div class="info-label">Avg IQ Score</div>
                    <div class="info-value orange">{{ number_format($stats['avg_iq'] ?? 0, 1) }}</div>
                </div>
                <div class="info-item">
                    <div class="info-label">Avg Volume</div>
                    <div class="info-value cyan">{{ number_format($stats['avg_volume'] ?? 0) }}</div>
                </div>
                <div class="info-item">
                    <div class="info-label">Duration</div>
                    <div class="info-value">{{ $analysis->duration_seconds ?? 0 }}s</div>
                </div>
            </div>
        </div>
        
        <!-- Analyzed ASINs -->
        <div class="asins-section">
            <div class="asins-title">📦 Analyzed Products ({{ $analysis->asin_count }})</div>
            <div class="asins-grid">
                @php $asins = is_array($analysis->asins) ? $analysis->asins : json_decode($analysis->asins, true); @endphp
                @foreach($asins ?? [] as $index => $asin)
                <div class="asin-card">
                    <div class="asin-number">{{ $index + 1 }}</div>
                    <div>
                        <div class="asin-code">{{ $asin }}</div>
                        <a href="https://{{ $analysis->marketplace }}/dp/{{ $asin }}" target="_blank" class="asin-link">View on Amazon →</a>
                    </div>
                </div>
                @endforeach
            </div>
        </div>
        
        <!-- Filters -->
        <div class="filters-bar">
            <span class="filter-label">Quick Filters:</span>
            <a href="{{ route('cerebro.show', $analysis->id) }}" class="filter-btn {{ !request('filter') ? 'active' : '' }}">All ({{ $analysis->total_keywords }})</a>
            <a href="{{ route('cerebro.show', ['id' => $analysis->id, 'filter' => 'top']) }}" class="filter-btn {{ request('filter') == 'top' ? 'active' : '' }}">🔥 Top</a>
            <a href="{{ route('cerebro.show', ['id' => $analysis->id, 'filter' => 'opportunity']) }}" class="filter-btn {{ request('filter') == 'opportunity' ? 'active' : '' }}">💎 Opportunity</a>
            <a href="{{ route('cerebro.show', ['id' => $analysis->id, 'filter' => 'low_comp']) }}" class="filter-btn {{ request('filter') == 'low_comp' ? 'active' : '' }}">✅ Low Competition</a>
            <div style="flex: 1;"></div>
            <form method="GET" action="{{ route('cerebro.show', $analysis->id) }}" style="display: flex; gap: 8px;">
                <input type="text" name="search" class="filter-input" placeholder="Search keyword..." value="{{ request('search') }}">
                <button type="submit" class="btn btn-primary" style="padding: 8px 16px;">Search</button>
            </form>
        </div>
        
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
                            <th><a href="{{ route('cerebro.show', ['id' => $analysis->id, 'sort' => 'search_volume', 'dir' => request('dir') == 'desc' ? 'asc' : 'desc']) }}" style="color: inherit; text-decoration: none;">Volume ↕</a></th>
                            <th><a href="{{ route('cerebro.show', ['id' => $analysis->id, 'sort' => 'cerebro_iq_score', 'dir' => request('dir') == 'desc' ? 'asc' : 'desc']) }}" style="color: inherit; text-decoration: none;">IQ Score ↕</a></th>
                            <th>CPR</th>
                            <th>Words</th>
                            <th>Ranking</th>
                            @foreach($asins ?? [] as $index => $asin)
                            <th class="rank-cell" title="{{ $asin }}">#{{ $index + 1 }}</th>
                            @endforeach
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($keywords as $kw)
                        @php
                            $organicRanks = is_array($kw->organic_ranks) ? $kw->organic_ranks : json_decode($kw->organic_ranks, true);
                            $iqClass = $kw->cerebro_iq_score >= 5 ? 'iq-excellent' : ($kw->cerebro_iq_score >= 3 ? 'iq-good' : ($kw->cerebro_iq_score >= 1 ? 'iq-moderate' : 'iq-poor'));
                        @endphp
                        <tr>
                            <td>
                                <span class="keyword-text">{{ $kw->keyword }}</span>
                                <button class="copy-btn" onclick="navigator.clipboard.writeText('{{ $kw->keyword }}')" title="Copy keyword">📋</button>
                            </td>
                            <td style="color: #60a5fa; font-weight: 600;">{{ number_format($kw->search_volume) }}</td>
                            <td><span class="iq-score {{ $iqClass }}">{{ number_format($kw->cerebro_iq_score, 1) }}</span></td>
                            <td style="color: #f59e0b;">{{ $kw->cpr_8day }}</td>
                            <td style="color: #9ca3af;">{{ $kw->word_count }}</td>
                            <td>
                                <span class="ranking-badge">{{ $kw->asins_ranking }}/{{ count($asins ?? []) }}</span>
                            </td>
                            @foreach($asins ?? [] as $asin)
                            <td class="rank-cell">
                                @if(isset($organicRanks[$asin]) && $organicRanks[$asin])
                                <span class="rank-value">#{{ $organicRanks[$asin] }}</span>
                                @else
                                <span class="rank-none">-</span>
                                @endif
                            </td>
                            @endforeach
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            
            @if($keywords->hasPages())
            <div class="pagination">
                {{ $keywords->appends(request()->query())->links() }}
            </div>
            @endif
        </div>
    </div>
</body>
</html>
