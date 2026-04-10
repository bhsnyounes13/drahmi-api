<?php
require_once __DIR__ . '/Service.php';
require_once __DIR__ . '/../../middleware/AuthMiddleware.php';
require_once __DIR__ . '/../../core/Response.php';

class NotificationController {
    private $service;

    public function __construct() {
        $this->service = new NotificationService();
    }

    public function index() {
        $user = AuthMiddleware::verify();
        $limit = $_GET['limit'] ?? 20;
        $result = $this->service->getAll($user->id, $limit);
        Response::success('Notifications retrieved', $result['data']);
    }

    public function unread() {
        $user = AuthMiddleware::verify();
        $result = $this->service->getUnread($user->id);
        Response::success('Unread notifications retrieved', $result['data']);
    }

    public function markAsRead($id) {
        $user = AuthMiddleware::verify();
        $result = $this->service->markAsRead($id, $user->id);
        $result['success'] ? Response::success($result['message']) : Response::error($result['message']);
    }
}