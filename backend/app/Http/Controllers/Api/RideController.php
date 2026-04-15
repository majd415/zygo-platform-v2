<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Ride;
use App\Models\RideRequest;
use App\Services\DispatchService;
use App\Events\RideAccepted;
use App\Events\DriverLocationUpdated;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Log;

class RideController extends Controller
{
    protected $dispatchService;

    public function __construct(DispatchService $dispatchService)
    {
        $this->dispatchService = $dispatchService;
    }

    /**
     * Store a ride request and start dispatching.
     */
    public function requestRide(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'pickup_lat' => 'required|numeric',
            'pickup_lng' => 'required|numeric',
            'pickup_address' => 'required|string',
            'destination_lat' => 'required|numeric',
            'destination_lng' => 'required|numeric',
            'destination_address' => 'required|string',
            'fare' => 'required|numeric',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        // Enforce minimum fare from settings
        $settings = \App\Models\Setting::first();
        $minFare = $settings->min_fare_syp ?? 5000;
        $fare = round(max((float)$request->fare, (float)$minFare));

        $ride = Ride::create([
            'rider_id' => $request->user()->id,
            'status' => 'searching',
            'ride_code' => strtoupper(substr(md5(uniqid()), 0, 6)),
            'share_token' => substr(md5(uniqid(rand(), true)), 0, 16),
            'pickup_lat' => $request->pickup_lat,
            'pickup_lng' => $request->pickup_lng,
            'pickup_address' => $request->pickup_address,
            'dropoff_lat' => $request->destination_lat,
            'dropoff_lng' => $request->destination_lng,
            'dropoff_address' => $request->destination_address,
            'ride_price' => $fare,
            'car_type' => $request->car_type ?? 'economy',
            'payment_method' => $request->payment_method ?? 'cash',
            'coupon_id' => $request->coupon_id,
            'discount_amount' => $request->discount_amount ?? 0,
            'distance_text' => $request->distance_text,
            'duration_text' => $request->duration_text,
            'request_expires_at' => now()->addSeconds(20),
        ]);

        Log::info("🔵 [ZYGO_DEBUG] Ride dispatch started: Ride #{$ride->id}");
        Log::info("🔍 [ZYGO_DEBUG] Selecting nearest drivers for Ride #{$ride->id}");
        
        // Start dispatching (Phase 3: Queued Background Job)
        \App\Jobs\DispatchRideJob::dispatch($ride);

        return response()->json([
            'message' => __('messages.ride_requested'),
            'ride' => $ride
        ]);
    }

    /**
     * Driver accepts a ride.
     */
    public function acceptRide(Request $request, $id)
    {
        $driver = $request->user();

        return \Illuminate\Support\Facades\DB::transaction(function () use ($id, $driver) {
            Log::info("🔨 [ZYGO_DEBUG] Ride acceptance transaction started for Ride #{$id}");
            
            // SELECT FOR UPDATE to prevent race conditions
            $ride = Ride::where('id', $id)->lockForUpdate()->firstOrFail();
            Log::info("🔒 [ZYGO_DEBUG] Ride #{$id} locked for update");

            if ($ride->status !== 'searching') {
                Log::warning("⚠️ [ZYGO_DEBUG] Driver #{$driver->id} tried to accept Ride #{$id} but status is '{$ride->status}'");
                $msg = $ride->status === 'cancelled' 
                    ? __('messages.ride_no_longer_available') 
                    : __('messages.ride_already_accepted');
                return response()->json(['message' => $msg], 422);
            }

            // Update ride
            $ride->update([
                'driver_id' => $driver->id,
                'status' => 'accepted',
                'accepted_at' => now(),
            ]);

            Log::info("✅ [ZYGO_DEBUG] Ride #{$id} accepted by driver #{$driver->id}");

            // Update successful request
            RideRequest::where('ride_id', $ride->id)
                ->where('driver_id', $driver->id)
                ->update(['status' => 'accepted']);

            // Cancel all other pending requests for this ride
            $otherRequests = RideRequest::where('ride_id', $ride->id)
                ->where('driver_id', '!=', $driver->id)
                ->where('status', 'sent')
                ->get();

            foreach ($otherRequests as $req) {
                $req->update(['status' => 'cancelled']);
                // Notify other drivers that the ride is no longer available
                event(new \App\Events\RideNoLongerAvailable($ride->id, $req->driver_id));
                Log::info("📢 [ZYGO_DEBUG] Notifying other drivers: rideNoLongerAvailable for Ride #{$ride->id}");
            }

            // Notify rider via WebSocket
            Log::info("📡 [ZYGO_DEBUG] Broadcasting driverAcceptedRide for Ride #{$ride->id}");
            event(new RideAccepted($ride, $driver));
            
            // Legacy status update
            event(new \App\Events\RideStatusUpdated($ride, "ride.accepted"));

            // Load ride with rider info for response
            $ride->load('rider');

            return response()->json([
                'message' => __('messages.ride_accepted'),
                'ride' => $ride,
                'rider' => $ride->rider
            ]);
        });
    }

