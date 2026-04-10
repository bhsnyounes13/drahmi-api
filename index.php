<?php
header('Content-Type: application/json');
header('Cache-Control: no-store');
$uri = $_GET['route'] ?? $_SERVER['REQUEST_URI'] ?? '/';
$uri = strtok($uri, '?');
$method = $_SERVER['REQUEST_METHOD'] ?? 'GET';

$routes = [
    'GET /' => ['status' => 'ok', 'message' => 'Drahmi API'],
    'POST /api/auth/login' => ['status' => 'ok', 'login' => 'endpoint works'],
    'POST /api/auth/register' => ['status' => 'ok', 'register' => 'endpoint works'],
    'GET /api/health' => ['status' => 'ok', 'health' => 'check'],
];
$key = "$method $uri";
if (isset($routes[$key])) {
    echo json_encode($routes[$key]);
} else {
    http_response_code(404);
    echo json_encode(['error' => 'not found', 'key' => $key]);
}