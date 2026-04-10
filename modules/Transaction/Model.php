<?php
require_once __DIR__ . '/../../core/Database.php';

class TransactionModel {
    private $db;

    public function __construct() {
        $this->db = Database::getInstance()->getConnection();
    }

    public function getAll($userId, $limit = 50, $offset = 0) {
        $stmt = $this->db->prepare("
            SELECT t.*, c.nom as categorie_nom, c.icone as categorie_icon, c.couleur as categorie_couleur
            FROM transactionss t
            LEFT JOIN categorie c ON t.categorie_id = c.id
            WHERE t.utilisateur_id = :user_id
            ORDER BY t.date DESC, t.id DESC
            LIMIT :limit OFFSET :offset
        ");
        $stmt->bindValue(':user_id', $userId, PDO::PARAM_INT);
        $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
        $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll();
    }

    public function getById($id, $userId) {
        $stmt = $this->db->prepare("
            SELECT t.*, c.nom as categorie_nom, c.icone as categorie_icon
            FROM transactions t
            LEFT JOIN categorie c ON t.categorie_id = c.id
            WHERE t.id = :id AND t.utilisateur_id = :user_id
        ");
        $stmt->execute(['id' => $id, 'user_id' => $userId]);
        return $stmt->fetch();
    }

    public function create($data, $userId) {
        $sql = "INSERT INTO transactions (utilisateur_id, montant, type, categorie_id, description, date)
                VALUES (:utilisateur_id, :montant, :type, :categorie_id, :description, :date)";
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute([
            'utilisateur_id' => $userId,
            'montant' => $data['montant'],
            'type' => $data['type'],
            'categorie_id' => $data['categorie_id'],
            'description' => $data['description'] ?? null,
            'date' => $data['date'] ?? date('Y-m-d')
        ]);

        return $this->db->lastInsertId();
    }

    public function update($id, $data, $userId) {
        $fields = [];
        $params = ['id' => $id, 'user_id' => $userId];

        foreach (['montant', 'type', 'categorie_id', 'description', 'date'] as $field) {
            if (isset($data[$field])) {
                $fields[] = "$field = :$field";
                $params[$field] = $data[$field];
            }
        }

        if (empty($fields)) return false;

        $sql = "UPDATE transactions SET " . implode(', ', $fields) . " WHERE id = :id AND utilisateur_id = :user_id";
        $stmt = $this->db->prepare($sql);
        return $stmt->execute($params);
    }

    public function delete($id, $userId) {
        $stmt = $this->db->prepare("DELETE FROM transactions WHERE id = :id AND utilisateur_id = :user_id");
        return $stmt->execute(['id' => $id, 'user_id' => $userId]);
    }

    public function getByDateRange($userId, $startDate, $endDate) {
        $stmt = $this->db->prepare("
            SELECT t.*, c.nom as categorie_nom
            FROM transactions t
            LEFT JOIN categorie c ON t.categorie_id = c.id
            WHERE t.utilisateur_id = :user_id AND t.date BETWEEN :start_date AND :end_date
            ORDER BY t.date DESC
        ");
        $stmt->execute([
            'user_id' => $userId,
            'start_date' => $startDate,
            'end_date' => $endDate
        ]);
        return $stmt->fetchAll();
    }

    public function getTotalByType($userId, $type, $startDate = null, $endDate = null) {
        $sql = "SELECT SUM(montant) as total FROM transactions WHERE utilisateur_id = :user_id AND type = :type";
        $params = ['user_id' => $userId, 'type' => $type];

        if ($startDate && $endDate) {
            $sql .= " AND date BETWEEN :start_date AND :end_date";
            $params['start_date'] = $startDate;
            $params['end_date'] = $endDate;
        }

        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        $result = $stmt->fetch();
        return $result['total'] ?? 0;
    }

    public function getByCategory($userId, $startDate = null, $endDate = null) {
        $sql = "
            SELECT c.nom, c.couleur, SUM(t.montant) as total
            FROM transactions t
            JOIN categorie c ON t.categorie_id = c.id
            WHERE t.utilisateur_id = :user_id AND t.type = 'depense'
        ";
        $params = ['user_id' => $userId];

        if ($startDate && $endDate) {
            $sql .= " AND t.date BETWEEN :start_date AND :end_date";
            $params['start_date'] = $startDate;
            $params['end_date'] = $endDate;
        }

        $sql .= " GROUP BY c.id, c.nom, c.couleur ORDER BY total DESC";

        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll();
    }
}