<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use App\Models\Ride;
use App\Services\DispatchService;
use Illuminate\Support\Facades\Log;

class DispatchRideJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $ride;
    
    /**
     * The number of times the job may be attempted.
     * 1-20: Dispatch batches (15s each) -> 5 minutes total
     * 21: Final check/cancel
     */
    public $tries = 21;

    /**
     * Create a new job instance.
     */
    public function __construct(Ride $ride)
    {
        $this->ride = $ride;
    }

    /**
     * Execute the job.
     */
    public function handle(DispatchService $dispatchService)
    {
        Log::info("🚀 [ZYGO_DEBUG] Processing DispatchRideJob for Ride #{$this->ride->id} (Attempt: " . $this->attempts() . ")");
        
        $this->ride->refresh();
        
        if ($this->ride->status !== 'searching') {
            Log::info("✅ [ZYGO_DEBUG] Ride #{$this->ride->id} already accepted/cancelled. Aborting job.");
            return;
        }

        if ($this->attempts() < $this->tries) {
            $dispatchSucceded = $dispatchService->dispatchBatch($this->ride);

            if (!$dispatchSucceded) {
                Log::info("⚠️ [ZYGO_DEBUG] No drivers found on attempt " . $this->attempts() . ", retrying in 15s...");
                $this->release(15);
                return;
            }

            $this->release(15);
        } else {
            if ($this->ride->status === 'searching') {
                Log::warning("🔴 [ZYGO_DEBUG] Dispatch exhausted for Ride #{$this->ride->id}. Marking as no_driver_found.");
                $this->ride->update([
                    'status' => 'no_driver_found',
                    'cancel_reason' => 'Timeout: 5 minutes search limit exceeded'
                ]);
                
                // Notify rider via Socket
                event(new \App\Events\RideRequestExpired($this->ride));
            }
        }
    }
}
