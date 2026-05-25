// Content Script - Runs on Amazon product pages
console.log('Amazon Product Analyzer - Content Script Loaded');

// Robust language check to detect if the page is rendered in Arabic
function isPageArabic() {
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

// Arabic translation helper
function t(key) {
    if (!isPageArabic()) return key;

    const translations = {
        'Amazon Analyzer': 'محلل أمازون',
        'Product Intelligence': 'ذكاء المنتجات',
        'Analyze Product': 'تحليل المنتج',
        'Reverse ASIN': 'الأسين العكسي',
        'FBA Calculator': 'حاسبة أرباح FBA',
        'Market Analysis': 'تحليل السوق',
        'Keyword Magnet': 'مغناطيس الكلمات المفتاحية',
        'Analyze market or discover keyword ideas': 'حلل السوق أو اكتشف أفكار الكلمات المفتاحية',
        'Preparing keyword discovery...': 'جاري تجهيز اكتشاف الكلمات المفتاحية...',
        'Analyzing product...': 'جاري تحليل المنتج...',
        'Could not detect product ASIN': 'تعذر اكتشاف الرقم التعريفي (ASIN) للمنتج',
        'Analysis failed: ': 'فشل التحليل: ',
        'Loading calculator data...': 'جاري تحميل بيانات الحاسبة...',
        'Calculator panel not found': 'لم يتم العثور على لوحة الحاسبة',
        'FBA Profit Calculator': 'حاسبة أرباح FBA',
        'Your Costs': 'تكاليفك',
        'Product Cost / Unit': 'تكلفة المنتج / الوحدة',
        'Shipping Cost / Unit': 'تكلفة الشحن / الوحدة',
        '(Inbound to FBA)': '(الوارد إلى FBA)',
        'CPC (Ad Cost/Unit)': 'تكلفة النقرة (إعلانات/وحدة)',
        'Taxes': 'الضرائب',
        'Product Data (Editable)': 'بيانات المنتج (قابلة للتعديل)',
        'Selling Price': 'سعر البيع',
        'Est. Monthly Sales': 'المبيعات الشهرية المقدرة',
        'units': 'وحدات',
        'Amazon Fees (Auto)': 'رسوم أمازون (تلقائي)',
        'Weight': 'الوزن',
        'Dimensions': 'الأبعاد',
        'Fulfillment Fee': 'رسوم الشحن (Fulfillment)',
        'Storage Fee (Est. / mo)': 'رسوم التخزين (المقدرة / الشهر)',
        'Referral Fee (15%)': 'رسوم الإحالة (15%)',
        'Profit Analysis': 'تحليل الأرباح',
        'Net Profit/Unit': 'صافي الربح/الوحدة',
        'Net Margin': 'هامش الربح الصافي',
        'ROI': 'عائد الاستثمار (ROI)',
        'Monthly Profit': 'الربح الشهري',
        'Adjust inputs above to see live profit calculations': 'قم بتعديل المدخلات أعلاه لرؤية حسابات الأرباح المباشرة',
        'Close Calculator': 'إغلاق الحاسبة'
    };

    return translations[key] || key;
}

// Detect if we're on an Amazon product page
function isProductPage() {
    const url = window.location.href;
    return url.includes('/dp/') || url.includes('/gp/product/');
}

// Detect if we're on an Amazon search results page
function isSearchPage() {
    const url = window.location.href;
    return url.includes('/s?') || url.includes('/s/?');
}

// Initialize on product pages
if (isProductPage()) {
    console.log('Product page detected');
    initializeAnalyzer();
}

// Initialize on search pages
if (isSearchPage()) {
    console.log('Search results page detected');
    initializeSearchAnalyzer();
}

function initializeAnalyzer() {
    console.log('Initializing analyzer...');
    injectAnalyzerButton();
}

function checkAuthAndExecute(callback) {
    chrome.runtime.sendMessage({ action: 'getAuth' }, (response) => {
        if (chrome.runtime.lastError) {
            console.error('Chrome runtime error:', chrome.runtime.lastError);
            showAuthAlert('An extension error occurred. Please reload the page.');
            return;
        }

        if (response && response.authenticated) {
            callback();
        } else {
            showAuthAlert();
        }
    });
}

function showAuthAlert(message = '') {
    const resultDiv = document.getElementById('sv-product-result');
    if (!resultDiv) return;

    const isArabic = isPageArabic();
    const alertTitle = isArabic ? '🔑 تسجيل الدخول مطلوب' : '🔑 Authentication Required';
    const alertMsg = message || (isArabic 
        ? 'يرجى النقر فوق أيقونة الإضافة في شريط أدوات المتصفح لتسجيل الدخول أو إنشاء حساب وتفعيل أدوات التحليل.' 
        : 'Please click the extension icon in your browser toolbar to log in or register and unlock the analyzer tools.');

    resultDiv.innerHTML = `
        <div style="
            padding: 28px; 
            background: linear-gradient(135deg, rgba(30, 41, 59, 0.95) 0%, rgba(15, 23, 42, 0.98) 100%); 
            backdrop-filter: blur(12px);
            border-radius: 16px; 
            border: 1px solid #f59e0b; 
            margin: 20px;
            display: flex;
            flex-direction: column;
            gap: 14px;
            align-items: center;
            text-align: center;
            box-shadow: 0 10px 25px rgba(0,0,0,0.3);
        ">
            <div style="font-size: 36px; filter: drop-shadow(0 0 10px rgba(245, 158, 11, 0.4));">🔐</div>
            <div style="font-weight: 800; font-size: 18px; color: #f8fafc; letter-spacing: 0.5px;">${alertTitle}</div>
            <div style="font-size: 13px; color: #cbd5e1; line-height: 1.6; max-width: 480px;">${alertMsg}</div>
        </div>
    `;
    resultDiv.style.display = 'block';
    
    // Smooth scroll to results
    resultDiv.scrollIntoView({ behavior: 'smooth', block: 'nearest' });
}

function injectAnalyzerButton() {
    // Check if panel already exists
    if (document.getElementById('sv-product-panel-container')) {
        return;
    }

    const isArabic = isPageArabic();

    // Target elements for injection (in order of preference)
    const targets = [
        document.getElementById('above-dp-container'),
        document.querySelector('[data-feature-name="desktop-breadcrumbs"]'),
        document.getElementById('wayfinding-breadcrumbs_feature_div'),
        document.getElementById('titleSection')
    ];

    let target = null;
    for (const t of targets) {
        if (t) {
            target = t;
            break;
        }
    }

    if (!target) {
        console.log('No suitable target for inline panel, using floating fallback');
        injectFloatingButtons();
        return;
    }

    // Create Inline Panel
    const container = document.createElement('div');
    container.id = 'sv-product-panel-container';
    container.style.marginBottom = '20px';
    container.style.marginTop = '20px';
    container.style.fontFamily = "'Inter', system-ui, -apple-system, sans-serif";

    // Panel HTML with professional glassmorphism design and Arabic support
    container.innerHTML = `
        <div id="sv-product-panel" style="
            background: linear-gradient(135deg, #0f172a 0%, #1e293b 100%); 
            border-radius: 16px; 
            box-shadow: 0 10px 30px rgba(0,0,0,0.25), inset 0 1px 0 rgba(255,255,255,0.05); 
            color: white; 
            border: 1px solid #334155;
            overflow: hidden;
            direction: ${isArabic ? 'rtl' : 'ltr'};
        ">
            <!-- Header / Toolbar -->
            <div style="
                padding: 16px 24px; 
                display: flex; 
                align-items: center; 
                justify-content: space-between; 
                border-bottom: 1px solid #334155;
                flex-wrap: wrap;
                gap: 12px;
            ">
                <div style="display: flex; align-items: center; gap: 14px;">
                    <div style="
                        font-weight: 800; 
                        font-size: 16px; 
                        color: #f59e0b; 
                        display: flex; 
                        align-items: center; 
                        gap: 8px;
                        letter-spacing: 0.5px;
                    ">
                        <span style="font-size: 20px;">🔍</span> ${t('Amazon Analyzer')}
                    </div>
                    <div style="width: 1px; height: 20px; background: #475569;"></div>
                    <div style="font-size: 13px; color: #94a3b8; font-weight: 500;">${t('Product Intelligence')}</div>
                </div>
                <div style="display: flex; gap: 12px; flex-wrap: wrap;">
                    <button id="sv-btn-analyze" style="
                        background: linear-gradient(135deg, #f59e0b, #d97706); 
                        color: white; 
                        border: none; 
                        padding: 8px 18px; 
                        border-radius: 12px; 
                        font-size: 13px; 
                        font-weight: 700; 
                        cursor: pointer; 
                        display: flex; 
                        align-items: center; 
                        gap: 8px; 
                        box-shadow: 0 4px 12px rgba(245, 158, 11, 0.35);
                        transition: all 0.2s ease-in-out;
                        white-space: nowrap;
                        flex-shrink: 0;
                        min-width: max-content;">
                        <span>🔍</span> ${t('Analyze Product')}
                    </button>
                    <button id="sv-btn-reverse" style="
                        background: linear-gradient(135deg, #6366f1, #4f46e5); 
                        color: white; 
                        border: none; 
                        padding: 8px 18px; 
                        border-radius: 12px; 
                        font-size: 13px; 
                        font-weight: 700; 
                        cursor: pointer; 
                        display: flex; 
                        align-items: center; 
                        gap: 8px; 
                        box-shadow: 0 4px 12px rgba(99, 102, 241, 0.35);
                        transition: all 0.2s ease-in-out;
                        white-space: nowrap;
                        flex-shrink: 0;
                        min-width: max-content;">
                        <span>🔑</span> ${t('Reverse ASIN')}
                    </button>
                    <button id="sv-btn-calculator" style="
                        background: linear-gradient(135deg, #10b981, #059669); 
                        color: white; 
                        border: none; 
                        padding: 8px 18px; 
                        border-radius: 12px; 
                        font-size: 13px; 
                        font-weight: 700; 
                        cursor: pointer; 
                        display: flex; 
                        align-items: center; 
                        gap: 8px; 
                        box-shadow: 0 4px 12px rgba(16, 185, 129, 0.35);
                        transition: all 0.2s ease-in-out;
                        white-space: nowrap;
                        flex-shrink: 0;
                        min-width: max-content;">
                        <span>💰</span> ${t('FBA Calculator')}
                    </button>
                </div>
            </div>
            
            <!-- Result Area (Hidden initially) -->
            <div id="sv-product-result" style="display: none; padding: 0;">
                <!-- Content will be injected here -->
            </div>
        </div>
    `;

    // Insert after target
    target.insertAdjacentElement('afterend', container);
    console.log('Inline product panel injected');

    // Add Event Listeners for smooth transitions
    const setupButtonEffects = (id, hoverScale = 'scale(1.03)', shadow = '') => {
        const btn = document.getElementById(id);
        if (btn) {
            btn.addEventListener('mouseenter', () => {
                btn.style.transform = hoverScale;
                btn.style.filter = 'brightness(1.1)';
            });
            btn.addEventListener('mouseleave', () => {
                btn.style.transform = 'scale(1)';
                btn.style.filter = 'none';
            });
        }
    };

    setupButtonEffects('sv-btn-analyze');
    setupButtonEffects('sv-btn-reverse');
    setupButtonEffects('sv-btn-calculator');

    document.getElementById('sv-btn-analyze').addEventListener('click', () => {
        checkAuthAndExecute(() => analyzeCurrentProduct('full'));
    });

    document.getElementById('sv-btn-reverse').addEventListener('click', () => {
        checkAuthAndExecute(() => analyzeCurrentProduct('keywords'));
    });

    document.getElementById('sv-btn-calculator').addEventListener('click', () => {
        checkAuthAndExecute(() => openFBACalculator());
    });
}

function injectFloatingButtons() {
    // Original floating button logic as fallback
    if (document.getElementById('amazon-analyzer-btn')) return;
    const container = document.createElement('div');
    container.id = 'amazon-analyzer-btn-container';

    // ... (rest of old floating button code could be here, but simpler to just inline basic fallback) ...
    // For brevity of replacement, I'll just skip the complex CSS for fallback or implement simple one

    const btn = document.createElement('div');
    btn.textContent = '🔍 Analyze';
    btn.style.cssText = 'position:fixed;bottom:20px;right:20px;background:#232f3e;color:white;padding:12px;border-radius:50px;cursor:pointer;z-index:9999;box-shadow:0 4px 10px rgba(0,0,0,0.3);font-weight:bold;';
    btn.onclick = () => analyzeCurrentProduct('full');
    document.body.appendChild(btn);
}

async function analyzeCurrentProduct(mode = 'full') {
    console.log(`Starting product analysis in ${mode} mode...`);

    // Show loading state
    const loadingDiv = document.createElement('div');
    loadingDiv.id = 'analyzer-loading';
    loadingDiv.innerHTML = `
        <div class="analyzer-loading-content">
            <div class="analyzer-spinner"></div>
            <div>${mode === 'keywords' ? t('Preparing keyword discovery...') : t('Analyzing product...')}</div>
        </div>
    `;
    document.body.appendChild(loadingDiv);

    try {
        // Always scrape current page data first (Local Data)
        const localScraper = new DataScraper(document);
        const localData = localScraper.extractProductData();
        let productData = localData;

        const currentUrl = window.location.href;
        const isArabic = document.documentElement.lang.startsWith('ar') || currentUrl.includes('language=ar_');

        // If Arabic, try to fetch English version
        if (isArabic && !currentUrl.includes('language=en_')) {
            console.log('Arabic page detected. Attempting to fetch English version...');
            try {
                // Construct English URL
                let englishUrl = currentUrl;
                if (englishUrl.includes('language=')) {
                    englishUrl = englishUrl.replace(/language=ar_[A-Z]{2}/, 'language=en_AE');
                } else {
                    const separator = englishUrl.includes('?') ? '&' : '?';
                    englishUrl = `${englishUrl}${separator}language=en_AE`;
                }

                console.log(`Fetching English version from: ${englishUrl}`);

                const response = await fetch(englishUrl);
                if (response.ok) {
                    const text = await response.text();
                    const parser = new DOMParser();
                    const doc = parser.parseFromString(text, 'text/html');

                    // Scrape the English DOM
                    const englishScraper = new DataScraper(doc);
                    const englishData = englishScraper.extractProductData();

                    // Merge strategies:
                    productData = {
                        ...englishData,
                        // Preserve Arabic fields
                        title_ar: localData.title,
                        category_ar: localData.category,
                        categoryPath_ar: localData.categoryPath,
                        availability_ar: localData.availability
                    };

                    console.log('Successfully scraped and merged English + Arabic data');
                } else {
                    console.warn(`English fetch failed status: ${response.status}`);
                }
            } catch (err) {
                console.warn('Failed to fetch English version, falling back to local Arabic data:', err);
            }
        }

        console.log('Final Product Data:', productData);

        if (!productData.asin) {
            document.getElementById('analyzer-loading')?.remove();
            alert(t('Could not detect product ASIN'));
            return;
        }

        const marketplace = window.location.hostname.includes('.eg') ? 'amazon.eg' : 'amazon.com';

        // *** BACKEND API CALL FOR CALCULATIONS ***
        console.log('Sending data to backend for analysis...');

        const apiPayload = {
            asin: productData.asin,
            marketplace: marketplace,
            title: productData.title || '',
            price: parseFloat(productData.price) || 0,
            currency: productData.currency || (marketplace === 'amazon.eg' ? 'EGP' : 'USD'),
            bsr: productData.bsr ? parseInt(productData.bsr.rank || productData.bsr) : null,
            category: productData.category || (productData.bsr ? productData.bsr.category : 'default'),
            reviews_count: parseInt(productData.reviewCount) || 0, // Fixed: reviewCount not reviewsCount
            rating: parseFloat(productData.rating) || 0,
            is_fba: productData.isFBA === true,
            weight_kg: productData.weight?.kg || 0.5,
            monthly_badge: parseInt(productData.monthlySales) || null,
            cogs: null // Let backend use default (30% of price)
        };

        console.log('API Payload:', apiPayload); // Debug log

        let analysis;

        // Skip backend calculations if we only need keywords
        if (mode === 'keywords') {
            console.log('Skipping backend calculations for keywords-only mode');
            analysis = {
                ...productData,
                marketplace: marketplace,
                calculatedBy: 'client-keywords-only'
            };
        } else {
            try {
                const apiResponse = await fetch('http://127.0.0.1:8000/api/analyze', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'Accept': 'application/json'
                    },
                    body: JSON.stringify(apiPayload)
                });

                if (apiResponse.ok) {
                    const apiData = await apiResponse.json();
                    console.log('Backend analysis received:', apiData);

                    // Merge API response with scraped product data for display
                    analysis = {
                        ...productData,
                        ...apiData,
                        // Map backend schema to UI expectation
                        revenue: {
                            monthly: apiData.profit.monthly_revenue,
                            annual: apiData.profit.monthly_revenue * 12
                        },
                        fees: {
                            ...apiData.fees,
                            fba: apiData.fees.fulfillment,      // Map fulfillment to fba
                            storage: 0.20,                      // Default storage estimate (safe placeholder)
                            isEstimatedStorage: true            // Flag for placeholder
                        },
                        profit: {
                            ...apiData.profit,
                            margin: apiData.profit.margin_percent, // Map margin_percent to margin
                            perUnit: apiData.profit.per_unit,      // Map per_unit to perUnit
                            roi: apiData.profit.roi_percent        // Map roi_percent to roi
                        },
                        // Ensure key fields are preserved
                        title: productData.title,
                        title_ar: productData.title_ar,
                        asin: productData.asin,
                        marketplace: marketplace,
                        calculatedBy: 'backend'
                    };
                } else {
                    console.warn('Backend API failed, falling back to local calculation');
                    throw new Error('Backend unavailable');
                }
            } catch (apiError) {
                console.warn('API error, using local calculation:', apiError.message);

                // Fallback to local calculation
                const engine = new IntelligenceEngine(marketplace);
                analysis = engine.analyze(productData);
                analysis.calculatedBy = 'local';
            }
        }


        console.log('Analysis complete:', analysis);

        // Remove loading indicator
        document.getElementById('analyzer-loading')?.remove();

        // Display results
        displayResults(analysis, mode);
    } catch (error) {
        document.getElementById('analyzer-loading')?.remove();
        console.error('Analysis error:', error);
        alert('Analysis failed: ' + error.message);
    }
}

