<?php

class RatingModel extends Model {
    public function getAll() {
        $sql = "SELECT dr.*, 
                       driver.name as driver_name, 
                       rider.name as rider_name
                FROM driver_ratings dr
                LEFT JOIN users driver ON dr.driver_id = driver.id
                LEFT JOIN users rider ON dr.passenger_id = rider.id
                ORDER BY dr.created_at DESC";
        return $this->query($sql)->fetchAll();
    }

    public function deleteRating($id) {
        // Get driver_id before deletion
        $rating = $this->query("SELECT driver_id FROM driver_ratings WHERE id = ?", [$id])->fetch();

        if (!$rating) return false;

        $driverId = $rating['driver_id'];

        // Delete the rating
        $result = $this->query("DELETE FROM driver_ratings WHERE id = ?", [$id]);

        if ($result) {
            $this->recalculateDriverRating($driverId);
        }

        return $result;
    }

    private function recalculateDriverRating($driverId) {
        $stats = $this->query("SELECT COUNT(*) as count, AVG(rating) as average FROM driver_ratings WHERE driver_id = ?", [$driverId])->fetch();

        $count = $stats['count'] ?? 0;
        $avg = $stats['average'] ?? 5.00;

        $this->update('users', [
            'rating' => round($avg, 2),
            'rating_count' => $count
        ], $driverId);
    }
}
