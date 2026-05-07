<?php
header('Content-Type: application/json');
require_once 'config.php';

function jsonError($message, $code = 400) {
    http_response_code($code);
    echo json_encode(['success' => false, 'error' => $message]);
    exit;
}

try {
    $input = json_decode(file_get_contents('php://input'), true);
    
    if (!$input) {
        jsonError('Invalid request body');
    }
    
    $stripePaymentId = isset($input['stripe_payment_id']) ? trim($input['stripe_payment_id']) : '';
    $customerName = isset($input['customer_name']) ? trim($input['customer_name']) : '';
    $customerEmail = isset($input['customer_email']) ? trim($input['customer_email']) : '';
    $productId = isset($input['product_id']) ? intval($input['product_id']) : null;
    $quantity = isset($input['quantity']) ? intval($input['quantity']) : 1;
    $amount = isset($input['amount']) ? floatval($input['amount']) : 0;
    $currency = isset($input['currency']) ? strtoupper($input['currency']) : 'USD';
    $status = isset($input['status']) ? trim($input['status']) : 'pending';
    
    if (empty($stripePaymentId) || empty($customerName) || empty($customerEmail) || $amount <= 0) {
        jsonError('Missing required fields');
    }
    
    if (!filter_var($customerEmail, FILTER_VALIDATE_EMAIL)) {
        jsonError('Invalid email address');
    }
    
    $db = getDB();
    
    $stmt = $db->query(
        "INSERT INTO orders (stripe_payment_id, customer_name, customer_email, product_id, quantity, amount, currency, status) 
         VALUES (?, ?, ?, ?, ?, ?, ?, ?)",
        [$stripePaymentId, $customerName, $customerEmail, $productId, $quantity, $amount, $currency, $status]
    );
    
    $orderId = $db->lastInsertId();
    
    echo json_encode([
        'success' => true,
        'order_id' => $orderId,
        'message' => 'Order created successfully'
    ]);
    
} catch (Exception $e) {
    jsonError('Server error: ' . $e->getMessage(), 500);
}