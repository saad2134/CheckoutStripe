<?php
header('Content-Type: application/json');
require_once 'config.php';

try {
    initDatabase();
    $db = getDB();
    
    $stmt = $db->query("SELECT * FROM products WHERE active = TRUE ORDER BY id");
    $products = $stmt->fetchAll();
    
    echo json_encode([
        'success' => true,
        'products' => $products
    ]);
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'error' => $e->getMessage()
    ]);
}