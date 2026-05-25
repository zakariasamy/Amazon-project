// Shadow UI - Floating Analytics Dashboard
class ShadowUI {
  constructor() {
    this.panel = null;
    this.isVisible = false;
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
      // dynamic text translations if any
    };

    return translations[key.toLowerCase().trim()] || key;
  }

  /**
   * Translate a DOM container's text nodes and placeholders to Arabic dynamically
   */
  translateDOM(container) {
    if (!this.isArabic() || !container) return;

    // Apply RTL styling to container
    container.style.direction = 'rtl';
    container.style.textAlign = 'right';

    const translations = {
        'reverse asin': 'تحليل الأسين العكسي (Reverse ASIN)',
        'amazon analyzer': 'محلل أمازون',
        'minimize': 'تصغير',
        'close': 'إغلاق',
        'copy keywords': 'نسخ الكلمات المفتاحية',
        'copy': 'نسخ',
        'export': 'تصدير',
        'full calculator': 'الحاسبة الكاملة',
        'your cogs:': 'تكلفة البضاعة (COGS):',
        'profit/unit': 'الربح/الوحدة',
        'monthly profit': 'الربح الشهري',
        'annual profit': 'الربح السنوي',
        'total fees': 'إجمالي الرسوم',
        'fba:': 'الشحن من قبل أمازون (FBA):',
        'referral:': 'رسوم الإحالة:',
        'storage:': 'التخزين:',
        'monthly sales': 'المبيعات الشهرية',
        'daily sales': 'المبيعات اليومية',
        'monthly revenue': 'الإيرادات الشهرية',
        'confidence': 'الثقة',
        'units': 'وحدات',
        'units/day': 'وحدة/يوم',
        'projected': 'متوقعة',
        'per unit': 'لكل وحدة',
        'competition': 'المنافسة',
        'score:': 'الدرجة:',
        'reviews': 'تقييمات',
        'review velocity': 'سرعة التقييمات',
        'market saturated': 'السوق مشبع',
        'quick assessment': 'التقييم السريع',
        'seller feedback (optional)': 'تقييم البائع (اختياري)',
        'are you the seller? help improve our estimates by sharing your actual sales data.': 'هل أنت البائع؟ ساعدنا في تحسين تقديراتنا من خلال مشاركة مبيعاتك الفعلية.',
        'our estimate:': 'تقديرنا:',
        'your actual sales:': 'مبيعاتك الفعلية:',
        'submit feedback': 'إرسال التقييم',
        'per last 30 days': 'خلال آخر ٣٠ يوماً',
        'units sold': 'الوحدات المباعة',
        'price': 'السعر',
        'rating': 'التقييم',
        'bsr': 'ترتيب المبيعات (BSR)',
        'seller': 'البائع',
        'sellers': 'البائعين',
        'sales & revenue': 'المبيعات والإيرادات',
        'profit analysis': 'تحليل الأرباح',
        'copied!': 'تم النسخ!',
        'discovering keywords...': 'جاري اكتشاف الكلمات المفتاحية...',
        'keyword analysis': 'تحليل الكلمات المفتاحية',
        'keywords analyzed': 'كلمة مفتاحية تم تحليلها',
        'min volume': 'الحد الأدنى لحجم البحث',
        'max volume': 'الحد الأقصى لحجم البحث',
        'max ads': 'أقصى عدد للإعلانات',
        'max kd': 'أقصى صعوبة (KD)',
        'rank': 'الترتيب',
        'volume': 'حجم البحث',
        'kd': 'الصعوبة (KD)',
        'ads': 'الإعلانات',
        'avg $': 'متوسط السعر',
        'avg bsr': 'متوسط الترتيب',
        'gaps identified': 'الثغرات المحددة',
        'coping keywords...': 'جاري نسخ الكلمات المفتاحية...',
        'cogs': 'تكلفة البضائع',
        'active': 'مفعّل',
        'opportunity score': 'درجة الفرصة',
        'recommended': 'موصى به',
        'yes': 'نعم',
        'no': 'لا',
        'submitting...': 'جاري الإرسال...',
        'thank you! error was': 'شكرًا لك! نسبة الخطأ كانت',
        'please enter a valid number': 'الرجاء إدخال رقم صحيح',
        'api not available': 'واجهة برمجة التطبيقات (API) غير متاحة',
        'please login to submit feedback': 'الرجاء تسجيل الدخول لإرسال التقييم',
        'margin': 'هامش',
        'roi': 'عائد الاستثمار',
        'est.': 'تقديري'
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
   * Display analysis results in shadow panel
   */
  display(analysis, mode = 'full') {
    if (this.panel) {
      this.remove();
    }

    if (mode === 'keywords') {
      this.panel = this.createKeywordsPanel(analysis);
    } else {
      this.panel = this.createPanel(analysis);
    }

    document.body.appendChild(this.panel);
    this.translateDOM(this.panel);
    this.isVisible = true;

    // Fade in animation
    setTimeout(() => {
      this.panel.classList.add('visible');

      // Auto-trigger keywords if in keywords mode
      if (mode === 'keywords') {
        this.discoverKeywords(analysis);
      }
    }, 10);
  }

  /**
   * Render analysis results into a specific container (Inline Mode)
   */
  renderToContainer(container, analysis, mode = 'full') {
    if (!container) return;

    // Clear container
    container.innerHTML = '';
    container.style.display = 'block';

    // Create wrapper with shadow panel classes for styling compatibility
    const wrapper = document.createElement('div');
    wrapper.className = 'analyzer-shadow-panel analyzer-inline-mode';
    // Reset specific floating styles
    wrapper.style.position = 'static';
    wrapper.style.width = '100%';
    wrapper.style.maxWidth = '100%';
    wrapper.style.margin = '0';
    wrapper.style.boxShadow = 'none';
    wrapper.style.borderRadius = '0';
    wrapper.style.background = 'transparent';
    wrapper.style.transform = 'none';
    wrapper.style.opacity = '1';
    // Fix for clipping issues:
    wrapper.style.maxHeight = 'none';
    wrapper.style.overflow = 'visible';
    wrapper.style.height = 'auto';

    if (mode === 'keywords') {
      wrapper.innerHTML = `
            <div class="analyzer-panel-body" style="max-height: none; height: auto; overflow: visible;">
                ${this.renderKeywordDiscovery(analysis)}
            </div>
        `;
      // Store panel reference so discoverKeywords finds the container
      this.panel = wrapper;
      container.appendChild(wrapper);
      this.discoverKeywords(analysis);
    } else {
      wrapper.innerHTML = `
            <div class="analyzer-panel-body" style="max-height: none; height: auto; overflow: visible;">
                ${this.renderProductInfo(analysis)}
                ${this.renderSalesMetrics(analysis)}
                ${this.renderProfitMetrics(analysis)}
                <div class="analyzer-two-col-grid">
                    ${this.renderCompetitionMetrics(analysis)}
                    ${this.renderOpportunityScore(analysis)}
                    ${this.renderSellerFeedback(analysis)}
                </div>
            </div>
            <div class="analyzer-panel-footer">
                <button class="analyzer-btn analyzer-btn-secondary" id="analyzer-copy">📋 Copy</button>
                <button class="analyzer-btn analyzer-btn-primary" id="analyzer-export">📊 Export</button>
            </div>
        `;
      container.appendChild(wrapper);
      this.translateDOM(container);

      // Attach listeners
      wrapper.querySelector('#analyzer-copy').addEventListener('click', () => this.copyToClipboard(analysis));
      wrapper.querySelector('#analyzer-export').addEventListener('click', () => this.exportReport(analysis));

      // Init interactive elements
      this.panel = wrapper; // Set panel ref for interactions

      // Recalculate profit listener (dynamic updates for COGS, shipping, storage, ads, referral, and VAT)
      const recalculateProfit = () => {
        const activeTab = wrapper.querySelector('.fulfillment-tab.active');
        const mode = activeTab ? activeTab.dataset.mode : 'fba';
        
        const cogsVal = parseFloat(wrapper.querySelector('#custom-cogs-input')?.value || 0);
        const shippingVal = parseFloat(wrapper.querySelector('#custom-shipping-input')?.value || 0);
        const storageVal = mode === 'fbm' ? parseFloat(wrapper.querySelector('#custom-storage-input')?.value || 0) : 0;
        const adsVal = parseFloat(wrapper.querySelector('#custom-ads-input')?.value || 0);
        const referralVal = parseFloat(wrapper.querySelector('#custom-referral-input')?.value || 0);
        const applyVat = wrapper.querySelector('#custom-vat-checkbox')?.checked ?? true;
        this.updateProfitMetrics(cogsVal, shippingVal, storageVal, adsVal, referralVal, applyVat, analysis, wrapper);
      };

      ['#custom-cogs-input', '#custom-shipping-input', '#custom-storage-input', '#custom-ads-input', '#custom-referral-input'].forEach(selector => {
        wrapper.querySelector(selector)?.addEventListener('input', recalculateProfit);
      });
      wrapper.querySelector('#custom-vat-checkbox')?.addEventListener('change', recalculateProfit);

      // Switcher listener
      const tabs = wrapper.querySelectorAll('.fulfillment-tab');
      const storageGroup = wrapper.querySelector('#storage-input-group');
      const shippingLabel = wrapper.querySelector('#shipping-input-label');
      
      tabs.forEach(tab => {
        tab.addEventListener('click', () => {
          tabs.forEach(t => {
            t.classList.remove('active');
            t.style.background = 'transparent';
            t.style.color = '#94a3b8';
          });
          tab.classList.add('active');
          tab.style.background = '#34d399';
          tab.style.color = '#0f172a';
          
          const mode = tab.dataset.mode;
          
          // Toggle Storage Visibility
          if (mode === 'fbm') {
            if (storageGroup) storageGroup.style.display = 'flex';
          } else {
            if (storageGroup) storageGroup.style.display = 'none';
          }
          
          // Toggle Shipping Label
          if (mode === 'fba') {
            if (shippingLabel) shippingLabel.textContent = this.isArabic() ? 'تكلفة الشحن والتعبئة للمنتج:' : 'FBA Shipping & Handling (per product):';
          } else {
            if (shippingLabel) shippingLabel.textContent = this.isArabic() ? 'تكلفة الشحن (للمنتج):' : 'Shipping Cost (per product):';
          }
          
          recalculateProfit();
        });
      });

      // Run initial recalculation to reflect default 0.00 inputs
      recalculateProfit();

      // Feedback
      const feedbackBtn = wrapper.querySelector('#submit-feedback-btn');
      if (feedbackBtn) {
        feedbackBtn.addEventListener('click', () => this.submitSellerFeedback(analysis, wrapper));
      }

      // Detailed Calculator Listener (Inline Mode)
      const calcBtn = wrapper.querySelector('#open-detailed-calc-btn');
      if (calcBtn) {
        calcBtn.addEventListener('click', () => {
          document.dispatchEvent(new CustomEvent('open-fba-calculator'));
        });
      }

      // Keywords toggle in inline mode?
      // It's already rendered, but maybe the button in footer could scroll to it?
    }
  }

  /**
   * Create a simplified panel for Keywords only
   */
  createKeywordsPanel(analysis) {
    const panel = document.createElement('div');
    panel.id = 'amazon-analyzer-shadow-panel';
    panel.className = 'analyzer-shadow-panel analyzer-mode-keywords'; // Add class for specific styling

    panel.innerHTML = `
      <div class="analyzer-panel-header">
        <div class="analyzer-logo">
          <span class="analyzer-icon">🔑</span>
          <span class="analyzer-title">Reverse ASIN</span>
        </div>
        <div class="analyzer-actions">
          <button class="analyzer-action-btn analyzer-close-btn" id="analyzer-close" title="Close">×</button>
        </div>
      </div>

      <div class="analyzer-panel-body">
        ${this.renderKeywordDiscovery(analysis)}
      </div>

      <div class="analyzer-panel-footer">
        <button class="analyzer-btn analyzer-btn-secondary" id="analyzer-copy">
          📋 Copy Keywords
        </button>
      </div>
    `;

    // Event listeners
    panel.querySelector('#analyzer-close').addEventListener('click', () => this.remove());
    panel.querySelector('#analyzer-copy').addEventListener('click', () => this.copyKeywordsToClipboard());

    // Note: discoverKeywords is triggered automatically via display()

    return panel;
  }

  copyKeywordsToClipboard() {
    const items = Array.from(this.panel.querySelectorAll('.keyword-item'));
    if (items.length === 0) return;

    const text = items.map(item => {
      const kw = item.querySelector('.keyword-text').textContent;
      const pos = item.querySelector('.keyword-position').textContent;
      return `${kw}\t${pos}`;
    }).join('\n');

    navigator.clipboard.writeText(text).then(() => {
      const btn = this.panel.querySelector('#analyzer-copy');
      const original = btn.innerHTML;
      btn.innerHTML = this.isArabic() ? '✅ تم النسخ!' : '✅ Copied!';
      setTimeout(() => btn.innerHTML = original, 2000);
    });
  }

  /**
   * Create the shadow panel with results
   */
  createPanel(analysis) {
    const panel = document.createElement('div');
    panel.id = 'amazon-analyzer-shadow-panel';
    panel.className = 'analyzer-shadow-panel';

    panel.innerHTML = `
      <div class="analyzer-panel-header">
        <div class="analyzer-logo">
          <span class="analyzer-icon">🔍</span>
          <span class="analyzer-title">Amazon Analyzer</span>
        </div>
        <div class="analyzer-actions">
          <button class="analyzer-action-btn" id="analyzer-minimize" title="Minimize">_</button>
          <button class="analyzer-action-btn analyzer-close-btn" id="analyzer-close" title="Close">×</button>
        </div>
      </div>

      <div class="analyzer-panel-body">
        ${this.renderProductInfo(analysis)}
        ${this.renderSalesMetrics(analysis)}
        ${this.renderProfitMetrics(analysis)}
        <div class="analyzer-two-col-grid">
            ${this.renderCompetitionMetrics(analysis)}
            ${this.renderOpportunityScore(analysis)}
            ${this.renderSellerFeedback(analysis)}
        </div>
        ${this.renderKeywordDiscovery(analysis)}
      </div>

      <div class="analyzer-panel-footer">
        <button class="analyzer-btn analyzer-btn-secondary" id="analyzer-keywords" title="Find keywords this product ranks for">
          🔑 Reverse ASIN
        </button>
        <button class="analyzer-btn analyzer-btn-secondary" id="analyzer-copy">
          📋 Copy
        </button>
        <button class="analyzer-btn analyzer-btn-primary" id="analyzer-export">
          📊 Export
        </button>
      </div>
      
      <!-- Restore Toggle (Hidden by default) -->
      <div class="analyzer-restore-handle" id="analyzer-restore" style="display: none;">
        <span class="analyzer-icon">🔍</span>
      </div>
    `;

    // Event listeners
    panel.querySelector('#analyzer-close').addEventListener('click', () => this.remove());
    panel.querySelector('#analyzer-minimize').addEventListener('click', () => this.minimize());
    panel.querySelector('#analyzer-restore').addEventListener('click', () => this.restore());
    panel.querySelector('#analyzer-copy').addEventListener('click', () => this.copyToClipboard(analysis));
    panel.querySelector('#analyzer-export').addEventListener('click', () => this.exportReport(analysis));
    panel.querySelector('#analyzer-keywords').addEventListener('click', () => this.discoverKeywords(analysis));

    // Recalculate profit listener (dynamic updates for COGS, shipping, storage, ads, referral, and VAT checkbox in floating panel)
    const recalculateProfit = () => {
      const activeTab = panel.querySelector('.fulfillment-tab.active');
      const mode = activeTab ? activeTab.dataset.mode : 'fba';
      
      const cogsVal = parseFloat(panel.querySelector('#custom-cogs-input')?.value || 0);
      const shippingVal = parseFloat(panel.querySelector('#custom-shipping-input')?.value || 0);
      const storageVal = mode === 'fbm' ? parseFloat(panel.querySelector('#custom-storage-input')?.value || 0) : 0;
      const adsVal = parseFloat(panel.querySelector('#custom-ads-input')?.value || 0);
      const referralVal = parseFloat(panel.querySelector('#custom-referral-input')?.value || 0);
      const applyVat = panel.querySelector('#custom-vat-checkbox')?.checked ?? true;
      this.updateProfitMetrics(cogsVal, shippingVal, storageVal, adsVal, referralVal, applyVat, analysis, panel);
    };

    ['#custom-cogs-input', '#custom-shipping-input', '#custom-storage-input', '#custom-ads-input', '#custom-referral-input'].forEach(selector => {
      panel.querySelector(selector)?.addEventListener('input', recalculateProfit);
    });
    panel.querySelector('#custom-vat-checkbox')?.addEventListener('change', recalculateProfit);

    // Switcher listener
    const tabs = panel.querySelectorAll('.fulfillment-tab');
    const storageGroup = panel.querySelector('#storage-input-group');
    const shippingLabel = panel.querySelector('#shipping-input-label');
    
    tabs.forEach(tab => {
      tab.addEventListener('click', () => {
        tabs.forEach(t => {
          t.classList.remove('active');
          t.style.background = 'transparent';
          t.style.color = '#94a3b8';
        });
        tab.classList.add('active');
        tab.style.background = '#34d399';
        tab.style.color = '#0f172a';
        
        const mode = tab.dataset.mode;
        
        // Toggle Storage Visibility
        if (mode === 'fbm') {
          if (storageGroup) storageGroup.style.display = 'flex';
        } else {
          if (storageGroup) storageGroup.style.display = 'none';
        }
        
        // Toggle Shipping Label
        if (mode === 'fba') {
          if (shippingLabel) shippingLabel.textContent = this.isArabic() ? 'تكلفة الشحن والتعبئة للمنتج:' : 'FBA Shipping & Handling (per product):';
        } else {
          if (shippingLabel) shippingLabel.textContent = this.isArabic() ? 'تكلفة الشحن (للمنتج):' : 'Shipping Cost (per product):';
        }
        
        recalculateProfit();
      });
    });

    // Run initial recalculation to reflect default 0.00 inputs
    recalculateProfit();

    // Seller Feedback Form Listener
    const feedbackBtn = panel.querySelector('#submit-feedback-btn');
    if (feedbackBtn) {
      feedbackBtn.addEventListener('click', () => this.submitSellerFeedback(analysis, panel));
    }

    // Detailed Calculator Listener
    const calcBtn = panel.querySelector('#open-detailed-calc-btn');
    if (calcBtn) {
      calcBtn.addEventListener('click', () => {
        document.dispatchEvent(new CustomEvent('open-fba-calculator'));
      });
    }

    return panel;
  }

  renderProductInfo(analysis) {
    const allRankings = analysis.bsrData?.allRankings || analysis.bsr?.allRankings || [];

    // Handle BSR which can be: object {rank, category}, number, or string
    let bsrValue = null;
    if (analysis.bsr && typeof analysis.bsr === 'object') {
      bsrValue = analysis.bsr.rank;
    } else if (analysis.bsr) {
      bsrValue = analysis.bsr;
    }

    const bsrInt = parseInt(bsrValue || '');
    const mainBSR = !isNaN(bsrInt) && bsrInt > 0 ? `#${bsrInt.toLocaleString()}` : 'N/A';
    const currency = analysis.currency || 'USD';

    const isArMode = this.isArabic();
    let titleHtml = '';
    
    if (isArMode && analysis.title_ar) {
      titleHtml = `
        <div class="analyzer-product-title-ar" style="margin: 0; font-size: 16px; font-weight: 800; color: #f8fafc; line-height: 1.4;">${this.truncate(analysis.title_ar, 70)}</div>
        <div class="analyzer-product-title" style="margin-top: 4px; font-size: 13px; color: #94a3b8; line-height: 1.4;">${this.truncate(analysis.title || 'Unknown Product', 70)}</div>
      `;
    } else {
      titleHtml = `
        <div class="analyzer-product-title" style="margin: 0; font-size: 16px; font-weight: 800; color: #f8fafc; line-height: 1.4;">${this.truncate(analysis.title || 'Unknown Product', 70)}</div>
        ${analysis.title_ar ? `<div class="analyzer-product-title-ar" style="margin-top: 4px; font-size: 13px; color: #94a3b8; line-height: 1.4;">${this.truncate(analysis.title_ar, 70)}</div>` : ''}
      `;
    }

    return `
      <div class="analyzer-section analyzer-product-section" style="padding: 18px 24px; border-radius: 12px; background: linear-gradient(135deg, #1e293b 0%, #0f172a 100%); border: 1px solid #334155;">
        <div class="analyzer-product-header" style="display: flex; justify-content: space-between; align-items: center; gap: 16px; flex-wrap: wrap; border-bottom: 1px solid #334155; padding-bottom: 14px; margin-bottom: 14px; direction: ${this.isArabic() ? 'rtl' : 'ltr'};">
          <div style="flex: 1; min-width: 250px; text-align: ${this.isArabic() ? 'right' : 'left'};">
            ${titleHtml}
          </div>
          
          <!-- Quick Stats Row (Inside Header for Side-by-Side placement) -->
          <div class="analyzer-quick-stats" style="display: flex; gap: 14px; flex-wrap: wrap; margin: 0; padding: 0; border: none; align-items: center; direction: ${this.isArabic() ? 'rtl' : 'ltr'};">
            <div class="analyzer-stat" style="display: flex; flex-direction: column; gap: 2px; text-align: center;">
              <span class="stat-label" style="font-size: 11px; color: #94a3b8; text-transform: uppercase; letter-spacing: 0.5px; font-weight: 600;">ASIN</span>
              <span class="stat-value" style="font-size: 14px; color: #e2e8f0; font-weight: 700; font-family: monospace;">${analysis.asin || 'N/A'}</span>
            </div>
            <div style="width: 1px; height: 24px; background: #334155;"></div>
            <div class="analyzer-stat" style="display: flex; flex-direction: column; gap: 2px; text-align: center;">
              <span class="stat-label" style="font-size: 11px; color: #94a3b8; text-transform: uppercase; letter-spacing: 0.5px; font-weight: 600;">${this.isArabic() ? 'السعر' : 'Price'}</span>
              <span class="stat-value stat-price" style="font-size: 14px; color: #34d399 !important; font-weight: 700;">${currency} ${analysis.price || '0'}</span>
            </div>
            <div style="width: 1px; height: 24px; background: #334155;"></div>
            <div class="analyzer-stat" style="display: flex; flex-direction: column; gap: 2px; text-align: center;">
              <span class="stat-label" style="font-size: 11px; color: #94a3b8; text-transform: uppercase; letter-spacing: 0.5px; font-weight: 600;">${this.isArabic() ? 'التقييم' : 'Rating'}</span>
              <span class="stat-value" style="font-size: 14px; color: #f59e0b; font-weight: 700;">⭐ ${analysis.rating || '0'}</span>
            </div>
            <div style="width: 1px; height: 24px; background: #334155;"></div>
            <div class="analyzer-stat" style="display: flex; flex-direction: column; gap: 2px; text-align: center;">
              <span class="stat-label" style="font-size: 11px; color: #94a3b8; text-transform: uppercase; letter-spacing: 0.5px; font-weight: 600;">${this.isArabic() ? 'تقييمات' : 'Reviews'}</span>
              <span class="stat-value" style="font-size: 14px; color: #e2e8f0; font-weight: 700;">${parseInt(analysis.reviewCount || 0).toLocaleString()}</span>
            </div>
            <div style="width: 1px; height: 24px; background: #334155;"></div>
            <div class="analyzer-stat" style="display: flex; flex-direction: column; gap: 2px; text-align: center;">
              <span class="stat-label" style="font-size: 11px; color: #94a3b8; text-transform: uppercase; letter-spacing: 0.5px; font-weight: 600;">${this.isArabic() ? 'ترتيب المبيعات (BSR)' : 'BSR'}</span>
              <span class="stat-value stat-bsr" style="font-size: 14px; color: #fb923c !important; font-weight: 700;">${mainBSR}</span>
            </div>
          </div>
        </div>
        
        <!-- Secondary Info Row -->
        <div class="analyzer-info-row" style="display: flex; flex-wrap: wrap; gap: 8px; margin-top: 12px; justify-content: flex-start; direction: ${this.isArabic() ? 'rtl' : 'ltr'};">
          ${analysis.brand ? `<span class="info-chip">🏷️ ${analysis.brand}</span>` : ''}
          <span class="info-chip">📁 ${analysis.category || 'N/A'}</span>
          ${analysis.dimensions ? `<span class="info-chip">📏 ${analysis.dimensions}</span>` : ''}
          ${analysis.weight ? `<span class="info-chip">⚖️ ${analysis.weight}</span>` : ''}
          <span class="info-chip" style="color: ${(analysis.sellerCount || 1) > 10 ? '#f87171' : '#34d399'};">🏪 ${analysis.sellerCount || 1} ${this.isArabic() ? 'بائع' : 'seller'}${(analysis.sellerCount || 1) !== 1 && !this.isArabic() ? 's' : ''}</span>
        </div>

        ${allRankings.length > 1 ? `
          <div class="analyzer-bsr-list" style="display: flex; flex-wrap: wrap; gap: 6px; margin-top: 10px; direction: ${this.isArabic() ? 'rtl' : 'ltr'};">
            ${allRankings.map((r, i) => `<span class="bsr-chip ${i === 0 ? 'primary' : ''}">#${r.rank.toLocaleString()} ${r.category}</span>`).join('')}
          </div>
        ` : ''}
      </div>
    `;
  }

  renderSalesMetrics(analysis) {
    const currency = analysis.currency || 'USD';
    return `
      <div class="analyzer-section" style="direction: ${this.isArabic() ? 'rtl' : 'ltr'}; text-align: ${this.isArabic() ? 'right' : 'left'};">
        <h3 class="analyzer-section-title">📈 ${this.isArabic() ? 'المبيعات والإيرادات' : 'Sales & Revenue'}</h3>
        <div class="analyzer-metrics-grid">
          <div class="analyzer-metric">
            <div class="analyzer-metric-label">${this.isArabic() ? 'المبيعات الشهرية' : 'Monthly Sales'}</div>
            <div class="analyzer-metric-value">${analysis.sales.monthly.toLocaleString()}</div>
            <div class="analyzer-metric-sublabel">${this.isArabic() ? 'وحدة' : 'units'}</div>
          </div>
          <div class="analyzer-metric">
            <div class="analyzer-metric-label">${this.isArabic() ? 'المبيعات اليومية' : 'Daily Sales'}</div>
            <div class="analyzer-metric-value">${analysis.sales.daily}</div>
            <div class="analyzer-metric-sublabel">${this.isArabic() ? 'وحدة/يوم' : 'units/day'}</div>
          </div>
          <div class="analyzer-metric">
            <div class="analyzer-metric-label">${this.isArabic() ? 'الإيرادات الشهرية' : 'Monthly Revenue'}</div>
            <div class="analyzer-metric-value">${currency} ${analysis.revenue.monthly.toLocaleString()}</div>
            <div class="analyzer-metric-sublabel">${currency} ${analysis.revenue.annual.toLocaleString()}/${this.isArabic() ? 'سنوياً' : 'yr'}</div>
          </div>
          <div class="analyzer-metric">
            <div class="analyzer-metric-label">${this.isArabic() ? 'الثقة' : 'Confidence'}</div>
            <div class="analyzer-metric-value analyzer-confidence-${analysis.sales.confidence}">
              ${this.getConfidenceBadge(analysis.sales.confidence)}
            </div>
          </div>
        </div>
      </div>
    `;
  }

  renderProfitMetrics(analysis) {
    const marginClass = this.getProfitClass(analysis.profit.margin);
    const currency = analysis.currency || 'USD';
    const price = parseFloat(analysis.price || 0);
    const estimatedCOGS = (price * 0.25).toFixed(2);
    const annualProfit = (analysis.profit.monthly * 12).toLocaleString();

    return `
      <div class="analyzer-section" style="direction: ${this.isArabic() ? 'rtl' : 'ltr'}; text-align: ${this.isArabic() ? 'right' : 'left'};">
        <h3 class="analyzer-section-title">💰 ${this.isArabic() ? 'تحليل الأرباح' : 'Profit Analysis'} <span class="estimate-badge" style="background: rgba(245, 158, 11, 0.15); color: #f59e0b;">⚠️ ${this.isArabic() ? 'تقديري' : 'Est.'}</span></h3>

        
        <!-- Fulfillment Mode Switcher -->
        <div class="fulfillment-switcher" style="display: flex; gap: 6px; margin-bottom: 16px; background: #0f172a; padding: 4px; border-radius: 8px; border: 1px solid #334155; width: fit-content; direction: ${this.isArabic() ? 'rtl' : 'ltr'};">
          <button class="fulfillment-tab active" data-mode="fba" style="padding: 6px 14px; border: none; background: #34d399; color: #0f172a; font-weight: 700; font-size: 11px; border-radius: 6px; cursor: pointer; transition: all 0.2s;">FBA</button>
          <button class="fulfillment-tab" data-mode="fbm" style="padding: 6px 14px; border: none; background: transparent; color: #94a3b8; font-weight: 700; font-size: 11px; border-radius: 6px; cursor: pointer; transition: all 0.2s;">FBM</button>
          <button class="fulfillment-tab" data-mode="easyship" style="padding: 6px 14px; border: none; background: transparent; color: #94a3b8; font-weight: 700; font-size: 11px; border-radius: 6px; cursor: pointer; transition: all 0.2s;">Easy Ship</button>
        </div>

        <!-- Profit Inputs Grid (3 columns, 2 rows) -->
        <div style="display: grid; grid-template-columns: repeat(3, 1fr); gap: 12px 16px; margin-bottom: 14px; align-items: flex-end;">
          <div class="cogs-input-group" style="display: flex; flex-direction: column; gap: 4px;">
            <label style="font-size: 11px; color: #cbd5e1; font-weight: 500;">${this.isArabic() ? 'تكلفة البضاعة (COGS):' : 'Your COGS:'}</label>
            <div style="display: flex; align-items: center; gap: 6px;">
              <input type="number" id="custom-cogs-input" placeholder="${estimatedCOGS}" step="0.01" min="0" value="0.00" style="width: 100%; padding: 8px 12px; background: #0f172a !important; border: 1px solid #475569 !important; border-radius: 8px; color: #34d399 !important; font-size: 14px; font-weight: 700; outline: none; transition: border-color 0.2s;" />
              <span style="font-size: 12px; color: #94a3b8; font-weight: 600;">${currency}</span>
            </div>
          </div>
          <div class="cogs-input-group" style="display: flex; flex-direction: column; gap: 4px;">
            <label id="shipping-input-label" style="font-size: 11px; color: #cbd5e1; font-weight: 500;">${this.isArabic() ? 'تكلفة الشحن والتعبئة للمنتج:' : 'FBA Shipping & Handling (per product):'}</label>
            <div style="display: flex; align-items: center; gap: 6px;">
              <input type="number" id="custom-shipping-input" placeholder="0.00" step="0.01" min="0" value="0.00" style="width: 100%; padding: 8px 12px; background: #0f172a !important; border: 1px solid #475569 !important; border-radius: 8px; color: #34d399 !important; font-size: 14px; font-weight: 700; outline: none; transition: border-color 0.2s;" />
              <span style="font-size: 12px; color: #94a3b8; font-weight: 600;">${currency}</span>
            </div>
          </div>
          <div class="cogs-input-group" id="storage-input-group" style="display: none; flex-direction: column; gap: 4px;">
            <label style="font-size: 11px; color: #cbd5e1; font-weight: 500;">${this.isArabic() ? 'تكلفة التخزين (للمنتج):' : 'Storage Cost (per product):'}</label>
            <div style="display: flex; align-items: center; gap: 6px;">
              <input type="number" id="custom-storage-input" placeholder="0.00" step="0.01" min="0" value="0.00" style="width: 100%; padding: 8px 12px; background: #0f172a !important; border: 1px solid #475569 !important; border-radius: 8px; color: #34d399 !important; font-size: 14px; font-weight: 700; outline: none; transition: border-color 0.2s;" />
              <span style="font-size: 12px; color: #94a3b8; font-weight: 600;">${currency}</span>
            </div>
          </div>
          <div class="cogs-input-group" style="display: flex; flex-direction: column; gap: 4px;">
            <label style="font-size: 11px; color: #cbd5e1; font-weight: 500;">${this.isArabic() ? 'إجمالي تكلفة إعلانات (للمنتج):' : 'Total Ad Spend (per product):'}</label>
            <div style="display: flex; align-items: center; gap: 6px;">
              <input type="number" id="custom-ads-input" placeholder="0.00" step="0.01" min="0" value="0.00" style="width: 100%; padding: 8px 12px; background: #0f172a !important; border: 1px solid #475569 !important; border-radius: 8px; color: #34d399 !important; font-size: 14px; font-weight: 700; outline: none; transition: border-color 0.2s;" />
              <span style="font-size: 12px; color: #94a3b8; font-weight: 600;">${currency}</span>
            </div>
          </div>
          <div class="cogs-input-group" style="display: flex; flex-direction: column; gap: 4px;">
            <label style="font-size: 11px; color: #cbd5e1; font-weight: 500;">${this.isArabic() ? 'ربح امازون (referral):' : 'Amazon Referral Fee:'}</label>
            <div style="display: flex; align-items: center; gap: 6px;">
              <input type="number" id="custom-referral-input" placeholder="0.00" step="0.01" min="0" value="0.00" style="width: 100%; padding: 8px 12px; background: #0f172a !important; border: 1px solid #475569 !important; border-radius: 8px; color: #34d399 !important; font-size: 14px; font-weight: 700; outline: none; transition: border-color 0.2s;" />
              <span style="font-size: 12px; color: #94a3b8; font-weight: 600;">${currency}</span>
            </div>
          </div>
          <div class="cogs-input-group" style="display: flex; flex-direction: column; gap: 8px; justify-content: center; height: 100%; padding-bottom: 6px;">
            <label style="font-size: 11px; color: #cbd5e1; font-weight: 600; display: flex; align-items: center; gap: 8px; cursor: pointer; user-select: none; margin: 0;">
              <input type="checkbox" id="custom-vat-checkbox" checked style="width: 16px; height: 16px; accent-color: #34d399 !important; cursor: pointer; flex-shrink: 0;" />
              <span>${this.isArabic() ? 'ضريبة 14% VAT' : '14% VAT'}</span>
            </label>
          </div>
        </div>
        
        <div style="display: flex; justify-content: flex-end; margin-bottom: 16px; margin-top: 4px;">
          <button id="open-detailed-calc-btn" style="background: #1e293b; border: 1px solid #475569; border-radius: 8px; padding: 6px 14px; font-size: 12px; cursor: pointer; color: #f1f5f9; display: flex; align-items: center; gap: 6px; transition: all 0.2s; font-weight: 600;">
            🧮 ${this.isArabic() ? 'الحاسبة الكاملة' : 'Full Calculator'}
          </button>
        </div>

        <!-- Profit Metrics - 4 columns -->
        <div class="analyzer-metrics-grid" id="profit-metrics-grid">
          <div class="analyzer-metric">
            <div class="analyzer-metric-label">${this.isArabic() ? 'الربح/الوحدة' : 'Profit/Unit'}</div>
            <div class="analyzer-metric-value ${marginClass}" id="profit-per-unit">${currency} ${analysis.profit.perUnit.toFixed(2)}</div>
            <div class="analyzer-metric-sublabel" id="profit-margin">${analysis.profit.margin.toFixed(1)}% ${this.isArabic() ? 'هامش' : 'margin'}</div>
          </div>
          <div class="analyzer-metric">
            <div class="analyzer-metric-label">${this.isArabic() ? 'الربح الشهري' : 'Monthly Profit'}</div>
            <div class="analyzer-metric-value ${marginClass}" id="monthly-profit">${currency} ${analysis.profit.monthly.toLocaleString()}</div>
            <div class="analyzer-metric-sublabel" id="roi-display">${analysis.profit.roi.toFixed(0)}% ROI</div>
          </div>
          <div class="analyzer-metric">
            <div class="analyzer-metric-label">${this.isArabic() ? 'الربح السنوي' : 'Annual Profit'}</div>
            <div class="analyzer-metric-value ${marginClass}" id="annual-profit">${currency} ${annualProfit}</div>
            <div class="analyzer-metric-sublabel">${this.isArabic() ? 'متوقعة' : 'projected'}</div>
          </div>
          <div class="analyzer-metric">
            <div class="analyzer-metric-label">${this.isArabic() ? 'إجمالي الرسوم' : 'Total Fees'}</div>
            <div class="analyzer-metric-value" id="total-fees-display">${currency} 0.00</div>
            <div class="analyzer-metric-sublabel">${this.isArabic() ? 'لكل وحدة' : 'per unit'}</div>
          </div>
        </div>
        
        <!-- Fee Breakdown - compact -->
        <div class="fee-breakdown" style="direction: ${this.isArabic() ? 'rtl' : 'ltr'};">
          <span>${this.isArabic() ? 'الشحن (FBA):' : 'FBA:'} ${currency} ${analysis.fees.fba.toFixed(2)}</span>
          <span>${this.isArabic() ? 'الإحالة:' : 'Referral:'} ${currency} ${analysis.fees.referral.toFixed(2)}</span>
          <span>${this.isArabic() ? 'التخزين:' : 'Storage:'} ${currency} ${analysis.fees.storage.toFixed(2)}/${this.isArabic() ? 'شهر' : 'mo'} ${analysis.fees.isEstimatedStorage ? '⚠️' : ''}</span>
        </div>
      </div>
    `;
  }

  renderCompetitionMetrics(analysis) {
    return '';
  }

  translateNoteMessage(message) {
    if (!message) return '';
    let msg = message.trim();
    
    // Low sales volume - demand unverified
    if (msg.includes('Low sales volume - demand unverified') || (msg.includes('Low sales volume') && msg.includes('unverified'))) {
      return 'حجم مبيعات منخفض - الطلب غير مؤكد';
    }
    
    // Excellent margins (XX%)
    if (msg.includes('Excellent margins')) {
      const match = msg.match(/\((\d+.*?)\)/);
      const pct = match ? match[1] : '';
      return `💰 هامش ربح ممتاز ${pct ? `(${pct})` : ''}`;
    }
    if (msg.includes('Excellent profit margin')) {
      const match = msg.match(/(\d+\.?\d*%)/);
      const pct = match ? match[1] : '';
      return `💰 هامش ربح ممتاز ${pct ? `(${pct})` : ''}`;
    }
    if (msg.includes('Low profit margin')) {
      const match = msg.match(/(\d+\.?\d*%)/);
      const pct = match ? match[1] : '';
      return `⚠️ هامش ربح منخفض ${pct ? `(${pct})` : ''}`;
    }

    // High rating (X★) - tough to compete
    if (msg.includes('High rating') && msg.includes('compete')) {
      const match = msg.match(/\((\d+.*?★)\)/);
      const rating = match ? match[1] : '';
      return `⚠️ تقييم مرتفع ${rating ? `(${rating})` : ''} - من الصعب المنافسة`;
    }

    // Very low competition - only X reviews
    if (msg.includes('Very low competition')) {
      const match = msg.match(/only\s+(\d+)\s+reviews/i);
      const reviews = match ? match[1] : '';
      return `🟢 منافسة منخفضة جداً ${reviews ? `- فقط ${reviews} تقييمات` : ''}`;
    }
    if (msg.includes('Low competition')) {
      const match = msg.match(/only\s+(\d+)\s+reviews/i);
      const reviews = match ? match[1] : '';
      return `🟢 منافسة منخفضة ${reviews ? `- فقط ${reviews} تقييمات` : ''}`;
    }
    if (msg.includes('High competition')) {
      const match = msg.match(/(\d+)\s+reviews/i);
      const reviews = match ? match[1] : '';
      return `⚠️ منافسة عالية ${reviews ? `- ${reviews} تقييمات` : ''}`;
    }

    // Saturated
    if (msg.includes('saturated') && msg.includes('critical')) {
      return '⚠️ يبدو أن السوق مشبع. التميز سيكون حاسماً';
    }
    if (msg.includes('Low competition') && msg.includes('presence')) {
      return '✅ منافسة منخفضة. فرصة جيدة لإثبات وجودك';
    }
    if (msg.includes('ROI potential')) {
      const match = msg.match(/(\d+.*?%)/);
      const roi = match ? match[1] : '';
      return `🚀 إمكانية تحقيق عائد استثماري قوي ${roi ? `(${roi})` : ''}`;
    }

    const localTranslations = {
      'excellent profit margin': 'هامش ربح ممتاز',
      'good profit margin': 'هامش ربح جيد',
      'low profit margin': 'هامش ربح منخفض',
      'low competition': 'منافسة منخفضة',
      'high competition': 'منافسة عالية',
      'high sales volume': 'حجم مبيعات مرتفع',
      'good sales volume': 'حجم مبيعات جيد',
      'low sales volume': 'حجم مبيعات منخفض'
    };

    const lower = msg.toLowerCase().replace(/[^a-z0-9\s]/g, '').trim();
    if (localTranslations[lower]) {
      return localTranslations[lower];
    }

    return message;
  }

  renderOpportunityScore(analysis) {
    if (!analysis.opportunity) analysis.opportunity = {};
    
    // Unify notes: if opportunity.notes is not defined, map insights or reasons
    if (!analysis.opportunity.notes) {
      analysis.opportunity.notes = [];
      if (analysis.insights && analysis.insights.length > 0) {
        analysis.opportunity.notes = analysis.insights.map(ins => ({
          type: ins.type,
          message: ins.message
        }));
      } else if (analysis.opportunity.reasons) {
        analysis.opportunity.notes = analysis.opportunity.reasons.map(reason => {
          let type = 'success';
          if (reason.includes('⚠️') || reason.includes('Low')) type = 'warning';
          return { type, message: reason };
        });
      }
    }

    const notes = analysis.opportunity.notes;
    const isArabic = this.isArabic();
    
    // Competition metrics to include inside Quick Assessment
    const compLevel = analysis.competition.level;
    const compScore = analysis.competition.score;
    const reviewCount = analysis.reviewCount;
    const velocity = analysis.competition.reviewVelocity;
    const isSaturated = analysis.competition.saturated;
    
    // Style competition label
    let compLabel = '';
    let compBg = '';
    let compText = '';
    if (compLevel === 'very_low') { compLabel = isArabic ? 'منافسة منخفضة جداً' : 'Very Low Competition'; compBg = 'rgba(16, 185, 129, 0.15)'; compText = '#34d399'; }
    else if (compLevel === 'low') { compLabel = isArabic ? 'منافسة منخفضة' : 'Low Competition'; compBg = 'rgba(52, 211, 153, 0.15)'; compText = '#34d399'; }
    else if (compLevel === 'medium') { compLabel = isArabic ? 'منافسة متوسطة' : 'Medium Competition'; compBg = 'rgba(245, 158, 11, 0.15)'; compText = '#fde68a'; }
    else if (compLevel === 'high') { compLabel = isArabic ? 'منافسة عالية' : 'High Competition'; compBg = 'rgba(239, 68, 68, 0.15)'; compText = '#fca5a5'; }
    else { compLabel = isArabic ? 'منافسة عالية جداً' : 'Very High Competition'; compBg = 'rgba(239, 68, 68, 0.2)'; compText = '#fca5a5'; }

    const borderSide = isArabic ? 'border-right' : 'border-left';
    const notesHtml = notes.map(note => {
      let nBg = 'rgba(16, 185, 129, 0.1)';
      let nBorder = '#10b981';
      let nText = '#a7f3d0';
      if (note.type === 'warning') { nBg = 'rgba(245, 158, 11, 0.1)'; nBorder = '#f59e0b'; nText = '#fde68a'; }
      else if (note.type === 'danger') { nBg = 'rgba(239, 68, 68, 0.1)'; nBorder = '#ef4444'; nText = '#fecaca'; }
      
      const translatedMsg = isArabic ? this.translateNoteMessage(note.message) : note.message;
      return `<div style="padding: 10px 14px; background: ${nBg}; ${borderSide}: 4px solid ${nBorder}; color: ${nText}; font-size: 13px; border-radius: 6px; line-height: 1.5; text-align: ${isArabic ? 'right' : 'left'};">${translatedMsg}</div>`;
    }).join('');

    return `
      <div class="analyzer-section" style="direction: ${isArabic ? 'rtl' : 'ltr'}; text-align: ${isArabic ? 'right' : 'left'};">
        <h3 class="analyzer-section-title">📋 ${isArabic ? 'التقييم السريع' : 'Quick Assessment'}</h3>
        
         <!-- Integrated Competition Metrics inside Quick Assessment -->
        <div style="display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 12px; background: #1e293b; padding: 12px 16px; border-radius: 10px; border: 1px solid #334155; margin-bottom: 14px;">
          <div style="display: flex; align-items: center; gap: 10px;">
            <span style="font-size: 13px; font-weight: 700; color: ${compText}; background: ${compBg}; padding: 4px 12px; border-radius: 20px;">
              ${compLabel}
            </span>
            <span style="font-size: 12px; color: #94a3b8; font-weight: 600;">
              ${isArabic ? 'الدرجة:' : 'Score:'} ${compScore}/5
            </span>
          </div>
          <div style="display: flex; gap: 10px; align-items: center; font-size: 12px; color: #cbd5e1; font-weight: 500;">
            <span>📝 ${reviewCount} ${isArabic ? 'تقييمات' : 'reviews'}</span>
            <span style="color: #475569;">•</span>
            <span>📈 ~${velocity}/month ${isArabic ? 'سرعة التقييمات' : 'review velocity'}</span>
            ${isSaturated ? `<span style="color: #ef4444; font-weight: 700; background: rgba(239, 68, 68, 0.1); padding: 2px 8px; border-radius: 4px; border: 1px solid rgba(239,68,68,0.2);">⚠️ ${isArabic ? 'السوق مشبع' : 'Market saturated'}</span>` : ''}
          </div>
        </div>

        <!-- Assessment Notes -->
        <div id="assessment-notes-container" style="display: flex; flex-direction: column; gap: 8px;">
          ${notesHtml}
        </div>
      </div>
    `;
  }

  renderInsights(analysis) {
    if (!analysis.insights || analysis.insights.length === 0) {
      return '';
    }

    return `
      <div class="analyzer-section">
        <h3 class="analyzer-section-title">💡 Key Insights</h3>
        <div class="analyzer-insights">
          ${analysis.insights.map(insight => `
            <div class="analyzer-insight analyzer-insight-${insight.type}">
              ${insight.message}
            </div>
          `).join('')}
        </div>
      </div>
    `;
  }

  renderKeywordDiscovery(analysis) {
    return `
      <div class="analyzer-section analyzer-keyword-section">
        <div id="keyword-results" class="keyword-results" style="display: block;">
          <div class="keyword-loading">🔄 Starting keyword discovery...</div>
        </div>
      </div>
    `;
  }

  renderSellerFeedback(analysis) {
    const currency = analysis.currency || 'USD';
    const estimatedSales = analysis.sales?.monthly || 0;

    return `
      <div class="analyzer-section analyzer-feedback-section">
        <h3 class="analyzer-section-title">📊 Seller Feedback (Optional)</h3>
        <div class="feedback-content">
          <p class="feedback-info">Are you the seller? Help improve our estimates by sharing your actual sales data.</p>
          <div class="feedback-form">
            <div class="feedback-row">
              <label>Our Estimate:</label>
              <span class="feedback-value">${estimatedSales.toLocaleString()} units/month</span>
            </div>
            <div class="feedback-row">
              <label for="actual-sales-input">Your Actual Sales:</label>
              <input type="number" id="actual-sales-input" placeholder="Units sold" min="0" class="feedback-input">
              <span class="feedback-hint">per last 30 days</span>
            </div>
            <button id="submit-feedback-btn" class="analyzer-btn analyzer-btn-secondary feedback-btn">
              📤 Submit Feedback
            </button>
            <div id="feedback-message" class="feedback-message"></div>
          </div>
        </div>
      </div>
    `;
  }

  async discoverKeywords(analysis) {
    const resultsDiv = this.panel.querySelector('#keyword-results');
    if (!resultsDiv) return;

    resultsDiv.style.display = 'block';
    resultsDiv.innerHTML = '<div class="keyword-loading">🔄 Discovering keywords... (This scrapes Amazon search results)</div>';
    this.translateDOM(resultsDiv);

    try {
      // Check if ReverseAsin class exists (it's injected by content script system)
      if (typeof ReverseAsin === 'undefined') {
        resultsDiv.innerHTML = '<div class="keyword-error">❌ Keyword discovery module not loaded</div>';
        return;
      }

      // Initialize ReverseAsin (Hybrid Mode: Suggestions from Backend -> Search on Client -> Save to Backend)
      const reverseAsin = new ReverseAsin(analysis.marketplace || 'amazon.com');

      // Pass the current document for carousel-based keyword extraction
      const results = await reverseAsin.discoverKeywords(analysis.asin, document, (stage, current, total, message) => {
        resultsDiv.innerHTML = `<div class="keyword-loading">🔄 ${message}</div>`;
        this.translateDOM(resultsDiv);
      });

      if (results.error && (!results.keywords || results.keywords.length === 0)) {
        resultsDiv.innerHTML = `<div class="keyword-error">❌ ${results.error}</div>`;
        this.translateDOM(resultsDiv);
        return;
      }

      // Display comprehensive results table (ALL keywords)
      const allKeywords = results.allKeywords || results.keywords || [];
      const summary = results.analysisSummary || {
        total_keywords: allKeywords.length,
        keywords_found: results.keywordsFound || 0,
        keywords_not_found: allKeywords.length - (results.keywordsFound || 0),
        gaps_identified: 0
      };

      resultsDiv.innerHTML = `
        <div class="ra-header">
          <div class="ra-title-row">
            <span class="ra-title">📊 Keyword Analysis</span>
            <span class="ra-count">${summary.total_keywords} keywords analyzed</span>
          </div>
          <div class="ra-filters">
            <div class="ra-filter-group">
              <label>Min Volume</label>
              <input type="number" id="filter-min-volume" placeholder="0" min="0" value="">
            </div>
            <div class="ra-filter-group">
              <label>Max Volume</label>
              <input type="number" id="filter-max-volume" placeholder="∞" min="0" value="">
            </div>
            <div class="ra-filter-group">
              <label>Max Ads</label>
              <input type="number" id="filter-max-ads" placeholder="∞" min="0" max="60" value="">
            </div>
            <div class="ra-filter-group">
              <label>Max KD</label>
              <select id="filter-max-kd">
                <option value="100">All</option>
                <option value="30">Easy (≤30)</option>
                <option value="50">Medium (≤50)</option>
                <option value="70">Hard (≤70)</option>
              </select>
            </div>
          </div>
        </div>
        <div class="ra-table-container">
          <table class="ra-table">
            <thead>
              <tr>
                <th class="sortable col-keyword" data-sort="keyword">Keyword</th>
                <th class="sortable col-rank" data-sort="position">Rank</th>
                <th class="sortable col-volume" data-sort="estimated_volume">Volume</th>
                <th class="sortable col-kd" data-sort="difficulty_score">KD</th>
                <th class="sortable col-sales" data-sort="total_sales">Sales</th>
                <th class="sortable col-ads" data-sort="sponsored_count">Ads</th>
                <th class="sortable col-price" data-sort="avg_price">Avg $</th>
                <th class="sortable col-bsr" data-sort="avg_bsr">Avg BSR</th>
              </tr>
            </thead>
            <tbody id="ra-table-body">
              ${this.renderKeywordRows(allKeywords)}
            </tbody>
          </table>
        </div>
      `;

      // Add filter and sort event listeners
      this.setupKeywordTableEvents(resultsDiv, allKeywords);
      this.translateDOM(resultsDiv);
    } catch (error) {
      resultsDiv.innerHTML = `<div class="keyword-error">❌ Error: ${error.message}</div>`;
      this.translateDOM(resultsDiv);
    }
  }

  extractTitleKeywords(title) {
    if (!title) return [];

    const stopWords = ['for', 'with', 'and', 'the', 'by', 'in', 'of', 'to', 'a', 'an', 'is', 'it', 'on', 'as', 'at'];
    const cleaned = title.toLowerCase().replace(/[^\w\s\-]/g, ' ').replace(/\s+/g, ' ').trim();
    const words = cleaned.split(' ').filter(w => w.length > 2 && !stopWords.includes(w));

    const keywords = [];

    // 2-word combinations
    for (let i = 0; i < words.length - 1 && keywords.length < 15; i++) {
      keywords.push(`${words[i]} ${words[i + 1]}`);
    }

    // 3-word combinations
    for (let i = 0; i < words.length - 2 && keywords.length < 20; i++) {
      keywords.push(`${words[i]} ${words[i + 1]} ${words[i + 2]}`);
    }

    return [...new Set(keywords)];
  }

  /**
   * Render keyword table rows
   */
  renderKeywordRows(keywords, filters = {}) {
    const minVolume = filters.minVolume || 0;
    const maxVolume = filters.maxVolume || Infinity;
    const maxAds = filters.maxAds !== undefined ? filters.maxAds : Infinity;
    const maxKd = filters.maxKd || 100;

    return keywords
      .filter(kw => {
        // Filter by min search volume
        if (minVolume > 0 && (!kw.estimated_volume || kw.estimated_volume < minVolume)) return false;
        // Filter by max search volume
        if (maxVolume < Infinity && kw.estimated_volume && kw.estimated_volume > maxVolume) return false;
        // Filter by max ads count
        if (maxAds < Infinity && kw.sponsored_count != null && kw.sponsored_count > maxAds) return false;
        // Filter by max keyword difficulty
        if (kw.difficulty_score != null && kw.difficulty_score > maxKd) return false;
        return true;
      })
      .map(kw => `
        <tr>
          <td class="col-keyword">${kw.keyword}</td>
          <td class="col-rank">${kw.position ? `#${kw.position}` : '—'}</td>
          <td class="col-volume">${this.formatVolume(kw.estimated_volume)}</td>
          <td class="col-kd">${this.formatDifficulty(kw.difficulty_score, kw.difficulty_level)}</td>
          <td class="col-sales">${this.formatSales(kw.total_sales)}</td>
          <td class="col-ads">${kw.sponsored_count != null ? kw.sponsored_count : '—'}</td>
          <td class="col-price">${kw.avg_price ? this.getCurrencySymbol() + kw.avg_price.toFixed(0) : '—'}</td>
          <td class="col-bsr">${kw.avg_bsr != null && kw.avg_bsr > 0 ? '#' + kw.avg_bsr.toLocaleString() : '0'}</td>
        </tr>
      `).join('');
  }

  formatVolume(volume) {
    if (volume === null || volume === undefined) return '—';
    if (volume === 0) return '0';
    if (volume >= 10000) return `${(volume / 1000).toFixed(0)}K`;
    if (volume >= 1000) return `${(volume / 1000).toFixed(1)}K`;
    return volume.toLocaleString();
  }

  formatSales(sales) {
    if (sales === null || sales === undefined) return '—';
    if (sales === 0) return '0';
    if (sales >= 1000) return `${(sales / 1000).toFixed(1)}K`;
    return sales.toLocaleString();
  }

  /**
   * Get currency symbol based on current Amazon marketplace
   */
  getCurrencySymbol() {
    const hostname = window.location.hostname;
    const currencyMap = {
      'amazon.eg': 'EGP',
      'amazon.co.uk': '£',
      'amazon.de': '€',
      'amazon.fr': '€',
      'amazon.it': '€',
      'amazon.es': '€',
      'amazon.ae': 'AED',
      'amazon.sa': 'SAR',
      'amazon.in': '₹',
      'amazon.jp': '¥',
      'amazon.com': '$',
      'amazon.ca': 'C$',
      'amazon.com.mx': 'MX$',
      'amazon.com.br': 'R$',
    };

    for (const [domain, symbol] of Object.entries(currencyMap)) {
      if (hostname.includes(domain)) {
        return symbol;
      }
    }
    return '$'; // Default fallback
  }

  formatDemand(demandLevel) {
    if (!demandLevel) return '—';
    const levels = {
      'high': '<span style="color:#4ade80;">⚡ High</span>',
      'medium': '<span style="color:#facc15;">⚠️ Med</span>',
      'low': '<span style="color:#9ca3af;">💤 Low</span>'
    };
    return levels[demandLevel] || demandLevel;
  }

  formatDifficulty(score, level) {
    if (score == null) return '—';
    let color = '#4ade80'; // green
    if (score >= 70) color = '#f87171'; // red
    else if (score >= 50) color = '#fb923c'; // orange
    else if (score >= 30) color = '#facc15'; // yellow
    return `<span style="color:${color};">${score}</span>`;
  }

  /**
   * Setup filter and sort event listeners for keyword table
   */
  setupKeywordTableEvents(container, allKeywords) {
    const tbody = container.querySelector('#ra-table-body');
    const filterMinVolume = container.querySelector('#filter-min-volume');
    const filterMaxVolume = container.querySelector('#filter-max-volume');
    const filterMaxAds = container.querySelector('#filter-max-ads');
    const filterMaxKd = container.querySelector('#filter-max-kd');

    const updateTable = () => {
      const filters = {
        minVolume: parseInt(filterMinVolume?.value) || 0,
        maxVolume: filterMaxVolume?.value ? parseInt(filterMaxVolume.value) : Infinity,
        maxAds: filterMaxAds?.value ? parseInt(filterMaxAds.value) : Infinity,
        maxKd: parseInt(filterMaxKd?.value) || 100
      };
      if (tbody) {
        tbody.innerHTML = this.renderKeywordRows(allKeywords, filters);
        this.translateDOM(tbody);
      }
    };

    // Attach filter event listeners (use 'input' for immediate feedback on number inputs)
    filterMinVolume?.addEventListener('input', updateTable);
    filterMaxVolume?.addEventListener('input', updateTable);
    filterMaxAds?.addEventListener('input', updateTable);
    filterMaxKd?.addEventListener('change', updateTable);

    // Sortable columns
    container.querySelectorAll('th.sortable').forEach(th => {
      th.addEventListener('click', () => {
        const sortKey = th.dataset.sort;
        const currentDir = th.dataset.sortDir === 'asc' ? 'desc' : 'asc';
        th.dataset.sortDir = currentDir;

        // Update sort indicator
        container.querySelectorAll('th.sortable').forEach(h => h.classList.remove('sorted-asc', 'sorted-desc'));
        th.classList.add(currentDir === 'asc' ? 'sorted-asc' : 'sorted-desc');

        allKeywords.sort((a, b) => {
          let valA = a[sortKey];
          let valB = b[sortKey];
          if (valA === null || valA === undefined) valA = currentDir === 'asc' ? Infinity : -Infinity;
          if (valB === null || valB === undefined) valB = currentDir === 'asc' ? Infinity : -Infinity;
          if (typeof valA === 'string') return currentDir === 'asc' ? valA.localeCompare(valB) : valB.localeCompare(valA);
          return currentDir === 'asc' ? valA - valB : valB - valA;
        });
        updateTable();
      });
    });
  }


  async submitSellerFeedback(analysis, panel) {
    const actualSalesInput = panel.querySelector('#actual-sales-input');
    const messageDiv = panel.querySelector('#feedback-message');

    if (!actualSalesInput || !messageDiv) return;

    const actualSales = parseInt(actualSalesInput.value);
    if (isNaN(actualSales) || actualSales < 0) {
      messageDiv.innerHTML = '<span class="error">Please enter a valid number</span>';
      return;
    }

    messageDiv.innerHTML = '<span class="pending">📤 Submitting...</span>';
    this.translateDOM(messageDiv);

    try {
      // Check if ApiClient exists and user is logged in
      if (typeof ApiClient === 'undefined') {
        messageDiv.innerHTML = '<span class="error">API not available</span>';
        return;
      }

      await ApiClient.init();

      const result = await ApiClient.submitSalesFeedback({
        asin: analysis.asin,
        marketplace: analysis.marketplace || 'amazon.com',
        category: analysis.category || 'default',
        bsr: analysis.bsr || 0,
        estimatedSales: analysis.sales?.monthly || 0,
        actualSales: actualSales,
        salesWindowDays: 30
      });

      messageDiv.innerHTML = `<span class="success">✅ Thank you! Error was ${result.error_percent}%</span>`;
      this.translateDOM(messageDiv);
      actualSalesInput.value = '';
    } catch (error) {
      if (error.message.includes('authenticated') || error.message.includes('login')) {
        messageDiv.innerHTML = '<span class="error">⚠️ Please login to submit feedback</span>';
      } else {
        messageDiv.innerHTML = `<span class="error">❌ ${error.message}</span>`;
      }
      this.translateDOM(messageDiv);
    }
  }

  updateProfitMetrics(cogs, shipping, storage, ads, referral, applyVat, analysis, panel) {
    const price = parseFloat(analysis.price) || 0;
    const currency = analysis.currency || 'USD';

    // 14% VAT forced (if applyVat checkbox is checked)
    const vat = applyVat ? price * 0.14 : 0;

    // Per Unit = Price - VAT - COGS - Shipping - Storage - Referral - Ads
    const perUnit = price - vat - cogs - shipping - storage - referral - ads;

    const margin = price > 0 ? (perUnit / price) * 100 : 0;
    const totalCost = cogs + vat + shipping + storage + referral + ads;
    const roi = totalCost > 0 ? (perUnit / totalCost) * 100 : 0;
    const monthly = perUnit * (analysis.sales.monthly || 0);
    const annual = monthly * 12;

    // Determine class based on new margin
    const marginClass = this.getProfitClass(margin);

    // Update DOM elements
    const updateEl = (selector, text, addClass) => {
      const el = panel.querySelector(selector);
      if (el) {
        el.textContent = text;
        if (addClass) {
          el.classList.remove('analyzer-profit-excellent', 'analyzer-profit-good', 'analyzer-profit-acceptable', 'analyzer-profit-poor');
          el.classList.add(addClass);
        }
      }
    };

    const updatedFees = shipping + storage + referral;

    updateEl('#profit-per-unit', `${currency} ${perUnit.toFixed(2)}`, marginClass);
    updateEl('#profit-margin', `${margin.toFixed(1)}% margin`);
    updateEl('#monthly-profit', `${currency} ${monthly.toLocaleString()}`, marginClass);
    updateEl('#annual-profit', `${currency} ${annual.toLocaleString()}`, marginClass);
    updateEl('#roi-display', `${roi.toFixed(0)}% ROI`);
    updateEl('#total-fees-display', `${currency} ${updatedFees.toFixed(2)}`);

    // Update the margin note dynamically in analysis.opportunity.notes
    if (!analysis.opportunity) analysis.opportunity = {};
    if (!analysis.opportunity.notes) {
      analysis.opportunity.notes = [];
      if (analysis.insights && analysis.insights.length > 0) {
        analysis.opportunity.notes = analysis.insights.map(ins => ({
          type: ins.type,
          message: ins.message
        }));
      } else if (analysis.opportunity.reasons) {
        analysis.opportunity.notes = analysis.opportunity.reasons.map(reason => {
          let type = 'success';
          if (reason.includes('⚠️') || reason.includes('Low')) type = 'warning';
          return { type, message: reason };
        });
      }
    }
    
    const marginNoteIndex = analysis.opportunity.notes.findIndex(note => 
      note.message.toLowerCase().includes('margin') || 
      note.message.toLowerCase().includes('margins') || 
      note.message.includes('هامش')
    );
    
    const isArabic = this.isArabic();
    
    let noteType = 'success';
    let noteMessage = '';
    if (margin >= 40) {
      noteType = 'success';
      noteMessage = isArabic ? `💰 هامش ربح ممتاز (${margin.toFixed(1)}%)` : `💰 Excellent margins (${margin.toFixed(1)}%)`;
    } else if (margin >= 25) {
      noteType = 'success';
      noteMessage = isArabic ? `💰 هامش ربح جيد (${margin.toFixed(1)}%)` : `💰 Good margins (${margin.toFixed(1)}%)`;
    } else if (margin >= 15) {
      noteType = 'warning';
      noteMessage = isArabic ? `⚠️ هامش ربح مقبول (${margin.toFixed(1)}%)` : `⚠️ Acceptable margins (${margin.toFixed(1)}%)`;
    } else {
      noteType = 'danger';
      noteMessage = isArabic ? `❌ هامش ربح منخفض جداً (${margin.toFixed(1)}%)` : `❌ Very low margins (${margin.toFixed(1)}%)`;
    }

    const newNote = {
      type: noteType,
      message: noteMessage
    };

    if (marginNoteIndex > -1) {
      analysis.opportunity.notes[marginNoteIndex] = newNote;
    } else {
      analysis.opportunity.notes.push(newNote);
    }
    
    // Now regenerate and update the HTML inside #assessment-notes-container!
    const notesContainer = panel.querySelector('#assessment-notes-container');
    if (notesContainer) {
      const borderSide = isArabic ? 'border-right' : 'border-left';
      notesContainer.innerHTML = analysis.opportunity.notes.map(note => {
        let nBg = 'rgba(16, 185, 129, 0.1)';
        let nBorder = '#10b981';
        let nText = '#a7f3d0';
        if (note.type === 'warning') { nBg = 'rgba(245, 158, 11, 0.1)'; nBorder = '#f59e0b'; nText = '#fde68a'; }
        else if (note.type === 'danger') { nBg = 'rgba(239, 68, 68, 0.1)'; nBorder = '#ef4444'; nText = '#fecaca'; }
        
        // Translate note message if in Arabic mode
        const translatedMsg = isArabic ? this.translateNoteMessage(note.message) : note.message;
        return `<div style="padding: 10px 14px; background: ${nBg}; ${borderSide}: 4px solid ${nBorder}; color: ${nText}; font-size: 13px; border-radius: 6px; line-height: 1.5; text-align: ${isArabic ? 'right' : 'left'};">${translatedMsg}</div>`;
      }).join('');
    }

    this.translateDOM(panel);
  }

  getConfidenceBadge(confidence) {
    if (this.isArabic()) {
      const badges = {
        'high': '🟢 مرتفعة',
        'medium': '🟡 متوسطة',
        'low': '🟠 منخفضة',
        'very_low': '🔴 منخفضة جداً'
      };
      return badges[confidence] || confidence;
    }
    const badges = {
      'high': '🟢 High',
      'medium': '🟡 Medium',
      'low': '🟠 Low',
      'very_low': '🔴 Very Low'
    };
    return badges[confidence] || confidence;
  }

  getCompetitionLabel(level) {
    if (this.isArabic()) {
      const labels = {
        'very_low': 'منافسة منخفضة جداً',
        'low': 'منافسة منخفضة',
        'medium': 'منافسة متوسطة',
        'high': 'منافسة عالية',
        'very_high': 'منافسة عالية جداً'
      };
      return labels[level] || level;
    }
    const labels = {
      'very_low': 'Very Low Competition',
      'low': 'Low Competition',
      'medium': 'Medium Competition',
      'high': 'High Competition',
      'very_high': 'Very High Competition'
    };
    return labels[level] || level;
  }

  getProfitClass(margin) {
    if (margin >= 40) return 'analyzer-profit-excellent';
    if (margin >= 25) return 'analyzer-profit-good';
    if (margin >= 15) return 'analyzer-profit-acceptable';
    return 'analyzer-profit-poor';
  }

  getOpportunityClass(score) {
    if (score >= 80) return 'analyzer-opp-excellent';
    if (score >= 60) return 'analyzer-opp-good';
    if (score >= 40) return 'analyzer-opp-fair';
    return 'analyzer-opp-poor';
  }

  truncate(str, length) {
    if (!str) return '';
    return str.length > length ? str.substring(0, length) + '...' : str;
  }

  copyToClipboard(analysis) {
    const text = this.formatForCopy(analysis);
    navigator.clipboard.writeText(text).then(() => {
      alert(this.isArabic() ? '✅ تم نسخ البيانات إلى الحافظة!' : '✅ Data copied to clipboard!');
    }).catch(err => {
      console.error('Copy failed:', err);
    });
  }

  formatForCopy(analysis) {
    return `
AMAZON PRODUCT ANALYSIS
=======================

Product: ${analysis.title}
ASIN: ${analysis.asin}
Price: $${analysis.price}
Rating: ${analysis.rating} (${analysis.reviewCount} reviews)
BSR: #${analysis.bsr} in ${analysis.category}

SALES ESTIMATES
===============
Monthly Sales: ${analysis.sales.monthly} units
Daily Sales: ${analysis.sales.daily} units
Monthly Revenue: $${analysis.revenue.monthly}
Annual Revenue: $${analysis.revenue.annual}

PROFIT ANALYSIS
===============
Profit per Unit: $${analysis.profit.perUnit} (${analysis.profit.margin}% margin)
Monthly Profit: $${analysis.profit.monthly}
ROI: ${analysis.profit.roi}%
Fees: $${analysis.fees.total} (FBA: $${analysis.fees.fba}, Referral: $${analysis.fees.referral})

COMPETITION
===========
Level: ${analysis.competition.level}
Score: ${analysis.competition.score}/5
Review Velocity: ${analysis.competition.reviewVelocity}/month

OPPORTUNITY SCORE
=================
Score: ${analysis.opportunity.score}/100
Rating: ${analysis.opportunity.rating}
Recommended: ${analysis.opportunity.recommended ? 'YES' : 'NO'}

Analyzed at: ${analysis.analyzedAt}
    `.trim();
  }

  exportReport(analysis) {
    const dataStr = "data:text/json;charset=utf-8," + encodeURIComponent(JSON.stringify(analysis, null, 2));
    const downloadAnchorNode = document.createElement('a');
    downloadAnchorNode.setAttribute("href", dataStr);
    downloadAnchorNode.setAttribute("download", `amazon-analysis-${analysis.asin}.json`);
    document.body.appendChild(downloadAnchorNode);
    downloadAnchorNode.click();
    downloadAnchorNode.remove();
  }

  /**
   * Minimize the panel to the side
   */
  minimize() {
    if (this.panel) {
      this.panel.classList.add('minimized');
      this.panel.querySelector('#analyzer-restore').style.display = 'flex';
    }
  }

  /**
   * Restore the panel from minimized state
   */
  restore() {
    if (this.panel) {
      this.panel.classList.remove('minimized');
      this.panel.querySelector('#analyzer-restore').style.display = 'none';
    }
  }

  remove() {
    if (this.panel) {
      this.panel.classList.remove('visible');
      setTimeout(() => {
        this.panel.remove();
        this.panel = null;
        this.isVisible = false;
      }, 300);
    }
  }
}
// ShadowUI is now available globally
