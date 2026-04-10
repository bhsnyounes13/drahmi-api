<?php
require_once __DIR__ . '/../../core/Database.php';

class CategoryModel {
    private $db;

    public function __construct() {
        $this->db = Database::getInstance()->getConnection();
    }

    public function getAll($userId) {
        $stmt = $this->db->prepare("
            SELECT * FROM categorie 
            WHERE utilisateur_id IS NULL OR utilisateur_id = :user_id
            ORDER BY type, nom
        ");
        $stmt->execute(['user_id' => $userId]);
        return $stmt->fetchAll();
    }

    public function getById($id) {
        $stmt = $this->db->prepare("SELECT * FROM categorie WHERE id = :id");
        $stmt->execute(['id' => $id]);
        return $stmt->fetch();
    }

    public function getByType($userId, $type) {
        $stmt = $this->db->prepare("
            SELECT * FROM categorie 
            WHERE (utilisateur_id IS NULL OR utilisateur_id = :user_id) AND type = :type
            ORDER BY nom
        ");
        $stmt->execute(['user_id' => $userId, 'type' => $type]);
        return $stmt->fetchAll();
    }

    public function create($data, $userId) {
        $sql = "INSERT INTO categorie (utilisateur_id, nom, icone, type, couleur)
                VALUES (:utilisateur_id, :nom, :icone, :type, :couleur)";
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute([
            'utilisateur_id' => $userId,
            'nom' => $data['nom'],
            'icone' => $data['icone'] ?? 'category',
            'type' => $data['type'],
            'couleur' => $data['couleur'] ?? '#6D76FF'
        ]);
        return $this->db->lastInsertId();
    }

    public function update($id, $data) {
        $fields = [];
        $params = ['id' => $id];

        foreach (['nom', 'icone', 'type', 'couleur'] as $field) {
            if (isset($data[$field])) {
                $fields[] = "$field = :$field";
                $params[$field] = $data[$field];
            }
        }

        if (empty($fields)) return false;

        $sql = "UPDATE categorie SET " . implode(', ', $fields) . " WHERE id = :id";
        $stmt = $this->db->prepare($sql);
        return $stmt->execute($params);
    }

    public function delete($id) {
        $stmt = $this->db->prepare("DELETE FROM categorie WHERE id = :id");
        return $stmt->execute(['id' => $id]);
    }
}