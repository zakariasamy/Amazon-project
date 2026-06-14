/**
 * SaveToList - Shared "Save to List" picker for the Chrome extension.
 *
 * Renders a floating panel that lets the user:
 *  1. Browse folders (flat structure, parent shown via indent)
 *  2. Choose or create a list of the correct type
 *  3. POST the items to the backend
 *
 * Usage:
 *   const picker = new SaveToList({ listType: 'keyword_magnet', items: [...], baseUrl: '...' });
 *   picker.open();
 */
class SaveToList {
    /**
     * @param {Object} opts
     * @param {string} opts.listType  - One of: products, keyword_magnet, competitor_keyword_analyzer, reverse_asin
     * @param {Array}  opts.items     - Array of plain objects to save (one per row)
     * @param {string} opts.baseUrl   - Backend base URL
     * @param {string} opts.token     - Auth token
     * @param {Function} [opts.onSuccess] - Callback(count) after successful save
     */
    constructor({ listType, items, description, baseUrl, token, onSuccess }) {
        this.listType    = listType;
        this.items       = items;
        this.description = description || null;
        this.baseUrl     = baseUrl || 'http://127.0.0.1:8000';
        this.token       = token || '';
        this.onSuccess   = onSuccess || null;

        this.folders = [];
        this.selectedFolderId = null;
        this.selectedListId   = null;
        this.overlay  = null;
        this.panel    = null;
        this.isSaving = false;

        // Type meta
        this.TYPE_LABELS = {
            'products':                    'Products',
            'keyword_magnet':              'Keyword Magnet',
            'competitor_keyword_analyzer': 'Competitor Keyword Analyzer',
            'reverse_asin':                'Reverse ASIN',
            'market_analysis':             'Market Analysis',
        };
        this.TYPE_ICONS = {
            'products':                    '📦',
            'keyword_magnet':              '🧲',
            'competitor_keyword_analyzer': '🔍',
            'reverse_asin':                '🔄',
            'market_analysis':             '📊',
        };
    }

    // ── Public API ──────────────────────────────────────────────────────────

    async open() {
        // Detect invalidated extension context BEFORE opening the modal.
        // If the page wasn't refreshed after an extension reload, chrome.storage
        // and chrome.runtime are unavailable — show a friendly inline alert instead.
        if (!this._isExtensionContextValid()) {
            this._showPageRefreshAlert();
            return;
        }

        this._buildOverlay();
        await this._loadFolders();
    }

    /**
     * Returns false when the extension context has been invalidated
     * (e.g. extension was reloaded but the page was not refreshed).
     */
    _isExtensionContextValid() {
        try {
            if (typeof chrome === 'undefined' || !chrome.runtime || !chrome.runtime.id) {
                return false;
            }
            return true;
        } catch (e) {
            return false;
        }
    }

    /**
     * Shows a small fixed banner at the top of the page asking the user to
     * refresh — used when the extension context is invalidated.
     */
    _showPageRefreshAlert() {
        // Remove any existing alert
        document.getElementById('stl-refresh-alert')?.remove();

        const alert = document.createElement('div');
        alert.id = 'stl-refresh-alert';
        alert.style.cssText = `
            position: fixed;
            top: 16px;
            left: 50%;
            transform: translateX(-50%);
            background: linear-gradient(135deg, #1e293b, #0f172a);
            border: 1px solid #f59e0b;
            border-radius: 14px;
            padding: 14px 22px;
            display: flex;
            align-items: center;
            gap: 14px;
            z-index: 999999999;
            box-shadow: 0 8px 32px rgba(0,0,0,0.5);
            font-family: 'Inter', -apple-system, system-ui, sans-serif;
            max-width: 500px;
            animation: stl-fadein 0.25s ease;
        `;
        alert.innerHTML = `
            <span style="font-size:22px;flex-shrink:0;">🔄</span>
            <div style="flex:1;">
                <div style="color:#fbbf24;font-weight:700;font-size:13px;margin-bottom:3px;">Extension reloaded — page refresh needed</div>
                <div style="color:#94a3b8;font-size:12px;">The extension was updated or reloaded. Refresh the page to restore full functionality.</div>
            </div>
            <button id="stl-refresh-btn" style="
                background: #f59e0b;
                border: none;
                color: #000;
                padding: 8px 16px;
                border-radius: 9px;
                cursor: pointer;
                font-size: 12px;
                font-weight: 700;
                white-space: nowrap;
                flex-shrink: 0;
            ">Refresh Page</button>
            <button id="stl-dismiss-btn" style="
                background: transparent;
                border: none;
                color: #64748b;
                cursor: pointer;
                font-size: 18px;
                padding: 0 4px;
                line-height: 1;
            ">×</button>
        `;

        // Add animation keyframes if not already present
        if (!document.getElementById('stl-styles')) {
            const style = document.createElement('style');
            style.id = 'stl-styles';
            style.textContent = '@keyframes stl-fadein { from { opacity:0; transform:translateX(-50%) translateY(-8px); } to { opacity:1; transform:translateX(-50%) translateY(0); } }';
            document.head.appendChild(style);
        }

        document.body.appendChild(alert);

        alert.querySelector('#stl-refresh-btn').addEventListener('click', () => window.location.reload());
        alert.querySelector('#stl-dismiss-btn').addEventListener('click', () => alert.remove());

        // Auto-dismiss after 10 seconds
        setTimeout(() => alert?.remove(), 10000);
    }

