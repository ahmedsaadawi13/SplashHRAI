<?php
// FILE: /app/models/Job.php

class Job extends Model {
    protected $table = 'jobs';

    public function getWithDetails($id, $tenantId) {
        $sql = "SELECT j.*, d.name as department_name, p.title as position_title,
                       u.first_name as manager_first_name, u.last_name as manager_last_name,
                       (SELECT COUNT(*) FROM candidates WHERE job_id = j.id) as total_candidates
                FROM {$this->table} j
                LEFT JOIN departments d ON j.department_id = d.id
                LEFT JOIN positions p ON j.position_id = p.id
                LEFT JOIN users u ON j.hiring_manager_id = u.id
                WHERE j.id = ? AND j.tenant_id = ?";

        return $this->db->fetch($sql, array($id, $tenantId));
    }

    public function getOpenJobs($tenantId, $limit = 50) {
        return $this->findAll($tenantId, array('status' => 'open'), $limit);
    }

    public function getCandidateCount($jobId, $tenantId) {
        $result = $this->db->fetch(
            "SELECT COUNT(*) as count FROM candidates WHERE job_id = ? AND tenant_id = ?",
            array($jobId, $tenantId)
        );
        return (int)$result['count'];
    }
}
