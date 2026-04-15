<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Redis;
use App\Models\User;
use Illuminate\Support\Facades\Log;

class DumpLocationsCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'drivers:dump-locations';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Dump real-time driver locations from Redis into MySQL database';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info("Starting Redis location dump...");
        
        try {
            // Get all driver IDs from the GEO index (Sorted Set)
            $driverIds = Redis::zrange('driver_locations', 0, -1);
            
            if (empty($driverIds)) {
                $this->info("No active driver locations found in Redis.");
                return 0;
            }

            // Get exact coordinates for all these drivers
            $coordinates = Redis::geopos('driver_locations', ...$driverIds);
            
            $count = 0;
            foreach ($driverIds as $index => $id) {
                if (!empty($coordinates[$index])) {
                    $lng = $coordinates[$index][0];
                    $lat = $coordinates[$index][1];
                    
                    User::where('id', $id)->update([
                        'last_latitude' => $lat,
                        'last_longitude' => $lng,
                        'last_location_at' => now(),
                    ]);
                    $count++;
                }
            }

            $this->info("Successfully dumped {$count} driver locations into the database.");
        } catch (\Exception $e) {
            Log::error("Failed to dump driver locations from Redis to DB: " . $e->getMessage());
            $this->error("Error: " . $e->getMessage());
            return 1;
        }
        
        return 0;
    }
}
