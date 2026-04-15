<?php
// C:\xampp\htdocs\dashboardtaxi\models\NotificationModel.php

class NotificationModel extends Model {
    public function getNotifications($limit = 4, $offset = 0) {
        return $this->query("SELECT * FROM notifications ORDER BY created_at DESC LIMIT $limit OFFSET $offset")->fetchAll();
    }

    public function getTotalCount() {
        return $this->query("SELECT COUNT(*) as total FROM notifications")->fetch()['total'];
    }

    public function deleteNotification($id) {
        return $this->query("DELETE FROM notifications WHERE id = ?", [$id]);
    }

    public function deleteMultipleNotifications($ids) {
        if (empty($ids)) return false;
        $placeholders = str_repeat('?,', count($ids) - 1) . '?';
        return $this->query("DELETE FROM notifications WHERE id IN ($placeholders)", $ids);
    }

    public function broadcast($data) {
        $title_en = $data['title_en'];
        $title_ar = $data['title_ar'];
        $message_en = $data['message_en'];
        $message_ar = $data['message_ar'];
        $type = $data['type'] ?? 'important';
        $target = $data['target']; // all, drivers, riders, specific
        $userId = $data['user_id'] ?? null;
        $image = $data['image'] ?? null;
        $link = $data['link'] ?? null;

        // Store in DB
        $sql = "INSERT INTO notifications (title_en, title_ar, message_en, message_ar, type, user_id, image, link, created_at, updated_at) VALUES (?, ?, ?, ?, ?, ?, ?, ?, NOW(), NOW())";
        $result = $this->query($sql, [$title_en, $title_ar, $message_en, $message_ar, $type, $target === 'specific' ? $userId : null, $image, $link]);
        
        // Execute background Artisan worker
        $artisanPath = 'C:/xampp/htdocs/taxiApp_backend/backend/artisan';
        $cmdTitleEn = escapeshellarg($title_en);
        $cmdTitleAr = escapeshellarg($title_ar);
        $cmdMsgEn = escapeshellarg($message_en);
        $cmdMsgAr = escapeshellarg($message_ar);
        $cmdType = escapeshellarg($type);
        $cmdTarget = escapeshellarg($target);

        $cmd = "php $artisanPath broadcast:push --target=$cmdTarget --title_en=$cmdTitleEn --title_ar=$cmdTitleAr --message_en=$cmdMsgEn --message_ar=$cmdMsgAr --type=$cmdType";
        
        if ($target === 'specific' && !empty($userId)) {
            $cmdUserId = escapeshellarg($userId);
            $cmd .= " --user_id=$cmdUserId";
        }

        // Dispatch background shell exec gracefully on Windows Host
        if (strtoupper(substr(PHP_OS, 0, 3)) === 'WIN') {
            pclose(popen("start /B " . $cmd, "r")); 
        } else {
            exec($cmd . " > /dev/null 2>&1 &");  
        }

        return $result;
    }
}
?>
