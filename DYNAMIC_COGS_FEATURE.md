# ✨ New Feature: Dynamic COGS Calculation

## 🎯 **Goal**
Allow users to input their **REAL** manufacturing cost (COGS) to see accurate profit calculations, replacing the estimated 25% default.

## 🛠️ **Implementation Details**

### **1. Input Field**
Added a number input field in the Profit Analysis section:
- **Default value:** Shows the estimated 25% COGS as placeholder.
- **Input type:** Number (step 0.01).
- **Event:** Listens for `input` changes to calculate in real-time.

### **2. Dynamic Recalculation**
When you type a number (e.g., "95"), the tool immediately recalculates:
- **Profit per Unit:** `Price - Fees - New COGS`
- **Profit Margin:** `(Profit / Price) * 100`
- **ROI:** `(Profit / (COGS + Fees)) * 100`
- **Monthly Profit:** `Profit per Unit * Estimated Monthly Sales`

### **3. Visual Updates**
- Updates the **values** (amount) instantly.
- Updates the **color coding** (Excellent/Good/Poor) based on the new margin!
  - If your high COGS makes margins low, the text turns red.
  - If you secured a great price, it turns green!

---

## 🧪 **How to Test**

1. **Reload Extension** 🔄
2. **Analyze a Product**
3. **Find the "Your Real COGS" input** box in the Profit section
4. **Type a value:**
   - Try a **low cost** (e.g., 20 EGP) → Watch profit GO UP 🟢
   - Try a **high cost** (e.g., 90 EGP) → Watch profit GO DOWN 🔴

## 📝 **Why This Matters**
This transforms the tool from a "guesser" to a **precise business calculator**. You can now validate supplier quotes instantly!

**Example:**
*"Supplier offered me 85 EGP/unit. Can I make money?"*
👉 Type "85" into the box.
👉 Result: **NEGATIVE PROFIT**. ❌
👉 Conclusion: Negotiate better price or find another product!
