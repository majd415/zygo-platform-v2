<?php
require_once 'Model.php';

class UserModel extends Model {
    public function getUsers($role = null, $search = '', $limit = 10, $offset = 0) {
        $sql = "SELECT * FROM users WHERE 1=1";
        $params = [];

        if ($role) {
            $sql .= " AND role = ?";
            $params[] = $role;
        }

        if ($search) {
            $sql .= " AND (name LIKE ? OR phone LIKE ? OR email LIKE ?)";
            $searchTerm = "%$search%";
            $params[] = $searchTerm;
            $params[] = $searchTerm;
            $params[] = $searchTerm;
        }

        $sql .= " ORDER BY created_at DESC LIMIT $limit OFFSET $offset";
        return $this->query($sql, $params)->fetchAll();
    }

    public function getUserCount($role = null, $search = '') {
        $sql = "SELECT COUNT(*) as count FROM users WHERE 1=1";
        $params = [];

        if ($role) {
            $sql .= " AND role = ?";
            $params[] = $role;
        }

        if ($search) {
            $sql .= " AND (name LIKE ? OR phone LIKE ? OR email LIKE ?)";
            $searchTerm = "%$search%";
            $params[] = $searchTerm;
            $params[] = $searchTerm;
            $params[] = $searchTerm;
        }

        return $this->query($sql, $params)->fetch()['count'];
    }

    public function getUserById($id) {
        return $this->query("SELECT * FROM users WHERE id = ?", [$id])->fetch();
    }

    public function updateUser($id, $data) {
        $fields = "name = ?, email = ?, phone = ?, role = ?, service_category = ?, status = ?, updated_at = NOW()";
        $params = [
            $data['name'],
            $data['email'],
            $data['phone'],
            $data['role'],
            $data['service_category'] ?? 'economy',
            $data['status']
        ];

        if (!empty($data['password'])) {
            $fields .= ", password = ?";
            $params[] = password_hash($data['password'], PASSWORD_DEFAULT);
        }

        $params[] = $id;
        $sql = "UPDATE users SET $fields WHERE id = ?";
        return $this->query($sql, $params);
    }

    public function getUsersForExport($ids = [], $roleFilter = '', $search = '') {
        $sql = "SELECT id, name, email, phone, role, status, created_at FROM users WHERE 1=1";
        $params = [];

        if (!empty($ids)) {
            $placeholders = implode(',', array_fill(0, count($ids), '?'));
            $sql .= " AND id IN ($placeholders)";
            $params = array_merge($params, $ids);
        } else {
            if ($roleFilter) {
                $sql .= " AND role = ?";
                $params[] = $roleFilter;
            }
            if ($search) {
                $sql .= " AND (name LIKE ? OR phone LIKE ? OR email LIKE ?)";
                $searchTerm = "%$search%";
                $params[] = $searchTerm;
                $params[] = $searchTerm;
                $params[] = $searchTerm;
            }
        }

        $sql .= " ORDER BY created_at DESC";
        return $this->query($sql, $params)->fetchAll();
    }

    public function deleteUser($id) {
        // Also clean up related records to prevent FK constraints
        $this->query("DELETE FROM personal_access_tokens WHERE tokenable_id = ? AND tokenable_type LIKE '%User%'", [$id]);
        $this->query("DELETE FROM wallet_transactions WHERE user_id = ?", [$id]);
        $this->query("DELETE FROM driver_documents WHERE user_id = ?", [$id]);
        return $this->query("DELETE FROM users WHERE id = ?", [$id]);
    }
}
?>
