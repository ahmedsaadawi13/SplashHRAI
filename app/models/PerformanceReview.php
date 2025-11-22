<?php
// FILE: /app/models/PerformanceReview.php

class PerformanceReview extends Model {
    protected $table = 'performance_reviews';

    public function getWithDetails($id, $tenantId) {
        $sql = "SELECT pr.*, e.first_name as employee_first_name, e.last_name as employee_last_name,
                       m.first_name as manager_first_name, m.last_name as manager_last_name,
                       pc.name as cycle_name
                FROM {$this->table} pr
                JOIN employees e ON pr.employee_id = e.id
                JOIN users m ON pr.manager_id = m.id
                JOIN performance_cycles pc ON pr.cycle_id = pc.id
                WHERE pr.id = ? AND pr.tenant_id = ?";

        return $this->db->fetch($sql, array($id, $tenantId));
    }

    public function getByCycle($cycleId, $tenantId) {
        $sql = "SELECT pr.*, e.first_name, e.last_name, e.employee_code
                FROM {$this->table} pr
                JOIN employees e ON pr.employee_id = e.id
                WHERE pr.cycle_id = ? AND pr.tenant_id = ?
                ORDER BY pr.created_at DESC";

        return $this->db->fetchAll($sql, array($cycleId, $tenantId));
    }
}
