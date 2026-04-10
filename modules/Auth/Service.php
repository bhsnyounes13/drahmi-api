<?php
require_once __DIR__ . '/Model.php';
require_once __DIR__ . '/../../utils/JwtHelper.php';

class AuthService {
    private $model;

    public function __construct() {
        $this->model = new AuthModel();
    }

    public function register($data) {
        if (!isset($data['email'], $data['password'], $data['nom'], $data['prenom'])) {
            return ['success' => false, 'message' => 'Missing required fields: email, password, nom, prenom'];
        }

        if ($this->model->findByEmail($data['email'])) {
            return ['success' => false, 'message' => 'Email already exists'];
        }

        if (strlen($data['password']) < 6) {
            return ['success' => false, 'message' => 'Password must be at least 6 characters'];
        }

        if (!filter_var($data['email'], FILTER_VALIDATE_EMAIL)) {
            return ['success' => false, 'message' => 'Invalid email format'];
        }

        $userId = $this->model->create($data);
        $token = JwtHelper::encode(['id' => $userId, 'email' => $data['email']]);

        return [
            'success' => true,
            'message' => 'Registration successful',
            'data' => [
                'token' => $token,
                'user' => [
                    'id' => $userId,
                    'email' => $data['email'],
                    'nom' => $data['nom'],
                    'prenom' => $data['prenom']
                ]
            ]
        ];
    }

    public function login($email, $password) {
        $user = $this->model->findByEmail($email);

        if (!$user) {
            return ['success' => false, 'message' => 'Invalid email or password'];
        }

        if (!password_verify($password, $user['mot_de_passe'])) {
            return ['success' => false, 'message' => 'Invalid email or password'];
        }

        $this->model->updateLastLogin($user['id']);

        $token = JwtHelper::encode([
            'id' => $user['id'],
            'email' => $user['email']
        ]);

        return [
            'success' => true,
            'message' => 'Login successful',
            'data' => [
                'token' => $token,
                'user' => [
                    'id' => $user['id'],
                    'email' => $user['email'],
                    'nom' => $user['nom'],
                    'prenom' => $user['prenom']
                ]
            ]
        ];
    }

    public function refreshToken($token) {
        $newToken = JwtHelper::refresh($token);
        
        if (!$newToken) {
            return ['success' => false, 'message' => 'Invalid token'];
        }

        return [
            'success' => true,
            'message' => 'Token refreshed',
            'data' => ['token' => $newToken]
        ];
    }

    public function getProfile($userId) {
        $user = $this->model->findById($userId);
        
        if (!$user) {
            return ['success' => false, 'message' => 'User not found'];
        }

        return ['success' => true, 'data' => $user];
    }
}