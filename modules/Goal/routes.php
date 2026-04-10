<?php
require_once __DIR__ . '/Controller.php';

return [
    'GET /api/objectifs' => [GoalController::class, 'index'],
    'GET /api/objectifs/{id}' => [GoalController::class, 'show'],
    'POST /api/objectifs' => [GoalController::class, 'store'],
    'PUT /api/objectifs/{id}' => [GoalController::class, 'update'],
    'DELETE /api/objectifs/{id}' => [GoalController::class, 'destroy']
];