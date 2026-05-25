# 🧠 Keyword Analyzer Pro - Comprehensive Implementation Plan

> **Helium 10 Cerebro Clone for Amazon Product Analyzer**

---

## 📋 Executive Summary

| Aspect | Specification |
|--------|---------------|
| **Feature Name** | Keyword Analyzer Pro |
| **Max ASINs** | 10 (same as Cerebro) |
| **Core Function** | Multi-ASIN keyword comparison & opportunity discovery |
| **Key Metric** | Cerebro IQ Score |
| **Data Source** | Amazon SERP scraping + BSR estimation |
| **Backend** | Results viewable in web dashboard |

---

## 🏷️ Suggested Feature Names

| # | Name | Theme | Notes |
|---|------|-------|-------|
| 1 | **Keyword Analyzer Pro** | Professional | Clear, descriptive |
| 2 | **KeywordIQ** | Intelligence | Emphasizes the IQ score |
| 3 | **ASIN Spy** | Competitor | Spy on competitor keywords |
| 4 | **Keyword Hunter** | Action | Find hidden keywords |
| 5 | **RankRadar** | Detection | Detect keyword rankings |
| 6 | **Competitor Keywords** | Simple | Direct description |
| 7 | **Keyword X-Ray** | Analysis | See inside ASINs |
| 8 | **KeyScope** | Vision | Scope out keywords |
| 9 | **Multi-ASIN Analyzer** | Technical | Accurate description |
| 10 | **Keyword Matrix** | Comparison | Multi-ASIN comparison grid |

**Recommended:** **Keyword Analyzer Pro** or **KeywordIQ**
- Professional sounding
- Clear functionality
- Works for marketing

---

## 🎯 Complete Cerebro Feature List

### A. Data Points Per Keyword (22 Metrics)

| # | Metric | Description | Can Implement? |
|---|--------|-------------|----------------|
| 1 | **Keyword** | The search phrase | ✅ Yes |
| 2 | **Search Volume** | Monthly searches | ✅ Yes (BSR-based) |
| 3 | **Search Vol Trend** | 30-day % change | ⚠️ Needs history |
| 4 | **Cerebro IQ Score** | Opportunity score | ✅ Yes |
| 5 | **Competing Products** | Total products in SERP | ✅ Yes |
| 6 | **Title Density** | Page 1 products with keyword in title | ✅ Yes |
| 7 | **Organic Rank** | Position per ASIN | ✅ Yes |
| 8 | **Sponsored Rank** | PPC position per ASIN | ✅ Yes |
| 9 | **Amazon Recommended Rank** | AR position | ⚠️ Complex |
| 10 | **Keyword Sales** | Monthly sales from keyword | ✅ Yes (estimate) |
| 11 | **CPR (8-Day)** | Units to rank on Page 1 | ✅ Yes (formula) |
| 12 | **CPR (Total)** | Total giveaway estimate | ✅ Yes |
| 13 | **Suggested PPC Bid** | Recommended bid | ❌ No (needs Ads API) |
| 14 | **Sponsored ASINs** | ASINs running PPC | ✅ Yes |
| 15 | **Word Count** | Words in keyword | ✅ Yes |
| 16 | **Amazon Choice** | Has AC badge? | ✅ Yes |
| 17 | **Position Type** | Organic/Sponsored/Both | ✅ Yes |
| 18 | **ASIN 1-10 Rank** | Individual rank columns | ✅ Yes |
| 19 | **ASINs Ranking** | How many of 10 rank | ✅ Yes |
| 20 | **Avg Organic Rank** | Average across ASINs | ✅ Yes |
| 21 | **Relative Rank** | Rank vs competitors | ✅ Yes |
| 22 | **Match Type** | How keyword was found | ✅ Yes |

### B. Complete Filter List (18 Filters)

