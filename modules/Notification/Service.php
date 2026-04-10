<?php
require_once __DIR__ . '/Model.php';

class NotificationService {
    private $model;

    public function __construct() {
        $this->model = new NotificationModel();
    }

    public function getAll($userId, $limit = 20) {
        $notifications = $this->model->getAll($userId, $limit);
        return ['success' => true, 'data' => $notifications];
    }

    public function getUnread($userId) {
        $notifications = $this->model->getUnread($userId);
        return ['success' => true, 'data' => $notifications];
    }

    public function markAsRead($id, $userId) {
        $result = $this->model->markAsRead($id, $userId);
        return $result ? ['success' => true, 'message' => 'Marked as read'] : ['success' => false, 'message' => 'Failed'];
    }
}