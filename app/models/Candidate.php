<?php
// FILE: /app/models/Candidate.php

class Candidate extends Model {
    protected $table = 'candidates';

    public function getWithJobDetails($id, $tenantId) {
        $sql = "SELECT c.*, j.title as job_title, j.department_id,
                       d.name as department_name
                FROM {$this->table} c
                JOIN jobs j ON c.job_id = j.id
                LEFT JOIN departments d ON j.department_id = d.id
                WHERE c.id = ? AND c.tenant_id = ?";

        return $this->db->fetch($sql, array($id, $tenantId));
    }

    public function getByJob($jobId, $tenantId, $status = null) {
        $sql = "SELECT * FROM {$this->table} WHERE job_id = ? AND tenant_id = ?";
        $params = array($jobId, $tenantId);

        if ($status) {
            $sql .= " AND status = ?";
            $params[] = $status;
        }

        $sql .= " ORDER BY ai_score DESC, created_at DESC";

        return $this->db->fetchAll($sql, $params);
    }

    public function updateStatus($id, $status, $tenantId, $reason = null) {
        $data = array('status' => $status);

        if ($status === 'rejected') {
            $data['rejected_reason'] = $reason;
            $data['rejected_at'] = date('Y-m-d H:i:s');
        }

        return $this->update($id, $data, $tenantId);
    }
}