    close() {
        this.overlay?.remove();
        this.overlay = null;
        this.panel   = null;
    }

    // ── UI Builder ──────────────────────────────────────────────────────────

    _buildOverlay() {
        // Remove any stale panel
        document.getElementById('stl-overlay')?.remove();

        this.overlay = document.createElement('div');
        this.overlay.id = 'stl-overlay';
        this.overlay.style.cssText = `
            position: fixed; inset: 0;
            background: rgba(0,0,0,0.6);
            backdrop-filter: blur(4px);
            z-index: 99999999;
            display: flex; align-items: center; justify-content: center;
            font-family: 'Inter', -apple-system, system-ui, sans-serif;
        `;
        this.overlay.addEventListener('click', e => { if (e.target === this.overlay) this.close(); });

        this.panel = document.createElement('div');
        this.panel.id = 'stl-panel';
        this.panel.style.cssText = `
            background: #0f172a;
            border: 1px solid #334155;
            border-radius: 20px;
            width: 460px;
            max-width: 95vw;
            max-height: 80vh;
            overflow: hidden;
            display: flex;
            flex-direction: column;
            box-shadow: 0 25px 60px rgba(0,0,0,0.5);
        `;

        const typeLabel = this.TYPE_LABELS[this.listType] || this.listType;
        const typeIcon  = this.TYPE_ICONS[this.listType] || '📋';
        const itemCount = this.items.length;

        this.panel.innerHTML = `
            <div id="stl-header" style="
                padding: 16px 20px;
                background: linear-gradient(135deg, #1e293b, #0f172a);
                border-bottom: 1px solid #334155;
                display: flex; justify-content: space-between; align-items: center;
            ">
                <div style="display:flex;align-items:center;gap:10px;">
                    <span style="font-size:22px;">${typeIcon}</span>
                    <div>
                        <div style="color:#fff;font-weight:700;font-size:15px;">Save to List</div>
                        <div style="color:#94a3b8;font-size:12px;">Saving ${itemCount} ${typeLabel} item${itemCount !== 1 ? 's' : ''}</div>
                    </div>
                </div>
                <button id="stl-close" style="
                    background:#374151;border:none;color:#9ca3af;
                    width:30px;height:30px;border-radius:8px;cursor:pointer;font-size:18px;
                    display:flex;align-items:center;justify-content:center;
                ">×</button>
            </div>

            <div id="stl-body" style="padding:20px;overflow-y:auto;flex:1;">
                <div id="stl-loading" style="text-align:center;padding:30px;color:#94a3b8;">
                    <div style="font-size:28px;margin-bottom:8px;">⏳</div>
                    Loading folders…
                </div>
            </div>

            <div id="stl-footer" style="
                padding:14px 20px;
                border-top:1px solid #334155;
                display:flex;gap:10px;justify-content:flex-end;
            ">
                <button id="stl-cancel-btn" style="
                    background:transparent;border:1px solid #374151;color:#9ca3af;
                    padding:9px 18px;border-radius:10px;cursor:pointer;font-size:13px;font-weight:600;
                ">Cancel</button>
                <button id="stl-save-btn" style="
                    background:linear-gradient(135deg,#6366f1,#0ea5e9);
                    border:none;color:#fff;padding:9px 18px;
                    border-radius:10px;cursor:pointer;font-size:13px;font-weight:600;
                    opacity:0.4;pointer-events:none;
                " disabled>Save Items</button>
            </div>
        `;

        this.overlay.appendChild(this.panel);
        document.body.appendChild(this.overlay);

        this.panel.querySelector('#stl-close').addEventListener('click', () => this.close());
        this.panel.querySelector('#stl-cancel-btn').addEventListener('click', () => this.close());
        this.panel.querySelector('#stl-save-btn').addEventListener('click', () => this._save());
    }

