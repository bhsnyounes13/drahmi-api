<?php
require_once __DIR__ . '/../../core/Database.php';

class UserModel {
    private $db;

    public function __construct() {
        $this->db = Database::getInstance()->getConnection();
    }

    public function getById($id) {
        $stmt = $this->db->prepare("SELECT id, nom, prenom, email, telephone, date_creation, dernier_connexion FROM utilisateur WHERE id = :id");
        $stmt->execute(['id' => $id]);
        return $stmt->fetch();
    }

    public function update($id, $data) {
        $fields = [];
        $params = ['id' => $id];

        foreach (['nom', 'prenom', 'telephone'] as $field) {
            if (isset($data[$field])) {
                $fields[] = "$field = :$field";
                $params[$field] = $data[$field];
            }
        }

        if (empty($fields)) return false;

        $sql = "UPDATE utilisateur SET " . implode(', ', $fields) . " WHERE id = :id";
        $stmt = $this->db->prepare($sql);
        return $stmt->execute($params);
    }

    public function updateAvatar($id, $avatarPath) {
        $stmt = $this->db->prepare("UPDATE utilisateur SET avatar = :avatar WHERE id = :id");
        return $stmt->execute(['avatar' => $avatarPath, 'id' => $id]);
    }
}