<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\CalibrationService;
use Illuminate\Http\Request;

class CalibrationController extends Controller
{
    protected $calibrationService;

    public function __construct(CalibrationService $calibrationService)
    {
        $this->calibrationService = $calibrationService;
    }

    /**
     * Get calibration status for a category
     */
    public function status(Request $request)
    {
        $validated = $request->validate([
            'marketplace' => 'required|string|max:30',
            'category' => 'required|string|max:100'
        ]);

        // Get recent calibration log
        $log = \DB::table('calibration_log')
            ->where('marketplace', $validated['marketplace'])
            ->where('category', $validated['category'])
            ->orderByDesc('applied_at')
            ->limit(5)
            ->get();

        // Get current constant
        $current = \DB::table('algorithm_constants')
            ->where('marketplace', $validated['marketplace'])
            ->where('category', $validated['category'])
            ->where('is_active', true)
            ->first();

        // Get feedback count
        $feedbackCount = \DB::table('sales_feedback')
            ->where('marketplace', $validated['marketplace'])
            ->where('category', $validated['category'])
            ->where('created_at', '>=', now()->subDays(30))
            ->count();

        return response()->json([
            'success' => true,
            'current_constant' => $current,
            'feedback_count_30d' => $feedbackCount,
            'min_required' => CalibrationService::MIN_SAMPLES,
            'can_calibrate' => $feedbackCount >= CalibrationService::MIN_SAMPLES,
            'recent_calibrations' => $log
        ]);
    }

    /**
     * Manually trigger calibration (admin only)
     */
    public function trigger(Request $request)
    {
        $validated = $request->validate([
            'marketplace' => 'required|string|max:30',
            'category' => 'required|string|max:100'
        ]);

        // Check if user is admin (you can implement proper admin check)
        $user = $request->user();
        if ($user->subscription_tier !== 'enterprise') {
            return response()->json([
                'success' => false,
                'message' => 'Only enterprise users can trigger manual calibration'
            ], 403);
        }

        $result = $this->calibrationService->recalibrate(
            $validated['marketplace'],
            $validated['category']
        );

        return response()->json([
            'success' => $result['adjusted'] ?? false,
            'result' => $result
        ]);
    }

    /**
     * Run full calibration (scheduled job)
     */
    public function runFull(Request $request)
    {
        // Check if user is admin
        $user = $request->user();
        if ($user->subscription_tier !== 'enterprise') {
            return response()->json([
                'success' => false,
                'message' => 'Only enterprise users can trigger full calibration'
            ], 403);
        }

        $results = $this->calibrationService->runFullCalibration();

        return response()->json([
            'success' => true,
            'categories_processed' => count($results),
            'results' => $results
        ]);
    }
}
