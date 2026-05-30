// Cerebro UI - Multi-ASIN Keyword Analysis Interface
// Integrates with search page to allow ASIN selection and Cerebro analysis

class CerebroUI {
    constructor() {
        this.selectedAsins = new Set();
        this.maxAsins = 10;
        this.panel = null;
        this.selectionBar = null;
        this.isAnalyzing = false;
        this.testModeEnabled = false;
        this.testModeProductUrl = '';
        this.testAsin = null;
    }

    /**
     * Check if active page is set to Arabic
     */
    isArabic() {
        const url = window.location.href;
        const hasArUrl = url.includes('language=ar') || url.includes('/ar/');
        const isRtl = document.documentElement.getAttribute('dir') === 'rtl' || 
                      document.body?.getAttribute('dir') === 'rtl' || 
                      document.documentElement.style.direction === 'rtl' ||
                      document.body?.style.direction === 'rtl' ||
                      document.documentElement.classList.contains('a-rtl');
        const hasArLang = document.documentElement.lang && document.documentElement.lang.startsWith('ar');
        return !!(hasArUrl || isRtl || hasArLang);
    }

    parseBooleanSetting(value, defaultValue = false) {
        if (value === undefined || value === null) return defaultValue;
        if (typeof value === 'boolean') return value;
        if (typeof value === 'number') return value !== 0;

        const s = String(value).trim().toLowerCase();
        if (s === '1' || s === 'true' || s === 'yes' || s === 'on') return true;
        if (s === '0' || s === 'false' || s === 'no' || s === 'off' || s === '') return false;
        return defaultValue;
    }

    getBackendBaseUrl() {
        try {
            if (typeof window !== 'undefined') {
                if (window.ApiClient && window.ApiClient.baseUrl) return window.ApiClient.baseUrl;
                if (window.API_CONFIG && window.API_CONFIG.baseUrl) return window.API_CONFIG.baseUrl;
            }
        } catch (e) {
            // ignore
        }
        return 'http://127.0.0.1:8000'; // fallback
    }

    parseAsinFromUrl(url) {
        if (!url) return null;
        const match = url.match(/(?:dp|gp\/product)\/([A-Z0-9]{10})/i);
        return match ? match[1].toUpperCase() : null;
    }

    /**
     * Translate dynamic key to Arabic if in Arabic mode
     */
    t(key) {
        if (!this.isArabic()) return key;

        const translations = {
            'starting analysis...': 'بدء التحليل...',
            'initializing...': 'جاري التهيئة...',
            'fetching product page: ': 'جاري جلب صفحة المنتج: ',
            'scraping cerebro attributes...': 'جاري استخراج بيانات الأسين...',
            'loading ranks from backend...': 'جاري تحميل التصنيفات من الخادم...',
            'analysis complete': 'اكتمل التحليل بنجاح!'
        };

        const cleanedKey = key.toLowerCase().trim();
        for (const [eng, ara] of Object.entries(translations)) {
            if (cleanedKey.includes(eng)) {
                return key.toLowerCase().replace(eng, ara);
            }
        }
        return key;
    }

    /**
     * Translate a DOM container's text nodes and placeholders to Arabic dynamically
     */
    translateDOM(container) {
        if (!this.isArabic() || !container) return;

        // Apply RTL styling to container
        container.style.direction = 'rtl';
        if (container.id === 'cerebro-selection-bar' || container.id === 'cerebro-results-panel') {
            container.style.textAlign = 'right';
        }

        const translations = {
            'competitor keyword analyzer': 'محلل الكلمات المفتاحية للمنافسين',
            'select products to analyze': 'اختر منتجات لتحليلها',
            'clear all': 'مسح الكل',
            'analyze keywords': 'تحليل الكلمات المفتاحية',
            'asins analyzed': 'المنتجات (ASINs) المحللة',
            'keywords found': 'الكلمات المكتشفة',
            'avg iq score': 'متوسط درجة الذكاء',
            'avg volume': 'متوسط حجم البحث',
            'duration': 'المدة',
            'quick:': 'سريع:',
            'all': 'الكل',
            'top': 'الأعلى',
            'opportunity': 'فرصة',
            'low comp': 'منافسة منخفضة',
            'long-tail': 'طويلة الذيل',
            'export': 'تصدير',
            'vol:': 'الحجم:',
            'words:': 'الكلمات:',
            'ranking:': 'التصنيف:',
            'search keyword...': 'البحث عن كلمة مفتاحية...',
            'apply': 'تطبيق',
            'clear': 'مسح',
            'keyword': 'الكلمة المفتاحية',
            'volume ↕': 'حجم البحث ↕',
            'top 3 sales share ↕': 'نسبة مبيعات أعلى 3 ↕',
            'sales ↕': 'المبيعات ↕',
            'ad density ↕': 'كثافة الإعلانات ↕',
            'difficulty ↕': 'الصعوبة ↕',
            'sponsored ↕': 'الممولة ↕',
            'words': 'الكلمات',
            'ranking': 'التصنيف',
            'all-ranking': 'كل التصنيفات',
            'not-ranking': 'بدون تصنيف',
            'ranked': 'تصنيف واحد أو أكثر',
            'analysis complete - ': 'اكتمل التحليل - ',
            'keywords found': 'كلمات مفتاحية مكتشفة',
            'showing': 'عرض',
            'of': 'من',
            'product selected': 'منتج محدد',
            'products selected': 'منتجات محددة',
            'try again': 'محاولة أخرى',
            'analysis failed': 'فشل التحليل'
        };

        const walk = (node) => {
            if (node.nodeType === 3) {
                const text = node.nodeValue.trim().toLowerCase();
                for (const [eng, ara] of Object.entries(translations)) {
                    if (text === eng) {
                        node.nodeValue = ara;
                        break;
                    } else if (text.includes(eng)) {
                        node.nodeValue = node.nodeValue.replace(new RegExp(eng, 'gi'), ara);
                    }
                }
            } else if (node.nodeType === 1) {
                if (node.placeholder) {
                    const placeholderLower = node.placeholder.toLowerCase();
                    for (const [eng, ara] of Object.entries(translations)) {
                        if (placeholderLower.includes(eng)) {
                            node.placeholder = node.placeholder.replace(new RegExp(eng, 'gi'), ara);
                        }
                    }
                }
                if (node.title) {
                    const titleLower = node.title.toLowerCase();
                    for (const [eng, ara] of Object.entries(translations)) {
                        if (titleLower.includes(eng)) {
                            node.title = node.title.replace(new RegExp(eng, 'gi'), ara);
                        }
                    }
                }
                node.childNodes.forEach(walk);
            }
        };

        walk(container);
    }

