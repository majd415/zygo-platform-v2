<?php
// C:\xampp\htdocs\dashboardtaxi\models\WalletModel.php

class WalletModel extends Model {
    public function generateCards($count, $balance, $expiry = null, $batchId = null) {
        for ($i = 0; $i < $count; $i++) {
            $code = $this->generateUniqueCode();
            $this->query("INSERT INTO recharge_cards (code, balance, expiry_date, status, batch_id) VALUES (?, ?, ?, 'active', ?)", [$code, $balance, $expiry, $batchId]);
        }
    }

    public function getCardsByBatch($batchId) {
        return $this->query("SELECT * FROM recharge_cards WHERE batch_id = ? ORDER BY id ASC", [$batchId])->fetchAll();
    }

    public function getCardById($id) {
        return $this->query("SELECT * FROM recharge_cards WHERE id = ?", [$id])->fetch();
    }

    private function generateUniqueCode() {
        return strtoupper(substr(md5(uniqid(mt_rand(), true)), 0, 12));
    }

    public function getCards($status = null, $limit = 50) {
        $sql = "SELECT * FROM recharge_cards";
        $params = [];
        if ($status) {
            $sql .= " WHERE status = ?";
            $params[] = $status;
        }
        $sql .= " ORDER BY created_at DESC LIMIT $limit";
        return $this->query($sql, $params)->fetchAll();
    }

    public function getWalletStats() {
        return [
            'total_balance_issued' => $this->query("SELECT SUM(balance) as total FROM recharge_cards WHERE status = 'used'")->fetch()['total'] ?? 0,
            'active_cards_value' => $this->query("SELECT SUM(balance) as total FROM recharge_cards WHERE status = 'active'")->fetch()['total'] ?? 0,
            'total_transactions' => $this->query("SELECT COUNT(*) as count FROM recharge_card_usage")->fetch()['count'],
        ];
    }

    public function deleteCard($id) {
        return $this->query("DELETE FROM recharge_cards WHERE id = ?", [$id]);
    }
}
?>
