<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ $folder->name }} - Keyword Folder</title>
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
        
        .nav-links { display: flex; gap: 24px; }
        .nav-links a { color: #475569; text-decoration: none; font-size: 14px; font-weight: 500; transition: color 0.2s; }
        .nav-links a:hover, .nav-links a.active { color: #6366f1; }
        
        .container { max-width: 1400px; margin: 0 auto; padding: 40px 24px; }
        
        .folder-header {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            margin-bottom: 24px;
            flex-wrap: wrap;
            gap: 20px;
        }
        
        .folder-info { display: flex; align-items: center; gap: 16px; }
        
        .back-btn {
            color: #475569;
            text-decoration: none;
            font-size: 28px;
            transition: color 0.2s;
        }
        .back-btn:hover { color: #0f172a; }
        
        .folder-icon {
            width: 56px;
            height: 56px;
            border-radius: 14px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 24px;
        }
        
        .folder-title { font-size: 24px; font-weight: 700; color: #0f172a; }
        .folder-meta { font-size: 13px; color: #475569; margin-top: 4px; }
        
        .action-bar { display: flex; gap: 12px; flex-wrap: wrap; }
        
        .btn {
            padding: 10px 18px;
            border-radius: 8px;
            font-size: 13px;
            font-weight: 600;
            cursor: pointer;
            display: flex;
            align-items: center;
            gap: 6px;
            transition: all 0.2s;
            border: none;
            text-decoration: none;
        }
        
        .btn-primary {
            background: linear-gradient(135deg, #6366f1, #4f46e5);
            color: white;
        }
        .btn-primary:hover { transform: translateY(-2px); box-shadow: 0 4px 15px rgba(99, 102, 241, 0.4); }
        
        .btn-secondary {
            background: #ffffff;
            border: 1px solid #e2e8f0;
            color: #475569;
            box-shadow: 0 1px 2px 0 rgba(0, 0, 0, 0.05);
        }
        .btn-secondary:hover { border-color: #6366f1; background: #f1f5f9; color: #0f172a; }
        
        .btn-danger { background: #ef4444; color: white; }
        
        .filter-bar {
            background: #ffffff;
            border: 1px solid #e2e8f0;
            border-radius: 12px;
            padding: 16px;
            margin-bottom: 20px;
            display: flex;
            gap: 12px;
            flex-wrap: wrap;
            align-items: center;
            box-shadow: 0 1px 3px 0 rgba(0, 0, 0, 0.05);
        }
        
        .filter-group { display: flex; align-items: center; gap: 6px; }
        .filter-label { font-size: 12px; color: #475569; }
        
        .filter-input {
            padding: 8px 10px;
            border-radius: 6px;
            border: 1px solid #e2e8f0;
            background: #ffffff;
            color: #0f172a;
            font-size: 12px;
            width: 80px;
        }
        .filter-input:focus { outline: none; border-color: #6366f1; }
        
        .search-input {
            flex: 1;
            min-width: 200px;
            padding: 8px 12px;
            border-radius: 8px;
            border: 1px solid #e2e8f0;
            background: #ffffff;
            color: #0f172a;
            font-size: 13px;
        }
        
        .keywords-table {
            width: 100%;
            border-collapse: collapse;
            background: #ffffff;
            border: 1px solid #e2e8f0;
            border-radius: 12px;
            overflow: hidden;
            box-shadow: 0 1px 3px 0 rgba(0, 0, 0, 0.05);
        }
        
        .keywords-table th {
            background: #f8fafc;
            padding: 14px 12px;
            text-align: left;
            font-size: 11px;
            text-transform: uppercase;
            color: #475569;
            border-bottom: 2px solid #e2e8f0;
            cursor: pointer;
        }
        .keywords-table th:hover { color: #6366f1; }
        
        .keywords-table td {
            padding: 12px;
            border-bottom: 1px solid #e2e8f0;
            font-size: 13px;
            color: #0f172a;
        }
        .keywords-table tr:hover { background: rgba(99, 102, 241, 0.03); }
        
        .checkbox-cell { width: 40px; }
        .keyword-text { font-weight: 500; color: #0f172a; }
        
        .badge {
            display: inline-block;
            padding: 4px 10px;
            border-radius: 6px;
            font-size: 12px;
            font-weight: 600;
        }
        .badge-primary { background: rgba(99, 102, 241, 0.1); color: #6366f1; }
        .badge-success { background: rgba(16, 185, 129, 0.1); color: #10b981; }
        .badge-warning { background: rgba(245, 158, 11, 0.1); color: #f59e0b; }
        
        .pagination {
            display: flex;
            justify-content: center;
            gap: 8px;
            margin-top: 24px;
        }
        .pagination a, .pagination span {
            padding: 8px 14px;
            background: #ffffff;
            border: 1px solid #e2e8f0;
            color: #475569;
            border-radius: 6px;
            text-decoration: none;
            font-size: 13px;
            box-shadow: 0 1px 2px 0 rgba(0, 0, 0, 0.05);
        }
        .pagination a:hover { background: #f1f5f9; color: #0f172a; }
        .pagination span.active { background: #6366f1; color: white; border-color: #6366f1; }
        
        .empty-state {
            text-align: center;
            padding: 80px 20px;
            color: #475569;
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
            max-width: 600px;
            max-height: 80vh;
            overflow-y: auto;
            box-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.1), 0 10px 10px -5px rgba(0, 0, 0, 0.04);
            color: #0f172a;
        }
        
        .modal-title { font-size: 18px; font-weight: 700; margin-bottom: 20px; color: #0f172a; }
        
        .drop-zone {
            border: 2px dashed #cbd5e1;
            border-radius: 12px;
            padding: 40px;
            text-align: center;
            cursor: pointer;
            transition: all 0.2s;
            color: #475569;
        }
        .drop-zone:hover, .drop-zone.dragover { border-color: #6366f1; background: rgba(99, 102, 241, 0.05); }
        
        textarea {
            width: 100%;
            height: 200px;
            padding: 12px;
            border-radius: 8px;
            border: 1px solid #e2e8f0;
            background: #ffffff;
            color: #0f172a;
            font-size: 13px;
            font-family: monospace;
            resize: vertical;
        }
        textarea:focus { outline: none; border-color: #6366f1; }
        
        .modal-footer { display: flex; justify-content: flex-end; gap: 12px; margin-top: 24px; }
        .btn-cancel {
            padding: 10px 20px;
            border-radius: 8px;
            border: 1px solid #e2e8f0;
            background: transparent;
            color: #475569;
            cursor: pointer;
            transition: all 0.2s;
        }
        .btn-cancel:hover { background: #f1f5f9; color: #0f172a; }
    </style>
</head>
<body>
    <nav class="nav">
        <a href="{{ route('cerebro.folders') }}" class="nav-logo">
            <span>📁</span>
            <span>{{ $folder->name }}</span>
        </a>
        <div class="nav-links">
            <a href="{{ route('dashboard') }}">Dashboard</a>
            <a href="{{ route('cerebro.folders') }}" class="active">Folders</a>
        </div>
    </nav>
    
    <div class="container">
        <div class="folder-header">
            <div class="folder-info">
                <a href="{{ route('cerebro.folders') }}" class="back-btn">←</a>
                <div class="folder-icon" style="background: {{ $folder->color }}20; color: {{ $folder->color }};">📁</div>
                <div>
                    <div class="folder-title">{{ $folder->name }}</div>
                    <div class="folder-meta">
                        {{ number_format($folder->keyword_count) }} keywords
                        @if($folder->description) • {{ $folder->description }} @endif
                    </div>
                </div>
            </div>
            <div class="action-bar">
                <button class="btn btn-secondary" onclick="openImportModal()">📤 Import CSV</button>
                <a href="/cerebro/folders/{{ $folder->id }}/export-csv" class="btn btn-secondary">📥 Export CSV</a>
                <button class="btn btn-primary" onclick="openAddModal()">➕ Add Keywords</button>
                <button class="btn btn-danger" id="deleteSelected" style="display: none;" onclick="deleteSelected()">🗑️ Delete Selected</button>
            </div>
        </div>
        
        <div class="filter-bar">
            <div class="filter-group">
                <span class="filter-label">Volume:</span>
                <input type="number" class="filter-input" id="minVolume" placeholder="Min" value="{{ request('min_volume') }}">
                <span style="color: #6b7280;">-</span>
                <input type="number" class="filter-input" id="maxVolume" placeholder="Max" value="{{ request('max_volume') }}">
            </div>
            <div class="filter-group">
                <span class="filter-label">IQ ≥</span>
                <input type="number" class="filter-input" id="minIq" placeholder="0" step="0.5" value="{{ request('min_iq') }}">
            </div>
            <input type="text" class="search-input" id="searchKeyword" placeholder="🔍 Search keywords..." value="{{ request('search') }}">
            <button class="btn btn-primary" onclick="applyFilters()">Apply</button>
            <button class="btn btn-secondary" onclick="clearFilters()">Clear</button>
        </div>
        
        @if(count($keywords) > 0)
            <table class="keywords-table">
                <thead>
                    <tr>
                        <th class="checkbox-cell"><input type="checkbox" id="selectAll" onchange="toggleSelectAll()"></th>
                        <th>Keyword</th>
                        <th onclick="sortBy('search_volume')">Volume ↕</th>
                        <th onclick="sortBy('cerebro_iq_score')">IQ Score ↕</th>
                        <th>CPR</th>
                        <th>Words</th>
                        <th>Source</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($keywords as $kw)
                        <tr>
                            <td class="checkbox-cell"><input type="checkbox" class="keyword-checkbox" value="{{ $kw->id }}" onchange="updateDeleteButton()"></td>
                            <td class="keyword-text">{{ $kw->keyword }}</td>
                            <td><span class="badge badge-primary">{{ number_format($kw->search_volume) }}</span></td>
                            <td>
                                <span class="badge {{ $kw->cerebro_iq_score >= 5 ? 'badge-success' : ($kw->cerebro_iq_score >= 3 ? 'badge-warning' : 'badge-primary') }}">
                                    {{ number_format($kw->cerebro_iq_score, 1) }}
                                </span>
                            </td>
                            <td>{{ $kw->cpr_8day ?? '-' }}</td>
                            <td>{{ $kw->word_count }}</td>
                            <td style="color: #9ca3af; font-size: 11px;">{{ $kw->source }}</td>
                            <td><button class="btn btn-secondary" style="padding: 4px 8px; font-size: 11px;" onclick="deleteKeyword({{ $kw->id }})">🗑️</button></td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
            
            <div class="pagination">
                @if($keywords->onFirstPage())
                    <span>← Prev</span>
                @else
                    <a href="{{ $keywords->previousPageUrl() }}">← Prev</a>
                @endif
                
                <span class="active">{{ $keywords->currentPage() }} / {{ $keywords->lastPage() }}</span>
                
                @if($keywords->hasMorePages())
                    <a href="{{ $keywords->nextPageUrl() }}">Next →</a>
                @else
                    <span>Next →</span>
                @endif
            </div>
        @else
            <div class="empty-state">
                <div style="font-size: 64px; margin-bottom: 16px;">📝</div>
                <h3 style="font-size: 20px; margin-bottom: 8px; color: #0f172a;">No keywords yet</h3>
                <p>Add keywords manually or import from CSV</p>
            </div>
        @endif
    </div>

    <!-- Import CSV Modal -->
    <div class="modal-backdrop" id="importModal">
        <div class="modal">
            <div class="modal-title">📤 Import Keywords from CSV</div>
            <form id="importForm" action="/cerebro/folders/{{ $folder->id }}/import-csv" method="POST" enctype="multipart/form-data">
                @csrf
                <div class="drop-zone" id="dropZone" onclick="document.getElementById('csvFile').click()">
                    <div style="font-size: 36px; margin-bottom: 12px;">📄</div>
                    <p style="margin-bottom: 8px; font-weight: 600;">Drop CSV file here or click to browse</p>
                    <p style="font-size: 12px; color: #475569;">CSV must have a "keyword" column</p>
                    <input type="file" id="csvFile" name="file" accept=".csv,.txt" style="display: none;" onchange="handleFileSelect(this)">
                </div>
                <div id="selectedFile" style="display: none; margin-top: 16px; padding: 12px; background: #f1f5f9; border: 1px solid #e2e8f0; border-radius: 8px;">
                    <span id="fileName"></span>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn-cancel" onclick="closeImportModal()">Cancel</button>
                    <button type="submit" class="btn btn-primary" id="importBtn" disabled>Import</button>
                </div>
            </form>
        </div>
    </div>

    <!-- Add Keywords Modal -->
    <div class="modal-backdrop" id="addModal">
        <div class="modal">
            <div class="modal-title">➕ Add Keywords</div>
            <label style="font-size: 13px; color: #9ca3af; margin-bottom: 8px; display: block;">Enter keywords (one per line)</label>
            <textarea id="keywordsText" placeholder="keyword one&#10;keyword two&#10;keyword three"></textarea>
            <div class="modal-footer">
                <button type="button" class="btn-cancel" onclick="closeAddModal()">Cancel</button>
                <button type="button" class="btn btn-primary" onclick="addKeywords()">Add Keywords</button>
            </div>
        </div>
    </div>

    <script>
        const csrfToken = document.querySelector('meta[name="csrf-token"]').content;
        const folderId = {{ $folder->id }};
        
        function openImportModal() { document.getElementById('importModal').classList.add('active'); }
        function closeImportModal() { document.getElementById('importModal').classList.remove('active'); }
        function openAddModal() { document.getElementById('addModal').classList.add('active'); }
        function closeAddModal() { document.getElementById('addModal').classList.remove('active'); }
        
        function handleFileSelect(input) {
            if (input.files.length > 0) {
                document.getElementById('fileName').textContent = '📄 ' + input.files[0].name;
                document.getElementById('selectedFile').style.display = 'block';
                document.getElementById('importBtn').disabled = false;
            }
        }
        
        const dropZone = document.getElementById('dropZone');
        dropZone.addEventListener('dragover', (e) => { e.preventDefault(); dropZone.classList.add('dragover'); });
        dropZone.addEventListener('dragleave', () => dropZone.classList.remove('dragover'));
        dropZone.addEventListener('drop', (e) => {
            e.preventDefault();
            dropZone.classList.remove('dragover');
            document.getElementById('csvFile').files = e.dataTransfer.files;
            handleFileSelect(document.getElementById('csvFile'));
        });
        
        async function addKeywords() {
            const text = document.getElementById('keywordsText').value;
            const lines = text.split('\n').map(l => l.trim()).filter(l => l.length > 0);
            
            if (lines.length === 0) { alert('Please enter at least one keyword'); return; }
            
            const keywords = lines.map(keyword => ({ keyword, source: 'manual' }));
            
            const response = await fetch('/cerebro/folders/' + folderId + '/keywords', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': csrfToken, 'Accept': 'application/json' },
                body: JSON.stringify({ keywords })
            });
            
            const data = await response.json();
            if (data.success) {
                alert(`Added ${data.added} keywords, ${data.duplicates} duplicates skipped`);
                window.location.reload();
            } else { alert('Failed to add keywords'); }
        }
        
        function toggleSelectAll() {
            const checked = document.getElementById('selectAll').checked;
            document.querySelectorAll('.keyword-checkbox').forEach(cb => cb.checked = checked);
            updateDeleteButton();
        }
        
        function updateDeleteButton() {
            const selected = document.querySelectorAll('.keyword-checkbox:checked').length;
            document.getElementById('deleteSelected').style.display = selected > 0 ? 'flex' : 'none';
        }
        
        async function deleteSelected() {
            const ids = Array.from(document.querySelectorAll('.keyword-checkbox:checked')).map(cb => parseInt(cb.value));
            if (!confirm(`Delete ${ids.length} keywords?`)) return;
            
            const response = await fetch('/cerebro/folders/' + folderId + '/keywords', {
                method: 'DELETE',
                headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': csrfToken, 'Accept': 'application/json' },
                body: JSON.stringify({ keyword_ids: ids })
            });
            
            if ((await response.json()).success) window.location.reload();
        }
        
        async function deleteKeyword(id) {
            if (!confirm('Delete this keyword?')) return;
            
            const response = await fetch('/cerebro/folders/' + folderId + '/keywords', {
                method: 'DELETE',
                headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': csrfToken, 'Accept': 'application/json' },
                body: JSON.stringify({ keyword_ids: [id] })
            });
            
            if ((await response.json()).success) window.location.reload();
        }
        
        function applyFilters() {
            const params = new URLSearchParams();
            const minVol = document.getElementById('minVolume').value;
            const maxVol = document.getElementById('maxVolume').value;
            const minIq = document.getElementById('minIq').value;
            const search = document.getElementById('searchKeyword').value;
            
            if (minVol) params.set('min_volume', minVol);
            if (maxVol) params.set('max_volume', maxVol);
            if (minIq) params.set('min_iq', minIq);
            if (search) params.set('search', search);
            
            window.location.search = params.toString();
        }
        
        function clearFilters() { window.location.search = ''; }
        
        function sortBy(column) {
            const params = new URLSearchParams(window.location.search);
            const current = params.get('sort_by');
            const currentDir = params.get('sort_dir') || 'desc';
            
            params.set('sort_by', column);
            params.set('sort_dir', current === column && currentDir === 'desc' ? 'asc' : 'desc');
            
            window.location.search = params.toString();
        }
        
        document.querySelectorAll('.modal-backdrop').forEach(modal => {
            modal.addEventListener('click', (e) => { if (e.target === modal) modal.classList.remove('active'); });
        });
    </script>
</body>
</html>
