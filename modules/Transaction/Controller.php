<?php
require_once __DIR__ . '/Service.php';
require_once __DIR__ . '/../../middleware/AuthMiddleware.php';
require_once __DIR__ . '/../../core/Response.php';

class TransactionController {
    private $service;

    public function __construct() {
        $this->service = new TransactionService();
    }

    public function index() {
        $user = AuthMiddleware::verify();
        $limit = $_GET['limit'] ?? 50;
        $offset = $_GET['offset'] ?? 0;
        
        $result = $this->service->getAll($user->id, $limit, $offset);
        Response::success('Transactions retrieved', $result['data']);
    }

    public function show($id) {
        $user = AuthMiddleware::verify();
        $result = $this->service->getById($id, $user->id);
        
        if ($result['success']) {
            Response::success('Transaction found', $result['data']);
        } else {
            Response::notFound($result['message']);
        }
    }

    public function store() {
        $user = AuthMiddleware::verify();
        $data = json_decode(file_get_contents('php://input'), true) ?? [];
        
        $result = $this->service->create($data, $user->id);
        
        if ($result['success']) {
            Response::success($result['message'], $result['data'], 201);
        } else {
            Response::error($result['message']);
        }
    }

    public function update($id) {
        $user = AuthMiddleware::verify();
        $data = json_decode(file_get_contents('php://input'), true) ?? [];
        
        $result = $this->service->update($id, $data, $user->id);
        
        if ($result['success']) {
            Response::success($result['message']);
        } else {
            Response::error($result['message']);
        }
    }

    public function destroy($id) {
        $user = AuthMiddleware::verify();
        $result = $this->service->delete($id, $user->id);
        
        if ($result['success']) {
            Response::success($result['message']);
        } else {
            Response::error($result['message']);
        }
    }

    public function byDate() {
        $user = AuthMiddleware::verify();
        $startDate = $_GET['start_date'] ?? date('Y-m-01');
        $endDate = $_GET['end_date'] ?? date('Y-m-d');
        
        $result = $this->service->getByDateRange($user->id, $startDate, $endDate);
        Response::success('Transactions retrieved', $result['data']);
    }

    public function summary() {
        $user = AuthMiddleware::verify();
        $startDate = $_GET['start_date'] ?? null;
        $endDate = $_GET['end_date'] ?? null;
        
        $result = $this->service->getSummary($user->id, $startDate, $endDate);
        Response::success('Summary retrieved', $result['data']);
    }

    public function byCategory() {
        $user = AuthMiddleware::verify();
        $startDate = $_GET['start_date'] ?? null;
        $endDate = $_GET['end_date'] ?? null;
        
        $result = $this->service->getByCategory($user->id, $startDate, $endDate);
        Response::success('Category breakdown retrieved', $result['data']);
    }
}