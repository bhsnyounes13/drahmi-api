<?php
require_once __DIR__ . '/Model.php';
require_once __DIR__ . '/../../core/Database.php';

class TransactionService {
    private $model;

    public function __construct() {
        $this->model = new TransactionModel();
    }

    public function getAll($userId, $limit = 50, $offset = 0) {
        $transactionss = $this->model->getAll($userId, $limit, $offset);
        return ['success' => true, 'data' => $transactionss];
    }

    public function getById($id, $userId) {
        $transactions = $this->model->getById($id, $userId);
        if (!$transactions) {
            return ['success' => false, 'message' => 'Transaction not found'];
        }
        return ['success' => true, 'data' => $transactions];
    }

    public function create($data, $userId) {
        if (!isset($data['montant']) || !isset($data['type']) || !isset($data['categorie_id'])) {
            return ['success' => false, 'message' => 'Missing required fields: montant, type, categorie_id'];
        }

        if (!in_array($data['type'], ['revenu', 'depense'])) {
            return ['success' => false, 'message' => 'Invalid transactions type'];
        }

        if (!is_numeric($data['montant']) || $data['montant'] <= 0) {
            return ['success' => false, 'message' => 'Amount must be a positive number'];
        }

        $transactionsId = $this->model->create($data, $userId);

        $this->logToHistorique($userId, $transactionsId, 'create', $data);
        $this->updateGoalProgress($userId, $data);
        $this->checkAlerts($userId, $data);

        return ['success' => true, 'message' => 'Transaction created', 'data' => ['id' => $transactionsId]];
    }

    public function update($id, $data, $userId) {
        $existing = $this->model->getById($id, $userId);
        if (!$existing) {
            return ['success' => false, 'message' => 'Transaction not found'];
        }

        $result = $this->model->update($id, $data, $userId);
        
        if ($result) {
            $this->logToHistorique($userId, $id, 'update', $data);
        }

        return $result 
            ? ['success' => true, 'message' => 'Transaction updated']
            : ['success' => false, 'message' => 'Update failed'];
    }

    public function delete($id, $userId) {
        $existing = $this->model->getById($id, $userId);
        if (!$existing) {
            return ['success' => false, 'message' => 'Transaction not found'];
        }

        $result = $this->model->delete($id, $userId);
        
        if ($result) {
            $this->logToHistorique($userId, $id, 'delete', $existing);
        }

        return $result 
            ? ['success' => true, 'message' => 'Transaction deleted']
            : ['success' => false, 'message' => 'Delete failed'];
    }

    public function getByDateRange($userId, $startDate, $endDate) {
        $transactionss = $this->model->getByDateRange($userId, $startDate, $endDate);
        return ['success' => true, 'data' => $transactionss];
    }

    public function getSummary($userId, $startDate = null, $endDate = null) {
        $totalRevenu = $this->model->getTotalByType($userId, 'revenu', $startDate, $endDate);
        $totalDepense = $this->model->getTotalByType($userId, 'depense', $startDate, $endDate);
        
        return [
            'success' => true,
            'data' => [
                'total_revenu' => (float) $totalRevenu,
                'total_depense' => (float) $totalDepense,
                'balance' => (float) ($totalRevenu - $totalDepense)
            ]
        ];
    }

    public function getByCategory($userId, $startDate = null, $endDate = null) {
        $data = $this->model->getByCategory($userId, $startDate, $endDate);
        return ['success' => true, 'data' => $data];
    }

    private function logToHistorique($userId, $transactionsId, $action, $data) {
        $db = Database::getInstance()->getConnection();
        $stmt = $db->prepare("
            INSERT INTO historique (utilisateur_id, action, entite_type, entite_id, details, date)
            VALUES (:user_id, :action, 'transactions', :entity_id, :details, NOW())
        ");
        $stmt->execute([
            'user_id' => $userId,
            'action' => $action,
            'entity_id' => $transactionsId,
            'details' => json_encode($data)
        ]);
    }

    private function updateGoalProgress($userId, $data) {
        if ($data['type'] !== 'depense') return;

        $db = Database::getInstance()->getConnection();
        $stmt = $db->prepare("
            SELECT * FROM objectif 
            WHERE utilisateur_id = :user_id AND statut = 'active'
        ");
        $stmt->execute(['user_id' => $userId]);
        $goals = $stmt->fetchAll();

        foreach ($goals as $goal) {
            $newProgress = ($goal['progression'] ?? 0) + $data['montant'];
            $newProgress = min($newProgress, $goal['montant_cible']);
            
            $updateStmt = $db->prepare("UPDATE objectif SET progression = :progression WHERE id = :id");
            $updateStmt->execute(['progression' => $newProgress, 'id' => $goal['id']]);
        }
    }

    private function checkAlerts($userId, $data) {
        if ($data['type'] !== 'depense') return;

        $db = Database::getInstance()->getConnection();
        $stmt = $db->prepare("
            SELECT * FROM alert 
            WHERE utilisateur_id = :user_id AND type = 'budget' AND active = 1
        ");
        $stmt->execute(['user_id' => $userId]);
        $alerts = $stmt->fetchAll();

        foreach ($alerts as $alert) {
            $stmt2 = $db->prepare("
                SELECT COALESCE(SUM(montant), 0) as total FROM transactions 
                WHERE utilisateur_id = :user_id AND type = 'depense' 
                AND date >= DATE_SUB(NOW(), INTERVAL 1 MONTH)
            ");
            $stmt2->execute(['user_id' => $userId]);
            $total = $stmt2->fetch()['total'] ?? 0;

            if ($total > $alert['seuil']) {
                $this->createNotification($userId, 'budget_alert', 'Budget threshold exceeded');
            }
        }
    }

    private function createNotification($userId, $type, $message) {
        $db = Database::getInstance()->getConnection();
        $stmt = $db->prepare("
            INSERT INTO historique (utilisateur_id, action, entite_type, details, date)
            VALUES (:user_id, 'alert', :type, :message, NOW())
        ");
        $stmt->execute([
            'user_id' => $userId,
            'type' => $type,
            'message' => $message
        ]);
    }
}