    /**
     * Update driver's live location.
     */
    public function updateLocation(Request $request, $id)
    {
        $ride = Ride::find($id);
        
        $validator = Validator::make($request->all(), [
            'lat' => 'required|numeric',
            'lng' => 'required|numeric',
            'heading' => 'nullable|numeric',
        ]);

        Log::info("Driver location updated for ride #{$id}");

        if ($validator->fails()) return response()->json($validator->errors(), 422);

        // Always update driver's last known location (even if ride is gone)
        $driver = $request->user();
        
        try {
            \Illuminate\Support\Facades\Redis::geoadd('driver_locations', $request->lng, $request->lat, $driver->id);
        } catch (\Exception $e) {
            \Illuminate\Support\Facades\Log::warning("Redis GEOADD failed: " . $e->getMessage());
        }

        $driver->update([
            'last_latitude' => $request->lat,
            'last_longitude' => $request->lng,
            'last_bearing' => $request->heading ?? 0,
            'last_location_at' => now(),
        ]);

        // only broadcast if ride exists and is active
        if ($ride && in_array($ride->status, ['accepted', 'arrived', 'started'])) {
            event(new DriverLocationUpdated($ride->id, $request->lat, $request->lng, $request->heading ?? 0));
        }

        return response()->json(['message' => __('messages.location_updated')]);
    }

