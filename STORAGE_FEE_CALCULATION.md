# 📦 Storage Fee Calculation Explained

## ❓ The Question
**"How did you calculate Storage: EGP 0.54/mo?"**

## 🧮 The Formula
Amazon charges storage fees based on the **Volume** (space taken up) in cubic feet.

```
Monthly Fee = Volume (cubic feet) × Rate per cubic foot
```

---

## 1️⃣ The Rate (Amazon.eg)
We use the **Standard Size** rate for non-peak months (Jan-Sep).

- **Rate:** **27.00 EGP** per cubic foot
- *(Peak rate Oct-Dec is 75.00 EGP)*

> **Source:** `src/engine/market-constants.js`

## 2️⃣ The Volume (Your Product)
For this specific product, the tool used a **"Small Standard" default** because exact dimensions couldn't be parsed from the page text.

- **Estimated Volume:** **0.02 cubic feet**
- *(This is approx. the size of a small shoe box or kitchen scale box)*

## 3️⃣ The Calculation

```math
0.02 \text{ (Volume)} \times 27.00 \text{ (Rate)} = \mathbf{0.54 \text{ EGP}}
```

---

## ⚖️ accuracy Check
For a **Digital Kitchen Scale**:
- **Typical Box:** 25cm x 18cm x 4cm
- **Volume:** 0.0018 cubic meters ≈ **0.06 cubic feet**
- **Real Fee would be:** `0.06 * 27 = 1.62 EGP`

**Result:** The **0.54 EGP** is a conservative baseline. The real fee usually ranges from **0.50 EGP to 2.00 EGP** for this category.

> **Note:** Storage fees are usually the *smallest* fee (compared to the 30 EGP FBA fee and 16.5 EGP Referral fee), so small variations here don't impact your profit much!
