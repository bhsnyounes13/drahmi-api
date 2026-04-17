<?php
/**
 * Point d’entrée Vercel : API JSON de démonstration (sans base MySQL).
 * Pour une API complète avec persistance, utiliser `api.php` + MySQL en local.
 */
header('Content-Type: application/json; charset=utf-8');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS');
header('Access-Control-Allow-Headers: Authorization, Content-Type');

if (($_SERVER['REQUEST_METHOD'] ?? '') === 'OPTIONS') {
    http_response_code(204);
    exit;
}

$path = parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH) ?: '/';
$method = $_SERVER['REQUEST_METHOD'] ?? 'GET';
$raw = file_get_contents('php://input') ?: '{}';
$input = json_decode($raw, true);
if (!is_array($input)) {
    $input = [];
}

function json_out($data, $code = 200) {
    http_response_code($code);
    echo json_encode($data, JSON_UNESCAPED_UNICODE);
    exit;
}

$demoToken = 'demo_stub_token';
$demoUser = ['id' => 1, 'email' => 'demo@test.com', 'nom' => 'Demo', 'prenom' => 'User'];

$auth = $_SERVER['HTTP_AUTHORIZATION'] ?? '';
$hasBearer = str_starts_with($auth, 'Bearer ');

$key = $method . ' ' . $path;

/* ---------- Auth (démo) ---------- */
if ($key === 'POST /api/auth/login') {
    $email = $input['email'] ?? '';
    $pass = $input['password'] ?? '';
    if ($email && $pass) {
        json_out([
            'success' => true,
            'message' => 'OK',
            'data' => [
                'token' => $demoToken,
                'user' => $demoUser,
            ],
        ]);
    }
    json_out(['success' => false, 'message' => 'Identifiants invalides'], 401);
}

if ($key === 'POST /api/auth/register') {
    json_out([
        'success' => true,
        'message' => 'Inscription',
        'data' => [
            'token' => $demoToken,
            'user' => array_merge($demoUser, [
                'nom' => $input['nom'] ?? 'Nouveau',
                'prenom' => $input['prenom'] ?? 'Compte',
                'email' => $input['email'] ?? 'user@example.com',
            ]),
        ],
    ], 201);
}

if ($key === 'GET /api/user' || $key === 'GET /api/auth/profile') {
    if (!$hasBearer) {
        json_out(['success' => false, 'message' => 'Non authentifié'], 401);
    }
    json_out([
        'success' => true,
        'data' => array_merge($demoUser, [
            'date_naissance' => '1995-01-15',
            'taille_abitable' => '3',
            'revenu_mensuel' => '120000',
            'repartition' => 'moi',
        ]),
    ]);
}

if ($key === 'PUT /api/user' || $key === 'PUT /api/auth/profile') {
    if (!$hasBearer) {
        json_out(['success' => false, 'message' => 'Non authentifié'], 401);
    }
    json_out(['success' => true, 'message' => 'Profil mis à jour', 'data' => $input]);
}

/* ---------- Dashboard ---------- */
if ($key === 'GET /api/dashboard') {
    if (!$hasBearer) {
        json_out(['success' => false, 'message' => 'Non authentifié'], 401);
    }
    json_out([
        'success' => true,
        'data' => [
            'summary' => [
                'total_revenu' => 145000,
                'total_depense' => 25000,
                'balance' => 120000,
                'nb_transactions' => 8,
            ],
            'spending_by_category' => [
                ['nom' => 'Alimentation', 'couleur' => '#FF9000', 'total' => 7500],
                ['nom' => 'Transport', 'couleur' => '#D13B3B', 'total' => 6000],
                ['nom' => 'Shopping', 'couleur' => '#6C4497', 'total' => 5000],
                ['nom' => 'Sport', 'couleur' => '#24355F', 'total' => 2000],
                ['nom' => 'Etudes', 'couleur' => '#BB3D3D', 'total' => 3000],
                ['nom' => 'Loisirs', 'couleur' => '#D88818', 'total' => 1500],
            ],
            'active_goals' => [
                [
                    'id' => 1,
                    'titre' => 'Voiture',
                    'montant_cible' => 5000000,
                    'montant_actuel' => 2250000,
                    'date_limite' => '2032-06-10',
                    'statut' => 'active',
                ],
            ],
            'monthly_trend' => [],
        ],
    ]);
}

/* ---------- Transactions ---------- */
if ($key === 'GET /api/transactions') {
    if (!$hasBearer) {
        json_out(['success' => false, 'message' => 'Non authentifié'], 401);
    }
    $today = date('Y-m-d');
    $yesterday = date('Y-m-d', strtotime('-1 day'));
    json_out([
        'success' => true,
        'data' => [
            [
                'id' => 101,
                'description' => 'Carburant',
                'montant' => 4500,
                'type' => 'depense',
                'date' => $today . 'T14:30:00',
                'categorie_id' => 2,
                'categorie_nom' => 'Transport',
                'categorie_couleur' => '#D13B3B',
            ],
            [
                'id' => 102,
                'description' => 'Restaurant',
                'montant' => 1200,
                'type' => 'depense',
                'date' => $today . 'T12:45:00',
                'categorie_id' => 1,
                'categorie_nom' => 'Alimentation',
                'categorie_couleur' => '#FF9000',
            ],
            [
                'id' => 103,
                'description' => 'Salaire',
                'montant' => 120000,
                'type' => 'revenu',
                'date' => $yesterday . 'T09:00:00',
                'categorie_nom' => 'Revenu',
                'categorie_couleur' => '#4CAF50',
            ],
        ],
    ]);
}

