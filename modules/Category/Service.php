<?php
require_once __DIR__ . '/Model.php';

class CategoryService {
    private $model;

    public function __construct() {
        $this->model = new CategoryModel();
    }

    public function getAll($userId) {
        $categories = $this->model->getAll($userId);
        return ['success' => true, 'data' => $categories];
    }

    public function getById($id) {
        $category = $this->model->getById($id);
        if (!$category) {
            return ['success' => false, 'message' => 'Category not found'];
        }
        return ['success' => true, 'data' => $category];
    }

    public function getByType($userId, $type) {
        if (!in_array($type, ['revenu', 'depense'])) {
            return ['success' => false, 'message' => 'Invalid type'];
        }
        
        $categories = $this->model->getByType($userId, $type);
        return ['success' => true, 'data' => $categories];
    }

    public function create($data, $userId) {
        if (!isset($data['nom'], $data['type'])) {
            return ['success' => false, 'message' => 'Missing required fields: nom, type'];
        }

        if (!in_array($data['type'], ['revenu', 'depense'])) {
            return ['success' => false, 'message' => 'Invalid type'];
        }

        $categoryId = $this->model->create($data, $userId);
        return ['success' => true, 'message' => 'Category created', 'data' => ['id' => $categoryId]];
    }

    public function update($id, $data) {
        if (isset($data['type']) && !in_array($data['type'], ['revenu', 'depense'])) {
            return ['success' => false, 'message' => 'Invalid type'];
        }

        $result = $this->model->update($id, $data);
        return $result 
            ? ['success' => true, 'message' => 'Category updated']
            : ['success' => false, 'message' => 'Update failed'];
    }

    public function delete($id) {
        $result = $this->model->delete($id);
        return $result 
            ? ['success' => true, 'message' => 'Category deleted']
            : ['success' => false, 'message' => 'Delete failed'];
    }
}