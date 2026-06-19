<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Google Keyword Planner Tool - SelaaScout</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
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
            --panel-shadow: 0 10px 30px rgba(0, 0, 0, 0.04);
            --border: 1px solid rgba(0, 0, 0, 0.08);
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
            border-right: 1px solid rgba(0,0,0,0.08);
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
            border-bottom: 1px solid rgba(0,0,0,0.08);
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
        }

        .nav-item:hover {
            background: rgba(0,0,0,0.04);
            color: var(--white);
        }

        .nav-item.active {
            background: var(--primary);
            color: #ffffff;
        }

        .nav-item-icon {
            font-size: 1.25rem;
        }

        .sidebar-footer {
            margin-top: auto;
            padding-top: 1rem;
            border-top: 1px solid rgba(0,0,0,0.08);
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
            color: white;
        }

        /* Main Area */
        .main {
            margin-left: 260px;
            padding: 2rem;
            max-width: 1400px;
        }

        .header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 2rem;
        }

        .header h1 {
            font-size: 1.75rem;
            font-weight: 800;
            background: linear-gradient(135deg, var(--white) 0%, var(--gray-light) 100%);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
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
            color: #ffffff;
            box-shadow: 0 4px 15px rgba(240, 136, 4, 0.35);
        }

        .btn-primary:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 20px rgba(240, 136, 4, 0.45);
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

        /* Main Form & Results Layout */
        .grid-layout {
            display: grid;
            grid-template-columns: 380px 1fr;
            gap: 2rem;
            align-items: start;
        }

        @media (max-width: 1024px) {
            .grid-layout {
                grid-template-columns: 1fr;
            }
        }

        .card {
            background: var(--dark-light);
            border: var(--border);
            border-radius: 16px;
            padding: 1.5rem;
            box-shadow: var(--panel-shadow);
            margin-bottom: 1.5rem;
        }

        .card-title {
            font-size: 1.1rem;
            font-weight: 700;
            margin-bottom: 1.25rem;
            color: var(--white);
            display: flex;
            align-items: center;
            gap: 8px;
            border-bottom: 1px solid rgba(0, 0, 0, 0.05);
            padding-bottom: 0.75rem;
        }

        /* Input Form styling */
        .form-group {
            margin-bottom: 1.25rem;
        }

        .form-group label {
            display: block;
            font-size: 0.825rem;
            font-weight: 600;
            color: var(--gray-light);
            margin-bottom: 0.5rem;
        }

        .form-group input,
        .form-group select {
            width: 100%;
            padding: 0.75rem 1rem;
            border: 1px solid var(--dark-medium);
            border-radius: 10px;
            font-size: 0.9rem;
            font-family: inherit;
            color: var(--white);
            background: var(--dark-light);
            transition: all 0.3s;
        }

        .form-group input:focus,
        .form-group select:focus {
            outline: none;
            border-color: var(--primary);
            box-shadow: 0 0 0 3px rgba(240, 136, 4, 0.15);
        }

        /* Mode Selector Tab */
        .mode-tabs {
            display: flex;
            background: rgba(0,0,0,0.03);
            padding: 4px;
            border-radius: 10px;
            margin-bottom: 1.25rem;
        }

        .mode-tab {
            flex: 1;
            padding: 0.5rem;
            text-align: center;
            border-radius: 8px;
            font-size: 0.85rem;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.2s;
            color: var(--gray);
        }

        .mode-tab.active {
            background: #ffffff;
            color: var(--primary);
            box-shadow: 0 2px 8px rgba(0,0,0,0.05);
        }

        /* Metrics Grid */
        .metrics-grid {
            display: grid;
            grid-template-columns: repeat(2, 1fr);
            gap: 1.25rem;
            margin-bottom: 1.5rem;
        }

        .metric-box {
            background: rgba(0,0,0,0.02);
            border: var(--border);
            border-radius: 12px;
            padding: 1.25rem;
            text-align: center;
        }

        .metric-title {
            font-size: 0.75rem;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 0.05em;
            color: var(--gray);
            margin-bottom: 0.5rem;
        }

        .metric-value {
            font-size: 1.75rem;
            font-weight: 800;
            color: var(--white);
        }

        .metric-sub {
            font-size: 0.75rem;
            color: var(--gray-light);
            margin-top: 0.25rem;
        }

        /* Results table */
        .table-wrapper {
            overflow-x: auto;
            border-radius: 12px;
            border: var(--border);
            margin-top: 1rem;
        }

        .table {
            width: 100%;
            border-collapse: collapse;
        }

        .table th,
        .table td {
            padding: 1rem;
            text-align: left;
            border-bottom: 1px solid rgba(0,0,0,0.05);
        }

        .table th {
            font-size: 0.75rem;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.05em;
            color: var(--gray);
            background: rgba(0,0,0,0.02);
        }

        .table td {
            font-size: 0.875rem;
        }

        .table tbody tr:hover {
            background: rgba(240, 136, 4, 0.02);
        }

        .badge {
            display: inline-flex;
            padding: 0.25rem 0.5rem;
            font-size: 0.75rem;
            font-weight: 700;
            border-radius: 6px;
        }

        .badge-success {
            background: rgba(16, 185, 129, 0.1);
            color: #10b981;
        }

        .badge-warning {
            background: rgba(245, 158, 11, 0.1);
            color: #f59e0b;
        }

        .badge-danger {
            background: rgba(239, 68, 68, 0.1);
            color: #ef4444;
        }

        /* Loader Overlay */
        .loading-overlay {
            display: none;
            position: absolute;
            top: 0; left: 0; right: 0; bottom: 0;
            background: rgba(255, 255, 255, 0.8);
            border-radius: 16px;
            z-index: 10;
            align-items: center;
            justify-content: center;
            flex-direction: column;
            gap: 12px;
        }

        .spinner {
            width: 40px;
            height: 40px;
            border: 4px solid rgba(240, 136, 4, 0.1);
            border-top-color: var(--primary);
            border-radius: 50%;
            animation: spin 1s linear infinite;
        }

        @keyframes spin {
            to { transform: rotate(360deg); }
        }

        .alert-info {
            background: #eff6ff;
            color: #1d4ed8;
            border: 1px solid #bfdbfe;
            padding: 1rem;
            border-radius: 12px;
            font-size: 0.85rem;
            margin-bottom: 1.5rem;
            line-height: 1.5;
        }

        .alert-danger {
            background: #fef2f2;
            color: #991b1b;
            border: 1px solid #fecaca;
            padding: 1rem;
            border-radius: 12px;
            font-size: 0.85rem;
            margin-bottom: 1.5rem;
            line-height: 1.5;
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

                <a href="/dashboard/folders" class="nav-item">
                    <span class="nav-item-icon">📁</span>
                    My Folders
                </a>
            </div>

            @if(Auth::user()->isAdmin())
            <div class="nav-section">
                <div class="nav-section-title">Admin</div>
                <a href="/admin/settings" class="nav-item">
                    <span class="nav-item-icon">⚙️</span>
                    Admin Tools Settings
                </a>
            </div>
            @endif
        </nav>

        <div class="sidebar-footer">
            <div class="user-card">
                <div class="user-avatar">{{ substr(Auth::user()->name ?? 'U', 0, 1) }}</div>
                <div class="user-info">
                    <div class="user-name" style="color: var(--white);">{{ Auth::user()->name ?? 'User' }}</div>
                    <div class="user-plan" style="color: var(--gray);">Developer Mode</div>
                </div>
            </div>
        </div>
    </aside>

    <!-- Main Content -->
    <main class="main">
        <div class="header">
            <h1>Google Keyword Planner & forecasting 💡</h1>
            <div class="header-actions">
                <a href="/dashboard" class="btn btn-outline">← Back to Dashboard</a>
            </div>
        </div>
        <!-- Stacked Report Layout -->
        <div style="position: relative;">
            <!-- Product & Keyword Details Panel (Read-only Summary) -->
            <div class="card" style="position: relative; margin-bottom: 2rem;">
                <div class="loading-overlay" id="form-loader" style="display: flex;">
                    <div class="spinner"></div>
                    <p style="font-weight:600;font-size:0.85rem;color:var(--primary); margin-top: 10px;">Querying Google Ads API & Running Algorithms...</p>
                </div>
                
                <h2 class="card-title">📊 Keyword & Product Analysis Details</h2>
                <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 1.5rem; padding: 0.5rem 0;">
                    <div>
                        <div style="font-size: 0.75rem; font-weight: 700; text-transform: uppercase; color: var(--gray); margin-bottom: 0.25rem;">Target Keyword</div>
                        <div style="font-size: 1.1rem; font-weight: 700;" id="summary-keyword">{{ $keyword ?? 'N/A' }}</div>
                    </div>
                    <div>
                        <div style="font-size: 0.75rem; font-weight: 700; text-transform: uppercase; color: var(--gray); margin-bottom: 0.25rem;">ASIN Identifier</div>
                        <div style="font-size: 1.1rem; font-weight: 700;" id="summary-asin">{{ $asin ?? 'N/A' }}</div>
                    </div>
                    <div>
                        <div style="font-size: 0.75rem; font-weight: 700; text-transform: uppercase; color: var(--gray); margin-bottom: 0.25rem;">Marketplace</div>
                        <div style="font-size: 1.1rem; font-weight: 700;" id="summary-marketplace">{{ strtoupper($marketplace) }}</div>
                    </div>
                    <div>
                        <div style="font-size: 0.75rem; font-weight: 700; text-transform: uppercase; color: var(--gray); margin-bottom: 0.25rem;">BSR (Best Sellers Rank)</div>
                        <div style="font-size: 1.1rem; font-weight: 700;" id="summary-bsr">{{ $bsr ? number_format($bsr) : 'N/A' }}</div>
                    </div>
                    <div>
                        <div style="font-size: 0.75rem; font-weight: 700; text-transform: uppercase; color: var(--gray); margin-bottom: 0.25rem;">Amazon Monthly Sales</div>
                        <div style="font-size: 1.1rem; font-weight: 700;" id="summary-sales">{{ $sales ? number_format($sales) : 'Estimating...' }}</div>
                    </div>
                </div>
                
                <!-- Hidden form to pass parameters to the javascript execution function -->
                <form id="simulate-form" style="display: none;">
                    @csrf
                    <input type="hidden" name="asin" value="{{ $asin }}">
                    <input type="hidden" name="keyword" value="{{ $keyword }}">
                    <input type="hidden" name="category" value="{{ $category }}">
                    <input type="hidden" name="marketplace" value="{{ $marketplace }}">
                    <input type="hidden" name="bsr" value="{{ $bsr }}">
                    <input type="hidden" name="sales" value="{{ $sales }}">
                </form>
            </div>

            <div class="alert-info" id="api-status-banner" style="margin-bottom: 2rem;">
                🔍 Querying Google Ads API & Running Algorithms...
            </div>

            <div class="metrics-grid" style="grid-template-columns: repeat(auto-fit, minmax(240px, 1fr)); gap: 1.5rem; margin-bottom: 2rem;">
                <div class="metric-box" style="border-left: 5px solid var(--danger);">
                    <div class="metric-title">Before Search Volume</div>
                    <div class="metric-value" id="val-before">-</div>
                    <div class="metric-sub">Pure BSR-based expected volume</div>
                </div>
                <div class="metric-box" style="border-left: 5px solid var(--success);">
                    <div class="metric-title">After Blended Volume</div>
                    <div class="metric-value" id="val-after">-</div>
                    <div class="metric-sub">Hybrid scaled by Google Intent Ratio</div>
                </div>
            </div>

            <!-- 12-Month Projections Line Chart -->
            <div class="card" style="margin-bottom: 2rem;">
                <h2 class="card-title">📈 12-Month Sales Projections (Google Seasonality-Adjusted)</h2>
                <div style="height: 320px; position: relative;">
                    <canvas id="salesChart"></canvas>
                </div>
            </div>

            <!-- Keywords List -->
            <div class="card">
                <h2 class="card-title">💡 Google Ads Keyword Planner Suggestions & Dampening Ratios</h2>
                <div class="table-wrapper">
                    <table class="table">
                        <thead>
                            <tr>
                                <th>Keyword Suggestion</th>
                                <th>Google Vol (Ads API)</th>
                                <th>Amazon Buy Intent Ratio</th>
                                <th>Before Vol (Inflated)</th>
                                <th>After Vol (Damped)</th>
                                <th>Expected CPC Bid</th>
                            </tr>
                        </thead>
                        <tbody id="keyword-rows">
                            <tr>
                                <td colspan="6" style="text-align: center; color: var(--gray);">No data loaded.</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </main>

    <script>
        let salesChart = null;

        // Initialize empty chart
        function initChart(labels = [], dataPoints = []) {
            const ctx = document.getElementById('salesChart').getContext('2d');
            
            if (salesChart) {
                salesChart.destroy();
            }

            salesChart = new Chart(ctx, {
                type: 'line',
                data: {
                    labels: labels,
                    datasets: [{
                        label: 'Seasonality-Adjusted Monthly Sales (Estimated)',
                        data: dataPoints,
                        borderColor: '#f08804',
                        backgroundColor: 'rgba(240, 136, 4, 0.05)',
                        borderWidth: 3,
                        tension: 0.4,
                        fill: true,
                        pointBackgroundColor: '#007185',
                        pointBorderColor: '#ffffff',
                        pointHoverRadius: 8,
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    scales: {
                        y: {
                            beginAtZero: true,
                            grid: {
                                color: 'rgba(0, 0, 0, 0.04)'
                            }
                        },
                        x: {
                            grid: {
                                display: false
                            }
                        }
                    },
                    plugins: {
                        legend: {
                            display: false
                        }
                    }
                }
            });
        }

        async function runSimulation() {
            const form = document.getElementById('simulate-form');
            const loader = document.getElementById('form-loader');
            loader.style.display = 'flex';

            const formData = new FormData(form);
            const banner = document.getElementById('api-status-banner');

            try {
                const response = await fetch('/admin/google-keyword-planner/simulate', {
                    method: 'POST',
                    body: formData,
                    headers: {
                        'X-Requested-With': 'XMLHttpRequest'
                    }
                });

                const res = await response.json();
                loader.style.display = 'none';

                if (res.success) {
                    banner.className = 'alert-info';
                    banner.innerHTML = '✅ Connected to Google Ads API successfully! Calculations computed using real search volumes.';

                    // Update sales summary card value if it was estimated
                    if (res.is_sales_estimated) {
                        document.getElementById('summary-sales').innerText = Number(res.estimated_sales).toLocaleString();
                    }

                    // Pre-fill metrics
                    document.getElementById('val-before').innerText = Number(res.before_amazon_volume).toLocaleString();
                    document.getElementById('val-after').innerText = Number(res.keywords[0].after_volume).toLocaleString();

                    // Render table rows
                    const tbody = document.getElementById('keyword-rows');
                    tbody.innerHTML = '';

                    res.keywords.forEach(kw => {
                        const tr = document.createElement('tr');
                        tr.innerHTML = `
                            <td style="font-weight: 600; color: var(--secondary);">${kw.keyword}</td>
                            <td>${Number(kw.google_volume).toLocaleString()}</td>
                            <td><span class="badge ${parseFloat(kw.amazon_intent_ratio) > 15 ? 'badge-success' : 'badge-warning'}">${kw.amazon_intent_ratio}</span></td>
                            <td style="text-decoration: line-through; color: var(--gray);">${Number(kw.before_volume).toLocaleString()}</td>
                            <td style="font-weight: 700; color: var(--primary-dark);">${Number(kw.after_volume).toLocaleString()}</td>
                            <td style="font-weight: 600;">$${kw.suggested_bid.toFixed(2)}</td>
                        `;
                        tbody.appendChild(tr);
                    });

                    // Render chart
                    const chartLabels = res.projections.map(p => p.month);
                    const chartData = res.projections.map(p => p.expected_sales);
                    initChart(chartLabels, chartData);

                } else {
                    banner.className = 'alert-danger';
                    banner.innerHTML = '⚠️ ' + (res.error || 'Calculations failed to run on the server.');
                }
            } catch (err) {
                loader.style.display = 'none';
                console.error(err);
                banner.className = 'alert-danger';
                banner.innerHTML = '⚠️ Connection error occurred while processing algorithms.';
            }
        }

        // Run automatically when DOM loaded
        window.addEventListener('DOMContentLoaded', () => {
            initChart(['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec'], [0,0,0,0,0,0,0,0,0,0,0,0]);
            runSimulation();
        });
    </script>
</body>
</html>
