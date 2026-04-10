<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

require_once __DIR__ . '/config/config.php';
require_once __DIR__ . '/core/Database.php';

try {
    $db = Database::getInstance()->getConnection();
    echo "Connected\n";
    
    // Simulate what the API does
    $userId = 1;
    $data = [
        'montant' => 150,
        'type' => 'depense',
        'categorie_id' => 1,
        'description' => 'Test transaction',
        'date' => '2026-04-10'
    ];
    
    // Insert directly
    $stmt = $db->prepare("INSERT INTO transaction (utilisateur_id, montant, type, categorie_id, description, date) VALUES (?, ?, ?, ?, ?, ?)");
    $stmt->execute([
        $userId,
        $data['montant'],
        $data['type'],
        $data['categorie_id'],
        $data['description'],
        $data['date']
    ]);
    
    $id = $db->lastInsertId();
    echo "Created transaction ID: $id\n";
    
    // Test historical logging
    $stmt = $db->prepare("INSERT INTO historique (utilisateur_id, action, entite_type, entite_id, details, date) VALUES (?, 'create', 'transaction', ?, ?, NOW())");
    $stmt->execute([$userId, $id, json_encode($data)]);
    echo "Logged to historique\n";
    
    echo "SUCCESS!";
    
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
    echo "File: " . $e->getFile() . "\n";
    echo "Line: " . $e->getLine() . "\n";
}