<?php
// C:\xampp\htdocs\dashboardtaxi\models\DashboardModel.php

class DashboardModel extends Model {
    public function getStats() {
        return [
            'total_users' => (int)$this->query("SELECT COUNT(*) as count FROM users WHERE role = 'rider'")->fetch()['count'],
            'total_drivers' => (int)$this->query("SELECT COUNT(*) as count FROM users WHERE role = 'driver'")->fetch()['count'],
            'online_drivers' => (int)$this->query("SELECT COUNT(*) as count FROM users WHERE role = 'driver' AND is_online = 1")->fetch()['count'],
            'pending_drivers' => (int)$this->query("SELECT COUNT(*) as count FROM driver_documents WHERE status = 'pending'")->fetch()['count'] ?? 0,
            'active_rides' => (int)$this->query("SELECT COUNT(*) as count FROM rides WHERE status IN ('pending', 'accepted', 'arrived', 'started')")->fetch()['count'],
            'completed_rides' => (int)$this->query("SELECT COUNT(*) as count FROM rides WHERE status = 'completed'")->fetch()['count'],
            'canceled_rides' => (int)$this->query("SELECT COUNT(*) as count FROM rides WHERE status IN ('cancelled', 'cancelled_by_driver')")->fetch()['count'],
            'total_revenue' => (float)($this->query("SELECT SUM(ride_price) as total FROM rides WHERE status = 'completed'")->fetch()['total'] ?? 0),
            'total_generated_cards' => (int)$this->query("SELECT COUNT(*) as count FROM recharge_cards")->fetch()['count'],
            'total_generated_value' => (float)($this->query("SELECT SUM(balance) as total FROM recharge_cards")->fetch()['total'] ?? 0),
            'platform_earnings' => (float)($this->query("SELECT platform_earnings FROM settings LIMIT 1")->fetch()['platform_earnings'] ?? 0),
        ];
    }

    public function getMonthlyRevenue($months = 7) {
        return $this->query("
            SELECT 
                DATE_FORMAT(created_at, '%b') as month,
                SUM(ride_price) as total,
                COUNT(*) as ride_count
            FROM rides 
            WHERE status = 'completed' 
            AND created_at >= DATE_SUB(NOW(), INTERVAL $months MONTH)
            GROUP BY YEAR(created_at), MONTH(created_at)
            ORDER BY YEAR(created_at), MONTH(created_at)
        ")->fetchAll();
    }

    public function getMonthlyRiders($months = 7) {
        return $this->query("
            SELECT 
                DATE_FORMAT(created_at, '%b') as month,
                COUNT(*) as total
            FROM users 
            WHERE role = 'rider'
            AND created_at >= DATE_SUB(NOW(), INTERVAL $months MONTH)
            GROUP BY YEAR(created_at), MONTH(created_at)
            ORDER BY YEAR(created_at), MONTH(created_at)
        ")->fetchAll();
    }

    public function getOnlineDrivers() {
        return $this->query("
            SELECT 
                u.id, u.name, u.last_latitude as lat, u.last_longitude as lng,
                (SELECT status FROM rides WHERE driver_id = u.id AND status IN ('accepted', 'arrived', 'started') LIMIT 1) as ride_status
            FROM users u
            WHERE u.role = 'driver' AND u.is_online = 1
        ")->fetchAll();
    }

    public function getRecentRides($limit = 5) {
        $limit = (int)$limit;
        return $this->query("
            SELECT r.*, u.name as rider_name, d.name as driver_name 
            FROM rides r 
            LEFT JOIN users u ON r.rider_id = u.id 
            LEFT JOIN users d ON r.driver_id = d.id 
            ORDER BY r.created_at DESC 
            LIMIT $limit
        ")->fetchAll();
    }
}
?>
