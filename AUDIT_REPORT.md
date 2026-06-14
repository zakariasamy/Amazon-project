# 🔍 Audit Report: Frontend Calculations & Backend Offline Guards

This audit examines the seven tools in the Chrome Extension to identify:
1. Calculations performed in the frontend (JS) that should be moved to the backend.
2. The behavior of each tool when the backend server is offline/unavailable (lack of backend availability checks and fallbacks).

---

## 📊 Summary of Audited Tools

| Tool | Frontend Calculations | Backend Offline Behavior (Current) | Status |
| :--- | :--- | :--- | :--- |
| **1. Market Analysis** | Sponsored/organic count, ad density %, fallback sales metrics. | Catch block shows alert `Search analysis failed: API error` but allows starting the tool. | ⚠️ Needs Fix |
| **2. Keyword Magnet** | Volume estimation heuristic, difficulty score (listing strength, ad density, review barrier, brand dominance), CPR, keyword sales. | Catches error, logs warning, and silently falls back to local calculations. | ⚠️ Needs Fix |
| **3. Competitor Analyzer Pro** | IQ Score, keyword sales, organic rank stats (avg/min/max), title density, fallback volume estimation. | Catches error, logs warning, and silently falls back to local calculations. | ⚠️ Needs Fix |
| **4. Analyze Product** | Sales, revenue, fees, profit, competition, opportunity score (runs `IntelligenceEngine` locally). | Catches error, logs warning, and silently falls back to local calculations. | ⚠️ Needs Fix |
| **5. FBA Calculator** | Taxes, net profit, net margin, investment, ROI, monthly profit, annual profit. | Catches error, logs warning, and silently falls back to local calculations. | ⚠️ Needs Fix |
| **6. Reverse ASIN** | Broad category candidate keywords generation, cleaning, filtering. | Catches error, logs warning, and skips metrics (volume/difficulty) for tested keywords. | ⚠️ Needs Fix |
| **7. Save to list** | No calculations. | Displays `Network error loading folders` inside the picker UI, blocking save. | ✅ OK (Guarded) |

---

## 🔍 Detailed Findings & Code Locations