    // ── Data Loading ────────────────────────────────────────────────────────

    async _loadFolders() {
        try {
            const res = await fetch(`${this.baseUrl}/api/dashboard/folders`, {
                headers: this._headers(),
            });
            if (res.status === 401) {
                this._handleUnauthorized();
                return;
            }
            const data = await res.json();
            if (data.success) {
                this.folders = data.folders || [];
                this._renderFolderPicker();
            } else {
                this._showError('Could not load folders.');
            }
        } catch (e) {
            this._showError('Network error loading folders.');
        }
    }

    async _loadListsForFolder(folderId) {
        this._renderListLoading();
        try {
            const res = await fetch(`${this.baseUrl}/api/dashboard/folders/${folderId}/lists`, {
                headers: this._headers(),
            });
            if (res.status === 401) {
                this._handleUnauthorized();
                return;
            }
            const data = await res.json();
            if (data.success) {
                this._renderListPicker(data.lists || [], folderId);
            } else {
                this._showError('Could not load lists.');
            }
        } catch (e) {
            this._showError('Network error loading lists.');
        }
    }

    // ── Render States ───────────────────────────────────────────────────────

    _renderFolderPicker() {
        const body = this.panel.querySelector('#stl-body');
        if (this.folders.length === 0) {
            body.innerHTML = `
                <div style="text-align:center;padding:30px;color:#94a3b8;">
                    <div style="font-size:32px;margin-bottom:10px;">📂</div>
                    <p style="margin-bottom:14px;font-size:13px;">No folders yet.<br>Create one in the dashboard first.</p>
                    <a href="${this.baseUrl}/folders" target="_blank"
                       style="color:#6366f1;font-size:13px;font-weight:600;text-decoration:none;">
                       Open Dashboard →
                    </a>
                </div>`;
            return;
        }

        // Build tree structure
        const roots = this.folders.filter(f => !f.parent_id);
        const children = (parentId) => this.folders.filter(f => f.parent_id === parentId);

        let html = `<div style="margin-bottom:14px;color:#94a3b8;font-size:12px;font-weight:600;text-transform:uppercase;letter-spacing:.06em;">Select Folder</div>`;

        const renderFolderRow = (folder, depth = 0) => {
            const indent = depth * 18;
            html += `
                <div class="stl-folder-row" data-folder-id="${folder.id}" style="
                    display:flex;align-items:center;gap:10px;
                    padding:10px 12px;padding-left:${12 + indent}px;
                    border-radius:10px;cursor:pointer;margin-bottom:4px;
                    border:2px solid transparent;
                    color:#e2e8f0;font-size:13px;font-weight:500;
                    transition:all 0.2s;background:#1e293b;
                " onmouseover="this.style.borderColor='${folder.color || '#6366f1'}'"
                   onmouseout="if(!this.classList.contains('stl-selected'))this.style.borderColor='transparent'">
                    <span style="width:10px;height:10px;border-radius:3px;background:${folder.color || '#6366f1'};flex-shrink:0;"></span>
                    <span>📁 ${this._esc(folder.name)}</span>
                </div>`;
            children(folder.id).forEach(c => renderFolderRow(c, depth + 1));
        };

        roots.forEach(f => renderFolderRow(f, 0));

        body.innerHTML = html;

        body.querySelectorAll('.stl-folder-row').forEach(row => {
            row.addEventListener('click', () => {
                body.querySelectorAll('.stl-folder-row').forEach(r => {
                    r.classList.remove('stl-selected');
                    r.style.borderColor = 'transparent';
                    r.style.background = '#1e293b';
                });
                row.classList.add('stl-selected');
                row.style.borderColor = '#6366f1';
                row.style.background = '#1e2944';
                this.selectedFolderId = parseInt(row.dataset.folderId);
                this.selectedListId = null;
                this._disableSave();
                this._loadListsForFolder(this.selectedFolderId);
            });
        });
    }

