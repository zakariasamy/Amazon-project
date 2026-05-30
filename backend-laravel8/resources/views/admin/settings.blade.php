<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Settings - Amazon Product Analyzer</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <style>
        :root {
            --bg: #0f172a;
            --panel: #1e293b;
            --line: rgba(255,255,255,0.1);
            --text: #fff;
            --muted: #94a3b8;
            --primary: #6366f1;
            --success: #10b981;
        }

        * { box-sizing: border-box; }

        body {
            margin: 0;
            min-height: 100vh;
            font-family: Inter, -apple-system, BlinkMacSystemFont, sans-serif;
            background: var(--bg);
            color: var(--text);
        }

        .layout {
            max-width: 980px;
            margin: 0 auto;
            padding: 40px 24px;
        }

        .topbar {
            display: flex;
            justify-content: space-between;
            align-items: center;
            gap: 16px;
            margin-bottom: 28px;
        }

        h1 {
            margin: 0 0 8px;
            font-size: 30px;
            letter-spacing: 0;
        }

        .subtitle {
            color: var(--muted);
            margin: 0;
        }

        .back {
            color: var(--text);
            text-decoration: none;
            border: 1px solid var(--line);
            border-radius: 8px;
            padding: 10px 14px;
            font-weight: 600;
        }

        .notice {
            background: rgba(16,185,129,0.12);
            border: 1px solid rgba(16,185,129,0.35);
            color: #86efac;
            border-radius: 8px;
            padding: 12px 14px;
            margin-bottom: 18px;
        }

        form {
            background: var(--panel);
            border: 1px solid var(--line);
            border-radius: 8px;
            overflow: hidden;
        }

        .setting {
            display: grid;
            grid-template-columns: 1fr 220px;
            gap: 24px;
            padding: 20px;
            border-bottom: 1px solid var(--line);
            align-items: center;
        }

        .group-title {
            padding: 18px 20px;
            background: rgba(99,102,241,0.14);
            border-bottom: 1px solid var(--line);
            font-weight: 800;
            color: #c7d2fe;
        }

        .setting:last-of-type { border-bottom: 0; }

        .label {
            font-weight: 700;
            margin-bottom: 6px;
        }

        .description {
            color: var(--muted);
            font-size: 14px;
            line-height: 1.5;
        }

        input[type="number"],
        input[type="text"] {
            width: 100%;
            padding: 11px 12px;
            border: 1px solid var(--line);
            border-radius: 8px;
            background: #0f172a;
            color: var(--text);
            font: inherit;
        }

        .toggle {
            display: flex;
            justify-content: flex-end;
            align-items: center;
            gap: 10px;
            font-weight: 600;
            color: var(--muted);
        }

        .toggle input {
            width: 22px;
            height: 22px;
            accent-color: var(--success);
        }

        .actions {
            display: flex;
            justify-content: flex-end;
            padding: 20px;
            border-top: 1px solid var(--line);
        }

        button {
            background: var(--primary);
            color: var(--text);
            border: 0;
            border-radius: 8px;
            padding: 12px 18px;
            font-weight: 700;
            cursor: pointer;
        }

        @media (max-width: 720px) {
            .topbar,
            .setting {
                grid-template-columns: 1fr;
                display: grid;
            }

            .toggle {
                justify-content: flex-start;
            }
        }
    </style>