### 1. Market Analysis
* **File**: [content-script.js](file:///c:/Users/zakar/OneDrive/Desktop/New%20folder/Amazon%20project/chrome-extension/src/content/content-script.js)
* **Frontend Calculations**:
  * Lines 1375-1393: Calculates sponsored count, organic count, density percent, total products, and fallback sales metrics.
  * Difficulty score and search volume are requested from the backend `/api/search-volume/estimate`, but if it fails, it does not gracefully handle the server-offline state.
* **Server Offline Behavior**:
  * Lines 1409-1413: Logs warning to console and alerts the user with `Search analysis failed: ...` rather than preventing interaction.

### 2. Keyword Magnet
* **File**: [magnet-analyzer.js](file:///c:/Users/zakar/OneDrive/Desktop/New%20folder/Amazon%20project/chrome-extension/src/api/magnet-analyzer.js)
* **Frontend Calculations**:
  * Line 1293: Calculates `cpr8day = Math.ceil((searchVolume * 0.02) / 8)` and `cprTotal`.
  * Line 1297: Calculates keyword sales as `Math.round(searchVolume * 0.10 * 0.15)`.
  * Lines 1405-1419: `estimateSearchVolume()` computes volume locally from product count and reviews.
  * Lines 1424-1491: `calculateKeywordDifficulty()` computes the listing strength, ad density, review barrier, and brand dominance.
  * Lines 1321-1348: `getDefaultMetrics()` calculates heuristic defaults when scraping fails.
* **Server Offline Behavior**:
  * Lines 1283-1289: In `getKeywordMetrics()`, if the backend `/api/search-volume/batch-estimate` fails, it falls back to local difficulty calculation (`calculateKeywordDifficulty`) and local volume estimation (`estimateSearchVolume`) without informing the user that the backend is down.

### 3. Competitor Analyzer Pro (Cerebro)
* **File**: [cerebro-analyzer.js](file:///c:/Users/zakar/OneDrive/Desktop/New%20folder/Amazon%20project/chrome-extension/src/api/cerebro-analyzer.js)
* **Frontend Calculations**:
  * Line 1087: Calculates `cerebro_iq_score = (Volume / Competing Products) * 10`.
  * Line 1093: Calculates `keyword_sales = estimateKeywordSales(searchVolume, topRank)`.
  * Line 1097: Calculates `avg_organic_rank`, `min_organic_rank`, `max_organic_rank`.
  * Line 1108: Calculates `total_keyword_sales` fallback using volume × 9.5% or a BSR-bracket scale.
  * Line 923: Local fallback `calculateSearchVolumeSerpV2` for search volume estimation.
* **Server Offline Behavior**:
  * Lines 121-132: If backend settings fetch fails on startup, it proceeds with defaults silently.
  * Lines 921-925: If backend `/api/search-volume/estimate` fails, it catches the error, logs a warning, and falls back to local calculations (`calculateSearchVolumeSerpV2`).

### 4. Analyze Product (Product Analysis)
* **File**: [content-script.js](file:///c:/Users/zakar/OneDrive/Desktop/New%20folder/Amazon%20project/chrome-extension/src/content/content-script.js)
* **Frontend Calculations**:
  * Lines 533-541: In `analyzeCurrentProduct()`, if the backend `/api/analyze` call fails, it instantiates the local `IntelligenceEngine` to calculate sales, revenue, fees, profit, competition, opportunity score.
* **Server Offline Behavior**:
  * Silent fallback to the local `IntelligenceEngine`. The user is unaware they are using outdated or incorrect local heuristics.

### 5. FBA Calculator
* **Files**: [content-script.js](file:///c:/Users/zakar/OneDrive/Desktop/New%20folder/Amazon%20project/chrome-extension/src/content/content-script.js) and [shadow-ui.js](file:///c:/Users/zakar/OneDrive/Desktop/New%20folder/Amazon%20project/chrome-extension/src/ui/shadow-ui.js)
* **Frontend Calculations**:
  * [content-script.js](file:///c:/Users/zakar/OneDrive/Desktop/New%20folder/Amazon%20project/chrome-extension/src/content/content-script.js) Lines 877-895: In `recalculate()`, if the backend `/api/fees/calculate-profit` fails, it computes referral fees, total costs, net profit, net margin, investment, and ROI locally in JS.
  * [shadow-ui.js](file:///c:/Users/zakar/OneDrive/Desktop/New%20folder/Amazon%20project/chrome-extension/src/ui/shadow-ui.js) Lines 1451-1490: `updateProfitMetrics()` calculates VAT, net profit per unit, margin, ROI, monthly profit, annual profit, and total fees in JS.
* **Server Offline Behavior**:
  * Falls back to local FBA/FBM profit calculations silently.

### 6. Reverse ASIN
* **File**: [reverse-asin.js](file:///c:/Users/zakar/OneDrive/Desktop/New%20folder/Amazon%20project/chrome-extension/src/api/reverse-asin.js)
* **Frontend Calculations**:
  * Candidate keyword generation, cleaning, filtering, and prioritization are done in JS.
* **Server Offline Behavior**:
  * Lines 129-140: Settings fetch failure is caught silently.
  * Lines 472-478: If the backend `/api/search-volume/batch-estimate` fails, it continues checking keywords without obtaining their search volumes or difficulty scores, leading to blank/incomplete results.

### 7. Save to list
* **File**: [save-to-list.js](file:///c:/Users/zakar/OneDrive/Desktop/New%20folder/Amazon%20project/chrome-extension/src/ui/save-to-list.js)
* **Frontend Calculations**:
  * None.
* **Server Offline Behavior**:
  * Shows `Network error loading folders` inside the folder selection modal and blocks further actions.

---

## 🛠️ Proposed Fixes & Implementation Plan

To satisfy the core requirements:
1. **Move calculations to the backend**: Remove all frontend math fallbacks (e.g., local `IntelligenceEngine`, local FBA profit calculations, local KD, and local search volume estimations).
2. **Backend Server Offline Guard**: Implement a global check for backend server availability. If the backend is not running, disable user actions in the Chrome Extension and display a clear, red `⚠️ Backend server is offline. Please start the server to use this tool.` warning.

### Step-by-Step Implementation:
1. **Global Backend Health Check**:
   * Add a `checkHealth()` function in [api-client.js](file:///c:/Users/zakar/OneDrive/Desktop/New%20folder/Amazon%20project/chrome-extension/src/api/api-client.js).
2. **Content Script Guard**:
   * In [content-script.js](file:///c:/Users/zakar/OneDrive/Desktop/New%20folder/Amazon%20project/chrome-extension/src/content/content-script.js), check the backend status on load. If the server is offline, disable the "Analyze Product", "Reverse ASIN", "FBA Calculator", and "Market Analysis" buttons, and show a warning message.
   * Remove the Catch-block local calculation fallbacks from `analyzeCurrentProduct()` and `recalculate()`.
3. **Keyword Magnet Guard**:
   * In [magnet-ui.js](file:///c:/Users/zakar/OneDrive/Desktop/New%20folder/Amazon%20project/chrome-extension/src/ui/magnet-ui.js), verify backend health before opening the input panel. If offline, display a server offline warning instead of the input panel.
   * Remove local fallbacks from [magnet-analyzer.js](file:///c:/Users/zakar/OneDrive/Desktop/New%20folder/Amazon%20project/chrome-extension/src/api/magnet-analyzer.js).
4. **Competitor Keyword Analyzer Guard**:
   * In [cerebro-ui.js](file:///c:/Users/zakar/OneDrive/Desktop/New%20folder/Amazon%20project/chrome-extension/src/ui/cerebro-ui.js), verify backend health. If offline, block the floating Cerebro selection bar actions and show the server offline error.
   * Remove local fallbacks from [cerebro-analyzer.js](file:///c:/Users/zakar/OneDrive/Desktop/New%20folder/Amazon%20project/chrome-extension/src/api/cerebro-analyzer.js).
