<?php
// Скопируй этот файл в Database.php и впиши свои данные:
//   cp afisha/config/Database.example.php afisha/config/Database.php

class Database {
    private static $instance = null;
    private $pdo;

    private string $host     = '127.0.0.1';
    private string $port     = '8889';       // MAMP: 8889, XAMPP: 3306
    private string $db       = 'afisha';
    private string $user     = 'root';
    private string $password = '';           // вставь свой пароль
    private string $charset  = 'utf8mb4';

    private function __construct() {
        $dsn = "mysql:host={$this->host};port={$this->port};dbname={$this->db};charset={$this->charset}";
        $options = [
            PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES   => false,
        ];
        $this->pdo = new PDO($dsn, $this->user, $this->password, $options);
    }

    public static function getInstance(): self {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    public function getConnection(): PDO {
        return $this->pdo;
    }
}