// ... legacy functions ...

function displayResults(analysis, mode = 'full') {
    // Check if we have an inline result container from the new product panel
    const inlineContainer = document.getElementById('sv-product-result');

    if (inlineContainer) {
        // Use inline rendering
        try {
            const ui = new ShadowUI();
            ui.renderToContainer(inlineContainer, analysis, mode);
            console.log('Results displayed in inline panel');
            return;
        } catch (error) {
            console.error('Inline rendering error:', error);
            // Fallthrough to floating if inline fails
        }
    }

    // Floating UI Fallback
    try {
        const ui = new ShadowUI();
        ui.display(analysis, mode);
    } catch (error) {
        console.error('Shadow UI error:', error);
        // Fallback to alert
        alert('Analysis complete! Check console for details.');
        console.log('Analysis results:', analysis);
    }
}

// ==================== FBA CALCULATOR ====================

async function openFBACalculator() {
    console.log('Opening FBA Calculator...');

    const resultContainer = document.getElementById('sv-product-result');
    if (!resultContainer) {
        alert(t('Calculator panel not found'));
        return;
    }

    const isArabic = document.documentElement.lang.startsWith('ar') || window.location.href.includes('language=ar_');

    // Show loading
    resultContainer.style.display = 'block';
    resultContainer.innerHTML = `
        <div style="padding: 30px; text-align: center; color: #f59e0b;">
            <div style="font-size: 32px; margin-bottom: 12px; animation: pulse 1.5s infinite;">⏳</div>
            <div>${t('Loading calculator data...')}</div>
        </div>
    `;

    try {
        // Scrape product data
        const scraper = new DataScraper(document);
        const productData = scraper.extractProductData();

        // Detect marketplace and currency
        const url = window.location.href;
        const isEgypt = url.includes('amazon.eg');
        const currency = isEgypt ? 'EGP' : 'USD';
        const marketplace = isEgypt ? 'amazon.eg' : 'amazon.com';

        // Get fees from backend
        let fulfillmentFee = 0;
        let referralFee = 0;
        let estimatedSales = 30;

        try {
            const apiPayload = {
                marketplace: marketplace,
                asin: productData.asin,
                price: parseFloat(productData.price) || 0,
                currency: currency,
                category: productData.category || 'default',
                weight_kg: productData.weight?.kg || 0.5,
                is_fba: true
            };

            const response = await fetch('http://127.0.0.1:8000/api/analyze', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json', 'Accept': 'application/json' },
                body: JSON.stringify(apiPayload)
            });

            if (response.ok) {
                const data = await response.json();
                fulfillmentFee = data.fees?.fulfillment || 0;
                referralFee = data.fees?.referral || 0;
                estimatedSales = data.sales?.monthly || 30;
            }
        } catch (e) {
            console.warn('Could not fetch fees from backend:', e.message);
            const price = parseFloat(productData.price) || 100;
            referralFee = price * 0.15;
            fulfillmentFee = isEgypt ? 25 : 3.5;
        }

        const price = parseFloat(productData.price) || 0;
        const weight = productData.weight?.kg || 0.5;
        const dimensions = productData.dimensions || 'N/A';
        const defaultCOGS = (price * 0.25).toFixed(2);

        // Render calculator UI with premium Slate theme
        resultContainer.innerHTML = `
            <div style="
                background: linear-gradient(135deg, #0f172a 0%, #1e293b 100%); 
                padding: 24px; 
                color: white; 
                font-family: 'Inter', system-ui, -apple-system, sans-serif;
                border-radius: 12px;
                border: 1px solid #334155;
                box-shadow: inset 0 1px 0 rgba(255,255,255,0.05);
                direction: ${isArabic ? 'rtl' : 'ltr'};
            ">
                <h3 style="margin: 0 0 20px 0; color: #10b981; font-size: 16px; display: flex; align-items: center; gap: 8px; font-weight: 800;">
                    💰 ${t('FBA Profit Calculator')} <span style="font-size: 12px; color: #94a3b8; font-weight: normal;">(${currency})</span>
                </h3>
                
                <!-- Two Column Layout -->
                <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(280px, 1fr)); gap: 24px;">
                    
                    <!-- Left Column: Inputs -->
                    <div>
                        <div style="font-size: 11px; color: #94a3b8; margin-bottom: 14px; text-transform: uppercase; letter-spacing: 1px; font-weight: 700;">📝 ${t('Your Costs')}</div>
                        
                        <div style="display: grid; gap: 14px;">
                            <div style="display: flex; justify-content: space-between; align-items: center;">
                                <label style="font-size: 13px; color: #cbd5e1; font-weight: 500;">${t('Product Cost / Unit')}</label>
                                <div style="display: flex; align-items: center; gap: 6px;">
                                    <input type="number" id="calc-product-cost" value="${defaultCOGS}" step="0.01" min="0" style="width: 110px; padding: 8px 12px; border: 1px solid #475569; border-radius: 8px; background: #0f172a; color: white; font-size: 13px; text-align: right; outline: none;">
                                    <span style="font-size: 11px; color: #94a3b8; font-weight: 600;">${currency}</span>
                                </div>
                            </div>
                            
                            <div style="display: flex; justify-content: space-between; align-items: center;">
                                <div style="display: flex; flex-direction: column;">
                                    <label style="font-size: 13px; color: #cbd5e1; font-weight: 500;">${t('Shipping Cost / Unit')}</label>
                                    <span style="font-size: 9px; color: #94a3b8; font-weight: 500;">${t('(Inbound to FBA)')}</span>
                                </div>
                                <div style="display: flex; align-items: center; gap: 6px;">
                                    <input type="number" id="calc-shipping-cost" value="0" step="0.01" min="0" style="width: 110px; padding: 8px 12px; border: 1px solid #475569; border-radius: 8px; background: #0f172a; color: white; font-size: 13px; text-align: right; outline: none;">
                                    <span style="font-size: 11px; color: #94a3b8; font-weight: 600;">${currency}</span>
                                </div>
                            </div>
                            
                            <div style="display: flex; justify-content: space-between; align-items: center;">
                                <label style="font-size: 13px; color: #cbd5e1; font-weight: 500;">${t('CPC (Ad Cost/Unit)')}</label>
                                <div style="display: flex; align-items: center; gap: 6px;">
                                    <input type="number" id="calc-cpc-cost" value="0" step="0.01" min="0" style="width: 110px; padding: 8px 12px; border: 1px solid #475569; border-radius: 8px; background: #0f172a; color: white; font-size: 13px; text-align: right; outline: none;">
                                    <span style="font-size: 11px; color: #94a3b8; font-weight: 600;">${currency}</span>
                                </div>
                            </div>
                            
                            <div style="display: flex; justify-content: space-between; align-items: center;">
                                <label style="font-size: 13px; color: #cbd5e1; font-weight: 500;">${t('Taxes')}</label>
                                <div style="display: flex; align-items: center; gap: 6px;">
                                    <input type="number" id="calc-tax-percent" value="${isEgypt ? 14 : 0}" step="0.1" min="0" max="100" style="width: 80px; padding: 8px 12px; border: 1px solid #475569; border-radius: 8px; background: #0f172a; color: white; font-size: 13px; text-align: right; outline: none;">
                                    <span style="font-size: 11px; color: #94a3b8; font-weight: 600;">%</span>
                                </div>
                            </div>
                        </div>
                        
                        <div style="font-size: 11px; color: #94a3b8; margin: 24px 0 14px 0; text-transform: uppercase; letter-spacing: 1px; font-weight: 700;">📦 ${t('Product Data (Editable)')}</div>
                        
                        <div style="display: grid; gap: 14px;">
                            <div style="display: flex; justify-content: space-between; align-items: center;">
                                <label style="font-size: 13px; color: #cbd5e1; font-weight: 500;">${t('Selling Price')}</label>
                                <div style="display: flex; align-items: center; gap: 6px;">
                                    <input type="number" id="calc-price" value="${price.toFixed(2)}" step="0.01" min="0" style="width: 110px; padding: 8px 12px; border: 1px solid #f59e0b; border-radius: 8px; background: #0f172a; color: #f59e0b; font-size: 13px; text-align: right; font-weight: 700; outline: none;">
                                    <span style="font-size: 11px; color: #94a3b8; font-weight: 600;">${currency}</span>
                                </div>
                            </div>
                            
                            <div style="display: flex; justify-content: space-between; align-items: center;">
                                <label style="font-size: 13px; color: #cbd5e1; font-weight: 500;">${t('Est. Monthly Sales')}</label>
                                <div style="display: flex; align-items: center; gap: 6px;">
                                    <input type="number" id="calc-monthly-sales" value="${estimatedSales}" step="1" min="0" style="width: 110px; padding: 8px 12px; border: 1px solid #f59e0b; border-radius: 8px; background: #0f172a; color: #f59e0b; font-size: 13px; text-align: right; font-weight: 700; outline: none;">
                                    <span style="font-size: 11px; color: #94a3b8; font-weight: 600;">${t('units')}</span>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Right Column: Auto Data & Results -->
                    <div>
                        <div style="font-size: 11px; color: #94a3b8; margin-bottom: 14px; text-transform: uppercase; letter-spacing: 1px; font-weight: 700;">📊 ${t('Amazon Fees (Auto)')}</div>
                        
                        <div style="background: rgba(30, 41, 59, 0.5); border-radius: 12px; padding: 16px; margin-bottom: 20px; border: 1px solid #334155;">
                            <div style="display: grid; gap: 10px; font-size: 13px;">
                                <div style="display: flex; justify-content: space-between;">
                                    <span style="color: #94a3b8; font-weight: 500;">${t('Weight')}</span>
                                    <span style="color: #f8fafc; font-weight: 600;">${weight} kg</span>
                                </div>
                                <div style="display: flex; justify-content: space-between;">
                                    <span style="color: #94a3b8; font-weight: 500;">${t('Dimensions')}</span>
                                    <span style="color: #f8fafc; font-weight: 600; text-align: end; max-width: 180px;">${dimensions}</span>
                                </div>
                                <div style="display: flex; justify-content: space-between; border-top: 1px solid #334155; padding-top: 10px; margin-top: 4px;">
                                    <span style="color: #94a3b8; font-weight: 500;">${t('Fulfillment Fee')}</span>
                                    <span id="calc-fulfillment-fee" style="color: #f97316; font-weight: 700;">${fulfillmentFee.toFixed(2)} ${currency}</span>
                                </div>
                                <div style="display: flex; justify-content: space-between;">
                                    <span style="color: #94a3b8; font-weight: 500;">${t('Storage Fee (Est. / mo)')}</span>
                                    <span id="calc-storage-fee" style="color: #f97316; font-weight: 700;">0.00 ${currency}</span>
                                </div>
                                <div style="display: flex; justify-content: space-between;">
                                    <span style="color: #94a3b8; font-weight: 500;">${t('Referral Fee (15%)')}</span>
                                    <span id="calc-referral-fee" style="color: #f97316; font-weight: 700;">${referralFee.toFixed(2)} ${currency}</span>
                                </div>
                            </div>
                        </div>
                        
                        <div style="font-size: 11px; color: #94a3b8; margin-bottom: 14px; text-transform: uppercase; letter-spacing: 1px; font-weight: 700;">💵 ${t('Profit Analysis')}</div>
                        
                        <div style="background: linear-gradient(135deg, rgba(6, 78, 59, 0.45) 0%, rgba(2, 44, 34, 0.45) 100%); border-radius: 12px; padding: 20px; border: 1px solid #059669; box-shadow: 0 4px 20px rgba(16, 185, 129, 0.15);">
                            <div style="display: grid; gap: 14px;">
                                <div style="display: flex; justify-content: space-between; align-items: center;">
                                    <span style="color: #cbd5e1; font-size: 13px; font-weight: 500;">${t('Net Profit/Unit')}</span>
                                    <span id="calc-net-profit" style="color: #10b981; font-size: 20px; font-weight: 800;">0.00 ${currency}</span>
                                </div>
                                <div style="display: flex; justify-content: space-between; align-items: center;">
                                    <span style="color: #cbd5e1; font-size: 13px; font-weight: 500;">${t('Net Margin')}</span>
                                    <span id="calc-net-margin" style="color: #10b981; font-size: 20px; font-weight: 800;">0%</span>
                                </div>
                                <div style="display: flex; justify-content: space-between; align-items: center;">
                                    <span style="color: #cbd5e1; font-size: 13px; font-weight: 500;">${t('ROI')}</span>
                                    <span id="calc-roi" style="color: #10b981; font-size: 20px; font-weight: 800;">0%</span>
                                </div>
                                <div style="display: flex; justify-content: space-between; align-items: center; border-top: 1px solid #047857; padding-top: 14px; margin-top: 4px;">
                                    <span style="color: #cbd5e1; font-size: 13px; font-weight: 500;">${t('Monthly Profit')}</span>
                                    <span id="calc-monthly-profit" style="color: #f59e0b; font-size: 22px; font-weight: 800;">0 ${currency}</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                
                <!-- Bottom Bar -->
                <div style="margin-top: 24px; padding-top: 16px; border-top: 1px solid #334155; display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 12px;">
                    <div style="font-size: 12px; color: #94a3b8; font-weight: 500; display: flex; align-items: center; gap: 4px;">
                        💡 ${t('Adjust inputs above to see live profit calculations')}
                    </div>
                    <button id="calc-close-btn" style="background: #334155; color: white; border: none; padding: 8px 18px; border-radius: 8px; cursor: pointer; font-size: 13px; font-weight: 700; transition: all 0.2s;" onmouseenter="this.style.background='#475569'" onmouseleave="this.style.background='#334155'">
                        ${t('Close Calculator')}
                    </button>
                </div>
            </div>
        `;

        // Store data for recalculation
        const calcData = {
            marketplace: marketplace,
            currency: currency,
            fulfillmentFee: fulfillmentFee,
            category: productData.category || 'default',
            weightKg: weight
        };

        // Debounce timer
        let debounceTimer = null;

        // Calculate function - calls backend API
        const recalculate = async () => {
            const sellingPrice = parseFloat(document.getElementById('calc-price').value) || 0;
            const productCost = parseFloat(document.getElementById('calc-product-cost').value) || 0;
            const shippingCost = parseFloat(document.getElementById('calc-shipping-cost').value) || 0;
            const cpcCost = parseFloat(document.getElementById('calc-cpc-cost').value) || 0;
            const taxPercent = parseFloat(document.getElementById('calc-tax-percent').value) || 0;
            const monthlySales = parseInt(document.getElementById('calc-monthly-sales').value) || 0;

            try {
                const response = await fetch('http://127.0.0.1:8000/api/fees/calculate-profit', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json', 'Accept': 'application/json' },
                    body: JSON.stringify({
                        marketplace: calcData.marketplace,
                        selling_price: sellingPrice,
                        product_cost: productCost,
                        shipping_cost: shippingCost,
                        cpc_cost: cpcCost,
                        tax_percent: taxPercent,
                        monthly_sales: monthlySales,
                        weight_kg: calcData.weightKg,
                        category: calcData.category,
                        is_fba: true
                    })
                });

                if (response.ok) {
                    const data = await response.json();

                    // Update fees display
                    document.getElementById('calc-referral-fee').textContent = `${data.fees.referral_fee} ${calcData.currency}`;
                    document.getElementById('calc-fulfillment-fee').textContent = `${data.fees.fulfillment_fee} ${calcData.currency}`;
                    document.getElementById('calc-storage-fee').textContent = `${data.fees.storage_fee} ${calcData.currency}`;

                    // Update profit metrics
                    document.getElementById('calc-net-profit').textContent = `${data.profit.per_unit} ${calcData.currency}`;
                    document.getElementById('calc-net-profit').style.color = data.analysis.color;

                    document.getElementById('calc-net-margin').textContent = `${data.profit.margin_percent}%`;
                    document.getElementById('calc-net-margin').style.color = data.profit.margin_percent >= 20 ? '#34d399' : data.profit.margin_percent >= 10 ? '#ffc107' : '#dc3545';

                    document.getElementById('calc-roi').textContent = `${data.profit.roi_percent}%`;
                    document.getElementById('calc-roi').style.color = data.profit.roi_percent >= 50 ? '#34d399' : data.profit.roi_percent >= 25 ? '#ffc107' : '#dc3545';

                    document.getElementById('calc-monthly-profit').textContent = `${data.profit.monthly.toLocaleString()} ${calcData.currency}`;
                    document.getElementById('calc-monthly-profit').style.color = data.profit.monthly >= 0 ? '#febd69' : '#dc3545';

                    console.log('Backend calculation:', data.analysis.message);
                } else {
                    throw new Error('API failed');
                }
            } catch (e) {
                // Fallback to local calculation if API fails
                console.warn('Using local calculation fallback:', e.message);

                const refFee = sellingPrice * 0.15;
                document.getElementById('calc-referral-fee').textContent = `${refFee.toFixed(2)} ${calcData.currency}`;

                const totalCosts = productCost + shippingCost + cpcCost + calcData.fulfillmentFee + refFee;
                const taxAmount = (sellingPrice * taxPercent) / 100;
                const netProfit = sellingPrice - totalCosts - taxAmount;
                const netMargin = sellingPrice > 0 ? (netProfit / sellingPrice) * 100 : 0;
                const investment = productCost + shippingCost + cpcCost;
                const roi = investment > 0 ? (netProfit / investment) * 100 : 0;
                const monthlyProfit = netProfit * monthlySales;

                document.getElementById('calc-net-profit').textContent = `${netProfit.toFixed(2)} ${calcData.currency}`;
                document.getElementById('calc-net-profit').style.color = netProfit >= 0 ? '#34d399' : '#dc3545';
                document.getElementById('calc-net-margin').textContent = `${netMargin.toFixed(1)}%`;
                document.getElementById('calc-net-margin').style.color = netMargin >= 20 ? '#34d399' : netMargin >= 10 ? '#ffc107' : '#dc3545';
                document.getElementById('calc-roi').textContent = `${roi.toFixed(0)}%`;
                document.getElementById('calc-roi').style.color = roi >= 50 ? '#34d399' : roi >= 25 ? '#ffc107' : '#dc3545';
                document.getElementById('calc-monthly-profit').textContent = `${monthlyProfit.toLocaleString(undefined, { maximumFractionDigits: 0 })} ${calcData.currency}`;
                document.getElementById('calc-monthly-profit').style.color = monthlyProfit >= 0 ? '#febd69' : '#dc3545';
            }
        };

        // Debounced recalculate
        const debouncedRecalculate = () => {
            clearTimeout(debounceTimer);
            debounceTimer = setTimeout(recalculate, 300);
        };

        // Add event listeners to all inputs
        ['calc-product-cost', 'calc-shipping-cost', 'calc-cpc-cost', 'calc-tax-percent', 'calc-price', 'calc-monthly-sales'].forEach(id => {
            document.getElementById(id).addEventListener('input', debouncedRecalculate);
        });

        // Close button
        document.getElementById('calc-close-btn').addEventListener('click', () => {
            resultContainer.style.display = 'none';
            resultContainer.innerHTML = '';
        });

        // Initial calculation
        recalculate();

    } catch (error) {
        console.error('Calculator error:', error);
        resultContainer.innerHTML = `
            <div style="padding: 20px; color: #dc3545; text-align: center;">
                ❌ Error loading calculator: ${error.message}
            </div>
        `;
    }
}

