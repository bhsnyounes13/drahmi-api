<?php
require_once __DIR__ . '/Model.php';

class BillService {
    private $model;

    public function __construct() {
        $this->model = new BillModel();
    }

    public function getAll($userId) {
        $bills = $this->model->getAll($userId);
        return ['success' => true, 'data' => $bills];
    }

    public function getUpcoming($userId, $days = 7) {
        $bills = $this->model->getUpcoming($userId, $days);
        return ['success' => true, 'data' => $bills];
    }

    public function create($data, $userId) {
        if (!isset($data['nom'], $data['montant'], $data['date_echeance'])) {
            return ['success' => false, 'message' => 'Name, amount and due date are required'];
        }
        $id = $this->model->create($data, $userId);
        return ['success' => true, 'message' => 'Bill created', 'data' => ['id' => $id]];
    }

    public function update($id, $data, $userId) {
        $result = $this->model->update($id, $data, $userId);
        return $result ? ['success' => true, 'message' => 'Bill updated'] : ['success' => false, 'message' => 'Update failed'];
    }

    public function delete($id, $userId) {
        $result = $this->model->delete($id, $userId);
        return $result ? ['success' => true, 'message' => 'Bill deleted'] : ['success' => false, 'message' => 'Delete failed'];
    }
}