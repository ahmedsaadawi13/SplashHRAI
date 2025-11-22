<?php
// FILE: /app/controllers/PerformanceController.php

class PerformanceController extends Controller {

    public function __construct() {
        parent::__construct();
        $this->requireLogin();
    }

    public function index() {
        $cycles = $this->db->fetchAll(
            "SELECT * FROM performance_cycles WHERE tenant_id = ? ORDER BY created_at DESC",
            array($this->tenantId)
        );

        $this->view('performance/index', array('cycles' => $cycles));
    }

    public function cycle($id) {
        $cycle = $this->db->fetch(
            "SELECT * FROM performance_cycles WHERE id = ? AND tenant_id = ?",
            array($id, $this->tenantId)
        );

        if (!$cycle) {
            $this->redirect('/performance?error=not_found');
        }

        $reviewModel = $this->model('PerformanceReview');
        $reviews = $reviewModel->getByCycle($id, $this->tenantId);

        $this->view('performance/cycle', array(
            'cycle' => $cycle,
            'reviews' => $reviews
        ));
    }

    public function review($id) {
        $reviewModel = $this->model('PerformanceReview');
        $review = $reviewModel->getWithDetails($id, $this->tenantId);

        if (!$review) {
            $this->redirect('/performance?error=not_found');
        }

        $this->view('performance/review', array('review' => $review));
    }

    public function submitSelfReview($id) {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $this->verifyCsrf();

            $data = array(
                'self_review' => $_POST['self_review'],
                'self_rating' => $_POST['self_rating'],
                'self_submitted_at' => date('Y-m-d H:i:s'),
                'status' => 'self_review_complete'
            );

            $reviewModel = $this->model('PerformanceReview');
            $reviewModel->update($id, $data, $this->tenantId);

            $this->logActivity('performance', $id, 'self_review', "Submitted self review");

            $this->redirect('/performance/review/' . $id . '?success=submitted');
        }
    }

    public function submitManagerReview($id) {
        $this->requireRole(array('hr_admin', 'hr_manager'));

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $this->verifyCsrf();

            $review = $this->db->fetch(
                "SELECT * FROM performance_reviews WHERE id = ? AND tenant_id = ?",
                array($id, $this->tenantId)
            );

            $data = array(
                'manager_review' => $_POST['manager_review'],
                'manager_rating' => $_POST['manager_rating'],
                'strengths' => $_POST['strengths'] ?? '',
                'areas_for_improvement' => $_POST['areas_for_improvement'] ?? '',
                'goals_for_next_period' => $_POST['goals_for_next_period'] ?? '',
                'manager_submitted_at' => date('Y-m-d H:i:s'),
                'overall_rating' => $_POST['overall_rating'] ?? $_POST['manager_rating'],
                'status' => 'manager_review_complete'
            );

            // Generate AI summary
            $reviewData = array(
                'employee_name' => 'Employee',
                'period' => 'Current period',
                'self_review' => $review['self_review'],
                'manager_review' => $data['manager_review'],
                'achievements' => 'N/A'
            );

            $aiSummary = AIHelper::generateReviewSummary($reviewData, $this->tenantId);
            $data['ai_summary'] = $aiSummary;

            $reviewModel = $this->model('PerformanceReview');
            $reviewModel->update($id, $data, $this->tenantId);

            $this->logActivity('performance', $id, 'manager_review', "Submitted manager review");

            $this->redirect('/performance/review/' . $id . '?success=submitted');
        }
    }
}