// ==================== SEARCH PAGE ANALYZER ====================

function checkAuthAndExecuteSearch(callback) {
    chrome.runtime.sendMessage({ action: 'getAuth' }, (response) => {
        if (chrome.runtime.lastError) {
            console.error('Chrome runtime error:', chrome.runtime.lastError);
            return;
        }

        if (response && response.authenticated) {
            callback();
        } else {
            showSearchAuthAlert();
        }
    });
}

function showSearchAuthAlert() {
    const isArabic = isPageArabic();
    const alertTitle = isArabic ? '🔑 تسجيل الدخول مطلوب' : '🔑 Authentication Required';
    const alertMsg = isArabic 
        ? 'يرجى النقر فوق أيقونة الإضافة في شريط الأدوات لتسجيل الدخول وتفعيل أدوات تحليل البحث.' 
        : 'Please click the extension icon in your browser toolbar to log in and unlock search analysis.';

    // Remove existing search toasts if any
    const existing = document.getElementById('sv-search-auth-toast');
    if (existing) {
        existing.remove();
    }

    // Create a beautiful floating toast
    const toast = document.createElement('div');
    toast.id = 'sv-search-auth-toast';
    toast.style.cssText = `
        position: fixed;
        bottom: 30px;
        right: 30px;
        background: linear-gradient(135deg, #1e293b 0%, #0f172a 100%);
        border-left: 4px solid #f59e0b;
        color: white;
        padding: 20px 24px;
        border-radius: 12px;
        box-shadow: 0 10px 25px rgba(0, 0, 0, 0.35);
        z-index: 1000000;
        font-family: 'Inter', sans-serif;
        max-width: 380px;
        direction: ${isArabic ? 'rtl' : 'ltr'};
        text-align: ${isArabic ? 'right' : 'left'};
    `;
    toast.innerHTML = `
        <div style="display: flex; gap: 14px; align-items: flex-start;">
            <div style="font-size: 24px; margin-top: -2px;">🔐</div>
            <div>
                <div style="font-weight: 700; font-size: 14px; margin-bottom: 4px; color: #f8fafc;">${alertTitle}</div>
                <div style="font-size: 12px; color: #94a3b8; line-height: 1.5;">${alertMsg}</div>
            </div>
            <button onclick="this.parentElement.parentElement.remove()" style="background:none;border:none;color:#94a3b8;font-size:16px;cursor:pointer;padding:0;margin-left:12px;font-weight:700;">×</button>
        </div>
    `;
    document.body.appendChild(toast);
    
    // Auto remove after 6 seconds
    setTimeout(() => {
        if (toast.parentNode) {
            toast.remove();
        }
    }, 6000);
}

