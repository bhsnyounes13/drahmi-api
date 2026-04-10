<?php
require_once __DIR__ . '/../../core/Database.php';

class DebtModel {
    private $db;

    public function __construct() {
        $this->db = Database::getInstance()->getConnection();
    }

    public function getAll($userId) {
        $stmt = $this->db->prepare("SELECT * FROM dette WHERE utilisateur_id = :user_id ORDER BY date_creation DESC");
        $stmt->execute(['user_id' => $userId]);
        return $stmt->fetchAll();
    }

    public function create($data, $userId) {
        $stmt = $this->db->prepare("INSERT INTO dette (utilisateur_id, nom, montant_initial, montant_restant, taux_interet, cree_par) VALUES (:user_id, :nom, :montant, :montant, :taux, NOW())");
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
        foreach (['nom', 'montant_restant', 'taux_interet', 'statut'] as $field) {
            if (isset($data[$field])) {
                $fields[] = "$field = :$field";
                $params[$field] = $data[$field];
            }
        }
        if (empty($fields)) return false;
        $sql = "UPDATE dette SET " . implode(', ', $fields) . " WHERE id = :id AND utilisateur_id = :user_id";
        $stmt = $this->db->prepare($sql);
        return $stmt->execute($params);
    }

    public function delete($id, $userId) {
        $stmt = $this->db->prepare("DELETE FROM dette WHERE id = :id AND utilisateur_id = :user_id");
        return $stmt->execute(['id' => $id, 'user_id' => $userId]);
    }
}