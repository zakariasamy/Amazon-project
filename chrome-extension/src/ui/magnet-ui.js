// Magnet UI - Keyword Suggestion Tool Interface
// Allows users to discover keyword ideas from a seed keyword on Amazon

class MagnetUI {
    constructor() {
        this.panel = null;
        this.isAnalyzing = false;
        this.sortDir = 'desc';
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

    /**
     * Translate dynamic key to Arabic if in Arabic mode
     */
    t(key) {
        if (!this.isArabic()) return key;

        const translations = {
            'starting analysis...': 'بدء التحليل...',
            'fetching autocomplete suggestions...': 'جاري جلب اقتراحات الإكمال التلقائي...',
            'generating related keywords...': 'جاري توليد الكلمات المفتاحية ذات الصلة...',
            'extracting keywords from search results...': 'جاري استخراج الكلمات المفتاحية من نتائج البحث...',
            'extracting attributes from top products...': 'جاري استخراج الصفات من أفضل المنتجات...',
            'enriching keywords with metrics...': 'جاري إثراء الكلمات المفتاحية بالمقاييس...',
            'filtering generic keywords...': 'جاري تصفية الكلمات المفتاحية العامة...',
            'sorting results...': 'جاري فرز النتائج...',
            'analysis complete!': 'اكتمل التحليل بنجاح!'
        };

        const cleanedKey = key.toLowerCase().trim();
        return translations[cleanedKey] || key;
    }

    /**
     * Translate a DOM container's text nodes and placeholders to Arabic dynamically
     */
    translateDOM(container) {
        if (!this.isArabic() || !container) return;

        // Apply RTL styling to container
        container.style.direction = 'rtl';
        if (container.id === 'magnet-input-panel' || container.id === 'magnet-results-panel') {
            container.style.textAlign = 'right';
        }

        const translations = {
            'keyword magnet': 'مغناطيس الكلمات المفتاحية',
            'discover keyword ideas': 'اكتشف أفكار الكلمات المفتاحية',
            'seed keyword': 'الكلمة المفتاحية البذرية',
            'enter a keyword to discover ideas...': 'أدخل كلمة مفتاحية لاكتشاف الأفكار...',
            'discovery sources (all enabled)': 'مصادر الاكتشاف (جميعها مفعّل)',
            'autocomplete': 'الإكمال التلقائي',
            'related': 'ذات صلة',
            'titles': 'العناوين',
            'attributes': 'الصفات',
            'metrics': 'المقاييس',
            'cancel': 'إلغاء',
            'discover keywords': 'اكتشاف الكلمات المفتاحية',
            'starting analysis...': 'بدء التحليل...',
            'keywords found': 'الكلمات المكتشفة',
            'top iq score': 'أعلى درجة ذكاء',
            'avg volume': 'متوسط حجم البحث',
            'total sales': 'إجمالي المبيعات',
            'duration': 'المدة',
            'quick:': 'سريع:',
            'all': 'الكل',
            'high volume': 'حجم بحث مرتفع',
            'opportunity': 'فرصة',
            'low competition': 'منافسة منخفضة',
            'easy wins': 'أرباح سهلة',
            'long-tail': 'طويلة الذيل',
            'advanced filters': 'مرشحات متقدمة',
            'export': 'تصدير',
            'dashboard': 'لوحة التحكم',
            'search volume': 'حجم البحث',
            'magnet iq score': 'درجة ذكاء المغناطيس',
            'word count': 'عدد الكلمات',
            'title density max': 'الحد الأقصى لكثافة العنوان',
            'competition max': 'الحد الأقصى للمنافسة',
            'match type': 'نوع المطابقة',
            'all types': 'كل الأنواع',
            'seed': 'بذرة',
            'title': 'عنوان',
            'include phrases (comma separated)': 'تضمين العبارات (مفصولة بفاصلة)',
            'exclude phrases (comma separated)': 'استبعاد العبارات (مفصولة بفاصلة)',
            'apply filters': 'تطبيق المرشحات',
            'clear': 'مسح',
            'search keywords...': 'البحث عن كلمات مفتاحية...',
            'keyword': 'الكلمة المفتاحية',
            'volume ↕': 'حجم البحث ↕',
            'iq score ↕': 'درجة الذكاء ↕',
            'title density': 'كثافة العنوان',
            'competition': 'المنافسة',
            'cpr': 'معدل الإطلاق (CPR)',
            'sales': 'المبيعات',
            'avg price': 'متوسط السعر',
            'words': 'الكلمات',
            'type': 'النوع',
            'analysis failed': 'فشل التحليل',
            'close': 'إغلاق',
            'copy': 'نسخ',
            'please enter a seed keyword': 'الرجاء إدخال كلمة مفتاحية بذرية',
            'analysis already in progress': 'التحليل قيد التنفيذ بالفعل',
            'magnetanalyzer not loaded. please reload the page.': 'محلل المغناطيس لم يتم تحميله. يرجى إعادة تحميل الصفحة.',
            'no data to export': 'لا توجد بيانات للتصدير',
            'csv exported successfully!': 'تم تصدير ملف CSV بنجاح!',
            'please login to save results': 'الرجاء تسجيل الدخول لحفظ النتائج',
            'analysis saved to dashboard!': 'تم حفظ التحليل في لوحة التحكم!',
            'could not save to dashboard: ': 'تعذر الحفظ في لوحة التحكم: ',
            'backend unavailable - results not saved': 'الخادم الخلفي غير متاح - لم يتم حفظ النتائج',
            'showing': 'عرض',
            'of': 'من',
            'keywords': 'كلمات مفتاحية',
            'filtered': 'مفلترة',
            'analysis complete': 'اكتمل التحليل',
            'found': 'تم العثور على',
            'keywords for': 'كلمات مفتاحية لـ',
            'search volume minimum': 'الحد الأدنى لحجم البحث',
            'search volume maximum': 'الحد الأقصى لحجم البحث',
            'magnet iq score minimum': 'الحد الأدنى لدرجة ذكاء المغناطيس',
            'magnet iq score maximum': 'الحد الأقصى لدرجة ذكاء المغناطيس',
            'word count minimum': 'الحد الأدنى لعدد الكلمات',
            'word count maximum': 'الحد الأقصى لعدد الكلمات'
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
     * Initialize Magnet UI (called from toolbar button, not auto-init)
     */
    init() {
        console.log('[Magnet] UI initialized and ready');
    }

    /**
     * Open the seed keyword input panel
     */
    openInputPanel() {
        if (this.panel) return;

        // Get current search term if on search page
        const urlParams = new URLSearchParams(window.location.search);
        const currentKeyword = urlParams.get('k') || '';

        // Create backdrop
        const backdrop = document.createElement('div');
        backdrop.id = 'magnet-backdrop';
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

        // Create panel
        this.panel = document.createElement('div');
        this.panel.id = 'magnet-input-panel';
        this.panel.style.cssText = `
            position: fixed;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            width: 500px;
            background: #0f172a;
            border: 1px solid #374151;
            border-radius: 16px;
            z-index: 9999999;
            box-shadow: 0 25px 50px rgba(0,0,0,0.5);
            font-family: 'Inter', -apple-system, system-ui, sans-serif;
            overflow: hidden;
        `;

        const marketplace = window.location.hostname;
        const marketplaceFlag = this.getMarketplaceFlag(marketplace);

        this.panel.innerHTML = `
            <!-- Header -->
            <div style="
                padding: 20px 24px;
                background: linear-gradient(135deg, #f59e0b20, #d9770620);
                border-bottom: 1px solid #374151;
                display: flex;
                align-items: center;
                gap: 14px;
            ">
                <span style="font-size: 36px;">🧲</span>
                <div>
                    <div style="font-weight: 700; color: #fff; font-size: 20px;">Keyword Magnet</div>
                    <div style="color: #9ca3af; font-size: 12px;">${marketplaceFlag} ${marketplace} • Discover keyword ideas</div>
                </div>
                <button id="magnet-close-btn" style="
                    margin-left: auto;
                    background: #374151;
                    border: none;
                    color: #9ca3af;
                    width: 32px;
                    height: 32px;
                    border-radius: 8px;
                    cursor: pointer;
                    font-size: 18px;
                ">×</button>
            </div>

            <!-- Content -->
            <div style="padding: 24px;">
                <label style="display: block; color: #e5e7eb; font-size: 14px; font-weight: 600; margin-bottom: 8px;">
                    Seed Keyword
                </label>
                <input type="text" id="magnet-seed-input" value="${currentKeyword}" placeholder="Enter a keyword to discover ideas..." style="
                    width: 100%;
                    padding: 14px 16px;
                    background: #1e293b;
                    border: 2px solid #374151;
                    border-radius: 10px;
                    color: #fff;
                    font-size: 15px;
                    outline: none;
                    transition: border-color 0.2s;
                    box-sizing: border-box;
                ">
                <p style="color: #6b7280; font-size: 11px; margin-top: 8px;">
                    💡 Magnet will find related keywords, autocomplete suggestions, and keyword ideas from search results.
                </p>

                <div style="margin-top: 20px;">
                    <label style="display: block; color: #e5e7eb; font-size: 14px; font-weight: 600; margin-bottom: 8px;">
                        Discovery Sources (All Enabled)
                    </label>
                    <div style="display: flex; flex-wrap: wrap; gap: 8px;">
                        <span style="background: #a78bfa20; color: #a78bfa; padding: 6px 12px; border-radius: 6px; font-size: 11px; font-weight: 600;">
                            ⌨️ Autocomplete
                        </span>
                        <span style="background: #10b98120; color: #10b981; padding: 6px 12px; border-radius: 6px; font-size: 11px; font-weight: 600;">
                            🔗 Related
                        </span>
                        <span style="background: #60a5fa20; color: #60a5fa; padding: 6px 12px; border-radius: 6px; font-size: 11px; font-weight: 600;">
                            📄 Titles
                        </span>
                        <span style="background: #2dd4bf20; color: #2dd4bf; padding: 6px 12px; border-radius: 6px; font-size: 11px; font-weight: 600;">
                            🏷️ Attributes
                        </span>
                        <span style="background: #f59e0b20; color: #f59e0b; padding: 6px 12px; border-radius: 6px; font-size: 11px; font-weight: 600;">
                            📊 Metrics
                        </span>
                    </div>
                </div>
            </div>

            <!-- Footer -->
            <div style="
                padding: 16px 24px;
                background: #1e293b;
                border-top: 1px solid #374151;
                display: flex;
                justify-content: flex-end;
                gap: 12px;
            ">
                <button id="magnet-cancel-btn" style="
                    background: #374151;
                    border: none;
                    color: #e5e7eb;
                    padding: 12px 24px;
                    border-radius: 10px;
                    cursor: pointer;
                    font-size: 14px;
                    font-weight: 600;
                ">Cancel</button>
                <button id="magnet-analyze-btn" style="
                    background: linear-gradient(135deg, #f59e0b, #d97706);
                    border: none;
                    color: white;
                    padding: 12px 24px;
                    border-radius: 10px;
                    cursor: pointer;
                    font-size: 14px;
                    font-weight: 600;
                    display: flex;
                    align-items: center;
                    gap: 8px;
                    box-shadow: 0 4px 15px rgba(245, 158, 11, 0.3);
                    transition: all 0.2s;
                ">
                    <span>🔍</span>
                    <span>Discover Keywords</span>
                </button>
            </div>
        `;

        document.body.appendChild(this.panel);
        this.translateDOM(this.panel);

        // Event listeners
        backdrop.addEventListener('click', () => this.closePanel());
        this.panel.querySelector('#magnet-close-btn').addEventListener('click', () => this.closePanel());
        this.panel.querySelector('#magnet-cancel-btn').addEventListener('click', () => this.closePanel());
        this.panel.querySelector('#magnet-analyze-btn').addEventListener('click', () => this.startAnalysis());

        // Focus input
        const input = this.panel.querySelector('#magnet-seed-input');
        input.focus();
        input.select();

        // Enter key to start
        input.addEventListener('keypress', (e) => {
            if (e.key === 'Enter') this.startAnalysis();
        });

        // Input focus effect
        input.addEventListener('focus', () => input.style.borderColor = '#f59e0b');
        input.addEventListener('blur', () => input.style.borderColor = '#374151');
    }

    /**
     * Close the input panel
     */
    closePanel() {
        document.getElementById('magnet-backdrop')?.remove();
        document.getElementById('magnet-input-panel')?.remove();
        document.getElementById('magnet-results-panel')?.remove();
        this.panel = null;
    }

    /**
     * Start the Magnet analysis
     */
    async startAnalysis() {
        const input = this.panel?.querySelector('#magnet-seed-input');
        const seedKeyword = input?.value?.trim();

        if (!seedKeyword) {
            this.showToast('Please enter a seed keyword', 'warning');
            return;
        }

        if (this.isAnalyzing) {
            this.showToast('Analysis already in progress', 'warning');
            return;
        }

        this.isAnalyzing = true;

        // All options are always enabled (forced)
        const options = {
            useAutocomplete: true,
            useRelated: true,
            useTitles: true,
            useAttributes: true,
            scrapeMetrics: true,
        };

        // Transform to results panel
        this.showResultsPanel(seedKeyword);

        try {
            // Initialize MagnetAnalyzer
            if (typeof MagnetAnalyzer === 'undefined') {
                throw new Error('MagnetAnalyzer not loaded. Please reload the page.');
            }

            const marketplace = window.location.hostname;
            const analyzer = new MagnetAnalyzer(marketplace);

            const results = await analyzer.analyze(seedKeyword, options, (percent, message) => {
                this.updateProgress(percent, message);
            });

            // Display results
            this.displayResults(results);

            // Auto-save to backend
            this.saveToBackend(results);

        } catch (error) {
            console.error('[Magnet] Analysis error:', error);
            this.showError(error.message);
        } finally {
            this.isAnalyzing = false;
        }
    }

    /**
     * Show results panel with progress
     */
    showResultsPanel(seedKeyword) {
        // Remove input panel
        document.getElementById('magnet-input-panel')?.remove();

        // Create results panel
        this.panel = document.createElement('div');
        this.panel.id = 'magnet-results-panel';
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

        const marketplace = window.location.hostname;
        const marketplaceFlag = this.getMarketplaceFlag(marketplace);

        this.panel.innerHTML = `
            <!-- Header -->
            <div style="
                padding: 16px 24px;
                background: linear-gradient(135deg, #1e293b, #0f172a);
                border-bottom: 1px solid #374151;
                display: flex;
                justify-content: space-between;
                align-items: center;
            ">
                <div style="display: flex; align-items: center; gap: 12px;">
                    <span style="font-size: 28px;">🧲</span>
                    <div>
                        <div style="font-weight: 700; color: #fff; font-size: 18px;">Keyword Magnet</div>
                        <div id="magnet-status" style="color: #94a3b8; font-size: 12px;">${marketplaceFlag} Analyzing "${seedKeyword}"...</div>
                    </div>
                </div>
                <button id="magnet-close-btn" style="
                    background: #374151;
                    border: none;
                    color: #9ca3af;
                    width: 32px;
                    height: 32px;
                    border-radius: 8px;
                    cursor: pointer;
                    font-size: 18px;
                ">×</button>
            </div>

            <!-- Progress -->
            <div id="magnet-content" style="flex: 1; overflow: hidden; display: flex; flex-direction: column;">
                <div id="magnet-progress" style="padding: 60px; text-align: center;">
                    <div style="
                        width: 80px;
                        height: 80px;
                        border: 4px solid #374151;
                        border-top-color: #f59e0b;
                        border-radius: 50%;
                        margin: 0 auto 20px;
                        animation: magnet-spin 1s linear infinite;
                    "></div>
                    <div id="magnet-progress-text" style="color: #fff; font-size: 16px; font-weight: 600;">Starting analysis...</div>
                    <div style="
                        width: 300px;
                        height: 6px;
                        background: #374151;
                        border-radius: 3px;
                        margin: 20px auto 0;
                        overflow: hidden;
                    ">
                        <div id="magnet-progress-fill" style="
                            width: 0%;
                            height: 100%;
                            background: linear-gradient(90deg, #f59e0b, #d97706);
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
            @keyframes magnet-spin {
                to { transform: rotate(360deg); }
            }
        `;
        this.panel.appendChild(style);

        document.body.appendChild(this.panel);
        this.translateDOM(this.panel);

        // Close handler
        this.panel.querySelector('#magnet-close-btn').addEventListener('click', () => this.closePanel());
    }

    /**
     * Update progress display
     */
    updateProgress(percent, message) {
        const progressText = this.panel?.querySelector('#magnet-progress-text');
        const progressFill = this.panel?.querySelector('#magnet-progress-fill');
        const status = this.panel?.querySelector('#magnet-status');

        const translated = this.t(message);

        if (progressText) progressText.textContent = translated;
        if (progressFill) progressFill.style.width = `${percent}%`;
        if (status) status.textContent = `${percent}% - ${translated}`;
    }

    /**
     * Display analysis results
     */
    displayResults(results) {
        const content = this.panel?.querySelector('#magnet-content');
        if (!content) return;

        const keywords = results.keywords || [];
        const marketplace = results.marketplace || window.location.hostname;
        const currency = this.getMarketplaceCurrency(marketplace);

        content.innerHTML = `
            <!-- Summary Stats -->
            <div style="
                padding: 16px 24px;
                background: #1e293b;
                border-bottom: 1px solid #374151;
                display: grid;
                grid-template-columns: repeat(6, 1fr);
                gap: 16px;
            ">
                <div style="text-align: center;">
                    <div style="color: #9ca3af; font-size: 10px; text-transform: uppercase; font-weight: 600;">Seed Keyword</div>
                    <div style="color: #fbbf24; font-size: 16px; font-weight: 700;">${results.seed_keyword}</div>
                </div>
                <div style="text-align: center;">
                    <div style="color: #9ca3af; font-size: 10px; text-transform: uppercase; font-weight: 600;">Keywords Found</div>
                    <div style="color: #10b981; font-size: 24px; font-weight: 700;">${keywords.length}</div>
                </div>
                <div style="text-align: center;">
                    <div style="color: #9ca3af; font-size: 10px; text-transform: uppercase; font-weight: 600;">Top IQ Score</div>
                    <div style="color: #f59e0b; font-size: 24px; font-weight: 700;">${this.calcMax(keywords, 'magnet_iq_score')}</div>
                </div>
                <div style="text-align: center;">
                    <div style="color: #9ca3af; font-size: 10px; text-transform: uppercase; font-weight: 600;">Avg Volume</div>
                    <div style="color: #60a5fa; font-size: 24px; font-weight: 700;">${this.formatNumber(this.calcAvg(keywords, 'search_volume'))}</div>
                </div>
                <div style="text-align: center;">
                    <div style="color: #9ca3af; font-size: 10px; text-transform: uppercase; font-weight: 600;">Total Sales</div>
                    <div style="color: #22d3ee; font-size: 24px; font-weight: 700;">${this.formatNumber(this.calcSum(keywords, 'keyword_sales'))}</div>
                </div>
                <div style="text-align: center;">
                    <div style="color: #9ca3af; font-size: 10px; text-transform: uppercase; font-weight: 600;">Duration</div>
                    <div style="color: #a78bfa; font-size: 24px; font-weight: 700;">${results.duration_seconds}s</div>
                </div>
            </div>

            <!-- Quick Filters -->
            <div style="
                padding: 12px 24px;
                background: #1e293b;
                border-bottom: 1px solid #374151;
                display: flex;
                align-items: center;
                gap: 8px;
                flex-wrap: wrap;
            ">
                <span style="color: #9ca3af; font-size: 11px; font-weight: 600;">Quick:</span>
                <button class="magnet-quick-filter active" data-filter="all" style="
                    background: #f59e0b; border: none; color: white;
                    padding: 5px 10px; border-radius: 5px; cursor: pointer; font-size: 11px;
                ">All (${keywords.length})</button>
                <button class="magnet-quick-filter" data-filter="high_volume" style="
                    background: #374151; border: none; color: #e5e7eb;
                    padding: 5px 10px; border-radius: 5px; cursor: pointer; font-size: 11px;
                ">🔥 High Volume</button>
                <button class="magnet-quick-filter" data-filter="opportunity" style="
                    background: #374151; border: none; color: #e5e7eb;
                    padding: 5px 10px; border-radius: 5px; cursor: pointer; font-size: 11px;
                ">💎 Opportunity</button>
                <button class="magnet-quick-filter" data-filter="low_competition" style="
                    background: #374151; border: none; color: #e5e7eb;
                    padding: 5px 10px; border-radius: 5px; cursor: pointer; font-size: 11px;
                ">✅ Low Competition</button>
                <button class="magnet-quick-filter" data-filter="easy_wins" style="
                    background: #374151; border: none; color: #e5e7eb;
                    padding: 5px 10px; border-radius: 5px; cursor: pointer; font-size: 11px;
                ">🎯 Easy Wins</button>
                <button class="magnet-quick-filter" data-filter="long_tail" style="
                    background: #374151; border: none; color: #e5e7eb;
                    padding: 5px 10px; border-radius: 5px; cursor: pointer; font-size: 11px;
                ">📝 Long-tail</button>
                <div style="flex: 1;"></div>
                <button id="magnet-toggle-filters" style="
                    background: #374151; border: none; color: #9ca3af;
                    padding: 5px 10px; border-radius: 5px; cursor: pointer; font-size: 11px;
                ">⚙️ Advanced Filters</button>
                <button id="magnet-export-btn" style="
                    background: #10b981; border: none; color: white;
                    padding: 6px 14px; border-radius: 6px; cursor: pointer; font-size: 11px; font-weight: 600;
                ">📥 Export</button>
                <a href="${this.getDashboardUrl()}" target="_blank" style="
                    background: #6366f1; border: none; color: white;
                    padding: 6px 14px; border-radius: 6px; cursor: pointer; font-size: 11px; font-weight: 600;
                    text-decoration: none;
                ">📊 Dashboard</a>
            </div>

            <!-- Advanced Filters (Helium 10-style) -->
            <div id="magnet-advanced-filters" style="
                padding: 16px 24px;
                background: #0f172a;
                border-bottom: 1px solid #374151;
                display: none;
            ">
                <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(140px, 1fr)); gap: 12px; font-size: 11px;">
                    <!-- Volume Range -->
                    <div>
                        <label style="color: #9ca3af; display: block; margin-bottom: 4px;">Search Volume</label>
                        <div style="display: flex; gap: 4px; align-items: center;">
                            <input type="number" id="filter-vol-min" placeholder="Min" style="
                                width: 60px; padding: 6px; border-radius: 4px; border: 1px solid #374151;
                                background: #1e293b; color: #e5e7eb; font-size: 11px;
                            ">
                            <span style="color: #6b7280;">-</span>
                            <input type="number" id="filter-vol-max" placeholder="Max" style="
                                width: 60px; padding: 6px; border-radius: 4px; border: 1px solid #374151;
                                background: #1e293b; color: #e5e7eb; font-size: 11px;
                            ">
                        </div>
                    </div>
                    
                    <!-- IQ Score Range -->
                    <div>
                        <label style="color: #9ca3af; display: block; margin-bottom: 4px;">Magnet IQ Score</label>
                        <div style="display: flex; gap: 4px; align-items: center;">
                            <input type="number" id="filter-iq-min" placeholder="Min" step="0.5" style="
                                width: 60px; padding: 6px; border-radius: 4px; border: 1px solid #374151;
                                background: #1e293b; color: #e5e7eb; font-size: 11px;
                            ">
                            <span style="color: #6b7280;">-</span>
                            <input type="number" id="filter-iq-max" placeholder="Max" step="0.5" style="
                                width: 60px; padding: 6px; border-radius: 4px; border: 1px solid #374151;
                                background: #1e293b; color: #e5e7eb; font-size: 11px;
                            ">
                        </div>
                    </div>
                    
                    <!-- Word Count Range -->
                    <div>
                        <label style="color: #9ca3af; display: block; margin-bottom: 4px;">Word Count</label>
                        <div style="display: flex; gap: 4px; align-items: center;">
                            <input type="number" id="filter-words-min" placeholder="Min" min="1" style="
                                width: 50px; padding: 6px; border-radius: 4px; border: 1px solid #374151;
                                background: #1e293b; color: #e5e7eb; font-size: 11px;
                            ">
                            <span style="color: #6b7280;">-</span>
                            <input type="number" id="filter-words-max" placeholder="Max" style="
                                width: 50px; padding: 6px; border-radius: 4px; border: 1px solid #374151;
                                background: #1e293b; color: #e5e7eb; font-size: 11px;
                            ">
                        </div>
                    </div>
                    
                    <!-- Title Density -->
                    <div>
                        <label style="color: #9ca3af; display: block; margin-bottom: 4px;">Title Density Max</label>
                        <input type="number" id="filter-td-max" placeholder="e.g. 10" min="0" max="48" style="
                            width: 100%; padding: 6px; border-radius: 4px; border: 1px solid #374151;
                            background: #1e293b; color: #e5e7eb; font-size: 11px;
                        ">
                    </div>
                    
                    <!-- Competition Max -->
                    <div>
                        <label style="color: #9ca3af; display: block; margin-bottom: 4px;">Competition Max</label>
                        <input type="number" id="filter-comp-max" placeholder="e.g. 50000" style="
                            width: 100%; padding: 6px; border-radius: 4px; border: 1px solid #374151;
                            background: #1e293b; color: #e5e7eb; font-size: 11px;
                        ">
                    </div>
                    
                    <!-- Match Type -->
                    <div>
                        <label style="color: #9ca3af; display: block; margin-bottom: 4px;">Match Type</label>
                        <select id="filter-match-type" style="
                            width: 100%; padding: 6px; border-radius: 4px; border: 1px solid #374151;
                            background: #1e293b; color: #e5e7eb; font-size: 11px;
                        ">
                            <option value="all">All Types</option>
                            <option value="seed">Seed</option>
                            <option value="autocomplete">Autocomplete</option>
                            <option value="related">Related</option>
                            <option value="title">Title</option>
                        </select>
                    </div>
                </div>
                
                <div style="display: grid; grid-template-columns: 1fr 1fr auto; gap: 12px; margin-top: 12px; font-size: 11px;">
                    <!-- Include Phrase -->
                    <div>
                        <label style="color: #9ca3af; display: block; margin-bottom: 4px;">Include Phrases (comma separated)</label>
                        <input type="text" id="filter-include" placeholder="e.g. digital, kitchen" style="
                            width: 100%; padding: 6px; border-radius: 4px; border: 1px solid #374151;
                            background: #1e293b; color: #e5e7eb; font-size: 11px;
                        ">
                    </div>
                    
                    <!-- Exclude Phrase -->
                    <div>
                        <label style="color: #9ca3af; display: block; margin-bottom: 4px;">Exclude Phrases (comma separated)</label>
                        <input type="text" id="filter-exclude" placeholder="e.g. cheap, used" style="
                            width: 100%; padding: 6px; border-radius: 4px; border: 1px solid #374151;
                            background: #1e293b; color: #e5e7eb; font-size: 11px;
                        ">
                    </div>
                    
                    <!-- Apply / Clear -->
                    <div style="display: flex; align-items: flex-end; gap: 8px;">
                        <button id="magnet-apply-filters" style="
                            background: #f59e0b; border: none; color: white;
                            padding: 8px 16px; border-radius: 6px; cursor: pointer; font-size: 12px; font-weight: 600;
                        ">Apply Filters</button>
                        <button id="magnet-clear-filters" style="
                            background: #374151; border: none; color: #e5e7eb;
                            padding: 8px 16px; border-radius: 6px; cursor: pointer; font-size: 12px;
                        ">Clear</button>
                    </div>
                </div>
                
                <!-- Search within filters -->
                <div style="margin-top: 12px;">
                    <input type="text" id="magnet-search" placeholder="🔍 Search keywords..." style="
                        width: 100%; padding: 8px 12px; border-radius: 6px; border: 1px solid #374151;
                        background: #1e293b; color: #e5e7eb; font-size: 12px;
                    ">
                </div>
            </div>

            <!-- Results Table -->
            <div style="flex: 1; overflow: auto; padding: 0;">
                <table id="magnet-results-table" style="width: 100%; border-collapse: collapse; font-size: 12px;">
                    <thead style="position: sticky; top: 0; z-index: 10;">
                        <tr style="background: #0f172a; color: #9ca3af; text-transform: uppercase; font-size: 10px;">
                            <th style="padding: 12px; text-align: left; border-bottom: 2px solid #374151;">Keyword</th>
                            <th style="padding: 12px; text-align: right; border-bottom: 2px solid #374151; cursor: pointer;" data-sort="search_volume">Volume ↕</th>
                            <th style="padding: 12px; text-align: right; border-bottom: 2px solid #374151; cursor: pointer;" data-sort="magnet_iq_score">IQ Score ↕</th>
                            <th style="padding: 12px; text-align: right; border-bottom: 2px solid #374151;">Title Density</th>
                            <th style="padding: 12px; text-align: right; border-bottom: 2px solid #374151;">Competition</th>
                            <th style="padding: 12px; text-align: right; border-bottom: 2px solid #374151;">CPR</th>
                            <th style="padding: 12px; text-align: right; border-bottom: 2px solid #374151;">Sales</th>
                            <th style="padding: 12px; text-align: right; border-bottom: 2px solid #374151;">Avg Price</th>
                            <th style="padding: 12px; text-align: center; border-bottom: 2px solid #374151;">Words</th>
                            <th style="padding: 12px; text-align: center; border-bottom: 2px solid #374151;">Type</th>
                        </tr>
                    </thead>
                    <tbody id="magnet-results-tbody">
                        ${this.renderKeywordRows(keywords, currency)}
                    </tbody>
                </table>
            </div>
        `;

        // Store data for filtering
        this.resultsData = { keywords, currency };

        // Quick filter events
        content.querySelectorAll('.magnet-quick-filter').forEach(btn => {
            btn.addEventListener('click', (e) => {
                content.querySelectorAll('.magnet-quick-filter').forEach(b => {
                    b.style.background = '#374151';
                });
                e.target.style.background = '#f59e0b';
                this.clearAdvancedFilters();
                this.applyQuickFilter(e.target.dataset.filter);
            });
        });

        // Toggle advanced filters
        content.querySelector('#magnet-toggle-filters')?.addEventListener('click', () => {
            const filtersPanel = content.querySelector('#magnet-advanced-filters');
            const toggleBtn = content.querySelector('#magnet-toggle-filters');
            if (filtersPanel) {
                const isVisible = filtersPanel.style.display !== 'none';
                filtersPanel.style.display = isVisible ? 'none' : 'block';
                toggleBtn.style.background = isVisible ? '#374151' : '#f59e0b';
                toggleBtn.style.color = isVisible ? '#9ca3af' : 'white';
            }
        });

        // Apply advanced filters
        content.querySelector('#magnet-apply-filters')?.addEventListener('click', () => {
            this.applyAdvancedFilters();
        });

        // Clear advanced filters
        content.querySelector('#magnet-clear-filters')?.addEventListener('click', () => {
            this.clearAdvancedFilters();
            this.applyAdvancedFilters();
        });

        // Search
        content.querySelector('#magnet-search')?.addEventListener('input', (e) => {
            this.filterBySearch(e.target.value);
        });

        // Export
        content.querySelector('#magnet-export-btn')?.addEventListener('click', () => this.exportCSV());

        // Sorting
        content.querySelectorAll('th[data-sort]').forEach(th => {
            th.addEventListener('click', () => this.sortTable(th.dataset.sort));
        });

        // Update status
        const status = this.panel?.querySelector('#magnet-status');
        if (status) status.textContent = `✅ Found ${keywords.length} keywords for "${results.seed_keyword}"`;

        this.translateDOM(this.panel);
    }

    /**
     * Render keyword rows
     */
    renderKeywordRows(keywords, currency = 'USD') {
        return keywords.map((kw, idx) => {
            const iqClass = kw.magnet_iq_score >= 5 ? '#10b981' :
                kw.magnet_iq_score >= 3 ? '#f59e0b' :
                    kw.magnet_iq_score >= 1 ? '#fb923c' : '#ef4444';

            const typeColors = {
                'seed': '#fbbf24',
                'autocomplete': '#a78bfa',
                'related': '#10b981',
                'title': '#60a5fa',
                'suggestion': '#f472b6',
                'attribute': '#2dd4bf',
                'google': '#f87171',
                'bing': '#38bdf8',
                'youtube': '#f87171'
            };

            return `
                <tr style="background: ${idx % 2 === 0 ? '#0f172a' : '#1e293b'}; border-bottom: 1px solid #374151;">
                    <td style="padding: 10px 12px;">
                        <span style="color: #fff; font-weight: 500;">${kw.keyword}</span>
                        <button onclick="navigator.clipboard.writeText('${kw.keyword}')" style="
                            background: none; border: none; color: #6b7280; cursor: pointer; margin-left: 6px; font-size: 12px;
                        " title="Copy">📋</button>
                    </td>
                    <td style="padding: 10px 12px; text-align: right; color: #60a5fa; font-weight: 600;">${this.formatNumber(kw.search_volume)}</td>
                    <td style="padding: 10px 12px; text-align: right;">
                        <span style="background: ${iqClass}20; color: ${iqClass}; padding: 2px 8px; border-radius: 4px; font-weight: 700;">
                            ${(kw.magnet_iq_score || 0).toFixed(1)}
                        </span>
                    </td>
                    <td style="padding: 10px 12px; text-align: right; color: #9ca3af;">${kw.title_density}/48</td>
                    <td style="padding: 10px 12px; text-align: right; color: #f472b6;">${this.formatNumber(kw.competing_products)}</td>
                    <td style="padding: 10px 12px; text-align: right; color: #f59e0b;">${kw.cpr_8day}/day</td>
                    <td style="padding: 10px 12px; text-align: right; color: #10b981;">${this.formatNumber(kw.keyword_sales)}</td>
                    <td style="padding: 10px 12px; text-align: right;">
                        <span style="color: #6b7280; font-size: 10px;">${currency}</span>
                        <span style="color: #e5e7eb;">${(kw.avg_price || 0).toFixed(2)}</span>
                    </td>
                    <td style="padding: 10px 12px; text-align: center; color: #9ca3af;">${kw.word_count}</td>
                    <td style="padding: 10px 12px; text-align: center;">
                        ${(kw.match_type || '').split(',').map(rawType => {
                const type = rawType.trim();
                return `
                                <span style="
                                    background: ${typeColors[type] || '#6b7280'}20;
                                    color: ${typeColors[type] || '#6b7280'};
                                    padding: 2px 6px;
                                    border-radius: 4px;
                                    font-size: 9px;
                                    font-weight: 600;
                                    text-transform: uppercase;
                                    display: inline-block;
                                    margin: 1px;
                                ">${type}</span>
                            `;
            }).join('')}
                    </td>
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

        switch (filterName) {
            case 'high_volume':
                filtered = filtered.filter(kw => kw.search_volume >= 1000);
                break;
            case 'opportunity':
                filtered = filtered.filter(kw => kw.magnet_iq_score >= 3 && kw.title_density <= 5);
                break;
            case 'low_competition':
                filtered = filtered.filter(kw => kw.competing_products <= 10000 && kw.search_volume >= 500);
                break;
            case 'easy_wins':
                filtered = filtered.filter(kw => kw.cpr_8day <= 10 && kw.competing_products <= 5000 && kw.search_volume >= 300);
                break;
            case 'long_tail':
                filtered = filtered.filter(kw => kw.word_count >= 4 && kw.search_volume >= 100);
                break;
        }

        const tbody = this.panel?.querySelector('#magnet-results-tbody');
        if (tbody) {
            tbody.innerHTML = this.renderKeywordRows(filtered, this.resultsData.currency);
            this.translateDOM(tbody);
        }

        const status = this.panel?.querySelector('#magnet-status');
        if (status) {
            status.textContent = `Showing ${filtered.length} of ${this.resultsData.keywords.length} keywords`;
            this.translateDOM(status);
        }
    }

    /**
     * Apply advanced filters (Helium 10-style)
     */
    applyAdvancedFilters() {
        if (!this.resultsData) return;

        // Get filter values
        const volMin = parseFloat(this.panel?.querySelector('#filter-vol-min')?.value) || 0;
        const volMax = parseFloat(this.panel?.querySelector('#filter-vol-max')?.value) || Infinity;
        const iqMin = parseFloat(this.panel?.querySelector('#filter-iq-min')?.value) || 0;
        const iqMax = parseFloat(this.panel?.querySelector('#filter-iq-max')?.value) || Infinity;
        const wordsMin = parseInt(this.panel?.querySelector('#filter-words-min')?.value) || 0;
        const wordsMax = parseInt(this.panel?.querySelector('#filter-words-max')?.value) || Infinity;
        const tdMax = parseInt(this.panel?.querySelector('#filter-td-max')?.value) || Infinity;
        const compMax = parseInt(this.panel?.querySelector('#filter-comp-max')?.value) || Infinity;
        const matchType = this.panel?.querySelector('#filter-match-type')?.value || 'all';
        const includePhrase = (this.panel?.querySelector('#filter-include')?.value || '').toLowerCase().trim();
        const excludePhrase = (this.panel?.querySelector('#filter-exclude')?.value || '').toLowerCase().trim();
        const searchText = (this.panel?.querySelector('#magnet-search')?.value || '').toLowerCase().trim();

        let filtered = this.resultsData.keywords.filter(kw => {
            // Volume filter
            if (kw.search_volume < volMin || kw.search_volume > volMax) return false;

            // IQ Score filter
            if ((kw.magnet_iq_score || 0) < iqMin || (kw.magnet_iq_score || 0) > iqMax) return false;

            // Word count filter
            if (kw.word_count < wordsMin || kw.word_count > wordsMax) return false;

            // Title density filter
            if ((kw.title_density || 0) > tdMax) return false;

            // Competition filter
            if ((kw.competing_products || 0) > compMax) return false;

            // Match type filter
            if (matchType !== 'all' && kw.match_type !== matchType) return false;

            // Include phrase filter (any of the comma-separated phrases)
            if (includePhrase) {
                const phrases = includePhrase.split(',').map(p => p.trim()).filter(p => p);
                if (!phrases.some(p => kw.keyword.toLowerCase().includes(p))) return false;
            }

            // Exclude phrase filter (none of the comma-separated phrases)
            if (excludePhrase) {
                const phrases = excludePhrase.split(',').map(p => p.trim()).filter(p => p);
                if (phrases.some(p => kw.keyword.toLowerCase().includes(p))) return false;
            }

            // Search text filter
            if (searchText && !kw.keyword.toLowerCase().includes(searchText)) return false;

            return true;
        });

        // Clear quick filter active states
        this.panel?.querySelectorAll('.magnet-quick-filter').forEach(btn => {
            btn.style.background = '#374151';
        });

        const tbody = this.panel?.querySelector('#magnet-results-tbody');
        if (tbody) {
            tbody.innerHTML = this.renderKeywordRows(filtered, this.resultsData.currency);
            this.translateDOM(tbody);
        }

        const status = this.panel?.querySelector('#magnet-status');
        if (status) {
            status.textContent = `Showing ${filtered.length} of ${this.resultsData.keywords.length} keywords (filtered)`;
            this.translateDOM(status);
        }
    }

    /**
     * Clear advanced filter inputs
     */
    clearAdvancedFilters() {
        const inputs = [
            '#filter-vol-min', '#filter-vol-max',
            '#filter-iq-min', '#filter-iq-max',
            '#filter-words-min', '#filter-words-max',
            '#filter-td-max', '#filter-comp-max',
            '#filter-include', '#filter-exclude',
            '#magnet-search'
        ];

        inputs.forEach(id => {
            const el = this.panel?.querySelector(id);
            if (el) el.value = '';
        });

        const matchType = this.panel?.querySelector('#filter-match-type');
        if (matchType) matchType.value = 'all';
    }

    /**
     * Filter by search text
     */
    filterBySearch(searchText) {
        if (!this.resultsData) return;

        const text = searchText.toLowerCase().trim();
        let filtered = this.resultsData.keywords;

        if (text) {
            filtered = filtered.filter(kw => kw.keyword.toLowerCase().includes(text));
        }

        const tbody = this.panel?.querySelector('#magnet-results-tbody');
        if (tbody) {
            tbody.innerHTML = this.renderKeywordRows(filtered, this.resultsData.currency);
            this.translateDOM(tbody);
        }
    }

    /**
     * Sort table
     */
    sortTable(column) {
        if (!this.resultsData) return;

        this.sortDir = this.sortDir === 'desc' ? 'asc' : 'desc';

        this.resultsData.keywords.sort((a, b) => {
            const aVal = a[column] || 0;
            const bVal = b[column] || 0;
            return this.sortDir === 'desc' ? bVal - aVal : aVal - bVal;
        });

        const tbody = this.panel?.querySelector('#magnet-results-tbody');
        if (tbody) {
            tbody.innerHTML = this.renderKeywordRows(this.resultsData.keywords, this.resultsData.currency);
            this.translateDOM(tbody);
        }
    }

    /**
     * Export to CSV
     */
    exportCSV() {
        if (!this.resultsData?.keywords?.length) {
            this.showToast('No data to export', 'warning');
            return;
        }

        const headers = ['Keyword', 'Search Volume', 'IQ Score', 'Title Density', 'Competing Products',
            'Word Count', 'CPR 8-Day', 'CPR Total', 'Keyword Sales', 'Avg Price', 'Avg Reviews',
            'Sponsored Count', 'Match Type', 'Relevance Score'];

        const rows = this.resultsData.keywords.map(kw => [
            `"${kw.keyword}"`,
            kw.search_volume,
            kw.magnet_iq_score?.toFixed(2) || 0,
            kw.title_density,
            kw.competing_products,
            kw.word_count,
            kw.cpr_8day,
            kw.cpr_total || 0,
            kw.keyword_sales,
            kw.avg_price?.toFixed(2) || 0,
            kw.avg_reviews || 0,
            kw.sponsored_count || 0,
            kw.match_type,
            kw.relevance_score || 0
        ]);

        const csv = [headers.join(','), ...rows.map(r => r.join(','))].join('\n');

        const blob = new Blob([csv], { type: 'text/csv' });
        const url = URL.createObjectURL(blob);
        const a = document.createElement('a');
        a.href = url;
        a.download = `magnet_keywords_${Date.now()}.csv`;
        a.click();
        URL.revokeObjectURL(url);

        this.showToast('CSV exported successfully!', 'success');
    }

    /**
     * Save results to backend
     */
    async saveToBackend(results) {
        try {
            // Get auth token from chrome.storage
            let authToken = null;
            if (typeof chrome !== 'undefined' && chrome.storage) {
                const data = await new Promise(resolve => {
                    chrome.storage.local.get(['authToken'], resolve);
                });
                authToken = data.authToken;
            }

            if (!authToken) {
                console.warn('[Magnet] No auth token found - cannot save to backend');
                this.showToast('⚠️ Please login to save results', 'warning');
                return;
            }

            const payload = {
                marketplace: results.marketplace,
                seed_keyword: results.seed_keyword,
                name: `Magnet: ${results.seed_keyword}`,
                duration_seconds: results.duration_seconds,
                keywords: results.keywords.map(kw => ({
                    keyword: kw.keyword,
                    search_volume: kw.search_volume || 0,
                    magnet_iq_score: kw.magnet_iq_score || 0,
                    competing_products: kw.competing_products || 0,
                    title_density: kw.title_density || 0,
                    word_count: kw.word_count || 1,
                    cpr_8day: kw.cpr_8day || 0,
                    cpr_total: kw.cpr_total || 0,
                    keyword_sales: kw.keyword_sales || 0,
                    avg_price: kw.avg_price || 0,
                    avg_reviews: kw.avg_reviews || 0,
                    sponsored_count: kw.sponsored_count || 0,
                    match_type: kw.match_type || 'autocomplete',
                    relevance_score: kw.relevance_score || 0
                }))
            };

            console.log('[Magnet] Saving to backend with auth token...');

            const response = await fetch('http://127.0.0.1:8000/api/magnet/analyze', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                    'Authorization': `Bearer ${authToken}`
                },
                body: JSON.stringify(payload)
            });

            const data = await response.json();

            if (response.ok && data.success) {
                console.log('[Magnet] Analysis saved to backend:', data.analysis_id);
                this.showToast('✅ Analysis saved to dashboard!', 'success');
            } else {
                console.error('[Magnet] Backend save failed:', data);
                this.showToast('⚠️ Could not save to dashboard: ' + (data.message || 'Unknown error'), 'warning');
            }
        } catch (error) {
            console.error('[Magnet] Failed to save to backend:', error);
            this.showToast('⚠️ Backend unavailable - results not saved', 'warning');
        }
    }

    /**
     * Show error message
     */
    showError(message) {
        const progress = this.panel?.querySelector('#magnet-progress');
        if (progress) {
            progress.innerHTML = `
                <div style="font-size: 60px; margin-bottom: 20px;">❌</div>
                <div style="color: #ef4444; font-size: 18px; font-weight: 600;">Analysis Failed</div>
                <div style="color: #9ca3af; font-size: 14px; margin-top: 10px;">${message}</div>
                <button onclick="document.getElementById('magnet-backdrop')?.click()" style="
                    margin-top: 20px;
                    background: #374151;
                    border: none;
                    color: #e5e7eb;
                    padding: 10px 24px;
                    border-radius: 8px;
                    cursor: pointer;
                    font-size: 14px;
                ">Close</button>
            `;
            this.translateDOM(progress);
        }
    }

    /**
     * Show toast message
     */
    showToast(message, type = 'info') {
        const toast = document.createElement('div');
        const colors = {
            success: '#10b981',
            warning: '#f59e0b',
            error: '#ef4444',
            info: '#6366f1'
        };

        toast.style.cssText = `
            position: fixed;
            bottom: 24px;
            left: 50%;
            transform: translateX(-50%);
            background: ${colors[type]};
            color: white;
            padding: 12px 24px;
            border-radius: 8px;
            font-size: 14px;
            font-weight: 600;
            z-index: 99999999;
            animation: fadeInUp 0.3s ease;
            font-family: 'Inter', system-ui, sans-serif;
        `;
        toast.textContent = message;

        document.body.appendChild(toast);
        setTimeout(() => toast.remove(), 3000);
    }

    // Helper methods
    getMarketplaceFlag(hostname) {
        // Normalize hostname (remove www.)
        const normalizedHost = (hostname || '').replace('www.', '');
        const flags = {
            'amazon.eg': '🇪🇬',
            'amazon.com': '🇺🇸',
            'amazon.co.uk': '🇬🇧',
            'amazon.de': '🇩🇪',
            'amazon.ae': '🇦🇪',
            'amazon.sa': '🇸🇦'
        };
        // Check for partial match (e.g., "www.amazon.eg" contains "amazon.eg")
        for (const [domain, flag] of Object.entries(flags)) {
            if (normalizedHost.includes(domain)) return flag;
        }
        return '🌐';
    }

    getMarketplaceCurrency(hostname) {
        // Normalize hostname (remove www.)
        const normalizedHost = (hostname || '').replace('www.', '');
        const currencies = {
            'amazon.eg': 'EGP',
            'amazon.com': 'USD',
            'amazon.co.uk': 'GBP',
            'amazon.de': 'EUR',
            'amazon.ae': 'AED',
            'amazon.sa': 'SAR'
        };
        // Check for partial match
        for (const [domain, currency] of Object.entries(currencies)) {
            if (normalizedHost.includes(domain)) return currency;
        }
        return 'USD';
    }

    getDashboardUrl() {
        // Get the API URL from ApiClient if available
        return 'http://127.0.0.1:8000/magnet';
    }

    formatNumber(num) {
        if (num >= 1000000) return (num / 1000000).toFixed(1) + 'M';
        if (num >= 1000) return (num / 1000).toFixed(1) + 'K';
        return Math.round(num || 0).toString();
    }

    calcAvg(arr, key) {
        if (!arr.length) return 0;
        const sum = arr.reduce((acc, item) => acc + (item[key] || 0), 0);
        return Math.round(sum / arr.length);
    }

    calcSum(arr, key) {
        return arr.reduce((acc, item) => acc + (item[key] || 0), 0);
    }

    calcMax(arr, key) {
        if (!arr.length) return 0;
        return Math.max(...arr.map(item => item[key] || 0)).toFixed(1);
    }
}

// Auto-initialize on Amazon pages
if (window.location.hostname.includes('amazon.')) {
    window.magnetUI = new MagnetUI();

    // Initialize after DOM is ready
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', () => window.magnetUI.init());
    } else {
        window.magnetUI.init();
    }
}
