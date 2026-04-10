<?php
require_once __DIR__ . '/Service.php';
require_once __DIR__ . '/../../core/Response.php';

class AuthController {
    private $service;

    public function __construct() {
        $this->service = new AuthService();
    }

    public function register() {
        $data = json_decode(file_get_contents('php://input'), true) ?? [];
        $result = $this->service->register($data);

        if ($result['success']) {
            Response::success($result['message'], $result['data'], 201);
        } else {
            Response::error($result['message']);
        }
    }

    public function login() {
        $input = file_get_contents('php://input');
        file_put_contents(__DIR__ . '/../../logs/auth.log', date('Y-m-d H:i:s') . " login input: " . strlen($input) . " bytes\n" . $input . "\n", FILE_APPEND);
        $data = json_decode($input, true) ?? [];
        
        if (!isset($data['email']) || !isset($data['password'])) {
            Response::error('Email and password are required');
        }

        $result = $this->service->login($data['email'], $data['password']);

        if ($result['success']) {
            Response::success($result['message'], $result['data']);
        } else {
            Response::error($result['message'], 401);
        }
    }

    public function refresh() {
        $data = json_decode(file_get_contents('php://input'), true) ?? [];
        
        if (!isset($data['token'])) {
            Response::error('Token is required');
        }

        $result = $this->service->refreshToken($data['token']);

        if ($result['success']) {
            Response::success($result['message'], $result['data']);
        } else {
            Response::error($result['message']);
        }
    }

    public function profile() {
        $user = $this->verifyToken();
        $result = $this->service->getProfile($user->id);

        if ($result['success']) {
            Response::success('Profile retrieved', $result['data']);
        } else {
            Response::error($result['message']);
        }
    }

    private function verifyToken() {
        $authHeader = $_SERVER['HTTP_AUTHORIZATION'] ?? null;
        
        if (!$authHeader) {
            Response::unauthorized('No token provided');
        }

        $parts = explode(' ', $authHeader);
        if (count($parts) !== 2) {
            Response::unauthorized('Invalid token format');
        }

        $token = $parts[1];
        require_once __DIR__ . '/../../utils/JwtHelper.php';
        $decoded = JwtHelper::decode($token);

        if (!$decoded) {
            Response::unauthorized('Invalid or expired token');
        }

        return $decoded;
    }
}