<?php
// FILE: /app/controllers/EmployeeController.php

class EmployeeController extends Controller {

    public function __construct() {
        parent::__construct();
        $this->requireLogin();
    }

    public function index() {
        $this->requireRole(array('hr_admin', 'hr_manager', 'hr_specialist'));

        $employeeModel = $this->model('Employee');
        $departmentModel = $this->model('Department');

        $filters = array(
            'search' => $_GET['search'] ?? '',
            'department_id' => $_GET['department_id'] ?? '',
            'employment_status' => $_GET['employment_status'] ?? ''
        );

        $employees = $employeeModel->search($this->tenantId, $filters, 50, 0);
        $departments = $departmentModel->findAll($this->tenantId);

        $this->view('employees/index', array(
            'employees' => $employees,
            'departments' => $departments,
            'filters' => $filters
        ));
    }

    public function view($id) {
        $employeeModel = $this->model('Employee');
        $employee = $employeeModel->getWithDetails($id, $this->tenantId);

        if (!$employee) {
            $this->redirect('/employees?error=not_found');
        }

        $documents = $this->db->fetchAll(
            "SELECT * FROM employee_documents WHERE employee_id = ? AND tenant_id = ? ORDER BY created_at DESC",
            array($id, $this->tenantId)
        );

        $notes = $this->db->fetchAll(
            "SELECT en.*, u.first_name, u.last_name
             FROM employee_notes en
             JOIN users u ON en.created_by = u.id
             WHERE en.employee_id = ? AND en.tenant_id = ?
             ORDER BY en.created_at DESC",
            array($id, $this->tenantId)
        );

        $this->view('employees/view', array(
            'employee' => $employee,
            'documents' => $documents,
            'notes' => $notes
        ));
    }

    public function create() {
        $this->requireRole(array('hr_admin', 'hr_manager'));

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $this->handleCreate();
        } else {
            $departmentModel = $this->model('Department');
            $positionModel = $this->model('Position');

            $this->view('employees/create', array(
                'departments' => $departmentModel->findAll($this->tenantId),
                'positions' => $positionModel->findAll($this->tenantId),
                'csrf_token' => Security::generateCsrfToken()
            ));
        }
    }

    private function handleCreate() {
        $this->verifyCsrf();

        // Check quota
        $employeeModel = $this->model('Employee');
        $currentCount = $employeeModel->count($this->tenantId);
        if (!$this->checkQuota('employees', $currentCount)) {
            $this->redirect('/employees?error=quota_exceeded');
            return;
        }

        $data = array(
            'tenant_id' => $this->tenantId,
            'first_name' => Security::sanitize($_POST['first_name'] ?? ''),
            'last_name' => Security::sanitize($_POST['last_name'] ?? ''),
            'email' => Security::sanitize($_POST['email'] ?? ''),
            'phone' => Security::sanitize($_POST['phone'] ?? ''),
            'date_of_birth' => $_POST['date_of_birth'] ?? null,
            'gender' => $_POST['gender'] ?? null,
            'department_id' => $_POST['department_id'] ?? null,
            'position_id' => $_POST['position_id'] ?? null,
            'employment_type' => $_POST['employment_type'] ?? 'full_time',
            'employment_status' => 'active',
            'hire_date' => $_POST['hire_date'] ?? date('Y-m-d'),
            'salary' => $_POST['salary'] ?? 0,
            'work_email' => Security::sanitize($_POST['work_email'] ?? '')
        );

        $validator = new Validator($data);
        $validator->required(array('first_name', 'last_name', 'email'))
                  ->email('email')
                  ->unique('email', 'employees', $this->tenantId);

        if ($validator->fails()) {
            $this->redirect('/employees/create?error=' . urlencode($validator->firstError()));
            return;
        }

        // Generate employee code
        $data['employee_code'] = $employeeModel->generateEmployeeCode($this->tenantId);

        $employeeId = $employeeModel->create($data);

        $this->logActivity('employee', $employeeId, 'create', "Created employee: {$data['first_name']} {$data['last_name']}");

        $this->redirect('/employees/view/' . $employeeId . '?success=created');
    }

    public function edit($id) {
        $this->requireRole(array('hr_admin', 'hr_manager'));

        $employeeModel = $this->model('Employee');
        $employee = $employeeModel->find($id, $this->tenantId);

        if (!$employee) {
            $this->redirect('/employees?error=not_found');
        }

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $this->handleEdit($id);
        } else {
            $departmentModel = $this->model('Department');
            $positionModel = $this->model('Position');

            $this->view('employees/edit', array(
                'employee' => $employee,
                'departments' => $departmentModel->findAll($this->tenantId),
                'positions' => $positionModel->findAll($this->tenantId),
                'csrf_token' => Security::generateCsrfToken()
            ));
        }
    }

    private function handleEdit($id) {
        $this->verifyCsrf();

        $data = array(
            'first_name' => Security::sanitize($_POST['first_name'] ?? ''),
            'last_name' => Security::sanitize($_POST['last_name'] ?? ''),
            'email' => Security::sanitize($_POST['email'] ?? ''),
            'phone' => Security::sanitize($_POST['phone'] ?? ''),
            'department_id' => $_POST['department_id'] ?? null,
            'position_id' => $_POST['position_id'] ?? null,
            'employment_status' => $_POST['employment_status'] ?? 'active',
            'salary' => $_POST['salary'] ?? 0
        );

        $employeeModel = $this->model('Employee');
        $employeeModel->update($id, $data, $this->tenantId);

        $this->logActivity('employee', $id, 'update', "Updated employee information");

        $this->redirect('/employees/view/' . $id . '?success=updated');
    }

    public function delete($id) {
        $this->requireRole(array('hr_admin'));
        $this->verifyCsrf();

        $employeeModel = $this->model('Employee');
        $employeeModel->delete($id, $this->tenantId);

        $this->logActivity('employee', $id, 'delete', "Deleted employee");

        $this->redirect('/employees?success=deleted');
    }
}
