<?php
// AJAX endpoint: Get driver's latest location for a specific ride
// Used by live_map.php polling fallback
header('Content-Type: application/json');
require_once __DIR__ . '/../config.php';

$rideId = intval($_GET['ride_id'] ?? 0);
if (!$rideId) {
    echo json_encode(['error' => 'Missing ride_id']);
    exit;
}

try {
    $db = getDB();
    
    // Get the driver's latest location from the users table via the ride
    $stmt = $db->prepare("
        SELECT u.last_latitude as latitude, u.last_longitude as longitude, u.last_bearing as bearing
        FROM rides r
        JOIN users u ON u.id = r.driver_id
        WHERE r.id = :ride_id AND r.driver_id IS NOT NULL
        LIMIT 1
    ");
    $stmt->execute([':ride_id' => $rideId]);
    $result = $stmt->fetch();
    
    if ($result && $result['latitude'] && $result['longitude']) {
        echo json_encode([
            'latitude' => floatval($result['latitude']),
            'longitude' => floatval($result['longitude']),
            'bearing' => floatval($result['bearing'] ?? 0),
        ]);
    } else {
        echo json_encode(['error' => 'No location data']);
    }
} catch (Exception $e) {
    echo json_encode(['error' => 'Database error']);
}