    /**
     * Initialize Cerebro UI on search page
     * Injects checkboxes on product rows and adds floating selection bar
     */
    async initOnSearchPage() {
        console.log('[Cerebro] Initializing on search page');
        
        // 1. Create selection bar first so it's ready
        this.createSelectionBar();
        
        // 2. Observe changes so dynamic cards are handled
        this.observeProductChanges();
        
        // 3. Inject checkboxes immediately (without waiting for settings, to ensure fast UI)
        this.injectProductCheckboxes();
        
        // 4. Fetch settings asynchronously to check for test mode and auto-select
        try {
            const baseUrl = this.getBackendBaseUrl();
            const response = await fetch(`${baseUrl}/api/settings`);
            if (response.ok) {
                const configData = await response.json();
                const settings = configData.settings || {};
                
                this.testModeEnabled = this.parseBooleanSetting(settings.test_mode_enabled, false);
                this.testModeProductUrl = settings.test_mode_product_url || '';
                
                if (this.testModeEnabled && this.testModeProductUrl) {
                    this.testAsin = this.parseAsinFromUrl(this.testModeProductUrl);
                    console.log(`[Cerebro] Test Mode Active: Auto-selecting test ASIN ${this.testAsin}`);
                    
                    // Trigger auto-selection on any already injected checkbox
                    this.autoSelectTestProduct();
                }
            }
        } catch (e) {
            console.warn('[Cerebro] Failed to fetch settings for test mode:', e);
        }
    }

    /**
     * Auto-select the test product checkbox if it exists on the page
     */
    autoSelectTestProduct() {
        if (!this.testModeEnabled || !this.testAsin) return;
        
        const checkbox = document.querySelector(`.cerebro-asin-checkbox[data-asin="${this.testAsin}"]`);
        if (checkbox && !checkbox.checked) {
            checkbox.checked = true;
            this.selectedAsins.add(this.testAsin);
            
            const card = checkbox.closest('[data-asin]');
            if (card) {
                card.style.outline = '2px solid #6366f1';
                card.style.outlineOffset = '-2px';
            }
            
            const label = checkbox.parentElement?.querySelector('.cerebro-position-label');
            if (label) {
                label.style.display = 'inline-block';
            }
            
            this.updateSelectionNumbers();
            this.updateSelectionBar();
            console.log(`[Cerebro] Successfully auto-selected test product: ${this.testAsin}`);
        }
    }

    /**
     * Add checkboxes to all product cards on the search page
     */
    injectProductCheckboxes() {
        // Find all product cards
        const productCards = document.querySelectorAll('[data-asin]:not([data-asin=""])');

        productCards.forEach((card, index) => {
            if (card.querySelector('.cerebro-asin-checkbox')) return; // Already has checkbox inside

            const asin = card.getAttribute('data-asin');
            if (!asin || asin.length < 5) return;

            // Skip if this element is nested inside another element with the same ASIN
            // (prevents duplicate checkboxes)
            const parentWithAsin = card.parentElement?.closest(`[data-asin="${asin}"]`);
            if (parentWithAsin) return;

            // Create checkbox container
            const checkboxContainer = document.createElement('div');
            checkboxContainer.className = 'cerebro-checkbox-container';
            checkboxContainer.style.cssText = `
                position: absolute;
                top: 8px;
                left: 8px;
                z-index: 100;
                display: flex;
                align-items: center;
                gap: 6px;
            `;

            // Create checkbox
            const checkbox = document.createElement('input');
            checkbox.type = 'checkbox';
            checkbox.className = 'cerebro-asin-checkbox';
            checkbox.dataset.asin = asin;
            checkbox.style.cssText = `
                width: 20px;
                height: 20px;
                cursor: pointer;
                accent-color: #6366f1;
            `;

            // Position label
            const label = document.createElement('span');
            label.className = 'cerebro-position-label';
            label.style.cssText = `
                background: linear-gradient(135deg, #6366f1, #0ea5e9);
                color: white;
                font-size: 10px;
                font-weight: 700;
                padding: 2px 6px;
                border-radius: 4px;
                display: none;
            `;

            checkbox.addEventListener('change', (e) => {
                if (e.target.checked) {
                    if (this.selectedAsins.size >= this.maxAsins) {
                        e.target.checked = false;
                        this.showToast(`Maximum ${this.maxAsins} ASINs allowed`, 'warning');
                        return;
                    }
                    this.selectedAsins.add(asin);
                    label.style.display = 'inline-block';
                    this.updateSelectionNumbers();
                    card.style.outline = '2px solid #6366f1';
                    card.style.outlineOffset = '-2px';
                } else {
                    this.selectedAsins.delete(asin);
                    label.style.display = 'none';
                    this.updateSelectionNumbers();
                    card.style.outline = 'none';
                }
                this.updateSelectionBar();
            });

            checkboxContainer.appendChild(checkbox);
            checkboxContainer.appendChild(label);

            // Make card position relative for absolute checkbox positioning
            const cardStyle = window.getComputedStyle(card);
            if (cardStyle.position === 'static') {
                card.style.position = 'relative';
            }

            card.appendChild(checkboxContainer);

            // Auto check if this is the test ASIN in test mode
            if (this.testModeEnabled && this.testAsin && asin === this.testAsin) {
                checkbox.checked = true;
                this.selectedAsins.add(asin);
                label.style.display = 'inline-block';
                card.style.outline = '2px solid #6366f1';
                card.style.outlineOffset = '-2px';
                
                setTimeout(() => {
                    this.updateSelectionNumbers();
                    this.updateSelectionBar();
                }, 100);
            }
        });

        console.log(`[Cerebro] Injected checkboxes on ${productCards.length} products`);
    }

    /**
     * Update the position numbers on selected checkboxes
     */
    updateSelectionNumbers() {
        const checkboxes = document.querySelectorAll('.cerebro-asin-checkbox:checked');
        let position = 1;
        checkboxes.forEach(cb => {
            const label = cb.parentElement.querySelector('.cerebro-position-label');
            if (label) {
                label.textContent = `#${position}`;
                position++;
            }
        });
    }

