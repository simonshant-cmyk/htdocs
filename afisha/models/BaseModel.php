<?php
require_once __DIR__ . '/../config/Database.php';

abstract class BaseModel {
    protected PDO $db;

    public function __construct() {
        $this->db = Database::getInstance()->getConnection();
    }
}