    _renderListLoading() {
        // Append list section below folder picker
        let listSection = this.panel.querySelector('#stl-list-section');
        if (!listSection) {
            listSection = document.createElement('div');
            listSection.id = 'stl-list-section';
            listSection.style.marginTop = '16px';
            this.panel.querySelector('#stl-body').appendChild(listSection);
        }
        listSection.innerHTML = `<div style="color:#94a3b8;font-size:12px;padding:8px 0;">Loading lists…</div>`;
    }

    _renderListPicker(lists, folderId) {
        let listSection = this.panel.querySelector('#stl-list-section');
        if (!listSection) {
            listSection = document.createElement('div');
            listSection.id = 'stl-list-section';
            listSection.style.marginTop = '16px';
            this.panel.querySelector('#stl-body').appendChild(listSection);
        }

        // Filter by matching type
        const compatible = lists.filter(l => l.type === this.listType);
        const typeLabel  = this.TYPE_LABELS[this.listType];
        const typeIcon   = this.TYPE_ICONS[this.listType];

        // For competitor_keyword_analyzer: each analysis run must go into its own fresh list
        const isAnalyzerType = this.listType === 'competitor_keyword_analyzer';

        let html = `<hr style="border:none;border-top:1px solid #334155;margin-bottom:14px;">
            <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:10px;">
                <div style="color:#94a3b8;font-size:12px;font-weight:600;text-transform:uppercase;letter-spacing:.06em;">${typeIcon} ${typeLabel} Lists</div>
                <button id="stl-new-list-btn" style="
                    background:#6366f1;border:none;color:#fff;padding:5px 12px;
                    border-radius:8px;cursor:pointer;font-size:11px;font-weight:600;
                ">+ New List</button>
            </div>`;

        if (isAnalyzerType) {
            html += `<div style="color:#64748b;font-size:11px;padding:4px 0 10px 0;display:flex;align-items:flex-start;gap:6px;">
                <span style="color:#f59e0b;flex-shrink:0;">⚠️</span>
                <span>Each analysis run must be saved to its own <strong style="color:#94a3b8;">empty list</strong>. Lists with existing data are locked.</span>
            </div>`;
        }

        if (compatible.length === 0) {
            html += `<div style="color:#64748b;font-size:12px;padding:8px 0;">No ${typeLabel} lists in this folder yet. Create one below.</div>`;
        } else {
            compatible.forEach(list => {
                const isLocked = isAnalyzerType && list.item_count > 0;
                if (isLocked) {
                    html += `
                        <div class="stl-list-row-locked" style="
                            padding:10px 12px;border-radius:10px;
                            border:2px solid #1e293b;margin-bottom:4px;
                            color:#475569;font-size:13px;background:#111827;cursor:not-allowed;
                        " title="This list already contains data. Create a new list for this run.">
                            <div style="display:flex;justify-content:space-between;align-items:center;">
                                <div style="font-weight:600;">${this._esc(list.name)}</div>
                                <span style="font-size:10px;background:rgba(239,68,68,.15);color:#f87171;padding:2px 8px;border-radius:20px;font-weight:700;">🔒 Locked</span>
                            </div>
                            <div style="color:#374151;font-size:11px;margin-top:2px;">${list.item_count} item${list.item_count !== 1 ? 's' : ''} — already has analysis data</div>
                        </div>`;
                } else {
                    html += `
                        <div class="stl-list-row" data-list-id="${list.id}" style="
                            padding:10px 12px;border-radius:10px;cursor:pointer;
                            border:2px solid transparent;margin-bottom:4px;
                            color:#e2e8f0;font-size:13px;
                            background:#1e293b;transition:all 0.2s;
                        " onmouseover="this.style.borderColor='#6366f1'"
                           onmouseout="if(!this.classList.contains('stl-selected'))this.style.borderColor='transparent'">
                            <div style="display:flex;justify-content:space-between;align-items:center;">
                                <div style="font-weight:600;">${this._esc(list.name)}</div>
                                ${isAnalyzerType ? `<span style="font-size:10px;background:rgba(16,185,129,.15);color:#34d399;padding:2px 8px;border-radius:20px;font-weight:700;">✓ Empty</span>` : ''}
                            </div>
                            <div style="color:#64748b;font-size:11px;margin-top:2px;">${list.item_count} item${list.item_count !== 1 ? 's' : ''} saved</div>
                        </div>`;
                }
            });
        }

        // New list inline form (hidden by default)
        html += `
            <div id="stl-new-list-form" style="display:none;margin-top:10px;background:#1e293b;border-radius:12px;padding:14px;">
                <div style="color:#94a3b8;font-size:12px;margin-bottom:8px;font-weight:600;">New ${typeLabel} List</div>
                <input id="stl-new-list-name" type="text" placeholder="List name…" maxlength="100" style="
                    width:100%;padding:8px 12px;background:#0f172a;border:1px solid #374151;
                    border-radius:8px;color:#fff;font-size:13px;outline:none;
                    font-family:inherit;margin-bottom:8px;
                ">
                <div style="display:flex;gap:8px;justify-content:flex-end;">
                    <button id="stl-cancel-new" style="background:transparent;border:1px solid #374151;color:#9ca3af;padding:6px 14px;border-radius:8px;cursor:pointer;font-size:12px;">Cancel</button>
                    <button id="stl-confirm-new" style="background:#6366f1;border:none;color:#fff;padding:6px 14px;border-radius:8px;cursor:pointer;font-size:12px;font-weight:600;">Create & Select</button>
                </div>
            </div>`;

        listSection.innerHTML = html;

        // List row selection
        listSection.querySelectorAll('.stl-list-row').forEach(row => {
            row.addEventListener('click', () => {
                listSection.querySelectorAll('.stl-list-row').forEach(r => {
                    r.classList.remove('stl-selected');
                    r.style.borderColor = 'transparent';
                    r.style.background = '#1e293b';
                });
                row.classList.add('stl-selected');
                row.style.borderColor = '#6366f1';
                row.style.background = '#1e2944';
                this.selectedListId = parseInt(row.dataset.listId);
                this._enableSave();
            });
        });

        // New list form toggle
        listSection.querySelector('#stl-new-list-btn').addEventListener('click', () => {
            listSection.querySelector('#stl-new-list-form').style.display = 'block';
        });
        listSection.querySelector('#stl-cancel-new').addEventListener('click', () => {
            listSection.querySelector('#stl-new-list-form').style.display = 'none';
        });
        listSection.querySelector('#stl-confirm-new').addEventListener('click', () => {
            this._createListAndSelect(folderId, listSection);
        });
        listSection.querySelector('#stl-new-list-name').addEventListener('keydown', e => {
            if (e.key === 'Enter') this._createListAndSelect(folderId, listSection);
        });
    }

