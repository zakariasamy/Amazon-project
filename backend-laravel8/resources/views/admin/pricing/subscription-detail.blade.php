<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Subscription #{{ $subscription->id }} — Admin</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <style>
        :root { --bg:#f8fafc; --panel:#ffffff; --line:rgba(0,0,0,0.08); --text:#0f172a; --muted:#475569; --primary:#f08804; --success:#10b981; --warning:#f59e0b; --danger:#ef4444; }
        * { box-sizing:border-box; margin:0; padding:0; }
        body { font-family:'Inter',sans-serif; background:var(--bg); color:var(--text); min-height:100vh; }
        .layout { max-width:1000px; margin:0 auto; padding:40px 24px; }
        .topbar { display:flex; justify-content:space-between; align-items:center; margin-bottom:32px; gap:16px; flex-wrap:wrap; }
        h1 { font-size:1.75rem; font-weight:800; background:linear-gradient(135deg,#0f172a 0%,#475569 100%); -webkit-background-clip:text; -webkit-text-fill-color:transparent; }
        .back { color:var(--text); text-decoration:none; border:1px solid rgba(0,0,0,0.15); border-radius:10px; padding:10px 18px; font-weight:600; font-size:14px; }
        .back:hover { background:rgba(0,0,0,0.04); }
        .card { background:var(--panel); border:1px solid var(--line); border-radius:16px; padding:28px; margin-bottom:24px; }
        .card-title { font-size:14px; font-weight:700; color:var(--muted); text-transform:uppercase; letter-spacing:.05em; margin-bottom:20px; padding-bottom:12px; border-bottom:1px solid var(--line); }
        .info-grid { display:grid; grid-template-columns:1fr 1fr; gap:20px; }
        .info-item label { font-size:11px; font-weight:600; color:var(--muted); text-transform:uppercase; letter-spacing:.05em; display:block; margin-bottom:4px; }
        .info-item span { font-size:15px; font-weight:500; }
        .badge { display:inline-flex; align-items:center; padding:4px 12px; border-radius:50px; font-size:12px; font-weight:600; }
        .badge-green { background:#d1fae5; color:#065f46; }
        .badge-gray  { background:#f1f5f9; color:#475569; }
        .badge-yellow{ background:#fef3c7; color:#92400e; }
        .badge-red   { background:#fee2e2; color:#991b1b; }
        .proof-img { max-width:100%; border-radius:12px; border:1px solid var(--line); margin-bottom:12px; max-height:500px; object-fit:contain; }
        table { width:100%; border-collapse:collapse; }
        th { padding:10px 14px; text-align:left; font-size:11px; font-weight:600; color:var(--muted); text-transform:uppercase; letter-spacing:.05em; border-bottom:1px solid var(--line); background:var(--bg); }
        td { padding:12px 14px; font-size:13px; border-bottom:1px solid var(--line); }
        tr:last-child td { border-bottom:none; }
        .actions { display:flex; gap:10px; flex-wrap:wrap; }
        .btn { display:inline-flex; align-items:center; gap:6px; padding:10px 18px; border-radius:10px; font-weight:600; font-size:14px; text-decoration:none; border:none; cursor:pointer; transition:all 0.2s; }
        .btn-primary { background:#6366f1; color:#fff; }
        .btn-success { background:#d1fae5; color:#065f46; border:1px solid #a7f3d0; }
        .btn-success:hover { background:#a7f3d0; }
        .btn-danger  { background:#fee2e2; color:#991b1b; border:1px solid #fecaca; }
        .btn-danger:hover { background:#fecaca; }
        .btn-outline { background:transparent; border:1px solid rgba(0,0,0,0.15); color:var(--text); }
        .btn-sm { padding:6px 12px; font-size:12px; }
        .alert { padding:14px 18px; border-radius:10px; margin-bottom:24px; font-size:14px; font-weight:500; }
        .alert-success { background:#d1fae5; color:#065f46; border:1px solid #a7f3d0; }
        .alert-error   { background:#fee2e2; color:#991b1b; border:1px solid #fecaca; }
        input[type=number],input[type=text] { padding:8px 12px; border:1px solid rgba(0,0,0,0.15); border-radius:8px; font-size:13px; font-family:inherit; }
        .reject-area { margin-top:12px; display:none; }
        .reject-area input { width:100%; margin-bottom:8px; }
    </style>
</head>
<body>
<div class="layout">
    <div class="topbar">
        <h1>🔍 Subscription #{{ $subscription->id }}</h1>
        <a href="{{ route('admin.pricing.subscriptions') }}" class="back">← All Subscriptions</a>
    </div>

    @if(session('success'))
        <div class="alert alert-success">✅ {{ session('success') }}</div>
    @endif
    @if(session('error'))
        <div class="alert alert-error">❌ {{ session('error') }}</div>
    @endif

    {{-- User & Plan Info --}}
    <div class="card">
        <div class="card-title">📊 Subscription Overview</div>
        <div class="info-grid">
            <div class="info-item">
                <label>User</label>
                <span>{{ $subscription->user?->name }} <small style="color:var(--muted);">({{ $subscription->user?->email }})</small></span>
            </div>
            <div class="info-item">
                <label>Plan</label>
                <span>{{ $subscription->plan?->name }} — ${{ number_format($subscription->plan?->price, 2) }}/{{ $subscription->plan?->billing_cycle }}</span>
            </div>
            <div class="info-item">
                <label>Status</label>
                @php
                    $badgeMap = ['active'=>'badge-green','pending_approval'=>'badge-yellow','rejected'=>'badge-red','expired'=>'badge-gray'];
                    $badgeCls = $badgeMap[$subscription->status] ?? 'badge-gray';
                @endphp
                <span class="badge {{ $badgeCls }}">{{ str_replace('_', ' ', ucfirst($subscription->status)) }}</span>
            </div>
            <div class="info-item">
                <label>Active Period</label>
                <span>
                    @if($subscription->current_period_start)
                        {{ $subscription->current_period_start->format('M d, Y') }} → {{ $subscription->current_period_end?->format('M d, Y') }}
                    @else
                        Not yet activated
                    @endif
                </span>
            </div>
        </div>

        @if($subscription->isPending())
        <div style="margin-top:24px;padding-top:20px;border-top:1px solid var(--line);">
            <div class="actions">
                <form method="POST" action="{{ route('admin.pricing.subscriptions.approve', $subscription->id) }}">
                    @csrf
                    <input type="text" name="admin_notes" placeholder="Optional approval note" style="width:280px;margin-right:8px;">
                    <button class="btn btn-success">✅ Approve & Activate</button>
                </form>
                <button class="btn btn-danger" onclick="document.getElementById('reject-area').style.display='block'">❌ Reject</button>
            </div>
            <div id="reject-area" class="reject-area">
                <form method="POST" action="{{ route('admin.pricing.subscriptions.reject', $subscription->id) }}">
                    @csrf
                    <input type="text" name="admin_notes" placeholder="Rejection reason (required)" required>
                    <button class="btn btn-danger">Confirm Rejection</button>
                </form>
            </div>
        </div>
        @endif
    </div>

    {{-- Payment Proofs --}}
    <div class="card">
        <div class="card-title">🧾 Payment Proofs</div>
        @forelse($subscription->paymentProofs as $proof)
            <div style="margin-bottom:20px;padding-bottom:20px;border-bottom:1px solid var(--line);">
                <img src="{{ asset('storage/' . $proof->proof_image_path) }}" alt="Payment Proof" class="proof-img">
                <div style="display:flex;gap:12px;align-items:center;flex-wrap:wrap;">
                    @php
                        $proofBadge = ['pending'=>'badge-yellow','approved'=>'badge-green','rejected'=>'badge-red'][$proof->status] ?? 'badge-gray';
                    @endphp
                    <span class="badge {{ $proofBadge }}">{{ ucfirst($proof->status) }}</span>
                    <span style="font-size:12px;color:var(--muted);">Uploaded {{ $proof->created_at->diffForHumans() }}</span>
                    @if($proof->admin_notes)
                        <span style="font-size:12px;color:var(--muted);">Note: {{ $proof->admin_notes }}</span>
                    @endif
                </div>
            </div>
        @empty
            <p style="color:var(--muted);font-size:14px;">No payment proofs uploaded.</p>
        @endforelse
    </div>

    {{-- Tool Limits --}}
    <div class="card">
        <div class="card-title" style="display:flex;justify-content:space-between;align-items:center;">
            🛠 Tool Usage Limits
            @if($subscription->toolLimits->count())
            <form method="POST" action="{{ route('admin.pricing.subscriptions.resetLimits', $subscription->id) }}">
                @csrf
                <button class="btn btn-outline btn-sm" onclick="return confirm('Reset all usage counts to 0?')">🔄 Reset All Usage</button>
            </form>
            @endif
        </div>
        @if($subscription->toolLimits->count())
        <table>
            <thead>
                <tr>
                    <th>Tool</th>
                    <th>Limit</th>
                    <th>Bonus</th>
                    <th>Used</th>
                    <th>Remaining</th>
                    <th>Next Reset</th>
                    <th>Override</th>
                </tr>
            </thead>
            <tbody>
                @foreach($subscription->toolLimits as $tl)
                <tr>
                    <td><strong>{{ str_replace('_', ' ', ucwords($tl->tool_name, '_')) }}</strong></td>
                    <td>{{ $tl->limit_count === -1 ? '∞ Unlimited' : number_format($tl->limit_count) }}</td>
                    <td>+{{ $tl->bonus_count }}</td>
                    <td>{{ number_format($tl->used_count) }}</td>
                    <td>
                        @if($tl->isUnlimited()) ∞
                        @else <span style="color:{{ $tl->remaining() > 0 ? 'var(--success)' : 'var(--danger)' }}">{{ number_format($tl->remaining()) }}</span>
                        @endif
                    </td>
                    <td style="font-size:12px;color:var(--muted);">{{ $tl->next_reset_at?->format('M d, Y') ?? '—' }}</td>
                    <td>
                        <form method="POST" action="{{ route('admin.pricing.limits.update', $tl->id) }}" style="display:flex;gap:6px;align-items:center;">
                            @csrf
                            <input type="number" name="limit_count" value="{{ $tl->limit_count }}" min="-1" style="width:70px;" title="Limit (-1=∞)">
                            <input type="number" name="bonus_count" value="{{ $tl->bonus_count }}" min="0" style="width:60px;" title="Bonus">
                            <button class="btn btn-primary btn-sm">Save</button>
                        </form>
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
        @else
            <p style="color:var(--muted);font-size:14px;">No tool limits instantiated yet. Activate the subscription to create them.</p>
        @endif
    </div>
</div>
</body>
</html>
