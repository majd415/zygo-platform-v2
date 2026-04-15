<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Ride;
use App\Jobs\DispatchRideJob;
use Carbon\Carbon;

class ProcessScheduledRides extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'rides:process-scheduled';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Find and process scheduled rides that are due for dispatch';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Checking for scheduled rides due...');

        // Find rides with status 'scheduled' where scheduled_at is now or in the past
        $rides = Ride::where('status', 'scheduled')
            ->where('scheduled_at', '<=', Carbon::now())
            ->get();

        if ($rides->isEmpty()) {
            $this->info('No rides due for dispatch.');
            return;
        }

        foreach ($rides as $ride) {
            $this->info("Processing ride #{$ride->id} for rider #{$ride->rider_id}");

            // Update status to searching
            $ride->update([
                'status' => 'searching'
            ]);

            // Dispatch the ride (this will notify nearby drivers)
            DispatchRideJob::dispatch($ride);

            $this->info("Ride #{$ride->id} dispatched successfully.");
        }

        $this->info('Done processing scheduled rides.');
    }
}
