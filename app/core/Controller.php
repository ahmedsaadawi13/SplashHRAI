<?php
// FILE: /app/core/Controller.php

class Controller {
    protected $db;
    protected $tenantId;
    protected $userId;
    protected $userRole;

    public function __construct() {
        $this->db = Database::getInstance();

        // Load session data
        if (isset($_SESSION['user_id'])) {
            $this->userId = $_SESSION['user_id'];
            $this->tenantId = $_SESSION['tenant_id'] ?? null;
            $this->userRole = $_SESSION['role'] ?? null;
        }
    }

    protected function model($model) {
        $modelPath = APP_PATH . '/models/' . $model . '.php';
        if (file_exists($modelPath)) {
            require_once $modelPath;
            return new $model();
        }
        throw new Exception("Model {$model} not found");
    }

    protected function view($view, $data = array()) {
        extract($data);
        $viewPath = APP_PATH . '/views/' . $view . '.php';
        if (file_exists($viewPath)) {
            require_once $viewPath;
        } else {
            throw new Exception("View {$view} not found");
        }
    }

    protected function redirect($url) {
        header('Location: ' . APP_URL . '/' . ltrim($url, '/'));
        exit;
    }

    protected function json($data, $statusCode = 200) {
        http_response_code($statusCode);
        header('Content-Type: application/json');
        echo json_encode($data);
        exit;
    }

    protected function requireLogin() {
        if (!$this->userId) {
            $this->redirect('/auth/login');
        }
    }

    protected function requireRole($roles) {
        $this->requireLogin();
        if (!is_array($roles)) {
            $roles = array($roles);
        }
        if (!in_array($this->userRole, $roles)) {
            $this->redirect('/dashboard?error=unauthorized');
        }
    }

    protected function verifyCsrf() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $token = $_POST[CSRF_TOKEN_NAME] ?? '';
            if (!Security::verifyCsrfToken($token)) {
                die('CSRF token validation failed');
            }
        }
    }

    protected function checkQuota($quotaType, $currentCount) {
        $subscription = $this->db->fetch(
            "SELECT ts.*, p.* FROM tenant_subscriptions ts
             JOIN plans p ON ts.plan_id = p.id
             WHERE ts.tenant_id = ? AND ts.status = 'active'",
            array($this->tenantId)
        );

        if (!$subscription) {
            return false;
        }

        $quotaMap = array(
            'employees' => 'max_employees',
            'jobs' => 'max_jobs',
            'candidates' => 'max_candidates',
            'documents' => 'max_documents'
        );

        if (isset($quotaMap[$quotaType])) {
            $maxAllowed = $subscription[$quotaMap[$quotaType]];
            if ($maxAllowed !== -1 && $currentCount >= $maxAllowed) {
                return false;
            }
        }

        return true;
    }

    protected function logActivity($entityType, $entityId, $action, $description) {
        ActivityLog::log(
            $this->tenantId,
            $this->userId,
            $entityType,
            $entityId,
            $action,
            $description
        );
    }
}
