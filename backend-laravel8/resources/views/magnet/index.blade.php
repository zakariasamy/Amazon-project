<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Keyword Magnet - Dashboard</title>
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
        
        .nav-links a:hover, .nav-links a.active {
            color: #f59e0b;
        }
        
        .container {
            max-width: 1400px;
            margin: 0 auto;
            padding: 24px;
        }
        
        .page-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
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
        
        .btn {
            padding: 10px 20px;
            border-radius: 8px;
            font-size: 14px;
            font-weight: 600;
            cursor: pointer;
            border: none;
            transition: all 0.2s;
        }
        
        .btn-primary {
            background: linear-gradient(135deg, #f59e0b, #d97706);
            color: white;
        }
        
        .btn-primary:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 15px rgba(245, 158, 11, 0.4);
        }
        
        .stats-grid {
            display: grid;
            grid-template-columns: repeat(4, 1fr);
            gap: 16px;
            margin-bottom: 24px;
        }
        
        .stat-card {
            background: #1e293b;
            border: 1px solid #374151;
            border-radius: 12px;
            padding: 20px;
        }
        
        .stat-label {
            font-size: 12px;
            color: #9ca3af;
            text-transform: uppercase;
            font-weight: 600;
            letter-spacing: 0.5px;
        }
        
        .stat-value {
            font-size: 28px;
            font-weight: 700;
            margin-top: 8px;
        }
        
        .stat-value.amber { color: #fbbf24; }
        .stat-value.blue { color: #60a5fa; }
        .stat-value.green { color: #10b981; }
        .stat-value.orange { color: #f59e0b; }
        
        .analysis-list {
            background: #1e293b;
            border: 1px solid #374151;
            border-radius: 12px;
            overflow: hidden;
        }
        
        .analysis-header {
            background: #0f172a;
            padding: 16px 20px;
            border-bottom: 1px solid #374151;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        
        .analysis-header h2 {
            font-size: 16px;
            font-weight: 600;
            color: #fff;
        }
        
        .analysis-table {
            width: 100%;
            border-collapse: collapse;
        }
        
        .analysis-table th {
            background: #0f172a;
            padding: 12px 16px;
            text-align: left;
            font-size: 11px;
            text-transform: uppercase;
            color: #9ca3af;
            font-weight: 600;
            letter-spacing: 0.5px;
            border-bottom: 1px solid #374151;
        }
        
        .analysis-table td {
            padding: 16px;
            border-bottom: 1px solid #374151;
        }
        
        .analysis-table tr:hover {
            background: #0f172a;
        }
        
        .analysis-name {
            font-weight: 600;
            color: #fff;
        }
        
        .seed-keyword {
            display: inline-block;
            background: #f59e0b20;
            color: #fbbf24;
            padding: 4px 10px;
            border-radius: 6px;
            font-size: 12px;
            font-weight: 500;
            margin-top: 6px;
        }
        
        .marketplace-badge {
            background: #6366f120;
            color: #a78bfa;
            padding: 4px 10px;
            border-radius: 6px;
            font-size: 11px;
            font-weight: 500;
        }
        
        .marketplace-flag {
            margin-right: 4px;
        }
        
        .keywords-count {
            font-size: 18px;
            font-weight: 700;
            color: #10b981;
        }
        
        .status-badge {
            padding: 4px 10px;
            border-radius: 6px;
            font-size: 11px;
            font-weight: 600;
        }
        
        .status-completed {
            background: #10b98120;
            color: #10b981;
        }
        
        .status-pending {
            background: #f59e0b20;
            color: #f59e0b;
        }
        
        .status-failed {
            background: #ef444420;
            color: #ef4444;
        }
        
        .action-btn {
            background: #374151;
            border: none;
            color: #9ca3af;
            padding: 8px 12px;
            border-radius: 6px;
            cursor: pointer;
            font-size: 12px;
            margin-right: 8px;
            transition: all 0.2s;
        }
        
        .action-btn:hover {
            background: #4b5563;
            color: #fff;
        }
        
        .action-btn.view {
            background: #f59e0b20;
            color: #f59e0b;
        }
        
        .action-btn.export {
            background: #10b98120;
            color: #10b981;
        }
        
        .action-btn.delete {
            background: #ef444420;
            color: #ef4444;
        }
        
        .empty-state {
            text-align: center;
            padding: 60px 20px;
        }
        
        .empty-state-icon {
            font-size: 48px;
            margin-bottom: 16px;
        }
        
        .empty-state-title {
            font-size: 18px;
            font-weight: 600;
            color: #fff;
            margin-bottom: 8px;
        }
        
        .empty-state-text {
            color: #9ca3af;
            font-size: 14px;
        }
        
        .time-ago {
            color: #6b7280;
            font-size: 12px;
        }
        
        .pagination {
            display: flex;
            justify-content: center;
            gap: 8px;
            padding: 20px;
        }
        
        .pagination a {
            padding: 8px 14px;
            background: #374151;
            color: #e5e7eb;
            border-radius: 6px;
            text-decoration: none;
            font-size: 13px;
        }
        
        .pagination a:hover, .pagination a.active {
            background: #f59e0b;
        }
        
        .alert {
            padding: 12px 16px;
            border-radius: 8px;
            margin-bottom: 16px;
            font-size: 14px;
        }
        
        .alert-success {
            background: #10b98120;
            color: #10b981;
            border: 1px solid #10b98140;
        }
        
        .alert-error {
            background: #ef444420;
            color: #ef4444;
            border: 1px solid #ef444440;
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
            <a href="{{ route('cerebro.index') }}">Keyword Analyzer</a>
            <a href="{{ route('magnet.index') }}" class="active">Keyword Magnet</a>
            <a href="{{ route('logout') }}">Logout</a>
        </div>
    </nav>
    
    <div class="container">
        @if(session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
        @endif
        
        @if(session('error'))
        <div class="alert alert-error">{{ session('error') }}</div>
        @endif
        
        <div class="page-header">
            <div>
                <h1 class="page-title">🧲 Keyword Magnet</h1>
                <p class="page-subtitle">Discover new keyword ideas from seed terms (Default: Amazon Egypt 🇪🇬)</p>
            </div>
        </div>
        
        <!-- How to Use Card -->
        <div style="background: linear-gradient(135deg, #1e293b 0%, #0f172a 100%); border: 1px solid #374151; border-radius: 12px; padding: 24px; margin-bottom: 24px;">
            <h2 style="font-size: 18px; font-weight: 600; margin-bottom: 16px; color: #fff; display: flex; align-items: center; gap: 10px;">
                <span>🚀</span> How to Run New Analysis
            </h2>
            <div style="display: flex; gap: 24px; flex-wrap: wrap;">
                <div style="flex: 1; min-width: 200px;">
                    <div style="display: flex; align-items: center; gap: 12px; margin-bottom: 12px;">
                        <span style="background: #f59e0b; color: #000; width: 28px; height: 28px; border-radius: 50%; display: flex; align-items: center; justify-content: center; font-weight: bold; font-size: 14px;">1</span>
                        <span style="color: #e5e7eb;">Go to <a href="https://www.amazon.eg" target="_blank" style="color: #f59e0b; text-decoration: none;">Amazon.eg</a> and search for a product</span>
                    </div>
                    <div style="display: flex; align-items: center; gap: 12px; margin-bottom: 12px;">
                        <span style="background: #f59e0b; color: #000; width: 28px; height: 28px; border-radius: 50%; display: flex; align-items: center; justify-content: center; font-weight: bold; font-size: 14px;">2</span>
                        <span style="color: #e5e7eb;">Click the <strong style="color: #f59e0b;">🧲 Keyword Magnet</strong> button in the toolbar</span>
                    </div>
                    <div style="display: flex; align-items: center; gap: 12px;">
                        <span style="background: #f59e0b; color: #000; width: 28px; height: 28px; border-radius: 50%; display: flex; align-items: center; justify-content: center; font-weight: bold; font-size: 14px;">3</span>
                        <span style="color: #e5e7eb;">Results will be saved here automatically</span>
                    </div>
                </div>
                <div style="flex: 1; min-width: 200px; background: #0f172a; border-radius: 8px; padding: 16px; border: 1px solid #374151;">
                    <p style="color: #9ca3af; font-size: 13px; margin: 0;">
                        💡 <strong style="color: #fff;">Tip:</strong> The Chrome extension runs directly on Amazon pages with full access to product data, giving you accurate keyword suggestions, search volume estimates, and title density metrics.
                    </p>
                </div>
            </div>
        </div>

        <!-- Stats Grid -->
        <div class="stats-grid">
            <div class="stat-card">
                <div class="stat-label">Total Searches</div>
                <div class="stat-value amber">{{ count($analyses ?? []) }}</div>
            </div>
            <div class="stat-card">
                <div class="stat-label">Keywords Discovered</div>
                <div class="stat-value blue">{{ number_format(collect($analyses)->sum('total_keywords')) }}</div>
            </div>
            <div class="stat-card">
                <div class="stat-label">Default Market</div>
                <div class="stat-value green">🇪🇬 EG</div>
            </div>
            <div class="stat-card">
                <div class="stat-label">Available Markets</div>
                <div class="stat-value orange">{{ count($marketplaces) }}</div>
            </div>
        </div>
        
        <!-- Analysis List -->
        <div class="analysis-list">
            <div class="analysis-header">
                <h2>📊 Keyword Discovery History</h2>
            </div>
            
            @if(count($analyses) > 0)
            <table class="analysis-table">
                <thead>
                    <tr>
                        <th>Seed Keyword</th>
                        <th>Marketplace</th>
                        <th>Keywords Found</th>
                        <th>Status</th>
                        <th>Date</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($analyses as $analysis)
                    @php
                        $mp = collect($marketplaces)->firstWhere('code', $analysis->marketplace);
                    @endphp
                    <tr>
                        <td>
                            <div class="analysis-name">{{ $analysis->name ?? 'Untitled' }}</div>
                            <div class="seed-keyword">🔍 {{ $analysis->seed_keyword }}</div>
                        </td>
                        <td>
                            <span class="marketplace-badge">
                                <span class="marketplace-flag">{{ $mp['flag'] ?? '🌐' }}</span>
                                {{ strtoupper(str_replace('amazon.', '', $analysis->marketplace)) }}
                            </span>
                        </td>
                        <td>
                            <span class="keywords-count">{{ number_format($analysis->total_keywords) }}</span>
                        </td>
                        <td>
                            <span class="status-badge status-{{ $analysis->status }}">
                                {{ ucfirst($analysis->status) }}
                            </span>
                        </td>
                        <td>
                            <span class="time-ago">{{ \Carbon\Carbon::parse($analysis->created_at)->diffForHumans() }}</span>
                        </td>
                        <td>
                            <a href="{{ route('magnet.show', $analysis->id) }}" class="action-btn view">👁️ View</a>
                            <a href="{{ route('magnet.export', $analysis->id) }}" class="action-btn export">📥 CSV</a>
                            <form action="{{ route('magnet.destroy', $analysis->id) }}" method="POST" style="display: inline;" onsubmit="return confirm('Delete this analysis?')">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="action-btn delete">🗑️</button>
                            </form>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
            
            @if($analyses->hasPages())
            <div class="pagination">
                {{ $analyses->links() }}
            </div>
            @endif
            
            @else
            <div class="empty-state">
                <div class="empty-state-icon">🧲</div>
                <div class="empty-state-title">No keyword discoveries yet</div>
                <div class="empty-state-text">Run Keyword Magnet from the Chrome extension to discover keyword ideas</div>
            </div>
            @endif
        </div>
    </div>
</body>
</html>
