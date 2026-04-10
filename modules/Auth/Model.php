<?php
require_once __DIR__ . '/../../core/Database.php';

class AuthModel {
    private $db;

    public function __construct() {
        $this->db = Database::getInstance()->getConnection();
    }

    public function findByEmail($email) {
        $stmt = $this->db->prepare("SELECT * FROM utilisateur WHERE email = :email");
        $stmt->execute(['email' => $email]);
        return $stmt->fetch();
    }

    public function findById($id) {
        $stmt = $this->db->prepare("SELECT id, nom, prenom, email, date_creation FROM utilisateur WHERE id = :id");
        $stmt->execute(['id' => $id]);
        return $stmt->fetch();
    }

    public function create($data) {
        $hashedPassword = password_hash($data['password'], PASSWORD_BCRYPT, ['cost' => 12]);
        
        $sql = "INSERT INTO utilisateur (nom, prenom, email, mot_de_passe, date_creation) 
                VALUES (:nom, :prenom, :email, :mot_de_passe, NOW())";
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute([
            'nom' => $data['nom'],
            'prenom' => $data['prenom'],
            'email' => $data['email'],
            'mot_de_passe' => $hashedPassword
        ]);
        
        return $this->db->lastInsertId();
    }

    public function updatePassword($userId, $newPassword) {
        $hashed = password_hash($newPassword, PASSWORD_BCRYPT, ['cost' => 12]);
        $stmt = $this->db->prepare("UPDATE utilisateur SET mot_de_passe = :mot_de_passe WHERE id = :id");
        return $stmt->execute(['mot_de_passe' => $hashed, 'id' => $userId]);
    }

    public function updateLastLogin($userId) {
        $stmt = $this->db->prepare("UPDATE utilisateur SET dernier_connexion = NOW() WHERE id = :id");
        return $stmt->execute(['id' => $userId]);
    }
}