function initializeSearchAnalyzer() {
    console.log('Initializing search analyzer...');
    injectSearchAnalyzerButton();

    // Initialize Cerebro UI for multi-ASIN selection
    if (typeof CerebroUI !== 'undefined') {
        const cerebroUI = new CerebroUI();
        cerebroUI.initOnSearchPage();
        window.cerebroUI = cerebroUI; // Make accessible globally
        console.log('Cerebro UI initialized for ASIN selection');
    }
}

function injectSearchAnalyzerButton() {
    // Check if button already exists
    if (document.getElementById('search-volume-btn-container')) {
        return;
    }

    const isArabic = isPageArabic();

    // Create inline container for the button
    const container = document.createElement('div');
    container.id = 'search-volume-btn-container';
    container.style.cssText = `
        width: 100%;
        margin: 16px 0 24px 0;
        display: flex;
        align-items: center;
        gap: 16px;
        font-family: 'Inter', system-ui, -apple-system, sans-serif;
        flex-wrap: wrap;
        direction: ${isArabic ? 'rtl' : 'ltr'};
    `;

    // Create the Market Analysis button with premium styling
    const btn = document.createElement('button');
    btn.id = 'market-analysis-btn';
    btn.style.cssText = `
        background: linear-gradient(135deg, #ff9900, #e68a00);
        color: #0f172a;
        border: none;
        padding: 10px 24px;
        border-radius: 12px;
        cursor: pointer;
        font-weight: 700;
        font-family: 'Inter', sans-serif;
        font-size: 14px;
        box-shadow: 0 4px 15px rgba(255, 153, 0, 0.35);
        display: flex;
        align-items: center;
        gap: 8px;
        transition: transform 0.2s, box-shadow 0.2s;
        white-space: nowrap;
        flex-shrink: 0;
    `;
    btn.innerHTML = `
        <span style="font-size: 18px;">📊</span>
        <span>${t('Market Analysis')}</span>
    `;

    btn.addEventListener('mouseenter', () => {
        btn.style.transform = 'translateY(-2px)';
        btn.style.background = '#febd69';
        btn.style.boxShadow = '0 6px 20px rgba(255, 153, 0, 0.5)';
    });

    btn.addEventListener('mouseleave', () => {
        btn.style.transform = 'scale(1)';
        btn.style.background = '#ff9900';
        btn.style.boxShadow = '0 4px 15px rgba(255, 153, 0, 0.35)';
    });

    btn.addEventListener('click', () => {
        checkAuthAndExecuteSearch(() => analyzeSearchPage());
    });

    // Create the Keyword Magnet button
    const magnetBtn = document.createElement('button');
    magnetBtn.id = 'keyword-magnet-btn';
    magnetBtn.style.cssText = `
        background: linear-gradient(135deg, #f59e0b, #d97706);
        color: white;
        border: none;
        padding: 10px 24px;
        border-radius: 12px;
        cursor: pointer;
        font-weight: 700;
        font-family: 'Inter', sans-serif;
        font-size: 14px;
        box-shadow: 0 4px 15px rgba(245, 158, 11, 0.35);
        display: flex;
        align-items: center;
        gap: 8px;
        transition: transform 0.2s, box-shadow 0.2s;
        white-space: nowrap;
        flex-shrink: 0;
    `;
    magnetBtn.innerHTML = `
        <span style="font-size: 18px;">🧲</span>
        <span>${t('Keyword Magnet')}</span>
    `;

    magnetBtn.addEventListener('mouseenter', () => {
        magnetBtn.style.transform = 'translateY(-2px)';
        magnetBtn.style.boxShadow = '0 6px 20px rgba(245, 158, 11, 0.5)';
    });

    magnetBtn.addEventListener('mouseleave', () => {
        magnetBtn.style.transform = 'scale(1)';
        magnetBtn.style.boxShadow = '0 4px 15px rgba(245, 158, 11, 0.35)';
    });

    magnetBtn.addEventListener('click', () => {
        checkAuthAndExecuteSearch(() => {
            if (typeof MagnetUI !== 'undefined' && window.magnetUI) {
                window.magnetUI.openInputPanel();
            } else {
                if (typeof MagnetUI !== 'undefined') {
                    window.magnetUI = new MagnetUI();
                    window.magnetUI.openInputPanel();
                } else {
                    alert('Keyword Magnet module not loaded. Please reload the page.');
                }
            }
        });
    });

    // Add description text
    const description = document.createElement('span');
    description.style.cssText = `
        color: #94a3b8;
        font-size: 13px;
        font-weight: 500;
        margin-left: 8px;
        margin-right: 8px;
    `;
    description.textContent = t('Analyze market or discover keyword ideas');

    container.appendChild(btn);
    container.appendChild(magnetBtn);
    container.appendChild(description);

    // Find the search results container and inject above it
    const searchResultsContainer = document.querySelector('[data-component-type="s-search-results"]');

    if (searchResultsContainer && searchResultsContainer.parentElement) {
        searchResultsContainer.parentElement.insertBefore(container, searchResultsContainer);
        console.log('Search analyzer buttons injected above search results');
    } else {
        // Fallback: try to find the main results div
        const mainSlot = document.querySelector('.s-main-slot.s-result-list');
        if (mainSlot && mainSlot.parentElement) {
            mainSlot.parentElement.insertBefore(container, mainSlot);
            console.log('Search analyzer buttons injected above main slot');
        } else {
            // Last fallback: prepend to search results area or body
            const searchArea = document.querySelector('#search') || document.querySelector('.s-desktop-content');
            if (searchArea) {
                searchArea.insertBefore(container, searchArea.firstChild);
                console.log('Search analyzer buttons injected at search area');
            } else {
                // Final fallback: use fixed position
                console.log('Could not find search container, using fixed position fallback');
                container.style.cssText = `
                    position: fixed;
                    right: 20px;
                    top: 120px;
                    z-index: 999999;
                    flex-direction: column;
                `;
                document.body.appendChild(container);
            }
        }
    }
}

