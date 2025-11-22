<?php
// FILE: /app/controllers/PayrollController.php

class PayrollController extends Controller {

    public function __construct() {
        parent::__construct();
        $this->requireLogin();
        $this->requireRole(array('hr_admin', 'hr_manager'));
    }

    public function index() {
        $periods = $this->db->fetchAll(
            "SELECT * FROM payroll_periods WHERE tenant_id = ? ORDER BY start_date DESC LIMIT 12",
            array($this->tenantId)
        );

        $this->view('payroll/index', array('periods' => $periods));
    }

    public function period($id) {
        $period = $this->db->fetch(
            "SELECT * FROM payroll_periods WHERE id = ? AND tenant_id = ?",
            array($id, $this->tenantId)
        );

        if (!$period) {
            $this->redirect('/payroll?error=not_found');
        }

        $payrollModel = $this->model('Payroll');
        $runs = $payrollModel->getByPeriod($id, $this->tenantId);

        $this->view('payroll/period', array(
            'period' => $period,
            'runs' => $runs
        ));
    }

    public function createPeriod() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $this->verifyCsrf();

            $data = array(
                'tenant_id' => $this->tenantId,
                'name' => Security::sanitize($_POST['name']),
                'period_type' => $_POST['period_type'] ?? 'monthly',
                'start_date' => $_POST['start_date'],
                'end_date' => $_POST['end_date'],
                'payment_date' => $_POST['payment_date'],
                'status' => 'draft'
            );

            $sql = "INSERT INTO payroll_periods (tenant_id, name, period_type, start_date, end_date, payment_date, status, created_at, updated_at)
                    VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)";

            $this->db->query($sql, array(
                $data['tenant_id'], $data['name'], $data['period_type'], $data['start_date'],
                $data['end_date'], $data['payment_date'], $data['status'],
                date('Y-m-d H:i:s'), date('Y-m-d H:i:s')
            ));

            $periodId = $this->db->lastInsertId();

            $this->logActivity('payroll', $periodId, 'create', "Created payroll period: {$data['name']}");

            $this->redirect('/payroll/period/' . $periodId);
        }
    }

    public function calculate($periodId) {
        $this->verifyCsrf();

        $payrollModel = $this->model('Payroll');
        $payrollModel->calculatePayroll($periodId, $this->tenantId);

        // Update period totals
        $totals = $this->db->fetch(
            "SELECT SUM(gross_salary) as total_gross, SUM(total_deductions) as total_deductions, SUM(net_salary) as total_net
             FROM payroll_runs WHERE period_id = ? AND tenant_id = ?",
            array($periodId, $this->tenantId)
        );

        $this->db->query(
            "UPDATE payroll_periods SET total_gross = ?, total_deductions = ?, total_net = ?, status = 'processing'
             WHERE id = ? AND tenant_id = ?",
            array($totals['total_gross'], $totals['total_deductions'], $totals['total_net'], $periodId, $this->tenantId)
        );

        $this->logActivity('payroll', $periodId, 'calculate', "Calculated payroll");

        $this->redirect('/payroll/period/' . $periodId . '?success=calculated');
    }

    public function approve($periodId) {
        $this->requireRole(array('hr_admin'));
        $this->verifyCsrf();

        $this->db->query(
            "UPDATE payroll_periods SET status = 'approved', approved_by = ? WHERE id = ? AND tenant_id = ?",
            array($this->userId, $periodId, $this->tenantId)
        );

        $this->db->query(
            "UPDATE payroll_runs SET status = 'approved' WHERE period_id = ? AND tenant_id = ?",
            array($periodId, $this->tenantId)
        );

        $this->logActivity('payroll', $periodId, 'approve', "Approved payroll period");

        $this->redirect('/payroll/period/' . $periodId . '?success=approved');
    }

    public function export($periodId) {
        $period = $this->db->fetch(
            "SELECT * FROM payroll_periods WHERE id = ? AND tenant_id = ?",
            array($periodId, $this->tenantId)
        );

        $runs = $this->db->fetchAll(
            "SELECT pr.*, e.employee_code, e.first_name, e.last_name, e.email
             FROM payroll_runs pr
             JOIN employees e ON pr.employee_id = e.id
             WHERE pr.period_id = ? AND pr.tenant_id = ?
             ORDER BY e.employee_code",
            array($periodId, $this->tenantId)
        );

        header('Content-Type: text/csv');
        header('Content-Disposition: attachment; filename="payroll_' . $period['name'] . '.csv"');

        $output = fopen('php://output', 'w');
        fputcsv($output, array('Employee Code', 'Name', 'Email', 'Basic Salary', 'Gross', 'Deductions', 'Net Salary'));

        foreach ($runs as $run) {
            fputcsv($output, array(
                $run['employee_code'],
                $run['first_name'] . ' ' . $run['last_name'],
                $run['email'],
                $run['basic_salary'],
                $run['gross_salary'],
                $run['total_deductions'],
                $run['net_salary']
            ));
        }

        fclose($output);
        exit;
    }
}
