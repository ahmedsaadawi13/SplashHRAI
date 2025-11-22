<?php
// FILE: /app/models/Payroll.php

class Payroll extends Model {
    protected $table = 'payroll_runs';

    public function getByPeriod($periodId, $tenantId) {
        $sql = "SELECT pr.*, e.first_name, e.last_name, e.employee_code
                FROM {$this->table} pr
                JOIN employees e ON pr.employee_id = e.id
                WHERE pr.period_id = ? AND pr.tenant_id = ?
                ORDER BY e.employee_code";

        return $this->db->fetchAll($sql, array($periodId, $tenantId));
    }

    public function calculatePayroll($periodId, $tenantId) {
        // Get all active employees
        $employees = $this->db->fetchAll(
            "SELECT e.*, pe.basic_salary
             FROM employees e
             JOIN payroll_employees pe ON e.id = pe.employee_id
             WHERE e.tenant_id = ? AND e.employment_status = 'active' AND pe.is_active = 1",
            array($tenantId)
        );

        $period = $this->db->fetch(
            "SELECT * FROM payroll_periods WHERE id = ? AND tenant_id = ?",
            array($periodId, $tenantId)
        );

        foreach ($employees as $employee) {
            // Calculate attendance
            $attendance = $this->db->fetch(
                "SELECT COUNT(*) as days_worked, SUM(total_hours) as total_hours
                 FROM attendance_records
                 WHERE employee_id = ? AND tenant_id = ? AND date BETWEEN ? AND ? AND status = 'present'",
                array($employee['id'], $tenantId, $period['start_date'], $period['end_date'])
            );

            $daysWorked = $attendance['days_worked'] ?? 0;
            $basicSalary = $employee['basic_salary'];
            $grossSalary = $basicSalary;
            $taxAmount = $grossSalary * 0.15; // Simple 15% tax
            $netSalary = $grossSalary - $taxAmount;

            $data = array(
                'tenant_id' => $tenantId,
                'period_id' => $periodId,
                'employee_id' => $employee['id'],
                'basic_salary' => $basicSalary,
                'total_earnings' => 0,
                'total_deductions' => $taxAmount,
                'gross_salary' => $grossSalary,
                'net_salary' => $netSalary,
                'overtime_hours' => 0,
                'overtime_amount' => 0,
                'bonus_amount' => 0,
                'deduction_amount' => 0,
                'tax_amount' => $taxAmount,
                'days_worked' => $daysWorked,
                'days_absent' => 0,
                'status' => 'draft'
            );

            $this->create($data);
        }

        return true;
    }
}