| # | Filter | Type | Range/Options |
|---|--------|------|---------------|
| 1 | **Search Volume** | Range | 0 - 999,999 |
| 2 | **Search Vol Trend** | Range | -100% to +100% |
| 3 | **Cerebro IQ Score** | Range | 0 - 100 |
| 4 | **Competing Products** | Range | 0 - 999,999 |
| 5 | **Title Density** | Range | 0 - 48 |
| 6 | **Word Count** | Range | 1 - 10 |
| 7 | **Organic Rank** | Range | 1 - 306 |
| 8 | **Sponsored Rank** | Range | 1 - 306 |
| 9 | **Amazon Recommended Rank** | Range | 1 - 306 |
| 10 | **Keyword Sales** | Range | 0 - 999,999 |
| 11 | **CPR (8-Day)** | Range | 0 - 9,999 |
| 12 | **Suggested PPC Bid** | Range | $0 - $50 |
| 13 | **Sponsored ASINs** | Range | 0 - 48 |
| 14 | **Match Type** | Multi-select | Organic, Sponsored, Amazon Rec |
| 15 | **Include Phrases** | Text | Contains these words |
| 16 | **Exclude Phrases** | Text | Does NOT contain |
| 17 | **Amazon Choice** | Toggle | Yes/No/All |
| 18 | **Advanced Rank Filter** | Complex | X of Y ASINs in ranks A-B |

### C. One-Click Quick Filters

| Button | Logic |
|--------|-------|
| **Top Keywords** | Volume ≥ 1000 AND Organic Rank ≤ 20 |
| **Opportunity Keywords** | IQ Score ≥ 3 AND Title Density ≤ 5 |
| **Low Competition** | Competing Products ≤ 10,000 AND Volume ≥ 500 |
| **Long-Tail** | Word Count ≥ 4 AND Volume ≥ 100 |
| **Not Ranking** | Organic Rank = null for YOUR ASIN |

---

## 🔢 Key Calculations

### 1. Cerebro IQ Score

```
IQ_Score = (Search_Volume / Competing_Products) × 10

Higher = Better opportunity
```

| IQ Score | Level | Action |
|----------|-------|--------|
| ≥ 5 | 🟢 Excellent | High priority target |
| 3 - 5 | 🟡 Good | Worth targeting |
| 1 - 3 | 🟠 Moderate | Consider if relevant |
| < 1 | 🔴 Poor | Skip |

### 2. CPR (Cerebro Product Rank) Giveaway

```
CPR_8Day = (Search_Volume × 0.02) / 8

WHERE:
- 0.02 = 2% of searches need to convert to your product
- 8 = Amazon's ranking period in days
```

| Volume | CPR 8-Day | CPR Total |
|--------|-----------|-----------|
| 1,000 | 3/day | 24 units |
| 10,000 | 25/day | 200 units |
| 50,000 | 125/day | 1,000 units |

### 3. Title Density

```
Title_Density = COUNT(Page 1 products WHERE keyword IN title)

Low (0-5) = Easy to rank
Medium (6-15) = Moderate competition
High (16+) = Highly competitive
```

### 4. Keyword Sales Attribution

```
Keyword_Sales = Position_Sales × (Click_Share_at_Position / Total_Click_Share)

WHERE Click_Share:
- Position 1-5: 15% each
- Position 6-10: 3% each
- Position 11+: 0.5% each
```

---

## 🏗️ Technical Architecture

### Component Diagram

```
┌──────────────────────────────────────────────────────────────────────┐
│                     CHROME EXTENSION                                  │
├──────────────────────────────────────────────────────────────────────┤
│                                                                       │
│  ┌─────────────────┐    ┌─────────────────┐    ┌─────────────────┐  │
│  │  CerebroUI      │───▶│ CerebroAnalyzer │───▶│  ReverseAsin    │  │
│  │  (10 ASIN input)│    │ (Orchestrator)  │    │  (Per-ASIN)     │  │
│  └─────────────────┘    └─────────────────┘    └─────────────────┘  │
│           │                     │                       │            │
│           │                     ▼                       ▼            │
│           │            ┌─────────────────┐    ┌─────────────────┐   │
│           │            │  IQ Calculator  │    │   SerpParser    │   │
│           │            └─────────────────┘    └─────────────────┘   │
│           │                     │                                    │
└───────────┼─────────────────────┼────────────────────────────────────┘
            │                     │
            ▼                     ▼ POST /api/cerebro/analyze
┌──────────────────────────────────────────────────────────────────────┐
│                      LARAVEL BACKEND                                  │
├──────────────────────────────────────────────────────────────────────┤
│  ┌─────────────────┐    ┌─────────────────┐    ┌─────────────────┐  │
│  │ CerebroController│◀──│ CerebroService  │───▶│   Database      │  │
│  └─────────────────┘    └─────────────────┘    └─────────────────┘  │
│           │                                                          │
│           ▼                                                          │
│  ┌─────────────────────────────────────────────────────────────┐    │
│  │  Web Dashboard (/cerebro)                                    │    │
│  │  - Analysis history                                          │    │
│  │  - Results with 18 filters                                   │    │
│  │  - Export CSV/Excel                                          │    │
│  └─────────────────────────────────────────────────────────────┘    │
└──────────────────────────────────────────────────────────────────────┘
```

