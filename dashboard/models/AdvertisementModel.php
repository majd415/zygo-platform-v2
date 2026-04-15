<?php
require_once 'Model.php';

class AdvertisementModel extends Model {
    protected $table = 'advertisements';
    
    public function getAll() {
        $stmt = $this->db->query("SELECT * FROM {$this->table} ORDER BY sort_order ASC, created_at DESC");
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    
    public function insert($data) {
        $stmt = $this->db->prepare("
            INSERT INTO {$this->table} (image_url, title_en, title_ar, description_en, description_ar, button_text_en, button_text_ar, click_action, is_active, sort_order, created_at, updated_at) 
            VALUES (:image_url, :title_en, :title_ar, :description_en, :description_ar, :button_text_en, :button_text_ar, :click_action, :is_active, :sort_order, NOW(), NOW())
        ");
        return $stmt->execute([
            ':image_url' => $data['image_url'],
            ':title_en' => $data['title_en'],
            ':title_ar' => $data['title_ar'],
            ':description_en' => $data['description_en'] ?? null,
            ':description_ar' => $data['description_ar'] ?? null,
            ':button_text_en' => $data['button_text_en'] ?? 'Explore',
            ':button_text_ar' => $data['button_text_ar'] ?? 'استكشاف',
            ':click_action' => $data['click_action'] ?? null,
            ':is_active' => $data['is_active'] ?? 1,
            ':sort_order' => $data['sort_order'] ?? 0
        ]);
    }
    
    public function updateAdvertisement($id, $data) {
        $fields = [];
        $params = [];
        foreach ($data as $key => $value) {
            $fields[] = "$key = :$key";
            $params[":$key"] = $value;
        }
        $params[':id'] = $id;
        
        $sql = "UPDATE {$this->table} SET " . implode(', ', $fields) . ", updated_at = NOW() WHERE id = :id";
        $stmt = $this->db->prepare($sql);
        return $stmt->execute($params);
    }

    public function deleteAdvertisement($id) {
        $stmt = $this->db->prepare("DELETE FROM {$this->table} WHERE id = :id");
        return $stmt->execute([':id' => $id]);
    }

    public function toggleStatus($id) {
        $stmt = $this->db->prepare("UPDATE {$this->table} SET is_active = NOT is_active, updated_at = NOW() WHERE id = :id");
        return $stmt->execute([':id' => $id]);
    }
}
?>
