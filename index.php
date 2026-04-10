<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);
ini_set('log_errors', 1);
ini_set('error_log', __DIR__ . '/logs/php_errors.log');

$logDir = __DIR__ . '/logs';
if (!is_dir($logDir)) {
    mkdir($logDir, 0777, true);
}

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS');
header('Access-Control-Allow-Headers: Authorization, Content-Type, X-Requested-With');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit;
}

require_once __DIR__ . '/config/config.php';
require_once __DIR__ . '/config/constants.php';

require_once __DIR__ . '/core/Database.php';
require_once __DIR__ . '/core/Response.php';
require_once __DIR__ . '/core/Request.php';
require_once __DIR__ . '/core/Router.php';

require_once __DIR__ . '/utils/JwtHelper.php';
require_once __DIR__ . '/utils/PasswordHelper.php';
require_once __DIR__ . '/utils/Validator.php';
require_once __DIR__ . '/utils/Logger.php';

require_once __DIR__ . '/middleware/AuthMiddleware.php';
require_once __DIR__ . '/middleware/CorsMiddleware.php';
require_once __DIR__ . '/middleware/ValidationMiddleware.php';

CorsMiddleware::handle();

$router = new Router();

$modules = [
    'Auth',
    'User',
    'Transaction',
    'Category',
    'Revenue',
    'Goal',
    'Saving',
    'Debt',
    'Bill',
    'Dashboard',
    'Simulation',
    'Notification'
];

foreach ($modules as $module) {
    $routesFile = __DIR__ . "/../modules/{$module}/routes.php";
    if (file_exists($routesFile)) {
        $routes = require $routesFile;
        foreach ($routes as $route => $handler) {
            $parts = explode(' ', $route, 2);
            $method = $parts[0];
            $path = $parts[1];
            
            switch ($method) {
                case 'GET':
                    $router->get($path, $handler);
                    break;
                case 'POST':
                    $router->post($path, $handler);
                    break;
                case 'PUT':
                    $router->put($path, $handler);
                    break;
                case 'DELETE':
                    $router->delete($path, $handler);
                    break;
            }
        }
    }
}

$router->setNotFoundHandler(function() {
    $input = file_get_contents('php://input');
    file_put_contents(__DIR__ . '/logs/debug.log', date('Y-m-d H:i:s') . " method:" . $_SERVER['REQUEST_METHOD'] . " uri:" . $_SERVER['REQUEST_URI'] . " input: $input\n", FILE_APPEND);
    Response::notFound('Endpoint not found. Check API documentation for available routes.');
});

// Health check endpoint
$router->get('/api/health', function() {
    Response::json([
        'success' => true,
        'message' => 'API is running',
        'timestamp' => date('Y-m-d H:i:s'),
        'server' => 'Drahmi API'
    ]);
});

$requestUri = $_GET['path'] ?? $_SERVER['REQUEST_URI'] ?? '/';
$requestUri = strtok($requestUri, '?');
$_SERVER['REQUEST_URI'] = $requestUri;

$router->dispatch();