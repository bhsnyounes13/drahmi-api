<?php
require_once __DIR__ . '/Model.php';

class UserService {
    private $model;

    public function __construct() {
        $this->model = new UserModel();
    }

    public function getProfile($userId) {
        $user = $this->model->getById($userId);
        if (!$user) {
            return ['success' => false, 'message' => 'User not found'];
        }
        return ['success' => true, 'data' => $user];
    }

    public function updateProfile($userId, $data) {
        $result = $this->model->update($userId, $data);
        return $result 
            ? ['success' => true, 'message' => 'Profile updated']
            : ['success' => false, 'message' => 'Update failed'];
    }

    public function updateAvatar($userId, $avatarPath) {
        $result = $this->model->updateAvatar($userId, $avatarPath);
        return $result 
            ? ['success' => true, 'message' => 'Avatar updated', 'data' => ['avatar' => $avatarPath]]
            : ['success' => false, 'message' => 'Update failed'];
    }
}