<?php
require_once __DIR__ . '/Service.php';
require_once __DIR__ . '/../../middleware/AuthMiddleware.php';
require_once __DIR__ . '/../../core/Response.php';

class DebtController {
    private $service;

    public function __construct() {
        $this->service = new DebtService();
    }

    public function index() {
        $user = AuthMiddleware::verify();
        $result = $this->service->getAll($user->id);
        Response::success('Debts retrieved', $result['data']);
    }

    public function store() {
        $user = AuthMiddleware::verify();
        $data = json_decode(file_get_contents('php://input'), true) ?? [];
        $result = $this->service->create($data, $user->id);
        $result['success'] ? Response::success($result['message'], $result['data'], 201) : Response::error($result['message']);
    }

    public function update($id) {
        $user = AuthMiddleware::verify();
        $data = json_decode(file_get_contents('php://input'), true) ?? [];
        $result = $this->service->update($id, $data, $user->id);
        $result['success'] ? Response::success($result['message']) : Response::error($result['message']);
    }

    public function destroy($id) {
        $user = AuthMiddleware::verify();
        $result = $this->service->delete($id, $user->id);
        $result['success'] ? Response::success($result['message']) : Response::error($result['message']);
    }
}