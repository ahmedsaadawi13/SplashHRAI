<?php
// FILE: /app/models/Attendance.php

class Attendance extends Model {
    protected $table = 'attendance_records';

    public function getByEmployee($employeeId, $tenantId, $startDate, $endDate) {
        $sql = "SELECT * FROM {$this->table}
                WHERE employee_id = ? AND tenant_id = ?
                AND date BETWEEN ? AND ?
                ORDER BY date DESC";

        return $this->db->fetchAll($sql, array($employeeId, $tenantId, $startDate, $endDate));
    }

    public function recordAttendance($tenantId, $employeeId, $date, $checkIn, $checkOut = null) {
        $totalHours = 0;
        if ($checkOut) {
            $in = new DateTime($checkIn);
            $out = new DateTime($checkOut);
            $diff = $out->diff($in);
            $totalHours = $diff->h + ($diff->i / 60);
        }

        $data = array(
            'tenant_id' => $tenantId,
            'employee_id' => $employeeId,
            'date' => $date,
            'check_in_time' => $checkIn,
            'check_out_time' => $checkOut,
            'total_hours' => round($totalHours, 2),
            'status' => 'present'
        );

        // Check if record exists
        $existing = $this->db->fetch(
            "SELECT id FROM {$this->table} WHERE employee_id = ? AND tenant_id = ? AND date = ?",
            array($employeeId, $tenantId, $date)
        );

        if ($existing) {
            return $this->update($existing['id'], $data, $tenantId);
        } else {
            return $this->create($data);
        }
    }
}
