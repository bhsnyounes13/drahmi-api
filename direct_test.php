<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

require_once __DIR__ . '/config/config.php';
require_once __DIR__ . '/core/Database.php';
require_once __DIR__ . '/core/Response.php';
require_once __DIR__ . '/utils/JwtHelper.php';
require_once __DIR__ . '/modules/Transaction/Model.php';
require_once __DIR__ . '/modules/Transaction/Service.php';

echo "=== Direct Service Test ===\n\n";

try {
    // Create service
    $service = new TransactionService();
    
    // Test data
    $userId = 1;
    $data = [
        'montant' => 100,
        'type' => 'depense',
        'categorie_id' => 2,
        'description' => 'Test',
        'date' => '2026-04-10'
    ];
    
    echo "Calling service->create() with userId=$userId\n";
    echo "Data: " . json_encode($data) . "\n\n";
    
    $result = $service->create($data, $userId);
    
    echo "Result: " . json_encode($result) . "\n";
    
    if ($result['success']) {
        echo "\n✓ SUCCESS! Transaction ID: " . $result['data']['id'] . "\n";
    } else {
        echo "\n✗ FAILED: " . $result['message'] . "\n";
    }
    
} catch (Exception $e) {
    echo "✗ EXCEPTION: " . $e->getMessage() . "\n";
    echo "File: " . $e->getFile() . "\n";
    echo "Line: " . $e->getLine() . "\n";
    echo "\nTrace:\n" . $e->getTraceAsString() . "\n";
} catch (Error $e) {
    echo "✗ ERROR: " . $e->getMessage() . "\n";
    echo "File: " . $e->getFile() . "\n";
    echo "Line: " . $e->getLine() . "\n";
    echo "\nTrace:\n" . $e->getTraceAsString() . "\n";
}