    /**
     * Create floating selection bar at bottom of screen
     */
    createSelectionBar() {
        if (this.selectionBar) return;

        this.selectionBar = document.createElement('div');
        this.selectionBar.id = 'cerebro-selection-bar';
        this.selectionBar.style.cssText = `
            position: fixed;
            bottom: -120px;
            left: 50%;
            transform: translateX(-50%);
            background: linear-gradient(135deg, #1e293b 0%, #0f172a 100%);
            border: 1px solid #6366f1;
            border-radius: 16px;
            padding: 14px 28px;
            display: flex;
            align-items: center;
            gap: 24px;
            z-index: 999999;
            box-shadow: 0 10px 40px rgba(99, 102, 241, 0.3), 0 0 0 1px rgba(255,255,255,0.1);
            font-family: 'Inter', -apple-system, system-ui, sans-serif;
            transition: bottom 0.3s ease-out;
            min-width: 700px;
            max-width: 90vw;
        `;

        this.selectionBar.innerHTML = `
            <div style="display: flex; align-items: center; gap: 12px;">
                <span style="font-size: 28px;">🧠</span>
                <div>
                    <div style="font-weight: 700; color: #fff; font-size: 15px;">Competitor Keyword Analyzer</div>
                    <div id="cerebro-selection-count" style="color: #94a3b8; font-size: 12px;">Select products to analyze</div>
                </div>
            </div>
            <div style="height: 36px; width: 1px; background: #374151;"></div>
            <div id="cerebro-selected-badges" style="display: flex; gap: 8px; flex-wrap: wrap; max-width: 500px; flex: 1;"></div>
            <button id="cerebro-clear-btn" style="
                background: transparent;
                border: 1px solid #374151;
                color: #9ca3af;
                padding: 8px 12px;
                border-radius: 8px;
                cursor: pointer;
                font-size: 12px;
                font-weight: 500;
                transition: all 0.2s;
            ">Clear All</button>
            <button id="cerebro-analyze-btn" style="
                background: linear-gradient(135deg, #6366f1, #4f46e5);
                border: none;
                color: white;
                padding: 10px 20px;
                border-radius: 10px;
                cursor: pointer;
                font-size: 13px;
                font-weight: 600;
                display: flex;
                align-items: center;
                gap: 8px;
                transition: all 0.2s;
                box-shadow: 0 4px 15px rgba(99, 102, 241, 0.4);
            ">
                <span>🔍</span>
                <span>Analyze Keywords</span>
            </button>
        `;

        document.body.appendChild(this.selectionBar);
        this.translateDOM(this.selectionBar);

        // Event listeners
        this.selectionBar.querySelector('#cerebro-clear-btn').addEventListener('click', () => this.clearSelection());
        this.selectionBar.querySelector('#cerebro-analyze-btn').addEventListener('click', () => this.startAnalysis());

        // Hover effects
        const analyzeBtn = this.selectionBar.querySelector('#cerebro-analyze-btn');
        analyzeBtn.addEventListener('mouseenter', () => {
            analyzeBtn.style.transform = 'translateY(-2px)';
            analyzeBtn.style.boxShadow = '0 6px 20px rgba(99, 102, 241, 0.5)';
        });
        analyzeBtn.addEventListener('mouseleave', () => {
            analyzeBtn.style.transform = 'translateY(0)';
            analyzeBtn.style.boxShadow = '0 4px 15px rgba(99, 102, 241, 0.4)';
        });

        // Initialize state
        this.updateSelectionBar();
    }

    /**
     * Update selection bar visibility and content
     */
    updateSelectionBar() {
        if (!this.selectionBar) return;

        const count = this.selectedAsins.size;
        const countEl = this.selectionBar.querySelector('#cerebro-selection-count');
        const badgesEl = this.selectionBar.querySelector('#cerebro-selected-badges');
        const analyzeBtn = this.selectionBar.querySelector('#cerebro-analyze-btn');

        // Update count text and bottom position
        if (count === 0) {
            countEl.textContent = this.isArabic() ? 'اختر منتجات لتحليلها' : 'Select products to analyze';
            this.selectionBar.style.bottom = '-120px';
        } else {
            countEl.textContent = `${count} product${count > 1 ? 's' : ''} selected`;
            if (this.isArabic()) {
                countEl.textContent = count === 1 ? `منتج واحد محدد` : `${count} منتجات محددة`;
            }
            this.selectionBar.style.bottom = '20px';
        }
        this.translateDOM(this.selectionBar);

        // Update badges
        badgesEl.innerHTML = Array.from(this.selectedAsins).slice(0, 5).map(asin => `
            <span style="
                background: #374151;
                color: #e5e7eb;
                padding: 4px 8px;
                border-radius: 4px;
                font-size: 10px;
                font-family: monospace;
            ">${asin.substring(0, 6)}...</span>
        `).join('');

        if (count > 5) {
            badgesEl.innerHTML += `<span style="color: #9ca3af; font-size: 11px;">+${count - 5} more</span>`;
        }

        // Update button state
        if (count === 0) {
            analyzeBtn.disabled = true;
            analyzeBtn.style.opacity = '0.5';
            analyzeBtn.style.cursor = 'not-allowed';
        } else {
            analyzeBtn.disabled = false;
            analyzeBtn.style.opacity = '1';
            analyzeBtn.style.cursor = 'pointer';
        }
    }

    /**
     * Clear all selected ASINs
     */
    clearSelection() {
        this.selectedAsins.clear();

        // Uncheck all checkboxes
        document.querySelectorAll('.cerebro-asin-checkbox').forEach(cb => {
            cb.checked = false;
            const card = cb.closest('[data-asin]');
            if (card) card.style.outline = 'none';
            const label = cb.parentElement.querySelector('.cerebro-position-label');
            if (label) label.style.display = 'none';
        });

        this.updateSelectionBar();
    }

    /**
     * Observe DOM for dynamically loaded products
     */
    observeProductChanges() {
        const observer = new MutationObserver((mutations) => {
            let hasNewProducts = false;
            for (const mutation of mutations) {
                if (mutation.addedNodes.length > 0) {
                    for (const node of mutation.addedNodes) {
                        if (node.nodeType === 1 && (node.hasAttribute?.('data-asin') || node.querySelector?.('[data-asin]'))) {
                            hasNewProducts = true;
                            break;
                        }
                    }
                }
            }
            if (hasNewProducts) {
                setTimeout(() => this.injectProductCheckboxes(), 100);
            }
        });

        observer.observe(document.body, { childList: true, subtree: true });
    }

