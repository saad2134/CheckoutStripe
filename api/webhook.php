<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, Stripe-Signature');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit;
}

require_once 'config.php';

$payload = file_get_contents('php://input');
$sigHeader = isset($_SERVER['HTTP_STRIPE_SIGNATURE']) ? $_SERVER['HTTP_STRIPE_SIGNATURE'] : '';

$event = json_decode($payload, true);

if (!$event || !isset($event['type'])) {
    http_response_code(400);
    exit;
}

try {
    $db = getDB();
    
    switch ($event['type']) {
        case 'payment_intent.succeeded':
            $paymentIntent = $event['data']['object'];
            $stripeId = $paymentIntent['id'];
            $amount = $paymentIntent['amount'] / 100;
            $currency = strtoupper($paymentIntent['currency']);
            $metadata = $paymentIntent['metadata'];
            
            $stmt = $db->query(
                "UPDATE orders SET status = 'succeeded' WHERE stripe_payment_id = ?",
                [$stripeId]
            );
            break;
            
        case 'payment_intent.payment_failed':
            $paymentIntent = $event['data']['object'];
            $stripeId = $paymentIntent['id'];
            
            $stmt = $db->query(
                "UPDATE orders SET status = 'failed' WHERE stripe_payment_id = ?",
                [$stripeId]
            );
            break;
            
        default:
            http_response_code(200);
            echo json_encode(['received' => true]);
            exit;
    }
    
    http_response_code(200);
    echo json_encode(['received' => true]);
    
} catch (Exception $e) {
    error_log("Webhook error: " . $e->getMessage());
    http_response_code(500);
    echo json_encode(['error' => 'Webhook processing failed']);
}