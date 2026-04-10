<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

require_once __DIR__ . '/config/config.php';
require_once __DIR__ . '/core/Database.php';
require_once __DIR__ . '/core/Response.php';
require_once __DIR__ . '/utils/JwtHelper.php';
require_once __DIR__ . '/middleware/AuthMiddleware.php';

echo "Testing Transaction Controller flow:\n\n";

try {
    // 1. Get user from token (like AuthMiddleware::verify() does)
    $headers = getallheaders();
    $authHeader = $headers['Authorization'] ?? null;
    
    if (!$authHeader) {
        echo "No auth header\n";
        
        // Generate token for testing
        $token = JwtHelper::encode(['id' => 1, 'email' => 'test@drahmi.com']);
        echo "Generated test token\n";
        
        // Decode
        $user = JwtHelper::decode($token);
        echo "User ID from token: " . $user->id . "\n";
        
        // Create transaction data
        $data = json_decode('{"montant":250,"type":"depense","categorie_id":1,"description":"Cafe","date":"2026-04-10"}', true);
        
        echo "Data to insert:\n";
        print_r($data);
        
        // Try service
        require_once __DIR__ . '/modules/Transaction/Service.php';
        $service = new TransactionService();
        
        echo "\nCalling service->create()...\n";
        $result = $service->create($data, $user->id);
        
        echo "Result:\n";
        print_r($result);
        
    }
    
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
    echo "File: " . $e->getFile() . "\n";
    echo "Line: " . $e->getLine() . "\n";
    echo "Trace:\n" . $e->getTraceAsString() . "\n";
}