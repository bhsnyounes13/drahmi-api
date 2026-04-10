<?php
require_once __DIR__ . '/../../core/Database.php';

class NotificationModel {
    private $db;

    public function __construct() {
        $this->db = Database::getInstance()->getConnection();
    }

    public function getAll($userId, $limit = 20) {
        $stmt = $this->db->prepare("SELECT * FROM historique WHERE utilisateur_id = :user_id ORDER BY date DESC LIMIT :limit");
        $stmt->bindValue(':user_id', $userId, PDO::PARAM_INT);
        $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll();
    }

    public function getUnread($userId) {
        $stmt = $this->db->prepare("SELECT * FROM historique WHERE utilisateur_id = :user_id AND lu = 0 ORDER BY date DESC");
        $stmt->execute(['user_id' => $userId]);
        return $stmt->fetchAll();
    }

    public function markAsRead($id, $userId) {
        $stmt = $this->db->prepare("UPDATE historique SET lu = 1 WHERE id = :id AND utilisateur_id = :user_id");
        return $stmt->execute(['id' => $id, 'user_id' => $userId]);
    }

    public function create($data, $userId) {
        $stmt = $this->db->prepare("INSERT INTO historique (utilisateur_id, action, entite_type, entite_id, details, date) VALUES (:user_id, :action, :type, :entity_id, :details, NOW())");
        $stmt->execute([
            'user_id' => $userId,
            'action' => $data['action'] ?? 'notification',
            'type' => $data['type'] ?? 'general',
            'entity_id' => $data['entity_id'] ?? null,
            'details' => $data['message'] ?? null
        ]);
        return $this->db->lastInsertId();
    }
}