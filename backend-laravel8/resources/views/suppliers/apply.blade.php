@php
    $isRtl = app()->getLocale() === 'ar';
    $currentLang = app()->getLocale();
@endphp
<!DOCTYPE html>
<html lang="{{ $currentLang }}" dir="{{ $isRtl ? 'rtl' : 'ltr' }}">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ __('suppliers.apply_title') }} - Amazon Product Analyzer</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    @if($isRtl)
    <link href="https://fonts.googleapis.com/css2?family=Cairo:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    @endif
    <style>
        :root {
            --primary: #6366f1;
            --primary-dark: #4f46e5;
            --primary-light: #818cf8;
            --secondary: #0ea5e9;
            --success: #10b981;
            --warning: #f59e0b;
            --danger: #ef4444;
            --dark: #f8fafc;
            --dark-light: #ffffff;
            --gray: #64748b;
            --gray-light: #475569;
            --white: #0f172a;
            --gradient: linear-gradient(135deg, var(--primary) 0%, var(--secondary) 100%);
        }

        * { margin: 0; padding: 0; box-sizing: border-box; }

        body {
            font-family: {{ $isRtl ? "'Cairo'" : "'Inter'" }}, -apple-system, BlinkMacSystemFont, sans-serif;
            background: var(--dark);
            color: var(--white);
            min-height: 100vh;
            line-height: 1.6;
        }

        .header {
            background: var(--dark-light);
            border-bottom: 1px solid rgba(0,0,0,0.08);
            padding: 1rem 2rem;
            position: sticky;
            top: 0;
            z-index: 100;
        }

        .header-content {
            max-width: 800px;
            margin: 0 auto;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .logo {
            display: flex;
            align-items: center;
            gap: 0.75rem;
            text-decoration: none;
            color: var(--white);
            font-weight: 700;
            font-size: 1.25rem;
        }

        .logo-icon {
            width: 40px;
            height: 40px;
            background: var(--gradient);
            border-radius: 10px;
            display: flex;
            align-items: center;
            justify-content: center;
            color: #ffffff;
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

        .btn-outline {
            background: transparent;
            border: 2px solid rgba(0,0,0,0.08);
            color: var(--white);
        }
        .btn-outline:hover {
            border-color: var(--primary);
            color: var(--primary);
        }

        .btn-primary {
            background: var(--gradient);
            color: #ffffff;
            width: 100%;
            padding: 1rem;
            font-size: 1rem;
        }
        .btn-primary:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 15px rgba(99, 102, 241, 0.4);
        }

        .main {
            max-width: 800px;
            margin: 0 auto;
            padding: 2.5rem 2rem;
        }

        .page-title {
            text-align: center;
            margin-bottom: 2rem;
        }

        .page-title h1 {
            font-size: 2.25rem;
            font-weight: 800;
            margin-bottom: 0.5rem;
            background: var(--gradient);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
        }

        .page-title p {
            color: var(--gray-light);
        }

        .requirements {
            background: rgba(245, 158, 11, 0.08);
            border: 1px solid rgba(245, 158, 11, 0.25);
            border-radius: 12px;
            padding: 1.5rem;
            margin-bottom: 2rem;
        }

        .requirements h3 {
            color: #d97706;
            margin-bottom: 1rem;
            display: flex;
            align-items: center;
            gap: 0.5rem;
            font-weight: 700;
        }

        .requirements ul {
            list-style: none;
        }

        .requirements li {
            padding: 0.5rem 0;
            color: var(--gray-light);
            display: flex;
            align-items: flex-start;
            gap: 0.5rem;
            font-weight: 500;
        }

        .requirements li::before {
            content: "✓";
            color: #d97706;
            font-weight: bold;
        }

        .form-card {
            background: var(--dark-light);
            border: 1px solid rgba(0,0,0,0.08);
            border-radius: 16px;
            padding: 2rem;
            box-shadow: 0 4px 6px -1px rgba(0,0,0,0.02);
        }

        .form-group {
            margin-bottom: 1.5rem;
        }

        .form-row {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 1rem;
        }

        .form-label {
            display: block;
            margin-bottom: 0.5rem;
            font-weight: 600;
            font-size: 0.9rem;
            color: var(--white);
        }

        .form-hint {
            font-size: 0.8rem;
            color: var(--gray);
            margin-top: 0.25rem;
        }

        .form-input, .form-select, .form-textarea {
            width: 100%;
            background: var(--dark);
            border: 1px solid rgba(0,0,0,0.08);
            color: var(--white);
            padding: 0.875rem 1rem;
            border-radius: 10px;
            font-size: 0.95rem;
            font-family: inherit;
            transition: all 0.2s;
            outline: none;
        }

        .form-input:focus, .form-select:focus, .form-textarea:focus {
            border-color: var(--primary);
            box-shadow: 0 0 0 3px rgba(99, 102, 241, 0.15);
            background: var(--dark-light);
        }

        .form-textarea {
            min-height: 100px;
            resize: vertical;
        }

        .form-file {
            background: var(--dark);
            border: 2px dashed rgba(0,0,0,0.12);
            border-radius: 10px;
            padding: 2rem;
            text-align: center;
            cursor: pointer;
            transition: all 0.3s;
        }

        .form-file:hover {
            border-color: var(--primary);
            background: rgba(99, 102, 241, 0.02);
        }

        .form-file input {
            display: none;
        }

        .form-file-label {
            color: var(--gray-light);
            font-weight: 500;
        }

        .form-file-label span {
            color: var(--primary);
            text-decoration: underline;
        }

        .error-text {
            color: var(--danger);
            font-size: 0.85rem;
            margin-top: 0.5rem;
            font-weight: 500;
        }

        .success-message {
            background: rgba(16, 185, 129, 0.08);
            border: 1px solid rgba(16, 185, 129, 0.2);
            border-radius: 12px;
            padding: 1.5rem;
            text-align: center;
            color: var(--success);
            margin-bottom: 2rem;
            font-weight: 600;
        }

        .divider {
            display: flex;
            align-items: center;
            gap: 1rem;
            margin: 1.5rem 0;
            color: var(--gray);
            font-size: 0.85rem;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        .divider::before, .divider::after {
            content: '';
            flex: 1;
            height: 1px;
            background: rgba(0,0,0,0.08);
        }

        @media (max-width: 768px) {
            .form-row { grid-template-columns: 1fr; }
            .main { padding: 1.5rem 1rem; }
        }
    </style>
</head>
<body>
    <header class="header">
        <div class="header-content">
            <a href="/suppliers" class="logo">
                <div class="logo-icon">🏪</div>
                {{ __('suppliers.apply_title') }}
            </a>
            <a href="/suppliers" class="btn btn-outline">{{ __('suppliers.back_to_suppliers') }}</a>
        </div>
    </header>

    <main class="main">
        <div class="page-title">
            <h1>{{ __('suppliers.apply_title') }}</h1>
            <p>{{ __('suppliers.apply_subtitle') }}</p>
        </div>

        @if(session('success'))
            <div class="success-message">
                ✓ {{ session('success') }}
            </div>
        @endif

        <div class="requirements">
            <h3>⚠️ {{ __('suppliers.requirements_title') }}</h3>
            <ul>
                <li>{{ __('suppliers.requirement_1') }}</li>
                <li>{{ __('suppliers.requirement_2') }}</li>
                <li>{{ __('suppliers.requirement_3') }}</li>
            </ul>
        </div>

        <form class="form-card" method="POST" action="{{ route('suppliers.submit') }}" enctype="multipart/form-data">
            @csrf
            
            <div class="form-row">
                <div class="form-group">
                    <label class="form-label">{{ __('suppliers.supplier_name') }} *</label>
                    <input type="text" name="supplier_name" class="form-input" value="{{ old('supplier_name') }}" required>
                    @error('supplier_name')
                        <div class="error-text">{{ $message }}</div>
                    @enderror
                </div>
                <div class="form-group">
                    <label class="form-label">{{ __('suppliers.supplier_name_ar') }} *</label>
                    <input type="text" name="supplier_name_ar" class="form-input" value="{{ old('supplier_name_ar') }}" required>
                    @error('supplier_name_ar')
                        <div class="error-text">{{ $message }}</div>
                    @enderror
                </div>
            </div>

            <div class="form-row">
                <div class="form-group">
                    <label class="form-label">{{ __('suppliers.applicant_email') }} *</label>
                    <input type="email" name="applicant_email" class="form-input" value="{{ old('applicant_email') }}" required>
                    @error('applicant_email')
                        <div class="error-text">{{ $message }}</div>
                    @enderror
                </div>
                <div class="form-group">
                    <label class="form-label">{{ __('suppliers.applicant_phone') }} *</label>
                    <input type="tel" name="applicant_phone" class="form-input" value="{{ old('applicant_phone') }}" required>
                    @error('applicant_phone')
                        <div class="error-text">{{ $message }}</div>
                    @enderror
                </div>
            </div>

            <div class="form-group">
                <label class="form-label">{{ __('suppliers.category') }} *</label>
                <select name="category" class="form-select" required>
                    <option value="">{{ __('suppliers.select_category') }}</option>
                    @foreach($categories as $key => $names)
                        <option value="{{ $key }}" {{ old('category') === $key ? 'selected' : '' }}>
                            {{ $names[$currentLang] }}
                        </option>
                    @endforeach
                </select>
                @error('category')
                    <div class="error-text">{{ $message }}</div>
                @enderror
            </div>

            <div class="form-row">
                <div class="form-group">
                    <label class="form-label">{{ __('suppliers.description') }}</label>
                    <textarea name="description" class="form-textarea" placeholder="{{ __('suppliers.description_hint') }}">{{ old('description') }}</textarea>
                </div>
                <div class="form-group">
                    <label class="form-label">{{ __('suppliers.description_ar') }}</label>
                    <textarea name="description_ar" class="form-textarea" placeholder="{{ __('suppliers.description_hint') }}">{{ old('description_ar') }}</textarea>
                </div>
            </div>

            <div class="divider">{{ __('suppliers.contact_hint') }}</div>

            <div class="form-row">
                <div class="form-group">
                    <label class="form-label">{{ __('suppliers.telegram_group_link') }}</label>
                    <input type="url" name="telegram_group_link" class="form-input" placeholder="https://t.me/..." value="{{ old('telegram_group_link') }}">
                    @error('telegram_group_link')
                        <div class="error-text">{{ $message }}</div>
                    @enderror
                </div>
                <div class="form-group">
                    <label class="form-label">{{ __('suppliers.website') }}</label>
                    <input type="url" name="website" class="form-input" placeholder="https://..." value="{{ old('website') }}">
                    @error('website')
                        <div class="error-text">{{ $message }}</div>
                    @enderror
                </div>
            </div>

            <div class="form-row">
                <div class="form-group">
                    <label class="form-label">{{ __('suppliers.location') }}</label>
                    <input type="text" name="location" class="form-input" value="{{ old('location') }}">
                </div>
                <div class="form-group">
                    <label class="form-label">{{ __('suppliers.location_ar') }}</label>
                    <input type="text" name="location_ar" class="form-input" value="{{ old('location_ar') }}">
                </div>
            </div>

            <div class="form-group">
                <label class="form-label">{{ __('suppliers.proof_documents') }} *</label>
                <div class="form-file" onclick="document.getElementById('proof_documents').click()">
                    <input type="file" id="proof_documents" name="proof_documents[]" multiple accept=".pdf,.jpg,.jpeg,.png">
                    <div class="form-file-label">
                        📎 <span>{{ __('suppliers.proof_hint') }}</span>
                    </div>
                </div>
                @error('proof_documents')
                    <div class="error-text">{{ $message }}</div>
                @enderror
                @error('proof_documents.*')
                    <div class="error-text">{{ $message }}</div>
                @enderror
            </div>

            <button type="submit" class="btn btn-primary">{{ __('suppliers.submit_application') }}</button>
        </form>
    </main>
</body>
</html>
