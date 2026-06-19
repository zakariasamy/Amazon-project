// Google Planner UI - Interactive Seasonality & Forecast Overlay
// Renders keyword volume projections directly inside the Amazon tab.

class GooglePlannerUI {
    constructor() {
        this.panel = null;
        this.backdrop = null;
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
     * Open the Google Keyword Planner overlay panel
     */
    async open(params = {}) {
        if (this.panel) return;

        const isArabic = this.isArabic();

        // 1. Create Backdrop
        this.backdrop = document.createElement('div');
        this.backdrop.id = 'google-planner-backdrop';
        this.backdrop.style.cssText = `
            position: fixed;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: rgba(15, 23, 42, 0.75);
            backdrop-filter: blur(4px);
            z-index: 9999998;
            transition: opacity 0.3s ease;
        `;
        document.body.appendChild(this.backdrop);

        // 2. Create Panel
        this.panel = document.createElement('div');
        this.panel.id = 'google-planner-panel';
        this.panel.style.cssText = `
            position: fixed;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            width: 90vw;
            max-width: 1050px;
            max-height: 85vh;
            background: #0f172a;
            border: 1px solid #334155;
            border-radius: 16px;
            z-index: 9999999;
            box-shadow: 0 25px 50px -12px rgba(0,0,0,0.5);
            font-family: 'Inter', -apple-system, system-ui, sans-serif;
            display: flex;
            flex-direction: column;
            overflow: hidden;
            color: #f1f5f9;
            direction: ${isArabic ? 'rtl' : 'ltr'};
            text-align: ${isArabic ? 'right' : 'left'};
        `;

        this.panel.innerHTML = `
            <!-- Header -->
            <div style="
                padding: 18px 24px;
                background: linear-gradient(135deg, #1e293b 0%, #0f172a 100%);
                border-bottom: 1px solid #334155;
                display: flex;
                align-items: center;
                justify-content: space-between;
                gap: 16px;
            ">
                <div style="display: flex; align-items: center; gap: 12px;">
                    <span style="font-size: 28px;">💡</span>
                    <div>
                        <div style="font-weight: 800; color: #fff; font-size: 18px; letter-spacing: -0.5px;">
                            ${isArabic ? 'توقع المبيعات والكلمات المفتاحية' : 'Google Keyword Planner Forecast'}
                        </div>
                        <div style="color: #94a3b8; font-size: 12px; margin-top: 2px;">
                            ${isArabic ? 'تحليل الكلمات المفتاحية وموسمية المبيعات' : 'Seasonality trends & purchase intent analytics'}
                        </div>
                    </div>
                </div>
                <button id="google-planner-close" style="
                    background: #334155;
                    border: none;
                    color: #94a3b8;
                    width: 32px;
                    height: 32px;
                    border-radius: 8px;
                    cursor: pointer;
                    font-size: 20px;
                    display: flex;
                    align-items: center;
                    justify-content: center;
                    transition: all 0.2s;
                ">×</button>
            </div>

            <!-- Content Area -->
            <div id="google-planner-content" style="flex: 1; overflow-y: auto; padding: 24px; position: relative;">
                <!-- Loading State -->
                <div id="google-planner-loader" style="
                    display: flex;
                    flex-direction: column;
                    align-items: center;
                    justify-content: center;
                    padding: 80px 20px;
                    gap: 16px;
                ">
                    <div style="
                        width: 48px;
                        height: 48px;
                        border: 4px solid rgba(240, 136, 4, 0.15);
                        border-top-color: #f08804;
                        border-radius: 50%;
                        animation: google-spin 1s linear infinite;
                    "></div>
                    <div style="font-weight: 600; font-size: 14px; color: #f08804;">
                        ${isArabic ? 'جاري الاتصال بـ Google Ads API وتشغيل خوارزميات التوقع...' : 'Querying Google Ads API & running forecasting algorithms...'}
                    </div>
                </div>
            </div>
        `;

        // Inject spin animation style
        const styleNode = document.createElement('style');
        styleNode.textContent = `
            @keyframes google-spin {
                to { transform: rotate(360deg); }
            }
            #google-planner-close:hover {
                background: #475569;
                color: #fff;
            }
        `;
        this.panel.appendChild(styleNode);
        document.body.appendChild(this.panel);

        // Add close listeners
        this.backdrop.addEventListener('click', () => this.close());
        this.panel.querySelector('#google-planner-close').addEventListener('click', () => this.close());

        // 3. Query Backend API
        await this.fetchForecast(params);
    }

    /**
     * Query API to retrieve real forecasted data
     */
    async asyncPost(url, payload) {
        let headers = {
            'Content-Type': 'application/json',
            'X-Requested-With': 'XMLHttpRequest'
        };
        
        // Retrieve sanctum headers if getAuthHeaders exists
        if (typeof getAuthHeaders === 'function') {
            try {
                const authHeaders = await getAuthHeaders();
                headers = { ...headers, ...authHeaders };
            } catch (e) {
                console.warn('[Google Planner] Auth headers missing, calling backend natively', e);
            }
        }

        const response = await fetch(url, {
            method: 'POST',
            headers: headers,
            body: JSON.stringify(payload)
        });

        if (!response.ok) {
            let errMsg = `Server returned status: ${response.status}`;
            try {
                const errData = await response.json();
                if (errData && errData.error) {
                    errMsg = errData.error;
                } else if (errData && errData.message) {
                    errMsg = errData.message;
                }
            } catch (jsonErr) {}
            throw new Error(errMsg);
        }
        return await response.json();
    }

    async fetchForecast(params) {
        const isArabic = this.isArabic();
        const contentDiv = this.panel.querySelector('#google-planner-content');

        try {
            const apiBase = 'http://127.0.0.1:8000';
            const res = await this.asyncPost(`${apiBase}/api/google-keyword-planner/forecast`, {
                keyword: params.keyword || '',
                asin: params.asin || '',
                category: params.category || 'default',
                marketplace: params.marketplace || 'amazon.com',
                bsr: params.bsr || 0,
                sales: params.sales || 0
            });

            if (res.success) {
                this.renderReport(res, params);
            } else {
                throw new Error('Algorithm processing failed.');
            }
        } catch (err) {
            console.error('[Google Planner] Error fetching report:', err);
            contentDiv.innerHTML = `
                <div style="
                    background: rgba(239, 68, 68, 0.15);
                    border: 1px solid rgba(239, 68, 68, 0.3);
                    color: #f87171;
                    padding: 16px;
                    border-radius: 12px;
                    font-size: 13.5px;
                    line-height: 1.6;
                ">
                    ❌ <strong>${isArabic ? 'خطأ في جلب تقرير Google Ads' : 'Google Ads API Error'}</strong>: 
                    <div style="margin-top: 8px;">${err.message}</div>
                </div>
            `;
        }
    }

    /**
     * Render the report contents
     */
    renderReport(res, params) {
        const isArabic = this.isArabic();
        const contentDiv = this.panel.querySelector('#google-planner-content');
        
        const beforeVol = parseInt(res.before_amazon_volume) || 0;
        const afterVol = res.keywords && res.keywords[0] ? parseInt(res.keywords[0].after_volume) : 0;
        const statusClass = 'google-alert-success';
        const bannerMsg = isArabic 
            ? '✅ تم الاتصال بـ Google Ads API بنجاح وحساب مقاييس الشراء الحقيقية!' 
            : '✅ Connected to Google Ads API successfully! Calculations computed using real search volumes.';

        // Styles for notification banners
        const styles = `
            .google-alert-success { background: rgba(16, 185, 129, 0.12); border: 1px solid rgba(16, 185, 129, 0.3); color: #34d399; }
            .google-alert-warning { background: rgba(245, 158, 11, 0.12); border: 1px solid rgba(245, 158, 11, 0.3); color: #fbbf24; }
            .google-alert-danger { background: rgba(239, 68, 68, 0.12); border: 1px solid rgba(239, 68, 68, 0.3); color: #f87171; }
        `;
        
        let styleSheet = document.createElement('style');
        styleSheet.textContent = styles;
        this.panel.appendChild(styleSheet);

        // Build HTML
        let html = `
            <!-- Banner Message -->
            <div class="${statusClass}" style="
                padding: 14px 18px;
                border-radius: 10px;
                font-size: 13px;
                font-weight: 500;
                margin-bottom: 20px;
                line-height: 1.5;
            ">
                ${bannerMsg}
            </div>

            <!-- Top Level Product Info Summary -->
            <div style="
                background: #1e293b;
                border: 1px solid #334155;
                border-radius: 12px;
                padding: 16px 20px;
                margin-bottom: 20px;
                display: grid;
                grid-template-columns: repeat(auto-fit, minmax(180px, 1fr));
                gap: 16px;
            ">
                <div>
                    <div style="font-size: 10px; text-transform: uppercase; color: #94a3b8; font-weight: 700;">${isArabic ? 'الكلمة المفتاحية' : 'Target Keyword'}</div>
                    <div style="font-size: 14px; font-weight: 700; color: #fff; margin-top: 4px;">${params.keyword || 'N/A'}</div>
                </div>
                <div>
                    <div style="font-size: 10px; text-transform: uppercase; color: #94a3b8; font-weight: 700;">ASIN</div>
                    <div style="font-size: 14px; font-weight: 700; color: #fff; margin-top: 4px;">${params.asin || 'N/A'}</div>
                </div>
                <div>
                    <div style="font-size: 10px; text-transform: uppercase; color: #94a3b8; font-weight: 700;">${isArabic ? 'الترتيب BSR' : 'BSR Rank'}</div>
                    <div style="font-size: 14px; font-weight: 700; color: #fff; margin-top: 4px;">${params.bsr ? Number(params.bsr).toLocaleString() : 'N/A'}</div>
                </div>
                <div>
                    <div style="font-size: 10px; text-transform: uppercase; color: #94a3b8; font-weight: 700;">${isArabic ? 'المبيعات الشهرية' : 'Amazon Monthly Sales'}</div>
                    <div style="font-size: 14px; font-weight: 700; color: #3b82f6; margin-top: 4px;">
                        ${res.estimated_sales ? Number(res.estimated_sales).toLocaleString() : '0'} 
                        ${res.is_sales_estimated ? `<span style="font-size:10px; color:#fbbf24;">(${isArabic ? 'تقديري' : 'Estimated'})</span>` : ''}
                    </div>
                </div>
            </div>

            <!-- Main Comparison Metrics -->
            <div style="
                display: grid;
                grid-template-columns: repeat(2, 1fr);
                gap: 20px;
                margin-bottom: 24px;
            ">
                <!-- Before -->
                <div style="
                    background: rgba(239, 68, 68, 0.04);
                    border: 1px solid rgba(239, 68, 68, 0.15);
                    border-left: 5px solid #ef4444;
                    border-radius: 12px;
                    padding: 20px;
                ">
                    <div style="font-size: 11px; text-transform: uppercase; font-weight: 700; color: #94a3b8; letter-spacing: 0.5px;">
                        ${isArabic ? 'حجم البحث التقليدي (غير الدقيق)' : 'Before Search Volume (Legacy)'}
                    </div>
                    <div style="font-size: 28px; font-weight: 800; color: #ef4444; margin-top: 8px;">
                        ${beforeVol.toLocaleString()}
                    </div>
                    <div style="font-size: 11px; color: #64748b; margin-top: 4px;">
                        ${isArabic ? 'تقدير مضخم يفترض تساوي الكلمات المفتاحية' : 'Inflated baseline assuming static conversion rate'}
                    </div>
                </div>

                <!-- After -->
                <div style="
                    background: rgba(16, 185, 129, 0.04);
                    border: 1px solid rgba(16, 185, 129, 0.15);
                    border-left: 5px solid #10b981;
                    border-radius: 12px;
                    padding: 20px;
                ">
                    <div style="font-size: 11px; text-transform: uppercase; font-weight: 700; color: #94a3b8; letter-spacing: 0.5px;">
                        ${isArabic ? 'حجم البحث الفعلي المعدل (دقيق)' : 'After Blended Volume (Damped)'}
                    </div>
                    <div style="font-size: 28px; font-weight: 800; color: #10b981; margin-top: 8px;">
                        ${afterVol.toLocaleString()}
                    </div>
                    <div style="font-size: 11px; color: #64748b; margin-top: 4px;">
                        ${isArabic ? 'معدل بوزن الشراء الفعلي من Google Keyword Planner' : 'Purchase-intent weighted by Google Keyword Planner'}
                    </div>
                </div>
            </div>

            <!-- Seasonality Forecast SVG Sparkline -->
            <div style="
                background: #1e293b;
                border: 1px solid #334155;
                border-radius: 12px;
                padding: 20px;
                margin-bottom: 24px;
            ">
                <h3 style="font-size: 14px; font-weight: 700; color: #fff; margin-bottom: 18px; display: flex; align-items:center; gap: 8px;">
                    📈 ${isArabic ? 'توقعات المبيعات لـ 12 شهراً القادمة (موسمية)' : '12-Month Seasonality & Sales Forecast'}
                </h3>
                <div style="height: 140px; width: 100%; position: relative;">
                    ${this.generateSVGChart(res.projections || [])}
                </div>
            </div>

            <!-- Keywords Suggestions Table -->
            <div style="
                background: #1e293b;
                border: 1px solid #334155;
                border-radius: 12px;
                overflow: hidden;
            ">
                <div style="
                    padding: 16px 20px;
                    border-bottom: 1px solid #334155;
                    font-weight: 700;
                    color: #fff;
                    font-size: 14px;
                ">
                    💡 ${isArabic ? 'اقتراحات الكلمات المفتاحية ومؤشرات النية الشرائية' : 'Google Planner Suggestion & Intended Dampening'}
                </div>
                <div style="overflow-x: auto;">
                    <table style="width: 100%; border-collapse: collapse; text-align: left; font-size: 13px;">
                        <thead>
                            <tr style="background: rgba(0,0,0,0.15); border-bottom: 1px solid #334155;">
                                <th style="padding: 12px 20px; color: #94a3b8; font-weight: 700; text-align: inherit;">${isArabic ? 'الكلمة المقترحة' : 'Keyword Idea'}</th>
                                <th style="padding: 12px 20px; color: #94a3b8; font-weight: 700; text-align: inherit;">${isArabic ? 'حجم بحث Google' : 'Google Vol'}</th>
                                <th style="padding: 12px 20px; color: #94a3b8; font-weight: 700; text-align: inherit;">${isArabic ? 'نسبة النية الشرائية' : 'Amazon Intent (AIR)'}</th>
                                <th style="padding: 12px 20px; color: #94a3b8; font-weight: 700; text-align: inherit;">${isArabic ? 'الحجم القديم' : 'Before Vol'}</th>
                                <th style="padding: 12px 20px; color: #94a3b8; font-weight: 700; text-align: inherit;">${isArabic ? 'الحجم المعدل' : 'After Vol'}</th>
                                <th style="padding: 12px 20px; color: #94a3b8; font-weight: 700; text-align: inherit;">${isArabic ? 'مزايدة CPC المقترحة' : 'CPC Bid'}</th>
                            </tr>
                        </thead>
                        <tbody>
                            ${(res.keywords || []).map(kw => {
                                const intentVal = parseFloat(kw.amazon_intent_ratio) || 0;
                                const badgeBg = intentVal > 15 ? 'rgba(16, 185, 129, 0.15)' : 'rgba(245, 158, 11, 0.15)';
                                const badgeColor = intentVal > 15 ? '#10b981' : '#f59e0b';
                                return `
                                    <tr style="border-bottom: 1px solid #334155;">
                                        <td style="padding: 12px 20px; font-weight: 600; color: #3b82f6;">${kw.keyword}</td>
                                        <td style="padding: 12px 20px;">${parseInt(kw.google_volume).toLocaleString()}</td>
                                        <td style="padding: 12px 20px;">
                                            <span style="background: ${badgeBg}; color: ${badgeColor}; padding: 3px 8px; border-radius: 6px; font-size: 11px; font-weight: 700;">
                                                ${kw.amazon_intent_ratio}
                                            </span>
                                        </td>
                                        <td style="padding: 12px 20px; text-decoration: line-through; color: #64748b;">${parseInt(kw.before_volume).toLocaleString()}</td>
                                        <td style="padding: 12px 20px; font-weight: 700; color: #f08804;">${parseInt(kw.after_volume).toLocaleString()}</td>
                                        <td style="padding: 12px 20px; font-weight: 600;">$${parseFloat(kw.suggested_bid).toFixed(2)}</td>
                                    </tr>
                                `;
                            }).join('')}
                        </tbody>
                    </table>
                </div>
            </div>
        `;

        contentDiv.innerHTML = html;
    }

    /**
     * Generate a responsive, modern SVG line and area chart for seasonality
     */
    generateSVGChart(projections) {
        if (!projections || projections.length === 0) return '';

        const months = projections.map(p => p.month);
        const data = projections.map(p => p.expected_sales);
        const maxVal = Math.max(...data, 10);
        const height = 100;
        const width = 950;
        const padding = 20;

        // Calculate points
        const points = data.map((val, idx) => {
            const x = padding + (idx * ((width - (padding * 2)) / (data.length - 1)));
            const y = (height + padding) - ((val / maxVal) * height);
            return { x, y, val, month: months[idx] };
        });

        // Path definitions
        const pathData = points.map((p, idx) => `${idx === 0 ? 'M' : 'L'} ${p.x} ${p.y}`).join(' ');
        const areaPathData = `${pathData} L ${points[points.length - 1].x} ${height + padding} L ${points[0].x} ${height + padding} Z`;

        let pointsHtml = '';
        let labelsHtml = '';

        points.forEach((p, idx) => {
            // Draw points
            pointsHtml += `
                <circle cx="${p.x}" cy="${p.y}" r="4" fill="#007185" stroke="#ffffff" stroke-width="1.5">
                    <title>${p.month}: ${p.val.toLocaleString()} units</title>
                </circle>
            `;

            // Draw month labels
            labelsHtml += `
                <text x="${p.x}" y="${height + padding + 15}" font-size="10" fill="#94a3b8" text-anchor="middle" font-weight="600">
                    ${p.month}
                </text>
            `;
        });

        return `
            <svg viewBox="0 0 ${width} ${height + padding + 25}" style="width: 100%; height: 100%; overflow: visible;">
                <!-- Gradient Area -->
                <defs>
                    <linearGradient id="chartGrad" x1="0" y1="0" x2="0" y2="1">
                        <stop offset="0%" stop-color="#f08804" stop-opacity="0.25"/>
                        <stop offset="100%" stop-color="#f08804" stop-opacity="0.0"/>
                    </linearGradient>
                </defs>
                <path d="${areaPathData}" fill="url(#chartGrad)" />

                <!-- Grid baseline -->
                <line x1="${padding}" y1="${height + padding}" x2="${width - padding}" y2="${height + padding}" stroke="#334155" stroke-dasharray="3 3" />

                <!-- Line -->
                <path d="${pathData}" fill="none" stroke="#f08804" stroke-width="3" stroke-linecap="round" stroke-linejoin="round" />

                <!-- Points & Labels -->
                ${pointsHtml}
                ${labelsHtml}
            </svg>
        `;
    }

    /**
     * Close the modal and cleanup
     */
    close() {
        if (this.backdrop) {
            this.backdrop.remove();
            this.backdrop = null;
        }
        if (this.panel) {
            this.panel.remove();
            this.panel = null;
        }
    }
}
