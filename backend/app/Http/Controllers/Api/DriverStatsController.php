<?php
namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Ride;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class DriverStatsController extends Controller
{
    /**
     * Get driver-specific statistics: completed trips, total earnings, rating, and level.
     */
    public function getStats(Request $request)
    {
        $user = $request->user();

        if ($user->role !== 'driver') {
            return response()->json(['message' => 'Unauthorized. Must be a driver.'], 403);
        }

        // 1. Calculate Completed Trips
        $completedTrips = Ride::where('driver_id', $user->id)
            ->where('status', 'completed')
            ->count();

        // 2. Calculate Total Earnings
        $totalEarnings = Ride::where('driver_id', $user->id)
            ->where('status', 'completed')
            ->sum('ride_price');

        // 3. Driver Rating (From User model snapshot)
        $rating = (float) ($user->rating ?? 5.0);

        // 4. Calculate Level (Simple logic: based on trips)
        $level = 'professional_driver';
        if ($completedTrips > 100 && $rating >= 4.7) {
            $level = 'master_driver';
        } else if ($completedTrips < 10) {
            $level = 'new_driver';
        }

        return response()->json([
            'completed_trips' => $completedTrips,
            'total_earnings' => (float) $totalEarnings,
            'rating' => $rating,
            'level' => $level,
        ]);
    }
}
