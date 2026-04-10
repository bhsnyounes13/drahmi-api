<?php
require_once __DIR__ . '/Model.php';

class SavingService {
    private $model;

    public function __construct() {
        $this->model = new SavingModel();
    }

    public function getAll($userId) {
        $savings = $this->model->getAll($userId);
        return ['success' => true, 'data' => $savings];
    }

    public function create($data, $userId) {
        if (!isset($data['nom'], $data['montant'])) {
            return ['success' => false, 'message' => 'Name and amount are required'];
        }
        $id = $this->model->create($data, $userId);
        return ['success' => true, 'message' => 'Saving created', 'data' => ['id' => $id]];
    }

    public function update($id, $data, $userId) {
        $result = $this->model->update($id, $data, $userId);
        return $result ? ['success' => true, 'message' => 'Saving updated'] : ['success' => false, 'message' => 'Update failed'];
    }

    public function delete($id, $userId) {
        $result = $this->model->delete($id, $userId);
        return $result ? ['success' => true, 'message' => 'Saving deleted'] : ['success' => false, 'message' => 'Delete failed'];
    }
}