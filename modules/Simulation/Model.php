<?php
require_once __DIR__ . '/../../core/Database.php';

class SimulationModel {
    private $db;

    public function __construct() {
        $this->db = Database::getInstance()->getConnection();
    }

    public function save($data, $userId) {
        $stmt = $this->db->prepare("INSERT INTO simulation (utilisateur_id, parametres, resultats, date_simulation) VALUES (:user_id, :parametres, :resultats, NOW())");
        $stmt->execute([
            'user_id' => $userId,
            'parametres' => json_encode($data['parametres']),
            'resultats' => json_encode($data['resultats'])
        ]);
        return $this->db->lastInsertId();
    }

    public function getHistory($userId, $limit = 10) {
        $stmt = $this->db->prepare("SELECT * FROM simulation WHERE utilisateur_id = :user_id ORDER BY date_simulation DESC LIMIT :limit");
        $stmt->bindValue(':user_id', $userId, PDO::PARAM_INT);
        $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll();
    }

    public function calculate($params) {
        $revenusMensuel = $params['revenus_mensuel'] ?? 0;
        $depensesMensuel = $params['depenses_mensuel'] ?? 0;
        $epargneActuelle = $params['epargne_actuelle'] ?? 0;
        $tauxRendement = ($params['taux_rendement'] ?? 5) / 100;
        $dureeMois = $params['duree_mois'] ?? 12;

        $epargneMensuelle = $revenusMensuel - $depensesMensuel;
        $totalSans = $epargneActuelle;
        $totalAvec = $epargneActuelle;
        $interets = [];

        for ($i = 1; $i <= $dureeMois; $i++) {
            $totalSans += $epargneMensuelle;
            
            $interet = $totalAvec * ($tauxRendement / 12);
            $totalAvec += $epargneMensuelle + $interet;
            
            $interets[] = [
                'mois' => $i,
                'sans_epargne' => round($totalSans, 2),
                'avec_epargne' => round($totalAvec, 2),
                'interet' => round($interet, 2)
            ];
        }

        return [
            'epargne_mensuelle' => $epargneMensuelle,
            'total_sans_rendement' => round($totalSans, 2),
            'total_avec_rendement' => round($totalAvec, 2),
            'total_interets' => round($totalAvec - $totalSans - ($epargneMensuelle * $dureeMois), 2),
            'projection' => $interets
        ];
    }
}