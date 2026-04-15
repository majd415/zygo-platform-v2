<?php
// C:\xampp\htdocs\dashboardtaxi\models\Model.php

class Model {
    protected $db;

    public function __construct() {
        $this->db = getDB();
    }

    public function query($sql, $params = []) {
        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        return $stmt;
    }

    public function all($table) {
        return $this->query("SELECT * FROM `$table`")->fetchAll();
    }

    public function find($table, $id) {
        return $this->query("SELECT * FROM `$table` WHERE id = ?", [$id])->fetch();
    }

    public function update($table, $data, $id) {
        $fields = [];
        $params = [];
        foreach ($data as $key => $value) {
            $fields[] = "`$key` = ?";
            $params[] = $value;
        }
        $params[] = $id;
        $sql = "UPDATE `$table` SET " . implode(', ', $fields) . " WHERE id = ?";
        return $this->query($sql, $params);
    }

    public function delete($table, $id) {
        return $this->query("DELETE FROM `$table` WHERE id = ?", [$id]);
    }
}
?>
