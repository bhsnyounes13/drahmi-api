<?php
require_once __DIR__ . '/Service.php';
require_once __DIR__ . '/../../middleware/AuthMiddleware.php';
require_once __DIR__ . '/../../core/Response.php';

class DashboardController {
    private $service;

    public function __construct() {
        $this->service = new DashboardService();
    }

    public function summary() {
        $user = AuthMiddleware::verify();
        $startDate = $_GET['start_date'] ?? null;
        $endDate = $_GET['end_date'] ?? null;
        $result = $this->service->getSummary($user->id, $startDate, $endDate);
        Response::success('Summary retrieved', $result['data']);
    }

    public function spendingByCategory() {
        $user = AuthMiddleware::verify();
        $startDate = $_GET['start_date'] ?? null;
        $endDate = $_GET['end_date'] ?? null;
        $result = $this->service->getSpendingByCategory($user->id, $startDate, $endDate);
        Response::success('Spending by category retrieved', $result['data']);
    }

    public function monthlyTrend() {
        $user = AuthMiddleware::verify();
        $months = $_GET['months'] ?? 6;
        $result = $this->service->getMonthlyTrend($user->id, $months);
        Response::success('Monthly trend retrieved', $result['data']);
    }

    public function goals() {
        $user = AuthMiddleware::verify();
        $result = $this->service->getGoals($user->id);
        Response::success('Goals retrieved', $result['data']);
    }

    public function bills() {
        $user = AuthMiddleware::verify();
        $result = $this->service->getBills($user->id);
        Response::success('Bills retrieved', $result['data']);
    }

    public function index() {
        $user = AuthMiddleware::verify();
        $result = $this->service->getFullDashboard($user->id);
        Response::success('Dashboard data retrieved', $result['data']);
    }
}