<?php
require_once __DIR__ . '/Controller.php';

return [
    'GET /api/factures' => [BillController::class, 'index'],
    'GET /api/factures/upcoming' => [BillController::class, 'upcoming'],
    'POST /api/factures' => [BillController::class, 'store'],
    'PUT /api/factures/{id}' => [BillController::class, 'update'],
    'DELETE /api/factures/{id}' => [BillController::class, 'destroy']
];