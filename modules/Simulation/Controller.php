<?php
require_once __DIR__ . '/Service.php';
require_once __DIR__ . '/../../middleware/AuthMiddleware.php';
require_once __DIR__ . '/../../core/Response.php';

class SimulationController {
    private $service;

    public function __construct() {
        $this->service = new SimulationService();
    }

    public function calculate() {
        $user = AuthMiddleware::verify();
        $data = json_decode(file_get_contents('php://input'), true) ?? [];
        $result = $this->service->calculate($data, $user->id);
        $result['success'] ? Response::success('Simulation calculated', $result['data']) : Response::error($result['message']);
    }

    public function history() {
        $user = AuthMiddleware::verify();
        $limit = $_GET['limit'] ?? 10;
        $result = $this->service->getHistory($user->id, $limit);
        Response::success('Simulation history retrieved', $result['data']);
    }
}