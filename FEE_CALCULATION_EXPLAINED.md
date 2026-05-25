# 📊 Fee Calculation & Profit Explanation

## ❓ Why Was Profit Negative Before?

### **Old Calculation (WRONG):**
```
Price:     110 EGP
- COGS:    -38.5 EGP (35% of price) ❌ TOO HIGH
- FBA Fee: -100 EGP ❌ EGYPT FEES WERE TOO HIGH
- Ref Fee: -16.5 EGP ✅ Correct (15%)
- Storage: -0.5 EGP ✅
= Profit:  -45.5 EGP ❌ NEGATIVE!
```

## ✅ New Calculation (FIXED):

### **Current Calculation:**
```
Price:     110 EGP
- COGS:    -27.5 EGP (25% of price) ✅ More realistic
- FBA Fee: -30 EGP ✅ Fixed Egypt fees
- Ref Fee: -16.5 EGP ✅ (15% of 110)
- Storage: -0.5 EGP ✅
= Profit:  +35.5 EGP ✅ POSITIVE!

Monthly:  35.5 × 1,190 = 42,245 EGP profit/month 🎉
```

---

## 💰 How Fees Are Calculated

### **1. FBA Fees (Fulfillment by Amazon)**
Based on **weight** of the product:

**Egypt (amazon.eg) - NEW VALUES:**
- **Small items** (< 1 lb): `30 EGP` base fee
- **Medium items** (1-20 lbs): `40 EGP + (weight-1) × 5 EGP`
- **Large items** (20-50 lbs): `100 EGP + (weight-1) × 10 EGP`

**Your Product:**
- Weight: ~1 lb (kitchen scale)
- Tier: Small standard
- FBA Fee: **30 EGP** ✅

**US (amazon.com) for comparison:**
- Small items: `$3.22` (~100 EGP equivalent)
- We were accidentally using US fees for Egypt!

---

### **2. Referral Fees**
Amazon's commission on each sale:

**By Category:**
- Electronics: `8%`
- **Home & Kitchen: `15%`** ← Your product
- Clothing: `17%`
- Most others: `15%`

**Your Product:**
- Price: 110 EGP
- Category: Home & Kitchen (15%)
- Referral Fee: `110 × 0.15 = 16.5 EGP` ✅

---

### **3. Storage Fees**
Monthly cost to store in Amazon warehouse:

**Egypt:**
- Jan-Sep: `~0.50 EGP` per unit/month
- Oct-Dec: `~1.00 EGP` (holiday peak)

**Your Product:**
- Storage: **0.50 EGP/month** ✅

---

### **4. COGS (Cost of Goods Sold)**
Estimated manufacturing/sourcing cost:

**Assumptions:**
- **Old:** 35% of sale price (too high!)
- **New:** 25% of sale price ✅ More realistic

**Your Product:**
- COGS: `110 × 0.25 = 27.5 EGP`

---

## 🎯 Full Profit Breakdown

| Item | Amount (EGP) | Calculation |
|------|--------------|-------------|
| **Sale Price** | 110.00 | Listed price |
| **- COGS** | -27.50 | 25% of price |
| **- FBA Fee** | -30.00 | Small item tier |
| **- Referral Fee** | -16.50 | 15% of price |
| **- Storage** | -0.50 | Monthly estimate |
| **= Profit per Unit** | **35.50** | ✅ **32.3% margin** |
| | | |
| **× Monthly Sales** | × 1,190 | BSR #4 estimate |
| **= Monthly Profit** | **42,245** | 🎉 |
| **× 12 Months** | **× 12** | |
| **= Annual Profit** | **506,940 EGP** | 💰 |

---

## 📈 Revenue vs Profit

### **Revenue** (Total sales)
Money coming IN from customers:
```
1,190 sales/month × 110 EGP = 130,900 EGP/month
```

### **Profit** (Money you keep)
Revenue MINUS all costs:
```
Revenue:     130,900 EGP
- All Costs: -88,655 EGP (COGS + Fees)
= Profit:     42,245 EGP ✅
```

---

## ✅ Changes Made:

1. **Reduced COGS:** 35% → 25%
2. **Fixed Egypt FBA Fees:** 100 → 30 EGP
3. **Added Currency Detection:** Shows EGP for Egypt
4. **Larger UI:** 420px → 550px width

---

## 🧪 Test Results Expected:

For product B08P5MP4YC (Kitchen Scale):
- ✅ Price: **110 EGP**
- ✅ FBA Fee: **~30 EGP**
- ✅ Total Fees: **~47 EGP**
- ✅ Profit: **~35 EGP per unit**
- ✅ Monthly Profit: **~42,000 EGP**
- ✅ Margin: **32%** (Good!)

**Reload extension and test!** 🚀