    /**
     * Start the Cerebro analysis
     */
    async startAnalysis() {
        if (this.selectedAsins.size === 0) {
            this.showToast('Please select at least one product', 'warning');
            return;
        }

        if (this.isAnalyzing) {
            this.showToast('Analysis already in progress', 'warning');
            return;
        }

        this.isAnalyzing = true;
        const asins = Array.from(this.selectedAsins);

        // Create and show results panel
        this.createResultsPanel();
        this.updateProgress(0, 'Starting analysis...');

        try {
            // Initialize CerebroAnalyzer
            if (typeof CerebroAnalyzer === 'undefined') {
                throw new Error('CerebroAnalyzer not loaded');
            }

            const marketplace = window.location.hostname;
            const analyzer = new CerebroAnalyzer(marketplace);

            const results = await analyzer.analyze(asins, (percent, message) => {
                this.updateProgress(percent, message);
            });

            // Display results
            this.displayResults(results);

            // Save to backend
            this.saveToBackend(results);

        } catch (error) {
            console.error('[Cerebro] Analysis error:', error);
            this.showError(error.message);
        } finally {
            this.isAnalyzing = false;
        }
    }

    /**
     * Create the results panel
     */
    createResultsPanel() {
        // Remove existing panel
        document.getElementById('cerebro-results-panel')?.remove();

        this.panel = document.createElement('div');
        this.panel.id = 'cerebro-results-panel';
        this.panel.style.cssText = `
            position: fixed;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            width: 90vw;
            max-width: 1400px;
            max-height: 85vh;
            background: #0f172a;
            border: 1px solid #374151;
            border-radius: 16px;
            z-index: 9999999;
            box-shadow: 0 25px 50px rgba(0,0,0,0.5);
            font-family: 'Inter', -apple-system, system-ui, sans-serif;
            display: flex;
            flex-direction: column;
            overflow: hidden;
        `;

        this.panel.innerHTML = `
            <div style="
                padding: 16px 24px;
                background: linear-gradient(135deg, #1e293b, #0f172a);
                border-bottom: 1px solid #374151;
                display: flex;
                justify-content: space-between;
                align-items: center;
            ">
                <div style="display: flex; align-items: center; gap: 12px;">
                    <span style="font-size: 28px;">🧠</span>
                    <div>
                        <div style="font-weight: 700; color: #fff; font-size: 18px;">Competitor Keyword Analyzer</div>
                        <div id="cerebro-status" style="color: #94a3b8; font-size: 12px;">Initializing...</div>
                    </div>
                </div>
                <button id="cerebro-close-btn" style="
                    background: #374151;
                    border: none;
                    color: #9ca3af;
                    width: 32px;
                    height: 32px;
                    border-radius: 8px;
                    cursor: pointer;
                    font-size: 18px;
                    display: flex;
                    align-items: center;
                    justify-content: center;
                    transition: all 0.2s;
                ">×</button>
            </div>
            <div id="cerebro-content" style="flex: 1; overflow: hidden; display: flex; flex-direction: column;">
                <div id="cerebro-progress" style="padding: 40px; text-align: center;">
                    <div style="
                        width: 80px;
                        height: 80px;
                        border: 4px solid #374151;
                        border-top-color: #6366f1;
                        border-radius: 50%;
                        margin: 0 auto 20px;
                        animation: cerebro-spin 1s linear infinite;
                    "></div>
                    <div id="cerebro-progress-text" style="color: #fff; font-size: 16px; font-weight: 600;">Starting analysis...</div>
                    <div id="cerebro-progress-bar" style="
                        width: 300px;
                        height: 6px;
                        background: #374151;
                        border-radius: 3px;
                        margin: 20px auto 0;
                        overflow: hidden;
                    ">
                        <div id="cerebro-progress-fill" style="
                            width: 0%;
                            height: 100%;
                            background: linear-gradient(90deg, #6366f1, #0ea5e9);
                            border-radius: 3px;
                            transition: width 0.3s;
                        "></div>
                    </div>
                </div>
            </div>
        `;

        // Add animation style
        const style = document.createElement('style');
        style.textContent = `
            @keyframes cerebro-spin {
                to { transform: rotate(360deg); }
            }
        `;
        this.panel.appendChild(style);

        document.body.appendChild(this.panel);
        this.translateDOM(this.panel);

        // Add backdrop
        const backdrop = document.createElement('div');
        backdrop.id = 'cerebro-backdrop';
        backdrop.style.cssText = `
            position: fixed;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: rgba(0,0,0,0.7);
            z-index: 9999998;
        `;
        document.body.appendChild(backdrop);

        // Close handlers
        this.panel.querySelector('#cerebro-close-btn').addEventListener('click', () => this.closePanel());
        backdrop.addEventListener('click', () => this.closePanel());
    }

    /**
     * Update progress display
     */
    updateProgress(percent, message) {
        const progressText = this.panel?.querySelector('#cerebro-progress-text');
        const progressFill = this.panel?.querySelector('#cerebro-progress-fill');
        const status = this.panel?.querySelector('#cerebro-status');

        const translated = this.t(message);

        if (progressText) progressText.textContent = translated;
        if (progressFill) progressFill.style.width = `${percent}%`;
        if (status) status.textContent = `${percent}% - ${translated}`;
    }

