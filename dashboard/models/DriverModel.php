<?php
// C:\xampp\htdocs\dashboardtaxi\models\DriverModel.php

class DriverModel extends Model {
    public function getAllDrivers($status = '', $search = '') {
        $where = ["u.role = 'driver'"];
        $params = [];

        if ($status === 'pending') {
            $where[] = "dd.status = 'pending'";
        } elseif ($status === 'approved') {
            $where[] = "u.status = 'approved'";
        }

        if (!empty($search)) {
            $where[] = "(u.name LIKE ? OR u.phone LIKE ? OR u.email LIKE ?)";
            $params[] = "%$search%";
            $params[] = "%$search%";
            $params[] = "%$search%";
        }

        $whereClause = implode(' AND ', $where);

        return $this->query("
            SELECT u.*, dd.id as doc_id, dd.status as doc_status, 
                   dd.national_id_front, dd.national_id_back, dd.car_photo, 
                   dd.car_photo_front, dd.car_photo_back, dd.driving_license, 
                   dd.license_back, dd.registration_front, dd.registration_back, dd.insurance_photo,
                   dd.car_type, dd.car_model, dd.car_year, dd.car_plate, dd.car_color,
                   (SELECT COUNT(*) FROM rides WHERE driver_id = u.id AND status = 'completed') as total_trips
            FROM users u
            LEFT JOIN driver_documents dd ON u.id = dd.user_id
            WHERE $whereClause
            ORDER BY u.created_at DESC
        ", $params)->fetchAll();
    }

    public function getDriverDetails($userId) {
        $user = $this->query("SELECT * FROM users WHERE id = ?", [$userId])->fetch();
        $docs = $this->query("SELECT * FROM driver_documents WHERE user_id = ?", [$userId])->fetch();
        $stats = $this->getDriverStats($userId);
        
        return [
            'user' => $user,
            'docs' => $docs,
            'stats' => $stats
        ];
    }

    public function getDriverStats($userId) {
        $trips = $this->query("SELECT COUNT(*) as total FROM rides WHERE driver_id = ? AND status = 'completed'", [$userId])->fetch();
        $earnings = $this->query("SELECT SUM(ride_price) as total FROM rides WHERE driver_id = ? AND status = 'completed'", [$userId])->fetch();
        $cancelled = $this->query("SELECT COUNT(*) as total FROM rides WHERE driver_id = ? AND status = 'cancelled_by_driver'", [$userId])->fetch();
        
        $totalAssigned = ($trips['total'] ?? 0) + ($cancelled['total'] ?? 0);
        $reliability = $totalAssigned > 0 ? round((($trips['total'] ?? 0) / $totalAssigned) * 100) : 100;

        return [
            'total_trips' => $trips['total'] ?? 0,
            'total_earnings' => $earnings['total'] ?? 0,
            'reliability' => $reliability . '%'
        ];
    }

    public function updateDocumentStatus($id, $status, $reason = null, $userId = null) {
        if ($userId) {
            $this->query("UPDATE users SET rejection_reason = ? WHERE id = ?", [$reason, $userId]);
            
            // If documents are approved, also activate the user
            if ($status === 'approved') {
                $this->query("UPDATE users SET status = 'approved' WHERE id = ?", [$userId]);
            } elseif ($status === 'rejected') {
                $this->query("UPDATE users SET status = 'pending' WHERE id = ?", [$userId]);
            }
        }
        return $this->query("
            UPDATE driver_documents 
            SET status = ?, rejection_reason = ?, updated_at = NOW() 
            WHERE id = ?
        ", [$status, $reason, $id]);
    }

    public function updateDriverStatus($userId, $status, $reason = null) {
        if ($reason !== null) {
            return $this->query("UPDATE users SET status = ?, rejection_reason = ? WHERE id = ?", [$status, $reason, $userId]);
        }
        return $this->query("UPDATE users SET status = ? WHERE id = ?", [$status, $userId]);
    }

    public function verifyDriver($userId, $isVerified = 1) {
        return $this->query("UPDATE users SET is_verified = ? WHERE id = ?", [$isVerified, $userId]);
    }

    public function deleteDriver($userId) {
        try {
            // 1. Delete documents
            $this->query("DELETE FROM driver_documents WHERE user_id = ?", [$userId]);
            
            // 2. Delete ride requests
            $this->query("DELETE FROM ride_requests WHERE driver_id = ?", [$userId]);
            
            // 3. Dissociate from rides (set driver to NULL to preserve trip history)
            $this->query("UPDATE rides SET driver_id = NULL WHERE driver_id = ?", [$userId]);
            
            // 4. Delete wallet transactions
            $this->query("DELETE FROM wallet_transactions WHERE user_id = ?", [$userId]);
            
            // 5. Delete authentication tokens
            $this->query("DELETE FROM personal_access_tokens WHERE tokenable_id = ? AND tokenable_type LIKE '%User%'", [$userId]);
            
            // 6. Finally delete the user
            return $this->query("DELETE FROM users WHERE id = ?", [$userId]);
        } catch (Exception $e) {
            // Log error or handle as needed
            return false;
        }
    }
}
?>
