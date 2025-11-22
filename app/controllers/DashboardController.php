<?php
// FILE: /app/controllers/DashboardController.php

class DashboardController extends Controller {

    public function __construct() {
        parent::__construct();
        $this->requireLogin();
    }

    public function index() {
        $stats = $this->getStats();
        $recentActivities = ActivityLog::getRecent($this->tenantId, 10);
        $pendingLeaves = array();
        $upcomingInterviews = array();

        if (in_array($this->userRole, array('hr_admin', 'hr_manager', 'hr_specialist'))) {
            $leaveModel = $this->model('LeaveRequest');
            $pendingLeaves = $leaveModel->getPendingRequests($this->tenantId, 5);

            $upcomingInterviews = $this->db->fetchAll(
                "SELECT ci.*, c.first_name, c.last_name, j.title as job_title
                 FROM candidate_interviews ci
                 JOIN candidates c ON ci.candidate_id = c.id
                 JOIN jobs j ON ci.job_id = j.id
                 WHERE ci.tenant_id = ? AND ci.status = 'scheduled'
                 AND ci.scheduled_at >= NOW()
                 ORDER BY ci.scheduled_at ASC
                 LIMIT 5",
                array($this->tenantId)
            );
        }

        $this->view('dashboard/index', array(
            'stats' => $stats,
            'recent_activities' => $recentActivities,
            'pending_leaves' => $pendingLeaves,
            'upcoming_interviews' => $upcomingInterviews
        ));
    }

    private function getStats() {
        $stats = array();

        // Employee stats
        $employeeStats = $this->db->fetch(
            "SELECT COUNT(*) as total,
                    SUM(CASE WHEN employment_status = 'active' THEN 1 ELSE 0 END) as active,
                    SUM(CASE WHEN hire_date >= DATE_SUB(CURDATE(), INTERVAL 30 DAY) THEN 1 ELSE 0 END) as new_hires
             FROM employees WHERE tenant_id = ?",
            array($this->tenantId)
        );

        $stats['total_employees'] = $employeeStats['total'];
        $stats['active_employees'] = $employeeStats['active'];
        $stats['new_hires_month'] = $employeeStats['new_hires'];

        // Job stats
        $jobStats = $this->db->fetch(
            "SELECT COUNT(*) as total,
                    SUM(CASE WHEN status = 'open' THEN 1 ELSE 0 END) as open_jobs
             FROM jobs WHERE tenant_id = ?",
            array($this->tenantId)
        );

        $stats['total_jobs'] = $jobStats['total'];
        $stats['open_jobs'] = $jobStats['open_jobs'];

        // Candidate stats
        $candidateStats = $this->db->fetch(
            "SELECT COUNT(*) as total,
                    SUM(CASE WHEN status = 'applied' THEN 1 ELSE 0 END) as new_applications
             FROM candidates WHERE tenant_id = ?",
            array($this->tenantId)
        );

        $stats['total_candidates'] = $candidateStats['total'];
        $stats['new_applications'] = $candidateStats['new_applications'];

        // Leave requests
        $leaveStats = $this->db->fetch(
            "SELECT COUNT(*) as pending_requests
             FROM leave_requests WHERE tenant_id = ? AND status = 'pending'",
            array($this->tenantId)
        );

        $stats['pending_leave_requests'] = $leaveStats['pending_requests'];

        // Attendance today
        $attendanceStats = $this->db->fetch(
            "SELECT COUNT(*) as present_today
             FROM attendance_records
             WHERE tenant_id = ? AND date = CURDATE() AND status = 'present'",
            array($this->tenantId)
        );

        $stats['present_today'] = $attendanceStats['present_today'];

        return $stats;
    }
}
