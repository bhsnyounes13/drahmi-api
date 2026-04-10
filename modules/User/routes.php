<?php
require_once __DIR__ . '/Controller.php';

return [
    'GET /api/user/profile' => [UserController::class, 'profile'],
    'PUT /api/user/profile' => [UserController::class, 'update'],
    'PUT /api/user/avatar' => [UserController::class, 'updateAvatar']
];