    /**
     * Update ride status (arrived, started, etc.)
     */
    public function updateStatus(Request $request, $id)
    {
        $status = $request->status;

        $validStatuses = ['arrived', 'started', 'completed', 'cancelled'];
        if (!in_array($status, $validStatuses)) {
            return response()->json(['message' => 'Invalid status.'], 400);
        }

        // Block rider from cancelling once the trip has started
        if ($status === 'cancelled') {
            $ride = Ride::findOrFail($id);
            if ($ride->status === 'started') {
                $user = $request->user();
                if ($user && $user->id === $ride->rider_id) {
                    return response()->json(['message' => 'Cannot cancel a ride that has already started.'], 403);
                }
            }
        }

        // Use DB transaction with row locking to prevent duplicate charges
        // from concurrent requests (polling + WebSocket + button press)
        return \Illuminate\Support\Facades\DB::transaction(function () use ($request, $id, $status) {
            // Lock the ride row to prevent concurrent modifications
            $ride = Ride::where('id', $id)->lockForUpdate()->firstOrFail();

            // Idempotency Guard: If ride is already in the requested status,
            // return success without re-processing (prevents double charges)
            if ($ride->status === $status) {
                Log::info("[ZYGO_GUARD] Idempotent request: Ride #{$id} already '{$status}', skipping.");
                return response()->json([
                    'message' => __('messages.status_updated', ['status' => $status]),
                    'ride' => $ride,
                    'idempotent' => true,
                ]);
            }

            // Also guard against going backwards (e.g. completed -> started)
            $statusOrder = ['searching' => 0, 'accepted' => 1, 'arrived' => 2, 'started' => 3, 'completed' => 4, 'cancelled' => 5];
            $currentOrder = $statusOrder[$ride->status] ?? -1;
            $newOrder = $statusOrder[$status] ?? -1;
            if ($newOrder <= $currentOrder && $status !== 'cancelled') {
                Log::info("[ZYGO_GUARD] Blocked backward transition: Ride #{$id} {$ride->status} -> {$status}");
                return response()->json([
                    'message' => __('messages.status_updated', ['status' => $ride->status]),
                    'ride' => $ride,
                    'idempotent' => true,
                ]);
            }

            $updateData = ['status' => $status];
            if ($status === 'arrived') $updateData['arrived_at'] = now();
            if ($status === 'started') $updateData['started_at'] = now();
            if ($status === 'completed') {
                $updateData['completed_at'] = now();
                
                // Calculate Commission
                $settings = \App\Models\Setting::first();
                $commissionRate = $settings->commission_rate ?? 15.00;
                $ridePrice = $ride->ride_price ?? 0;
                
                $commissionAmount = ($ridePrice * $commissionRate) / 100;
                $driverEarnings = $ridePrice - $commissionAmount;
                
                $updateData['commission_amount'] = $commissionAmount;
                $updateData['driver_earnings'] = $driverEarnings;

                \Log::info("[ZYGO_PAY] Processing payment for Ride #{$ride->id} | Price: {$ridePrice} | Commission: {$commissionAmount} | Driver Earnings: {$driverEarnings} | Method: {$ride->payment_method}");

                // ======= PAYMENT PROCESSING =======
                $driver = $ride->driver;
                $rider = $ride->rider;

                if ($ride->payment_method === 'cash') {
                    if ($driver) {
                        $driverBalanceBefore = $driver->wallet_balance;
                        $driver->wallet_balance -= $commissionAmount;
                        $driver->save();

                        \App\Models\WalletTransaction::create([
                            'user_id' => $driver->id,
                            'user_type' => 'driver',
                            'amount' => $commissionAmount,
                            'type' => 'debit',
                            'transaction_type' => 'commission',
                            'description' => "Platform commission for Ride #{$ride->id} (Cash)",
                            'balance_before' => $driverBalanceBefore,
                            'balance_after' => $driver->wallet_balance,
                            'ride_id' => $ride->id,
                        ]);

                        \Log::info("[ZYGO_PAY] CASH: Deducted {$commissionAmount} from Driver #{$driver->id}. Before: {$driverBalanceBefore}, After: {$driver->wallet_balance}");
                    }
                } elseif ($ride->payment_method === 'wallet') {
                    if ($rider && $driver) {
                        if ($rider->wallet_balance < $ridePrice) {
                            \Log::warning("[ZYGO_PAY] WALLET: Rider #{$rider->id} insufficient balance! Has: {$rider->wallet_balance}, Needs: {$ridePrice}.");
                        }

                        $riderBalanceBefore = $rider->wallet_balance;
                        $rider->wallet_balance -= $ridePrice;
                        $rider->save();

                        \App\Models\WalletTransaction::create([
                            'user_id' => $rider->id,
                            'user_type' => 'rider',
                            'amount' => $ridePrice,
                            'type' => 'debit',
                            'transaction_type' => 'ride_payment',
                            'description' => "Ride payment for Ride #{$ride->id}",
                            'balance_before' => $riderBalanceBefore,
                            'balance_after' => $rider->wallet_balance,
                            'ride_id' => $ride->id,
                        ]);

                        \Log::info("[ZYGO_PAY] WALLET: Deducted {$ridePrice} from Rider #{$rider->id}. Before: {$riderBalanceBefore}, After: {$rider->wallet_balance}");

                        $driverBalanceBefore = $driver->wallet_balance;
                        $driver->wallet_balance += $driverEarnings;
                        $driver->save();

                        \App\Models\WalletTransaction::create([
                            'user_id' => $driver->id,
                            'user_type' => 'driver',
                            'amount' => $driverEarnings,
                            'type' => 'credit',
                            'transaction_type' => 'ride_payment',
                            'description' => "Earnings from Ride #{$ride->id} (Wallet)",
                            'balance_before' => $driverBalanceBefore,
                            'balance_after' => $driver->wallet_balance,
                            'ride_id' => $ride->id,
                        ]);

                        \Log::info("[ZYGO_PAY] WALLET: Credited {$driverEarnings} to Driver #{$driver->id}. Before: {$driverBalanceBefore}, After: {$driver->wallet_balance}");
                    }
                }

                // Track platform earnings
                if ($settings) {
                    $settings->platform_earnings = ($settings->platform_earnings ?? 0) + $commissionAmount;
                    $settings->save();
                    \Log::info("[ZYGO_PAY] Platform earnings updated: +{$commissionAmount}. Total: {$settings->platform_earnings}");
                }
            }
            if ($status === 'cancelled') {
                $updateData['cancelled_at'] = now();
                if ($request->has('reason')) {
                    $updateData['cancel_reason'] = $request->reason;
                }
            }

            // Update status INSIDE the transaction (locked row)
            $ride->update($updateData);

        // Invalidate Cache for history
        try {
            \Illuminate\Support\Facades\Cache::store('redis')->forget("user_{$ride->rider_id}_ride_history");
            if ($ride->driver_id) {
                \Illuminate\Support\Facades\Cache::store('redis')->forget("user_{$ride->driver_id}_ride_history");
            }
        } catch (\Exception $e) {}

        // Broadcast status update
        event(new \App\Events\RideStatusUpdated($ride, "ride.$status"));

        // Send FCM push to rider for important status changes
        if (in_array($status, ['arrived', 'started', 'completed'])) {
            $rider = $ride->rider;
            if ($rider && $rider->fcm_token) {
                $titles = [
                    'arrived' => 'Driver Arrived',
                    'started' => 'Trip Started',
                    'completed' => 'Trip Completed',
                ];
                $bodies = [
                    'arrived' => 'Your driver has arrived at the pickup location!',
                    'started' => 'Your trip has started. Enjoy the ride!',
                    'completed' => 'Your trip is complete. Please rate your driver.',
                ];
                \App\Services\FirebaseService::sendNotification(
                    $rider->fcm_token,
                    $titles[$status],
                    $bodies[$status],
                    [
                        'type' => "ride_$status",
                        'ride_id' => (string) $ride->id,
                    ]
                );
            }
        }

        // Send FCM push to driver if rider cancels
        if ($status === 'cancelled') {
            if ($ride->driver_id) {
                $driver = $ride->driver;
                if ($driver && $driver->fcm_token) {
                    \App\Services\FirebaseService::sendNotification(
                        $driver->fcm_token,
                        'Ride Cancelled',
                        'The rider has cancelled the ride.',
                        [
                            'type' => 'ride_cancelled',
                            'ride_id' => (string) $ride->id,
                        ]
                    );
                }
            } else {
                // Not accepted yet, notify all candidate drivers to dismiss dialogs
                $pendingRequests = \App\Models\RideRequest::where('ride_id', $ride->id)
                    ->where('status', 'sent')
                    ->get();
                
                foreach ($pendingRequests as $req) {
                    $req->update(['status' => 'cancelled']);
                    event(new \App\Events\RideNoLongerAvailable($ride->id, $req->driver_id));
                    
                    $driverObj = \App\Models\User::find($req->driver_id);
                    if ($driverObj && $driverObj->fcm_token) {
                        \App\Services\FirebaseService::sendNotification(
                            $driverObj->fcm_token,
                            'Ride Cancelled',
                            'The rider cancelled the request.',
                            [
                                'type' => 'ride_cancelled',
                                'ride_id' => (string) $ride->id,
                            ]
                        );
                    }
                }
            }
        }

        // Special: If completed, broadcast live stats to admin dashboard
        if ($status === 'completed') {
            event(new \App\Events\BroadcastAdminStats([
                'revenue_increment' => $ride->ride_price,
                'total_completed_rides_increment' => 1
            ]));
        }

        return response()->json([
            'message' => __('messages.status_updated', ['status' => $status]),
            'ride' => $ride
        ]);
        }); // End DB::transaction
    }