    /**
     * Display analysis results
     */
    displayResults(results) {
        const content = this.panel?.querySelector('#cerebro-content');
        if (!content) return;

        const keywords = results.keywords || [];
        const asins = results.asins || [];

        const asinSummaries = results.asin_summaries || [];
        const origin = window.location.origin;

        content.innerHTML = `
            <!-- Analyzed Products Strip -->
            <div style="
                padding: 14px 24px;
                background: linear-gradient(135deg, #0f172a 0%, #1e293b 100%);
                border-bottom: 1px solid #374151;
                display: flex;
                gap: 12px;
                align-items: flex-start;
                flex-wrap: wrap;
            ">
                <div style="color: #9ca3af; font-size: 10px; font-weight: 700; text-transform: uppercase; letter-spacing: 1px; padding-top: 6px; min-width: 80px;">
                    Analyzing:
                </div>
                <div style="display: flex; gap: 10px; flex-wrap: wrap;">
                    ${asinSummaries.map((s, i) => {
                        const colors = ['#6366f1', '#10b981', '#f59e0b', '#ef4444', '#0ea5e9', '#a855f7', '#fb923c', '#14b8a6', '#f43f5e', '#84cc16'];
                        const color = colors[i % colors.length];
                        const shortTitle = s.title ? (s.title.length > 55 ? s.title.substring(0, 55) + '…' : s.title) : s.asin;
                        const productUrl = `${origin}/dp/${s.asin}`;
                        const imgSrc = s.image || '';
                        return `
                        <a href="${productUrl}" target="_blank" style="
                            display: flex;
                            align-items: center;
                            gap: 10px;
                            background: #1e293b;
                            border: 1px solid ${color}40;
                            border-radius: 10px;
                            padding: 8px 12px;
                            text-decoration: none;
                            max-width: 260px;
                            transition: background 0.2s, border-color 0.2s;
                            cursor: pointer;
                        " onmouseover="this.style.background='#263045'; this.style.borderColor='${color}'" onmouseout="this.style.background='#1e293b'; this.style.borderColor='${color}40'">
                            <!-- Badge number -->
                            <div style="
                                min-width: 22px; height: 22px;
                                background: ${color};
                                border-radius: 50%;
                                display: flex; align-items: center; justify-content: center;
                                font-size: 11px; font-weight: 800; color: #fff;
                                flex-shrink: 0;
                            ">#${i + 1}</div>
                            <!-- Thumbnail -->
                            ${imgSrc ? `<img src="${imgSrc}" style="width: 44px; height: 44px; object-fit: contain; border-radius: 6px; background: #fff; flex-shrink: 0;" onerror="this.style.display='none'">` :
                                `<div style="width: 44px; height: 44px; background: #374151; border-radius: 6px; display: flex; align-items: center; justify-content: center; flex-shrink: 0;">
                                    <span style="font-size: 16px;">📦</span>
                                </div>`}
                            <!-- Info -->
                            <div style="overflow: hidden;">
                                <div style="color: #e5e7eb; font-size: 11px; font-weight: 600; line-height: 1.35; overflow: hidden; display: -webkit-box; -webkit-line-clamp: 2; -webkit-box-orient: vertical;">${shortTitle}</div>
                                <div style="margin-top: 3px; display: flex; align-items: center; gap: 6px;">
                                    <span style="color: #9ca3af; font-size: 10px; font-family: monospace;">${s.asin}</span>
                                    <span style="background: ${color}20; color: ${color}; font-size: 9px; font-weight: 700; padding: 1px 5px; border-radius: 4px;">${s.keywords_found} kw</span>
                                </div>
                            </div>
                        </a>`;
                    }).join('')}
                </div>
            </div>

            <!-- Summary Stats -->
            <div style="
                padding: 16px 24px;
                background: #1e293b;
                border-bottom: 1px solid #374151;
                display: grid;
                grid-template-columns: repeat(4, 1fr);
                gap: 16px;
            ">
                <div style="text-align: center;">
                    <div style="color: #9ca3af; font-size: 10px; text-transform: uppercase; font-weight: 600;">ASINs Analyzed</div>
                    <div style="color: #fff; font-size: 24px; font-weight: 700;">${asins.length}</div>
                </div>
                <div style="text-align: center;">
                    <div style="color: #9ca3af; font-size: 10px; text-transform: uppercase; font-weight: 600;">Keywords Found</div>
                    <div style="color: #10b981; font-size: 24px; font-weight: 700;">${keywords.length}</div>
                </div>

                <div style="text-align: center;">
                    <div style="color: #9ca3af; font-size: 10px; text-transform: uppercase; font-weight: 600;">Avg Volume</div>
                    <div style="color: #0ea5e9; font-size: 24px; font-weight: 700;">${this.formatNumber(this.calcAvg(keywords, 'search_volume'))}</div>
                </div>
                <div style="text-align: center;">
                    <div style="color: #9ca3af; font-size: 10px; text-transform: uppercase; font-weight: 600;">Duration</div>
                    <div style="color: #f59e0b; font-size: 24px; font-weight: 700;">${results.duration_seconds}s</div>
                </div>
            </div>

            <!-- Filters Bar -->
            <div style="
                padding: 12px 24px;
                background: #1e293b;
                border-bottom: 1px solid #374151;
                display: flex;
                flex-direction: column;
                gap: 10px;
            ">
                <!-- Row 1: Quick Filters -->
                <div style="display: flex; align-items: center; gap: 8px; flex-wrap: wrap;">
                    <span style="color: #9ca3af; font-size: 11px; font-weight: 600;">Quick:</span>
                    <button class="cerebro-quick-filter active" data-filter="all" style="
                        background: #6366f1; border: none; color: white;
                        padding: 5px 10px; border-radius: 5px; cursor: pointer; font-size: 11px;
                    ">All (${keywords.length})</button>
                    <button class="cerebro-quick-filter" data-filter="top_keywords" style="
                        background: #374151; border: none; color: #e5e7eb;
                        padding: 5px 10px; border-radius: 5px; cursor: pointer; font-size: 11px;
                    ">🔥 Top</button>
                    <button class="cerebro-quick-filter" data-filter="opportunity" style="
                        background: #374151; border: none; color: #e5e7eb;
                        padding: 5px 10px; border-radius: 5px; cursor: pointer; font-size: 11px;
                    ">💎 Opportunity</button>
                    <button class="cerebro-quick-filter" data-filter="low_competition" style="
                        background: #374151; border: none; color: #e5e7eb;
                        padding: 5px 10px; border-radius: 5px; cursor: pointer; font-size: 11px;
                    ">✅ Low Comp</button>
                    <button class="cerebro-quick-filter" data-filter="long_tail" style="
                        background: #374151; border: none; color: #e5e7eb;
                        padding: 5px 10px; border-radius: 5px; cursor: pointer; font-size: 11px;
                    ">📝 Long-tail</button>
                    <div style="flex: 1;"></div>
                    <button id="cerebro-export-btn" style="
                        background: #10b981; border: none; color: white;
                        padding: 6px 14px; border-radius: 6px; cursor: pointer; font-size: 11px; font-weight: 600;
                    ">📥 Export</button>
                </div>
                
                <!-- Row 2: Advanced Filters -->
                <div style="display: flex; align-items: center; gap: 10px; flex-wrap: wrap; font-size: 11px;">
                    <!-- Volume -->
                    <div style="display: flex; align-items: center; gap: 3px;">
                        <span style="color: #9ca3af;">Vol:</span>
                        <input type="number" id="cerebro-filter-vol-min" placeholder="Min" style="
                            width: 55px; padding: 4px 5px; border-radius: 4px; border: 1px solid #374151;
                            background: #0f172a; color: #e5e7eb; font-size: 11px;">
                        <span style="color: #6b7280;">-</span>
                        <input type="number" id="cerebro-filter-vol-max" placeholder="Max" style="
                            width: 55px; padding: 4px 5px; border-radius: 4px; border: 1px solid #374151;
                            background: #0f172a; color: #e5e7eb; font-size: 11px;">
                    </div>
                    
                    <!-- Words -->
                    <div style="display: flex; align-items: center; gap: 3px;">
                        <span style="color: #9ca3af;">Words:</span>
                        <input type="number" id="cerebro-filter-words-min" placeholder="1" style="
                            width: 40px; padding: 4px 5px; border-radius: 4px; border: 1px solid #374151;
                            background: #0f172a; color: #e5e7eb; font-size: 11px;">
                        <span style="color: #6b7280;">-</span>
                        <input type="number" id="cerebro-filter-words-max" placeholder="10" style="
                            width: 40px; padding: 4px 5px; border-radius: 4px; border: 1px solid #374151;
                            background: #0f172a; color: #e5e7eb; font-size: 11px;">
                    </div>
                    

                    
                    <!-- Ranking -->
                    <div style="display: flex; align-items: center; gap: 3px;">
                        <span style="color: #9ca3af;">Ranking:</span>
                        <select id="cerebro-filter-ranking" style="
                            padding: 4px 6px; border-radius: 4px; border: 1px solid #374151;
                            background: #0f172a; color: #e5e7eb; font-size: 11px;">
                            <option value="all">All</option>
                            <option value="ranked">≥1 Ranking</option>
                            <option value="all-ranking">All Rank</option>
                            <option value="not-ranking">No Rank</option>
                        </select>
                    </div>
                    
                    <!-- Search -->
                    <input type="text" id="cerebro-filter-search" placeholder="Search keyword..." style="
                        flex: 1; min-width: 100px; padding: 5px 8px; border-radius: 4px; border: 1px solid #374151;
                        background: #0f172a; color: #e5e7eb; font-size: 11px;">
                    
                    <!-- Apply/Clear -->
                    <button id="cerebro-apply-filters" style="
                        background: #6366f1; border: none; color: white;
                        padding: 5px 12px; border-radius: 5px; cursor: pointer; font-size: 11px; font-weight: 600;
                    ">Apply</button>
                    <button id="cerebro-clear-filters" style="
                        background: #374151; border: none; color: #e5e7eb;
                        padding: 5px 10px; border-radius: 5px; cursor: pointer; font-size: 11px;
                    ">Clear</button>
                </div>
            </div>

            <!-- Results Table -->
            <div style="flex: 1; overflow: auto; padding: 0 24px 24px;">
                <table id="cerebro-results-table" style="width: 100%; border-collapse: collapse; font-size: 12px; margin-top: 16px;">
                    <thead style="position: sticky; top: 0; z-index: 10;">
                        <tr style="background: #1e293b; color: #9ca3af; text-transform: uppercase; font-size: 10px;">
                            <th style="padding: 12px 8px; text-align: left; border-bottom: 2px solid #374151;">Keyword</th>
                            <th style="padding: 12px 8px; text-align: right; border-bottom: 2px solid #374151; cursor: pointer;" data-sort="search_volume" title="Estimated monthly searches for this keyword">Searches ↕</th>
                            <th style="padding: 12px 8px; text-align: right; border-bottom: 2px solid #374151; cursor: pointer;" data-sort="total_click_share">Top 3 Sales Share ↕</th>
                            <th style="padding: 12px 8px; text-align: right; border-bottom: 2px solid #374151; cursor: pointer;" data-sort="total_keyword_sales" title="Total monthly sales across all products for this keyword">Sales ↕</th>
                            <th style="padding: 12px 8px; text-align: right; border-bottom: 2px solid #374151; cursor: pointer;" data-sort="sponsored_count">AD Density ↕</th>
                            <th style="padding: 12px 8px; text-align: right; border-bottom: 2px solid #374151; cursor: pointer;" data-sort="difficulty_score">Difficulty ↕</th>
                            <th style="padding: 12px 8px; text-align: right; border-bottom: 2px solid #374151; cursor: pointer;" data-sort="sponsored_count">Sponsored ↕</th>
                            <th style="padding: 12px 8px; text-align: right; border-bottom: 2px solid #374151;">Words</th>
                            <th style="padding: 12px 8px; text-align: center; border-bottom: 2px solid #374151;">Ranking</th>
                            ${asins.map((asin, i) => `
                                <th style="padding: 12px 8px; text-align: center; border-bottom: 2px solid #374151; font-size: 9px;" title="${asin}">#${i + 1}</th>
                            `).join('')}
                        </tr>
                    </thead>
                    <tbody id="cerebro-results-tbody">
                        ${this.renderKeywordRows(keywords, asins)}
                    </tbody>
                </table>
            </div>
        `;

        // Store data for filtering
        this.resultsData = { keywords, asins };

        // Quick filter event listeners
        content.querySelectorAll('.cerebro-quick-filter').forEach(btn => {
            btn.addEventListener('click', (e) => {
                this.clearAdvancedFilters();
                this.applyQuickFilter(e.target.dataset.filter);
            });
        });

        content.querySelector('#cerebro-export-btn').addEventListener('click', () => this.exportCSV());

        // Advanced filter event listeners
        content.querySelector('#cerebro-apply-filters')?.addEventListener('click', () => this.applyAdvancedFilters());
        content.querySelector('#cerebro-clear-filters')?.addEventListener('click', () => {
            this.clearAdvancedFilters();
            this.applyAdvancedFilters();
        });
        content.querySelector('#cerebro-filter-search')?.addEventListener('keypress', (e) => {
            if (e.key === 'Enter') this.applyAdvancedFilters();
        });

        // Sorting
        content.querySelectorAll('th[data-sort]').forEach(th => {
            th.addEventListener('click', () => this.sortTable(th.dataset.sort));
        });

        // Update status
        const status = this.panel?.querySelector('#cerebro-status');
        if (status) status.textContent = `Analysis complete - ${keywords.length} keywords found`;

        this.translateDOM(this.panel);
    }

