<?php
require_once __DIR__ . '/Controller.php';

return [
    'GET /api/revenus' => [RevenueController::class, 'index'],
    'GET /api/revenus/{id}' => [RevenueController::class, 'show'],
    'POST /api/revenus' => [RevenueController::class, 'store'],
    'PUT /api/revenus/{id}' => [RevenueController::class, 'update'],
    'DELETE /api/revenus/{id}' => [RevenueController::class, 'destroy']
];