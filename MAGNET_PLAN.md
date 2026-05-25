# 🧲 Magnet Keyword Suggestion Tool - Implementation Plan

> **Objective**: Generate high-intent keyword ideas from a seed term by combining Amazon data, external search trends, and technical product attributes.

---

## 🚀 Discovery Sources (The Upgrade)

### 1. ⌨️ Autocomplete (Multi-Prefix)
Fetches suggestions from Amazon's completion API using the seed keyword + letter prefixes (a-z) and shopper modifiers (best, cheap, for).

### 2. 🌐 Global Research (Outside Amazon)
Pulls free suggestions from high-traffic external sources:
- **Google**: `suggestqueries.google.com/complete/search?client=chrome&q={keyword}`
- **Bing**: `www.bing.com/osjson.aspx?query={keyword}`
- **YouTube**: `suggestqueries.google.com/complete/search?client=youtube&ds=yt&q={keyword}`

### 3. 📄 Attribute-Based Keywords (Technical Scrape)
**This is the discovery upgrade.**
- **Flow**: Magnet will fetch the content of the **top N products** (configurable from backend, default: 5) on the Search Results Page in the background.
- **Extraction**: It identifies the "Core Noun" and combines it with technical values scraped from the product detail specification tables.
- **Scraped Attributes**: Brand, Color, Material, Size, Special Features, Back Style, etc.
- **Example**: If seed is "Chair" and spec is "Mesh", it generates **"Mesh Chair"**.

#### HTML Selector Logic:
```javascript
// Target table: table.a-normal.a-spacing-micro
// Each row has class like: po-brand, po-color, po-material, po-special_feature
// Value is in: td.a-span9 > span.po-break-word

const rows = doc.querySelectorAll('table.a-normal.a-spacing-micro tr[class*="po-"]');
rows.forEach(row => {
    const value = row.querySelector('td.a-span9 .po-break-word')?.textContent?.trim();
    // Combine with core noun: e.g., "Mesh" + "Chair" = "Mesh Chair"
});
```

#### Backend Configuration:
- **Setting**: `magnet_attribute_product_count` (default: 5)
- **Editable via**: Dashboard settings or constants API

---

## 📊 Evaluation Metrics (No Ranks)

| Metric | Calculation / Source |
|--------|----------------------|
| **Search Volume** | Estimated via BSR/Review velocity |
| **Magnet IQ Score** | (Search Volume / Competing Products) × 10 |
| **Title Density** | Count of Page 1 titles containing the exact keyword |
| **CPR 8-Day** | Estimated units needed to rank on Page 1 |
| **Relevance** | Word overlap score (0-100) compared to seed |

---

## 🏗️ Technical Architecture

### 1. Database (Laravel)
- **Table**: `magnet_analyses` (Metadata: user, seed, marketplace, duration)
- **Table**: `magnet_keywords` (Data: all 14 metrics + discovery source)

### 2. Engine (Chrome Extension)
- `MagnetAnalyzer.js`: Orchestrates the 4 discovery methods.
- `AttributeScraper.js`: Special logic to parse the `Brand Name`, `Material`, etc., from detail blocks.

---

## ✅ Implementation Phases
1. ✅ **Migrations**: Created `magnet_analyses`, `magnet_keywords`, `magnet_settings` tables.
2. ✅ **Backend Settings API**: Added `/api/magnet/settings` endpoint for configurable `attribute_product_count`.
3. ✅ **Extension Engine**: 
   - Added `getAttributeKeywords()` method to scrape product detail pages.
   - Added `getTitleKeywordsWithAsins()` to collect ASINs during SERP scan.
   - Added `fetchBackendSettings()` to get configurable settings from backend.
4. ✅ **UI Updates**: 
   - Added `attribute` match type styling to dashboard (`show.blade.php`).
   - Added `Attribute Keywords` checkbox to extension input panel (`magnet-ui.js`).
   - Added color styling for `attribute`, `google`, `bing`, `youtube` types.
5. ✅ **Refinements**:
   - Implemented advanced filtering for generic/illogical attribute values.
   - Added slash-splitting logic (e.g., "Grey/Black" -> "Grey", "Black") for maximum keyword discovery.

---
*Created: Jan 2026 | Last Updated: Jan 10, 2026*
