<?php
require_once __DIR__ . '/Service.php';
require_once __DIR__ . '/../../middleware/AuthMiddleware.php';
require_once __DIR__ . '/../../core/Response.php';

class CategoryController {
    private $service;

    public function __construct() {
        $this->service = new CategoryService();
    }

    public function index() {
        $user = AuthMiddleware::verify();
        $result = $this->service->getAll($user->id);
        Response::success('Categories retrieved', $result['data']);
    }

    public function show($id) {
        $user = AuthMiddleware::verify();
        $result = $this->service->getById($id);
        
        if ($result['success']) {
            Response::success('Category found', $result['data']);
        } else {
            Response::notFound($result['message']);
        }
    }

    public function byType($type) {
        $user = AuthMiddleware::verify();
        $result = $this->service->getByType($user->id, $type);
        
        if ($result['success']) {
            Response::success('Categories retrieved', $result['data']);
        } else {
            Response::error($result['message']);
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
        
        $result = $this->service->update($id, $data);
        
        if ($result['success']) {
            Response::success($result['message']);
        } else {
            Response::error($result['message']);
        }
    }

    public function destroy($id) {
        $user = AuthMiddleware::verify();
        $result = $this->service->delete($id);
        
        if ($result['success']) {
            Response::success($result['message']);
        } else {
            Response::error($result['message']);
        }
    }
}