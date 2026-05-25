<?php

namespace App\Services;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

class CalibrationService
{
    // Minimum unique ASINs required for calibration
    const MIN_SAMPLES = 5;
    
    // Maximum adjustment per cycle (±15%)
    const MAX_ADJUSTMENT = 0.15;
    
    // Cap errors at ±60% to prevent outliers
    const ERROR_WINSORIZE = 0.60;
    
    // Days between adjustments
    const COOLDOWN_DAYS = 7;

    /**
     * Recalibrate constants for a specific category
     */
    public function recalibrate(string $marketplace, string $category): array
    {
        // Get recent feedback samples
        $samples = $this->getSamples($marketplace, $category);

        // Guardrail 1: Minimum sample size
        if (count($samples) < self::MIN_SAMPLES) {
            return [
                'adjusted' => false,
                'reason' => 'insufficient_samples',
                'sample_count' => count($samples),
                'required' => self::MIN_SAMPLES
            ];
        }

        // Guardrail 2: Check cooldown
        $lastAdjust = $this->getLastAdjustment($marketplace, $category);
        if ($lastAdjust && $lastAdjust->applied_at) {
            $daysSince = now()->diffInDays($lastAdjust->applied_at);
            if ($daysSince < self::COOLDOWN_DAYS) {
                return [
                    'adjusted' => false,
                    'reason' => 'cooldown_active',
                    'days_remaining' => self::COOLDOWN_DAYS - $daysSince
                ];
            }
        }

        // Guardrail 3: Winsorize errors (cap at ±60%)
        $errors = array_map(function ($sample) {
            if ($sample->actual_sales_normalized == 0) return 0;
            $error = ($sample->estimated_sales - $sample->actual_sales_normalized) / $sample->actual_sales_normalized;
            return max(-self::ERROR_WINSORIZE, min(self::ERROR_WINSORIZE, $error));
        }, $samples);

        $avgError = array_sum($errors) / count($errors);

        // Only adjust if error is significant (> 30%)
        if (abs($avgError) < 0.30) {
            return [
                'adjusted' => false,
                'reason' => 'within_tolerance',
                'avg_error_percent' => round($avgError * 100, 2),
                'sample_count' => count($samples)
            ];
        }

        // Calculate adjustment (inverse of error)
        $adjustment = -$avgError;

        // Guardrail 4: Cap adjustment magnitude
        $adjustment = max(-self::MAX_ADJUSTMENT, min(self::MAX_ADJUSTMENT, $adjustment));

        // Get current C value
        $currentConstant = $this->getCurrentConstant($marketplace, $category);
        if (!$currentConstant) {
            return [
                'adjusted' => false,
                'reason' => 'constant_not_found'
            ];
        }

        $currentC = $currentConstant->c_value;
        $newC = round($currentC * (1 + $adjustment));

        // Apply the adjustment
        $this->applyAdjustment($marketplace, $category, $currentC, $newC, $avgError, count($samples));

        // Clear cache
        Cache::forget("constants_{$marketplace}");
        Cache::forget('algorithm_constants');

        Log::info("Calibration applied", [
            'marketplace' => $marketplace,
            'category' => $category,
            'previous_c' => $currentC,
            'new_c' => $newC,
            'avg_error' => round($avgError * 100, 2) . '%',
            'samples' => count($samples)
        ]);

        return [
            'adjusted' => true,
            'previous_c' => $currentC,
            'new_c' => $newC,
            'adjustment_percent' => round($adjustment * 100, 2),
            'avg_error_percent' => round($avgError * 100, 2),
            'sample_count' => count($samples)
        ];
    }

    /**
     * Run calibration for all categories with sufficient data
     */
    public function runFullCalibration(): array
    {
        $results = [];

        // Get all categories with feedback
        $categories = DB::table('sales_feedback')
            ->where('created_at', '>=', now()->subDays(30))
            ->select('marketplace', 'category', DB::raw('COUNT(*) as sample_count'))
            ->groupBy('marketplace', 'category')
            ->having('sample_count', '>=', self::MIN_SAMPLES)
            ->get();

        foreach ($categories as $cat) {
            $result = $this->recalibrate($cat->marketplace, $cat->category);
            $results[] = [
                'marketplace' => $cat->marketplace,
                'category' => $cat->category,
                'result' => $result
            ];
        }

        return $results;
    }

    /**
     * Get feedback samples for a category
     */
    private function getSamples(string $marketplace, string $category): array
    {
        return DB::table('sales_feedback')
            ->where('marketplace', $marketplace)
            ->where('category', $category)
            ->where('created_at', '>=', now()->subDays(30))
            ->orderByDesc('created_at')
            ->limit(50)
            ->get()
            ->toArray();
    }

    /**
     * Get last calibration adjustment
     */
    private function getLastAdjustment(string $marketplace, string $category)
    {
        return DB::table('calibration_log')
            ->where('marketplace', $marketplace)
            ->where('category', $category)
            ->orderByDesc('applied_at')
            ->first();
    }

    /**
     * Get current constant for category
     */
    private function getCurrentConstant(string $marketplace, string $category)
    {
        return DB::table('algorithm_constants')
            ->where('marketplace', $marketplace)
            ->where('category', $category)
            ->where('is_active', true)
            ->first();
    }

    /**
     * Apply the calibration adjustment
     */
    private function applyAdjustment(
        string $marketplace,
        string $category,
        float $previousC,
        float $newC,
        float $avgError,
        int $sampleCount
    ): void {
        // Update the constant
        DB::table('algorithm_constants')
            ->where('marketplace', $marketplace)
            ->where('category', $category)
            ->where('is_active', true)
            ->update([
                'c_value' => $newC,
                'updated_at' => now()
            ]);

        // Log the calibration
        DB::table('calibration_log')->insert([
            'marketplace' => $marketplace,
            'category' => $category,
            'previous_c' => $previousC,
            'new_c' => $newC,
            'avg_error_percent' => round($avgError * 100, 2),
            'sample_count' => $sampleCount,
            'applied_at' => now()
        ]);
    }
}
