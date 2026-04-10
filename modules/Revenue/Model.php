<?php
require_once __DIR__ . '/../../core/Database.php';

class RevenueModel {
    private $db;

    public function __construct() {
        $this->db = Database::getInstance()->getConnection();
    }

    public function getAll($userId) {
        $stmt = $this->db->prepare("SELECT * FROM revenu WHERE utilisateur_id = :user_id ORDER BY date DESC");
        $stmt->execute(['user_id' => $userId]);
        return $stmt->fetchAll();
    }

    public function getById($id, $userId) {
        $stmt = $this->db->prepare("SELECT * FROM revenu WHERE id = :id AND utilisateur_id = :user_id");
        $stmt->execute(['id' => $id, 'user_id' => $userId]);
        return $stmt->fetch();
    }

    public function create($data, $userId) {
        $stmt = $this->db->prepare("INSERT INTO revenu (utilisateur_id, montant, source, date) VALUES (:user_id, :montant, :source, :date)");
        $stmt->execute([
            'user_id' => $userId,
            'montant' => $data['montant'],
            'source' => $data['source'] ?? null,
            'date' => $data['date'] ?? date('Y-m-d')
        ]);
        return $this->db->lastInsertId();
    }

    public function update($id, $data, $userId) {
        $fields = [];
        $params = ['id' => $id, 'user_id' => $userId];
        foreach (['montant', 'source', 'date'] as $field) {
            if (isset($data[$field])) {
                $fields[] = "$field = :$field";
                $params[$field] = $data[$field];
            }
        }
        if (empty($fields)) return false;
        $sql = "UPDATE revenu SET " . implode(', ', $fields) . " WHERE id = :id AND utilisateur_id = :user_id";
        $stmt = $this->db->prepare($sql);
        return $stmt->execute($params);
    }

    public function delete($id, $userId) {
        $stmt = $this->db->prepare("DELETE FROM revenu WHERE id = :id AND utilisateur_id = :user_id");
        return $stmt->execute(['id' => $id, 'user_id' => $userId]);
    }
}