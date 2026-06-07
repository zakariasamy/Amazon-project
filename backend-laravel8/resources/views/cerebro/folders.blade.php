<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Keyword Folders - Dashboard</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        
        body {
            font-family: 'Inter', -apple-system, system-ui, sans-serif;
            background: #f8fafc;
            color: #0f172a;
            min-height: 100vh;
        }
        
        .nav {
            background: #ffffff;
            border-bottom: 1px solid #e2e8f0;
            padding: 16px 24px;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        
        .nav-logo {
            display: flex;
            align-items: center;
            gap: 12px;
            font-size: 18px;
            font-weight: 700;
            color: #0f172a;
            text-decoration: none;
        }
        
        .nav-links {
            display: flex;
            gap: 24px;
        }
        
        .nav-links a {
            color: #475569;
            text-decoration: none;
            font-size: 14px;
            font-weight: 500;
            transition: color 0.2s;
        }
        
        .nav-links a:hover, .nav-links a.active {
            color: #6366f1;
        }
        
        .container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 40px 24px;
        }
        
        .folders-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 32px;
        }
        
        .page-title {
            font-size: 28px;
            font-weight: 700;
            color: #0f172a;
        }
        
        .page-subtitle {
            color: #475569;
            font-size: 14px;
            margin-top: 4px;
        }
        
        .btn-primary {
            background: linear-gradient(135deg, #6366f1, #4f46e5);
            border: none;
            color: white;
            padding: 12px 24px;
            border-radius: 10px;
            font-weight: 600;
            cursor: pointer;
            display: flex;
            align-items: center;
            gap: 8px;
            font-size: 14px;
            transition: all 0.2s;
        }
        
        .btn-primary:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 15px rgba(99, 102, 241, 0.4);
        }
        
        .folders-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(320px, 1fr));
            gap: 20px;
        }
        
        .folder-card {
            background: #ffffff;
            border: 1px solid #e2e8f0;
            border-radius: 12px;
            padding: 20px;
            transition: all 0.2s;
            cursor: pointer;
            box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.05), 0 2px 4px -1px rgba(0, 0, 0, 0.03);
        }
        
        .folder-card:hover {
            border-color: #6366f1;
            transform: translateY(-2px);
            box-shadow: 0 10px 30px rgba(99, 102, 241, 0.12);
        }
        
        .folder-header {
            display: flex;
            align-items: center;
            gap: 12px;
            margin-bottom: 12px;
        }
        
        .folder-icon {
            width: 44px;
            height: 44px;
            border-radius: 10px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 20px;
        }
        
        .folder-name {
            font-size: 16px;
            font-weight: 600;
            color: #0f172a;
        }
        
        .folder-desc {
            font-size: 12px;
            color: #475569;
        }
        
        .folder-stats {
            display: flex;
            gap: 24px;
            padding-top: 16px;
            border-top: 1px solid #e2e8f0;
            margin-top: 16px;
        }
        
        .folder-stat {
            text-align: center;
        }
        
        .folder-stat-value {
            font-size: 20px;
            font-weight: 700;
            color: #6366f1;
        }
        
        .folder-stat-label {
            font-size: 10px;
            color: #64748b;
            text-transform: uppercase;
            margin-top: 2px;
        }
        
        .folder-actions {
            display: flex;
            gap: 8px;
            margin-top: 16px;
        }
        
        .btn-action {
            flex: 1;
            padding: 8px;
            border-radius: 6px;
            border: 1px solid #e2e8f0;
            background: transparent;
            color: #475569;
            font-size: 12px;
            cursor: pointer;
            transition: all 0.2s;
        }
        
        .btn-action:hover {
            background: #f1f5f9;
            color: #0f172a;
        }
        
        .btn-action.danger:hover {
            background: #ef4444;
            border-color: #ef4444;
            color: #fff;
        }
        
        .empty-state {
            text-align: center;
            padding: 80px 20px;
            color: #475569;
        }
        
        .empty-icon {
            font-size: 64px;
            margin-bottom: 16px;
        }
        
        .modal-backdrop {
            position: fixed;
            top: 0; left: 0; right: 0; bottom: 0;
            background: rgba(15, 23, 42, 0.4);
            backdrop-filter: blur(4px);
            z-index: 1000;
            display: none;
            align-items: center;
            justify-content: center;
        }
        
        .modal-backdrop.active { display: flex; }
        
        .modal {
            background: #ffffff;
            border: 1px solid #e2e8f0;
            border-radius: 16px;
            padding: 24px;
            width: 100%;
            max-width: 450px;
            box-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.1), 0 10px 10px -5px rgba(0, 0, 0, 0.04);
        }
        
        .modal-title {
            font-size: 18px;
            font-weight: 700;
            margin-bottom: 24px;
            color: #0f172a;
        }
        
        .form-group {
            margin-bottom: 20px;
        }
        
        .form-label {
            display: block;
            margin-bottom: 8px;
            font-size: 13px;
            color: #475569;
            font-weight: 600;
        }
        
        .form-input {
            width: 100%;
            padding: 12px;
            border-radius: 8px;
            border: 1px solid #e2e8f0;
            background: #ffffff;
            color: #0f172a;
            font-size: 14px;
        }
        
        .form-input:focus {
            outline: none;
            border-color: #6366f1;
        }
        
        .color-options {
            display: flex;
            gap: 10px;
        }
        
        .color-option {
            width: 36px;
            height: 36px;
            border-radius: 50%;
            border: 3px solid transparent;
            cursor: pointer;
            transition: all 0.2s;
        }
        
        .color-option:hover, .color-option.selected {
            border-color: #0f172a;
            transform: scale(1.1);
        }
        
        .modal-footer {
            display: flex;
            justify-content: flex-end;
            gap: 12px;
            margin-top: 28px;
        }
        
        .btn-cancel {
            padding: 10px 20px;
            border-radius: 8px;
            border: 1px solid #e2e8f0;
            background: transparent;
            color: #475569;
            cursor: pointer;
            font-size: 14px;
        }
        
        .btn-cancel:hover {
            background: #f1f5f9;
            color: #0f172a;
        }
    </style>
