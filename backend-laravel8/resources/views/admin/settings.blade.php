<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Settings - Amazon Product Analyzer</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <style>
        :root {
            --bg: #f8fafc;
            --panel: #ffffff;
            --line: rgba(0, 0, 0, 0.08);
            --text: #0f172a;
            --muted: #475569;
            --primary: #6366f1;
            --primary-hover: #4f46e5;
            --success: #10b981;
            --warning: #f59e0b;
        }

        * { box-sizing: border-box; }

        body {
            margin: 0;
            min-height: 100vh;
            font-family: 'Inter', -apple-system, BlinkMacSystemFont, sans-serif;
            background: var(--bg);
            color: var(--text);
            -webkit-font-smoothing: antialiased;
        }

        .layout {
            max-width: 1000px;
            margin: 0 auto;
            padding: 40px 24px;
        }

        .topbar {
            display: flex;
            justify-content: space-between;
            align-items: center;
            gap: 16px;
            margin-bottom: 32px;
        }

        h1 {
            margin: 0 0 6px;
            font-size: 32px;
            font-weight: 800;
            letter-spacing: -0.5px;
            background: linear-gradient(135deg, #0f172a 0%, #475569 100%);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
        }

        .subtitle {
            color: var(--muted);
            margin: 0;
            font-size: 15px;
        }

        .back {
            color: var(--text);
            text-decoration: none;
            border: 1px solid rgba(0, 0, 0, 0.15);
            border-radius: 10px;
            padding: 10px 18px;
            font-weight: 600;
            font-size: 14px;
            transition: all 0.2s;
            background: rgba(0, 0, 0, 0.02);
        }

        .back:hover {
            background: rgba(0, 0, 0, 0.04);
            border-color: rgba(0, 0, 0, 0.25);
        }

        .notice {
            background: rgba(16,185,129,0.12);
            border: 1px solid rgba(16,185,129,0.35);
            color: #86efac;
            border-radius: 10px;
            padding: 14px 18px;
            margin-bottom: 24px;
            font-weight: 500;
            font-size: 14px;
            box-shadow: 0 4px 12px rgba(16, 185, 129, 0.1);
        }

        /* Modern Tabs Design */
        .tabs-bar {
            display: flex;
            gap: 6px;
            margin-bottom: 24px;
            overflow-x: auto;
            padding: 4px;
            background: rgba(226, 232, 240, 0.8);
            border-radius: 12px;
            border: 1px solid var(--line);
            scrollbar-width: none;
        }

        .tabs-bar::-webkit-scrollbar {
            display: none;
        }

        .tab-btn {
            background: transparent;
            border: none;
            color: var(--muted);
            padding: 12px 20px;
            border-radius: 8px;
            font-weight: 600;
            font-size: 14px;
            cursor: pointer;
            white-space: nowrap;
            transition: all 0.2s ease-in-out;
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .tab-btn:hover {
            color: var(--text);
            background: rgba(0, 0, 0, 0.04);
        }

        .tab-btn.active {
            background: var(--primary);
            color: #fff;
            box-shadow: 0 4px 14px rgba(99, 102, 241, 0.35);
        }

        /* Settings Card Form */
        form {
            background: var(--panel);
            border: 1px solid var(--line);
            border-radius: 14px;
            overflow: hidden;
            box-shadow: 0 10px 30px rgba(0,0,0,0.05);
        }

        .tab-panel {
            display: none;
            animation: panelFade 0.2s ease-out;
        }

        .tab-panel.active {
            display: block;
        }

        @keyframes panelFade {
            from { opacity: 0; transform: translateY(4px); }
            to { opacity: 1; transform: translateY(0); }
        }

        .panel-header {
            padding: 20px 24px;
            background: rgba(0, 0, 0, 0.01);
            border-bottom: 1px solid var(--line);
            display: flex;
            justify-content: space-between;
            align-items: center;
            flex-wrap: wrap;
            gap: 12px;
        }

        .panel-title {
            font-size: 18px;
            font-weight: 800;
            color: var(--primary);
            margin: 0;
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .quick-actions {
            display: flex;
            gap: 8px;
        }

        .btn-action {
            background: rgba(0, 0, 0, 0.04);
            border: 1px solid rgba(0, 0, 0, 0.1);
            color: var(--muted);
            font-size: 12px;
            font-weight: 600;
            padding: 6px 12px;
            border-radius: 6px;
            cursor: pointer;
            transition: all 0.15s;
        }

        .btn-action:hover {
            color: var(--text);
            background: rgba(0, 0, 0, 0.08);
            border-color: rgba(0, 0, 0, 0.2);
        }

        .setting {
            display: grid;
            grid-template-columns: 1fr 180px;
            gap: 32px;
            padding: 24px;
            border-bottom: 1px solid var(--line);
            align-items: center;
        }

        .setting:last-of-type { border-bottom: 0; }

        .label {
            font-weight: 700;
            font-size: 15px;
            margin-bottom: 6px;
            color: var(--text);
        }

        .description {
            color: var(--muted);
            font-size: 13.5px;
            line-height: 1.5;
        }

        .error-msg {
            color: #ef4444;
            font-size: 12px;
            margin-top: 4px;
            font-weight: 600;
        }

        input[type="number"],
        input[type="text"] {
            width: 100%;
            padding: 10px 14px;
            border: 1px solid #cbd5e1;
            border-radius: 8px;
            background: #ffffff;
            color: var(--text);
            font: inherit;
            font-size: 14px;
            transition: all 0.2s;
        }

        input[type="number"]:focus,
        input[type="text"]:focus {
            outline: none;
            border-color: var(--primary);
            box-shadow: 0 0 0 3px rgba(99, 102, 241, 0.2);
        }

        /* Beautiful Toggle Switch Design */
        .switch {
            position: relative;
            display: inline-block;
            width: 52px;
            height: 26px;
            justify-self: end;
        }

        .switch input {
            opacity: 0;
            width: 0;
            height: 0;
        }

        .slider {
            position: absolute;
            cursor: pointer;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background-color: #cbd5e1;
            transition: .25s ease;
            border-radius: 34px;
            border: 1px solid rgba(0,0,0,0.05);
        }

        .slider:before {
            position: absolute;
            content: "";
            height: 18px;
            width: 18px;
            left: 3px;
            bottom: 3px;
            background-color: #f8fafc;
            transition: .25s ease;
            border-radius: 50%;
            box-shadow: 0 2px 4px rgba(0,0,0,0.2);
        }

        input:checked + .slider {
            background-color: var(--success);
        }

        input:focus + .slider {
            box-shadow: 0 0 1px var(--success);
        }

        input:checked + .slider:before {
            transform: translateX(26px);
        }

        /* Action bar footer */
        .actions {
            display: flex;
            justify-content: flex-end;
            padding: 20px 24px;
            background: rgba(0, 0, 0, 0.03);
            border-top: 1px solid var(--line);
        }

        .btn-save {
            background: var(--primary);
            color: #fff;
            border: 0;
            border-radius: 8px;
            padding: 12px 24px;
            font-weight: 700;
            font-size: 14px;
            cursor: pointer;
            transition: all 0.2s;
            box-shadow: 0 4px 14px rgba(99, 102, 241, 0.3);
        }

        .btn-save:hover {
            background: var(--primary-hover);
            transform: translateY(-1px);
            box-shadow: 0 6px 18px rgba(99, 102, 241, 0.45);
        }

        .btn-save:active {
            transform: translateY(0);
        }

        @media (max-width: 768px) {
            .topbar {
                flex-direction: column;
                align-items: flex-start;
                gap: 12px;
            }

            .back {
                align-self: flex-start;
            }

            .setting {
                grid-template-columns: 1fr;
                gap: 16px;
            }

            .switch {
                justify-self: start;
            }
        }
    </style>
</head>
<body>
    <main class="layout">
        <div class="topbar">
            <div>
                <h1>Admin Settings</h1>
                <p class="subtitle">Fine-tune feature permissions, scraper delay limits, and testing behaviors.</p>
            </div>
            <a class="back" href="/dashboard">← Back to Dashboard</a>
        </div>

        @if (session('status'))
            <div class="notice">✨ {{ session('status') }}</div>
        @endif

        <!-- Tabs Switcher -->
        <div class="tabs-bar">
            <button type="button" class="tab-btn active" data-tab="features">🛡️ Feature Toggles</button>
            <button type="button" class="tab-btn" data-tab="market">📊 Market Analysis</button>
            <button type="button" class="tab-btn" data-tab="analyzer">🧠 Competitor Keyword Analyzer</button>
            <button type="button" class="tab-btn" data-tab="reverse">🔑 Reverse ASIN</button>
            <button type="button" class="tab-btn" data-tab="magnet">🧲 Keyword Magnet</button>
            <button type="button" class="tab-btn" data-tab="testmode">🛠️ Test Mode</button>
        </div>

        <form method="POST" action="{{ route('admin.settings.update') }}">
            @csrf

            <!-- ================= TAB 1: FEATURE TOGGLES ================= -->
            <div class="tab-panel active" id="panel-features">
                <div class="panel-header">
                    <h2 class="panel-title">🛡️ Feature Toggles</h2>
                    <div class="quick-actions">
                        <button type="button" class="btn-action" onclick="toggleAllFeatures(true)">Enable All</button>
                        <button type="button" class="btn-action" onclick="toggleAllFeatures(false)">Disable All</button>
                    </div>
                </div>

                <div class="setting">
                    <div>
                        <div class="label">Market Analysis</div>
                        <div class="description">{{ $settings['feature_market_analysis_enabled']['description'] }}</div>
                        @error('feature_market_analysis_enabled')<div class="error-msg">{{ $message }}</div>@enderror
                    </div>
                    <label class="switch">
                        <input type="checkbox" class="feature-toggle" name="feature_market_analysis_enabled" value="1" {{ $settings['feature_market_analysis_enabled']['value'] ? 'checked' : '' }}>
                        <span class="slider"></span>
                    </label>
                </div>

                <div class="setting">
                    <div>
                        <div class="label">Competitor Keyword Analyzer (Keyword Analyzer Pro)</div>
                        <div class="description">{{ $settings['feature_keyword_analyzer_pro_enabled']['description'] }}</div>
                        @error('feature_keyword_analyzer_pro_enabled')<div class="error-msg">{{ $message }}</div>@enderror
                    </div>
                    <label class="switch">
                        <input type="checkbox" class="feature-toggle" name="feature_keyword_analyzer_pro_enabled" value="1" {{ $settings['feature_keyword_analyzer_pro_enabled']['value'] ? 'checked' : '' }}>
                        <span class="slider"></span>
                    </label>
                </div>

                <div class="setting">
                    <div>
                        <div class="label">Analyze Product</div>
                        <div class="description">{{ $settings['feature_analyze_product_enabled']['description'] }}</div>
                        @error('feature_analyze_product_enabled')<div class="error-msg">{{ $message }}</div>@enderror
                    </div>
                    <label class="switch">
                        <input type="checkbox" class="feature-toggle" name="feature_analyze_product_enabled" value="1" {{ $settings['feature_analyze_product_enabled']['value'] ? 'checked' : '' }}>
                        <span class="slider"></span>
                    </label>
                </div>

                <div class="setting">
                    <div>
                        <div class="label">Reverse ASIN</div>
                        <div class="description">{{ $settings['feature_reverse_asin_enabled']['description'] }}</div>
                        @error('feature_reverse_asin_enabled')<div class="error-msg">{{ $message }}</div>@enderror
                    </div>
                    <label class="switch">
                        <input type="checkbox" class="feature-toggle" name="feature_reverse_asin_enabled" value="1" {{ $settings['feature_reverse_asin_enabled']['value'] ? 'checked' : '' }}>
                        <span class="slider"></span>
                    </label>
                </div>

                <div class="setting">
                    <div>
                        <div class="label">FBA Calculator</div>
                        <div class="description">{{ $settings['feature_fba_calculator_enabled']['description'] }}</div>
                        @error('feature_fba_calculator_enabled')<div class="error-msg">{{ $message }}</div>@enderror
                    </div>
                    <label class="switch">
                        <input type="checkbox" class="feature-toggle" name="feature_fba_calculator_enabled" value="1" {{ $settings['feature_fba_calculator_enabled']['value'] ? 'checked' : '' }}>
                        <span class="slider"></span>
                    </label>
                </div>

                <div class="setting">
                    <div>
                        <div class="label">Keyword Magnet</div>
                        <div class="description">{{ $settings['feature_keyword_magnet_enabled']['description'] }}</div>
                        @error('feature_keyword_magnet_enabled')<div class="error-msg">{{ $message }}</div>@enderror
                    </div>
                    <label class="switch">
                        <input type="checkbox" class="feature-toggle" name="feature_keyword_magnet_enabled" value="1" {{ $settings['feature_keyword_magnet_enabled']['value'] ? 'checked' : '' }}>
                        <span class="slider"></span>
                    </label>
                </div>
            </div>

            <!-- ================= TAB 2: MARKET ANALYSIS ================= -->
            <div class="tab-panel" id="panel-market">
                <div class="panel-header">
                    <h2 class="panel-title">📊 Market Analysis scraper tuning</h2>
                </div>

                @foreach ([
                    'search_page_products_limit' => ['label' => 'BSR Products Limit', 'min' => 0],
                    'search_page_bsr_parallel_requests' => ['label' => 'Parallel BSR Fetches', 'min' => 1],
                    'search_page_bsr_delay_ms' => ['label' => 'Batch Delay (ms)', 'min' => 0],
                ] as $key => $meta)
                    <div class="setting">
                        <div>
                            <div class="label">{{ $meta['label'] }}</div>
                            <div class="description">{{ $settings[$key]['description'] }}</div>
                            @error($key)<div class="error-msg">{{ $message }}</div>@enderror
                        </div>
                        <input type="number" name="{{ $key }}" value="{{ old($key, $settings[$key]['value']) }}" min="{{ $meta['min'] }}">
                    </div>
                @endforeach
            </div>

            <!-- ================= TAB 3: KEYWORD ANALYZER ================= -->
            <div class="tab-panel" id="panel-analyzer">
                <div class="panel-header">
                    <h2 class="panel-title">🧠 Competitor Keyword Analyzer</h2>
                </div>

                <div class="setting">
                    <div>
                        <div class="label">Fetch BSR for Competitor Keyword Analyzer</div>
                        <div class="description">{{ $settings['cerebro_fetch_bsr_enabled']['description'] }}</div>
                        @error('cerebro_fetch_bsr_enabled')<div class="error-msg">{{ $message }}</div>@enderror
                    </div>
                    <label class="switch">
                        <input type="checkbox" name="cerebro_fetch_bsr_enabled" value="1" {{ $settings['cerebro_fetch_bsr_enabled']['value'] ? 'checked' : '' }}>
                        <span class="slider"></span>
                    </label>
                </div>

                <div class="setting">
                    <div>
                        <div class="label">Use Backend Cached Search Volume</div>
                        <div class="description">{{ $settings['cerebro_use_backend_cache']['description'] }}</div>
                        @error('cerebro_use_backend_cache')<div class="error-msg">{{ $message }}</div>@enderror
                    </div>
                    <label class="switch">
                        <input type="checkbox" name="cerebro_use_backend_cache" value="1" {{ $settings['cerebro_use_backend_cache']['value'] ? 'checked' : '' }}>
                        <span class="slider"></span>
                    </label>
                </div>

                @foreach ([
                    'cerebro_bsr_products_limit' => ['label' => 'BSR Products Limit', 'min' => 0],
                    'cerebro_bsr_parallel_requests' => ['label' => 'Parallel BSR Fetches', 'min' => 1],
                    'cerebro_bsr_delay_ms' => ['label' => 'Batch Delay (ms)', 'min' => 0],
                    'cerebro_search_delay_ms' => ['label' => 'Search Request Delay (ms)', 'min' => 0],
                    'cerebro_parallel_keywords' => ['label' => 'Parallel Keyword Scrapes', 'min' => 1],
                ] as $key => $meta)
                    <div class="setting">
                        <div>
                            <div class="label">{{ $meta['label'] }}</div>
                            <div class="description">{{ $settings[$key]['description'] }}</div>
                            @error($key)<div class="error-msg">{{ $message }}</div>@enderror
                        </div>
                        <input type="number" name="{{ $key }}" value="{{ old($key, $settings[$key]['value']) }}" min="{{ $meta['min'] }}">
                    </div>
                @endforeach
            </div>

            <!-- ================= TAB 4: REVERSE ASIN ================= -->
            <div class="tab-panel" id="panel-reverse">
                <div class="panel-header">
                    <h2 class="panel-title">🔑 Reverse ASIN Config</h2>
                </div>

                @foreach ([
                    'reverse_asin_products_limit' => ['label' => 'BSR Products Limit', 'min' => 0],
                    'reverse_asin_bsr_parallel_requests' => ['label' => 'Parallel BSR Fetches', 'min' => 1],
                    'reverse_asin_bsr_delay_ms' => ['label' => 'Batch Delay (ms)', 'min' => 0],
                    'reverse_asin_keywords_limit' => ['label' => 'Competitor Keywords Limit', 'min' => 1],
                    'reverse_asin_search_delay_ms' => ['label' => 'Search Request Delay (ms)', 'min' => 0],
                    'reverse_asin_backend_batch_size' => ['label' => 'Backend Batch Processing Size', 'min' => 1],
                ] as $key => $meta)
                    <div class="setting">
                        <div>
                            <div class="label">{{ $meta['label'] }}</div>
                            <div class="description">{{ $settings[$key]['description'] }}</div>
                            @error($key)<div class="error-msg">{{ $message }}</div>@enderror
                        </div>
                        <input type="number" name="{{ $key }}" value="{{ old($key, $settings[$key]['value']) }}" min="{{ $meta['min'] }}">
                    </div>
                @endforeach
            </div>

            <!-- ================= TAB 5: KEYWORD MAGNET ================= -->
            <div class="tab-panel" id="panel-magnet">
                <div class="panel-header">
                    <h2 class="panel-title">🧲 Keyword Magnet Configuration</h2>
                </div>

                @foreach ([
                    'attribute_product_count' => ['label' => 'Attribute Product Count', 'min' => 0],
                    'max_keywords_limit' => ['label' => 'Max Keywords Limit', 'min' => 10],
                    'delay_between_requests' => ['label' => 'Scraping Request Delay (ms)', 'min' => 0],
                    'attribute_variation_limit' => ['label' => 'Attribute Variation Limit', 'min' => 1],
                ] as $key => $meta)
                    <div class="setting">
                        <div>
                            <div class="label">{{ $meta['label'] }}</div>
                            <div class="description">{{ $magnetSettings[$key]['description'] }}</div>
                            @error($key)<div class="error-msg">{{ $message }}</div>@enderror
                        </div>
                        <input type="number" name="{{ $key }}" value="{{ old($key, $magnetSettings[$key]['value']) }}" min="{{ $meta['min'] }}">
                    </div>
                @endforeach

                <div class="setting">
                    <div>
                        <div class="label">Attribute Variation Scope</div>
                        <div class="description">{{ $magnetSettings['attribute_variation_scope']['description'] }}</div>
                        @error('attribute_variation_scope')<div class="error-msg">{{ $message }}</div>@enderror
                    </div>
                    <select name="attribute_variation_scope" style="width: 100%; padding: 10px 14px; border: 1px solid #cbd5e1; border-radius: 8px; background: #ffffff; color: var(--text); font-size: 14px;">
                        <option value="seed" {{ old('attribute_variation_scope', $magnetSettings['attribute_variation_scope']['value']) === 'seed' ? 'selected' : '' }}>Seed Keyword Only</option>
                        <option value="top_n" {{ old('attribute_variation_scope', $magnetSettings['attribute_variation_scope']['value']) === 'top_n' ? 'selected' : '' }}>Top N Organic Results</option>
                        <option value="all" {{ old('attribute_variation_scope', $magnetSettings['attribute_variation_scope']['value']) === 'all' ? 'selected' : '' }}>All Scraped Results</option>
                    </select>
                </div>

                @foreach ([
                    'use_autocomplete' => 'Use Amazon Autocomplete',
                    'use_related' => 'Use Related Keywords',
                    'use_titles' => 'Use Search Result Titles',
                    'use_attributes' => 'Use Product Attributes',
                    'use_google_suggestions' => 'Use Google Autocomplete',
                    'use_bing_suggestions' => 'Use Bing Autocomplete',
                    'use_youtube_suggestions' => 'Use YouTube Autocomplete',
                ] as $key => $label)
                    <div class="setting">
                        <div>
                            <div class="label">{{ $label }}</div>
                            <div class="description">{{ $magnetSettings[$key]['description'] }}</div>
                            @error($key)<div class="error-msg">{{ $message }}</div>@enderror
                        </div>
                        <label class="switch">
                            <input type="checkbox" name="{{ $key }}" value="1" {{ $magnetSettings[$key]['value'] ? 'checked' : '' }}>
                            <span class="slider"></span>
                        </label>
                    </div>
                @endforeach
            </div>

            <!-- ================= TAB 6: TEST MODE ================= -->
            <div class="tab-panel" id="panel-testmode">
                <div class="panel-header">
                    <h2 class="panel-title">🛠️ Test Mode Configuration</h2>
                </div>

                <div class="setting">
                    <div>
                        <div class="label">Enable Test Mode</div>
                        <div class="description">Enable Test Mode to override analysis tools (Competitor Keyword Analyzer, Reverse ASIN, Market Analysis, and Keyword Magnet) with a fixed keyword and product.</div>
                        @error('test_mode_enabled')<div class="error-msg">{{ $message }}</div>@enderror
                    </div>
                    <label class="switch">
                        <input type="checkbox" name="test_mode_enabled" value="1" {{ $settings['test_mode_enabled']['value'] ? 'checked' : '' }}>
                        <span class="slider"></span>
                    </label>
                </div>

                <div class="setting">
                    <div>
                        <div class="label">Test Mode Keyword</div>
                        <div class="description">The specific seed keyword to force-use when Test Mode is enabled (default: portal scale body).</div>
                        @error('test_mode_keyword')<div class="error-msg">{{ $message }}</div>@enderror
                    </div>
                    <input type="text" name="test_mode_keyword" value="{{ old('test_mode_keyword', $settings['test_mode_keyword']['value']) }}">
                </div>

                <div class="setting">
                    <div>
                        <div class="label">Test Mode Product URL</div>
                        <div class="description">The Amazon product URL to force-use when Test Mode is enabled.</div>
                        @error('test_mode_product_url')<div class="error-msg">{{ $message }}</div>@enderror
                    </div>
                    <input type="text" name="test_mode_product_url" value="{{ old('test_mode_product_url', $settings['test_mode_product_url']['value']) }}">
                </div>
            </div>

            <!-- Save Action Bar Footer -->
            <div class="actions">
                <button type="submit" class="btn-save">Save Settings</button>
            </div>
        </form>
    </main>

    <!-- Tab selection vanilla JS script -->
    <script>
        document.querySelectorAll('.tab-btn').forEach(btn => {
            btn.addEventListener('click', () => {
                // Remove active classes
                document.querySelectorAll('.tab-btn').forEach(b => b.classList.remove('active'));
                document.querySelectorAll('.tab-panel').forEach(p => p.classList.remove('active'));

                // Add active class to clicked tab and target panel
                btn.classList.add('active');
                const targetId = 'panel-' + btn.dataset.tab;
                document.getElementById(targetId).classList.add('active');
            });
        });

        // Quick feature enable/disable actions
        function toggleAllFeatures(enable) {
            document.querySelectorAll('#panel-features .feature-toggle').forEach(input => {
                input.checked = enable;
            });
        }
    </script>
</body>
</html>
