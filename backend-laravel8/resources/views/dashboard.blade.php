<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard - Amazon Product Analyzer</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <style>
        :root {
            --primary: #6366f1;
            --primary-dark: #4f46e5;
            --primary-light: #818cf8;
            --secondary: #0ea5e9;
            --success: #10b981;
            --warning: #f59e0b;
            --danger: #ef4444;
            --dark: #0f172a;
            --dark-light: #1e293b;
            --dark-medium: #334155;
            --gray: #64748b;
            --gray-light: #94a3b8;
            --white: #ffffff;
            --gradient: linear-gradient(135deg, var(--primary) 0%, var(--secondary) 100%);
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
            border-right: 1px solid rgba(255,255,255,0.1);
            padding: 1.5rem;
            display: flex;
            flex-direction: column;
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
            border-bottom: 1px solid rgba(255,255,255,0.1);
        }

        .sidebar-logo .icon {
            width: 40px;
            height: 40px;
            background: var(--gradient);
            border-radius: 10px;
            display: flex;
            align-items: center;
            justify-content: center;
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
        }

        .nav-item:hover {
            background: rgba(255,255,255,0.05);
            color: var(--white);
        }

        .nav-item.active {
            background: var(--primary);
            color: var(--white);
        }

        .nav-item-icon {
            font-size: 1.25rem;
        }

        .sidebar-footer {
            margin-top: auto;
            padding-top: 1rem;
            border-top: 1px solid rgba(255,255,255,0.1);
        }

        .user-card {
            display: flex;
            align-items: center;
            gap: 0.75rem;
            padding: 0.75rem;
            background: var(--dark);
            border-radius: 12px;
        }

        .user-avatar {
            width: 40px;
            height: 40px;
            background: var(--gradient);
            border-radius: 10px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: 600;
        }

        .user-info {
            flex: 1;
        }

        .user-name {
            font-weight: 600;
            font-size: 0.875rem;
        }

        .user-plan {
            font-size: 0.75rem;
            color: var(--gray);
        }

        /* Main Content */
        .main {
            margin-left: 260px;
            padding: 2rem;
        }

        .header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 2rem;
        }

        .header h1 {
            font-size: 1.75rem;
            font-weight: 700;
        }

        .header-actions {
            display: flex;
            gap: 1rem;
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
        }

        .btn-primary {
            background: var(--gradient);
            color: var(--white);
        }

        .btn-primary:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 15px rgba(99, 102, 241, 0.4);
        }

        .btn-outline {
            background: transparent;
            border: 2px solid rgba(255,255,255,0.2);
            color: var(--white);
        }

        .btn-outline:hover {
            border-color: var(--primary);
            color: var(--primary);
        }

        /* Stats Grid */
        .stats-grid {
            display: grid;
            grid-template-columns: repeat(4, 1fr);
            gap: 1.5rem;
            margin-bottom: 2rem;
        }

        .stat-card {
            background: var(--dark-light);
            border: 1px solid rgba(255,255,255,0.1);
            border-radius: 16px;
            padding: 1.5rem;
        }

        .stat-header {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            margin-bottom: 1rem;
        }

        .stat-icon {
            width: 45px;
            height: 45px;
            background: rgba(99, 102, 241, 0.1);
            border-radius: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.25rem;
        }

        .stat-trend {
            font-size: 0.75rem;
            font-weight: 600;
            padding: 0.25rem 0.5rem;
            border-radius: 50px;
        }

        .stat-trend.up {
            background: rgba(16, 185, 129, 0.1);
            color: #34d399;
        }

        .stat-trend.down {
            background: rgba(239, 68, 68, 0.1);
            color: #f87171;
        }

        .stat-value {
            font-size: 2rem;
            font-weight: 700;
            margin-bottom: 0.25rem;
        }

        .stat-label {
            font-size: 0.875rem;
            color: var(--gray);
        }

        /* Cards */
        .card {
            background: var(--dark-light);
            border: 1px solid rgba(255,255,255,0.1);
            border-radius: 16px;
            padding: 1.5rem;
            margin-bottom: 1.5rem;
        }

        .card-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 1.5rem;
        }

        .card-title {
            font-size: 1.125rem;
            font-weight: 600;
        }

        /* Quick Actions */
        .quick-actions {
            display: grid;
            grid-template-columns: repeat(4, 1fr);
            gap: 1rem;
        }

        .quick-action {
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            gap: 0.75rem;
            padding: 2rem 1.5rem;
            background: var(--dark);
            border: 2px dashed rgba(255,255,255,0.1);
            border-radius: 16px;
            text-decoration: none;
            color: var(--gray-light);
            transition: all 0.3s;
        }

        .quick-action:hover {
            border-color: var(--primary);
            background: rgba(99, 102, 241, 0.05);
            color: var(--white);
        }

        .quick-action-icon {
            font-size: 2rem;
        }

        .quick-action-title {
            font-weight: 600;
            font-size: 0.95rem;
        }

        .quick-action-desc {
            font-size: 0.8rem;
            color: var(--gray);
            text-align: center;
        }

        /* Recent Searches Table */
        .table-wrapper {
            overflow-x: auto;
        }

        .table {
            width: 100%;
            border-collapse: collapse;
        }

        .table th,
        .table td {
            padding: 1rem;
            text-align: left;
            border-bottom: 1px solid rgba(255,255,255,0.05);
        }

        .table th {
            font-size: 0.75rem;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 0.05em;
            color: var(--gray);
        }

        .table td {
            font-size: 0.875rem;
        }

        .table tbody tr:hover {
            background: rgba(255,255,255,0.02);
        }

        .keyword-cell {
            font-weight: 500;
        }

        .volume-badge {
            display: inline-flex;
            align-items: center;
            gap: 0.25rem;
            padding: 0.25rem 0.5rem;
            background: rgba(16, 185, 129, 0.1);
            border-radius: 6px;
            color: #34d399;
            font-weight: 600;
            font-size: 0.8rem;
        }

        .difficulty-bar {
            width: 60px;
            height: 6px;
            background: rgba(255,255,255,0.1);
            border-radius: 3px;
            overflow: hidden;
        }

        .difficulty-fill {
            height: 100%;
            border-radius: 3px;
        }

        .difficulty-fill.easy { background: #10b981; }
        .difficulty-fill.medium { background: #f59e0b; }
        .difficulty-fill.hard { background: #ef4444; }

        .empty-state {
            text-align: center;
            padding: 3rem;
            color: var(--gray);
        }

        .empty-state-icon {
            font-size: 3rem;
            margin-bottom: 1rem;
        }

        /* Extension Banner */
        .extension-banner {
            background: var(--gradient);
            border-radius: 16px;
            padding: 2rem;
            display: flex;
            align-items: center;
            justify-content: space-between;
            margin-bottom: 2rem;
        }

        .extension-banner h3 {
            font-size: 1.25rem;
            margin-bottom: 0.5rem;
        }

        .extension-banner p {
            opacity: 0.9;
            font-size: 0.95rem;
        }

        .btn-white {
            background: var(--white);
            color: var(--primary);
            padding: 0.875rem 1.5rem;
        }

        .btn-white:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 15px rgba(255,255,255,0.3);
        }

        @media (max-width: 1200px) {
            .stats-grid {
                grid-template-columns: repeat(2, 1fr);
            }
        }

        @media (max-width: 768px) {
            .sidebar {
                display: none;
            }
            .main {
                margin-left: 0;
            }
            .stats-grid {
                grid-template-columns: 1fr;
            }
            .quick-actions {
                grid-template-columns: 1fr;
            }
        }
    </style>
</head>
<body>
    <!-- Sidebar -->
    <aside class="sidebar">
        <a href="/" class="sidebar-logo">
            <div class="icon">📊</div>
            Amazon Analyzer
        </a>

        <nav>
            <div class="nav-section">
                <div class="nav-section-title">Main</div>
                <a href="/dashboard" class="nav-item active">
                    <span class="nav-item-icon">🏠</span>
                    Dashboard
                </a>
                <a href="/cerebro" class="nav-item">
                    <span class="nav-item-icon">🧠</span>
                    Keyword Analyzer Pro
                </a>
                <a href="/magnet" class="nav-item">
                    <span class="nav-item-icon">🧲</span>
                    Keyword Magnet
                </a>
                <a href="/dashboard/cerebro/folders" class="nav-item">
                    <span class="nav-item-icon">📁</span>
                    Keyword Folders
                </a>
                <a href="/keywords" class="nav-item">
                    <span class="nav-item-icon">🔍</span>
                    Keyword Research
                </a>
                <a href="/reverse-asin" class="nav-item">
                    <span class="nav-item-icon">🔄</span>
                    Reverse ASIN
                </a>
                <a href="/products" class="nav-item">
                    <span class="nav-item-icon">📦</span>
                    Product Tracker
                </a>
            </div>

            <div class="nav-section">
                <div class="nav-section-title">Tools</div>
                <a href="/guide" class="nav-item">
                    <span class="nav-item-icon">📚</span>
                    Product Research Guide
                </a>
                <a href="/calculator" class="nav-item">
                    <span class="nav-item-icon">🧮</span>
                    FBA Calculator
                </a>
                <a href="/history" class="nav-item">
                    <span class="nav-item-icon">📜</span>
                    Search History
                </a>
            </div>

            <div class="nav-section">
                <div class="nav-section-title">Account</div>
                <a href="/settings" class="nav-item">
                    <span class="nav-item-icon">⚙️</span>
                    Settings
                </a>
                <a href="/subscription" class="nav-item">
                    <span class="nav-item-icon">💎</span>
                    Subscription
                </a>
            </div>
        </nav>

        <div class="sidebar-footer">
            <div class="user-card">
                <div class="user-avatar">{{ substr(Auth::user()->name ?? 'U', 0, 1) }}</div>
                <div class="user-info">
                    <div class="user-name">{{ Auth::user()->name ?? 'User' }}</div>
                    <div class="user-plan">Free Plan</div>
                </div>
            </div>
        </div>
    </aside>

    <!-- Main Content -->
    <main class="main">
        <div class="header">
            <h1>Welcome back, {{ explode(' ', Auth::user()->name ?? 'User')[0] }}! 👋</h1>
            <div class="header-actions">
                <form method="POST" action="/logout" style="display: inline;">
                    @csrf
                    <button type="submit" class="btn btn-outline">Logout</button>
                </form>
            </div>
        </div>

        <!-- Extension Banner -->
        <div class="extension-banner">
            <div>
                <h3>🚀 Install the Chrome Extension</h3>
                <p>Analyze any Amazon page with one click. Search volume, BSR, sales & more.</p>
            </div>
            <a href="#" class="btn btn-white">Download Extension</a>
        </div>

        <!-- Stats Grid -->
        <div class="stats-grid">
            <div class="stat-card">
                <div class="stat-header">
                    <div class="stat-icon">🔍</div>
                    <span class="stat-trend up">+12%</span>
                </div>
                <div class="stat-value">0</div>
                <div class="stat-label">Keywords Analyzed</div>
            </div>
            <div class="stat-card">
                <div class="stat-header">
                    <div class="stat-icon">📦</div>
                    <span class="stat-trend up">+8%</span>
                </div>
                <div class="stat-value">0</div>
                <div class="stat-label">Products Tracked</div>
            </div>
            <div class="stat-card">
                <div class="stat-header">
                    <div class="stat-icon">🔄</div>
                    <span class="stat-trend down">-3%</span>
                </div>
                <div class="stat-value">0</div>
                <div class="stat-label">Reverse ASIN Lookups</div>
            </div>
            <div class="stat-card">
                <div class="stat-header">
                    <div class="stat-icon">⏱️</div>
                </div>
                <div class="stat-value">7</div>
                <div class="stat-label">Days Left in Trial</div>
            </div>
        </div>

        <!-- Quick Actions -->
        <div class="card">
            <div class="card-header">
                <h2 class="card-title">Quick Actions</h2>
            </div>
            <div class="quick-actions">
                <a href="/cerebro" class="quick-action">
                    <span class="quick-action-icon">🧠</span>
                    <span class="quick-action-title">Keyword Analyzer Pro</span>
                    <span class="quick-action-desc">Multi-ASIN keyword analysis</span>
                </a>
                <a href="/magnet" class="quick-action">
                    <span class="quick-action-icon">🧲</span>
                    <span class="quick-action-title">Keyword Magnet</span>
                    <span class="quick-action-desc">Discover keyword ideas</span>
                </a>
                <a href="#" class="quick-action">
                    <span class="quick-action-icon">🔍</span>
                    <span class="quick-action-title">Keyword Research</span>
                    <span class="quick-action-desc">Find high-volume keywords</span>
                </a>
                <a href="#" class="quick-action">
                    <span class="quick-action-icon">🧮</span>
                    <span class="quick-action-title">FBA Calculator</span>
                    <span class="quick-action-desc">Calculate profit margins</span>
                </a>
            </div>
        </div>

        <!-- Recent Searches -->
        <div class="card">
            <div class="card-header">
                <h2 class="card-title">Recent Searches</h2>
                <a href="/history" class="btn btn-outline" style="padding: 0.5rem 1rem; font-size: 0.8rem;">View All</a>
            </div>
            <div class="table-wrapper">
                <div class="empty-state">
                    <div class="empty-state-icon">🔍</div>
                    <h3>No searches yet</h3>
                    <p>Install the Chrome extension and start analyzing Amazon pages!</p>
                </div>
            </div>
        </div>
    </main>
</body>
</html>
