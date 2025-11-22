<?php
// FILE: /app/controllers/AttendanceController.php

class AttendanceController extends Controller {

    public function __construct() {
        parent::__construct();
        $this->requireLogin();
    }

    public function index() {
        $this->requireRole(array('hr_admin', 'hr_manager', 'hr_specialist'));

        $date = $_GET['date'] ?? date('Y-m-d');

        $records = $this->db->fetchAll(
            "SELECT ar.*, e.first_name, e.last_name, e.employee_code, d.name as department_name
             FROM attendance_records ar
             JOIN employees e ON ar.employee_id = e.id
             LEFT JOIN departments d ON e.department_id = d.id
             WHERE ar.tenant_id = ? AND ar.date = ?
             ORDER BY e.employee_code",
            array($this->tenantId, $date)
        );

        $this->view('attendance/index', array(
            'records' => $records,
            'date' => $date
        ));
    }

    public function record() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $this->verifyCsrf();

            $employeeId = $_POST['employee_id'];
            $date = $_POST['date'] ?? date('Y-m-d');
            $checkIn = $_POST['check_in_time'] ?? null;
            $checkOut = $_POST['check_out_time'] ?? null;

            $attendanceModel = $this->model('Attendance');
            $attendanceModel->recordAttendance($this->tenantId, $employeeId, $date, $checkIn, $checkOut);

            $this->logActivity('attendance', $employeeId, 'record', "Recorded attendance for date: {$date}");

            $this->redirect('/attendance?success=recorded&date=' . $date);
        }
    }

    public function report() {
        $this->requireRole(array('hr_admin', 'hr_manager'));

        $startDate = $_GET['start_date'] ?? date('Y-m-01');
        $endDate = $_GET['end_date'] ?? date('Y-m-t');

        $report = $this->db->fetchAll(
            "SELECT e.id, e.employee_code, e.first_name, e.last_name,
                    COUNT(ar.id) as days_present,
                    SUM(ar.total_hours) as total_hours,
                    SUM(CASE WHEN ar.status = 'late' THEN 1 ELSE 0 END) as late_days,
                    SUM(CASE WHEN ar.status = 'absent' THEN 1 ELSE 0 END) as absent_days
             FROM employees e
             LEFT JOIN attendance_records ar ON e.id = ar.employee_id
                 AND ar.date BETWEEN ? AND ? AND ar.tenant_id = ?
             WHERE e.tenant_id = ? AND e.employment_status = 'active'
             GROUP BY e.id
             ORDER BY e.employee_code",
            array($startDate, $endDate, $this->tenantId, $this->tenantId)
        );

        $this->view('attendance/report', array(
            'report' => $report,
            'start_date' => $startDate,
            'end_date' => $endDate
        ));
    }
}
