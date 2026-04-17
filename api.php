<?php
error_reporting(E_ALL);
ini_set('display_errors', 0);
ini_set('log_errors', 1);

$requestUri = $_SERVER['REQUEST_URI'];
$path = parse_url($requestUri, PHP_URL_PATH);
$method = $_SERVER['REQUEST_METHOD'];

// Read body FIRST before anything consumes it
$rawBody = file_get_contents('php://input');

// Debug: log all requests
$logFile = __DIR__ . '/logs/api2.log';
if (!is_dir(__DIR__ . '/logs')) mkdir(__DIR__ . '/logs', 0777);
file_put_contents($logFile, date('Y-m-d H:i:s') . " [$method] $path - body: " . strlen($rawBody) . " bytes: $rawBody\n", FILE_APPEND);

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS');
header('Access-Control-Allow-Headers: Authorization, Content-Type');

if ($method === 'OPTIONS') {
    http_response_code(200);
    exit;
}

require_once __DIR__ . '/config/config.php';
require_once __DIR__ . '/core/Database.php';
require_once __DIR__ . '/utils/JwtHelper.php';

try {
    $db = Database::getInstance()->getConnection();
    
    // No trimming - keep as is
    $input = file_get_contents('php://input');
    file_put_contents(__DIR__ . '/logs/api2.log', date('Y-m-d H:i:s') . " [$method] $path - body: " . strlen($input) . " bytes\n", FILE_APPEND);
    
    // LOGIN - exact match
    if ($method === 'POST' && $path === '/api/auth/login') {
        $data = json_decode($input, true);
        
        if (!isset($data['email']) || !isset($data['password'])) {
            echo json_encode(['success' => false, 'message' => 'Email and password required']);
            exit;
        }
        
        $stmt = $db->prepare("SELECT * FROM utilisateur WHERE email = ?");
        $stmt->execute([$data['email']]);
        $user = $stmt->fetch();
        
        if (!$user || !password_verify($data['password'], $user['mot_de_passe'])) {
            http_response_code(401);
            echo json_encode(['success' => false, 'message' => 'Invalid credentials']);
            exit;
        }
        
        $token = JwtHelper::encode(['id' => $user['id'], 'email' => $user['email']]);
        
        echo json_encode([
            'success' => true,
            'message' => 'Login successful',
            'data' => [
                'token' => $token,
                'user' => [
                    'id' => $user['id'],
                    'email' => $user['email'],
                    'nom' => $user['nom'],
                    'prenom' => $user['prenom']
                ]
            ]
        ]);
        exit;
    }
    
    // REGISTER
    if ($method === 'POST' && $path === '/api/auth/register') {
        $data = json_decode(file_get_contents('php://input'), true);
        
        if (!isset($data['email']) || !isset($data['password']) || !isset($data['nom']) || !isset($data['prenom'])) {
            echo json_encode(['success' => false, 'message' => 'Missing required fields']);
            exit;
        }
        
        $stmt = $db->prepare("SELECT id FROM utilisateur WHERE email = ?");
        $stmt->execute([$data['email']]);
        if ($stmt->fetch()) {
            echo json_encode(['success' => false, 'message' => 'Email already exists']);
            exit;
        }
        
        $password = password_hash($data['password'], PASSWORD_BCRYPT);
        $stmt = $db->prepare("INSERT INTO utilisateur (nom, prenom, email, mot_de_passe) VALUES (?, ?, ?, ?)");
        $stmt->execute([$data['nom'], $data['prenom'], $data['email'], $password]);
        $userId = $db->lastInsertId();
        
        $token = JwtHelper::encode(['id' => $userId, 'email' => $data['email']]);
        
        echo json_encode([
            'success' => true,
            'message' => 'Registration successful',
            'data' => [
                'token' => $token,
                'user' => ['id' => $userId, 'email' => $data['email'], 'nom' => $data['nom'], 'prenom' => $data['prenom']]
            ]
        ]);
        exit;
    }
    
    // GET TRANSACTIONS (Protected)
    if ($method === 'GET' && $path === '/api/transactions') {
        $token = $_SERVER['HTTP_AUTHORIZATION'] ?? '';
        if (!str_starts_with($token, 'Bearer ')) {
            http_response_code(401);
            echo json_encode(['success' => false, 'message' => 'No token provided']);
            exit;
        }
        
        $token = substr($token, 7);
        $decoded = JwtHelper::decode($token);
        if (!$decoded) {
            http_response_code(401);
            echo json_encode(['success' => false, 'message' => 'Invalid token']);
            exit;
        }
        
        $stmt = $db->prepare("
            SELECT t.*, c.nom as categorie_nom, c.icone as categorie_icon, c.couleur as categorie_couleur
            FROM transactions t
            LEFT JOIN categorie c ON t.categorie_id = c.id
            WHERE t.utilisateur_id = ?
            ORDER BY t.date DESC
        ");
        $stmt->execute([$decoded->id]);
        $transactions = $stmt->fetchAll();
        
        echo json_encode(['success' => true, 'data' => $transactions]);
        exit;
    }
    
    // CREATE TRANSACTION (Protected)
    if ($method === 'POST' && $path === '/api/transactions') {
        $token = $_SERVER['HTTP_AUTHORIZATION'] ?? '';
        if (!str_starts_with($token, 'Bearer ')) {
            http_response_code(401);
            echo json_encode(['success' => false, 'message' => 'No token provided']);
            exit;
        }
        
        $token = substr($token, 7);
        $decoded = JwtHelper::decode($token);
        if (!$decoded) {
            http_response_code(401);
            echo json_encode(['success' => false, 'message' => 'Invalid token']);
            exit;
        }
        
        $data = json_decode(file_get_contents('php://input'), true);
        
        if (!isset($data['montant']) || !isset($data['type']) || !isset($data['categorie_id'])) {
            echo json_encode(['success' => false, 'message' => 'Missing required fields']);
            exit;
        }
        
        $stmt = $db->prepare("INSERT INTO transactions (utilisateur_id, montant, type, categorie_id, description, date) VALUES (?, ?, ?, ?, ?, ?)");
        $stmt->execute([
            $decoded->id,
            $data['montant'],
            $data['type'],
            $data['categorie_id'],
            $data['description'] ?? null,
            $data['date'] ?? date('Y-m-d')
        ]);
        
        $id = $db->lastInsertId();
        
        // Log to historique
        $stmt2 = $db->prepare("INSERT INTO historique (utilisateur_id, action, entite_type, entite_id, details, date) VALUES (?, 'create', 'transaction', ?, ?, NOW())");
        $stmt2->execute([$decoded->id, $id, json_encode($data)]);
        
        echo json_encode(['success' => true, 'message' => 'Transaction created', 'data' => ['id' => $id]]);
        exit;
    }
    
    // GET CATEGORIES
    if ($method === 'GET' && $path === '/api/categories') {
        $token = $_SERVER['HTTP_AUTHORIZATION'] ?? '';
        $userId = 1;
        
        if (str_starts_with($token, 'Bearer ')) {
            $decoded = JwtHelper::decode(substr($token, 7));
            if ($decoded) $userId = $decoded->id;
        }
        
        $stmt = $db->prepare("SELECT * FROM categorie ORDER BY type, nom");
        $stmt->execute();
        $categories = $stmt->fetchAll();
        
        echo json_encode(['success' => true, 'data' => $categories]);
        exit;
    }
    
    // HEALTH CHECK - Test connectivity from phone
    if ($method === 'GET' && $path === '/api/health') {
        echo json_encode([
            'success' => true,
            'message' => 'API is running',
            'timestamp' => date('Y-m-d H:i:s'),
            'server' => 'Drahmi API'
        ]);
        exit;
    }
    
    // REFRESH TOKEN - Keep session alive
    if ($method === 'POST' && $path === '/api/auth/refresh') {
        $token = $_SERVER['HTTP_AUTHORIZATION'] ?? '';
        if (!str_starts_with($token, 'Bearer ')) {
            http_response_code(401);
            echo json_encode(['success' => false, 'message' => 'No token provided']);
            exit;
        }
        
        $token = substr($token, 7);
        $decoded = JwtHelper::decode($token);
        if (!$decoded) {
            http_response_code(401);
            echo json_encode(['success' => false, 'message' => 'Invalid token']);
            exit;
        }
        
        // Generate new token
        $newToken = JwtHelper::encode(['id' => $decoded->id, 'email' => $decoded->email]);
        
        echo json_encode([
            'success' => true,
            'message' => 'Token refreshed',
            'data' => ['token' => $newToken]
        ]);
        exit;
    }
    
    // USER PROFILE - Get user details
    if ($method === 'GET' && $path === '/api/user') {
        $token = $_SERVER['HTTP_AUTHORIZATION'] ?? '';
        if (!str_starts_with($token, 'Bearer ')) {
            http_response_code(401);
            echo json_encode(['success' => false, 'message' => 'No token provided']);
            exit;
        }
        
        $token = substr($token, 7);
        $decoded = JwtHelper::decode($token);
        if (!$decoded) {
            http_response_code(401);
            echo json_encode(['success' => false, 'message' => 'Invalid token']);
            exit;
        }
        
        $stmt = $db->prepare("SELECT id, nom, prenom, email, devise FROM utilisateur WHERE id = ?");
        $stmt->execute([$decoded->id]);
        $user = $stmt->fetch();
        
        echo json_encode(['success' => true, 'data' => $user]);
        exit;
    }
    
    // DELETE TRANSACTION
    if ($method === 'DELETE' && preg_match('#^/api/transactions/(\d+)$#', $path, $matches)) {
        $token = $_SERVER['HTTP_AUTHORIZATION'] ?? '';
        if (!str_starts_with($token, 'Bearer ')) {
            http_response_code(401);
            echo json_encode(['success' => false, 'message' => 'No token provided']);
            exit;
        }
        
        $token = substr($token, 7);
        $decoded = JwtHelper::decode($token);
        if (!$decoded) {
            http_response_code(401);
            echo json_encode(['success' => false, 'message' => 'Invalid token']);
            exit;
        }
        
        $transactionId = $matches[1];
        
        // Verify ownership
        $stmt = $db->prepare("SELECT id FROM transactions WHERE id = ? AND utilisateur_id = ?");
        $stmt->execute([$transactionId, $decoded->id]);
        if (!$stmt->fetch()) {
            http_response_code(403);
            echo json_encode(['success' => false, 'message' => 'Not authorized']);
            exit;
        }
        
        $stmt = $db->prepare("DELETE FROM transactions WHERE id = ?");
        $stmt->execute([$transactionId]);
        
        echo json_encode(['success' => true, 'message' => 'Transaction deleted']);
        exit;
    }
    
    // GOALS - Get all goals
    if ($method === 'GET' && $path === '/api/goals') {
        $token = $_SERVER['HTTP_AUTHORIZATION'] ?? '';
        if (!str_starts_with($token, 'Bearer ')) {
            http_response_code(401);
            echo json_encode(['success' => false, 'message' => 'No token provided']);
            exit;
        }
        
        $token = substr($token, 7);
        $decoded = JwtHelper::decode($token);
        if (!$decoded) {
            http_response_code(401);
            echo json_encode(['success' => false, 'message' => 'Invalid token']);
            exit;
        }
        
        $stmt = $db->prepare("SELECT * FROM objectif WHERE utilisateur_id = ? ORDER BY date_limite ASC");
        $stmt->execute([$decoded->id]);
        $goals = $stmt->fetchAll();
        
        echo json_encode(['success' => true, 'data' => $goals]);
        exit;
    }
    
    // CREATE GOAL
    if ($method === 'POST' && $path === '/api/goals') {
        $token = $_SERVER['HTTP_AUTHORIZATION'] ?? '';
        if (!str_starts_with($token, 'Bearer ')) {
            http_response_code(401);
            echo json_encode(['success' => false, 'message' => 'No token provided']);
            exit;
        }
        
        $token = substr($token, 7);
        $decoded = JwtHelper::decode($token);
        if (!$decoded) {
            http_response_code(401);
            echo json_encode(['success' => false, 'message' => 'Invalid token']);
            exit;
        }
        
        $data = json_decode(file_get_contents('php://input'), true);
        
        if (!isset($data['titre']) || !isset($data['montant_cible']) || !isset($data['date_limite'])) {
            echo json_encode(['success' => false, 'message' => 'Missing required fields']);
            exit;
        }
        
        $stmt = $db->prepare("INSERT INTO objectif (utilisateur_id, titre, montant_cible, date_limite, statut) VALUES (?, ?, ?, ?, 'active')");
        $stmt->execute([
            $decoded->id,
            $data['titre'],
            $data['montant_cible'],
            $data['date_limite']
        ]);
        
        $id = $db->lastInsertId();
        
        echo json_encode(['success' => true, 'message' => 'Goal created', 'data' => ['id' => $id]]);
        exit;
    }

    // --- Profil (aligné client Flutter : GET/PUT /api/user, GET /api/auth/profile) ---
    if ($method === 'GET' && ($path === '/api/user' || $path === '/api/auth/profile')) {
        $token = $_SERVER['HTTP_AUTHORIZATION'] ?? '';
        if (!str_starts_with($token, 'Bearer ')) {
            http_response_code(401);
            echo json_encode(['success' => false, 'message' => 'No token provided']);
            exit;
        }
        $decoded = JwtHelper::decode(substr($token, 7));
        if (!$decoded) {
            http_response_code(401);
            echo json_encode(['success' => false, 'message' => 'Invalid token']);
            exit;
        }
        $stmt = $db->prepare("SELECT id, nom, prenom, email, devise FROM utilisateur WHERE id = ?");
        $stmt->execute([$decoded->id]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        if (!$row) {
            echo json_encode(['success' => false, 'message' => 'User not found']);
            exit;
        }
        echo json_encode(['success' => true, 'data' => $row]);
        exit;
    }

    if ($method === 'PUT' && $path === '/api/user') {
        $token = $_SERVER['HTTP_AUTHORIZATION'] ?? '';
        if (!str_starts_with($token, 'Bearer ')) {
            http_response_code(401);
            echo json_encode(['success' => false, 'message' => 'No token provided']);
            exit;
        }
        $decoded = JwtHelper::decode(substr($token, 7));
        if (!$decoded) {
            http_response_code(401);
            echo json_encode(['success' => false, 'message' => 'Invalid token']);
            exit;
        }
        $data = json_decode(file_get_contents('php://input'), true) ?? [];
        $fields = [];
        $params = [];
        foreach (['prenom', 'nom', 'date_naissance', 'taille_abitable', 'revenu_mensuel', 'repartition', 'devise'] as $col) {
            if (array_key_exists($col, $data)) {
                $fields[] = "$col = ?";
                $params[] = $data[$col];
            }
        }
        if ($fields) {
            $params[] = $decoded->id;
            $sql = 'UPDATE utilisateur SET ' . implode(', ', $fields) . ' WHERE id = ?';
            try {
                $stmt = $db->prepare($sql);
                $stmt->execute($params);
            } catch (Exception $e) {
                $stmt2 = $db->prepare('UPDATE utilisateur SET nom = ?, prenom = ? WHERE id = ?');
                $stmt2->execute([$data['nom'] ?? '', $data['prenom'] ?? '', $decoded->id]);
            }
        }
        echo json_encode(['success' => true, 'message' => 'Profile updated']);
        exit;
    }

    // POST /api/categories — création catégorie utilisateur
    if ($method === 'POST' && $path === '/api/categories') {
        $token = $_SERVER['HTTP_AUTHORIZATION'] ?? '';
        if (!str_starts_with($token, 'Bearer ')) {
            http_response_code(401);
            echo json_encode(['success' => false, 'message' => 'No token provided']);
            exit;
        }
        $decoded = JwtHelper::decode(substr($token, 7));
        if (!$decoded) {
            http_response_code(401);
            echo json_encode(['success' => false, 'message' => 'Invalid token']);
            exit;
        }
        $data = json_decode(file_get_contents('php://input'), true) ?? [];
        if (empty($data['nom'])) {
            echo json_encode(['success' => false, 'message' => 'nom required']);
            exit;
        }
        $stmt = $db->prepare("INSERT INTO categorie (utilisateur_id, nom, icone, type, couleur) VALUES (?, ?, ?, ?, ?)");
        $stmt->execute([
            $decoded->id,
            $data['nom'],
            $data['icone'] ?? 'folder',
            $data['type'] ?? 'depense',
            $data['couleur'] ?? '#6A63D6',
        ]);
        echo json_encode(['success' => true, 'data' => ['id' => (int)$db->lastInsertId()]]);
        exit;
    }

    // Dettes / factures / épargne (schémas type create_sqlite / modules)
    if ($method === 'GET' && $path === '/api/dettes') {
        $token = $_SERVER['HTTP_AUTHORIZATION'] ?? '';
        if (!str_starts_with($token, 'Bearer ')) {
            http_response_code(401);
            echo json_encode(['success' => false, 'message' => 'No token provided']);
            exit;
        }
        $decoded = JwtHelper::decode(substr($token, 7));
        if (!$decoded) {
            http_response_code(401);
            echo json_encode(['success' => false, 'message' => 'Invalid token']);
            exit;
        }
        try {
            $stmt = $db->prepare("SELECT * FROM dette WHERE utilisateur_id = ? ORDER BY id DESC");
            $stmt->execute([$decoded->id]);
            echo json_encode(['success' => true, 'data' => $stmt->fetchAll(PDO::FETCH_ASSOC)]);
        } catch (Exception $e) {
            echo json_encode(['success' => true, 'data' => []]);
        }
        exit;
    }

    if ($method === 'POST' && $path === '/api/dettes') {
        $token = $_SERVER['HTTP_AUTHORIZATION'] ?? '';
        if (!str_starts_with($token, 'Bearer ')) {
            http_response_code(401);
            echo json_encode(['success' => false, 'message' => 'No token provided']);
            exit;
        }
        $decoded = JwtHelper::decode(substr($token, 7));
        if (!$decoded) {
            http_response_code(401);
            echo json_encode(['success' => false, 'message' => 'Invalid token']);
            exit;
        }
        $data = json_decode(file_get_contents('php://input'), true) ?? [];
        $m = (float)($data['montant'] ?? 0);
        $nom = $data['nom'] ?? 'Dette';
        try {
            $stmt = $db->prepare("INSERT INTO dette (utilisateur_id, nom, montant_initial, montant_restant, taux_interet) VALUES (?, ?, ?, ?, 0)");
            $stmt->execute([$decoded->id, $nom, $m, $m]);
            echo json_encode(['success' => true, 'data' => ['id' => (int)$db->lastInsertId()]]);
        } catch (Exception $e) {
            echo json_encode(['success' => false, 'message' => $e->getMessage()]);
        }
        exit;
    }

    if ($method === 'GET' && $path === '/api/factures') {
        $token = $_SERVER['HTTP_AUTHORIZATION'] ?? '';
        if (!str_starts_with($token, 'Bearer ')) {
            http_response_code(401);
            echo json_encode(['success' => false, 'message' => 'No token provided']);
            exit;
        }
        $decoded = JwtHelper::decode(substr($token, 7));
        if (!$decoded) {
            http_response_code(401);
            echo json_encode(['success' => false, 'message' => 'Invalid token']);
            exit;
        }
        try {
            $stmt = $db->prepare("SELECT * FROM facture WHERE utilisateur_id = ? ORDER BY date_echeance ASC");
            $stmt->execute([$decoded->id]);
            echo json_encode(['success' => true, 'data' => $stmt->fetchAll(PDO::FETCH_ASSOC)]);
        } catch (Exception $e) {
            echo json_encode(['success' => true, 'data' => []]);
        }
        exit;
    }

    if ($method === 'POST' && $path === '/api/factures') {
        $token = $_SERVER['HTTP_AUTHORIZATION'] ?? '';
        if (!str_starts_with($token, 'Bearer ')) {
            http_response_code(401);
            echo json_encode(['success' => false, 'message' => 'No token provided']);
            exit;
        }
        $decoded = JwtHelper::decode(substr($token, 7));
        if (!$decoded) {
            http_response_code(401);
            echo json_encode(['success' => false, 'message' => 'Invalid token']);
            exit;
        }
        $data = json_decode(file_get_contents('php://input'), true) ?? [];
        try {
            $stmt = $db->prepare("INSERT INTO facture (utilisateur_id, nom, montant, date_echeance, paye) VALUES (?, ?, ?, ?, 0)");
            $stmt->execute([
                $decoded->id,
                $data['nom'] ?? 'Facture',
                (float)($data['montant'] ?? 0),
                $data['date_echeance'] ?? date('Y-m-d'),
            ]);
            echo json_encode(['success' => true, 'data' => ['id' => (int)$db->lastInsertId()]]);
        } catch (Exception $e) {
            echo json_encode(['success' => false, 'message' => $e->getMessage()]);
        }
        exit;
    }

    if ($method === 'GET' && $path === '/api/savings') {
        $token = $_SERVER['HTTP_AUTHORIZATION'] ?? '';
        if (!str_starts_with($token, 'Bearer ')) {
            http_response_code(401);
            echo json_encode(['success' => false, 'message' => 'No token provided']);
            exit;
        }
        $decoded = JwtHelper::decode(substr($token, 7));
        if (!$decoded) {
            http_response_code(401);
            echo json_encode(['success' => false, 'message' => 'Invalid token']);
            exit;
        }
        try {
            $stmt = $db->prepare("SELECT COALESCE(SUM(montant), 0) as total FROM epargne WHERE utilisateur_id = ?");
            $stmt->execute([$decoded->id]);
            $row = $stmt->fetch(PDO::FETCH_ASSOC);
            $act = (float)($row['total'] ?? 0);
            $obj = 50000.0;
            $prog = $obj > 0 ? min(1.0, $act / $obj) : 0.0;
            echo json_encode([
                'success' => true,
                'data' => [
                    'objectif_montant' => $obj,
                    'montant_actuel' => $act,
                    'progress' => $prog,
                ],
            ]);
        } catch (Exception $e) {
            echo json_encode([
                'success' => true,
                'data' => ['objectif_montant' => 50000, 'montant_actuel' => 30000, 'progress' => 0.6],
            ]);
        }
        exit;
    }
    
    // GET DASHBOARD
    if ($method === 'GET' && $path === '/api/dashboard') {
        $token = $_SERVER['HTTP_AUTHORIZATION'] ?? '';
        if (!str_starts_with($token, 'Bearer ')) {
            http_response_code(401);
            echo json_encode(['success' => false, 'message' => 'No token provided']);
            exit;
        }
        
        $token = substr($token, 7);
        $decoded = JwtHelper::decode($token);
        if (!$decoded) {
            http_response_code(401);
            echo json_encode(['success' => false, 'message' => 'Invalid token']);
            exit;
        }
        
        $userId = $decoded->id;
        
        // Summary
        $stmt = $db->prepare("SELECT 
            COALESCE(SUM(CASE WHEN type = 'revenu' THEN montant ELSE 0 END), 0) as total_revenu,
            COALESCE(SUM(CASE WHEN type = 'depense' THEN montant ELSE 0 END), 0) as total_depense,
            COUNT(*) as nb_transactions
            FROM transactions WHERE utilisateur_id = ?");
        $stmt->execute([$userId]);
        $summary = $stmt->fetch();
        
        // Spending by category
        $stmt2 = $db->prepare("SELECT c.nom, c.couleur, COALESCE(SUM(t.montant), 0) as total
            FROM categorie c
            LEFT JOIN transactions t ON c.id = t.categorie_id AND t.utilisateur_id = ? AND t.type = 'depense'
            GROUP BY c.id
            HAVING total > 0
            ORDER BY total DESC");
        $stmt2->execute([$userId]);
        $byCategory = $stmt2->fetchAll();
        
        // Active goals
        $stmt3 = $db->prepare("SELECT * FROM objectif WHERE utilisateur_id = ? AND statut = 'active' LIMIT 3");
        $stmt3->execute([$userId]);
        $goals = $stmt3->fetchAll();
        
        echo json_encode([
            'success' => true,
            'data' => [
                'summary' => [
                    'total_revenu' => (float) $summary['total_revenu'],
                    'total_depense' => (float) $summary['total_depense'],
                    'balance' => (float) ($summary['total_revenu'] - $summary['total_depense']),
                    'nb_transactions' => (int) $summary['nb_transactions']
                ],
                'spending_by_category' => $byCategory,
                'active_goals' => $goals,
                'monthly_trend' => []
            ]
        ]);
        exit;
    }
    
    // Default - not found
    http_response_code(404);
    echo json_encode(['success' => false, 'message' => 'Endpoint not found']);
    
} catch (Exception $e) {
    error_log($e->getMessage());
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Server error: ' . $e->getMessage()]);
}