async function analyzeSearchPage() {
    console.log('Starting search page analysis...');

    // Show loading
    const loadingDiv = document.createElement('div');
    loadingDiv.id = 'search-analyzer-loading';
    loadingDiv.style.cssText = `
        position: fixed;
        top: 50%;
        left: 50%;
        transform: translate(-50%, -50%);
        background: rgba(0,0,0,0.9);
        color: white;
        padding: 30px 40px;
        border-radius: 12px;
        z-index: 999999;
        text-align: center;
        font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
    `;
    loadingDiv.innerHTML = `
        <div style="margin-bottom: 12px;">
            <div style="width: 40px; height: 40px; border: 3px solid #333; border-top-color: #667eea; border-radius: 50%; animation: spin 1s linear infinite; margin: 0 auto;"></div>
        </div>
        <div id="loading-message" style="font-size: 14px;">Scraping search results...</div>
        <style>@keyframes spin { to { transform: rotate(360deg); } }</style>
    `;
    document.body.appendChild(loadingDiv);

    const updateLoadingMessage = (msg) => {
        const el = document.getElementById('loading-message');
        if (el) el.textContent = msg;
    };

    try {
        // Fetch configuration from backend (same settings as Reverse ASIN)
        updateLoadingMessage('Fetching configuration...');
        let fetchLimit = 15; // Safe default
        let batchSize = 3;
        let batchDelay = 500;

        try {
            const configResponse = await fetch('http://127.0.0.1:8000/api/settings');
            if (configResponse.ok) {
                const configData = await configResponse.json();
                const settings = configData.settings || {};

                // Search Page specific settings
                fetchLimit = settings.search_page_products_limit || 20;
                batchSize = settings.search_page_bsr_parallel_requests || 5;
                batchDelay = settings.search_page_bsr_delay_ms || 300;

                console.log('[Search Page] BSR enrichment settings:', { fetchLimit, batchSize, batchDelay });
            }
        } catch (e) {
            console.warn('Failed to fetch settings, using defaults');
        }

        // Use SerpParser to extract data
        const parser = new SerpParser(document);
        const keyword = parser.extractKeyword();
        let products = parser.extractProducts();

        // Apply backend fetch limit
        if (fetchLimit && products.length > fetchLimit) {
            console.log(`Limiting analyzed products from ${products.length} to ${fetchLimit}`);
            products = products.slice(0, fetchLimit);
        }

        const pageInfo = parser.getPageInfo();

        console.log(`Keyword: "${keyword}", Products found: ${products.length}`);

        if (!keyword) {
            throw new Error('Could not detect search keyword');
        }

        if (products.length === 0) {
            throw new Error('No products found on page');
        }

        // ========== DEBUG: Log each product's data ==========
        console.log('========== PRODUCT DEBUG START ==========');
        products.forEach((p, i) => {
            console.log(`Product ${i + 1}:`, {
                asin: p.asin,
                title: p.title?.substring(0, 50) + '...',
                monthly_sales: p.monthly_sales,
                monthly_sales_raw: p.monthly_sales_raw,
                is_sponsored: p.is_sponsored,
                price: p.price
            });
        });
        console.log('========== PRODUCT DEBUG END ==========');

        // Count products with monthly sales
        const withSales = products.filter(p => p.monthly_sales).length;
        console.log(`Products with monthly sales badge: ${withSales}/${products.length}`);

        // Enrich products WITHOUT monthly sales with BSR
        updateLoadingMessage('Checking for missing sales data...');
        products = await parser.enrichWithBSR(products, {
            limit: fetchLimit,
            batchSize: batchSize,
            batchDelay: batchDelay,
            onProgress: (current, total, message) => {
                updateLoadingMessage(message || `Fetching BSR data... (${current}/${total})`);
            }
        });

        // ========== DEBUG: Log products after BSR enrichment ==========
        console.log('========== AFTER BSR ENRICHMENT ==========');
        products.forEach((p, i) => {
            const productUrl = `${window.location.origin}/dp/${p.asin}`;
            console.log(`Product ${i + 1} (after BSR):`, {
                asin: p.asin,
                title: p.title?.substring(0, 50) + '...',
                monthly_sales: p.monthly_sales,
                bsr: p.bsr,
                bsr_category: p.bsr_category,
                seller_count: p.seller_count,
                url: productUrl
            });
        });
        console.log('==========================================');

        updateLoadingMessage('Calculating search volume...');

        // Send to backend for calculation
        const marketplace = window.location.hostname.includes('.eg') ? 'amazon.eg' : 'amazon.com';

        // Send all products (do not filter out those without sales/bsr to match fetched count)
        const productsWithData = products;

        // Calculate total explicit monthly sales
        const totalExplicitSales = productsWithData.reduce((sum, p) => sum + (p.monthly_sales || 0), 0);
        console.log(`Sending ${productsWithData.length} products with data (from ${products.length} total)`);
        console.log(`Total explicit monthly sales in payload: ${totalExplicitSales}`);

        const apiPayload = {
            keyword: keyword,
            marketplace: marketplace,
            products: productsWithData // Send all products with data
        };

        console.log('Sending to backend:', apiPayload);

        const response = await fetch('http://127.0.0.1:8000/api/search-volume/estimate', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'Accept': 'application/json'
            },
            body: JSON.stringify(apiPayload)
        });

        if (!response.ok) {
            throw new Error(`API error: ${response.status}`);
        }

        const result = await response.json();
        console.log('Search volume result:', result);

        // Calculate full-page ad metrics (backend only sees filtered products)
        // We want to show density for the ENTIRE page, not just the valid data points
        const sponsoredCount = products.filter(p => p.is_sponsored).length;
        const totalProducts = products.length;
        const organicCount = totalProducts - sponsoredCount;
        const densityPercent = totalProducts > 0 ? Math.round((sponsoredCount / totalProducts) * 100) : 0;

        // Calculate fallback sales metrics (in case backend missing them)
        const fallbackSalesMetrics = {
            total_monthly_sales: totalExplicitSales,
            max_monthly_sales: Math.max(...productsWithData.map(p => p.monthly_sales || 0), 0),
            min_monthly_sales: Math.min(...productsWithData.map(p => p.monthly_sales || 0).filter(s => s > 0)) || 0
        };

        // Overwrite with full-page stats and add fallback sales
        result.ad_metrics = {
            sponsored_count: sponsoredCount,
            organic_count: organicCount,
            density_percent: densityPercent,
            total_products: totalProducts
        };
        result.sales_metrics_fallback = fallbackSalesMetrics;

        // ========== DEBUG: Log detailed calculation breakdown ==========
        console.log('========== CALCULATION BREAKDOWN ==========');
        console.log('Search Volume:', result.search_volume);
        console.log('Difficulty:', result.difficulty);
        console.log('Product Sales Used:', result.debug?.product_sales || 'N/A');
        console.log('============================================');

        // Remove loading
        document.getElementById('search-analyzer-loading')?.remove();

        // Display results with products data for table
        displaySearchVolumeResults(keyword, result, products);

    } catch (error) {
        document.getElementById('search-analyzer-loading')?.remove();
        console.error('Search analysis error:', error);
        alert('Search analysis failed: ' + error.message);
    }
}

// Helper: Generate mini bar chart for sales visualization
function generateSalesBars(sales, maxSales) {
    if (!sales || !maxSales) return '<span style="color: #4b5563;">-</span>';

    const percentage = Math.min(100, (sales / maxSales) * 100);
    const bars = 5;
    const filledBars = Math.round((percentage / 100) * bars);

    let html = '';
    for (let i = 0; i < bars; i++) {
        const height = 4 + (i * 2.5);
        const color = i < filledBars ? '#febd69' : '#3d4656';
        html += `<div style="width: 3px; height: ${height}px; background: ${color}; border-radius: 1px;"></div>`;
    }
    return html;
}

