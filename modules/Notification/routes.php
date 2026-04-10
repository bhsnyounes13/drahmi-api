<?php
require_once __DIR__ . '/Controller.php';

return [
    'GET /api/notifications' => [NotificationController::class, 'index'],
    'GET /api/notifications/unread' => [NotificationController::class, 'unread'],
    'PUT /api/notifications/{id}/read' => [NotificationController::class, 'markAsRead']
];