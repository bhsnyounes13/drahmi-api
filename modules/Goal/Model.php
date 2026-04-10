<?php
require_once __DIR__ . '/../../core/Database.php';

class GoalModel {
    private $db;

    public function __construct() {
        $this->db = Database::getInstance()->getConnection();
    }

    public function getAll($userId) {
        $stmt = $this->db->prepare("SELECT * FROM objectif WHERE utilisateur_id = :user_id ORDER BY date_cible ASC");
        $stmt->execute(['user_id' => $userId]);
        return $stmt->fetchAll();
    }

    public function getById($id, $userId) {
        $stmt = $this->db->prepare("SELECT * FROM objectif WHERE id = :id AND utilisateur_id = :user_id");
        $stmt->execute(['id' => $id, 'user_id' => $userId]);
        return $stmt->fetch();
    }

    public function create($data, $userId) {
        $stmt = $this->db->prepare("INSERT INTO objectif (utilisateur_id, nom, montant_cible, progression, date_cible, statut) VALUES (:user_id, :nom, :montant_cible, 0, :date_cible, 'active')");
        $stmt->execute([
            'user_id' => $userId,
            'nom' => $data['nom'],
            'montant_cible' => $data['montant_cible'],
            'date_cible' => $data['date_cible'] ?? null
        ]);
        return $this->db->lastInsertId();
    }

    public function update($id, $data, $userId) {
        $fields = [];
        $params = ['id' => $id, 'user_id' => $userId];
        foreach (['nom', 'montant_cible', 'progression', 'date_cible', 'statut'] as $field) {
            if (isset($data[$field])) {
                $fields[] = "$field = :$field";
                $params[$field] = $data[$field];
            }
        }
        if (empty($fields)) return false;
        $sql = "UPDATE objectif SET " . implode(', ', $fields) . " WHERE id = :id AND utilisateur_id = :user_id";
        $stmt = $this->db->prepare($sql);
        return $stmt->execute($params);
    }

    public function delete($id, $userId) {
        $stmt = $this->db->prepare("DELETE FROM objectif WHERE id = :id AND utilisateur_id = :user_id");
        return $stmt->execute(['id' => $id, 'user_id' => $userId]);
    }
}