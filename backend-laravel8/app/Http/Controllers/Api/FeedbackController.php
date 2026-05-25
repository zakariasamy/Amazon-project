<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class FeedbackController extends Controller
{
    /**
     * Submit actual sales data for calibration
     */
    public function submitSales(Request $request)
    {
        $validated = $request->validate([
            'asin' => 'required|string|max:20',
            'marketplace' => 'required|string|max:30',
            'category' => 'required|string|max:100',
            'bsr' => 'required|integer|min:1',
            'estimated_sales' => 'required|integer|min:0',
            'actual_sales' => 'required|integer|min:0',
            'sales_window_days' => 'integer|min:1|max:365'
        ]);

        $salesWindowDays = $validated['sales_window_days'] ?? 30;
        
        // Normalize to 30-day equivalent
        $actualSalesNormalized = round($validated['actual_sales'] * (30 / $salesWindowDays));
        
        // Calculate error percentage
        $errorPercent = $validated['estimated_sales'] > 0 
            ? (($validated['estimated_sales'] - $actualSalesNormalized) / $actualSalesNormalized) * 100 
            : 0;

        $feedback = DB::table('sales_feedback')->insertGetId([
            'user_id' => $request->user()->id,
            'asin' => $validated['asin'],
            'marketplace' => $validated['marketplace'],
            'category' => $validated['category'],
            'bsr' => $validated['bsr'],
            'estimated_sales' => $validated['estimated_sales'],
            'actual_sales' => $validated['actual_sales'],
            'actual_sales_normalized' => $actualSalesNormalized,
            'sales_window_days' => $salesWindowDays,
            'error_percent' => round($errorPercent, 2),
            'created_at' => now(),
            'updated_at' => now()
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Sales feedback submitted successfully',
            'feedback_id' => $feedback,
            'normalized_sales' => $actualSalesNormalized,
            'error_percent' => round($errorPercent, 2)
        ], 201);
    }

    /**
     * Submit estimate correction
     */
    public function submitCorrection(Request $request)
    {
        $validated = $request->validate([
            'asin' => 'required|string|max:20',
            'marketplace' => 'required|string|max:30',
            'field' => 'required|string|max:50',
            'original_value' => 'required|string|max:255',
            'corrected_value' => 'required|string|max:255',
            'reason' => 'nullable|string|max:500'
        ]);

        $correction = DB::table('estimate_corrections')->insertGetId([
            'user_id' => $request->user()->id,
            'asin' => $validated['asin'],
            'marketplace' => $validated['marketplace'],
            'field' => $validated['field'],
            'original_value' => $validated['original_value'],
            'corrected_value' => $validated['corrected_value'],
            'reason' => $validated['reason'] ?? null,
            'created_at' => now()
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Correction submitted successfully',
            'correction_id' => $correction
        ], 201);
    }

    /**
     * Get user's feedback history
     */
    public function getHistory(Request $request)
    {
        $salesFeedback = DB::table('sales_feedback')
            ->where('user_id', $request->user()->id)
            ->orderByDesc('created_at')
            ->limit(50)
            ->get();

        $corrections = DB::table('estimate_corrections')
            ->where('user_id', $request->user()->id)
            ->orderByDesc('created_at')
            ->limit(50)
            ->get();

        return response()->json([
            'success' => true,
            'sales_feedback' => $salesFeedback,
            'corrections' => $corrections
        ]);
    }
}
