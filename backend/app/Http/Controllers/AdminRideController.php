<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Ride;
use App\Models\User;
use App\Models\Setting;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class AdminRideController extends Controller
{
    public function index(Request $request)
    {
        $query = Ride::with(['rider', 'driver'])->orderBy('created_at', 'desc');

        // Status filter
        if ($request->filled('status') && $request->status !== 'all') {
            $query->where('status', $request->status);
        }

        $rides = $query->paginate(15)->appends($request->query());
        return view('admin.rides.index', compact('rides'));
    }

    /**
     * Update ride price from dashboard.
     */
    public function updatePrice(Request $request, $id)
    {
        $request->validate([
            'ride_price' => 'required|numeric|min:0',
        ]);

        $ride = Ride::findOrFail($id);
        $oldPrice = $ride->ride_price;
        $ride->update(['ride_price' => round($request->ride_price)]);

        Log::info("🔧 [ADMIN] Ride #{$id} price changed: {$oldPrice} → {$request->ride_price} by Admin #" . auth()->id());

        return redirect()->back()->with('success', "Ride #{$id} price updated: {$oldPrice} → {$request->ride_price} SYP");
    }

    /**
     * Force-complete a ride (status change only — no financial processing).
     */
    public function completeSimple($id)
    {
        $ride = Ride::findOrFail($id);

        if ($ride->status === 'completed') {
            return redirect()->back()->with('error', 'Ride is already completed.');
        }

        $ride->update([
            'status' => 'completed',
            'completed_at' => now(),
        ]);

        Log::info("🔧 [ADMIN] Ride #{$id} force-completed (no financials) by Admin #" . auth()->id());

        return redirect()->back()->with('success', "Ride #{$id} marked as completed (no financial processing).");
    }

    /**
     * Force-complete a ride WITH full financial processing (commission + wallet).
     */
    public function completeWithFinancials($id)
    {
        $ride = Ride::findOrFail($id);

        if ($ride->status === 'completed') {
            return redirect()->back()->with('error', 'Ride is already completed.');
        }

        if (!$ride->driver_id) {
            return redirect()->back()->with('error', 'Cannot process financials: No driver assigned.');
        }

        $settings = Setting::first();
        $commissionRate = $settings->commission_rate ?? 15.00;
        $ridePrice = $ride->ride_price;
        $commission = round(($ridePrice * $commissionRate) / 100);

        DB::transaction(function () use ($ride, $commission, $ridePrice, $commissionRate) {
            // 1. Complete the ride
            $ride->update([
                'status' => 'completed',
                'completed_at' => now(),
            ]);

            // 2. Deduct commission from driver wallet
            $driver = User::find($ride->driver_id);
            if ($driver) {
                $balanceBefore = $driver->wallet_balance;
                $driver->decrement('wallet_balance', $commission);

                // 3. Log the transaction
                DB::table('wallet_transactions')->insert([
                    'user_id' => $driver->id,
                    'transaction_type' => 'debit',
                    'amount' => -$commission,
                    'balance_before' => $balanceBefore,
                    'balance_after' => $balanceBefore - $commission,
                    'description' => "Admin force-complete: Commission ({$commissionRate}%) for Ride #{$ride->id}",
                    'ride_id' => $ride->id,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);

                Log::info("🔧 [ADMIN] Ride #{$ride->id} completed with financials. Commission: {$commission} SYP deducted from Driver #{$driver->id}");
            }
        });

        return redirect()->back()->with('success', "Ride #{$id} completed. Commission: {$commission} SYP deducted from driver.");
    }

    /**
     * Delete a ride.
     */
    public function destroy($id)
    {
        $ride = Ride::findOrFail($id);
        $ride->delete();

        Log::info("🔧 [ADMIN] Ride #{$id} deleted by Admin #" . auth()->id());

        return redirect()->back()->with('success', "Ride #{$id} deleted successfully.");
    }
}
