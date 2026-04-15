<?php
require_once '../config.php';
require_once '../models/Model.php';
require_once '../models/RideModel.php';

/**
 * Call the Laravel Backend to broadcast status change + send FCM push
 * to both driver and rider apps in real-time via artisan command.
 */
function notifyAppsViaBackendAPI($rideId, $newStatus) {
    try {
        $artisanPath = 'C:\\xampp\\htdocs\\taxiApp_backend\\backend';
        $cmd = "cd /d \"$artisanPath\" && php artisan ride:notify $rideId $newStatus";
        
        // Run in background so dashboard response is instant
        pclose(popen("start /B $cmd", 'r'));
    } catch (Exception $e) {
        // Silent fail - don't break dashboard flow
        error_log("Dashboard notify failed: " . $e->getMessage());
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    $model = new RideModel();
    
    if ($_POST['action'] === 'delete' && isset($_POST['ride_id'])) {
        try {
            $rideId = (int) $_POST['ride_id'];
            
            // Also delete related ride_requests
            $model->query("DELETE FROM ride_requests WHERE ride_id = ?", [$rideId]);
            
            // Delete the ride
            $model->delete('rides', $rideId);
            
            $_SESSION['success'] = "Ride #$rideId deleted successfully!";
        } catch (Exception $e) {
            $_SESSION['error'] = "Failed to delete ride: " . $e->getMessage();
        }
    }

    if ($_POST['action'] === 'update_price' && isset($_POST['ride_id'], $_POST['ride_price'])) {
        try {
            $rideId = (int) $_POST['ride_id'];
            $price = (float) $_POST['ride_price'];
            $model->updatePrice($rideId, $price);
            $_SESSION['success'] = "Ride #$rideId price updated to " . number_format($price) . " SYP";
        } catch (Exception $e) {
            $_SESSION['error'] = "Failed to update price: " . $e->getMessage();
        }
    }

    if ($_POST['action'] === 'complete_simple' && isset($_POST['ride_id'])) {
        try {
            $rideId = (int) $_POST['ride_id'];
            $model->updateStatus($rideId, 'completed');
            notifyAppsViaBackendAPI($rideId, 'completed');
            $_SESSION['success'] = "Ride #$rideId marked as completed (Status only).";
        } catch (Exception $e) {
            $_SESSION['error'] = "Failed to complete ride: " . $e->getMessage();
        }
    }

    if ($_POST['action'] === 'complete_financial' && isset($_POST['ride_id'])) {
        try {
            $rideId = (int) $_POST['ride_id'];
            $ride = $model->getRideDetails($rideId);
            
            if (!$ride || $ride['status'] === 'completed') {
                throw new Exception("Ride is already completed or not found.");
            }
            if (!$ride['driver_id']) {
                throw new Exception("No driver assigned to this ride.");
            }

            $db = getDB();
            $db->beginTransaction();

            // 1. Get Commission Rate
            $settings = $db->query("SELECT commission_rate FROM settings LIMIT 1")->fetch();
            $rate = $settings['commission_rate'] ?? 15.0;
            $commission = round(($ride['ride_price'] * $rate) / 100);

            // 2. Update Ride Status
            $model->updateStatus($rideId, 'completed');

            // 3. Update Driver Wallet
            $driverId = $ride['driver_id'];
            $driver = $db->query("SELECT wallet_balance FROM users WHERE id = $driverId")->fetch();
            $oldBalance = $driver['wallet_balance'] ?? 0;
            $newBalance = $oldBalance - $commission;
            
            $db->prepare("UPDATE users SET wallet_balance = ? WHERE id = ?")->execute([$newBalance, $driverId]);

            // 4. Log Transaction
            $db->prepare("INSERT INTO wallet_transactions (user_id, transaction_type, amount, balance_before, balance_after, description, ride_id, created_at, updated_at) 
                         VALUES (?, 'debit', ?, ?, ?, ?, ?, NOW(), NOW())")
               ->execute([
                   $driverId, 
                   -$commission, 
                   $oldBalance, 
                   $newBalance, 
                   "Admin force-complete: Commission ($rate%) for Ride #$rideId", 
                   $rideId
               ]);

            $db->commit();
            notifyAppsViaBackendAPI($rideId, 'completed');
            $_SESSION['success'] = "Ride #$rideId completed. Commission " . number_format($commission) . " SYP deducted from driver.";
        } catch (Exception $e) {
            if (isset($db) && $db->inTransaction()) $db->rollBack();
            $_SESSION['error'] = "Financial completion failed: " . $e->getMessage();
        }
    }
    
    if ($_POST['action'] === 'update' && isset($_POST['ride_id'])) {
        try {
            $rideId = (int) $_POST['ride_id'];
            $newStatus = $_POST['status'] ?? 'pending';
            $data = [
                'ride_code' => $_POST['ride_code'] ?? '',
                'pickup_address' => $_POST['pickup_address'] ?? '',
                'dropoff_address' => $_POST['dropoff_address'] ?? '',
                'ride_price' => (float) ($_POST['ride_price'] ?? 0),
                'status' => $newStatus,
                'car_type' => $_POST['car_type'] ?? 'standard',
                'payment_method' => $_POST['payment_method'] ?? 'cash',
                'updated_at' => date('Y-m-d H:i:s')
            ];

            if ($newStatus === 'completed') {
                $data['completed_at'] = date('Y-m-d H:i:s');
            }
            if ($newStatus === 'cancelled') {
                $data['cancelled_at'] = date('Y-m-d H:i:s');
            }

            $model->updateRide($rideId, $data);
            
            // Notify apps for important status changes
            if (in_array($newStatus, ['completed', 'cancelled', 'started', 'arrived'])) {
                notifyAppsViaBackendAPI($rideId, $newStatus);
            }
            
            $_SESSION['success'] = "Ride #$rideId updated successfully!";
        } catch (Exception $e) {
            $_SESSION['error'] = "Failed to update ride: " . $e->getMessage();
        }
    }

    header('Location: ../index.php?p=rides');
    exit;
}
?>
