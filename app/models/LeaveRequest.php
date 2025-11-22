<?php
// FILE: /app/models/LeaveRequest.php

class LeaveRequest extends Model {
    protected $table = 'leave_requests';

    public function getWithDetails($id, $tenantId) {
        $sql = "SELECT lr.*, e.first_name, e.last_name, e.employee_code,
                       lt.name as leave_type_name,
                       a.first_name as approver_first_name, a.last_name as approver_last_name
                FROM {$this->table} lr
                JOIN employees e ON lr.employee_id = e.id
                JOIN leave_types lt ON lr.leave_type_id = lt.id
                LEFT JOIN users a ON lr.approver_id = a.id
                WHERE lr.id = ? AND lr.tenant_id = ?";

        return $this->db->fetch($sql, array($id, $tenantId));
    }

    public function getPendingRequests($tenantId, $limit = 50) {
        $sql = "SELECT lr.*, e.first_name, e.last_name, lt.name as leave_type_name
                FROM {$this->table} lr
                JOIN employees e ON lr.employee_id = e.id
                JOIN leave_types lt ON lr.leave_type_id = lt.id
                WHERE lr.tenant_id = ? AND lr.status = 'pending'
                ORDER BY lr.created_at DESC
                LIMIT ?";

        return $this->db->fetchAll($sql, array($tenantId, $limit));
    }

    public function approve($id, $tenantId, $approverId, $comment = null) {
        $data = array(
            'status' => 'approved',
            'approver_id' => $approverId,
            'approver_comment' => $comment,
            'approved_at' => date('Y-m-d H:i:s')
        );

        return $this->update($id, $data, $tenantId);
    }

    public function reject($id, $tenantId, $approverId, $comment) {
        $data = array(
            'status' => 'rejected',
            'approver_id' => $approverId,
            'approver_comment' => $comment,
            'approved_at' => date('Y-m-d H:i:s')
        );

        return $this->update($id, $data, $tenantId);
    }
}
