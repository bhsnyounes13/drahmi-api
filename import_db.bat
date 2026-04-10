@echo off
echo Importing Drahmi database...
"C:\xampp\mysql\bin\mysql.exe" -u root drahmi_db -e "SET NAMES utf8mb4; SET FOREIGN_KEY_CHECKS = 0;"

REM Create tables
"C:\xampp\mysql\bin\mysql.exe" -u root drahmi_db -e "CREATE TABLE IF NOT EXISTS utilisateur (id INT AUTO_INCREMENT PRIMARY KEY, nom VARCHAR(100) NOT NULL, prenom VARCHAR(100) NOT NULL, email VARCHAR(255) NOT NULL UNIQUE, mot_de_passe VARCHAR(255) NOT NULL, telephone VARCHAR(20), avatar VARCHAR(500), date_creation DATETIME DEFAULT CURRENT_TIMESTAMP, dernier_connexion DATETIME);"

"C:\xampp\mysql\bin\mysql.exe" -u root drahmi_db -e "CREATE TABLE IF NOT EXISTS categorie (id INT AUTO_INCREMENT PRIMARY KEY, utilisateur_id INT, nom VARCHAR(100) NOT NULL, icone VARCHAR(50) DEFAULT 'category', type ENUM('revenu','depense') NOT NULL, couleur VARCHAR(20) DEFAULT '#6D76FF');"

"C:\xampp\mysql\bin\mysql.exe" -u root drahmi_db -e "CREATE TABLE IF NOT EXISTS transaction (id INT AUTO_INCREMENT PRIMARY KEY, utilisateur_id INT NOT NULL, montant DECIMAL(15,2) NOT NULL, type ENUM('revenu','depense') NOT NULL, categorie_id INT, description TEXT, date DATE NOT NULL, date_creation DATETIME DEFAULT CURRENT_TIMESTAMP);"

"C:\xampp\mysql\bin\mysql.exe" -u root drahmi_db -e "CREATE TABLE IF NOT EXISTS revenu (id INT AUTO_INCREMENT PRIMARY KEY, utilisateur_id INT NOT NULL, montant DECIMAL(15,2) NOT NULL, source VARCHAR(200), date DATE NOT NULL);"

"C:\xampp\mysql\bin\mysql.exe" -u root drahmi_db -e "CREATE TABLE IF NOT EXISTS objectif (id INT AUTO_INCREMENT PRIMARY KEY, utilisateur_id INT NOT NULL, nom VARCHAR(200) NOT NULL, montant_cible DECIMAL(15,2) NOT NULL, progression DECIMAL(15,2) DEFAULT 0, date_cible DATE, statut ENUM('active','completed','paused') DEFAULT 'active');"

"C:\xampp\mysql\bin\mysql.exe" -u root drahmi_db -e "CREATE TABLE IF NOT EXISTS epargne (id INT AUTO_INCREMENT PRIMARY KEY, utilisateur_id INT NOT NULL, nom VARCHAR(200) NOT NULL, montant DECIMAL(15,2) NOT NULL, taux DECIMAL(5,2) DEFAULT 0);"

"C:\xampp\mysql\bin\mysql.exe" -u root drahmi_db -e "CREATE TABLE IF NOT EXISTS dette (id INT AUTO_INCREMENT PRIMARY KEY, utilisateur_id INT NOT NULL, nom VARCHAR(200) NOT NULL, montant_initial DECIMAL(15,2) NOT NULL, montant_restant DECIMAL(15,2) NOT NULL, taux_interet DECIMAL(5,2) DEFAULT 0, statut ENUM('active','paid') DEFAULT 'active');"

"C:\xampp\mysql\bin\mysql.exe" -u root drahmi_db -e "CREATE TABLE IF NOT EXISTS facture (id INT AUTO_INCREMENT PRIMARY KEY, utilisateur_id INT NOT NULL, nom VARCHAR(200) NOT NULL, montant DECIMAL(15,2) NOT NULL, date_echeance DATE NOT NULL, paye TINYINT DEFAULT 0);"

"C:\xampp\mysql\bin\mysql.exe" -u root drahmi_db -e "CREATE TABLE IF NOT EXISTS alert (id INT AUTO_INCREMENT PRIMARY KEY, utilisateur_id INT NOT NULL, type ENUM('budget','goal','bill') NOT NULL, seuil DECIMAL(15,2), message TEXT, active TINYINT DEFAULT 1);"

"C:\xampp\mysql\bin\mysql.exe" -u root drahmi_db -e "CREATE TABLE IF NOT EXISTS historique (id INT AUTO_INCREMENT PRIMARY KEY, utilisateur_id INT NOT NULL, action VARCHAR(50) NOT NULL, entite_type VARCHAR(50), entite_id INT, details TEXT, lu TINYINT DEFAULT 0);"

"C:\xampp\mysql\bin\mysql.exe" -u root drahmi_db -e "CREATE TABLE IF NOT EXISTS repartition (id INT AUTO_INCREMENT PRIMARY KEY, utilisateur_id INT NOT NULL, categorie_id INT NOT NULL, pourcentage DECIMAL(5,2) NOT NULL, mois INT NOT NULL, annee INT NOT NULL);"

"C:\xampp\mysql\bin\mysql.exe" -u root drahmi_db -e "CREATE TABLE IF NOT EXISTS simulation (id INT AUTO_INCREMENT PRIMARY KEY, utilisateur_id INT NOT NULL, parametres JSON, resultats JSON, date_simulation DATETIME DEFAULT CURRENT_TIMESTAMP);"

REM Insert default categories
"C:\xampp\mysql\bin\mysql.exe" -u root drahmi_db -e "INSERT INTO categorie (nom, icone, type, couleur) VALUES ('Alimentation', 'restaurant', 'depense', '#FF6B6B'), ('Transport', 'directions_car', 'depense', '#4ECDC4'), ('Loisirs', 'sports_esports', 'depense', '#45B7D1'), ('Shopping', 'shopping_bag', 'depense', '#96CEB4'), ('Santé', 'local_hospital', 'depense', '#FFEAA7'), ('Maison', 'home', 'depense', '#DDA0DD'), ('Factures', 'receipt', 'depense', '#98D8C8'), ('Autre Dépense', 'more_horiz', 'depense', '#B8B8B8'), ('Salaire', 'work', 'revenu', '#6D76FF'), ('Freelance', 'laptop', 'revenu', '#4ECDC4'), ('Investissement', 'trending_up', 'revenu', '#45B7D1'), ('Autre Revenu', 'attach_money', 'revenu', '#96CEB4');"

REM Insert test user (password: password123)
"C:\xampp\mysql\bin\mysql.exe" -u root drahmi_db -e "INSERT INTO utilisateur (nom, prenom, email, mot_de_passe) VALUES ('Test', 'User', 'test@drahmi.com', '\$2y\$12\$LQv3c1yqBWVHxkd0LHAkCOYz6TtxMQJqhN8/LewY5GyYWv0X8J7uO');"

echo Database imported successfully!
pause