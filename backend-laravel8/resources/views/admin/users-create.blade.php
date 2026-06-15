<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Create User — Admin Tools Settings</title>
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
            max-width: 600px;
            margin: 0 auto;
            padding: 40px 24px;
        }

        .topbar {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 32px;
            gap: 16px;
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

        .card {
            background: var(--panel);
            border: 1px solid var(--line);
            border-radius: 16px;
            padding: 32px;
            box-shadow: 0 4px 6px -1px rgba(0,0,0,0.05);
        }

        .form-group {
            margin-bottom: 20px;
        }

        label {
            display: block;
            font-size: 13px;
            font-weight: 600;
            margin-bottom: 6px;
            color: var(--text);
        }

        .form-control {
            width: 100%;
            padding: 10px 14px;
            border-radius: 8px;
            border: 1px solid rgba(0, 0, 0, 0.15);
            font-size: 14px;
            font-family: inherit;
            outline: none;
            transition: border-color 0.2s;
        }

        .form-control:focus {
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

        .actions {
            display: flex;
            justify-content: flex-end;
            gap: 12px;
            margin-top: 32px;
        }

        .alert {
            padding: 14px 18px;
            border-radius: 10px;
            margin-bottom: 24px;
            font-size: 14px;
            font-weight: 500;
        }

        .alert-error {
            background: #fee2e2;
            color: #991b1b;
            border: 1px solid #fecaca;
        }

        .section-title {
            font-size: 15px;
            font-weight: 700;
            margin: 28px 0 14px;
            padding-bottom: 6px;
            border-bottom: 1px solid var(--line);
        }
    </style>
</head>
<body>
<div class="layout">
    <div class="topbar">
        <div>
            <h1>👥 Create User</h1>
            <p class="subtitle">Add a new user account to the system</p>
        </div>
        <a href="{{ route('admin.users.index') }}" class="back">Cancel</a>
    </div>

    @if($errors->any())
        <div class="alert alert-error">
            <ul>
                @foreach ($errors->all() as $error)
                    <li>❌ {{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <div class="card">
        <form method="POST" action="{{ route('admin.users.store') }}">
            @csrf

            <div class="form-group">
                <label for="name">Full Name</label>
                <input type="text" name="name" id="name" class="form-control" value="{{ old('name') }}" placeholder="e.g. John Doe" required>
            </div>

            <div class="form-group">
                <label for="email">Email Address</label>
                <input type="email" name="email" id="email" class="form-control" value="{{ old('email') }}" placeholder="e.g. john@example.com" required>
            </div>

            <div class="form-group">
                <label for="password">Password</label>
                <input type="password" name="password" id="password" class="form-control" placeholder="Minimum 8 characters" required>
            </div>

            <div class="form-group">
                <label for="role">Role</label>
                <select name="role" id="role" class="form-control" required>
                    <option value="0" {{ old('role', '0') == '0' ? 'selected' : '' }}>User (Standard customer)</option>
                    <option value="1" {{ old('role') == '1' ? 'selected' : '' }}>Admin (Full control)</option>
                </select>
            </div>

            <div class="section-title">🎁 Optional Free Trial</div>

            <div class="form-group">
                <label for="pricing_plan_id">Select Trial Plan</label>
                <select name="pricing_plan_id" id="pricing_plan_id" class="form-control">
                    <option value="">-- No trial subscription --</option>
                    @foreach($plans as $plan)
                        <option value="{{ $plan->id }}" {{ old('pricing_plan_id') == $plan->id ? 'selected' : '' }}>
                            {{ $plan->name }} ({{ $plan->billing_cycle }})
                        </option>
                    @endforeach
                </select>
            </div>

            <div class="form-group">
                <label for="duration_days">Trial Duration (Days)</label>
                <input type="number" name="duration_days" id="duration_days" class="form-control" value="{{ old('duration_days') }}" placeholder="Leave blank to use plan default" min="1" max="365">
            </div>

            <div class="actions">
                <a href="{{ route('admin.users.index') }}" class="btn btn-outline">Cancel</a>
                <button type="submit" class="btn btn-primary">Create User</button>
            </div>
        </form>
    </div>
</div>
</body>
</html>