    async _createListAndSelect(folderId, listSection) {
        const nameInput = listSection.querySelector('#stl-new-list-name');
        const name = nameInput.value.trim();
        if (!name) { nameInput.style.borderColor = '#ef4444'; return; }

        const confirmBtn = listSection.querySelector('#stl-confirm-new');
        confirmBtn.textContent = 'Creating…';
        confirmBtn.disabled = true;

        try {
            const res = await fetch(`${this.baseUrl}/api/dashboard/lists`, {
                method: 'POST',
                headers: { ...this._headers(), 'Content-Type': 'application/json' },
                body: JSON.stringify({ folder_id: folderId, name, type: this.listType }),
            });
            if (res.status === 401) {
                this._handleUnauthorized();
                return;
            }
            const data = await res.json();
            if (data.success) {
                this.selectedListId = data.list.id;
                this._enableSave();
                // Reload list picker with new list selected
                await this._loadListsForFolder(folderId);
                // Auto-select the new list
                const newRow = listSection.querySelector(`[data-list-id="${this.selectedListId}"]`);
                if (newRow) { newRow.click(); }
            } else {
                confirmBtn.textContent = 'Create & Select';
                confirmBtn.disabled = false;
                this._showToast('Failed to create list.', '#ef4444');
            }
        } catch {
            confirmBtn.textContent = 'Create & Select';
            confirmBtn.disabled = false;
            this._showToast('Network error.', '#ef4444');
        }
    }

    // ── Save Action ─────────────────────────────────────────────────────────

