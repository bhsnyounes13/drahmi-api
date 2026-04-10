<?php
require_once __DIR__ . '/config/config.php';
require_once __DIR__ . '/core/Database.php';

$db = Database::getInstance()->getConnection();

echo "Creating missing tables...\n\n";

// Create alert table
$db->exec("CREATE TABLE IF NOT EXISTS alert (
    id INT AUTO_INCREMENT PRIMARY KEY,
    utilisateur_id INT NOT NULL,
    type VARCHAR(20) NOT NULL,
    seuil DECIMAL(15,2),
    message TEXT,
    active TINYINT DEFAULT 1,
    date_creation DATETIME DEFAULT CURRENT_TIMESTAMP
)");
echo "✓ alert table created\n";

// Create repartition table
$db->exec("CREATE TABLE IF NOT EXISTS repartition (
    id INT AUTO_INCREMENT PRIMARY KEY,
    utilisateur_id INT NOT NULL,
    categorie_id INT NOT NULL,
    pourcentage DECIMAL(5,2) NOT NULL,
    mois INT NOT NULL,
    annee INT NOT NULL
)");
echo "✓ repartition table created\n";

// Create simulation table
$db->exec("CREATE TABLE IF NOT EXISTS simulation (
    id INT AUTO_INCREMENT PRIMARY KEY,
    utilisateur_id INT NOT NULL,
    parametres JSON,
    resultats JSON,
    date_simulation DATETIME DEFAULT CURRENT_TIMESTAMP
)");
echo "✓ simulation table created\n";

echo "\nAll tables created successfully!";