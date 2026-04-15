<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Ride;
use App\Events\RideRequestExpired;
use Illuminate\Support\Facades\Log;

class CheckExpiredRides extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'rides:check-expired';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Checks for ride requests that have exceeded their searching timeout and marks them as no_driver_found.';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        try {
            Log::info("⏲️ [ZYGO_DEBUG] CheckExpiredRides started...");
            
            $expiredRides = Ride::where('status', 'searching')
                ->where('request_expires_at', '<', now())
                ->get();

            if ($expiredRides->isNotEmpty()) {
                foreach ($expiredRides as $ride) {
                    Log::info("🔴 [ZYGO_DEBUG] Ride #{$ride->id} expired. No driver found in time.");
                    
                    $ride->update([
                        'status' => 'no_driver_found'
                    ]);

                    // Broadcast expiration to rider
                    event(new RideRequestExpired($ride));
                    
                    // Cleanup pending requests
                    $ride->requests()->where('status', 'sent')->update(['status' => 'cancelled']);
                }
            }
        } catch (\Exception $e) {
            Log::error("❌ [ZYGO_DEBUG] CheckExpiredRides failed: " . $e->getMessage());
            return 1; // Return failure code for artisan trace
        }
        return 0; // Return success
    }
}
