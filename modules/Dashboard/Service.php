<?php
require_once __DIR__ . '/Model.php';

class DashboardService {
    private $model;

    public function __construct() {
        $this->model = new DashboardModel();
    }

    public function getSummary($userId, $startDate = null, $endDate = null) {
        $summary = $this->model->getSummary($userId, $startDate, $endDate);
        return ['success' => true, 'data' => $summary];
    }

    public function getSpendingByCategory($userId, $startDate = null, $endDate = null) {
        $data = $this->model->getSpendingByCategory($userId, $startDate, $endDate);
        return ['success' => true, 'data' => $data];
    }

    public function getMonthlyTrend($userId, $months = 6) {
        $data = $this->model->getMonthlyTrend($userId, $months);
        return ['success' => true, 'data' => $data];
    }

    public function getGoals($userId) {
        $goals = $this->model->getActiveGoals($userId);
        return ['success' => true, 'data' => $goals];
    }

    public function getBills($userId) {
        $bills = $this->model->getUpcomingBills($userId);
        return ['success' => true, 'data' => $bills];
    }

    public function getFullDashboard($userId) {
        return [
            'success' => true,
            'data' => [
                'summary' => $this->model->getSummary($userId),
                'spending_by_category' => $this->model->getSpendingByCategory($userId),
                'monthly_trend' => $this->model->getMonthlyTrend($userId),
                'active_goals' => $this->model->getActiveGoals($userId),
                'upcoming_bills' => $this->model->getUpcomingBills($userId)
            ]
        ];
    }
}