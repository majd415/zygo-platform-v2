<?php
// C:\xampp\htdocs\dashboardtaxi\models\CouponModel.php

class CouponModel extends Model {
    public function getCoupons() {
        return $this->all('coupons');
    }

    public function createCoupon($data) {
        $percentage = $data['type'] === 'percentage' ? $data['value'] : 0;
        $fixed = $data['type'] === 'fixed' ? $data['value'] : 0;
        
        $sql = "INSERT INTO coupons (code, description, discount_percentage, fixed_discount, starts_at, expiration_date, is_active, created_at, updated_at) 
                VALUES (?, ?, ?, ?, ?, ?, ?, NOW(), NOW())";
        return $this->query($sql, [
            $data['code'],
            $data['description'],
            $percentage,
            $fixed,
            $data['starts_at'],
            $data['expires_at'],
            1 // Default active
        ]);
    }

    public function deleteCoupon($id) {
        return $this->query("DELETE FROM coupons WHERE id = ?", [$id]);
    }
}
?>
