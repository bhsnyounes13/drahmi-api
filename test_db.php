<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

require_once __DIR__ . '/config/config.php';
require_once __DIR__ . '/core/Database.php';

try {
    $db = Database::getInstance()->getConnection();
    echo "Database connected successfully!";
    
    // Test query
    $stmt = $db->query("SELECT * FROM utilisateur WHERE email = 'test@drahmi.com'");
    $user = $stmt->fetch();
    
    if ($user) {
        echo "\nUser found: " . $user['email'];
    } else {
        echo "\nNo user found";
    }
} catch (Exception $e) {
    echo "Error: " . $e->getMessage();
}