    /**
     * Rate a driver after trip completion.
     */
    public function rateDriver(Request $request, $id)
    {
        $validator = Validator::make($request->all(), [
            'rating' => 'required|integer|min:1|max:5',
            'comment' => 'nullable|string|max:500',
        ]);

        if ($validator->fails()) return response()->json($validator->errors(), 422);

        $ride = Ride::findOrFail($id);
        $rider = $request->user();

        if ($ride->rider_id !== $rider->id) {
            return response()->json(['message' => __('messages.forbidden')], 403);
        }

        if (!in_array($ride->status, ['completed', 'cancelled', 'arrived', 'started'])) {
            return response()->json(['message' => 'Cannot rate an incomplete ride.'], 422);
        }

        if ($ride->rating !== null) {
            return response()->json(['message' => 'Ride already rated.'], 422);
        }

        // Save rating in normalized table
        \App\Models\DriverRating::create([
            'ride_id' => $ride->id,
            'driver_id' => $ride->driver_id,
            'passenger_id' => $rider->id,
            'rating' => $request->rating,
            'comment' => $request->comment,
        ]);

        // Duplicate for legacy support in rides table
        $ride->update([
            'rating' => $request->rating,
            'rating_comment' => $request->comment,
        ]);

        // Update driver's global rating using weighted average from all records
        $driver = $ride->driver;
        if ($driver) {
            $stats = \App\Models\DriverRating::where('driver_id', $driver->id)
                ->selectRaw('COUNT(*) as count, AVG(rating) as average')
                ->first();
            
            $driver->update([
                'rating' => round($stats->average, 2),
                'rating_count' => $stats->count,
            ]);
        }

        return response()->json(['message' => 'Thank you for your feedback!', 'driver_rating' => $driver->rating]);
    }

