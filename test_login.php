<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

$requestUri = $_SERVER['REQUEST_URI'];
$path = parse_url($requestUri, PHP_URL_PATH);

$logFile = __DIR__ . '/logs/api.log';
if (!is_dir(dirname($logFile))) mkdir(dirname($logFile), 0777, true);
file_put_contents($logFile, date('Y-m-d H:i:s') . " - $path\n", FILE_APPEND);

require_once __DIR__ . '/config/config.php';
require_once __DIR__ . '/core/Database.php';
require_once __DIR__ . '/utils/JwtHelper.php';
require_once __DIR__ . '/core/Response.php';

try {
    $db = Database::getInstance()->getConnection();
    
    if ($path === '/api/auth/login') {
        $data = json_decode(file_get_contents('php://input'), true);
        
        if (!isset($data['email']) || !isset($data['password'])) {
            Response::error('Email and password required');
        }
        
        $stmt = $db->prepare("SELECT * FROM utilisateur WHERE email = ?");
        $stmt->execute([$data['email']]);
        $user = $stmt->fetch();
        
        if (!$user || !password_verify($data['password'], $user['mot_de_passe'])) {
            Response::error('Invalid credentials', 401);
        }
        
        $token = JwtHelper::encode(['id' => $user['id'], 'email' => $user['email']]);
        
        Response::success('Login successful', [
            'token' => $token,
            'user' => [
                'id' => $user['id'],
                'email' => $user['email'],
                'nom' => $user['nom'],
                'prenom' => $user['prenom']
            ]
        ]);
    }
    
    Response::notFound('Endpoint not found');
    
} catch (Exception $e) {
    file_put_contents($logFile, date('Y-m-d H:i:s') . " - Error: " . $e->getMessage() . "\n", FILE_APPEND);
    Response::error('Server error: ' . $e->getMessage(), 500);
}