</head>
<body>
    <nav class="nav">
        <a href="/" class="nav-logo">
            <span>📁</span>
            <span>Keyword Folders</span>
        </a>
        <div class="nav-links">
            <a href="{{ route('dashboard') }}">Dashboard</a>
            <a href="{{ route('cerebro.folders') }}" class="active">Folders</a>
        </div>
    </nav>
    
    <div class="container">
        <div class="folders-header">
            <div>
                <h1 class="page-title">📁 Keyword Folders</h1>
                <p class="page-subtitle">Organize your keywords into folders for easy management</p>
            </div>
            <button class="btn-primary" onclick="openCreateModal()">
                <span>➕</span>
                <span>New Folder</span>
            </button>
        </div>
        
        @if(count($folders) > 0)
            <div class="folders-grid">
                @foreach($folders as $folder)
                    <div class="folder-card" onclick="window.location='{{ route('cerebro.folder.show', $folder->id) }}'">
                        <div class="folder-header">
                            <div class="folder-icon" style="background: {{ $folder->color }}20; color: {{ $folder->color }};">📁</div>
                            <div>
                                <div class="folder-name">{{ $folder->name }}</div>
                                @if($folder->description)
                                    <div class="folder-desc">{{ Str::limit($folder->description, 40) }}</div>
                                @endif
                            </div>
                        </div>
                        <div class="folder-stats">
                            <div class="folder-stat">
                                <div class="folder-stat-value">{{ number_format($folder->keyword_count) }}</div>
                                <div class="folder-stat-label">Keywords</div>
                            </div>
                            <div class="folder-stat">
                                <div class="folder-stat-value" style="color: #10b981; font-size: 14px;">{{ $folder->created_at->diffForHumans() }}</div>
                                <div class="folder-stat-label">Created</div>
                            </div>
                        </div>
                        <div class="folder-actions" onclick="event.stopPropagation();">
                            <button class="btn-action" onclick="openEditModal({{ $folder->id }}, '{{ addslashes($folder->name) }}', '{{ $folder->color }}', '{{ addslashes($folder->description ?? '') }}')">✏️ Edit</button>
                            <a class="btn-action" href="/cerebro/folders/{{ $folder->id }}/export-csv">📥 Export</a>
                            <button class="btn-action danger" onclick="deleteFolder({{ $folder->id }})">🗑️</button>
                        </div>
                    </div>
                @endforeach
            </div>
        @else
            <div class="empty-state">
                <div class="empty-icon">📁</div>
                <h3 style="font-size: 20px; margin-bottom: 8px; color: #fff;">No folders yet</h3>
                <p>Create your first folder to start organizing keywords</p>
            </div>
        @endif
    </div>

    <!-- Create/Edit Modal -->
    <div class="modal-backdrop" id="folderModal">
        <div class="modal">
            <div class="modal-title" id="modalTitle">Create New Folder</div>
            <form id="folderForm">
                <input type="hidden" id="folderId">
                <input type="hidden" id="formMethod" value="POST">
                
                <div class="form-group">
                    <label class="form-label">Folder Name</label>
                    <input type="text" class="form-input" id="folderName" required placeholder="e.g., Main Keywords">
                </div>
                
                <div class="form-group">
                    <label class="form-label">Color</label>
                    <div class="color-options">
                        <div class="color-option selected" style="background: #6366f1;" data-color="#6366f1" onclick="selectColor(this)"></div>
                        <div class="color-option" style="background: #10b981;" data-color="#10b981" onclick="selectColor(this)"></div>
                        <div class="color-option" style="background: #f59e0b;" data-color="#f59e0b" onclick="selectColor(this)"></div>
                        <div class="color-option" style="background: #ef4444;" data-color="#ef4444" onclick="selectColor(this)"></div>
                        <div class="color-option" style="background: #0ea5e9;" data-color="#0ea5e9" onclick="selectColor(this)"></div>
                        <div class="color-option" style="background: #8b5cf6;" data-color="#8b5cf6" onclick="selectColor(this)"></div>
                        <div class="color-option" style="background: #ec4899;" data-color="#ec4899" onclick="selectColor(this)"></div>
                    </div>
                    <input type="hidden" id="folderColor" value="#6366f1">
                </div>
                
                <div class="form-group">
                    <label class="form-label">Description (optional)</label>
                    <input type="text" class="form-input" id="folderDescription" placeholder="Brief description...">
                </div>
                
                <div class="modal-footer">
                    <button type="button" class="btn-cancel" onclick="closeModal()">Cancel</button>
                    <button type="submit" class="btn-primary">Save Folder</button>
                </div>
            </form>
        </div>
    </div>

    <script>
        const csrfToken = document.querySelector('meta[name="csrf-token"]').content;
        
        function openCreateModal() {
            document.getElementById('modalTitle').textContent = 'Create New Folder';
            document.getElementById('formMethod').value = 'POST';
            document.getElementById('folderId').value = '';
            document.getElementById('folderName').value = '';
            document.getElementById('folderColor').value = '#6366f1';
            document.getElementById('folderDescription').value = '';
            document.querySelectorAll('.color-option').forEach(c => c.classList.remove('selected'));
            document.querySelector('.color-option[data-color="#6366f1"]').classList.add('selected');
            document.getElementById('folderModal').classList.add('active');
        }
        
        function openEditModal(id, name, color, description) {
            document.getElementById('modalTitle').textContent = 'Edit Folder';
            document.getElementById('formMethod').value = 'PUT';
            document.getElementById('folderId').value = id;
            document.getElementById('folderName').value = name;
            document.getElementById('folderColor').value = color;
            document.getElementById('folderDescription').value = description;
            document.querySelectorAll('.color-option').forEach(c => c.classList.remove('selected'));
            const colorOption = document.querySelector(`.color-option[data-color="${color}"]`);
            if (colorOption) colorOption.classList.add('selected');
            document.getElementById('folderModal').classList.add('active');
        }
        
        function closeModal() {
            document.getElementById('folderModal').classList.remove('active');
        }
        
        function selectColor(el) {
            document.querySelectorAll('.color-option').forEach(c => c.classList.remove('selected'));
            el.classList.add('selected');
            document.getElementById('folderColor').value = el.dataset.color;
        }
        
        async function deleteFolder(id) {
            if (!confirm('Are you sure you want to delete this folder? All keywords inside will be deleted.')) return;
            
            await fetch('/cerebro/folders/' + id, {
                method: 'DELETE',
                headers: {
                    'X-CSRF-TOKEN': csrfToken,
                    'Accept': 'application/json'
                }
            });
            window.location.reload();
        }
        
        document.getElementById('folderModal').addEventListener('click', function(e) {
            if (e.target === this) closeModal();
        });
        
        document.getElementById('folderForm').addEventListener('submit', async function(e) {
            e.preventDefault();
            
            const method = document.getElementById('formMethod').value;
            const id = document.getElementById('folderId').value;
            const url = method === 'POST' ? '/cerebro/folders' : '/cerebro/folders/' + id;
            
            const response = await fetch(url, {
                method: method,
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': csrfToken,
                    'Accept': 'application/json'
                },
                body: JSON.stringify({
                    name: document.getElementById('folderName').value,
                    color: document.getElementById('folderColor').value,
                    description: document.getElementById('folderDescription').value
                })
            });
            
            if (response.ok) {
                window.location.reload();
            } else {
                alert('Failed to save folder');
            }
        });
    </script>
</body>
</html>
