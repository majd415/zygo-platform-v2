<?php
// C:\xampp\htdocs\dashboardtaxi\models\RideModel.php

class RideModel extends Model {
    public function getRides($status = null, $search = '', $limit = 10, $offset = 0) {
        $sql = "
            SELECT r.*, u.name as rider_name, u.phone as rider_phone, d.name as driver_name, d.phone as driver_phone
            FROM rides r
            LEFT JOIN users u ON r.rider_id = u.id
            LEFT JOIN users d ON r.driver_id = d.id
            WHERE 1=1
        ";
        $params = [];

        if ($status) {
            $sql .= " AND r.status = ?";
            $params[] = $status;
        }

        if ($search) {
            $sql .= " AND (r.id LIKE ? OR r.ride_code LIKE ? OR u.name LIKE ? OR d.name LIKE ?)";
            $searchTerm = "%$search%";
            $params[] = $searchTerm;
            $params[] = $searchTerm;
            $params[] = $searchTerm;
            $params[] = $searchTerm;
        }

        $sql .= " ORDER BY r.created_at DESC LIMIT $limit OFFSET $offset";
        return $this->query($sql, $params)->fetchAll();
    }

    public function getRideCount($status = null, $search = '') {
        $sql = "
            SELECT COUNT(*) as count 
            FROM rides r
            LEFT JOIN users u ON r.rider_id = u.id
            LEFT JOIN users d ON r.driver_id = d.id
            WHERE 1=1
        ";
        $params = [];

        if ($status) {
            $sql .= " AND r.status = ?";
            $params[] = $status;
        }

        if ($search) {
            $sql .= " AND (r.id LIKE ? OR r.ride_code LIKE ? OR u.name LIKE ? OR d.name LIKE ?)";
            $searchTerm = "%$search%";
            $params[] = $searchTerm;
            $params[] = $searchTerm;
            $params[] = $searchTerm;
            $params[] = $searchTerm;
        }

        return $this->query($sql, $params)->fetch()['count'];
    }

    public function getRideDetails($id) {
        $sql = "
            SELECT r.*, u.name as rider_name, u.phone as rider_phone, d.name as driver_name, d.phone as driver_phone
            FROM rides r
            LEFT JOIN users u ON r.rider_id = u.id
            LEFT JOIN users d ON r.driver_id = d.id
            WHERE r.id = ?
        ";
        return $this->query($sql, [$id])->fetch();
    }

    public function getActiveRides() {
        $sql = "
            SELECT r.*, u.name as rider_name, d.name as driver_name, d.last_latitude as driver_lat, d.last_longitude as driver_lng
            FROM rides r
            JOIN users u ON r.rider_id = u.id
            LEFT JOIN users d ON r.driver_id = d.id
            WHERE r.status IN ('accepted', 'arrived', 'started')
        ";
        return $this->query($sql)->fetchAll();
    }

    public function updateStatus($id, $status) {
        $sql = "UPDATE rides SET status = ?, updated_at = NOW()";
        if ($status === 'completed') {
            $sql .= ", completed_at = NOW()";
        }
        $sql .= " WHERE id = ?";
        return $this->query($sql, [$status, $id]);
    }

    public function updatePrice($id, $price) {
        $sql = "UPDATE rides SET ride_price = ?, updated_at = NOW() WHERE id = ?";
        return $this->query($sql, [$price, $id]);
    }

    public function updateRide($id, $data) {
        return $this->update('rides', $data, $id);
    }
}
?>
