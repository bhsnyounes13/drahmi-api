<?php
require_once __DIR__ . '/Controller.php';

return [
    'GET /api/dashboard' => [DashboardController::class, 'index'],
    'GET /api/dashboard/summary' => [DashboardController::class, 'summary'],
    'GET /api/dashboard/spending-by-category' => [DashboardController::class, 'spendingByCategory'],
    'GET /api/dashboard/monthly-trend' => [DashboardController::class, 'monthlyTrend'],
    'GET /api/dashboard/goals' => [DashboardController::class, 'goals'],
    'GET /api/dashboard/bills' => [DashboardController::class, 'bills']
];