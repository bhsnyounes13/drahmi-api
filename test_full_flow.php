<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

require_once __DIR__ . '/config/config.php';
require_once __DIR__ . '/core/Database.php';

try {
    $db = Database::getInstance()->getConnection();
    
    // Get a valid JWT token
    require_once __DIR__ . '/utils/JwtHelper.php';
    $token = JwtHelper::encode(['id' => 1, 'email' => 'test@drahmi.com']);
    echo "Token generated: " . substr($token, 0, 50) . "...\n";
    
    // Decode token to get user
    $decoded = JwtHelper::decode($token);
    echo "Decoded user ID: " . $decoded->id . "\n";
    
    // Create transaction like the service does
    $data = [
        'montant' => 250,
        'type' => 'depense',
        'categorie_id' => 1,
        'description' => 'Cafe',
        'date' => date('Y-m-d')
    ];
    
    // Insert transaction
    $stmt = $db->prepare("INSERT INTO transaction (utilisateur_id, montant, type, categorie_id, description, date) VALUES (?, ?, ?, ?, ?, ?)");
    $stmt->execute([
        $decoded->id,
        $data['montant'],
        $data['type'],
        $data['categorie_id'],
        $data['description'],
        $data['date']
    ]);
    $transactionId = $db->lastInsertId();
    echo "Transaction created: $transactionId\n";
    
    // Log to historique
    $stmt2 = $db->prepare("INSERT INTO historique (utilisateur_id, action, entite_type, entite_id, details, date) VALUES (?, 'create', 'transaction', ?, ?, NOW())");
    $stmt2->execute([$decoded->id, $transactionId, json_encode($data)]);
    echo "Historique logged\n";
    
    echo "\nSUCCESS - Transaction created via API simulation!";
    
} catch (Exception $e) {
    echo "ERROR: " . $e->getMessage();
}