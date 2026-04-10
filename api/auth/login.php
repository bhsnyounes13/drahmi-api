<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

$logFile = __DIR__ . '/logs/api.log';
$logDir = dirname($logFile);
if (!is_dir($logDir)) mkdir($logDir, 0777, true);

function writeLog($msg) {
    global $logFile;
    file_put_contents($logFile, date('Y-m-d H:i:s') . " - $msg\n", FILE_APPEND);
}

writeLog("Request: " . $_SERVER['REQUEST_URI']);

require_once __DIR__ . '/config/config.php';

try {
    require_once __DIR__ . '/core/Database.php';
    $db = Database::getInstance()->getConnection();
    
    require_once __DIR__ . '/utils/JwtHelper.php';
    require_once __DIR__ . '/core/Response.php';
    require_once __DIR__ . '/modules/Auth/Controller.php';
    
    writeLog("Classes loaded");
    
    $input = file_get_contents('php://input');
    $data = json_decode($input, true);
    
    writeLog("Input: " . $input);
    
    if (!isset($data['email']) || !isset($data['password'])) {
        writeLog("Missing fields");
        echo json_encode(['success' => false, 'message' => 'Email and password required']);
        exit;
    }
    
    require_once __DIR__ . '/modules/Auth/Service.php';
    $service = new AuthService();
    
    $result = $service->login($data['email'], $data['password']);
    
    writeLog("Result: " . json_encode($result));
    
    if ($result['success']) {
        echo json_encode([
            'success' => true,
            'message' => 'Login successful',
            'data' => $result['data']
        ]);
    } else {
        http_response_code(401);
        echo json_encode([
            'success' => false,
            'message' => $result['message']
        ]);
    }
    
} catch (Exception $e) {
    writeLog("Error: " . $e->getMessage() . " in " . $e->getFile() . " line " . $e->getLine());
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Server error']);
}