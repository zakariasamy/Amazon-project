<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Competitor Keyword Analyzer - Dashboard</title>
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
            color: #6366f1;
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
            background: linear-gradient(135deg, #6366f1, #4f46e5);
            color: white;
        }
        
        .btn-primary:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 15px rgba(99, 102, 241, 0.4);
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
        
        .stat-value.purple { color: #8b5cf6; }
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
        
        .analysis-asins {
            display: flex;
            flex-wrap: wrap;
            gap: 6px;
            margin-top: 8px;
        }
        
        .asin-badge {
            background: #f1f5f9;
            color: #475569;
            padding: 4px 8px;
            border-radius: 4px;
            font-size: 10px;
            font-family: monospace;
            border: 1px solid #e2e8f0;
        }
        
        .marketplace-badge {
            background: rgba(99, 102, 241, 0.1);
            color: #4f46e5;
            padding: 4px 10px;
            border-radius: 6px;
            font-size: 11px;
            font-weight: 500;
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
            background: rgba(99, 102, 241, 0.1);
            color: #6366f1;
            border-color: rgba(99, 102, 241, 0.2);
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
            background: #6366f1;
            color: white;
            border-color: #6366f1;
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
            <a href="{{ route('cerebro.folders') }}">Folders</a>
            <a href="/settings">Settings</a>
        </div>
    </nav>
    
    <div class="container">
        <div class="page-header">
            <div>
                <h1 class="page-title">Keyword Analysis History</h1>
                <p class="page-subtitle">View all your multi-ASIN keyword analyses</p>
            </div>
        </div>
        
        <!-- Stats Grid -->
        <div class="stats-grid">
            <div class="stat-card">
                <div class="stat-label">Total Analyses</div>
                <div class="stat-value purple">{{ $stats['total_analyses'] ?? 0 }}</div>
            </div>
            <div class="stat-card">
                <div class="stat-label">Total Keywords</div>
                <div class="stat-value blue">{{ number_format($stats['total_keywords'] ?? 0) }}</div>
            </div>
            <div class="stat-card">
                <div class="stat-label">ASINs Analyzed</div>
                <div class="stat-value green">{{ $stats['total_asins'] ?? 0 }}</div>
            </div>
            <div class="stat-card">
                <div class="stat-label">This Month</div>
                <div class="stat-value orange">{{ $stats['this_month'] ?? 0 }}</div>
            </div>
        </div>
        
        <!-- Analysis List -->
        <div class="analysis-list">
            <div class="analysis-header">
                <h2>📊 Recent Analyses</h2>
            </div>
            
            @if(count($analyses) > 0)
            <table class="analysis-table">
                <thead>
                    <tr>
                        <th>Analysis</th>
                        <th>Marketplace</th>
                        <th>Keywords</th>
                        <th>Status</th>
                        <th>Date</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($analyses as $analysis)
                    <tr>
                        <td>
                            <div class="analysis-name">{{ $analysis->name ?? 'Untitled Analysis' }}</div>
                            <div class="analysis-asins">
                                @php $asins = is_array($analysis->asins) ? $analysis->asins : json_decode($analysis->asins, true); @endphp
                                @foreach(array_slice($asins ?? [], 0, 5) as $asin)
                                    <span class="asin-badge">{{ $asin }}</span>
                                @endforeach
                                @if(count($asins ?? []) > 5)
                                    <span class="asin-badge">+{{ count($asins) - 5 }} more</span>
                                @endif
                            </div>
                        </td>
                        <td>
                            <span class="marketplace-badge">{{ $analysis->marketplace }}</span>
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
                            <a href="{{ route('cerebro.show', $analysis->id) }}" class="action-btn view">👁️ View</a>
                            <a href="{{ route('cerebro.export', $analysis->id) }}" class="action-btn export">📥 CSV</a>
                            <form action="{{ route('cerebro.destroy', $analysis->id) }}" method="POST" style="display: inline;" onsubmit="return confirm('Delete this analysis?')">
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
                <div class="empty-state-icon">🔍</div>
                <div class="empty-state-title">No analyses yet</div>
                <div class="empty-state-text">Run Competitor Keyword Analyzer from the Chrome extension to see results here</div>
            </div>
            @endif
        </div>
    </div>
</body>
</html>
