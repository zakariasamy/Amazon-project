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
        
        .nav-links a:hover, .nav-links a.active {
            color: #d97706;
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
            color: #0f172a;
        }
        
        .page-subtitle {
            color: #475569;
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
            background: #ffffff;
            border: 1px solid #e2e8f0;
            border-radius: 12px;
            padding: 20px;
            box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.05), 0 2px 4px -1px rgba(0, 0, 0, 0.03);
        }
        
        .stat-label {
            font-size: 12px;
            color: #64748b;
            text-transform: uppercase;
            font-weight: 600;
            letter-spacing: 0.5px;
        }
        
        .stat-value {
            font-size: 28px;
            font-weight: 700;
            margin-top: 8px;
            color: #0f172a;
        }
        
        .stat-value.amber { color: #d97706; }
        .stat-value.blue { color: #2563eb; }
        .stat-value.green { color: #10b981; }
        .stat-value.orange { color: #d97706; }
        
        .analysis-list {
            background: #ffffff;
            border: 1px solid #e2e8f0;
            border-radius: 12px;
            overflow: hidden;
            box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.05);
        }
        
        .analysis-header {
            background: #f8fafc;
            padding: 16px 20px;
            border-bottom: 1px solid #e2e8f0;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        
        .analysis-header h2 {
            font-size: 16px;
            font-weight: 600;
            color: #0f172a;
        }
        
        .analysis-table {
            width: 100%;
            border-collapse: collapse;
        }
        
        .analysis-table th {
            background: #f8fafc;
            padding: 12px 16px;
            text-align: left;
            font-size: 11px;
            text-transform: uppercase;
            color: #475569;
            font-weight: 600;
            letter-spacing: 0.5px;
            border-bottom: 1px solid #e2e8f0;
        }
        
        .analysis-table td {
            padding: 16px;
            border-bottom: 1px solid #e2e8f0;
            color: #0f172a;
        }
        
        .analysis-table tr:hover {
            background: #f8fafc;
        }
        
        .analysis-name {
            font-weight: 600;
            color: #0f172a;
        }
        
        .seed-keyword {
            display: inline-block;
            background: rgba(245, 158, 11, 0.1);
            color: #d97706;
            padding: 4px 10px;
            border-radius: 6px;
            font-size: 12px;
            font-weight: 500;
            margin-top: 6px;
        }
        
        .marketplace-badge {
            background: rgba(99, 102, 241, 0.1);
            color: #4f46e5;
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
            background: rgba(16, 185, 129, 0.1);
            color: #10b981;
        }
        
        .status-pending {
            background: rgba(245, 158, 11, 0.1);
            color: #f59e0b;
        }
        
        .status-failed {
            background: rgba(239, 68, 68, 0.1);
            color: #ef4444;
        }
        
        .action-btn {
            background: #ffffff;
            border: 1px solid #e2e8f0;
            color: #475569;
            padding: 8px 12px;
            border-radius: 6px;
            cursor: pointer;
            font-size: 12px;
            margin-right: 8px;
            transition: all 0.2s;
        }
        
        .action-btn:hover {
            background: #f1f5f9;
            color: #0f172a;
        }
        
        .action-btn.view {
            background: rgba(245, 158, 11, 0.1);
            color: #d97706;
            border-color: rgba(245, 158, 11, 0.2);
        }
        
        .action-btn.export {
            background: rgba(16, 185, 129, 0.1);
            color: #10b981;
            border-color: rgba(16, 185, 129, 0.2);
        }
        
        .action-btn.delete {
            background: rgba(239, 68, 68, 0.1);
            color: #ef4444;
            border-color: rgba(239, 68, 68, 0.2);
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
            color: #0f172a;
            margin-bottom: 8px;
        }
        
        .empty-state-text {
            color: #475569;
            font-size: 14px;
        }
        
        .time-ago {
            color: #64748b;
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
            background: #ffffff;
            border: 1px solid #e2e8f0;
            color: #475569;
            border-radius: 6px;
            text-decoration: none;
            font-size: 13px;
        }
        
        .pagination a:hover, .pagination a.active {
            background: #f59e0b;
            color: white;
            border-color: #f59e0b;
        }
        
        .alert {
            padding: 12px 16px;
            border-radius: 8px;
            margin-bottom: 16px;
            font-size: 14px;
        }
        
        .alert-success {
            background: rgba(16, 185, 129, 0.1);
            color: #10b981;
            border: 1px solid rgba(16, 185, 129, 0.2);
        }
        
        .alert-error {
            background: rgba(239, 68, 68, 0.1);
            color: #ef4444;
            border: 1px solid rgba(239, 68, 68, 0.2);
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
        <div style="background: linear-gradient(135deg, #ffffff 0%, #f8fafc 100%); border: 1px solid #e2e8f0; border-radius: 12px; padding: 24px; margin-bottom: 24px; box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.05);">
            <h2 style="font-size: 18px; font-weight: 600; margin-bottom: 16px; color: #0f172a; display: flex; align-items: center; gap: 10px;">
                <span>🚀</span> How to Run New Analysis
            </h2>
            <div style="display: flex; gap: 24px; flex-wrap: wrap;">
                <div style="flex: 1; min-width: 200px;">
                    <div style="display: flex; align-items: center; gap: 12px; margin-bottom: 12px;">
                        <span style="background: #f59e0b; color: white; width: 28px; height: 28px; border-radius: 50%; display: flex; align-items: center; justify-content: center; font-weight: bold; font-size: 14px;">1</span>
                        <span style="color: #475569;">Go to <a href="https://www.amazon.eg" target="_blank" style="color: #d97706; text-decoration: none; font-weight: 600;">Amazon.eg</a> and search for a product</span>
                    </div>
                    <div style="display: flex; align-items: center; gap: 12px; margin-bottom: 12px;">
                        <span style="background: #f59e0b; color: white; width: 28px; height: 28px; border-radius: 50%; display: flex; align-items: center; justify-content: center; font-weight: bold; font-size: 14px;">2</span>
                        <span style="color: #475569;">Click the <strong style="color: #d97706;">🧲 Keyword Magnet</strong> button in the toolbar</span>
                    </div>
                    <div style="display: flex; align-items: center; gap: 12px;">
                        <span style="background: #f59e0b; color: white; width: 28px; height: 28px; border-radius: 50%; display: flex; align-items: center; justify-content: center; font-weight: bold; font-size: 14px;">3</span>
                        <span style="color: #475569;">Results will be saved here automatically</span>
                    </div>
                </div>
                <div style="flex: 1; min-width: 200px; background: #ffffff; border-radius: 8px; padding: 16px; border: 1px solid #e2e8f0; box-shadow: inset 0 2px 4px 0 rgba(0, 0, 0, 0.02);">
                    <p style="color: #64748b; font-size: 13px; margin: 0;">
                        💡 <strong style="color: #0f172a;">Tip:</strong> The Chrome extension runs directly on Amazon pages with full access to product data, giving you accurate keyword suggestions, search volume estimates, and title density metrics.
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
