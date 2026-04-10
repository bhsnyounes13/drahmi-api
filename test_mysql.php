<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

echo "Testing MySQL connections...\n\n";

$ports = [3306, 3307, 3308, 33060];

foreach ($ports as $port) {
    echo "Testing port $port... ";
    try {
        $dsn = "mysql:host=127.0.0.1;port=$port;dbname=drahmi_db;charset=utf8mb4";
        $pdo = new PDO($dsn, 'root', '', [
            PDO::ATTR_TIMEOUT => 2,
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION
        ]);
        echo "✓ SUCCESS!\n";
        
        // Test query
        $stmt = $pdo->query("SELECT COUNT(*) as cnt FROM utilisateur");
        $result = $stmt->fetch();
        echo "Users in database: " . $result['cnt'] . "\n";
        exit;
    } catch (Exception $e) {
        echo "FAILED - " . $e->getMessage() . "\n";
    }
}

echo "\nNo MySQL found on tested ports.";