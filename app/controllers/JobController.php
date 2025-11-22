<?php
// FILE: /app/controllers/JobController.php

class JobController extends Controller {

    public function __construct() {
        parent::__construct();
        $this->requireLogin();
        $this->requireRole(array('hr_admin', 'hr_manager', 'hr_specialist'));
    }

    public function index() {
        $jobModel = $this->model('Job');
        $jobs = $this->db->fetchAll(
            "SELECT j.*, d.name as department_name,
                    (SELECT COUNT(*) FROM candidates WHERE job_id = j.id) as candidate_count
             FROM jobs j
             LEFT JOIN departments d ON j.department_id = d.id
             WHERE j.tenant_id = ?
             ORDER BY j.created_at DESC",
            array($this->tenantId)
        );

        $this->view('jobs/index', array('jobs' => $jobs));
    }

    public function view($id) {
        $jobModel = $this->model('Job');
        $job = $jobModel->getWithDetails($id, $this->tenantId);

        if (!$job) {
            $this->redirect('/jobs?error=not_found');
        }

        $candidateModel = $this->model('Candidate');
        $candidates = $candidateModel->getByJob($id, $this->tenantId);

        $stages = $this->db->fetchAll(
            "SELECT * FROM job_stages WHERE job_id = ? AND tenant_id = ? ORDER BY position",
            array($id, $this->tenantId)
        );

        $this->view('jobs/view', array(
            'job' => $job,
            'candidates' => $candidates,
            'stages' => $stages
        ));
    }

    public function create() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $this->handleCreate();
        } else {
            $departmentModel = $this->model('Department');
            $positionModel = $this->model('Position');

            $this->view('jobs/create', array(
                'departments' => $departmentModel->findAll($this->tenantId),
                'positions' => $positionModel->findAll($this->tenantId),
                'csrf_token' => Security::generateCsrfToken()
            ));
        }
    }

    private function handleCreate() {
        $this->verifyCsrf();

        // Check quota
        $jobModel = $this->model('Job');
        $currentCount = $jobModel->count($this->tenantId, array('status' => 'open'));
        if (!$this->checkQuota('jobs', $currentCount)) {
            $this->redirect('/jobs?error=quota_exceeded');
            return;
        }

        $data = array(
            'tenant_id' => $this->tenantId,
            'title' => Security::sanitize($_POST['title'] ?? ''),
            'department_id' => $_POST['department_id'] ?? null,
            'description_html' => $_POST['description_html'] ?? '',
            'employment_type' => $_POST['employment_type'] ?? 'full_time',
            'location' => Security::sanitize($_POST['location'] ?? ''),
            'remote_allowed' => isset($_POST['remote_allowed']) ? 1 : 0,
            'salary_min' => $_POST['salary_min'] ?? null,
            'salary_max' => $_POST['salary_max'] ?? null,
            'openings' => $_POST['openings'] ?? 1,
            'status' => 'draft',
            'created_by' => $this->userId
        );

        $jobId = $jobModel->create($data);

        // Create default stages
        $defaultStages = array('Applied', 'Phone Screen', 'Interview', 'Assessment', 'Offer');
        foreach ($defaultStages as $index => $stageName) {
            $this->db->query(
                "INSERT INTO job_stages (tenant_id, job_id, name, position, is_final, created_at, updated_at)
                 VALUES (?, ?, ?, ?, ?, ?, ?)",
                array($this->tenantId, $jobId, $stageName, $index + 1, ($index === count($defaultStages) - 1 ? 1 : 0),
                      date('Y-m-d H:i:s'), date('Y-m-d H:i:s'))
            );
        }

        $this->logActivity('job', $jobId, 'create', "Created job: {$data['title']}");

        $this->redirect('/jobs/view/' . $jobId . '?success=created');
    }

    public function generateDescription($id) {
        $jobModel = $this->model('Job');
        $job = $jobModel->find($id, $this->tenantId);

        if (!$job) {
            $this->json(array('error' => 'Job not found'), 404);
        }

        $inputs = array(
            'title' => $job['title'],
            'department' => $job['department_id'],
            'employment_type' => $job['employment_type'],
            'location' => $job['location']
        );

        $description = AIHelper::generateJobDescription($inputs, $this->tenantId);

        $this->json(array('description' => $description));
    }
}
