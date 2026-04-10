<?php
require_once __DIR__ . '/config/config.php';
require_once __DIR__ . '/core/Database.php';

$db = Database::getInstance()->getConnection();

// Insert test user (password: password123)
$password = password_hash('password123', PASSWORD_BCRYPT, ['cost' => 12]);
$stmt = $db->prepare("INSERT INTO utilisateur (nom, prenom, email, mot_de_passe) VALUES (?, ?, ?, ?)");
$stmt->execute(['Test', 'User', 'test@drahmi.com', $password]);
echo "User created: " . $db->lastInsertId() . "\n";

// Insert default categories
$categories = [
    ['Alimentation', 'restaurant', 'depense', '#FF6B6B'],
    ['Transport', 'directions_car', 'depense', '#4ECDC4'],
    ['Loisirs', 'sports_esports', 'depense', '#45B7D1'],
    ['Shopping', 'shopping_bag', 'depense', '#96CEB4'],
    ['Santé', 'local_hospital', 'depense', '#FFEAA7'],
    ['Maison', 'home', 'depense', '#DDA0DD'],
    ['Factures', 'receipt', 'depense', '#98D8C8'],
    ['Autre Dépense', 'more_horiz', 'depense', '#B8B8B8'],
    ['Salaire', 'work', 'revenu', '#6D76FF'],
    ['Freelance', 'laptop', 'revenu', '#4ECDC4'],
    ['Investissement', 'trending_up', 'revenu', '#45B7D1'],
    ['Autre Revenu', 'attach_money', 'revenu', '#96CEB4']
];

foreach ($categories as $cat) {
    $stmt = $db->prepare("INSERT INTO categorie (nom, icone, type, couleur) VALUES (?, ?, ?, ?)");
    $stmt->execute($cat);
}
echo "Categories created: " . count($categories) . "\n";

echo "Done!";