function displaySearchVolumeResults(keyword, result, scrapedProducts = []) {
    // Remove existing panel (both inline and floating)
    document.getElementById('search-volume-panel')?.remove();
    document.getElementById('search-volume-inline-panel')?.remove();

    // Use backend-enriched products if available, otherwise use scraped products
    // But merge seller_count from scrapedProducts since backend doesn't have it
    let products = result.products && result.products.length > 0 ? result.products : scrapedProducts;

    // Merge seller_count from scrapedProducts into products (in case backend products don't have it)
    if (scrapedProducts.length > 0) {
        const scrapedMap = new Map(scrapedProducts.map(p => [p.asin, p]));
        products = products.map(p => {
            const scraped = scrapedMap.get(p.asin);
            if (scraped) {
                // Merge seller_count from scraped data, default to 1 if null
                p.seller_count = scraped.seller_count ?? p.seller_count ?? 1;
            }
            // If still null, default to 1 (product has at least 1 seller)
            if (p.seller_count === null || p.seller_count === undefined) {
                p.seller_count = 1;
            }
            return p;
        });
    }

    const sv = result.search_volume;
    const kd = result.difficulty;
    const adMetrics = result.ad_metrics;

    // KD color scheme (adjusted for Dark Mode)
    let kdColor = '#4ade80'; // Bright Green
    if (kd.score >= 70) kdColor = '#f87171'; // Red
    else if (kd.score >= 50) kdColor = '#fb923c'; // Orange
    else if (kd.score >= 30) kdColor = '#facc15'; // Yellow

    // Confidence color
    let confColor = '#4ade80'; // Bright Green
    if (sv.confidence === 'low') confColor = '#fb923c';
    else if (sv.confidence === 'very_low') confColor = '#f87171';
    else if (sv.confidence === 'medium') confColor = '#facc15';

    // Create the inline panel
    const inlinePanel = document.createElement('div');
    inlinePanel.id = 'search-volume-inline-panel';
    inlinePanel.style.cssText = `
        width: 100%;
        background: #232f3e; /* Amazon Dark Blue */
        border: 1px solid #37475a;
        border-radius: 8px;
        box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.3);
        margin: 16px 0 24px 0;
        overflow: hidden;
        font-family: 'Amazon Ember', -apple-system, system-ui, sans-serif;
        color: #fff;
    `;

    // Dark Mode Dashboard Layout
    inlinePanel.innerHTML = `
        <!-- Header -->
        <div style="background: #19222d; padding: 12px 20px; border-bottom: 1px solid #37475a; display: flex; justify-content: space-between; align-items: center;">
            <div style="display: flex; align-items: center; gap: 10px;">
                <h3 style="margin: 0; font-size: 15px; color: #fff; font-weight: 700;">Market Analysis: "${keyword}"</h3>
                <span style="background: ${confColor}20; color: ${confColor}; font-size: 10px; padding: 3px 8px; border-radius: 4px; font-weight: 600; letter-spacing: 0.5px; text-transform: uppercase; border: 1px solid ${confColor}40;">
                    ${sv.confidence.replace('_', ' ')} Confidence
                </span>
            </div>
            <button id="sv-inline-close-btn" style="background:none; border:none; cursor:pointer; color: #9ca3af; font-size: 18px; padding: 4px; width: 24px; height: 24px; display: flex; align-items: center; justify-content: center;">✕</button>
        </div>

        <!-- Metric Cards Grid (Dark Mode) -->
        <div style="display: grid; grid-template-columns: repeat(4, 1fr); gap: 1px; background: #37475a; border-bottom: 1px solid #37475a;">

            <!-- 1. Search Volume -->
            <div style="background: #232f3e; padding: 16px; display: flex; flex-direction: column; justify-content: center;">
                <div style="color: #9ca3af; font-size: 10px; font-weight: 700; letter-spacing: 0.5px; margin-bottom: 4px; text-transform: uppercase;">🔍 Search Volume / mo</div>
                <div style="font-size: 22px; font-weight: 700; color: #fff; line-height: 1.2;">
                    ${sv.estimated?.toLocaleString() || 'N/A'}
                </div>
                <div style="font-size: 11px; margin-top: 4px; font-weight: 500; color: ${sv.demand_level === 'high' ? '#4ade80' : sv.demand_level === 'medium' ? '#facc15' : '#9ca3af'}">
                    ${sv.demand_level === 'high' ? '⚡ High Demand' : sv.demand_level === 'medium' ? '⚠️ Medium Demand' : '💤 Low Demand'}
                </div>
            </div>

            <!-- 2. Difficulty -->
            <div style="background: #232f3e; padding: 16px; display: flex; flex-direction: column; justify-content: center;">
                <div style="color: #9ca3af; font-size: 10px; font-weight: 700; letter-spacing: 0.5px; margin-bottom: 4px; text-transform: uppercase;">🔥 Difficulty (KD)</div>
                <div style="font-size: 22px; font-weight: 700; color: ${kdColor}; line-height: 1.2;">
                    ${kd.score}<span style="font-size: 12px; color: #6b7280; font-weight: 400;">/100</span>
                </div>
                <div style="font-size: 11px; color: ${kdColor}; margin-top: 4px; font-weight: 500;">
                    ${kd.level === 'very_easy' ? '✅ Very Easy' : kd.level === 'easy' ? '✅ Easy' : kd.level === 'moderate' ? '⚠️ Moderate' : kd.level === 'hard' ? '🔴 Hard' : kd.level === 'very_hard' ? '🔴 Very Hard' : ''}
                </div>
            </div>

            <!-- 3. Total Sales -->
             <div style="background: #232f3e; padding: 16px; display: flex; flex-direction: column; justify-content: center;">
                <div style="color: #9ca3af; font-size: 10px; font-weight: 700; letter-spacing: 0.5px; margin-bottom: 4px; text-transform: uppercase;">🛒 Total Sales / mo</div>
                <div style="font-size: 22px; font-weight: 700; color: #febd69; line-height: 1.2;"> <!-- Amazon Orange for Sales -->
                    ${(sv.sales_metrics?.total_monthly_sales || result.sales_metrics_fallback?.total_monthly_sales || 0).toLocaleString()} <span style="font-size: 14px; color: #9ca3af; font-weight: 400;">units</span>
                </div>
            </div>

            <!-- 4. Sales Range -->
             <div style="background: #232f3e; padding: 16px; display: flex; flex-direction: column; justify-content: center;">
                <div style="color: #9ca3af; font-size: 10px; font-weight: 700; letter-spacing: 0.5px; margin-bottom: 4px; text-transform: uppercase;">🛒 Sales Range / mo</div>
                <div style="font-size: 18px; font-weight: 700; color: #fff; line-height: 1.2;">
                    ${(sv.sales_metrics?.min_monthly_sales || result.sales_metrics_fallback?.min_monthly_sales || 0).toLocaleString()} - ${(sv.sales_metrics?.max_monthly_sales || result.sales_metrics_fallback?.max_monthly_sales || 0).toLocaleString()}
                </div>
            </div>

            <!-- 5. Search Volume Range -->
             <div style="background: #232f3e; padding: 16px; display: flex; flex-direction: column; justify-content: center;">
                <div style="color: #9ca3af; font-size: 10px; font-weight: 700; letter-spacing: 0.5px; margin-bottom: 4px; text-transform: uppercase;">🔍 Search Volume Range / mo</div>
                <div style="font-size: 16px; font-weight: 600; color: #d1d5db; line-height: 1.2;">
                    ${sv.range?.min?.toLocaleString()} - ${sv.range?.max?.toLocaleString()}
                </div>
            </div>

            <!-- 6. Sponsored Ads -->
            <div style="background: #232f3e; padding: 16px; display: flex; flex-direction: column; justify-content: center;">
                <div style="color: #9ca3af; font-size: 10px; font-weight: 700; letter-spacing: 0.5px; margin-bottom: 4px; text-transform: uppercase;">📢 Sponsored Ads</div>
                <div style="font-size: 22px; font-weight: 700; color: #fb923c; line-height: 1.2;">
                    ${adMetrics?.sponsored_count || 0} <span style="font-size: 12px; color: #6b7280; font-weight: 400;">(${adMetrics?.density_percent || 0}%)</span>
                </div>
            </div>

            <!-- 7. Organic Results -->
            <div style="background: #232f3e; padding: 16px; display: flex; flex-direction: column; justify-content: center;">
                <div style="color: #9ca3af; font-size: 10px; font-weight: 700; letter-spacing: 0.5px; margin-bottom: 4px; text-transform: uppercase;">🌿 Organic Results</div>
                <div style="font-size: 22px; font-weight: 700; color: #4ade80; line-height: 1.2;">
                    ${adMetrics?.organic_count || 0}
                </div>
            </div>
            
            <!-- 8. AI Recommendation -->
            <div style="background: #232f3e; padding: 16px; display: flex; flex-direction: column; justify-content: center;">
                <div style="color: #9ca3af; font-size: 10px; font-weight: 700; letter-spacing: 0.5px; margin-bottom: 4px; text-transform: uppercase;">💡 AI Recommendation</div>
                <div style="font-size: 11px; color: #e5e7eb; line-height: 1.3; overflow: hidden; display: -webkit-box; -webkit-line-clamp: 3; -webkit-box-orient: vertical;" title="${kd.recommendation || ''}">
                    ${kd.recommendation || 'Analysis complete.'}
                </div>
            </div>
        </div>

        <!-- Product Statistics Row -->
        <div style="display: grid; grid-template-columns: repeat(5, 1fr); gap: 1px; background: #37475a; border-bottom: 1px solid #37475a;">
            <!-- Total Revenue -->
            <div style="background: #1a2332; padding: 14px 16px; text-align: center;">
                <div style="color: #9ca3af; font-size: 9px; font-weight: 700; letter-spacing: 0.5px; margin-bottom: 4px; text-transform: uppercase;">💰 Total Revenue / mo</div>
                <div style="font-size: 16px; font-weight: 700; color: #a78bfa;">
                    ${(result.product_stats?.total_revenue || 0).toLocaleString()}
                </div>
            </div>
            
            <!-- Average Revenue -->
            <div style="background: #1a2332; padding: 14px 16px; text-align: center;">
                <div style="color: #9ca3af; font-size: 9px; font-weight: 700; letter-spacing: 0.5px; margin-bottom: 4px; text-transform: uppercase;">📊 Avg Revenue / mo</div>
                <div style="font-size: 16px; font-weight: 700; color: #60a5fa;">
                    ${(result.product_stats?.average_revenue || 0).toLocaleString()}
                </div>
            </div>
            
            <!-- Average Price -->
            <div style="background: #1a2332; padding: 14px 16px; text-align: center;">
                <div style="color: #9ca3af; font-size: 9px; font-weight: 700; letter-spacing: 0.5px; margin-bottom: 4px; text-transform: uppercase;">🏷️ Avg Price</div>
                <div style="font-size: 16px; font-weight: 700; color: #10b981;">
                    ${(result.product_stats?.average_price || 0).toLocaleString()}
                </div>
            </div>
            
            <!-- Average BSR -->
            <div style="background: #1a2332; padding: 14px 16px; text-align: center;">
                <div style="color: #9ca3af; font-size: 9px; font-weight: 700; letter-spacing: 0.5px; margin-bottom: 4px; text-transform: uppercase;">📈 Avg BSR</div>
                <div style="font-size: 16px; font-weight: 700; color: #f59e0b;">
                    #${(result.product_stats?.average_bsr || 0).toLocaleString()}
                </div>
            </div>
            
            <!-- Average Reviews -->
            <div style="background: #1a2332; padding: 14px 16px; text-align: center;">
                <div style="color: #9ca3af; font-size: 9px; font-weight: 700; letter-spacing: 0.5px; margin-bottom: 4px; text-transform: uppercase;">⭐ Avg Reviews</div>
                <div style="font-size: 16px; font-weight: 700; color: #e5e7eb;">
                    ${(result.product_stats?.average_reviews || 0).toLocaleString()}
                </div>
            </div>
        </div>

        <!-- Footer Action Bar -->
        <div style="padding: 10px 20px; background: #19222d; display: flex; justify-content: space-between; align-items: center; color: #9ca3af; font-size: 11px;">
             <div style="cursor: pointer; display: flex; align-items: center; gap: 4px; user-select: none;" id="sv-toggle-details">
                <span style="display: inline-block; transition: transform 0.2s;">▶</span> Show Detailed Breakdown
             </div>
             <div>${result.products_analyzed || 0} products analyzed</div>
        </div>

        <!-- Collapsible Content -->
        <div id="sv-details-content" style="display: none; padding: 20px; background: #232f3e; border-top: 1px solid #37475a;">
             <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 20px;">
                <!-- Breakdown -->
                 <div>
                    <div style="font-size: 11px; font-weight: 600; color: #9ca3af; margin-bottom: 10px; text-transform: uppercase;">Difficulty Breakdown</div>
                    ${Object.entries(kd.breakdown || {}).map(([key, val]) => {
        let label = key.replace('_', ' ').replace(/\b\w/g, l => l.toUpperCase());
        let desc = '';
        if (key === 'listing_strength') { label = 'Listing Strength'; desc = '(Avg Rating & Reviews)'; }
        if (key === 'ad_density') { label = 'Top 10 Ad Density'; desc = '(Ads in Top Results)'; }
        if (key === 'review_barrier') { label = 'Review Barrier'; desc = '(Median Reviews Needed)'; }
        if (key === 'brand_dominance') { label = 'Brand Dominance'; desc = '(Top Brand Share)'; }
        return `
                        <div style="display: flex; justify-content: space-between; margin-bottom: 6px; font-size: 12px;">
                            <span style="color: #9ca3af;">${label} <span style="font-size: 10px; color: #6b7280;">${desc}</span></span>
                            <span style="font-weight: 600; color: #fff;">${val}/100</span>
                        </div>
                    `}).join('')}
                 </div>
                 <!-- Observations -->
                 <div>
                    <div style="font-size: 11px; font-weight: 600; color: #9ca3af; margin-bottom: 10px; text-transform: uppercase;">Key Observations</div>
                     ${result.insights?.length ? result.insights.map(i => `
                        <div style="display: flex; gap: 8px; margin-bottom: 6px; font-size: 12px; color: #e5e7eb;">
                            <span style="color: ${i.type === 'success' ? '#4ade80' : i.type === 'warning' ? '#fb923c' : '#9ca3af'};">•</span>
                            ${i.message}
                        </div>
                    `).join('') : '<div style="color: #6b7280; font-size: 12px;">No specific observations</div>'}
                 </div>
             </div>
        </div>

        <!-- Product Data Table -->
        ${products.length > 0 ? `
        <div style="border-top: 1px solid #37475a;">
            <div style="padding: 12px 20px; background: #19222d; display: flex; justify-content: space-between; align-items: center;">
                <div style="font-size: 13px; font-weight: 600; color: #fff;">📊 Product Data (${products.length} products)</div>
                <div style="font-size: 10px; color: #6b7280;">Click column header to sort</div>
            </div>
            <div style="overflow-x: auto; max-height: 500px; overflow-y: auto;">
                <table id="sv-products-table" style="width: 100%; border-collapse: collapse; font-size: 11px; min-width: 1200px;">
                    <thead style="position: sticky; top: 0; z-index: 10;">
                        <tr style="background: #1a2332; color: #9ca3af; text-transform: uppercase; font-size: 9px; letter-spacing: 0.5px;">
                            <th data-sort="position" style="padding: 10px 8px; text-align: center; cursor: pointer; user-select: none; white-space: nowrap; border-bottom: 2px solid #37475a;">#</th>
                            <th data-sort="title" style="padding: 10px 8px; text-align: left; cursor: pointer; user-select: none; min-width: 200px; border-bottom: 2px solid #37475a;">Product</th>
                            <th data-sort="title_density" style="padding: 10px 8px; text-align: center; cursor: pointer; user-select: none; border-bottom: 2px solid #37475a;" title="How many times the keyword appears in the title">Title Density</th>
                            <th style="padding: 10px 8px; text-align: left; border-bottom: 2px solid #37475a;">Brand</th>
                            <th data-sort="price" style="padding: 10px 8px; text-align: right; cursor: pointer; user-select: none; border-bottom: 2px solid #37475a;">Price</th>
                            <th data-sort="monthly_sales" style="padding: 10px 8px; text-align: right; cursor: pointer; user-select: none; border-bottom: 2px solid #37475a;">Sales / mo</th>
                            <th style="padding: 10px 8px; text-align: center; border-bottom: 2px solid #37475a;">Trend</th>
                            <th data-sort="revenue" style="padding: 10px 8px; text-align: right; cursor: pointer; user-select: none; border-bottom: 2px solid #37475a;">Revenue / mo</th>
                            <th data-sort="bsr" style="padding: 10px 8px; text-align: right; cursor: pointer; user-select: none; border-bottom: 2px solid #37475a;">BSR</th>
                            <th style="padding: 10px 8px; text-align: left; border-bottom: 2px solid #37475a;">Category</th>
                            <th style="padding: 10px 8px; text-align: right; border-bottom: 2px solid #37475a;">FBA Fees</th>
                            <th data-sort="seller_count" style="padding: 10px 8px; text-align: center; cursor: pointer; user-select: none; border-bottom: 2px solid #37475a;">Sellers</th>
                            <th data-sort="reviews" style="padding: 10px 8px; text-align: right; cursor: pointer; user-select: none; border-bottom: 2px solid #37475a;">Reviews</th>
                            <th data-sort="rating" style="padding: 10px 8px; text-align: center; cursor: pointer; user-select: none; border-bottom: 2px solid #37475a;">Rating</th>
                            <th style="padding: 10px 8px; text-align: center; border-bottom: 2px solid #37475a;">ASIN</th>
                        </tr>
                    </thead>
                    <tbody id="sv-products-tbody">
                        ${products.map((p, idx) => {
            const revenue = p.revenue || ((p.price || 0) * (p.monthly_sales || 0));
            const isFBA = p.is_fba !== undefined ? p.is_fba : !p.is_fbm;
            const salesBars = generateSalesBars(p.monthly_sales || 0, Math.max(...products.map(x => x.monthly_sales || 0)));
            const isEstimated = p.is_sales_estimated;
            const hasNoData = !p.monthly_sales && !p.bsr;
            const rowOpacity = hasNoData ? '0.5' : '1';
            // Calculate title density - count keyword occurrences in title
            const titleLower = (p.title || '').toLowerCase();
            const keywordLower = keyword.toLowerCase();
            const keywordWords = keywordLower.split(/\s+/).filter(w => w.length > 2);
            let titleDensity = 0;
            keywordWords.forEach(word => {
                const regex = new RegExp(word, 'gi');
                const matches = titleLower.match(regex);
                if (matches) titleDensity += matches.length;
            });
            p.title_density = titleDensity; // Store for sorting
            return `
                            <tr data-asin="${p.asin}" style="background: ${idx % 2 === 0 ? '#232f3e' : '#283547'}; border-bottom: 1px solid #37475a; transition: background 0.15s; opacity: ${rowOpacity};" ${hasNoData ? 'title="No sales data available (new product?)"' : ''}>
                                <td style="padding: 8px; text-align: center; color: #6b7280; font-weight: 600;">${p.position || idx + 1}</td>
                                <td style="padding: 8px;">
                                    <div style="display: flex; align-items: center; gap: 10px;">
                                        <img src="${p.image || 'https://via.placeholder.com/40'}" alt="" style="width: 40px; height: 40px; object-fit: contain; border-radius: 4px; background: #fff;" onerror="this.src='https://via.placeholder.com/40'"/>
                                        <div style="flex: 1; min-width: 0;">
                                            <a href="${window.location.origin}/dp/${p.asin}" target="_blank" style="color: #60a5fa; text-decoration: none; font-weight: 500; display: -webkit-box; -webkit-line-clamp: 2; -webkit-box-orient: vertical; overflow: hidden; line-height: 1.3; font-size: 11px;" title="${p.title || 'Unknown'}">
                                                ${(p.title || 'Unknown').substring(0, 60)}${(p.title || '').length > 60 ? '...' : ''}
                                            </a>
                                            ${p.is_sponsored ? '<span style="display: inline-block; background: #f59e0b20; color: #f59e0b; font-size: 8px; padding: 1px 4px; border-radius: 2px; margin-top: 2px;">Sponsored</span>' : ''}
                                        </div>
                                    </div>
                                </td>
                                <td style="padding: 8px; text-align: center; color: ${titleDensity >= 3 ? '#4ade80' : titleDensity >= 1 ? '#facc15' : '#f87171'}; font-weight: 600;" title="${titleDensity} keyword matches in title">${titleDensity}</td>
                                <td style="padding: 8px; text-align: left; color: #9ca3af; font-size: 10px;">${p.brand || '-'}</td>
                                <td style="padding: 8px; text-align: right; color: #10b981; font-weight: 700;">${p.price ? p.price.toLocaleString() : '-'}</td>
                                <td style="padding: 8px; text-align: right;">
                                    <span style="color: ${p.monthly_sales ? '#febd69' : '#6b7280'}; font-weight: 700;">${p.monthly_sales ? p.monthly_sales.toLocaleString() : '0'}</span>
                                    ${isEstimated ? '<span style="display: block; font-size: 8px; color: #9ca3af;">Est.</span>' : ''}
                                    ${p.is_new_product ? '<span style="display: block; font-size: 7px; color: #60a5fa;">New</span>' : ''}
                                </td>
                                <td style="padding: 8px; text-align: center;">
                                    <div style="display: flex; align-items: flex-end; justify-content: center; gap: 1px; height: 16px;">
                                        ${salesBars}
                                    </div>
                                </td>
                                <td style="padding: 8px; text-align: right; color: ${revenue > 0 ? '#a78bfa' : '#6b7280'}; font-weight: 600;">${revenue >= 0 ? revenue.toLocaleString() : '0'}</td>
                                <td style="padding: 8px; text-align: right; color: #f59e0b;">${p.bsr ? '#' + p.bsr.toLocaleString() : '-'}</td>
                                <td style="padding: 8px; text-align: left; color: #9ca3af; font-size: 10px; max-width: 100px; overflow: hidden; text-overflow: ellipsis; white-space: nowrap;" title="${p.bsr_category || p.category || ''}">${p.bsr_category || p.category || '-'}</td>
                                <td style="padding: 8px; text-align: right; color: #fb923c; font-size: 10px;">${p.estimated_fees ? p.estimated_fees.toLocaleString() : '-'}</td>
                                <td style="padding: 8px; text-align: center; color: ${p.seller_count && p.seller_count > 10 ? '#f87171' : p.seller_count ? '#4ade80' : '#6b7280'}; font-weight: 600;">${p.seller_count ? p.seller_count : '-'}</td>
                                <td style="padding: 8px; text-align: right; color: #e5e7eb;">${(p.reviews !== null && p.reviews !== undefined) ? p.reviews.toLocaleString() : '0'}</td>
                                <td style="padding: 8px; text-align: center;">
                                    ${(p.rating && p.rating > 0) ? `<span style="color: ${p.rating >= 4 ? '#4ade80' : p.rating >= 3 ? '#facc15' : '#f87171'};">⭐ ${p.rating}</span>` : '-'}
                                </td>
                                <td style="padding: 8px; text-align: center;">
                                    <span style="color: #9ca3af; font-family: monospace; font-size: 9px;">${p.asin}</span>
                                </td>
                            </tr>
                            `;
        }).join('')}
                    </tbody>
                </table>
            </div>
        </div>
        ` : ''}
    `;

    // Find the search results container and inject above it
    const searchResultsContainer = document.querySelector('[data-component-type="s-search-results"]');

    if (searchResultsContainer && searchResultsContainer.parentElement) {
        searchResultsContainer.parentElement.insertBefore(inlinePanel, searchResultsContainer);
        console.log('Analysis panel injected above search results');
    } else {
        const mainSlot = document.querySelector('.s-main-slot.s-result-list');
        if (mainSlot && mainSlot.parentElement) {
            mainSlot.parentElement.insertBefore(inlinePanel, mainSlot);
            console.log('Analysis panel injected above main slot');
        } else {
            console.log('Could not find search results container, using floating panel fallback');
            inlinePanel.style.position = 'fixed';
            inlinePanel.style.right = '20px';
            inlinePanel.style.top = '120px';
            inlinePanel.style.width = '380px';
            inlinePanel.style.zIndex = '999999';
            inlinePanel.style.boxShadow = '0 10px 25px -5px rgba(0, 0, 0, 0.5), 0 8px 10px -6px rgba(0, 0, 0, 0.5)';
            document.body.appendChild(inlinePanel);
        }
    }

    // Event Listeners
    document.getElementById('sv-inline-close-btn')?.addEventListener('click', () => {
        inlinePanel.remove();
    });

    const toggleBtn = document.getElementById('sv-toggle-details');
    const content = document.getElementById('sv-details-content');
    if (toggleBtn && content) {
        toggleBtn.addEventListener('click', () => {
            const isHidden = content.style.display === 'none';
            content.style.display = isHidden ? 'block' : 'none';
            toggleBtn.querySelector('span').style.transform = isHidden ? 'rotate(90deg)' : 'rotate(0deg)';
        });
    }

    // Table Sorting Functionality
    if (products.length > 0) {
        let currentSort = { column: null, ascending: true };

        const table = document.getElementById('sv-products-table');
        const headers = table?.querySelectorAll('th[data-sort]');

        headers?.forEach(header => {
            header.addEventListener('click', () => {
                const column = header.getAttribute('data-sort');

                // Toggle direction if same column, otherwise ascending
                if (currentSort.column === column) {
                    currentSort.ascending = !currentSort.ascending;
                } else {
                    currentSort.column = column;
                    currentSort.ascending = true;
                }

                // Update header indicators
                headers.forEach(h => {
                    h.style.color = '#9ca3af';
                    h.textContent = h.textContent.replace(/ [▲▼]/g, '');
                });
                header.style.color = '#febd69';
                header.textContent += currentSort.ascending ? ' ▲' : ' ▼';

                // Sort products
                const sortedProducts = [...products].sort((a, b) => {
                    let valA = a[column];
                    let valB = b[column];

                    // Handle revenue specially
                    if (column === 'revenue') {
                        valA = (a.price || 0) * (a.monthly_sales || 0);
                        valB = (b.price || 0) * (b.monthly_sales || 0);
                    }

                    // Null handling
                    if (valA == null) valA = column === 'title' ? '' : 0;
                    if (valB == null) valB = column === 'title' ? '' : 0;

                    // Compare
                    let comparison = 0;
                    if (typeof valA === 'string') {
                        comparison = valA.localeCompare(valB);
                    } else {
                        comparison = valA - valB;
                    }

                    return currentSort.ascending ? comparison : -comparison;
                });

                // Rebuild table body
                const tbody = document.getElementById('sv-products-tbody');
                if (tbody) {
                    tbody.innerHTML = sortedProducts.map((p, idx) => {
                        const revenue = p.revenue || ((p.price || 0) * (p.monthly_sales || 0));
                        const isFBA = p.is_fba !== undefined ? p.is_fba : !p.is_fbm;
                        const salesBars = generateSalesBars(p.monthly_sales || 0, Math.max(...products.map(x => x.monthly_sales || 0)));
                        const isEstimated = p.is_sales_estimated;
                        return `
                        <tr data-asin="${p.asin}" style="background: ${idx % 2 === 0 ? '#232f3e' : '#283547'}; border-bottom: 1px solid #37475a; transition: background 0.15s;">
                            <td style="padding: 8px; text-align: center; color: #6b7280; font-weight: 600;">${p.position || idx + 1}</td>
                            <td style="padding: 8px;">
                                <div style="display: flex; align-items: center; gap: 10px;">
                                    <img src="${p.image || 'https://via.placeholder.com/40'}" alt="" style="width: 40px; height: 40px; object-fit: contain; border-radius: 4px; background: #fff;" onerror="this.src='https://via.placeholder.com/40'"/>
                                    <div style="flex: 1; min-width: 0;">
                                        <a href="${window.location.origin}/dp/${p.asin}" target="_blank" style="color: #60a5fa; text-decoration: none; font-weight: 500; display: -webkit-box; -webkit-line-clamp: 2; -webkit-box-orient: vertical; overflow: hidden; line-height: 1.3; font-size: 11px;" title="${p.title || 'Unknown'}">
                                            ${(p.title || 'Unknown').substring(0, 60)}${(p.title || '').length > 60 ? '...' : ''}
                                        </a>
                                        ${p.is_sponsored ? '<span style="display: inline-block; background: #f59e0b20; color: #f59e0b; font-size: 8px; padding: 1px 4px; border-radius: 2px; margin-top: 2px;">Sponsored</span>' : ''}
                                    </div>
                                </div>
                            </td>
                            <td style="padding: 8px; text-align: center; color: ${(p.title_density || 0) >= 3 ? '#4ade80' : (p.title_density || 0) >= 1 ? '#facc15' : '#f87171'}; font-weight: 600;" title="${p.title_density || 0} keyword matches in title">${p.title_density || 0}</td>
                            <td style="padding: 8px; text-align: left; color: #9ca3af; font-size: 10px;">${p.brand || '-'}</td>
                            <td style="padding: 8px; text-align: right; color: #10b981; font-weight: 700;">${p.price ? p.price.toLocaleString() : '-'}</td>
                            <td style="padding: 8px; text-align: right;">
                                <span style="color: #febd69; font-weight: 700;">${p.monthly_sales ? p.monthly_sales.toLocaleString() : '-'}</span>
                                ${isEstimated ? '<span style="display: block; font-size: 8px; color: #9ca3af;">Est.</span>' : ''}
                            </td>
                            <td style="padding: 8px; text-align: center;">
                                <div style="display: flex; align-items: flex-end; justify-content: center; gap: 1px; height: 16px;">
                                    ${salesBars}
                                </div>
                            </td>
                            <td style="padding: 8px; text-align: right; color: #a78bfa; font-weight: 600;">${revenue > 0 ? revenue.toLocaleString() : '-'}</td>
                            <td style="padding: 8px; text-align: right; color: #f59e0b;">${p.bsr ? '#' + p.bsr.toLocaleString() : '-'}</td>
                            <td style="padding: 8px; text-align: left; color: #9ca3af; font-size: 10px; max-width: 100px; overflow: hidden; text-overflow: ellipsis; white-space: nowrap;" title="${p.bsr_category || p.category || ''}">${p.bsr_category || p.category || '-'}</td>
                            <td style="padding: 8px; text-align: right; color: #fb923c; font-size: 10px;">${p.estimated_fees ? p.estimated_fees.toLocaleString() : '-'}</td>
                            <td style="padding: 8px; text-align: center; color: ${p.seller_count && p.seller_count > 10 ? '#f87171' : p.seller_count ? '#4ade80' : '#6b7280'}; font-weight: 600;">${p.seller_count ? p.seller_count : '-'}</td>
                            <td style="padding: 8px; text-align: right; color: #e5e7eb;">${p.reviews ? p.reviews.toLocaleString() : '-'}</td>
                            <td style="padding: 8px; text-align: center;">
                                ${p.rating ? `<span style="color: ${p.rating >= 4 ? '#4ade80' : p.rating >= 3 ? '#facc15' : '#f87171'};">⭐ ${p.rating}</span>` : '-'}
                            </td>
                            <td style="padding: 8px; text-align: center;">
                                <span style="color: #9ca3af; font-family: monospace; font-size: 9px;">${p.asin}</span>
                            </td>
                        </tr>
                        `;
                    }).join('');
                }
            });
        });

        // Add hover effect to rows
        const rows = document.querySelectorAll('#sv-products-tbody tr');
        rows.forEach(row => {
            row.addEventListener('mouseenter', () => row.style.background = '#2d3a4f');
            row.addEventListener('mouseleave', () => {
                const idx = Array.from(rows).indexOf(row);
                row.style.background = idx % 2 === 0 ? '#232f3e' : '#283547';
            });
        });
    }
}

// Listen for URL changes (SPA navigation)
let lastUrl = window.location.href;
new MutationObserver(() => {
    const currentUrl = window.location.href;
    if (currentUrl !== lastUrl) {
        lastUrl = currentUrl;
        if (isProductPage()) {
            initializeAnalyzer();
        } else if (isSearchPage()) {
            initializeSearchAnalyzer();
        }
    }
}).observe(document.body, { subtree: true, childList: true });

// Listen for custom event from Shadow UI to open calculator
document.addEventListener('open-fba-calculator', () => {
    openFBACalculator();
});
