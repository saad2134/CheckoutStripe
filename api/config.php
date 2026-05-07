<?php
// Database Configuration
define('DB_HOST', 'localhost');
define('DB_NAME', 'stripe_checkout');
define('DB_USER', 'root');
define('DB_PASS', '');

// Stripe Configuration
// Using test keys - replace with your own in production
define('STRIPE_SECRET_KEY', 'sk_test_your_stripe_secret_key');
define('STRIPE_PUBLISHABLE_KEY', 'pk_test_your_stripe_publishable_key');
define('STRIPE_WEBHOOK_SECRET', 'whsec_your_webhook_secret');

define('STRIPE_CURRENCY', 'usd');

class Database {
    private $pdo;
    
    public function __construct() {
        $dsn = "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=utf8mb4";
        try {
            $this->pdo = new PDO($dsn, DB_USER, DB_PASS, [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES => false
            ]);
        } catch (PDOException $e) {
            error_log("Database connection failed: " . $e->getMessage());
            throw new Exception("Database connection failed");
        }
    }
    
    public function query($sql, $params = []) {
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($params);
        return $stmt;
    }
    
    public function lastInsertId() {
        return $this->pdo->lastInsertId();
    }
    
    public function beginTransaction() {
        return $this->pdo->beginTransaction();
    }
    
    public function commit() {
        return $this->pdo->commit();
    }
    
    public function rollBack() {
        return $this->pdo->rollBack();
    }
}

function getDB() {
    static $db = null;
    if ($db === null) {
        $db = new Database();
    }
    return $db;
}

function initDatabase() {
    $dsn = "mysql:host=" . DB_HOST . ";charset=utf8mb4";
    $pdo = new PDO($dsn, DB_USER, DB_PASS, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION
    ]);
    
    $pdo->exec("CREATE DATABASE IF NOT EXISTS " . DB_NAME);
    $pdo->exec("USE " . DB_NAME);
    
    $pdo->exec("
        CREATE TABLE IF NOT EXISTS products (
            id INT AUTO_INCREMENT PRIMARY KEY,
            name VARCHAR(255) NOT NULL,
            description TEXT,
            price DECIMAL(10,2) NOT NULL,
            image_url VARCHAR(500),
            active BOOLEAN DEFAULT TRUE,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4
    ");
    
    $pdo->exec("
        CREATE TABLE IF NOT EXISTS orders (
            id INT AUTO_INCREMENT PRIMARY KEY,
            stripe_payment_id VARCHAR(255) UNIQUE,
            customer_name VARCHAR(255) NOT NULL,
            customer_email VARCHAR(255) NOT NULL,
            product_id INT,
            quantity INT DEFAULT 1,
            amount DECIMAL(10,2) NOT NULL,
            currency VARCHAR(3) DEFAULT 'usd',
            status VARCHAR(50) DEFAULT 'pending',
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            FOREIGN KEY (product_id) REFERENCES products(id)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4
    ");
    
    $stmt = $pdo->query("SELECT COUNT(*) FROM products");
    $count = $stmt->fetchColumn();
    
    if ($count == 0) {
        $pdo->exec("
            INSERT INTO products (name, description, price, image_url) VALUES
            ('Premium SaaS Subscription', 'Annual subscription with full access to all features, priority support, and regular updates.', 99.00, 'https://images.unsplash.com/photo-1460925895917-afdab827c52f?w=800'),
            ('Developer License', 'Full developer license with source code access, documentation, and premium support.', 199.00, 'https://images.unsplash.com/photo-1555066931-4365d14bab8c?w=800'),
            ('Enterprise Plan', 'Enterprise solution with custom integrations, dedicated support, and SLA guarantee.', 499.00, 'https://images.unsplash.com/photo-1551434678-e076c223a692?w=800')
        ");
    }
    
    return true;
}