    /**
     * Get the user's current active ride (if any).
     * Used to resume ride UI when app re-opens.
     */
    public function getActiveRide(Request $request)
    {
        $user = $request->user();

        if ($user->role === 'driver') {
            $ride = Ride::where('driver_id', $user->id)
                ->whereIn('status', ['accepted', 'arrived', 'started'])
                ->with('rider')
                ->latest()
                ->first();
        } else {
            $ride = Ride::where('rider_id', $user->id)
                ->whereIn('status', ['searching', 'accepted', 'arrived', 'started'])
                ->with('driver')
                ->latest()
                ->first();
        }

        if (!$ride) {
            return response()->json(['ride' => null]);
        }

        $response = [
            'ride' => $ride,
            'ride_id' => $ride->id, // Top-level for easy access
            'status' => $ride->status,
        ];

        // Add driver details for rider
        if ($user->role !== 'driver' && $ride->driver) {
            $driverDoc = \App\Models\DriverDocument::where('user_id', $ride->driver_id)->first();
            $response['driver'] = [
                'name' => $ride->driver->name,
                'phone' => $ride->driver->phone,
                'photo' => $ride->driver->avatar_url ?? '',
                'rating' => $ride->driver->rating ?? 4.93,
                'latitude' => $ride->driver->last_latitude,
                'longitude' => $ride->driver->last_longitude,
                'bearing' => $ride->driver->last_bearing ?? 0,
                'vehicle' => [
                    'model' => $driverDoc?->car_model ?? 'Standard',
                    'plate' => $driverDoc?->car_plate ?? 'N/A',
                    'color' => $driverDoc?->car_color ?? 'White',
                ],
            ];
        }

        // Add rider details for driver
        if ($user->role === 'driver' && $ride->rider) {
            $response['rider'] = [
                'name' => $ride->rider->name,
                'phone' => $ride->rider->phone,
                'photo' => $ride->rider->avatar_url ?? '',
            ];
        }

        return response()->json($response);
    }

    /**
     * Get ride history for the authenticated user, with Redis Cache fallback
     */
    public function history(Request $request)
    {
        $user = $request->user();
        $cacheKey = "user_{$user->id}_ride_history";

        try {
            $rides = \Illuminate\Support\Facades\Cache::store('redis')->remember($cacheKey, 60, function () use ($user) {
                return $this->fetchHistoryFromDb($user);
            });
        } catch (\Exception $e) {
            \Illuminate\Support\Facades\Log::warning("Redis failed for History: " . $e->getMessage());
            $rides = $this->fetchHistoryFromDb($user);
        }

        return response()->json($rides);
    }

    protected function fetchHistoryFromDb($user)
    {
        $query = Ride::with(['driver', 'rider']);
        if ($user->role === 'driver') {
            $query->where('driver_id', $user->id);
        } else {
            $query->where('rider_id', $user->id);
        }
        return $query->orderBy('created_at', 'desc')->get();
    }