---

## 💾 Database Schema

### Table: `cerebro_analyses`

```sql
CREATE TABLE cerebro_analyses (
    id BIGINT UNSIGNED PRIMARY KEY AUTO_INCREMENT,
    user_id BIGINT UNSIGNED NOT NULL,
    name VARCHAR(255) NULL,
    marketplace VARCHAR(30) NOT NULL,
    asins JSON NOT NULL,  -- ["B0123", "B0456", ...]
    asin_count TINYINT DEFAULT 1,
    status ENUM('pending','processing','completed','failed') DEFAULT 'pending',
    total_keywords INT DEFAULT 0,
    progress_percent TINYINT DEFAULT 0,
    error_message TEXT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    completed_at TIMESTAMP NULL,
    
    INDEX idx_user (user_id),
    INDEX idx_status (status)
);
```

### Table: `cerebro_keywords`

```sql
CREATE TABLE cerebro_keywords (
    id BIGINT UNSIGNED PRIMARY KEY AUTO_INCREMENT,
    analysis_id BIGINT UNSIGNED NOT NULL,
    keyword VARCHAR(500) NOT NULL,
    word_count TINYINT DEFAULT 1,
    
    -- Volume & Opportunity
    search_volume INT DEFAULT 0,
    search_volume_trend DECIMAL(5,2) NULL,  -- % change
    cerebro_iq_score DECIMAL(8,2) DEFAULT 0,
    competing_products INT DEFAULT 0,
    title_density TINYINT DEFAULT 0,
    
    -- CPR Calculations
    cpr_8day INT DEFAULT 0,
    cpr_total INT DEFAULT 0,
    
    -- Sales
    keyword_sales INT DEFAULT 0,
    
    -- Sponsored
    sponsored_asin_count TINYINT DEFAULT 0,
    
    -- Per-ASIN Rankings (JSON)
    organic_ranks JSON NULL,     -- {"B0123": 5, "B0456": null, ...}
    sponsored_ranks JSON NULL,   -- {"B0123": 3, "B0456": 12, ...}
    
    -- Aggregates
    asins_ranking TINYINT DEFAULT 0,
    avg_organic_rank DECIMAL(5,1) NULL,
    min_organic_rank SMALLINT NULL,
    max_organic_rank SMALLINT NULL,
    
    -- Flags
    has_amazon_choice BOOLEAN DEFAULT FALSE,
    match_type ENUM('organic','sponsored','both','amazon_rec') DEFAULT 'organic',
    
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    
    FOREIGN KEY (analysis_id) REFERENCES cerebro_analyses(id) ON DELETE CASCADE,
    INDEX idx_analysis_iq (analysis_id, cerebro_iq_score DESC),
    INDEX idx_analysis_volume (analysis_id, search_volume DESC),
    INDEX idx_keyword (keyword(100))
);
```

---

## 🔌 API Endpoints

### Extension → Backend

| Method | Endpoint | Description |
|--------|----------|-------------|
| POST | `/api/cerebro/analyze` | Submit completed analysis |
| GET | `/api/cerebro/history` | List user's past analyses |
| GET | `/api/cerebro/{id}` | Get analysis with keywords |
| DELETE | `/api/cerebro/{id}` | Delete analysis |

### Web Dashboard

| Method | Endpoint | Description |
|--------|----------|-------------|
| GET | `/cerebro` | Dashboard home |
| GET | `/cerebro/{id}` | View analysis results |
| GET | `/cerebro/{id}/export` | Export CSV |