    /**
     * Clear advanced filter inputs
     */
    clearAdvancedFilters() {
        const ids = ['cerebro-filter-vol-min', 'cerebro-filter-vol-max',
            'cerebro-filter-words-min', 'cerebro-filter-words-max',
            'cerebro-filter-search'];
        ids.forEach(id => {
            const el = this.panel?.querySelector(`#${id}`);
            if (el) el.value = '';
        });
        const ranking = this.panel?.querySelector('#cerebro-filter-ranking');
        if (ranking) ranking.value = 'all';
    }

    /**
     * Apply advanced filters
     */
    applyAdvancedFilters() {
        if (!this.resultsData) return;

        const volMin = parseFloat(this.panel.querySelector('#cerebro-filter-vol-min')?.value) || 0;
        const volMax = parseFloat(this.panel.querySelector('#cerebro-filter-vol-max')?.value) || Infinity;
        const wordsMin = parseInt(this.panel.querySelector('#cerebro-filter-words-min')?.value) || 0;
        const wordsMax = parseInt(this.panel.querySelector('#cerebro-filter-words-max')?.value) || Infinity;
        const ranking = this.panel.querySelector('#cerebro-filter-ranking')?.value || 'all';
        const search = (this.panel.querySelector('#cerebro-filter-search')?.value || '').toLowerCase().trim();

        const asins = this.resultsData.asins;
        const totalAsins = asins.length;

        let filtered = this.resultsData.keywords.filter(kw => {
            // Volume filter
            if (kw.search_volume < volMin || kw.search_volume > volMax) return false;
            // Word count filter
            if (kw.word_count < wordsMin || kw.word_count > wordsMax) return false;
            // Ranking filter
            if (ranking === 'ranked' && kw.asins_ranking === 0) return false;
            if (ranking === 'all-ranking' && kw.asins_ranking < totalAsins) return false;
            if (ranking === 'not-ranking' && kw.asins_ranking > 0) return false;
            // Search filter
            if (search && !kw.keyword.toLowerCase().includes(search)) return false;
            return true;
        });

        // Clear quick filter active states
        this.panel.querySelectorAll('.cerebro-quick-filter').forEach(btn => {
            btn.style.background = '#374151';
        });

        // Re-render table
        const tbody = this.panel.querySelector('#cerebro-results-tbody');
        if (tbody) {
            tbody.innerHTML = this.renderKeywordRows(filtered, asins);
            this.translateDOM(tbody);
        }

        // Update status
        const status = this.panel?.querySelector('#cerebro-status');
        if (status) {
            status.textContent = `Showing ${filtered.length} of ${this.resultsData.keywords.length} keywords`;
            this.translateDOM(status);
        }
    }

