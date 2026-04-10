<?php
require_once __DIR__ . '/../../core/Database.php';

class DashboardModel {
    private $db;

    public function __construct() {
        $this->db = Database::getInstance()->getConnection();
    }

    public function getSummary($userId, $startDate = null, $endDate = null) {
        $where = "utilisateur_id = :user_id";
        $params = ['user_id' => $userId];

        if ($startDate && $endDate) {
            $where .= " AND date BETWEEN :start_date AND :end_date";
            $params['start_date'] = $startDate;
            $params['end_date'] = $endDate;
        }

        $sql = "SELECT 
            COALESCE(SUM(CASE WHEN type = 'revenu' THEN montant ELSE 0 END), 0) as total_revenu,
            COALESCE(SUM(CASE WHEN type = 'depense' THEN montant ELSE 0 END), 0) as total_depense,
            COUNT(*) as nb_transactionss
            FROM transactions WHERE $where";

        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        $result = $stmt->fetch();

        return [
            'total_revenu' => (float) $result['total_revenu'],
            'total_depense' => (float) $result['total_depense'],
            'balance' => (float) ($result['total_revenu'] - $result['total_depense']),
            'nb_transactionss' => (int) $result['nb_transactionss']
        ];
    }

    public function getSpendingByCategory($userId, $startDate = null, $endDate = null) {
        $sql = "SELECT c.nom, c.couleur, COALESCE(SUM(t.montant), 0) as total
            FROM categorie c
            LEFT JOIN transactions t ON c.id = t.categorie_id AND t.utilisateur_id = :user_id AND t.type = 'depense'";
        
        $params = ['user_id' => $userId];

        if ($startDate && $endDate) {
            $sql .= " AND t.date BETWEEN :start_date AND :end_date";
            $params['start_date'] = $startDate;
            $params['end_date'] = $endDate;
        }

        $sql .= " GROUP BY c.id, c.nom, c.couleur HAVING total > 0 ORDER BY total DESC";

        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll();
    }

    public function getMonthlyTrend($userId, $months = 6) {
        $date = date('Y-m', strtotime("-{$months} months"));
        $stmt = $this->db->prepare("SELECT 
            strftime('%Y-%m', date) as month,
            SUM(CASE WHEN type = 'revenu' THEN montant ELSE 0 END) as revenu,
            SUM(CASE WHEN type = 'depense' THEN montant ELSE 0 END) as depense
            FROM transactions 
            WHERE utilisateur_id = :user_id AND date >= :date
            GROUP BY strftime('%Y-%m', date)
            ORDER BY month ASC");
        $stmt->execute(['user_id' => $userId, 'date' => $date . '-01']);
        return $stmt->fetchAll();
    }

    public function getActiveGoals($userId) {
        $stmt = $this->db->prepare("SELECT * FROM objectif WHERE utilisateur_id = :user_id AND statut = 'active' ORDER BY date_cible ASC LIMIT 3");
        $stmt->execute(['user_id' => $userId]);
        return $stmt->fetchAll();
    }

    public function getUpcomingBills($userId) {
        $today = date('Y-m-d');
        $stmt = $this->db->prepare("SELECT * FROM facture WHERE utilisateur_id = :user_id AND paye = 0 AND date_echeance >= :today ORDER BY date_echeance ASC LIMIT 3");
        $stmt->execute(['user_id' => $userId, 'today' => $today]);
        return $stmt->fetchAll();
    }
}