<?php
require_once __DIR__ . '/../../core/Database.php';

class SavingModel {
    private $db;

    public function __construct() {
        $this->db = Database::getInstance()->getConnection();
    }

    public function getAll($userId) {
        $stmt = $this->db->prepare("SELECT * FROM epargne WHERE utilisateur_id = :user_id ORDER BY date_creation DESC");
        $stmt->execute(['user_id' => $userId]);
        return $stmt->fetchAll();
    }

    public function create($data, $userId) {
        $stmt = $this->db->prepare("INSERT INTO epargne (utilisateur_id, nom, montant, taux, date_creation) VALUES (:user_id, :nom, :montant, :taux, NOW())");
        $stmt->execute([
            'user_id' => $userId,
            'nom' => $data['nom'],
            'montant' => $data['montant'],
            'taux' => $data['taux'] ?? 0
        ]);
        return $this->db->lastInsertId();
    }

    public function update($id, $data, $userId) {
        $fields = [];
        $params = ['id' => $id, 'user_id' => $userId];
        foreach (['nom', 'montant', 'taux'] as $field) {
            if (isset($data[$field])) {
                $fields[] = "$field = :$field";
                $params[$field] = $data[$field];
            }
        }
        if (empty($fields)) return false;
        $sql = "UPDATE epargne SET " . implode(', ', $fields) . " WHERE id = :id AND utilisateur_id = :user_id";
        $stmt = $this->db->prepare($sql);
        return $stmt->execute($params);
    }

    public function delete($id, $userId) {
        $stmt = $this->db->prepare("DELETE FROM epargne WHERE id = :id AND utilisateur_id = :user_id");
        return $stmt->execute(['id' => $id, 'user_id' => $userId]);
    }
}