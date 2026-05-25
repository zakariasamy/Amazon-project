<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Cache;

class SeasonalityController extends Controller
{
    /**
     * Get seasonality multipliers
     */
    public function index(Request $request)
    {
        $year = $request->get('year', date('Y'));
        $marketplace = $request->get('marketplace');

        $query = DB::table('seasonality_factors')
            ->where('year', $year);

        if ($marketplace) {
            $query->where('marketplace', $marketplace);
        }

        $seasonality = Cache::remember("seasonality_{$year}_{$marketplace}", 86400, function () use ($query) {
            return $query->orderBy('marketplace')->orderBy('month')->get();
        });

        return response()->json([
            'success' => true,
            'year' => $year,
            'data' => $seasonality
        ]);
    }
}
