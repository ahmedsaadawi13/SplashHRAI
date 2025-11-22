<?php
// FILE: /app/controllers/ApiController.php

class ApiController extends Controller {

    private $apiTenantId;

    public function __construct() {
        parent::__construct();
        $this->authenticate();
        $this->checkRateLimit();
    }

    private function authenticate() {
        $apiKey = $_SERVER['HTTP_X_API_KEY'] ?? '';

        if (empty($apiKey)) {
            $this->json(array('error' => 'API key required'), 401);
        }

        $key = $this->db->fetch(
            "SELECT * FROM api_keys WHERE api_key = ? AND is_active = 1",
            array($apiKey)
        );

        if (!$key) {
            Logger::security("Invalid API key attempt: {$apiKey}");
            $this->json(array('error' => 'Invalid API key'), 401);
        }

        $this->apiTenantId = $key['tenant_id'];

        // Update last used
        $this->db->query(
            "UPDATE api_keys SET last_used_at = ? WHERE id = ?",
            array(date('Y-m-d H:i:s'), $key['id'])
        );
    }

    private function checkRateLimit() {
        $cacheFile = STORAGE_PATH . '/cache/api_rate_' . $this->apiTenantId . '.txt';

        if (file_exists($cacheFile)) {
            $data = json_decode(file_get_contents($cacheFile), true);
            $resetTime = $data['reset_time'] ?? 0;

            if (time() < $resetTime) {
                $count = $data['count'] ?? 0;

                if ($count >= API_RATE_LIMIT) {
                    $this->json(array('error' => 'Rate limit exceeded'), 429);
                }

                $data['count'] = $count + 1;
            } else {
                $data = array('count' => 1, 'reset_time' => time() + API_RATE_WINDOW);
            }
        } else {
            $data = array('count' => 1, 'reset_time' => time() + API_RATE_WINDOW);
        }

        file_put_contents($cacheFile, json_encode($data));
    }

    public function createCandidate() {
        $input = json_decode(file_get_contents('php://input'), true);

        $required = array('job_id', 'first_name', 'last_name', 'email');
        foreach ($required as $field) {
            if (empty($input[$field])) {
                $this->json(array('error' => "Field {$field} is required"), 400);
            }
        }

        $candidateModel = $this->model('Candidate');

        // Check quota
        $currentCount = $candidateModel->count($this->apiTenantId);
        if (!$this->checkQuota('candidates', $currentCount)) {
            $this->json(array('error' => 'Candidate quota exceeded'), 403);
        }

        $data = array(
            'tenant_id' => $this->apiTenantId,
            'job_id' => $input['job_id'],
            'first_name' => Security::sanitize($input['first_name']),
            'last_name' => Security::sanitize($input['last_name']),
            'email' => Security::sanitize($input['email']),
            'phone' => Security::sanitize($input['phone'] ?? ''),
            'status' => 'applied',
            'applied_at' => date('Y-m-d H:i:s')
        );

        $candidateId = $candidateModel->create($data);

        ActivityLog::log($this->apiTenantId, null, 'candidate', $candidateId, 'api_create', "Candidate created via API");

        $this->json(array(
            'success' => true,
            'candidate_id' => $candidateId,
            'message' => 'Candidate created successfully'
        ), 201);
    }

