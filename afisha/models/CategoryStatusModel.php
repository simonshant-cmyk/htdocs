<?php
require_once __DIR__ . '/BaseModel.php';

class CategoryModel extends BaseModel {
    public function getAll(): array {
        $stmt = $this->db->query('SELECT * FROM categories ORDER BY name ASC');
        return $stmt->fetchAll();
    }
}

class StatusModel extends BaseModel {
    public function getAll(): array {
        $stmt = $this->db->query('SELECT * FROM statuses ORDER BY status_id ASC');
        return $stmt->fetchAll();
    }
}