### Request/Response Examples

**POST /api/cerebro/analyze**
```json
{
  "marketplace": "amazon.com",
  "asins": ["B0AAAA", "B0BBBB", "B0CCCC"],
  "name": "Office Chair Competitors",
  "keywords": [
    {
      "keyword": "office chair",
      "search_volume": 45000,
      "competing_products": 50000,
      "title_density": 32,
      "organic_ranks": {"B0AAAA": 3, "B0BBBB": 7, "B0CCCC": null},
      "sponsored_ranks": {"B0AAAA": 2, "B0BBBB": null, "B0CCCC": 5}
    }
  ]
}
```

---

## 🎨 UI Design - Extension

### Multi-ASIN Input

```
┌────────────────────────────────────────────────────────────┐
│  🧠 Keyword Analyzer Pro                                    │
├────────────────────────────────────────────────────────────┤
│  Enter up to 10 ASINs:                                      │
│  ┌──────────────────────────────────────────────────────┐  │
│  │ 1. B0XXXXXXXX  [Primary - Your Product]         [×]  │  │
│  │ 2. B0YYYYYYYY                                   [×]  │  │
│  │ 3. B0ZZZZZZZZ                                   [×]  │  │
│  │ 4. _________________________ [+ Add Competitor]      │  │
│  └──────────────────────────────────────────────────────┘  │
│                                                             │
│  Marketplace: [🇺🇸 Amazon.com ▼]                            │
│                                                             │
│  ⚙️ Options:                                                │
│  [✓] Include sponsored keywords                             │
│  [✓] Calculate CPR estimates                                │
│  [ ] Deep scan (slower, more keywords)                      │
│                                                             │
│  [ 🔍 Start Analysis ]                                      │
└────────────────────────────────────────────────────────────┘
```

### Analysis Progress

```
┌────────────────────────────────────────────────────────────┐
│  Analyzing 5 ASINs...                                       │
├────────────────────────────────────────────────────────────┤
│  ████████████████░░░░░░░░░░░░░░░░░░  45%                   │
│                                                             │
│  ✓ B0AAAA - 156 keywords found                             │
│  ✓ B0BBBB - 203 keywords found                             │
│  ⏳ B0CCCC - Scanning...                                    │
│  ○ B0DDDD - Pending                                         │
│  ○ B0EEEE - Pending                                         │
│                                                             │
│  Found: 312 unique keywords                                 │
│  ETA: ~3 minutes remaining                                  │
│                                                             │
│  [ Cancel ]                                                 │
└────────────────────────────────────────────────────────────┘
```

### Results Table

```
┌────────────────────────────────────────────────────────────────────────────┐
│  📊 Results: 312 keywords | 5 ASINs | amazon.com                            │
├────────────────────────────────────────────────────────────────────────────┤
│  Quick: [Top KW] [Opportunity] [Low Comp] [Long-tail] [Not Ranking]        │
├────────────────────────────────────────────────────────────────────────────┤
│  Filters:  Vol [1000]-[∞]  IQ [2]-[∞]  Words [2]-[5]  [🔍 Apply]          │
│            Include: [_____]  Exclude: [_____]  Match: [All ▼]              │
├────────────────────────────────────────────────────────────────────────────┤
│ Keyword          │ Vol   │ IQ  │ TD │ #1  │ #2  │ #3  │ #4  │ #5  │ CPR  │
│──────────────────┼───────┼─────┼────┼─────┼─────┼─────┼─────┼─────┼──────│
│ office chair     │ 45K   │ 9.0 │ 32 │ #3  │ #7  │ -   │ #15 │ #22 │ 112  │
│ ergonomic chair  │ 28K   │ 14  │ 18 │ #1  │ #2  │ #5  │ #8  │ #12 │ 70   │
│ desk chair       │ 22K   │ 5.5 │ 24 │ -   │ #12 │ #8  │ -   │ #45 │ 55   │
│ computer chair   │ 18K   │ 7.2 │ 14 │ #4  │ -   │ #3  │ #9  │ -   │ 45   │
└────────────────────────────────────────────────────────────────────────────┘
│  [💾 Save to Dashboard] [📋 Copy] [📥 Export CSV]                           │
└────────────────────────────────────────────────────────────────────────────┘
```

