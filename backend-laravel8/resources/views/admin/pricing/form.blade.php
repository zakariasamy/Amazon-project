<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $plan ? 'Edit Plan' : 'New Plan' }} — Admin</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <style>
        :root { --bg:#f8fafc; --panel:#ffffff; --line:rgba(0,0,0,0.08); --text:#0f172a; --muted:#475569; --primary:#f08804; --success:#10b981; --danger:#ef4444; }
        * { box-sizing: border-box; margin:0; padding:0; }
        body { font-family:'Inter',sans-serif; background:var(--bg); color:var(--text); min-height:100vh; }
        .layout { max-width:800px; margin:0 auto; padding:40px 24px; }
        .topbar { display:flex; justify-content:space-between; align-items:center; margin-bottom:32px; gap:16px; }
        h1 { font-size:1.75rem; font-weight:800; background:linear-gradient(135deg,#0f172a 0%,#475569 100%); -webkit-background-clip:text; -webkit-text-fill-color:transparent; }
        .back { color:var(--text); text-decoration:none; border:1px solid rgba(0,0,0,0.15); border-radius:10px; padding:10px 18px; font-weight:600; font-size:14px; transition:all 0.2s; }
        .back:hover { background:rgba(0,0,0,0.04); }
        .card { background:var(--panel); border:1px solid var(--line); border-radius:16px; padding:32px; }
        .section-head { font-size:13px; font-weight:700; color:var(--muted); text-transform:uppercase; letter-spacing:.05em; margin:0 0 16px; padding-bottom:8px; border-bottom:1px solid var(--line); }
        .grid2 { display:grid; grid-template-columns:1fr 1fr; gap:20px; }
        .grid3 { display:grid; grid-template-columns:1fr 1fr 1fr; gap:20px; }
        .form-group { display:flex; flex-direction:column; gap:6px; }
        label { font-size:13px; font-weight:600; color:var(--muted); }
        input,select,textarea { padding:10px 14px; border:1px solid rgba(0,0,0,0.15); border-radius:10px; font-size:14px; font-family:inherit; background:#fff; color:var(--text); transition:border 0.2s; width:100%; }
        input:focus,select:focus,textarea:focus { outline:none; border-color:var(--primary); box-shadow:0 0 0 3px rgba(240,136,4,0.12); }
        textarea { resize:vertical; min-height:80px; }
        .hint { font-size:11px; color:var(--muted); }
        .tools-grid { display:grid; grid-template-columns:repeat(auto-fill, minmax(200px,1fr)); gap:12px; }
        .tool-row { display:flex; flex-direction:column; gap:4px; }
        .tool-row label { font-size:12px; }
        .checkbox-row { display:flex; align-items:center; gap:8px; }
        .checkbox-row input[type=checkbox] { width:16px; height:16px; }
        hr { border:none; border-top:1px solid var(--line); margin:28px 0; }
        .actions { display:flex; gap:12px; justify-content:flex-end; margin-top:28px; }
        .btn { display:inline-flex; align-items:center; gap:6px; padding:12px 22px; border-radius:10px; font-weight:600; font-size:14px; text-decoration:none; border:none; cursor:pointer; transition:all 0.2s; }
        .btn-primary { background:#f08804; color:#fff; }
        .btn-primary:hover { background:#cc7203; transform:translateY(-1px); }
        .btn-outline { background:transparent; border:1px solid rgba(0,0,0,0.15); color:var(--text); }
        .btn-outline:hover { background:rgba(0,0,0,0.04); }
        .alert { padding:14px 18px; border-radius:10px; margin-bottom:24px; font-size:14px; font-weight:500; }
        .alert-error { background:#fee2e2; color:#991b1b; border:1px solid #fecaca; }
        ul.errors { margin:0; padding-left:16px; }
    </style>
</head>
<body>
<div class="layout">
    <div class="topbar">
        <h1>{{ $plan ? '✏️ Edit Plan: ' . $plan->name : '➕ New Pricing Plan' }}</h1>
        <a href="{{ route('admin.pricing.index') }}" class="back">← All Plans</a>
    </div>

    @if($errors->any())
    <div class="alert alert-error">
        <ul class="errors">@foreach($errors->all() as $e)<li>{{ $e }}</li>@endforeach</ul>
    </div>
    @endif

    <form method="POST" action="{{ $plan ? route('admin.pricing.update', $plan->id) : route('admin.pricing.store') }}">
        @csrf
        @if($plan) @method('PUT') @endif

        <div class="card">
            <p class="section-head">Plan Details</p>
            <div class="grid3" style="margin-bottom:20px;">
                <div class="form-group">
                    <label>Plan Name *</label>
                    <input type="text" name="name" value="{{ old('name', $plan?->name) }}" required placeholder="e.g. Pro">
                </div>
                <div class="form-group">
                    <label>Billing Cycle *</label>
                    <select name="billing_cycle">
                        <option value="monthly" {{ old('billing_cycle', $plan?->billing_cycle) === 'monthly' ? 'selected' : '' }}>Monthly</option>
                        <option value="yearly"  {{ old('billing_cycle', $plan?->billing_cycle) === 'yearly'  ? 'selected' : '' }}>Yearly</option>
                    </select>
                </div>
                <div class="form-group">
                    <label>Trial Duration (Days)</label>
                    <input type="number" name="trial_days" min="0" value="{{ old('trial_days', $plan?->trial_days ?? 0) }}" placeholder="e.g. 7 or 14. 0 for no trial.">
                </div>
            </div>
            <div class="form-group" style="margin-bottom:20px;">
                <label>Description</label>
                <textarea name="description" placeholder="Short tagline shown on pricing card">{{ old('description', $plan?->description) }}</textarea>
            </div>
            <div class="grid3" style="margin-bottom:20px;">
                <div class="form-group">
                    <label>Price (USD) *</label>
                    <input type="number" name="price" step="0.01" min="0" value="{{ old('price', $plan?->price ?? 0) }}" required>
                </div>
                <div class="form-group">
                    <label>Sort Order</label>
                    <input type="number" name="sort_order" min="0" value="{{ old('sort_order', $plan?->sort_order ?? 0) }}">
                </div>
                <div class="form-group" style="justify-content:flex-end;gap:16px;padding-top:4px;">
                    <div class="checkbox-row">
                        <input type="checkbox" name="is_active" value="1" id="is_active" {{ old('is_active', $plan?->is_active ?? true) ? 'checked' : '' }}>
                        <label for="is_active">Active (visible on homepage)</label>
                    </div>
                    <div class="checkbox-row">
                        <input type="checkbox" name="is_featured" value="1" id="is_featured" {{ old('is_featured', $plan?->is_featured) ? 'checked' : '' }}>
                        <label for="is_featured">Featured (POPULAR badge)</label>
                    </div>
                </div>
            </div>

            <hr>
            <p class="section-head">🔥 Promotional Offer (optional)</p>
            <div class="grid3">
                <div class="form-group">
                    <label>Promo Price (USD)</label>
                    <input type="number" name="promo_price" step="0.01" min="0" value="{{ old('promo_price', $plan?->promo_price) }}" placeholder="Leave blank to disable">
                </div>
                <div class="form-group">
                    <label>Promo Starts At</label>
                    <input type="datetime-local" name="promo_start_at" value="{{ old('promo_start_at', $plan?->promo_start_at?->format('Y-m-d\TH:i')) }}">
                </div>
                <div class="form-group">
                    <label>Promo Ends At</label>
                    <input type="datetime-local" name="promo_end_at" value="{{ old('promo_end_at', $plan?->promo_end_at?->format('Y-m-d\TH:i')) }}">
                </div>
            </div>
            <p class="hint" style="margin-top:8px;">If promo price is set with no dates, it will always be shown as the active price.</p>

            <hr>
            <p class="section-head">🛠 Monthly Tool Limits</p>
            <p class="hint" style="margin-bottom:16px;">Enter the monthly usage limit for each tool. Leave blank or enter -1 for Unlimited.</p>

            @php
                $tools = [
                    'market_analysis'  => 'Market Analysis',
                    'keyword_magnet'   => 'Keyword Magnet',
                    'reverse_asin'     => 'Reverse ASIN',
                    'fba_calculator'   => 'FBA Calculator',
                    'cerebro'          => 'Competitor Keyword Analyzer',
                    'analyze_product'  => 'Analyze Product',
                    'search_volume'    => 'Search Volume',
                ];
                $existingLimits = $plan?->limits ?? [];
            @endphp

            <div class="tools-grid">
                @foreach($tools as $key => $label)
                    @php
                        $val = old("limit_{$key}", isset($existingLimits[$key]) ? ($existingLimits[$key] === -1 ? '' : $existingLimits[$key]) : '');
                    @endphp
                    <div class="tool-row">
                        <label>{{ $label }}</label>
                        <input type="number" name="limit_{{ $key }}" value="{{ $val }}" min="-1" placeholder="Unlimited">
                    </div>
                @endforeach
            </div>
        </div>

        <div class="actions">
            <a href="{{ route('admin.pricing.index') }}" class="btn btn-outline">Cancel</a>
            <button type="submit" class="btn btn-primary">
                {{ $plan ? '💾 Save Changes' : '✅ Create Plan' }}
            </button>
        </div>
    </form>
</div>
</body>
</html>
