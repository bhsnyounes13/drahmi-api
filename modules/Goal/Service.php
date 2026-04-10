<?php
require_once __DIR__ . '/Model.php';

class GoalService {
    private $model;

    public function __construct() {
        $this->model = new GoalModel();
    }

    public function getAll($userId) {
        $goals = $this->model->getAll($userId);
        return ['success' => true, 'data' => $goals];
    }

    public function getById($id, $userId) {
        $goal = $this->model->getById($id, $userId);
        if (!$goal) {
            return ['success' => false, 'message' => 'Goal not found'];
        }
        return ['success' => true, 'data' => $goal];
    }

    public function create($data, $userId) {
        if (!isset($data['nom'], $data['montant_cible'])) {
            return ['success' => false, 'message' => 'Name and target amount are required'];
        }
        $id = $this->model->create($data, $userId);
        return ['success' => true, 'message' => 'Goal created', 'data' => ['id' => $id]];
    }

    public function update($id, $data, $userId) {
        $result = $this->model->update($id, $data, $userId);
        return $result ? ['success' => true, 'message' => 'Goal updated'] : ['success' => false, 'message' => 'Update failed'];
    }

    public function delete($id, $userId) {
        $result = $this->model->delete($id, $userId);
        return $result ? ['success' => true, 'message' => 'Goal deleted'] : ['success' => false, 'message' => 'Delete failed'];
    }
}