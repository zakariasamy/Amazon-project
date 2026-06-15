<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Users — Admin Tools Settings</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <style>
        :root {
            --bg: #f8fafc;
            --panel: #ffffff;
            --line: rgba(0, 0, 0, 0.08);
            --text: #0f172a;
            --muted: #475569;
            --primary: #f08804;
            --primary-hover: #cc7203;
            --success: #10b981;
            --warning: #f59e0b;
            --danger: #ef4444;
        }

        * { box-sizing: border-box; margin: 0; padding: 0; }

        body {
            font-family: 'Inter', sans-serif;
            background: var(--bg);
            color: var(--text);
            min-height: 100vh;
        }

        .layout {
            max-width: 1200px;
            margin: 0 auto;
            padding: 40px 24px;
        }

        .topbar {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 32px;
            gap: 16px;
            flex-wrap: wrap;
        }

        h1 {
            font-size: 1.75rem;
            font-weight: 800;
            background: linear-gradient(135deg, #0f172a 0%, #475569 100%);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
        }

        .subtitle {
            color: var(--muted);
            font-size: 14px;
            margin-top: 4px;
        }

        .back {
            color: var(--text);
            text-decoration: none;
            border: 1px solid rgba(0,0,0,0.15);
            border-radius: 10px;
            padding: 10px 18px;
            font-weight: 600;
            font-size: 14px;
        }

        .back:hover {
            background: rgba(0,0,0,0.04);
        }

        .search-bar {
            display: flex;
            gap: 12px;
            margin-bottom: 24px;
            max-width: 500px;
        }

        .search-input {
            flex: 1;
            padding: 10px 16px;
            border-radius: 10px;
            border: 1px solid rgba(0, 0, 0, 0.15);
            font-size: 14px;
            font-family: inherit;
        }

        .search-input:focus {
            outline: none;
            border-color: var(--primary);
        }

        .btn {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            padding: 10px 20px;
            border-radius: 10px;
            font-weight: 600;
            font-size: 14px;
            text-decoration: none;
            border: none;
            cursor: pointer;
            transition: all 0.2s;
        }

        .btn-primary {
            background: var(--primary);
            color: #fff;
        }

        .btn-primary:hover {
            background: var(--primary-hover);
        }

        .btn-outline {
            background: transparent;
            border: 1px solid rgba(0,0,0,0.15);
            color: var(--text);
        }

        .btn-outline:hover {
            background: rgba(0,0,0,0.04);
        }

        .card {
            background: var(--panel);
            border: 1px solid var(--line);
            border-radius: 16px;
            overflow: hidden;
            box-shadow: 0 4px 6px -1px rgba(0,0,0,0.05);
        }

        table {
            width: 100%;
            border-collapse: collapse;
        }

        thead {
            background: var(--bg);
        }

        th {
            padding: 14px 16px;
            text-align: left;
            font-size: 11px;
            font-weight: 600;
            color: var(--muted);
            text-transform: uppercase;
            letter-spacing: .05em;
            border-bottom: 1px solid var(--line);
        }

        td {
            padding: 16px 16px;
            font-size: 14px;
            border-bottom: 1px solid var(--line);
            vertical-align: middle;
        }

        tr:last-child td {
            border-bottom: none;
        }

        tr:hover td {
            background: #f8fafc;
        }

        .badge {
            display: inline-flex;
            align-items: center;
            padding: 3px 10px;
            border-radius: 50px;
            font-size: 11px;
            font-weight: 600;
        }

        .badge-green { background: #d1fae5; color: #065f46; }
        .badge-gray { background: #f1f5f9; color: #475569; }
        .badge-yellow { background: #fef3c7; color: #92400e; }
        .badge-red { background: #fee2e2; color: #991b1b; }
        .badge-blue { background: #dbeafe; color: #1e40af; }

        .alert {
            padding: 14px 18px;
            border-radius: 10px;
            margin-bottom: 24px;
            font-size: 14px;
            font-weight: 500;
        }

        .alert-success {
            background: #d1fae5;
            color: #065f46;
            border: 1px solid #a7f3d0;
        }

        .alert-error {
            background: #fee2e2;
            color: #991b1b;
            border: 1px solid #fecaca;
        }

        .pagination {
            display: flex;
            justify-content: center;
            gap: 8px;
            margin-top: 24px;
        }

        .pagination a, .pagination span {
            padding: 8px 14px;
            border: 1px solid var(--line);
            border-radius: 8px;
            font-size: 13px;
            text-decoration: none;
            color: var(--text);
        }

        .pagination .active {
            background: var(--primary);
            color: #fff;
            border-color: var(--primary);
        }

        .form-select, .form-input-number {
            padding: 8px 12px;
            border-radius: 8px;
            border: 1px solid rgba(0, 0, 0, 0.15);
            font-size: 13px;
            font-family: inherit;
            outline: none;
            transition: border-color 0.2s;
        }

        .form-select:focus, .form-input-number:focus {
            border-color: var(--primary);
        }
    </style>
</head>
<body>
<div class="layout">
    <div class="topbar">
        <div>
            <h1>👥 Manage Users</h1>
            <p class="subtitle">Search users and assign free subscription trials</p>
        </div>
        <div style="display:flex; gap:12px; align-items:center;">
            <a href="{{ route('admin.users.create') }}" class="btn btn-primary" style="padding: 10px 18px; border-radius: 10px;">+ Create User</a>
            <a href="/admin/settings" class="back">← Admin Settings</a>
        </div>
    </div>

    @if(session('success'))
        <div class="alert alert-success">✅ {{ session('success') }}</div>
    @endif

    @if($errors->any())
        <div class="alert alert-error">
            <ul>
                @foreach ($errors->all() as $error)
                    <li>❌ {{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <!-- Search Form -->
    <form method="GET" action="{{ route('admin.users.index') }}" class="search-bar">
        <input type="text" name="search" value="{{ $search }}" placeholder="Search by name or email..." class="search-input">
        <button type="submit" class="btn btn-primary">Search</button>
        @if($search)
            <a href="{{ route('admin.users.index') }}" class="btn btn-outline">Clear</a>
        @endif
    </form>

    <div class="card">
        <table>
            <thead>
                <tr>
                    <th>#</th>
                    <th>User Info</th>
                    <th>Role</th>
                    <th>Active Subscription</th>
                    <th>Give Free Trial</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                @forelse($users as $user)
                <tr>
                    <td style="color:var(--muted);font-size:12px;">#{{ $user->id }}</td>
                    <td>
                        <strong>{{ $user->name }}</strong><br>
                        <span style="color:var(--muted);font-size:12px;">{{ $user->email }}</span>
                    </td>
                    <td>
                        @if($user->isAdmin())
                            <span class="badge badge-blue">Admin</span>
                        @else
                            <span class="badge badge-gray">User</span>
                        @endif
                    </td>
                    <td>
                        @php $sub = $user->activeSubscription(); @endphp
                        @if($sub)
                            <strong style="color:var(--success);">{{ $sub->plan?->name ?? 'Unknown Plan' }}</strong><br>
                            <span style="color:var(--muted);font-size:11px;">
                                Exp: {{ $sub->current_period_end ? $sub->current_period_end->format('Y-m-d') : 'Never' }}
                            </span>
                        @else
                            <span style="color:var(--muted);font-size:13px;">No active subscription</span>
                        @endif
                    </td>
                    <td>
                        @if($user->isAdmin())
                            <span style="color:var(--muted);font-size:12px;font-style:italic;">Admin bypasses limits</span>
                        @else
                            <form method="POST" action="{{ route('admin.users.giveTrial', $user->id) }}" style="display:flex; gap:8px; align-items:center;">
                                @csrf
                                <select name="pricing_plan_id" required class="form-select">
                                    <option value="">-- Choose Plan --</option>
                                    @foreach($plans as $plan)
                                        <option value="{{ $plan->id }}">{{ $plan->name }} ({{ $plan->billing_cycle }})</option>
                                    @endforeach
                                </select>
                                <input type="number" name="duration_days" min="1" max="365" placeholder="Days (opt)" class="form-input-number" style="width:85px;">
                                <button type="submit" class="btn btn-primary" style="padding:8px 12px; font-size:12px; border-radius:8px;">Grant Trial</button>
                            </form>
                        @endif
                    </td>
                    <td>
                        <a href="{{ route('admin.users.edit', $user->id) }}" class="btn btn-outline" style="padding: 8px 14px; font-size: 12px; border-radius: 8px;">Edit</a>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="6" style="text-align:center; padding:32px; color:var(--muted);">No users found.</td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    @if($users->hasPages())
        <div class="pagination" style="margin-top: 24px;">
            {{ $users->links() }}
        </div>
    @endif
</div>
</body>
</html>