</head>
<body>
    <main class="layout">
        <div class="topbar">
            <div>
                <h1>Admin Settings</h1>
                <p class="subtitle">Control heavier Amazon scraping behavior from one place.</p>
            </div>
            <a class="back" href="/dashboard">Dashboard</a>
        </div>

        @if (session('status'))
            <div class="notice">{{ session('status') }}</div>
        @endif

        <form method="POST" action="{{ route('admin.settings.update') }}">
            @csrf

            <div class="group-title">Market Analysis</div>

            @foreach ([
                'search_page_products_limit' => ['label' => 'BSR products per search page', 'min' => 0],
                'search_page_bsr_parallel_requests' => ['label' => 'Parallel BSR fetches', 'min' => 1],
                'search_page_bsr_delay_ms' => ['label' => 'BSR batch delay ms', 'min' => 0],
            ] as $key => $meta)
                <div class="setting">
                    <div>
                        <div class="label">{{ $meta['label'] }}</div>
                        <div class="description">{{ $settings[$key]['description'] }}</div>
                        @error($key)<div class="description">{{ $message }}</div>@enderror
                    </div>
                    <input type="number" name="{{ $key }}" value="{{ old($key, $settings[$key]['value']) }}" min="{{ $meta['min'] }}">
                </div>
            @endforeach

            <div class="group-title">Competitor Keyword Analyzer</div>

            <div class="setting">
                <div>
                    <div class="label">Fetch BSR for Competitor Keyword Analyzer</div>
                    <div class="description">{{ $settings['cerebro_fetch_bsr_enabled']['description'] }}</div>
                    @error('cerebro_fetch_bsr_enabled')<div class="description">{{ $message }}</div>@enderror
                </div>
                <label class="toggle">
                    <input type="checkbox" name="cerebro_fetch_bsr_enabled" value="1" {{ $settings['cerebro_fetch_bsr_enabled']['value'] ? 'checked' : '' }}>
                    Enabled
                </label>
            </div>

            <div class="setting">
                <div>
                    <div class="label">Use Backend Cached Search Volume</div>
                    <div class="description">{{ $settings['cerebro_use_backend_cache']['description'] }}</div>
                    @error('cerebro_use_backend_cache')<div class="description">{{ $message }}</div>@enderror
                </div>
                <label class="toggle">
                    <input type="checkbox" name="cerebro_use_backend_cache" value="1" {{ $settings['cerebro_use_backend_cache']['value'] ? 'checked' : '' }}>
                    Enabled
                </label>
            </div>

            @foreach ([
                'cerebro_bsr_products_limit' => ['label' => 'BSR products per keyword', 'min' => 0],
                'cerebro_bsr_parallel_requests' => ['label' => 'Parallel BSR fetches', 'min' => 1],
                'cerebro_bsr_delay_ms' => ['label' => 'BSR batch delay ms', 'min' => 0],
                'cerebro_search_delay_ms' => ['label' => 'Keyword search delay ms', 'min' => 0],
                'cerebro_parallel_keywords' => ['label' => 'Parallel keyword searches', 'min' => 1],
            ] as $key => $meta)
                <div class="setting">
                    <div>
                        <div class="label">{{ $meta['label'] }}</div>
                        <div class="description">{{ $settings[$key]['description'] }}</div>
                        @error($key)<div class="description">{{ $message }}</div>@enderror
                    </div>
                    <input type="number" name="{{ $key }}" value="{{ old($key, $settings[$key]['value']) }}" min="{{ $meta['min'] }}">
                </div>
            @endforeach

            <div class="group-title">Reverse ASIN</div>

            @foreach ([
                'reverse_asin_products_limit' => ['label' => 'BSR products per ASIN search', 'min' => 0],
                'reverse_asin_bsr_parallel_requests' => ['label' => 'Parallel BSR fetches', 'min' => 1],
                'reverse_asin_bsr_delay_ms' => ['label' => 'BSR batch delay ms', 'min' => 0],
                'reverse_asin_keywords_limit' => ['label' => 'Competitor keywords limit', 'min' => 1],
                'reverse_asin_search_delay_ms' => ['label' => 'Keyword search delay ms', 'min' => 0],
                'reverse_asin_backend_batch_size' => ['label' => 'Backend batch process size', 'min' => 1],
            ] as $key => $meta)
                <div class="setting">
                    <div>
                        <div class="label">{{ $meta['label'] }}</div>
                        <div class="description">{{ $settings[$key]['description'] }}</div>
                        @error($key)<div class="description" style="color: #ef4444;">{{ $message }}</div>@enderror
                    </div>
                    <input type="number" name="{{ $key }}" value="{{ old($key, $settings[$key]['value']) }}" min="{{ $meta['min'] }}">
                </div>
            @endforeach

            <div class="group-title">Test Mode Configuration</div>

            <div class="setting">
                <div>
                    <div class="label">Enable Test Mode</div>
                    <div class="description">Enable Test Mode to override analysis tools (Competitor Keyword Analyzer, Reverse ASIN, and Market Analysis) to focus on a single specific keyword and product.</div>
                    @error('test_mode_enabled')<div class="description" style="color: #ef4444;">{{ $message }}</div>@enderror
                </div>
                <label class="toggle">
                    <input type="checkbox" name="test_mode_enabled" value="1" {{ $settings['test_mode_enabled']['value'] ? 'checked' : '' }}>
                    Enabled
                </label>
            </div>

            <div class="setting">
                <div>
                    <div class="label">Test Mode Keyword</div>
                    <div class="description">The specific seed keyword to use in test mode (by default: portal scale body).</div>
                    @error('test_mode_keyword')<div class="description" style="color: #ef4444;">{{ $message }}</div>@enderror
                </div>
                <input type="text" name="test_mode_keyword" value="{{ old('test_mode_keyword', $settings['test_mode_keyword']['value']) }}">
            </div>

            <div class="setting">
                <div>
                    <div class="label">Test Mode Product URL</div>
                    <div class="description">The Amazon product URL to force analysis for. Competitor Keyword Analyzer and Reverse ASIN will analyze this product (and its ASIN).</div>
                    @error('test_mode_product_url')<div class="description" style="color: #ef4444;">{{ $message }}</div>@enderror
                </div>
                <input type="text" name="test_mode_product_url" value="{{ old('test_mode_product_url', $settings['test_mode_product_url']['value']) }}">
            </div>

            <div class="actions">
                <button type="submit">Save Settings</button>
            </div>
        </form>
    </main>
</body>
</html>