    /**
     * Render keyword rows
     */
    renderKeywordRows(keywords, asins) {
        return keywords.map((kw, idx) => {
            const iqColor = kw.cerebro_iq_score >= 5 ? '#10b981' :
                kw.cerebro_iq_score >= 3 ? '#f59e0b' :
                    kw.cerebro_iq_score >= 1 ? '#fb923c' : '#ef4444';

            const clickShareText = kw.total_click_share ? `${kw.total_click_share.toFixed(0)}%` : '0%';
            const salesText = this.formatNumber(kw.total_keyword_sales || 0);
            const totalPageProducts = kw.total_page_products > 0 ? kw.total_page_products : 48;
            const adDensityPct = Math.round(((kw.sponsored_count || 0) / totalPageProducts) * 100);
            const densityText = adDensityPct + '%';
            const difficultyHtml = this.formatDifficulty(kw.difficulty_score);
            const sponsoredText = kw.sponsored_count || 0;

            return `
                <tr style="background: ${idx % 2 === 0 ? '#0f172a' : '#1e293b'}; border-bottom: 1px solid #374151;">
                    <td style="padding: 10px 8px; color: #fff; font-weight: 500;">${kw.keyword}</td>
                    <td style="padding: 10px 8px; text-align: right; color: #60a5fa; font-weight: 600;" title="Estimated monthly searches (same formula as Magnet & Market Analysis)">${this.formatNumber(kw.search_volume)}</td>
                    <td style="padding: 10px 8px; text-align: right; color: #10b981; font-weight: 600;">${clickShareText}</td>
                    <td style="padding: 10px 8px; text-align: right; color: #0ea5e9; font-weight: 600;">${salesText}</td>
                    <td style="padding: 10px 8px; text-align: right; color: #a855f7;">${densityText}</td>
                    <td style="padding: 10px 8px; text-align: right;">${difficultyHtml}</td>
                    <td style="padding: 10px 8px; text-align: right; color: #eab308;">${sponsoredText}</td>
                    <td style="padding: 10px 8px; text-align: right; color: #9ca3af;">${kw.word_count}</td>
                    <td style="padding: 10px 8px; text-align: center;">
                        <span style="
                            background: ${kw.asins_ranking > 0 ? '#10b98120' : '#37415180'};
                            color: ${kw.asins_ranking > 0 ? '#10b981' : '#6b7280'};
                            padding: 2px 8px;
                            border-radius: 4px;
                            font-size: 11px;
                            font-weight: 600;
                        ">${kw.asins_ranking}/${asins.length}</span>
                    </td>
                    ${asins.map(asin => {
                const rank = kw.organic_ranks?.[asin];
                return `<td style="padding: 10px 8px; text-align: center; color: ${rank ? '#10b981' : '#374151'}; font-weight: ${rank ? '600' : '400'};">${rank ? `#${rank}` : '-'}</td>`;
            }).join('')}
                </tr>
            `;
        }).join('');
    }

