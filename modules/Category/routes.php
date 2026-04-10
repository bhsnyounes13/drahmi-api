<?php
require_once __DIR__ . '/Controller.php';

return [
    'GET /api/categories' => [CategoryController::class, 'index'],
    'GET /api/categories/{id}' => [CategoryController::class, 'show'],
    'GET /api/categories/type/{type}' => [CategoryController::class, 'byType'],
    'POST /api/categories' => [CategoryController::class, 'store'],
    'PUT /api/categories/{id}' => [CategoryController::class, 'update'],
    'DELETE /api/categories/{id}' => [CategoryController::class, 'destroy']
];