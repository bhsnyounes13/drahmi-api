<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

require_once __DIR__ . '/config/config.php';
require_once __DIR__ . '/core/Database.php';
require_once __DIR__ . '/utils/JwtHelper.php';

try {
    $db = Database::getInstance()->getConnection();
    echo "DB Connected\n";
    
    $stmt = $db->prepare("SELECT * FROM utilisateur WHERE email = ?");
    $stmt->execute(['test@drahmi.com']);
    $user = $stmt->fetch();
    
    if ($user) {
        echo "User found: " . $user['email'] . "\n";
        
        $token = JwtHelper::encode(['id' => $user['id'], 'email' => $user['email']]);
        echo "Token generated\n";
        echo "SUCCESS\n";
    } else {
        echo "User not found\n";
    }
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
    echo "File: " . $e->getFile() . "\n";
    echo "Line: " . $e->getLine() . "\n";
}