<?php
// FILE: /app/controllers/AuthController.php

class AuthController extends Controller {

    public function __construct() {
        parent::__construct();
    }

    public function login() {
        if ($this->userId) {
            $this->redirect('/dashboard');
        }

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $this->handleLogin();
        } else {
            $this->view('auth/login', array(
                'csrf_token' => Security::generateCsrfToken()
            ));
        }
    }

    private function handleLogin() {
        $this->verifyCsrf();

        $email = Security::sanitize($_POST['email'] ?? '');
        $password = $_POST['password'] ?? '';

        $validator = new Validator(array('email' => $email, 'password' => $password));
        $validator->required(array('email', 'password'))->email('email');

        if ($validator->fails()) {
            $this->view('auth/login', array(
                'error' => $validator->firstError(),
                'csrf_token' => Security::generateCsrfToken()
            ));
            return;
        }

        // Check login throttle
        if (!Security::checkLoginThrottle($email)) {
            Logger::security("Login throttled for: {$email}");
            $this->view('auth/login', array(
                'error' => 'Too many login attempts. Please try again later.',
                'csrf_token' => Security::generateCsrfToken()
            ));
            return;
        }

        $userModel = $this->model('User');
        $user = $userModel->authenticate($email, $password);

        if ($user) {
            Security::recordLoginAttempt($email, true);

            // Set session
            session_regenerate_id(true);
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['tenant_id'] = $user['tenant_id'];
            $_SESSION['role'] = $user['role'];
            $_SESSION['name'] = $user['first_name'] . ' ' . $user['last_name'];

            ActivityLog::log(
                $user['tenant_id'],
                $user['id'],
                'user',
                $user['id'],
                'login',
                'User logged in'
            );

            $this->redirect('/dashboard');
        } else {
            Security::recordLoginAttempt($email, false);
            Logger::security("Failed login attempt for: {$email}");

            $this->view('auth/login', array(
                'error' => 'Invalid email or password',
                'csrf_token' => Security::generateCsrfToken()
            ));
        }
    }

    public function logout() {
        if ($this->userId) {
            ActivityLog::log(
                $this->tenantId,
                $this->userId,
                'user',
                $this->userId,
                'logout',
                'User logged out'
            );
        }

        session_destroy();
        $this->redirect('/auth/login');
    }

    public function register() {
        // For multi-tenant SaaS, registration creates a new tenant
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $this->handleRegister();
        } else {
            $this->view('auth/register', array(
                'csrf_token' => Security::generateCsrfToken()
            ));
        }
    }

    private function handleRegister() {
        $this->verifyCsrf();

        $data = array(
            'company_name' => Security::sanitize($_POST['company_name'] ?? ''),
            'email' => Security::sanitize($_POST['email'] ?? ''),
            'first_name' => Security::sanitize($_POST['first_name'] ?? ''),
            'last_name' => Security::sanitize($_POST['last_name'] ?? ''),
            'password' => $_POST['password'] ?? ''
        );

        $validator = new Validator($data);
        $validator->required(array('company_name', 'email', 'first_name', 'last_name', 'password'))
                  ->email('email')
                  ->minLength('password', 8);

        if ($validator->fails()) {
            $this->view('auth/register', array(
                'error' => $validator->firstError(),
                'csrf_token' => Security::generateCsrfToken(),
                'data' => $data
            ));
            return;
        }

        try {
            $this->db->beginTransaction();

            // Create company
            $companyData = array(
                'name' => $data['company_name'],
                'email' => $data['email'],
                'status' => 'active'
            );

            $companyId = $this->db->query(
                "INSERT INTO companies (name, email, status, created_at, updated_at)
                 VALUES (?, ?, ?, ?, ?)",
                array($companyData['name'], $companyData['email'], $companyData['status'], date('Y-m-d H:i:s'), date('Y-m-d H:i:s'))
            );
            $companyId = $this->db->lastInsertId();

            // Create subscription (trial plan)
            $planId = 1; // Default starter plan
            $this->db->query(
                "INSERT INTO tenant_subscriptions (tenant_id, plan_id, status, billing_cycle, start_date, renewal_date, created_at, updated_at)
                 VALUES (?, ?, 'trialing', 'monthly', CURDATE(), DATE_ADD(CURDATE(), INTERVAL 14 DAY), ?, ?)",
                array($companyId, $planId, date('Y-m-d H:i:s'), date('Y-m-d H:i:s'))
            );

            // Create admin user
            $userData = array(
                'tenant_id' => $companyId,
                'email' => $data['email'],
                'password' => Security::hashPassword($data['password']),
                'first_name' => $data['first_name'],
                'last_name' => $data['last_name'],
                'role' => 'hr_admin',
                'is_active' => 1
            );

            $userId = $this->db->query(
                "INSERT INTO users (tenant_id, email, password, first_name, last_name, role, is_active, created_at, updated_at)
                 VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)",
                array($userData['tenant_id'], $userData['email'], $userData['password'], $userData['first_name'],
                      $userData['last_name'], $userData['role'], $userData['is_active'], date('Y-m-d H:i:s'), date('Y-m-d H:i:s'))
            );

            $this->db->commit();

            $this->view('auth/register_success', array(
                'email' => $data['email']
            ));

        } catch (Exception $e) {
            $this->db->rollBack();
            Logger::error("Registration failed: " . $e->getMessage());

            $this->view('auth/register', array(
                'error' => 'Registration failed. Please try again.',
                'csrf_token' => Security::generateCsrfToken(),
                'data' => $data
            ));
        }
    }
}
