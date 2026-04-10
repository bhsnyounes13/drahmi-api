<?php
header('Content-Type: application/json');

$uri = $_SERVER['REQUEST_URI'] ?? '/';
$uri = strtok($uri, '?');
$method = $_SERVER['REQUEST_METHOD'] ?? 'GET';
$key = "$method $uri";

$routes = [
    'POST /api/auth/login' => 'login',
    'POST /api/auth/register' => 'register',
    'GET /api/category' => 'getCategories',
    'POST /api/category' => 'addCategory',
    'GET /api/transaction' => 'getTransactions',
    'POST /api/transaction' => 'addTransaction',
    'GET /api/dashboard' => 'getDashboard',
    'GET /api/debt' => 'getDebts',
    'POST /api/debt' => 'addDebt',
    'GET /api/bill' => 'getBills',
    'POST /api/bill' => 'addBill',
];

if (isset($routes[$key])) {
    $action = $routes[$key];
    $input = json_decode(file_get_contents('php://input'), true) ?? [];
    
    switch ($action) {
        case 'login':
            $email = $input['email'] ?? '';
            $password = $input['password'] ?? '';
            if ($email === 'demo@test.com' && $password === 'test123') {
                echo json_encode(['success' => true, 'token' => 'demo_token', 'user' => ['id' => 1, 'email' => $email]]);
            } else {
                http_response_code(401);
                echo json_encode(['success' => false, 'message' => 'Invalid credentials']);
            }
            break;
        case 'register':
            echo json_encode(['success' => true, 'message' => 'User registered']);
            break;
        case 'getCategories':
            echo json_encode(['success' => true, 'categories' => [['id' => 1, 'name' => 'Food'], ['id' => 2, 'name' => 'Transport'], ['id' => 3, 'name' => 'Shopping']]]);
            break;
        case 'addCategory':
            echo json_encode(['success' => true, 'message' => 'Category added', 'id' => 4]);
            break;
        case 'getTransactions':
            echo json_encode(['success' => true, 'transactions' => []]);
            break;
        case 'addTransaction':
            echo json_encode(['success' => true, 'message' => 'Transaction added']);
            break;
        case 'getDashboard':
            echo json_encode(['success' => true, 'balance' => 5000, 'income' => 10000, 'expense' => 5000]);
            break;
        case 'getDebts':
            echo json_encode(['success' => true, 'debts' => []]);
            break;
        case 'addDebt':
            echo json_encode(['success' => true, 'message' => 'Debt added']);
            break;
        case 'getBills':
            echo json_encode(['success' => true, 'bills' => []]);
            break;
        case 'addBill':
            echo json_encode(['success' => true, 'message' => 'Bill added']);
            break;
        default:
            echo json_encode(['success' => false, 'message' => 'Action not found']);
    }
} else {
    http_response_code(404);
    echo json_encode(['success' => false, 'message' => 'Endpoint not found', 'key' => $key]);
}