<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit;
}

require_once 'config.php';

function jsonError($message, $code = 400) {
    http_response_code($code);
    echo json_encode(['success' => false, 'error' => $message]);
    exit;
}

if (!function_exists('curl_init')) {
    jsonError('cURL extension is not enabled. Add -d extension=php_curl.dll to your PHP command');
}

try {
    $input = json_decode(file_get_contents('php://input'), true);
    
    if (!$input) {
        jsonError('Invalid request body');
    }
    
    $amount = isset($input['amount']) ? floatval($input['amount']) : 0;
    $currency = isset($input['currency']) ? strtolower($input['currency']) : STRIPE_CURRENCY;
    $productId = isset($input['productId']) ? intval($input['productId']) : null;
    $productName = isset($input['productName']) ? trim($input['productName']) : '';
    $quantity = isset($input['quantity']) ? intval($input['quantity']) : 1;
    $customerName = isset($input['customer_name']) ? trim($input['customer_name']) : '';
    $customerCountry = isset($input['customer_country']) ? trim($input['customer_country']) : 'US';
    
    if ($amount <= 0) {
        jsonError('Invalid amount');
    }
    
    $amountCents = intval($amount * 100);
    
    $stripeParams = [
        'amount' => $amountCents,
        'currency' => $currency,
        'automatic_payment_methods[enabled]' => 'true',
        'description' => $productName ? "Purchase: $productName" : 'Online purchase',
        'shipping[name]' => $customerName ?: 'Customer',
        'shipping[address][line1]' => 'Address',
        'shipping[address][city]' => 'City',
        'shipping[address][country]' => $customerCountry,
        'metadata[product_id]' => $productId,
        'metadata[quantity]' => $quantity
    ];
    
    $ch = curl_init();
    curl_setopt_array($ch, [
        CURLOPT_URL => "https://api.stripe.com/v1/payment_intents",
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_USERPWD => STRIPE_SECRET_KEY . ':',
        CURLOPT_HTTPHEADER => [
            'Content-Type: application/x-www-form-urlencoded'
        ],
        CURLOPT_POST => true,
        CURLOPT_POSTFIELDS => http_build_query($stripeParams)
    ]);
    
    $caCert = __DIR__ . '/../cacert.pem';
    if (file_exists($caCert)) {
        curl_setopt($ch, CURLOPT_CAINFO, $caCert);
    } else {
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    }
    
    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $error = curl_error($ch);
    curl_close($ch);
    
    if ($error) {
        throw new Exception("cURL error: $error");
    }
    
    $data = json_decode($response, true);
    
    if ($httpCode >= 400) {
        $errorMsg = isset($data['error']['message']) ? $data['error']['message'] : 'Unknown Stripe error';
        throw new Exception($errorMsg);
    }
    
    echo json_encode([
        'success' => true,
        'clientSecret' => $data['client_secret'],
        'paymentIntentId' => $data['id']
    ]);
    
} catch (Exception $e) {
    jsonError('Payment error: ' . $e->getMessage());
}