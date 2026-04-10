<?php
require_once __DIR__ . '/Model.php';

class DebtService {
    private $model;

    public function __construct() {
        $this->model = new DebtModel();
    }

    public function getAll($userId) {
        $debts = $this->model->getAll($userId);
        return ['success' => true, 'data' => $debts];
    }

    public function create($data, $userId) {
        if (!isset($data['nom'], $data['montant'])) {
            return ['success' => false, 'message' => 'Name and amount are required'];
        }
        $id = $this->model->create($data, $userId);
        return ['success' => true, 'message' => 'Debt created', 'data' => ['id' => $id]];
    }

    public function update($id, $data, $userId) {
        $result = $this->model->update($id, $data, $userId);
        return $result ? ['success' => true, 'message' => 'Debt updated'] : ['success' => false, 'message' => 'Update failed'];
    }

    public function delete($id, $userId) {
        $result = $this->model->delete($id, $userId);
        return $result ? ['success' => true, 'message' => 'Debt deleted'] : ['success' => false, 'message' => 'Delete failed'];
    }
}