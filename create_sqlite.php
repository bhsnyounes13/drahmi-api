<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

$dbFile = __DIR__ . '/drahmi.db';

echo "Creating SQLite database: $dbFile\n\n";

try {
    $db = new PDO("sqlite:$dbFile");
    $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    echo "✓ SQLite database created\n\n";
    
    // Create tables
    $db->exec("
        CREATE TABLE IF NOT EXISTS utilisateur (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            nom TEXT NOT NULL,
            prenom TEXT NOT NULL,
            email TEXT UNIQUE NOT NULL,
            mot_de_passe TEXT NOT NULL,
            telephone TEXT,
            avatar TEXT,
            date_creation DATETIME DEFAULT CURRENT_TIMESTAMP,
            dernier_connexion DATETIME
        );
        
        CREATE TABLE IF NOT EXISTS categorie (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            utilisateur_id INTEGER,
            nom TEXT NOT NULL,
            icone TEXT DEFAULT 'category',
            type TEXT NOT NULL,
            couleur TEXT DEFAULT '#6D76FF'
        );
        
        CREATE TABLE IF NOT EXISTS transactions (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            utilisateur_id INTEGER NOT NULL,
            montant REAL NOT NULL,
            type TEXT NOT NULL,
            categorie_id INTEGER,
            description TEXT,
            date TEXT NOT NULL,
            date_creation DATETIME DEFAULT CURRENT_TIMESTAMP
        );
        
        CREATE TABLE IF NOT EXISTS objectif (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            utilisateur_id INTEGER NOT NULL,
            nom TEXT NOT NULL,
            montant_cible REAL NOT NULL,
            progression REAL DEFAULT 0,
            date_cible TEXT,
            statut TEXT DEFAULT 'active',
            date_creation DATETIME DEFAULT CURRENT_TIMESTAMP
        );
        
        CREATE TABLE IF NOT EXISTS epargne (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            utilisateur_id INTEGER NOT NULL,
            nom TEXT NOT NULL,
            montant REAL NOT NULL,
            taux REAL DEFAULT 0,
            date_creation DATETIME DEFAULT CURRENT_TIMESTAMP
        );
        
        CREATE TABLE IF NOT EXISTS dette (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            utilisateur_id INTEGER NOT NULL,
            nom TEXT NOT NULL,
            montant_initial REAL NOT NULL,
            montant_restant REAL NOT NULL,
            taux_interet REAL DEFAULT 0,
            statut TEXT DEFAULT 'active',
            cree_par DATETIME DEFAULT CURRENT_TIMESTAMP
        );
        
        CREATE TABLE IF NOT EXISTS facture (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            utilisateur_id INTEGER NOT NULL,
            nom TEXT NOT NULL,
            montant REAL NOT NULL,
            date_echeance TEXT NOT NULL,
            paye INTEGER DEFAULT 0,
            date_creation DATETIME DEFAULT CURRENT_TIMESTAMP
        );
        
        CREATE TABLE IF NOT EXISTS alert (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            utilisateur_id INTEGER NOT NULL,
            type TEXT NOT NULL,
            seuil REAL,
            message TEXT,
            active INTEGER DEFAULT 1,
            date_creation DATETIME DEFAULT CURRENT_TIMESTAMP
        );
        
        CREATE TABLE IF NOT EXISTS historique (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            utilisateur_id INTEGER NOT NULL,
            action TEXT NOT NULL,
            entite_type TEXT,
            entite_id INTEGER,
            details TEXT,
            lu INTEGER DEFAULT 0,
            date DATETIME DEFAULT CURRENT_TIMESTAMP
        );
        
        CREATE TABLE IF NOT EXISTS simulation (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            utilisateur_id INTEGER NOT NULL,
            parametres TEXT,
            resultats TEXT,
            date_simulation DATETIME DEFAULT CURRENT_TIMESTAMP
        );
    ");
    
    echo "✓ Tables created\n\n";
    
    // Insert test user
    $password = password_hash('password123', PASSWORD_BCRYPT);
    try {
        $stmt = $db->prepare("INSERT INTO utilisateur (nom, prenom, email, mot_de_passe) VALUES (?, ?, ?, ?)");
        $stmt->execute(['Test', 'User', 'test@drahmi.com', $password]);
        echo "✓ Test user created\n\n";
    } catch (Exception $e) {
        echo "User may already exist\n";
    }
    
    // Insert categories
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
    
    $stmt = $db->prepare("INSERT INTO categorie (nom, icone, type, couleur) VALUES (?, ?, ?, ?)");
    foreach ($categories as $cat) {
        try {
            $stmt->execute($cat);
        } catch (Exception $e) {}
    }
    echo "✓ Categories created\n\n";
    
    echo "SUCCESS! SQLite database ready.\n";
    echo "Database file: $dbFile\n";
    
} catch (Exception $e) {
    echo "ERROR: " . $e->getMessage() . "\n";
}