<?php
require_once __DIR__ . '/Model.php';

class SimulationService {
    private $model;

    public function __construct() {
        $this->model = new SimulationModel();
    }

    public function calculate($data, $userId) {
        if (!isset($data['revenus_mensuel'], $data['depenses_mensuel'])) {
            return ['success' => false, 'message' => 'Monthly income and expenses are required'];
        }

        $result = $this->model->calculate($data);

        if (isset($data['save']) && $data['save']) {
            $this->model->save([
                'parametres' => $data,
                'resultats' => $result
            ], $userId);
        }

        return ['success' => true, 'data' => $result];
    }

    public function getHistory($userId, $limit = 10) {
        $history = $this->model->getHistory($userId, $limit);
        
        foreach ($history as &$h) {
            $h['parametres'] = json_decode($h['parametres'], true);
            $h['resultats'] = json_decode($h['resultats'], true);
        }

        return ['success' => true, 'data' => $history];
    }
}