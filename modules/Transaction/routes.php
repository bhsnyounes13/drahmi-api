<?php
require_once __DIR__ . '/Controller.php';

return [
    'GET /api/transactions' => [TransactionController::class, 'index'],
    'GET /api/transactions/by-date' => [TransactionController::class, 'byDate'],
    'GET /api/transactions/summary' => [TransactionController::class, 'summary'],
    'GET /api/transactions/by-category' => [TransactionController::class, 'byCategory'],
    'GET /api/transactions/{id}' => [TransactionController::class, 'show'],
    'POST /api/transactions' => [TransactionController::class, 'store'],
    'PUT /api/transactions/{id}' => [TransactionController::class, 'update'],
    'DELETE /api/transactions/{id}' => [TransactionController::class, 'destroy']
];