---

## 📋 Implementation Phases

### Phase 1: Core Engine (5 days)

| Task | Priority | Effort |
|------|----------|--------|
| Create `CerebroAnalyzer` class | P0 | 2d |
| Multi-ASIN orchestration (parallel) | P0 | 1d |
| Competing products counter | P0 | 0.5d |
| Title density calculator | P0 | 0.5d |
| Cerebro IQ score formula | P0 | 0.5d |
| CPR giveaway calculator | P1 | 0.5d |

### Phase 2: Backend (4 days)

| Task | Priority | Effort |
|------|----------|--------|
| Database migrations | P0 | 0.5d |
| CerebroController CRUD | P0 | 1d |
| Filter query builder | P0 | 1d |
| Export to CSV | P1 | 0.5d |
| History pagination | P1 | 0.5d |
| Rate limiting | P1 | 0.5d |

### Phase 3: Extension UI (4 days)

| Task | Priority | Effort |
|------|----------|--------|
| Multi-ASIN input component | P0 | 1d |
| Progress tracking UI | P0 | 0.5d |
| Results table (10 ASIN columns) | P0 | 1d |
| Filter bar component | P0 | 1d |
| Quick filter buttons | P1 | 0.5d |

### Phase 4: Web Dashboard (5 days)

| Task | Priority | Effort |
|------|----------|--------|
| Dashboard layout | P0 | 1d |
| Analysis history list | P0 | 0.5d |
| Results viewer with filters | P0 | 2d |
| Advanced rank filter UI | P1 | 1d |
| Export functionality | P1 | 0.5d |

**Total Estimated Effort: 18 days (~4 weeks)**

---

## ✅ Implementation Checklist

```
PHASE 1: CORE ENGINE
[ ] CerebroAnalyzer.js class
[ ] Multi-ASIN parallel processing
[ ] Merge keyword results across ASINs
[ ] calculateCompetingProducts()
[ ] calculateTitleDensity()
[ ] calculateIQScore()
[ ] calculateCPR()
[ ] calculateKeywordSales()

PHASE 2: BACKEND
[ ] cerebro_analyses migration
[ ] cerebro_keywords migration
[ ] CerebroController.php
[ ] CerebroService.php
[ ] Filter query methods
[ ] Export CSV endpoint

PHASE 3: EXTENSION UI
[ ] cerebro-ui.js component
[ ] ASIN input with validation
[ ] Progress overlay
[ ] Results DataTable
[ ] Filter bar
[ ] Quick filter buttons
[ ] Save/Export buttons

PHASE 4: DASHBOARD
[ ] /cerebro route
[ ] cerebro.blade.php layout
[ ] Analysis history page
[ ] Results viewer page
[ ] Advanced filter modal
[ ] CSV download
```

---

## 🔗 Dependency on Existing Reverse ASIN

| Existing Component | Reuse Level | Notes |
|--------------------|-------------|-------|
| `ReverseAsin.discoverKeywords()` | 100% | Core engine |
| `ReverseAsin.searchForAsin()` | 100% | Position detection |
| `SerpParser` | 100% | Product extraction |
| `SearchVolumeController` | 80% | Add competing products |
| `shadow-ui.js` | 50% | New Cerebro panel |

**Recommendation:** Build Cerebro as a **wrapper** around ReverseAsin, not a replacement.

---

## ⚠️ Known Limitations

| Limitation | Impact | Mitigation |
|------------|--------|------------|
| No real-time search volume trends | Can't show % change | Store historical data |
| No PPC bid data | Missing CPM column | Remove column or use estimate |
| Rate limiting (10 ASINs = 500+ requests) | Slow analysis | Queue processing, delays |
| Search volume is estimated | Less accurate than H10 | Show confidence ranges |

---

## 🎯 Success Metrics

| Metric | Target |
|--------|--------|
| Keywords discovered per ASIN | ≥ 100 |
| Analysis time (10 ASINs) | < 10 min |
| IQ Score accuracy | Within 2x of Helium 10 |
| User adoption | 50%+ of active users try it |
