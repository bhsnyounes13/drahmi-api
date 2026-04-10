<?php
require_once __DIR__ . '/../../core/Database.php';

class BillModel {
    private $db;

    public function __construct() {
        $this->db = Database::getInstance()->getConnection();
    }

    public function getAll($userId) {
        $stmt = $this->db->prepare("SELECT * FROM facture WHERE utilisateur_id = :user_id ORDER BY date_echeance ASC");
        $stmt->execute(['user_id' => $userId]);
        return $stmt->fetchAll();
    }

    public function getUpcoming($userId, $days = 7) {
        $stmt = $this->db->prepare("SELECT * FROM facture WHERE utilisateur_id = :user_id AND date_echeance BETWEEN CURDATE() AND DATE_ADD(CURDATE(), INTERVAL :days DAY) AND paye = 0 ORDER BY date_echeance ASC");
        $stmt->execute(['user_id' => $userId, 'days' => $days]);
        return $stmt->fetchAll();
    }

    public function create($data, $userId) {
        $stmt = $this->db->prepare("INSERT INTO facture (utilisateur_id, nom, montant, date_echeance, paye) VALUES (:user_id, :nom, :montant, :date_echeance, 0)");
        $stmt->execute([
            'user_id' => $userId,
            'nom' => $data['nom'],
            'montant' => $data['montant'],
            'date_echeance' => $data['date_echeance']
        ]);
        return $this->db->lastInsertId();
    }

    public function update($id, $data, $userId) {
        $fields = [];
        $params = ['id' => $id, 'user_id' => $userId];
        foreach (['nom', 'montant', 'date_echeance', 'paye'] as $field) {
            if (isset($data[$field])) {
                $fields[] = "$field = :$field";
                $params[$field] = $data[$field];
            }
        }
        if (empty($fields)) return false;
        $sql = "UPDATE facture SET " . implode(', ', $fields) . " WHERE id = :id AND utilisateur_id = :user_id";
        $stmt = $this->db->prepare($sql);
        return $stmt->execute($params);
    }

    public function delete($id, $userId) {
        $stmt = $this->db->prepare("DELETE FROM facture WHERE id = :id AND utilisateur_id = :user_id");
        return $stmt->execute(['id' => $id, 'user_id' => $userId]);
    }
}