if ($key === 'POST /api/transactions') {
    if (!$hasBearer) {
        json_out(['success' => false, 'message' => 'Non authentifié'], 401);
    }
    json_out(['success' => true, 'message' => 'Transaction créée', 'data' => ['id' => rand(200, 999)]], 201);
}

/* ---------- Catégories ---------- */
if ($key === 'GET /api/categories') {
    json_out([
        'success' => true,
        'data' => [
            ['id' => 1, 'nom' => 'Alimentation', 'type' => 'depense', 'couleur' => '#FF9000', 'icone' => 'food'],
            ['id' => 2, 'nom' => 'Transport', 'type' => 'depense', 'couleur' => '#D13B3B', 'icone' => 'bus'],
            ['id' => 3, 'nom' => 'Shopping', 'type' => 'depense', 'couleur' => '#6C4497', 'icone' => 'bag'],
            ['id' => 4, 'nom' => 'Revenu', 'type' => 'revenu', 'couleur' => '#4CAF50', 'icone' => 'work'],
        ],
    ]);
}

if ($key === 'POST /api/categories') {
    if (!$hasBearer) {
        json_out(['success' => false, 'message' => 'Non authentifié'], 401);
    }
    json_out(['success' => true, 'message' => 'Catégorie ajoutée', 'data' => ['id' => rand(50, 99)]], 201);
}

/* ---------- Objectifs ---------- */
if ($key === 'GET /api/goals') {
    if (!$hasBearer) {
        json_out(['success' => false, 'message' => 'Non authentifié'], 401);
    }
    json_out([
        'success' => true,
        'data' => [
            [
                'id' => 1,
                'titre' => 'Voiture',
                'montant_cible' => 5000000,
                'montant_actuel' => 2250000,
                'date_limite' => '2032-06-10',
                'statut' => 'active',
            ],
        ],
    ]);
}

if ($key === 'POST /api/goals') {
    if (!$hasBearer) {
        json_out(['success' => false, 'message' => 'Non authentifié'], 401);
    }
    json_out(['success' => true, 'message' => 'Objectif créé', 'data' => ['id' => 2]], 201);
}

/* ---------- Dettes / Factures ---------- */
if ($key === 'GET /api/dettes') {
    if (!$hasBearer) {
        json_out(['success' => false, 'message' => 'Non authentifié'], 401);
    }
    json_out([
        'success' => true,
        'data' => [
            ['id' => 1, 'nom' => 'Ahmed', 'montant_restant' => 5000, 'date_echeance' => '2026-05-08'],
            ['id' => 2, 'nom' => 'Sarah', 'montant_restant' => 3000, 'date_echeance' => '2026-04-23'],
        ],
    ]);
}

if ($key === 'POST /api/dettes') {
    if (!$hasBearer) {
        json_out(['success' => false, 'message' => 'Non authentifié'], 401);
    }
    json_out(['success' => true, 'message' => 'Dette ajoutée', 'data' => ['id' => 3]], 201);
}

if ($key === 'GET /api/factures') {
    if (!$hasBearer) {
        json_out(['success' => false, 'message' => 'Non authentifié'], 401);
    }
    json_out([
        'success' => true,
        'data' => [
            ['id' => 1, 'nom' => 'Internet', 'montant' => 2000, 'date_echeance' => '2026-04-20'],
            ['id' => 2, 'nom' => 'Électricité', 'montant' => 5000, 'date_echeance' => '2026-04-25'],
            ['id' => 3, 'nom' => 'Téléphone', 'montant' => 1500, 'date_echeance' => '2026-04-18'],
        ],
    ]);
}

if ($key === 'POST /api/factures') {
    if (!$hasBearer) {
        json_out(['success' => false, 'message' => 'Non authentifié'], 401);
    }
    json_out(['success' => true, 'message' => 'Facture ajoutée', 'data' => ['id' => 4]], 201);
}

/* ---------- Épargne (synthèse) ---------- */
if ($key === 'GET /api/savings') {
    if (!$hasBearer) {
        json_out(['success' => false, 'message' => 'Non authentifié'], 401);
    }
    json_out([
        'success' => true,
        'data' => [
            'objectif_montant' => 50000,
            'montant_actuel' => 30000,
            'progress' => 0.6,
        ],
    ]);
}

if ($key === 'GET /api/health') {
    json_out(['success' => true, 'message' => 'OK', 'service' => 'drahmi-run-stub']);
}

json_out(['success' => false, 'message' => 'Route introuvable', 'path' => $path, 'method' => $method], 404);