    public function createEmployee() {
        $input = json_decode(file_get_contents('php://input'), true);

        $required = array('first_name', 'last_name', 'email');
        foreach ($required as $field) {
            if (empty($input[$field])) {
                $this->json(array('error' => "Field {$field} is required"), 400);
            }
        }

        $employeeModel = $this->model('Employee');

        // Check quota
        $currentCount = $employeeModel->count($this->apiTenantId);
        if (!$this->checkQuota('employees', $currentCount)) {
            $this->json(array('error' => 'Employee quota exceeded'), 403);
        }

        $data = array(
            'tenant_id' => $this->apiTenantId,
            'first_name' => Security::sanitize($input['first_name']),
            'last_name' => Security::sanitize($input['last_name']),
            'email' => Security::sanitize($input['email']),
            'phone' => Security::sanitize($input['phone'] ?? ''),
            'department_id' => $input['department_id'] ?? null,
            'position_id' => $input['position_id'] ?? null,
            'employment_type' => $input['employment_type'] ?? 'full_time',
            'employment_status' => 'active',
            'hire_date' => $input['hire_date'] ?? date('Y-m-d'),
            'salary' => $input['salary'] ?? 0
        );

        $data['employee_code'] = $employeeModel->generateEmployeeCode($this->apiTenantId);

        $employeeId = $employeeModel->create($data);

        ActivityLog::log($this->apiTenantId, null, 'employee', $employeeId, 'api_create', "Employee created via API");

        $this->json(array(
            'success' => true,
            'employee_id' => $employeeId,
            'employee_code' => $data['employee_code'],
            'message' => 'Employee created successfully'
        ), 201);
    }

    public function recordAttendance() {
        $input = json_decode(file_get_contents('php://input'), true);

        $required = array('employee_id', 'date', 'check_in_time');
        foreach ($required as $field) {
            if (empty($input[$field])) {
                $this->json(array('error' => "Field {$field} is required"), 400);
            }
        }

        $attendanceModel = $this->model('Attendance');
        $attendanceModel->recordAttendance(
            $this->apiTenantId,
            $input['employee_id'],
            $input['date'],
            $input['check_in_time'],
            $input['check_out_time'] ?? null
        );

        ActivityLog::log($this->apiTenantId, null, 'attendance', $input['employee_id'], 'api_record', "Attendance recorded via API");

        $this->json(array(
            'success' => true,
            'message' => 'Attendance recorded successfully'
        ));
    }

    public function getJobs() {
        $status = $_GET['status'] ?? 'open';

        $jobs = $this->db->fetchAll(
            "SELECT j.*, d.name as department_name
             FROM jobs j
             LEFT JOIN departments d ON j.department_id = d.id
             WHERE j.tenant_id = ? AND j.status = ?
             ORDER BY j.posted_date DESC
             LIMIT 50",
            array($this->apiTenantId, $status)
        );

        $this->json(array(
            'success' => true,
            'jobs' => $jobs
        ));
    }

    public function getEmployees() {
        $status = $_GET['status'] ?? 'active';
        $limit = min((int)($_GET['limit'] ?? 50), 100);

        $employees = $this->db->fetchAll(
            "SELECT e.id, e.employee_code, e.first_name, e.last_name, e.email,
                    e.department_id, e.position_id, e.employment_type, e.employment_status,
                    d.name as department_name, p.title as position_title
             FROM employees e
             LEFT JOIN departments d ON e.department_id = d.id
             LEFT JOIN positions p ON e.position_id = p.id
             WHERE e.tenant_id = ? AND e.employment_status = ?
             ORDER BY e.employee_code
             LIMIT ?",
            array($this->apiTenantId, $status, $limit)
        );

        $this->json(array(
            'success' => true,
            'employees' => $employees,
            'count' => count($employees)
        ));
    }

    private function checkQuota($quotaType, $currentCount) {
        $subscription = $this->db->fetch(
            "SELECT ts.*, p.* FROM tenant_subscriptions ts
             JOIN plans p ON ts.plan_id = p.id
             WHERE ts.tenant_id = ? AND ts.status = 'active'",
            array($this->apiTenantId)
        );

        if (!$subscription) {
            return false;
        }

        $quotaMap = array(
            'employees' => 'max_employees',
            'jobs' => 'max_jobs',
            'candidates' => 'max_candidates'
        );

        if (isset($quotaMap[$quotaType])) {
            $maxAllowed = $subscription[$quotaMap[$quotaType]];
            if ($maxAllowed !== -1 && $currentCount >= $maxAllowed) {
                return false;
            }
        }

        return true;
    }
}
