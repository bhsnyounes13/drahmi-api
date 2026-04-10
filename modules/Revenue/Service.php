<?php
require_once __DIR__ . '/Model.php';

class RevenueService {
    private $model;

    public function __construct() {
        $this->model = new RevenueModel();
    }

    public function getAll($userId) {
        $revenus = $this->model->getAll($userId);
        return ['success' => true, 'data' => $revenus];
    }

    public function getById($id, $userId) {
        $revenu = $this->model->getById($id, $userId);
        if (!$revenu) {
            return ['success' => false, 'message' => 'Revenue not found'];
        }
        return ['success' => true, 'data' => $revenu];
    }

    public function create($data, $userId) {
        if (!isset($data['montant'])) {
            return ['success' => false, 'message' => 'Amount is required'];
        }
        $id = $this->model->create($data, $userId);
        return ['success' => true, 'message' => 'Revenue created', 'data' => ['id' => $id]];
    }

    public function update($id, $data, $userId) {
        $result = $this->model->update($id, $data, $userId);
        return $result ? ['success' => true, 'message' => 'Revenue updated'] : ['success' => false, 'message' => 'Update failed'];
    }

    public function delete($id, $userId) {
        $result = $this->model->delete($id, $userId);
        return $result ? ['success' => true, 'message' => 'Revenue deleted'] : ['success' => false, 'message' => 'Delete failed'];
    }
}