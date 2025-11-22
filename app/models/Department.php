<?php
// FILE: /app/models/Department.php

class Department extends Model {
    protected $table = 'departments';

    public function getEmployeeCount($departmentId, $tenantId) {
        $result = $this->db->fetch(
            "SELECT COUNT(*) as count FROM employees WHERE department_id = ? AND tenant_id = ?",
            array($departmentId, $tenantId)
        );
        return (int)$result['count'];
    }
}
