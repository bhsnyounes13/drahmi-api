<?php
require_once __DIR__ . '/Controller.php';

return [
    'POST /api/auth/register' => [AuthController::class, 'register'],
    'POST /api/auth/login' => [AuthController::class, 'login'],
    'POST /api/auth/refresh' => [AuthController::class, 'refresh'],
    'GET /api/auth/profile' => [AuthController::class, 'profile']
];