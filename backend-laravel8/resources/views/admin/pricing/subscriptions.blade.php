<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Subscriptions — Admin</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <style>
        :root { --bg:#f8fafc; --panel:#ffffff; --line:rgba(0,0,0,0.08); --text:#0f172a; --muted:#475569; --primary:#f08804; --success:#10b981; --warning:#f59e0b; --danger:#ef4444; }
        * { box-sizing:border-box; margin:0; padding:0; }
        body { font-family:'Inter',sans-serif; background:var(--bg); color:var(--text); min-height:100vh; }
        .layout { max-width:1200px; margin:0 auto; padding:40px 24px; }
        .topbar { display:flex; justify-content:space-between; align-items:center; margin-bottom:32px; gap:16px; flex-wrap:wrap; }
        h1 { font-size:1.75rem; font-weight:800; background:linear-gradient(135deg,#0f172a 0%,#475569 100%); -webkit-background-clip:text; -webkit-text-fill-color:transparent; }
        .subtitle { color:var(--muted); font-size:14px; margin-top:4px; }
        .back { color:var(--text); text-decoration:none; border:1px solid rgba(0,0,0,0.15); border-radius:10px; padding:10px 18px; font-weight:600; font-size:14px; }
        .back:hover { background:rgba(0,0,0,0.04); }
        .filters { display:flex; gap:8px; margin-bottom:24px; flex-wrap:wrap; }
        .filter-btn { padding:8px 16px; border-radius:8px; font-size:13px; font-weight:600; text-decoration:none; border:1px solid rgba(0,0,0,0.12); color:var(--muted); background:#fff; transition:all 0.2s; }
        .filter-btn.active, .filter-btn:hover { background:var(--primary); color:#fff; border-color:var(--primary); }
        .card { background:var(--panel); border:1px solid var(--line); border-radius:16px; overflow:hidden; }
        table { width:100%; border-collapse:collapse; }
        thead { background:var(--bg); }
        th { padding:12px 16px; text-align:left; font-size:11px; font-weight:600; color:var(--muted); text-transform:uppercase; letter-spacing:.05em; border-bottom:1px solid var(--line); }
        td { padding:14px 16px; font-size:14px; border-bottom:1px solid var(--line); vertical-align:middle; }
        tr:last-child td { border-bottom:none; }
        tr:hover td { background:#f8fafc; }
        .badge { display:inline-flex; align-items:center; padding:3px 10px; border-radius:50px; font-size:11px; font-weight:600; }
        .badge-green { background:#d1fae5; color:#065f46; }
        .badge-gray  { background:#f1f5f9; color:#475569; }
        .badge-yellow{ background:#fef3c7; color:#92400e; }
        .badge-red   { background:#fee2e2; color:#991b1b; }
        .actions { display:flex; gap:6px; flex-wrap:wrap; }
        .btn { display:inline-flex; align-items:center; gap:4px; padding:7px 14px; border-radius:8px; font-weight:600; font-size:12px; text-decoration:none; border:none; cursor:pointer; transition:all 0.2s; }
        .btn-primary  { background:#6366f1; color:#fff; }
        .btn-success  { background:#d1fae5; color:#065f46; border:1px solid #a7f3d0; }
        .btn-danger   { background:#fee2e2; color:#991b1b; border:1px solid #fecaca; }
        .btn-outline  { background:transparent; border:1px solid rgba(0,0,0,0.15); color:var(--text); }
        .alert { padding:14px 18px; border-radius:10px; margin-bottom:24px; font-size:14px; font-weight:500; }
        .alert-success { background:#d1fae5; color:#065f46; border:1px solid #a7f3d0; }
        .alert-error   { background:#fee2e2; color:#991b1b; border:1px solid #fecaca; }
        .pagination { display:flex; justify-content:center; gap:8px; margin-top:24px; }
        .pagination a, .pagination span { padding:8px 14px; border:1px solid var(--line); border-radius:8px; font-size:13px; text-decoration:none; color:var(--text); }
        .pagination .active { background:var(--primary); color:#fff; border-color:var(--primary); }
    </style>
</head>
<body>
<div class="layout">
    <div class="topbar">
        <div>
            <h1>📋 Subscriptions</h1>
            <p class="subtitle">Review payment proofs and manage subscriber access</p>
        </div>
        <a href="{{ route('admin.pricing.index') }}" class="back">← Pricing Plans</a>
    </div>

    @if(session('success'))
        <div class="alert alert-success">✅ {{ session('success') }}</div>
    @endif
    @if(session('error'))
        <div class="alert alert-error">❌ {{ session('error') }}</div>
    @endif

    <div class="filters">
        @foreach(['pending_approval' => '⏳ Pending', 'active' => '✅ Active', 'rejected' => '❌ Rejected', 'expired' => '💤 Expired', 'all' => 'All'] as $val => $label)
            <a href="?status={{ $val }}" class="filter-btn {{ $status === $val ? 'active' : '' }}">{{ $label }}</a>
        @endforeach
    </div>

    <div class="card">
        <table>
            <thead>
                <tr>
                    <th>#</th>
                    <th>User</th>
                    <th>Plan</th>
                    <th>Status</th>
                    <th>Period</th>
                    <th>Proofs</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                @forelse($subscriptions as $sub)
                <tr>
                    <td style="color:var(--muted);font-size:12px;">#{{ $sub->id }}</td>
                    <td>
                        <strong>{{ $sub->user?->name ?? 'N/A' }}</strong><br>
                        <span style="color:var(--muted);font-size:12px;">{{ $sub->user?->email }}</span>
                    </td>
                    <td>{{ $sub->plan?->name ?? '—' }}</td>
                    <td>
                        @php
                            $badgeMap = ['active'=>'badge-green','pending_approval'=>'badge-yellow','rejected'=>'badge-red','expired'=>'badge-gray'];
                            $badgeCls = $badgeMap[$sub->status] ?? 'badge-gray';
                        @endphp
                        <span class="badge {{ $badgeCls }}">{{ str_replace('_', ' ', ucfirst($sub->status)) }}</span>
                    </td>
                    <td style="font-size:12px;color:var(--muted);">
                        @if($sub->current_period_start)
                            {{ $sub->current_period_start->format('M d') }} → {{ $sub->current_period_end?->format('M d, Y') }}
                        @else
                            —
                        @endif
                    </td>
                    <td>
                        @php $proofs = $sub->paymentProofs; @endphp
                        @if($proofs->count())
                            <span class="badge badge-yellow">{{ $proofs->count() }} proof(s)</span>
                        @else
                            <span style="color:var(--muted);font-size:12px;">None</span>
                        @endif
                    </td>
                    <td>
                        <div class="actions">
                            <a href="{{ route('admin.pricing.subscriptions.show', $sub->id) }}" class="btn btn-outline">👁 View</a>
                            @if($sub->isPending())
                                <form method="POST" action="{{ route('admin.pricing.subscriptions.approve', $sub->id) }}">
                                    @csrf
                                    <button class="btn btn-success">✅ Approve</button>
                                </form>
                                <button class="btn btn-danger" onclick="document.getElementById('reject-form-{{ $sub->id }}').style.display='block'">❌ Reject</button>
                            @endif
                        </div>
                        @if($sub->isPending())
                        <form id="reject-form-{{ $sub->id }}" method="POST" action="{{ route('admin.pricing.subscriptions.reject', $sub->id) }}" style="display:none;margin-top:8px;">
                            @csrf
                            <input type="text" name="admin_notes" placeholder="Rejection reason (required)" required style="padding:6px 10px;border:1px solid #fecaca;border-radius:6px;font-size:12px;width:100%;margin-bottom:4px;">
                            <button class="btn btn-danger" style="width:100%;">Confirm Reject</button>
                        </form>
                        @endif
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="7" style="text-align:center;padding:3rem;color:var(--muted);">No subscriptions found.</td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div class="pagination">
        {{ $subscriptions->appends(['status' => $status])->links() }}
    </div>
</div>
</body>
</html>
