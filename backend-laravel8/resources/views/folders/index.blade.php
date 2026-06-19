<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Folders — Amazon Analyzer</title>
    <meta name="description" content="Organize your saved keywords, products and analyses into folders.">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <style>
        :root {
            --primary: #6366f1;
            --primary-dark: #4f46e5;
            --primary-light: #818cf8;
            --secondary: #0ea5e9;
            --success: #10b981;
            --warning: #f59e0b;
            --danger: #ef4444;
            --bg: #f8fafc;
            --surface: #ffffff;
            --border: rgba(0,0,0,0.08);
            --text: #0f172a;
            --muted: #64748b;
            --muted-light: #475569;
            --gradient: linear-gradient(135deg, #6366f1 0%, #0ea5e9 100%);
        }
        * { margin:0; padding:0; box-sizing:border-box; }
        body { font-family:'Inter',sans-serif; background:var(--bg); color:var(--text); min-height:100vh; }

        /* ── Sidebar ─────────────────────────────────── */
        .sidebar {
            position:fixed; left:0; top:0; bottom:0; width:260px;
            background:var(--surface); border-right:1px solid var(--border);
            padding:1.5rem; display:flex; flex-direction:column; z-index:100;
        }
        .sidebar-logo {
            display:flex; align-items:center; gap:.75rem; font-size:1.25rem;
            font-weight:700; color:var(--text); text-decoration:none;
            margin-bottom:2rem; padding-bottom:1.5rem; border-bottom:1px solid var(--border);
        }
        .logo-icon { width:40px; height:40px; background:var(--gradient); border-radius:10px; display:flex; align-items:center; justify-content:center; color:#fff; }
        .nav-section { margin-bottom:1.5rem; }
        .nav-section-title { font-size:.7rem; font-weight:600; text-transform:uppercase; letter-spacing:.1em; color:var(--muted); margin-bottom:.75rem; padding-left:.75rem; }
        .nav-item { display:flex; align-items:center; gap:.75rem; padding:.75rem; color:var(--muted-light); text-decoration:none; border-radius:10px; transition:all .3s; margin-bottom:.25rem; font-size:.875rem; }
        .nav-item:hover { background:rgba(0,0,0,.04); color:var(--text); }
        .nav-item.active { background:var(--primary); color:#fff; }
        .sidebar-footer { margin-top:auto; padding-top:1rem; border-top:1px solid var(--border); }
        .user-card { display:flex; align-items:center; gap:.75rem; padding:.75rem; background:var(--bg); border-radius:12px; }
        .user-avatar { width:40px; height:40px; background:var(--gradient); border-radius:10px; display:flex; align-items:center; justify-content:center; font-weight:600; color:#fff; }
        .user-name { font-weight:600; font-size:.875rem; }
        .user-plan { font-size:.75rem; color:var(--muted); }

        /* ── Main ────────────────────────────────────── */
        .main { margin-left:260px; padding:2rem; }
        .page-header { display:flex; justify-content:space-between; align-items:center; margin-bottom:2rem; }
        .page-header h1 { font-size:1.75rem; font-weight:700; }

        /* ── Breadcrumb ──────────────────────────────── */
        .breadcrumb { display:flex; align-items:center; gap:.5rem; font-size:.875rem; color:var(--muted); margin-bottom:1.5rem; }
        .breadcrumb a { color:var(--primary); text-decoration:none; }
        .breadcrumb a:hover { text-decoration:underline; }
        .breadcrumb .sep { opacity:.4; }

        /* ── Alert ───────────────────────────────────── */
        .alert { padding:.875rem 1.25rem; border-radius:10px; margin-bottom:1.5rem; font-size:.875rem; font-weight:500; }
        .alert-success { background:rgba(16,185,129,.1); color:#059669; border:1px solid rgba(16,185,129,.2); }
        .alert-danger  { background:rgba(239,68,68,.1);   color:#dc2626; border:1px solid rgba(239,68,68,.2); }

        /* ── Button ──────────────────────────────────── */
        .btn { display:inline-flex; align-items:center; gap:.5rem; padding:.75rem 1.25rem; border-radius:10px; font-weight:600; font-size:.875rem; font-family:inherit; cursor:pointer; border:none; text-decoration:none; transition:all .3s; }
        .btn-primary { background:var(--gradient); color:#fff; }
        .btn-primary:hover { transform:translateY(-2px); box-shadow:0 4px 15px rgba(99,102,241,.4); }
        .btn-outline { background:transparent; border:2px solid var(--border); color:var(--text); }
        .btn-outline:hover { border-color:var(--primary); color:var(--primary); }
        .btn-danger { background:rgba(239,68,68,.1); color:#dc2626; border:1px solid rgba(239,68,68,.2); }
        .btn-danger:hover { background:#ef4444; color:#fff; }
        .btn-sm { padding:.45rem .875rem; font-size:.8rem; }

        /* ── Folder Grid ─────────────────────────────── */
        .section-title { font-size:1rem; font-weight:700; margin-bottom:1rem; display:flex; align-items:center; gap:.5rem; }
        .folders-grid { display:grid; grid-template-columns:repeat(auto-fill, minmax(220px, 1fr)); gap:1.25rem; margin-bottom:2.5rem; }

        .folder-card {
            background:var(--surface); border:1px solid var(--border); border-radius:16px;
            padding:1.5rem; cursor:pointer; text-decoration:none; color:var(--text);
            transition:all .3s; position:relative; overflow:hidden;
        }
        .folder-card::before {
            content:''; position:absolute; top:0; left:0; right:0; height:4px;
            background:var(--folder-color, var(--primary));
        }
        .folder-card:hover { transform:translateY(-3px); box-shadow:0 8px 25px rgba(0,0,0,.1); border-color:var(--folder-color, var(--primary)); }
        .folder-icon { font-size:2.25rem; margin-bottom:.875rem; display:block; }
        .folder-name { font-weight:700; font-size:1rem; margin-bottom:.375rem; }
        .folder-meta { font-size:.8rem; color:var(--muted); }
        .folder-actions { position:absolute; top:.75rem; right:.75rem; display:flex; gap:.35rem; opacity:0; transition:opacity .2s; }
        .folder-card:hover .folder-actions { opacity:1; }
        .icon-btn { width:30px; height:30px; border-radius:8px; display:flex; align-items:center; justify-content:center; border:none; cursor:pointer; font-size:.8rem; transition:all .2s; }
        .icon-btn-edit { background:rgba(99,102,241,.1); color:var(--primary); }
        .icon-btn-edit:hover { background:var(--primary); color:#fff; }
        .icon-btn-delete { background:rgba(239,68,68,.1); color:#ef4444; }
        .icon-btn-delete:hover { background:#ef4444; color:#fff; }

        /* ── New Folder Card ─────────────────────────── */
        .folder-card-new {
            background:transparent; border:2px dashed var(--border); border-radius:16px;
            padding:1.5rem; display:flex; flex-direction:column; align-items:center;
            justify-content:center; gap:.5rem; cursor:pointer; transition:all .3s;
            color:var(--muted); text-align:center; min-height:130px;
        }
        .folder-card-new:hover { border-color:var(--primary); color:var(--primary); background:rgba(99,102,241,.04); }
        .folder-card-new span { font-size:2rem; }
        .folder-card-new p { font-weight:600; font-size:.875rem; }

        /* ── Empty State ─────────────────────────────── */
        .empty-state { text-align:center; padding:4rem 2rem; color:var(--muted); }
        .empty-state-icon { font-size:4rem; margin-bottom:1.25rem; }
        .empty-state h3 { font-size:1.25rem; font-weight:700; margin-bottom:.5rem; color:var(--text); }
        .empty-state p { max-width:380px; margin:0 auto 1.5rem; line-height:1.6; }

        /* ── Modal ───────────────────────────────────── */
        .modal-overlay { position:fixed; inset:0; background:rgba(0,0,0,.5); backdrop-filter:blur(4px); z-index:1000; display:none; align-items:center; justify-content:center; }
        .modal-overlay.open { display:flex; }
        .modal { background:var(--surface); border-radius:20px; padding:2rem; width:100%; max-width:440px; box-shadow:0 25px 50px rgba(0,0,0,.15); }
        .modal-header { display:flex; justify-content:space-between; align-items:center; margin-bottom:1.5rem; }
        .modal-title { font-size:1.125rem; font-weight:700; }
        .modal-close { background:none; border:none; font-size:1.5rem; cursor:pointer; color:var(--muted); line-height:1; }
        .modal-close:hover { color:var(--text); }
        .form-group { margin-bottom:1.25rem; }
        .form-label { display:block; font-size:.875rem; font-weight:600; margin-bottom:.5rem; }
        .form-control { width:100%; padding:.75rem 1rem; border:2px solid var(--border); border-radius:10px; font-family:inherit; font-size:.875rem; background:var(--surface); color:var(--text); outline:none; transition:border-color .2s; }
        .form-control:focus { border-color:var(--primary); }
        .color-row { display:flex; gap:.5rem; flex-wrap:wrap; }
        .color-swatch { width:32px; height:32px; border-radius:8px; border:2px solid transparent; cursor:pointer; transition:all .2s; }
        .color-swatch.selected { border-color:var(--text); transform:scale(1.15); }
        .form-actions { display:flex; gap:.75rem; justify-content:flex-end; }

        @media(max-width:768px) {
            .sidebar { display:none; }
            .main { margin-left:0; }
        }

        /* ── My Folders marketplace dropdown ─────────────────────────── */
        .nav-item-group { position:relative; }
        .nav-item-group:hover .folders-dropdown,
        .folders-dropdown:hover { display:block; }
        .folders-chevron { margin-left:auto; font-size:16px; opacity:.5; transition:transform .2s; }
        .nav-item-group:hover .folders-chevron { transform:rotate(90deg); opacity:1; }
        .folders-dropdown {
            display:none; position:absolute; left:calc(100% + 4px); top:0;
            background:#fff; border:1px solid rgba(0,0,0,.1); border-radius:12px;
            box-shadow:0 8px 32px rgba(0,0,0,.12); min-width:230px; z-index:9999;
            overflow:hidden; animation:fadeInScale .15s ease;
        }
        @keyframes fadeInScale { from{opacity:0;transform:scale(.96)} to{opacity:1;transform:scale(1)} }
        .folders-dropdown-header {
            padding:10px 14px 8px; font-size:11px; font-weight:700;
            text-transform:uppercase; letter-spacing:.08em; color:#94a3b8;
            border-bottom:1px solid rgba(0,0,0,.07);
        }
        .folders-mp-item {
            display:flex; align-items:center; gap:10px; padding:10px 14px;
            cursor:pointer; transition:background .15s; text-decoration:none;
            color:#0f172a; font-size:14px; font-weight:500;
        }
        .folders-mp-item:hover { background:rgba(240,136,4,.07); }
        .folders-mp-item .mp-flag { font-size:20px; }
        .folders-mp-item .mp-name { flex:1; }
        .folders-mp-item .mp-currency { font-size:11px; color:#64748b; background:rgba(0,0,0,.06); border-radius:5px; padding:2px 6px; }
        .folders-mp-item .mp-pin { font-size:14px; opacity:.3; transition:opacity .2s; cursor:pointer; padding:2px 4px; }
        .folders-mp-item .mp-pin:hover, .folders-mp-item.pinned .mp-pin { opacity:1; }
        .folders-mp-item.pinned { background:rgba(240,136,4,.05); }
        .folders-mp-item.pinned .mp-name::after { content:' (pinned)'; font-size:10px; color:#f08804; font-weight:600; }
    </style>
</head>
<body>

<!-- Sidebar -->
<aside class="sidebar">
    <a href="/dashboard" class="sidebar-logo">
        <div class="logo-icon">📊</div>
        Amazon Analyzer
    </a>
    <nav>
        <div class="nav-section">
            <div class="nav-section-title">Main</div>
            <a href="/dashboard" class="nav-item">
                <span class="nav-item-icon">🏠</span>
                Dashboard
            </a>
            <div class="nav-item-group">
                <a href="#" class="nav-item active" onclick="openFoldersPinned(event)">
                    <span class="nav-item-icon">📁</span>
                    My Folders
                    <span class="folders-chevron">›</span>
                </a>
                <div class="folders-dropdown">
                    <div class="folders-dropdown-header">Open Folders For</div>
                    <div id="folders-dropdown-items"></div>
                </div>
            </div>
        </div>
        @if(Auth::user()->isAdmin())
        <div class="nav-section">
            <div class="nav-section-title">Admin</div>
            <a href="/admin/settings" class="nav-item">
                <span class="nav-item-icon">⚙️</span>
                Admin Tools Settings
            </a>
            <a href="{{ route('admin.pricing.index') }}" class="nav-item">
                <span class="nav-item-icon">💳</span>
                Pricing Plans
            </a>
            <a href="{{ route('admin.pricing.subscriptions') }}" class="nav-item">
                <span class="nav-item-icon">📋</span>
                Subscriptions
            </a>
            <a href="{{ route('admin.users.index') }}" class="nav-item">
                <span class="nav-item-icon">👥</span>
                Manage Users
            </a>
        </div>
        @endif
    </nav>
    <div class="sidebar-footer">
        @php $activeSub = Auth::user()->activeSubscription(); @endphp
        <div class="user-card">
            <div class="user-avatar">{{ substr(Auth::user()->name ?? 'U', 0, 1) }}</div>
            <div>
                <div class="user-name">{{ Auth::user()->name ?? 'User' }}</div>
                <div class="user-plan">{{ $activeSub ? $activeSub->plan->name . ' Plan' : 'Free Plan' }}</div>
            </div>
        </div>
    </div>
</aside>

<!-- Main -->
<main class="main">

    <div class="breadcrumb">
        <a href="/dashboard">Dashboard</a>
        <span class="sep">›</span>
        <span>My Folders</span>
    </div>

    @if(session('success'))
        <div class="alert alert-success">✓ {{ session('success') }}</div>
    @endif
    @if(session('error'))
        <div class="alert alert-danger">✗ {{ session('error') }}</div>
    @endif

    <div class="page-header">
        <h1>📁 My Folders
            <span id="mp-badge" style="font-size:14px;font-weight:600;margin-left:8px;padding:3px 10px;border-radius:20px;background:rgba(240,136,4,0.12);color:#f08804;"></span>
        </h1>
        <button class="btn btn-primary" onclick="openModal('create-folder-modal')">+ New Folder</button>
    </div>

    <!-- Folders Grid -->
    @if($folders->count() > 0)
        <div class="section-title">📂 Folders ({{ $folders->count() }})</div>
        <div class="folders-grid">
            @foreach($folders as $folder)
                <a href="/dashboard/folders/{{ $folder->id }}" class="folder-card" style="--folder-color: {{ $folder->color }}">
                    <div class="folder-actions">
                        <button class="icon-btn icon-btn-edit" onclick="event.preventDefault(); openEditModal({{ $folder->id }}, '{{ addslashes($folder->name) }}', '{{ $folder->color }}', '{{ addslashes($folder->description ?? '') }}')" title="Rename">✏️</button>
                        <button class="icon-btn icon-btn-delete" onclick="event.preventDefault(); confirmDelete({{ $folder->id }}, '{{ addslashes($folder->name) }}')" title="Delete">🗑️</button>
                    </div>
                    <span class="folder-icon">📁</span>
                    <div class="folder-name">{{ $folder->name }}</div>
                    <div class="folder-meta">
                        {{ $folder->children_count }} sub-folder{{ $folder->children_count != 1 ? 's' : '' }} ·
                        {{ $folder->lists->count() }} list{{ $folder->lists->count() != 1 ? 's' : '' }}
                    </div>
                </a>
            @endforeach

            <!-- New folder shortcut -->
            <div class="folder-card-new" onclick="openModal('create-folder-modal')">
                <span>➕</span>
                <p>New Folder</p>
            </div>
        </div>
    @else
        <div class="empty-state">
            <div class="empty-state-icon">📂</div>
            <h3>No folders yet</h3>
            <p>Create folders to organize your saved keywords, products, and analyses from the extension.</p>
            <button class="btn btn-primary" onclick="openModal('create-folder-modal')">+ Create your first folder</button>
        </div>
    @endif

</main>

<!-- ── Create Folder Modal ───────────────────────────── -->
<div id="create-folder-modal" class="modal-overlay">
    <div class="modal">
        <div class="modal-header">
            <span class="modal-title">📁 New Folder</span>
            <button class="modal-close" onclick="closeModal('create-folder-modal')">×</button>
        </div>
        <form method="POST" action="/dashboard/folders">
            @csrf
            <div class="form-group">
                <label class="form-label">Folder Name *</label>
                <input type="text" name="name" class="form-control" placeholder="e.g. Kitchen Products" required maxlength="100" id="create-folder-name">
            </div>
            <div class="form-group">
                <label class="form-label">Color</label>
                <div class="color-row">
                    @foreach(['#6366f1','#0ea5e9','#10b981','#f59e0b','#ef4444','#ec4899','#8b5cf6','#14b8a6'] as $c)
                        <div class="color-swatch {{ $c === '#6366f1' ? 'selected' : '' }}"
                             style="background:{{ $c }}"
                             onclick="selectColor(this, '{{ $c }}', 'create-color-input')"
                             title="{{ $c }}"></div>
                    @endforeach
                </div>
                <input type="hidden" name="color" id="create-color-input" value="#6366f1">
            </div>
            <div class="form-group">
                <label class="form-label">Description (optional)</label>
                <input type="text" name="description" class="form-control" placeholder="Short description…" maxlength="500">
            </div>
            <div class="form-actions">
                <button type="button" class="btn btn-outline" onclick="closeModal('create-folder-modal')">Cancel</button>
                <button type="submit" class="btn btn-primary">Create Folder</button>
            </div>
        </form>
    </div>
</div>

<!-- ── Edit Folder Modal ─────────────────────────────── -->
<div id="edit-folder-modal" class="modal-overlay">
    <div class="modal">
        <div class="modal-header">
            <span class="modal-title">✏️ Edit Folder</span>
            <button class="modal-close" onclick="closeModal('edit-folder-modal')">×</button>
        </div>
        <form id="edit-folder-form" method="POST">
            @csrf
            @method('PUT')
            <div class="form-group">
                <label class="form-label">Folder Name *</label>
                <input type="text" name="name" id="edit-folder-name" class="form-control" required maxlength="100">
            </div>
            <div class="form-group">
                <label class="form-label">Color</label>
                <div class="color-row" id="edit-color-row">
                    @foreach(['#6366f1','#0ea5e9','#10b981','#f59e0b','#ef4444','#ec4899','#8b5cf6','#14b8a6'] as $c)
                        <div class="color-swatch"
                             style="background:{{ $c }}"
                             onclick="selectColor(this, '{{ $c }}', 'edit-color-input')"
                             title="{{ $c }}"></div>
                    @endforeach
                </div>
                <input type="hidden" name="color" id="edit-color-input" value="#6366f1">
            </div>
            <div class="form-group">
                <label class="form-label">Description</label>
                <input type="text" name="description" id="edit-folder-desc" class="form-control" maxlength="500">
            </div>
            <div class="form-actions">
                <button type="button" class="btn btn-outline" onclick="closeModal('edit-folder-modal')">Cancel</button>
                <button type="submit" class="btn btn-primary">Save Changes</button>
            </div>
        </form>
    </div>
</div>

<!-- ── Delete Confirm Modal ──────────────────────────── -->
<div id="delete-folder-modal" class="modal-overlay">
    <div class="modal">
        <div class="modal-header">
            <span class="modal-title">🗑️ Delete Folder</span>
            <button class="modal-close" onclick="closeModal('delete-folder-modal')">×</button>
        </div>
        <p style="margin-bottom:1.5rem; line-height:1.6; color:var(--muted-light)">
            Are you sure you want to delete <strong id="delete-folder-name"></strong>?
            All sub-folders, lists and saved items inside will be permanently removed.
        </p>
        <form id="delete-folder-form" method="POST">
            @csrf
            @method('DELETE')
            <div class="form-actions">
                <button type="button" class="btn btn-outline" onclick="closeModal('delete-folder-modal')">Cancel</button>
                <button type="submit" class="btn btn-danger">Yes, Delete</button>
            </div>
        </form>
    </div>
</div>

<script>
    const COLORS = ['#6366f1','#0ea5e9','#10b981','#f59e0b','#ef4444','#ec4899','#8b5cf6','#14b8a6'];

    function openModal(id)  { document.getElementById(id).classList.add('open'); }
    function closeModal(id) { document.getElementById(id).classList.remove('open'); }

    // Close on backdrop click
    document.querySelectorAll('.modal-overlay').forEach(overlay => {
        overlay.addEventListener('click', e => { if (e.target === overlay) overlay.classList.remove('open'); });
    });

    function selectColor(el, color, inputId) {
        el.closest('.color-row').querySelectorAll('.color-swatch').forEach(s => s.classList.remove('selected'));
        el.classList.add('selected');
        document.getElementById(inputId).value = color;
    }

    function openEditModal(id, name, color, description) {
        document.getElementById('edit-folder-form').action = '/dashboard/folders/' + id;
        document.getElementById('edit-folder-name').value = name;
        document.getElementById('edit-folder-desc').value = description;
        document.getElementById('edit-color-input').value = color;
        // Highlight matching swatch
        document.getElementById('edit-color-row').querySelectorAll('.color-swatch').forEach(s => {
            s.classList.toggle('selected', s.title === color);
        });
        openModal('edit-folder-modal');
    }

    function confirmDelete(id, name) {
        document.getElementById('delete-folder-name').textContent = '"' + name + '"';
        document.getElementById('delete-folder-form').action = '/dashboard/folders/' + id;
        openModal('delete-folder-modal');
    }
</script>

</body>
</html>
<script>
(function() {
    const STORAGE_KEY = 'sela_pinned_marketplace';
    const MARKETPLACES = [
        { code: 'amazon.eg',  flag: '🇪🇬', name: 'Egypt',        currency: 'EGP' },
        { code: 'amazon.sa',  flag: '🇸🇦', name: 'Saudi Arabia', currency: 'SAR' },
        { code: 'amazon.ae',  flag: '🇦🇪', name: 'UAE',          currency: 'AED' },
        { code: 'amazon.com', flag: '🇺🇸', name: 'USA',          currency: 'USD' },
    ];
    const params = new URLSearchParams(window.location.search);
    const currentMp = params.get('marketplace') || 'amazon.eg';

    function getPinned() { return localStorage.getItem(STORAGE_KEY) || 'amazon.eg'; }
    function setPinned(code) { localStorage.setItem(STORAGE_KEY, code); }
    function foldersUrl(code) { return '/dashboard/folders?marketplace=' + encodeURIComponent(code); }

    function renderDropdown() {
        const pinned = getPinned();
        const container = document.getElementById('folders-dropdown-items');
        if (!container) return;
        const sorted = [...MARKETPLACES].sort((a, b) => a.code === pinned ? -1 : b.code === pinned ? 1 : 0);
        container.innerHTML = sorted.map(mp => `
            <a class="folders-mp-item ${mp.code === pinned ? 'pinned' : ''}"
               href="${foldersUrl(mp.code)}" data-code="${mp.code}">
                <span class="mp-flag">${mp.flag}</span>
                <span class="mp-name">${mp.name}</span>
                <span class="mp-currency">${mp.currency}</span>
                <span class="mp-pin" title="Pin" onclick="event.preventDefault();event.stopPropagation();pinMarketplace('${mp.code}')">
                    ${mp.code === pinned ? '📌' : '📍'}
                </span>
            </a>`).join('');
    }
    window.pinMarketplace = function(code) { setPinned(code); renderDropdown(); };
    window.openFoldersPinned = function(e) { e.preventDefault(); window.location.href = foldersUrl(getPinned()); };

    document.addEventListener('DOMContentLoaded', function() {
        renderDropdown();
        // Show current marketplace badge
        const mp = MARKETPLACES.find(m => m.code === currentMp);
        const badge = document.getElementById('mp-badge');
        if (badge && mp) badge.textContent = mp.flag + ' ' + mp.name + ' (' + mp.currency + ')';
        // Inject hidden marketplace field into the create-folder form
        document.querySelectorAll('form').forEach(f => {
            if (!f.querySelector('[name=marketplace]')) {
                const h = document.createElement('input');
                h.type = 'hidden'; h.name = 'marketplace'; h.value = currentMp;
                f.appendChild(h);
            }
        });
    });
})();
</script>