    async _save() {
        if (!this.selectedListId || this.isSaving) return;
        this.isSaving = true;

        const saveBtn = this.panel.querySelector('#stl-save-btn');
        saveBtn.textContent = 'Saving…';
        saveBtn.disabled = true;

        try {
            const res = await fetch(`${this.baseUrl}/api/dashboard/lists/${this.selectedListId}/items`, {
                method: 'POST',
                headers: { ...this._headers(), 'Content-Type': 'application/json' },
                body: JSON.stringify({ 
                    items: this.items,
                    description: this.description
                }),
            });
            if (res.status === 401) {
                this._handleUnauthorized();
                return;
            }
            const data = await res.json();
            if (data.success) {
                this._showToast(`✓ ${data.added} item${data.added !== 1 ? 's' : ''} saved!`, '#10b981');
                this.onSuccess?.(data.added);
                setTimeout(() => this.close(), 1200);
            } else {
                saveBtn.textContent = 'Save Items';
                saveBtn.disabled = false;
                this._showToast('Failed to save items.', '#ef4444');
            }
        } catch {
            saveBtn.textContent = 'Save Items';
            saveBtn.disabled = false;
            this._showToast('Network error while saving.', '#ef4444');
        } finally {
            this.isSaving = false;
        }
    }

    // ── Helpers ─────────────────────────────────────────────────────────────

    _headers() {
        return { 'Authorization': `Bearer ${this.token}`, 'Accept': 'application/json' };
    }

    _enableSave() {
        const btn = this.panel.querySelector('#stl-save-btn');
        btn.disabled = false;
        btn.style.opacity = '1';
        btn.style.pointerEvents = 'auto';
    }

    _disableSave() {
        const btn = this.panel.querySelector('#stl-save-btn');
        btn.disabled = true;
        btn.style.opacity = '0.4';
        btn.style.pointerEvents = 'none';
    }

    _showError(msg) {
        const body = this.panel.querySelector('#stl-body');
        body.innerHTML = `<div style="text-align:center;padding:30px;color:#ef4444;">${msg}</div>`;
    }

    _handleUnauthorized() {
        // Check if the context is invalidated first
        if (!this._isExtensionContextValid()) {
            this._showError(`
                <div style="font-size:28px;margin-bottom:12px;">🔄</div>
                <div style="color:#fbbf24;font-weight:700;font-size:14px;margin-bottom:6px;">Extension reloaded</div>
                <div style="color:#94a3b8;font-size:12px;margin-bottom:14px;">Refresh the page to restore full functionality.</div>
                <button onclick="window.location.reload()" style="
                    background:#f59e0b;border:none;color:#000;
                    padding:8px 20px;border-radius:9px;cursor:pointer;
                    font-size:12px;font-weight:700;
                ">Refresh Page</button>
            `);
            return;
        }

        // Valid context but 401 — user is not logged in
        try {
            chrome.runtime.sendMessage({ action: 'logout' }, () => {
                // Ignore sendMessage errors
                if (chrome.runtime.lastError) { /* noop */ }
            });
        } catch (e) { /* ignore */ }

        this._showError(`
            <div style="font-size:28px;margin-bottom:12px;">🔑</div>
            <div style="color:#ef4444;font-weight:700;font-size:14px;margin-bottom:6px;">Authentication required</div>
            <div style="color:#94a3b8;font-size:12px;margin-bottom:14px;">Please log in to the dashboard, then try again.</div>
            <a href="${this.baseUrl}/login" target="_blank" style="
                display:inline-block;background:#6366f1;color:#fff;text-decoration:none;
                padding:8px 20px;border-radius:9px;font-size:12px;font-weight:700;
            ">Open Login Page</a>
        `);
    }

    _showToast(msg, color = '#6366f1') {
        const toast = document.createElement('div');
        toast.style.cssText = `
            position: fixed; bottom: 24px; right: 24px;
            background: ${color}; color: #fff;
            padding: 10px 18px; border-radius: 10px;
            font-size: 13px; font-weight: 600;
            font-family: 'Inter', system-ui, sans-serif;
            z-index: 999999999; box-shadow: 0 4px 15px rgba(0,0,0,0.3);
            animation: stl-fadein 0.2s ease;
        `;
        toast.textContent = msg;
        document.body.appendChild(toast);
        setTimeout(() => toast.remove(), 2500);
    }

    _esc(str) {
        return String(str).replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;').replace(/"/g,'&quot;');
    }
}

// Export for use across extension files
if (typeof module !== 'undefined') module.exports = SaveToList;
