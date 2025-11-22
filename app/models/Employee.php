<?php
// FILE: /app/models/Employee.php

class Employee extends Model {
    protected $table = 'employees';

    public function getWithDetails($id, $tenantId) {
        $sql = "SELECT e.*, d.name as department_name, p.title as position_title,
                       m.first_name as manager_first_name, m.last_name as manager_last_name
                FROM {$this->table} e
                LEFT JOIN departments d ON e.department_id = d.id
                LEFT JOIN positions p ON e.position_id = p.id
                LEFT JOIN employees m ON e.manager_id = m.id
                WHERE e.id = ? AND e.tenant_id = ?";

        return $this->db->fetch($sql, array($id, $tenantId));
    }

    public function search($tenantId, $filters = array(), $limit = 50, $offset = 0) {
        $sql = "SELECT e.*, d.name as department_name, p.title as position_title
                FROM {$this->table} e
                LEFT JOIN departments d ON e.department_id = d.id
                LEFT JOIN positions p ON e.position_id = p.id
                WHERE e.tenant_id = ?";

        $params = array($tenantId);

        if (!empty($filters['search'])) {
            $sql .= " AND (e.first_name LIKE ? OR e.last_name LIKE ? OR e.email LIKE ? OR e.employee_code LIKE ?)";
            $searchTerm = '%' . $filters['search'] . '%';
            $params[] = $searchTerm;
            $params[] = $searchTerm;
            $params[] = $searchTerm;
            $params[] = $searchTerm;
        }

        if (!empty($filters['department_id'])) {
            $sql .= " AND e.department_id = ?";
            $params[] = $filters['department_id'];
        }

        if (!empty($filters['position_id'])) {
            $sql .= " AND e.position_id = ?";
            $params[] = $filters['position_id'];
        }

        if (!empty($filters['employment_status'])) {
            $sql .= " AND e.employment_status = ?";
            $params[] = $filters['employment_status'];
        }

        $sql .= " ORDER BY e.created_at DESC LIMIT ? OFFSET ?";
        $params[] = (int)$limit;
        $params[] = (int)$offset;

        return $this->db->fetchAll($sql, $params);
    }

    public function generateEmployeeCode($tenantId) {
        $result = $this->db->fetch(
            "SELECT employee_code FROM {$this->table} WHERE tenant_id = ? ORDER BY id DESC LIMIT 1",
            array($tenantId)
        );

        if ($result && $result['employee_code']) {
            $lastCode = $result['employee_code'];
            $number = (int)substr($lastCode, 3);
            $newNumber = $number + 1;
        } else {
            $newNumber = 1;
        }

        return 'EMP' . str_pad($newNumber, 4, '0', STR_PAD_LEFT);
    }
}
