<?php
header('Content-Type: application/json');
echo json_encode([
    'status' => 'ok',
    'test' => 'v3',
    'uri' => $_SERVER['REQUEST_URI'] ?? '/',
    'method' => $_SERVER['REQUEST_METHOD'] ?? 'GET'
]);
});

$requestUri = $_GET['path'] ?? $_SERVER['REQUEST_URI'] ?? '/';
$requestUri = strtok($requestUri, '?');
$_SERVER['REQUEST_URI'] = $requestUri;

$router->dispatch();