    /**
     * Apply quick filter
     */
    applyQuickFilter(filterName) {
        if (!this.resultsData) return;

        let filtered = [...this.resultsData.keywords];

        if (filterName !== 'all') {
            const analyzer = new CerebroAnalyzer();
            filtered = analyzer.applyQuickFilter(filtered, filterName);
        }

        // Update button states
        this.panel.querySelectorAll('.cerebro-quick-filter').forEach(btn => {
            btn.style.background = btn.dataset.filter === filterName ? '#6366f1' : '#374151';
        });

        // Re-render table
        const tbody = this.panel.querySelector('#cerebro-results-tbody');
        if (tbody) {
            tbody.innerHTML = this.renderKeywordRows(filtered, this.resultsData.asins);
            this.translateDOM(tbody);
        }

        // Update count
        const quickFilterAllBtn = this.panel.querySelector('.cerebro-quick-filter[data-filter="all"]');
        if (quickFilterAllBtn) {
            quickFilterAllBtn.textContent = `All (${this.resultsData.keywords.length})`;
            this.translateDOM(quickFilterAllBtn);
        }
    }

    /**
     * Sort table by column
     */
    sortTable(column) {
        if (!this.resultsData) return;

        this.sortDirection = this.sortColumn === column ? -this.sortDirection : -1;
        this.sortColumn = column;

        this.resultsData.keywords.sort((a, b) => {
            const aVal = a[column] || 0;
            const bVal = b[column] || 0;
            return (bVal - aVal) * this.sortDirection;
        });

        const tbody = this.panel.querySelector('#cerebro-results-tbody');
        if (tbody) {
            tbody.innerHTML = this.renderKeywordRows(this.resultsData.keywords, this.resultsData.asins);
            this.translateDOM(tbody);
        }
    }

    /**
     * Export to CSV
     */
    exportCSV() {
        if (!this.resultsData) return;

        const analyzer = new CerebroAnalyzer();
        analyzer.asins = this.resultsData.asins;
        const csv = analyzer.exportToCSV(this.resultsData.keywords);

        const blob = new Blob([csv], { type: 'text/csv' });
        const url = URL.createObjectURL(blob);
        const a = document.createElement('a');
        a.href = url;
        a.download = `cerebro_analysis_${Date.now()}.csv`;
        a.click();
        URL.revokeObjectURL(url);

        this.showToast('CSV exported successfully!', 'success');
    }

    /**
     * Save results to backend
     */
    async saveToBackend(results) {
        try {
            // Use the global ApiClient if available
            if (typeof ApiClient !== 'undefined' && ApiClient.baseUrl) {
                const response = await ApiClient.request('POST', '/api/cerebro/analyze', results);
                console.log('[Cerebro] Results saved to backend:', response);
                this.showToast('Analysis saved to dashboard!', 'success');
            } else {
                // Fallback to direct fetch with localhost
                const baseUrl = 'http://localhost:8000';
                const response = await fetch(`${baseUrl}/api/cerebro/analyze`, {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify(results)
                });
                if (response.ok) {
                    console.log('[Cerebro] Results saved to backend');
                    this.showToast('Analysis saved to dashboard!', 'success');
                }
            }
        } catch (e) {
            console.log('[Cerebro] Could not save to backend:', e);
            // Don't show error toast - backend save is optional
        }
    }

    /**
     * Close the results panel
     */
    closePanel() {
        this.panel?.remove();
        document.getElementById('cerebro-backdrop')?.remove();
        this.panel = null;
    }

    /**
     * Show error state
     */
    showError(message) {
        const content = this.panel?.querySelector('#cerebro-content');
        if (!content) return;

        content.innerHTML = `
            <div style="padding: 60px; text-align: center;">
                <div style="font-size: 48px; margin-bottom: 16px;">❌</div>
                <div style="color: #ef4444; font-size: 18px; font-weight: 600; margin-bottom: 8px;">Analysis Failed</div>
                <div style="color: #9ca3af; font-size: 14px;">${message}</div>
                <button id="cerebro-retry-btn" style="
                    margin-top: 24px;
                    background: #6366f1;
                    border: none;
                    color: white;
                    padding: 12px 24px;
                    border-radius: 8px;
                    cursor: pointer;
                    font-size: 14px;
                    font-weight: 600;
                ">Try Again</button>
            </div>
        `;
        this.translateDOM(content);

        content.querySelector('#cerebro-retry-btn').addEventListener('click', () => {
            this.closePanel();
            this.startAnalysis();
        });
    }

    /**
     * Show toast notification
     */
    showToast(message, type = 'info') {
        const toast = document.createElement('div');
        toast.style.cssText = `
            position: fixed;
            bottom: 100px;
            left: 50%;
            transform: translateX(-50%);
            background: ${type === 'success' ? '#10b981' : type === 'warning' ? '#f59e0b' : '#6366f1'};
            color: white;
            padding: 12px 24px;
            border-radius: 8px;
            font-size: 14px;
            font-weight: 500;
            z-index: 99999999;
            box-shadow: 0 4px 20px rgba(0,0,0,0.3);
        `;
        toast.textContent = message;
        document.body.appendChild(toast);
        setTimeout(() => toast.remove(), 3000);
    }

    /**
     * Helper: Calculate average
     */
    calcAvg(arr, key) {
        if (!arr.length) return 0;
        const sum = arr.reduce((acc, item) => acc + (item[key] || 0), 0);
        return Math.round(sum / arr.length * 10) / 10;
    }

    /**
     * Format difficulty score with color coding (same logic as Market Analysis)
     * Green <30, Yellow 30-50, Orange 50-70, Red >=70
     */
    formatDifficulty(score) {
        if (score == null || score === 0) return '<span style="color: #6b7280;">—</span>';
        let color = '#4ade80'; // green
        let label = 'Easy';
        if (score >= 70) { color = '#f87171'; label = 'Hard'; }
        else if (score >= 50) { color = '#fb923c'; label = 'Med'; }
        else if (score >= 30) { color = '#facc15'; label = 'Fair'; }
        return `<span style="color: ${color}; font-weight: 700;">${score}</span><span style="color: #6b7280; font-size: 10px; margin-left: 2px;">${label}</span>`;
    }

    /**
     * Helper: Format number
     */
    formatNumber(num) {
        if (!num) return '0';
        if (num >= 1000000) return (num / 1000000).toFixed(1) + 'M';
        if (num >= 1000) return (num / 1000).toFixed(1) + 'K';
        return num.toLocaleString();
    }
}

// Make available globally
if (typeof window !== 'undefined') {
    window.CerebroUI = CerebroUI;
}
