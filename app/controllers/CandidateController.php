<?php
// FILE: /app/controllers/CandidateController.php

class CandidateController extends Controller {

    public function __construct() {
        parent::__construct();
        $this->requireLogin();
        $this->requireRole(array('hr_admin', 'hr_manager', 'hr_specialist'));
    }

    public function index() {
        $candidates = $this->db->fetchAll(
            "SELECT c.*, j.title as job_title
             FROM candidates c
             JOIN jobs j ON c.job_id = j.id
             WHERE c.tenant_id = ?
             ORDER BY c.ai_score DESC, c.created_at DESC
             LIMIT 100",
            array($this->tenantId)
        );

        $this->view('candidates/index', array('candidates' => $candidates));
    }

    public function view($id) {
        $candidateModel = $this->model('Candidate');
        $candidate = $candidateModel->getWithJobDetails($id, $this->tenantId);

        if (!$candidate) {
            $this->redirect('/candidates?error=not_found');
        }

        $notes = $this->db->fetchAll(
            "SELECT cn.*, u.first_name, u.last_name
             FROM candidate_notes cn
             JOIN users u ON cn.created_by = u.id
             WHERE cn.candidate_id = ? AND cn.tenant_id = ?
             ORDER BY cn.created_at DESC",
            array($id, $this->tenantId)
        );

        $interviews = $this->db->fetchAll(
            "SELECT ci.*, u.first_name as interviewer_first_name, u.last_name as interviewer_last_name
             FROM candidate_interviews ci
             LEFT JOIN users u ON ci.interviewer_id = u.id
             WHERE ci.candidate_id = ? AND ci.tenant_id = ?
             ORDER BY ci.scheduled_at DESC",
            array($id, $this->tenantId)
        );

        $this->view('candidates/view', array(
            'candidate' => $candidate,
            'notes' => $notes,
            'interviews' => $interviews
        ));
    }

    public function create() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $this->handleCreate();
        } else {
            $jobModel = $this->model('Job');
            $jobs = $jobModel->getOpenJobs($this->tenantId);

            $this->view('candidates/create', array(
                'jobs' => $jobs,
                'csrf_token' => Security::generateCsrfToken()
            ));
        }
    }

    private function handleCreate() {
        $this->verifyCsrf();

        // Check quota
        $candidateModel = $this->model('Candidate');
        $currentCount = $candidateModel->count($this->tenantId);
        if (!$this->checkQuota('candidates', $currentCount)) {
            $this->redirect('/candidates?error=quota_exceeded');
            return;
        }

        $data = array(
            'tenant_id' => $this->tenantId,
            'job_id' => $_POST['job_id'] ?? 0,
            'first_name' => Security::sanitize($_POST['first_name'] ?? ''),
            'last_name' => Security::sanitize($_POST['last_name'] ?? ''),
            'email' => Security::sanitize($_POST['email'] ?? ''),
            'phone' => Security::sanitize($_POST['phone'] ?? ''),
            'years_of_experience' => $_POST['years_of_experience'] ?? 0,
            'status' => 'applied',
            'applied_at' => date('Y-m-d H:i:s')
        );

        // Handle resume upload
        if (isset($_FILES['resume']) && $_FILES['resume']['error'] === UPLOAD_ERR_OK) {
            $upload = FileUpload::upload($_FILES['resume'], 'resumes');
            if ($upload['success']) {
                $data['resume_path'] = $upload['filepath'];
            }
        }

        $candidateId = $candidateModel->create($data);

        // AI Resume Analysis
        if (isset($data['resume_path'])) {
            $this->analyzeResume($candidateId);
        }

        $this->logActivity('candidate', $candidateId, 'create', "Added candidate: {$data['first_name']} {$data['last_name']}");

        // Create notification
        Notification::create_notification(
            $this->tenantId,
            $this->userId,
            'new_candidate',
            'New Candidate Applied',
            "{$data['first_name']} {$data['last_name']} applied for a position",
            "/candidates/view/{$candidateId}"
        );

        $this->redirect('/candidates/view/' . $candidateId . '?success=created');
    }

    private function analyzeResume($candidateId) {
        $candidate = $this->db->fetch(
            "SELECT c.*, j.description_html
             FROM candidates c
             JOIN jobs j ON c.job_id = j.id
             WHERE c.id = ? AND c.tenant_id = ?",
            array($candidateId, $this->tenantId)
        );

        if ($candidate && $candidate['resume_path']) {
            $resumePath = UPLOAD_PATH . '/' . $candidate['resume_path'];
            if (file_exists($resumePath)) {
                $resumeText = file_get_contents($resumePath);
                if ($resumeText) {
                    $analysis = AIHelper::analyzeResume(
                        $resumeText,
                        strip_tags($candidate['description_html']),
                        $this->tenantId
                    );

                    $this->db->query(
                        "UPDATE candidates SET ai_score = ?, ai_summary = ?, skills = ? WHERE id = ?",
                        array($analysis['score'], $analysis['summary'], $analysis['skills'], $candidateId)
                    );
                }
            }
        }
    }

    public function updateStatus($id) {
        $this->verifyCsrf();

        $status = $_POST['status'] ?? '';
        $reason = Security::sanitize($_POST['reason'] ?? '');

        $candidateModel = $this->model('Candidate');
        $candidateModel->updateStatus($id, $status, $this->tenantId, $reason);

        $this->logActivity('candidate', $id, 'status_change', "Changed status to: {$status}");

        $this->redirect('/candidates/view/' . $id . '?success=status_updated');
    }

    public function scheduleInterview($id) {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $this->verifyCsrf();

            $data = array(
                'tenant_id' => $this->tenantId,
                'candidate_id' => $id,
                'job_id' => $_POST['job_id'],
                'interviewer_id' => $_POST['interviewer_id'],
                'interview_type' => $_POST['interview_type'] ?? 'in_person',
                'scheduled_at' => $_POST['scheduled_at'],
                'duration_minutes' => $_POST['duration_minutes'] ?? 60,
                'location' => Security::sanitize($_POST['location'] ?? ''),
                'status' => 'scheduled'
            );

            $sql = "INSERT INTO candidate_interviews (tenant_id, candidate_id, job_id, interviewer_id, interview_type, scheduled_at, duration_minutes, location, status, created_at, updated_at)
                    VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";

            $this->db->query($sql, array(
                $data['tenant_id'], $data['candidate_id'], $data['job_id'], $data['interviewer_id'],
                $data['interview_type'], $data['scheduled_at'], $data['duration_minutes'],
                $data['location'], $data['status'], date('Y-m-d H:i:s'), date('Y-m-d H:i:s')
            ));

            $this->redirect('/candidates/view/' . $id . '?success=interview_scheduled');
        }
    }
}
