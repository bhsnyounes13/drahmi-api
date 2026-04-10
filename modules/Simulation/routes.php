<?php
require_once __DIR__ . '/Controller.php';

return [
    'POST /api/simulation/calculate' => [SimulationController::class, 'calculate'],
    'GET /api/simulation/history' => [SimulationController::class, 'history']
];