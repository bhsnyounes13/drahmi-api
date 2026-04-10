<?php
require_once __DIR__ . '/Service.php';
require_once __DIR__ . '/../../middleware/AuthMiddleware.php';
require_once __DIR__ . '/../../core/Response.php';

class UserController {
    private $service;

    public function __construct() {
        $this->service = new UserService();
    }

    public function profile() {
        $user = AuthMiddleware::verify();
        $result = $this->service->getProfile($user->id);

        if ($result['success']) {
            Response::success('Profile retrieved', $result['data']);
        } else {
            Response::error($result['message']);
        }
    }

    public function update() {
        $user = AuthMiddleware::verify();
        $data = json_decode(file_get_contents('php://input'), true) ?? [];
        
        $result = $this->service->updateProfile($user->id, $data);
        
        if ($result['success']) {
            Response::success($result['message']);
        } else {
            Response::error($result['message']);
        }
    }

    public function updateAvatar() {
        $user = AuthMiddleware::verify();
        $data = json_decode(file_get_contents('php://input'), true) ?? [];
        
        if (!isset($data['avatar'])) {
            Response::error('Avatar path is required');
        }

        $result = $this->service->updateAvatar($user->id, $data['avatar']);
        
        if ($result['success']) {
            Response::success($result['message'], $result['data']);
        } else {
            Response::error($result['message']);
        }
    }
}