<?php
// FILE: /app/controllers/LeaveController.php

class LeaveController extends Controller {

    public function __construct() {
        parent::__construct();
        $this->requireLogin();
    }

    public function index() {
        $leaveModel = $this->model('LeaveRequest');

        if (in_array($this->userRole, array('hr_admin', 'hr_manager', 'hr_specialist'))) {
            $requests = $this->db->fetchAll(
                "SELECT lr.*, e.first_name, e.last_name, e.employee_code, lt.name as leave_type_name
                 FROM leave_requests lr
                 JOIN employees e ON lr.employee_id = e.id
                 JOIN leave_types lt ON lr.leave_type_id = lt.id
                 WHERE lr.tenant_id = ?
                 ORDER BY lr.created_at DESC
                 LIMIT 100",
                array($this->tenantId)
            );
        } else {
            // Employee sees own requests
            $employee = $this->db->fetch(
                "SELECT id FROM employees WHERE tenant_id = ? AND work_email = ?",
                array($this->tenantId, $_SESSION['email'] ?? '')
            );

            $requests = $employee ? $this->db->fetchAll(
                "SELECT lr.*, lt.name as leave_type_name
                 FROM leave_requests lr
                 JOIN leave_types lt ON lr.leave_type_id = lt.id
                 WHERE lr.tenant_id = ? AND lr.employee_id = ?
                 ORDER BY lr.created_at DESC",
                array($this->tenantId, $employee['id'])
            ) : array();
        }

        $this->view('leave/index', array('requests' => $requests));
    }

    public function create() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $this->handleCreate();
        } else {
            $leaveTypes = $this->db->fetchAll(
                "SELECT * FROM leave_types WHERE tenant_id = ? AND is_active = 1",
                array($this->tenantId)
            );

            $this->view('leave/create', array(
                'leave_types' => $leaveTypes,
                'csrf_token' => Security::generateCsrfToken()
            ));
        }
    }

    private function handleCreate() {
        $this->verifyCsrf();

        $data = array(
            'tenant_id' => $this->tenantId,
            'employee_id' => $_POST['employee_id'],
            'leave_type_id' => $_POST['leave_type_id'],
            'from_date' => $_POST['from_date'],
            'to_date' => $_POST['to_date'],
            'reason' => Security::sanitize($_POST['reason'] ?? ''),
            'status' => 'pending'
        );

        // Calculate days
        $from = new DateTime($data['from_date']);
        $to = new DateTime($data['to_date']);
        $diff = $to->diff($from);
        $data['total_days'] = $diff->days + 1;

        $leaveModel = $this->model('LeaveRequest');
        $leaveId = $leaveModel->create($data);

        $this->logActivity('leave', $leaveId, 'request', "Requested {$data['total_days']} days leave");

        // Notify HR managers
        $hrUsers = $this->db->fetchAll(
            "SELECT id FROM users WHERE tenant_id = ? AND role IN ('hr_admin', 'hr_manager')",
            array($this->tenantId)
        );

        foreach ($hrUsers as $hr) {
            Notification::create_notification(
                $this->tenantId,
                $hr['id'],
                'leave_request',
                'New Leave Request',
                "New leave request pending approval",
                "/leave/view/{$leaveId}"
            );
        }

        $this->redirect('/leave?success=created');
    }

    public function approve($id) {
        $this->requireRole(array('hr_admin', 'hr_manager'));
        $this->verifyCsrf();

        $comment = Security::sanitize($_POST['comment'] ?? '');

        $leaveModel = $this->model('LeaveRequest');
        $leaveModel->approve($id, $this->tenantId, $this->userId, $comment);

        $this->logActivity('leave', $id, 'approve', "Approved leave request");

        $this->redirect('/leave?success=approved');
    }

    public function reject($id) {
        $this->requireRole(array('hr_admin', 'hr_manager'));
        $this->verifyCsrf();

        $comment = Security::sanitize($_POST['comment'] ?? '');

        $leaveModel = $this->model('LeaveRequest');
        $leaveModel->reject($id, $this->tenantId, $this->userId, $comment);

        $this->logActivity('leave', $id, 'reject', "Rejected leave request");

        $this->redirect('/leave?success=rejected');
    }
}
