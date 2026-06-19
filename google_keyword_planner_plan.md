# Google Keyword Planner Integration & Forecasting Dashboard Plan

This plan outlines the design and integration of a hybrid forecasting system combining **Google Keyword Planner search volume trends** with **Amazon sales statistics (BSR, category conversion rates, and current sales)**.

---

## 🛡️ Admin Controls setting
The Google Keyword Planner integration will be globally controlled by the Admin.
- **Setting key**: `feature_google_keyword_planner_enabled`
- **Dashboard interface**: Toggle switch under the *Feature Toggles* tab in Admin settings.
- **Behavior**:
  - If **Disabled**: The testing dashboard `/admin/google-keyword-planner` will return a `403 Forbidden` or a "Feature Disabled" screen. The Chrome extension will hide all injected buttons/widgets.
  - If **Enabled**: Injected buttons will appear on Amazon product pages and search pages, and the prototype tool will be fully active.

---

## 📐 Mathematical Model for Amazon Intent Ratio (AIR)
We implement a product-level calculation approach for the AIR without using a category CVR baseline:
* **Product-Level (Enforced)**: The Amazon Intent Ratio (AIR) measures the conversion relationship derived directly from searches and sales. It is computed as:
  $$\text{AIR} = \frac{\text{Monthly Sales}}{SV_{Google} \times 0.10}$$
  We normalize this ratio using a standard baseline conversion rate of 10% (0.10) to map the raw conversion rate to an intent index, and we cap this ratio between `0.05` (5% minimum intent) and `1.00` (100% purchase intent).
* **Why Category CVR baseline is not needed**: Since we have both the Google Search Volume ($SV_{Google}$) and the Amazon Monthly Sales ($Sales$), the actual purchase intent is directly observable from the ratio of sales to search volume. A preset category CVR table is therefore unnecessary because the searches and sales data themselves define the conversion profile.
* **Missing Sales Badge Fallback**: When Amazon direct monthly sales badges are not available, the system maintains product-level specificity by dynamically predicting monthly sales from the product's Best Sellers Rank (BSR) using the app's pre-existing estimation formulas ($Sales = C / BSR^P$).

---

## 🛠️ Components to Build

### 1. Web Routes & Controller
- Register `/admin/google-keyword-planner` and `/admin/google-keyword-planner/simulate` (automated calculation API using stored settings) in `routes/web.php`.
- Expose Google Ads OAuth API settings (`google_ads_developer_token`, `google_ads_client_id`, `google_ads_client_secret`, `google_ads_refresh_token`, `google_ads_customer_id`) in SelaaScout's general `/admin/settings` configurations.

### 2. Dashboard Interface
- Reconfigure `resources/views/admin/google_keyword_test.blade.php`.
- Remove Mock Mode toggles, manually fillable parameter forms, and simulate/submit buttons.
- On page request, extract details automatically from URL params, query the backend (which calls the real Google Ads API), and render metrics and Chart.js forecast curves immediately.

### 3. Chrome Extension Hooks
- Content scripts inject a button on Amazon product detail pages (scrapes ASIN, BSR, Sales badge, category) and search pages (scrapes query text).
- Clicking redirects the user to the dashboard with query parameters to auto-trigger the keyword planner report.
- On the search page itself, running "Market Analysis" will query the real Google Ads API in the background using stored credentials to output intent-damped real volumes.
