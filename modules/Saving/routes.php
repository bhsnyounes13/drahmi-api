<?php
require_once __DIR__ . '/Controller.php';

return [
    'GET /api/epargne' => [SavingController::class, 'index'],
    'POST /api/epargne' => [SavingController::class, 'store'],
    'PUT /api/epargne/{id}' => [SavingController::class, 'update'],
    'DELETE /api/epargne/{id}' => [SavingController::class, 'destroy']
];