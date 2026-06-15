<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Pricing Plans — Admin</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <style>
        :root {
            --bg: #f8fafc; --panel: #ffffff; --line: rgba(0,0,0,0.08);
            --text: #0f172a; --muted: #475569;
            --primary: #f08804; --primary-hover: #cc7203;
            --success: #10b981; --warning: #f59e0b; --danger: #ef4444;
        }
        * { box-sizing: border-box; margin: 0; padding: 0; }
        body { font-family: 'Inter', sans-serif; background: var(--bg); color: var(--text); min-height: 100vh; }
        .layout { max-width: 1100px; margin: 0 auto; padding: 40px 24px; }
        .topbar { display: flex; justify-content: space-between; align-items: center; margin-bottom: 32px; gap: 16px; flex-wrap: wrap; }
        h1 { font-size: 2rem; font-weight: 800; letter-spacing: -0.5px; background: linear-gradient(135deg, #0f172a 0%, #475569 100%); -webkit-background-clip: text; -webkit-text-fill-color: transparent; }
        .subtitle { color: var(--muted); font-size: 14px; margin-top: 4px; }
        .btn { display: inline-flex; align-items: center; gap: 6px; padding: 10px 18px; border-radius: 10px; font-weight: 600; font-size: 14px; text-decoration: none; border: none; cursor: pointer; transition: all 0.2s; }
        .btn-primary { background: #f08804; color: #fff; }
        .btn-primary:hover { background: #cc7203; transform: translateY(-1px); }
        .btn-outline { background: transparent; border: 1px solid rgba(0,0,0,0.15); color: var(--text); }
        .btn-outline:hover { background: rgba(0,0,0,0.04); }
        .btn-sm { padding: 6px 12px; font-size: 12px; }
        .btn-danger { background: #fee2e2; color: var(--danger); border: 1px solid #fecaca; }
        .btn-danger:hover { background: #fecaca; }
        .btn-success { background: #d1fae5; color: var(--success); border: 1px solid #a7f3d0; }
        .alert { padding: 14px 18px; border-radius: 10px; margin-bottom: 24px; font-size: 14px; font-weight: 500; }
        .alert-success { background: #d1fae5; color: #065f46; border: 1px solid #a7f3d0; }
        .alert-error { background: #fee2e2; color: #991b1b; border: 1px solid #fecaca; }
        .card { background: var(--panel); border: 1px solid var(--line); border-radius: 16px; overflow: hidden; }
        table { width: 100%; border-collapse: collapse; }
        thead { background: var(--bg); }
        th { padding: 12px 16px; text-align: left; font-size: 12px; font-weight: 600; color: var(--muted); text-transform: uppercase; letter-spacing: 0.04em; border-bottom: 1px solid var(--line); }
        td { padding: 14px 16px; font-size: 14px; border-bottom: 1px solid var(--line); vertical-align: middle; }
        tr:last-child td { border-bottom: none; }
        tr:hover td { background: #f8fafc; }
        .badge { display: inline-flex; align-items: center; padding: 3px 10px; border-radius: 50px; font-size: 11px; font-weight: 600; }
        .badge-green { background: #d1fae5; color: #065f46; }
        .badge-gray { background: #f1f5f9; color: #475569; }
        .badge-yellow { background: #fef3c7; color: #92400e; }
        .badge-purple { background: #ede9fe; color: #5b21b6; }
        .actions { display: flex; gap: 6px; flex-wrap: wrap; }
        .nav-links { display: flex; gap: 12px; flex-wrap: wrap; }
        .back { color: var(--text); text-decoration: none; border: 1px solid rgba(0,0,0,0.15); border-radius: 10px; padding: 10px 18px; font-weight: 600; font-size: 14px; transition: all 0.2s; }
        .back:hover { background: rgba(0,0,0,0.04); }
    </style>
</head>
<body>
<div class="layout">
    <div class="topbar">
        <div>
            <h1>💳 Pricing Plans</h1>
            <p class="subtitle">Manage subscription plans displayed on the homepage</p>
        </div>
        <div class="nav-links">
            <a href="{{ route('admin.pricing.subscriptions') }}" class="btn btn-outline">📋 Subscriptions</a>
            <a href="{{ route('admin.pricing.create') }}" class="btn btn-primary">+ New Plan</a>
            <a href="{{ route('dashboard') }}" class="back">← Dashboard</a>
        </div>
    </div>

    @if(session('success'))
        <div class="alert alert-success">✅ {{ session('success') }}</div>
    @endif
    @if(session('error'))
        <div class="alert alert-error">❌ {{ session('error') }}</div>
    @endif

    <div class="card">
        <table>
            <thead>
                <tr>
                    <th>Plan</th>
                    <th>Price</th>
                    <th>Billing</th>
                    <th>Status</th>
                    <th>Featured</th>
                    <th>Promo</th>
                    <th>Order</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                @forelse($plans as $plan)
                <tr>
                    <td>
                        <strong>{{ $plan->name }}</strong><br>
                        <span style="color:var(--muted);font-size:12px;">{{ $plan->slug }}</span>
                    </td>
                    <td>
                        ${{ number_format($plan->price, 2) }}
                        @if($plan->isOnPromo())
                            <br><span class="badge badge-yellow">🔥 ${{ number_format($plan->promo_price, 2) }} promo</span>
                        @endif
                    </td>
                    <td>{{ ucfirst($plan->billing_cycle) }}</td>
                    <td>
                        @if($plan->is_active)
                            <span class="badge badge-green">Active</span>
                        @else
                            <span class="badge badge-gray">Inactive</span>
                        @endif
                    </td>
                    <td>
                        @if($plan->is_featured)
                            <span class="badge badge-purple">⭐ Featured</span>
                        @else
                            <span style="color:var(--muted);font-size:12px;">—</span>
                        @endif
                    </td>
                    <td>
                        @if($plan->promo_price)
                            <span style="font-size:12px;color:var(--muted);">
                                {{ $plan->promo_start_at ? $plan->promo_start_at->format('M d') : '∞' }}
                                → {{ $plan->promo_end_at ? $plan->promo_end_at->format('M d') : '∞' }}
                            </span>
                        @else
                            <span style="color:var(--muted);font-size:12px;">—</span>
                        @endif
                    </td>
                    <td>{{ $plan->sort_order }}</td>
                    <td>
                        <div class="actions">
                            <a href="{{ route('admin.pricing.edit', $plan->id) }}" class="btn btn-outline btn-sm">✏️ Edit</a>
                            <form method="POST" action="{{ route('admin.pricing.destroy', $plan->id) }}" onsubmit="return confirm('Delete this plan?')">
                                @csrf @method('DELETE')
                                <button class="btn btn-danger btn-sm">🗑</button>
                            </form>
                        </div>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="8" style="text-align:center;padding:3rem;color:var(--muted);">
                        No pricing plans yet. <a href="{{ route('admin.pricing.create') }}" style="color:var(--primary);">Create one →</a>
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
</body>
</html>
