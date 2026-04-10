<?php
require_once __DIR__ . '/Controller.php';

return [
    'GET /api/dettes' => [DebtController::class, 'index'],
    'POST /api/dettes' => [DebtController::class, 'store'],
    'PUT /api/dettes/{id}' => [DebtController::class, 'update'],
    'DELETE /api/dettes/{id}' => [DebtController::class, 'destroy']
];