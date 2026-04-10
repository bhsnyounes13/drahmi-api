<?php
header('Content-Type: application/json');

$input = file_get_contents('php://input');
$len = strlen($input);

echo json_encode([
    'inputLen' => $len,
    'input' => $input,
    'raw' => bin2hex($input),
    'method' => $_SERVER['REQUEST_METHOD'],
    'uri' => $_SERVER['REQUEST_URI']
]);