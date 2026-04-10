<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

require_once __DIR__ . '/config/config.php';
require_once __DIR__ . '/core/Database.php';

try {
    $db = Database::getInstance()->getConnection();
    
    // Check transaction table structure
    $stmt = $db->query("DESCRIBE transaction");
    $fields = $stmt->fetchAll();
    echo "Transaction fields:\n";
    print_r($fields);
    
    // Try inserting manually
    $stmt = $db->prepare("INSERT INTO transaction (utilisateur_id, montant, type, categorie_id, description, date) VALUES (?, ?, ?, ?, ?, ?)");
    $result = $stmt->execute([1, 150.00, 'depense', 1, 'Test', '2026-04-10']);
    echo "Insert result: " . ($result ? 'OK' : 'FAILED') . "\n";
    echo "Last ID: " . $db->lastInsertId() . "\n";
    
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
    echo "Trace: " . $e->getTraceAsString() . "\n";
}