    /**
     * Schedule a ride for later.
     */
    public function scheduleRide(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'pickup_lat' => 'required|numeric',
            'pickup_lng' => 'required|numeric',
            'pickup_address' => 'required|string',
            'destination_lat' => 'required|numeric',
            'destination_lng' => 'required|numeric',
            'destination_address' => 'required|string',
            'car_type' => 'required|string',
            'scheduled_at' => 'required|date',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $ride = Ride::create([
            'rider_id' => $request->user()->id,
            'status' => 'scheduled',
            'pickup_lat' => $request->pickup_lat,
            'pickup_lng' => $request->pickup_lng,
            'pickup_address' => $request->pickup_address,
            'dropoff_lat' => $request->destination_lat,
            'dropoff_lng' => $request->destination_lng,
            'dropoff_address' => $request->destination_address,
            'ride_price' => $request->fare ?? 0,
            'car_type' => $request->car_type,
            'scheduled_at' => \Carbon\Carbon::parse($request->scheduled_at)->format('Y-m-d H:i:s'),
        ]);

        return response()->json([
            'message' => 'Ride scheduled successfully',
            'ride' => $ride
        ]);
    }
    /**
     * Get heatmap data (recent ride requests).
     */
    public function getHeatmapData()
    {
        $locations = Ride::where('created_at', '>=', now()->subHours(24))
            ->where('status', '!=', 'cancelled')
            ->select('pickup_lat', 'pickup_lng')
            ->get()
            ->map(function ($ride) {
                return [
                    'lat' => (double) $ride->pickup_lat,
                    'lng' => (double) $ride->pickup_lng,
                ];
            });

        // Mock Fallback: If no real rides in 24h, generate some demand markers around the city center (Damascus)
        if ($locations->isEmpty()) {
            $mockData = [];
            $centerLat = 33.5138;
            $centerLng = 36.2765;
            for ($i = 0; $i < 15; $i++) {
                $mockData[] = [
                    'lat' => $centerLat + (rand(-100, 100) / 5000),
                    'lng' => $centerLng + (rand(-100, 100) / 5000),
                ];
            }
            return response()->json($mockData);
        }

        return response()->json($locations);
    }

    /**
     * Get ride status (lightweight endpoint for polling backup).
     */
    public function getRideStatus($id)
    {
        $ride = Ride::find($id);
        if (!$ride) {
            \Log::info("[ZYGO_POLL] getRideStatus: Ride #{$id} not found");
            return response()->json(['status' => 'not_found'], 404);
        }

        \Log::info("[ZYGO_POLL] getRideStatus: Ride #{$id} => {$ride->status}");

        return response()->json([
            'ride_id' => $ride->id,
            'status' => $ride->status,
            'ride_price' => $ride->ride_price,
            'distance_text' => $ride->distance_text,
            'duration_text' => $ride->duration_text,
            'pickup_address' => $ride->pickup_address,
            'dropoff_address' => $ride->dropoff_address,
        ]);
    }
    public function trackByToken($token) {
        $ride = \App\Models\Ride::where("share_token", $token)->first();
        if (!$ride) return response()->json(["status" => "not_found"], 404);
        if (in_array($ride->status, ["completed", "cancelled"])) return response()->json(["status" => "ended", "final_status" => $ride->status]);
        $driver = $ride->driver;
        $driverDoc = $driver ? \App\Models\DriverDocument::where("user_id", $driver->id)->first() : null;
        return response()->json(["status" => $ride->status, "pickup_lat" => (float)$ride->pickup_lat, "pickup_lng" => (float)$ride->pickup_lng, "pickup_address" => $ride->pickup_address, "dropoff_lat" => (float)$ride->dropoff_lat, "dropoff_lng" => (float)$ride->dropoff_lng, "dropoff_address" => $ride->dropoff_address, "driver" => $driver ? ["name" => $driver->name, "lat" => (float)$driver->last_latitude, "lng" => (float)$driver->last_longitude, "bearing" => (float)($driver->last_bearing ?? 0), "vehicle" => ["model" => $driverDoc?->car_model ?? "Car", "plate" => $driverDoc?->car_plate ?? "", "color" => $driverDoc?->car_color ?? ""]] : null]);
    }
}
