<?php
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
        $target = $data['target'];
        $userId = $data['user_id'] ?? null;
        $image = $data['image'] ?? null;
        $link = $data['link'] ?? null;

        $sql = "INSERT INTO notifications (title_en, title_ar, message_en, message_ar, type, user_id, image, link, created_at, updated_at) VALUES (?, ?, ?, ?, ?, ?, ?, ?, NOW(), NOW())";
        $result = $this->query($sql, [$title_en, $title_ar, $message_en, $message_ar, $type, $target === 'specific' ? $userId : null, $image, $link]);

        $tokens = [];
        if ($target === 'specific' && !empty($userId)) {
            $row = $this->query("SELECT fcm_token FROM users WHERE id = ? AND fcm_token IS NOT NULL", [$userId])->fetch();
            if ($row && $row['fcm_token']) $tokens[] = $row['fcm_token'];
        } else {
            $condition = "WHERE fcm_token IS NOT NULL AND fcm_token != ''";
            if ($target === 'drivers') $condition = "WHERE role = 'driver' AND fcm_token IS NOT NULL AND fcm_token != ''";
            elseif ($target === 'riders') $condition = "WHERE role = 'rider' AND fcm_token IS NOT NULL AND fcm_token != ''";
            $rows = $this->query("SELECT fcm_token FROM users $condition")->fetchAll();
            foreach ($rows as $row) {
                if (!empty($row['fcm_token'])) $tokens[] = $row['fcm_token'];
            }
        }

        if (!empty($tokens)) {
            $this->sendFcmNotifications($tokens, $title_en, $title_ar, $message_en, $message_ar, $type);
        }
        return $result;
    }

    private function sendFcmNotifications($tokens, $titleEn, $titleAr, $msgEn, $msgAr, $type) {
        $serviceAccountPath = '/var/www/backend/storage/app/firebase/service-account.json';
        if (!file_exists($serviceAccountPath)) return;
        $sa = json_decode(file_get_contents($serviceAccountPath), true);
        $accessToken = $this->getFirebaseAccessToken($sa);
        if (!$accessToken) return;

        $projectId = $sa['project_id'];
        $url = "https://fcm.googleapis.com/v1/projects/{$projectId}/messages:send";

        foreach (array_chunk($tokens, 500) as $chunk) {
            foreach ($chunk as $token) {
                $message = ['message' => ['token' => $token, 'notification' => ['title' => $titleEn, 'body' => $msgEn], 'data' => ['title_en' => $titleEn, 'title_ar' => $titleAr, 'message_en' => $msgEn, 'message_ar' => $msgAr, 'type' => $type]]];
                $ch = curl_init($url);
                curl_setopt_array($ch, [CURLOPT_POST => true, CURLOPT_HTTPHEADER => ['Authorization: Bearer ' . $accessToken, 'Content-Type: application/json'], CURLOPT_POSTFIELDS => json_encode($message), CURLOPT_RETURNTRANSFER => true, CURLOPT_TIMEOUT => 5]);
                curl_exec($ch);
                curl_close($ch);
            }
        }
    }

    private function getFirebaseAccessToken($sa) {
        $now = time();
        $header = base64_encode(json_encode(['alg' => 'RS256', 'typ' => 'JWT']));
        $payload = base64_encode(json_encode(['iss' => $sa['client_email'], 'scope' => 'https://www.googleapis.com/auth/firebase.messaging', 'aud' => 'https://oauth2.googleapis.com/token', 'iat' => $now, 'exp' => $now + 3600]));
        $toSign = str_replace(['+', '/', '='], ['-', '_', ''], $header) . '.' . str_replace(['+', '/', '='], ['-', '_', ''], $payload);
        openssl_sign($toSign, $signature, $sa['private_key'], 'SHA256');
        $jwt = $toSign . '.' . str_replace(['+', '/', '='], ['-', '_', ''], base64_encode($signature));
        $ch = curl_init('https://oauth2.googleapis.com/token');
        curl_setopt_array($ch, [CURLOPT_POST => true, CURLOPT_POSTFIELDS => http_build_query(['grant_type' => 'urn:ietf:params:oauth:grant-type:jwt-bearer', 'assertion' => $jwt]), CURLOPT_RETURNTRANSFER => true, CURLOPT_TIMEOUT => 10]);
        $resp = json_decode(curl_exec($ch), true);
        curl_close($ch);
        return $resp['access_token'] ?? null;
    }
}
?>
