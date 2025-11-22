-- FILE: /database.sql
-- SplashHRAI - Complete Database Schema
-- Multi-tenant AI-Powered HR SaaS Platform

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
SET time_zone = "+00:00";

CREATE DATABASE IF NOT EXISTS splashhr_ai DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE splashhr_ai;

-- ============================================================
-- CORE TABLES: Tenants, Users, Plans, Subscriptions
-- ============================================================

CREATE TABLE companies (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(255) NOT NULL,
    domain VARCHAR(100) UNIQUE,
    email VARCHAR(255),
    phone VARCHAR(50),
    address TEXT,
    city VARCHAR(100),
    state VARCHAR(100),
    country VARCHAR(100),
    postal_code VARCHAR(20),
    tax_id VARCHAR(100),
    logo_path VARCHAR(255),
    timezone VARCHAR(50) DEFAULT 'UTC',
    currency VARCHAR(3) DEFAULT 'USD',
    status ENUM('active', 'suspended', 'inactive') DEFAULT 'active',
    created_at DATETIME NOT NULL,
    updated_at DATETIME NOT NULL,
    INDEX idx_status (status),
    INDEX idx_created_at (created_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE users (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    tenant_id INT UNSIGNED NOT NULL,
    employee_id INT UNSIGNED NULL,
    email VARCHAR(255) NOT NULL,
    password VARCHAR(255) NOT NULL,
    first_name VARCHAR(100) NOT NULL,
    last_name VARCHAR(100) NOT NULL,
    role ENUM('platform_admin', 'hr_admin', 'hr_manager', 'hr_specialist', 'employee', 'viewer') NOT NULL,
    is_active TINYINT(1) DEFAULT 1,
    last_login_at DATETIME NULL,
    created_at DATETIME NOT NULL,
    updated_at DATETIME NOT NULL,
    INDEX idx_tenant_id (tenant_id),
    INDEX idx_email (email),
    INDEX idx_role (role),
    UNIQUE KEY unique_tenant_email (tenant_id, email)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE api_keys (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    tenant_id INT UNSIGNED NOT NULL,
    api_key VARCHAR(100) NOT NULL UNIQUE,
    name VARCHAR(100) NOT NULL,
    is_active TINYINT(1) DEFAULT 1,
    last_used_at DATETIME NULL,
    created_at DATETIME NOT NULL,
    updated_at DATETIME NOT NULL,
    INDEX idx_tenant_id (tenant_id),
    INDEX idx_api_key (api_key)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE plans (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    price_monthly DECIMAL(10,2) NOT NULL,
    price_yearly DECIMAL(10,2) NOT NULL,
    max_employees INT NOT NULL DEFAULT -1,
    max_jobs INT NOT NULL DEFAULT -1,
    max_candidates INT NOT NULL DEFAULT -1,
    max_documents INT NOT NULL DEFAULT -1,
    max_ai_tokens_per_month INT NOT NULL DEFAULT -1,
    payroll_enabled TINYINT(1) DEFAULT 1,
    ats_enabled TINYINT(1) DEFAULT 1,
    onboarding_enabled TINYINT(1) DEFAULT 1,
    performance_enabled TINYINT(1) DEFAULT 1,
    is_active TINYINT(1) DEFAULT 1,
    created_at DATETIME NOT NULL,
    updated_at DATETIME NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE tenant_subscriptions (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    tenant_id INT UNSIGNED NOT NULL,
    plan_id INT UNSIGNED NOT NULL,
    status ENUM('trialing', 'active', 'past_due', 'canceled') DEFAULT 'trialing',
    billing_cycle ENUM('monthly', 'yearly') DEFAULT 'monthly',
    start_date DATE NOT NULL,
    end_date DATE NULL,
    renewal_date DATE NOT NULL,
    ai_tokens_used INT DEFAULT 0,
    created_at DATETIME NOT NULL,
    updated_at DATETIME NOT NULL,
    INDEX idx_tenant_id (tenant_id),
    INDEX idx_status (status)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================
-- EMPLOYEE MANAGEMENT
-- ============================================================

CREATE TABLE departments (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    tenant_id INT UNSIGNED NOT NULL,
    name VARCHAR(100) NOT NULL,
    description TEXT,
    head_employee_id INT UNSIGNED NULL,
    parent_department_id INT UNSIGNED NULL,
    created_at DATETIME NOT NULL,
    updated_at DATETIME NOT NULL,
    INDEX idx_tenant_id (tenant_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE positions (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    tenant_id INT UNSIGNED NOT NULL,
    title VARCHAR(100) NOT NULL,
    description TEXT,
    department_id INT UNSIGNED NULL,
    level ENUM('entry', 'junior', 'mid', 'senior', 'lead', 'manager', 'director', 'executive') DEFAULT 'mid',
    created_at DATETIME NOT NULL,
    updated_at DATETIME NOT NULL,
    INDEX idx_tenant_id (tenant_id),
    INDEX idx_department_id (department_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE employees (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    tenant_id INT UNSIGNED NOT NULL,
    employee_code VARCHAR(50),
    first_name VARCHAR(100) NOT NULL,
    last_name VARCHAR(100) NOT NULL,
    middle_name VARCHAR(100),
    email VARCHAR(255) NOT NULL,
    phone VARCHAR(50),
    mobile VARCHAR(50),
    date_of_birth DATE,
    gender ENUM('male', 'female', 'other', 'prefer_not_to_say'),
    nationality VARCHAR(100),
    marital_status ENUM('single', 'married', 'divorced', 'widowed'),
    address TEXT,
    city VARCHAR(100),
    state VARCHAR(100),
    country VARCHAR(100),
    postal_code VARCHAR(20),
    department_id INT UNSIGNED NULL,
    position_id INT UNSIGNED NULL,
    manager_id INT UNSIGNED NULL,
    employment_type ENUM('full_time', 'part_time', 'contract', 'intern') DEFAULT 'full_time',
    employment_status ENUM('active', 'on_leave', 'suspended', 'terminated') DEFAULT 'active',
    hire_date DATE,
    termination_date DATE NULL,
    probation_end_date DATE NULL,
    work_location VARCHAR(255),
    work_email VARCHAR(255),
    salary DECIMAL(12,2),
    salary_currency VARCHAR(3) DEFAULT 'USD',
    payment_frequency ENUM('hourly', 'weekly', 'bi_weekly', 'monthly', 'yearly') DEFAULT 'monthly',
    bank_name VARCHAR(100),
    bank_account_number VARCHAR(100),
    bank_routing_number VARCHAR(100),
    tax_id VARCHAR(100),
    social_security_number VARCHAR(100),
    passport_number VARCHAR(100),
    passport_expiry_date DATE,
    visa_type VARCHAR(100),
    visa_expiry_date DATE,
    emergency_contact_name VARCHAR(255),
    emergency_contact_relationship VARCHAR(100),
    emergency_contact_phone VARCHAR(50),
    profile_photo_path VARCHAR(255),
    created_at DATETIME NOT NULL,
    updated_at DATETIME NOT NULL,
    INDEX idx_tenant_id (tenant_id),
    INDEX idx_employee_code (employee_code),
    INDEX idx_email (email),
    INDEX idx_department_id (department_id),
    INDEX idx_position_id (position_id),
    INDEX idx_manager_id (manager_id),
    INDEX idx_employment_status (employment_status),
    UNIQUE KEY unique_tenant_employee_code (tenant_id, employee_code)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE employee_documents (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    tenant_id INT UNSIGNED NOT NULL,
    employee_id INT UNSIGNED NOT NULL,
    title VARCHAR(255) NOT NULL,
    category ENUM('contract', 'certificate', 'resume', 'id_proof', 'address_proof', 'education', 'other') DEFAULT 'other',
    file_path VARCHAR(255) NOT NULL,
    file_size INT UNSIGNED,
    uploaded_by INT UNSIGNED,
    notes TEXT,
    created_at DATETIME NOT NULL,
    updated_at DATETIME NOT NULL,
    INDEX idx_tenant_id (tenant_id),
    INDEX idx_employee_id (employee_id),
    INDEX idx_category (category)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE employee_notes (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    tenant_id INT UNSIGNED NOT NULL,
    employee_id INT UNSIGNED NOT NULL,
    note TEXT NOT NULL,
    created_by INT UNSIGNED NOT NULL,
    is_private TINYINT(1) DEFAULT 0,
    created_at DATETIME NOT NULL,
    updated_at DATETIME NOT NULL,
    INDEX idx_tenant_id (tenant_id),
    INDEX idx_employee_id (employee_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE employee_terminations (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    tenant_id INT UNSIGNED NOT NULL,
    employee_id INT UNSIGNED NOT NULL,
    termination_date DATE NOT NULL,
    termination_type ENUM('resignation', 'retirement', 'termination', 'layoff', 'end_of_contract') NOT NULL,
    reason TEXT,
    notice_period_days INT,
    final_settlement_amount DECIMAL(12,2),
    exit_interview_completed TINYINT(1) DEFAULT 0,
    processed_by INT UNSIGNED,
    created_at DATETIME NOT NULL,
    updated_at DATETIME NOT NULL,
    INDEX idx_tenant_id (tenant_id),
    INDEX idx_employee_id (employee_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================
-- ATS - APPLICANT TRACKING SYSTEM
-- ============================================================

CREATE TABLE jobs (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    tenant_id INT UNSIGNED NOT NULL,
    title VARCHAR(255) NOT NULL,
    department_id INT UNSIGNED NULL,
    position_id INT UNSIGNED NULL,
    description_html TEXT,
    requirements_html TEXT,
    employment_type ENUM('full_time', 'part_time', 'contract', 'intern') DEFAULT 'full_time',
    location VARCHAR(255),
    remote_allowed TINYINT(1) DEFAULT 0,
    salary_min DECIMAL(12,2),
    salary_max DECIMAL(12,2),
    salary_currency VARCHAR(3) DEFAULT 'USD',
    openings INT DEFAULT 1,
    status ENUM('draft', 'open', 'closed', 'on_hold') DEFAULT 'draft',
    posted_date DATE,
    closing_date DATE,
    hiring_manager_id INT UNSIGNED,
    created_by INT UNSIGNED,
    created_at DATETIME NOT NULL,
    updated_at DATETIME NOT NULL,
    INDEX idx_tenant_id (tenant_id),
    INDEX idx_department_id (department_id),
    INDEX idx_status (status),
    INDEX idx_posted_date (posted_date)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE job_stages (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    tenant_id INT UNSIGNED NOT NULL,
    job_id INT UNSIGNED NOT NULL,
    name VARCHAR(100) NOT NULL,
    position INT DEFAULT 0,
    is_final TINYINT(1) DEFAULT 0,
    created_at DATETIME NOT NULL,
    updated_at DATETIME NOT NULL,
    INDEX idx_tenant_id (tenant_id),
    INDEX idx_job_id (job_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE candidates (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    tenant_id INT UNSIGNED NOT NULL,
    job_id INT UNSIGNED NOT NULL,
    first_name VARCHAR(100) NOT NULL,
    last_name VARCHAR(100) NOT NULL,
    email VARCHAR(255) NOT NULL,
    phone VARCHAR(50),
    location VARCHAR(255),
    resume_path VARCHAR(255),
    cover_letter_path VARCHAR(255),
    linkedin_url VARCHAR(255),
    portfolio_url VARCHAR(255),
    current_company VARCHAR(255),
    current_position VARCHAR(255),
    years_of_experience INT,
    expected_salary DECIMAL(12,2),
    notice_period_days INT,
    status ENUM('applied', 'screening', 'interview', 'assessment', 'offer', 'hired', 'rejected', 'withdrawn') DEFAULT 'applied',
    current_stage_id INT UNSIGNED NULL,
    ai_score INT DEFAULT 0,
    ai_summary TEXT,
    skills TEXT,
    source VARCHAR(100),
    rejected_reason TEXT,
    rejected_at DATETIME NULL,
    hired_as_employee_id INT UNSIGNED NULL,
    applied_at DATETIME NOT NULL,
    created_at DATETIME NOT NULL,
    updated_at DATETIME NOT NULL,
    INDEX idx_tenant_id (tenant_id),
    INDEX idx_job_id (job_id),
    INDEX idx_email (email),
    INDEX idx_status (status),
    INDEX idx_ai_score (ai_score)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE candidate_notes (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    tenant_id INT UNSIGNED NOT NULL,
    candidate_id INT UNSIGNED NOT NULL,
    note TEXT NOT NULL,
    created_by INT UNSIGNED NOT NULL,
    created_at DATETIME NOT NULL,
    updated_at DATETIME NOT NULL,
    INDEX idx_tenant_id (tenant_id),
    INDEX idx_candidate_id (candidate_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE candidate_activities (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    tenant_id INT UNSIGNED NOT NULL,
    candidate_id INT UNSIGNED NOT NULL,
    activity_type ENUM('status_change', 'stage_change', 'interview_scheduled', 'interview_completed', 'note_added', 'email_sent') NOT NULL,
    description TEXT,
    created_by INT UNSIGNED,
    created_at DATETIME NOT NULL,
    INDEX idx_tenant_id (tenant_id),
    INDEX idx_candidate_id (candidate_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE candidate_interviews (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    tenant_id INT UNSIGNED NOT NULL,
    candidate_id INT UNSIGNED NOT NULL,
    job_id INT UNSIGNED NOT NULL,
    interviewer_id INT UNSIGNED NOT NULL,
    interview_type ENUM('phone', 'video', 'in_person', 'technical', 'hr') DEFAULT 'in_person',
    scheduled_at DATETIME NOT NULL,
    duration_minutes INT DEFAULT 60,
    location VARCHAR(255),
    meeting_link VARCHAR(255),
    status ENUM('scheduled', 'completed', 'canceled', 'no_show') DEFAULT 'scheduled',
    rating INT,
    feedback TEXT,
    notes TEXT,
    created_at DATETIME NOT NULL,
    updated_at DATETIME NOT NULL,
    INDEX idx_tenant_id (tenant_id),
    INDEX idx_candidate_id (candidate_id),
    INDEX idx_interviewer_id (interviewer_id),
    INDEX idx_scheduled_at (scheduled_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================
-- ONBOARDING
-- ============================================================

CREATE TABLE onboarding_templates (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    tenant_id INT UNSIGNED NOT NULL,
    name VARCHAR(255) NOT NULL,
    description TEXT,
    department_id INT UNSIGNED NULL,
    position_id INT UNSIGNED NULL,
    is_active TINYINT(1) DEFAULT 1,
    created_at DATETIME NOT NULL,
    updated_at DATETIME NOT NULL,
    INDEX idx_tenant_id (tenant_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE onboarding_steps (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    tenant_id INT UNSIGNED NOT NULL,
    template_id INT UNSIGNED NOT NULL,
    title VARCHAR(255) NOT NULL,
    description TEXT,
    step_type ENUM('task', 'document', 'form', 'training', 'meeting') DEFAULT 'task',
    position INT DEFAULT 0,
    due_days_after_start INT DEFAULT 0,
    is_required TINYINT(1) DEFAULT 1,
    assigned_to_role ENUM('hr', 'manager', 'employee', 'it', 'admin') DEFAULT 'employee',
    created_at DATETIME NOT NULL,
    updated_at DATETIME NOT NULL,
    INDEX idx_tenant_id (tenant_id),
    INDEX idx_template_id (template_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE onboarding_assignments (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    tenant_id INT UNSIGNED NOT NULL,
    employee_id INT UNSIGNED NOT NULL,
    template_id INT UNSIGNED NOT NULL,
    start_date DATE NOT NULL,
    target_completion_date DATE,
    actual_completion_date DATE NULL,
    status ENUM('not_started', 'in_progress', 'completed') DEFAULT 'not_started',
    assigned_by INT UNSIGNED,
    created_at DATETIME NOT NULL,
    updated_at DATETIME NOT NULL,
    INDEX idx_tenant_id (tenant_id),
    INDEX idx_employee_id (employee_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE onboarding_step_logs (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    tenant_id INT UNSIGNED NOT NULL,
    assignment_id INT UNSIGNED NOT NULL,
    step_id INT UNSIGNED NOT NULL,
    status ENUM('pending', 'in_progress', 'completed', 'skipped') DEFAULT 'pending',
    due_date DATE,
    completed_at DATETIME NULL,
    completed_by INT UNSIGNED NULL,
    notes TEXT,
    created_at DATETIME NOT NULL,
    updated_at DATETIME NOT NULL,
    INDEX idx_tenant_id (tenant_id),
    INDEX idx_assignment_id (assignment_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================
-- ATTENDANCE & LEAVE
-- ============================================================

CREATE TABLE attendance_records (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    tenant_id INT UNSIGNED NOT NULL,
    employee_id INT UNSIGNED NOT NULL,
    date DATE NOT NULL,
    check_in_time TIME,
    check_out_time TIME,
    total_hours DECIMAL(5,2) DEFAULT 0,
    status ENUM('present', 'absent', 'half_day', 'late', 'on_leave') DEFAULT 'present',
    is_manual TINYINT(1) DEFAULT 0,
    notes TEXT,
    created_at DATETIME NOT NULL,
    updated_at DATETIME NOT NULL,
    INDEX idx_tenant_id (tenant_id),
    INDEX idx_employee_id (employee_id),
    INDEX idx_date (date),
    UNIQUE KEY unique_employee_date (tenant_id, employee_id, date)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE leave_types (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    tenant_id INT UNSIGNED NOT NULL,
    name VARCHAR(100) NOT NULL,
    description TEXT,
    days_per_year INT DEFAULT 0,
    carry_forward_allowed TINYINT(1) DEFAULT 0,
    max_carry_forward_days INT DEFAULT 0,
    requires_approval TINYINT(1) DEFAULT 1,
    is_paid TINYINT(1) DEFAULT 1,
    is_active TINYINT(1) DEFAULT 1,
    created_at DATETIME NOT NULL,
    updated_at DATETIME NOT NULL,
    INDEX idx_tenant_id (tenant_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE leave_balances (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    tenant_id INT UNSIGNED NOT NULL,
    employee_id INT UNSIGNED NOT NULL,
    leave_type_id INT UNSIGNED NOT NULL,
    year INT NOT NULL,
    total_days DECIMAL(5,2) DEFAULT 0,
    used_days DECIMAL(5,2) DEFAULT 0,
    available_days DECIMAL(5,2) DEFAULT 0,
    carried_forward_days DECIMAL(5,2) DEFAULT 0,
    created_at DATETIME NOT NULL,
    updated_at DATETIME NOT NULL,
    INDEX idx_tenant_id (tenant_id),
    INDEX idx_employee_id (employee_id),
    UNIQUE KEY unique_employee_leave_year (tenant_id, employee_id, leave_type_id, year)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE leave_requests (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    tenant_id INT UNSIGNED NOT NULL,
    employee_id INT UNSIGNED NOT NULL,
    leave_type_id INT UNSIGNED NOT NULL,
    from_date DATE NOT NULL,
    to_date DATE NOT NULL,
    total_days DECIMAL(5,2) NOT NULL,
    reason TEXT,
    status ENUM('pending', 'approved', 'rejected', 'canceled') DEFAULT 'pending',
    approver_id INT UNSIGNED NULL,
    approver_comment TEXT,
    approved_at DATETIME NULL,
    created_at DATETIME NOT NULL,
    updated_at DATETIME NOT NULL,
    INDEX idx_tenant_id (tenant_id),
    INDEX idx_employee_id (employee_id),
    INDEX idx_status (status),
    INDEX idx_from_date (from_date)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================
-- PERFORMANCE MANAGEMENT
-- ============================================================

CREATE TABLE performance_cycles (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    tenant_id INT UNSIGNED NOT NULL,
    name VARCHAR(255) NOT NULL,
    description TEXT,
    start_date DATE NOT NULL,
    end_date DATE NOT NULL,
    self_review_deadline DATE,
    manager_review_deadline DATE,
    status ENUM('draft', 'active', 'completed', 'closed') DEFAULT 'draft',
    created_by INT UNSIGNED,
    created_at DATETIME NOT NULL,
    updated_at DATETIME NOT NULL,
    INDEX idx_tenant_id (tenant_id),
    INDEX idx_status (status)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE performance_templates (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    tenant_id INT UNSIGNED NOT NULL,
    name VARCHAR(255) NOT NULL,
    description TEXT,
    template_json TEXT,
    is_active TINYINT(1) DEFAULT 1,
    created_at DATETIME NOT NULL,
    updated_at DATETIME NOT NULL,
    INDEX idx_tenant_id (tenant_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE performance_reviews (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    tenant_id INT UNSIGNED NOT NULL,
    cycle_id INT UNSIGNED NOT NULL,
    employee_id INT UNSIGNED NOT NULL,
    manager_id INT UNSIGNED NOT NULL,
    template_id INT UNSIGNED NULL,
    self_review TEXT,
    self_rating INT,
    self_submitted_at DATETIME NULL,
    manager_review TEXT,
    manager_rating INT,
    manager_submitted_at DATETIME NULL,
    overall_rating INT,
    strengths TEXT,
    areas_for_improvement TEXT,
    goals_for_next_period TEXT,
    ai_summary TEXT,
    status ENUM('pending', 'self_review_complete', 'manager_review_complete', 'completed') DEFAULT 'pending',
    created_at DATETIME NOT NULL,
    updated_at DATETIME NOT NULL,
    INDEX idx_tenant_id (tenant_id),
    INDEX idx_cycle_id (cycle_id),
    INDEX idx_employee_id (employee_id),
    INDEX idx_manager_id (manager_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE performance_feedback (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    tenant_id INT UNSIGNED NOT NULL,
    review_id INT UNSIGNED NOT NULL,
    from_user_id INT UNSIGNED NOT NULL,
    feedback_type ENUM('peer', 'manager', 'self', 'subordinate') NOT NULL,
    feedback TEXT,
    rating INT,
    submitted_at DATETIME NOT NULL,
    created_at DATETIME NOT NULL,
    updated_at DATETIME NOT NULL,
    INDEX idx_tenant_id (tenant_id),
    INDEX idx_review_id (review_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE performance_goals (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    tenant_id INT UNSIGNED NOT NULL,
    employee_id INT UNSIGNED NOT NULL,
    cycle_id INT UNSIGNED NULL,
    title VARCHAR(255) NOT NULL,
    description TEXT,
    target_date DATE,
    status ENUM('not_started', 'in_progress', 'completed', 'canceled') DEFAULT 'not_started',
    progress_percentage INT DEFAULT 0,
    created_by INT UNSIGNED,
    created_at DATETIME NOT NULL,
    updated_at DATETIME NOT NULL,
    INDEX idx_tenant_id (tenant_id),
    INDEX idx_employee_id (employee_id),
    INDEX idx_cycle_id (cycle_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================
-- PAYROLL
-- ============================================================

CREATE TABLE payroll_periods (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    tenant_id INT UNSIGNED NOT NULL,
    name VARCHAR(255) NOT NULL,
    period_type ENUM('weekly', 'bi_weekly', 'semi_monthly', 'monthly') DEFAULT 'monthly',
    start_date DATE NOT NULL,
    end_date DATE NOT NULL,
    payment_date DATE NOT NULL,
    status ENUM('draft', 'processing', 'approved', 'paid', 'closed') DEFAULT 'draft',
    total_gross DECIMAL(15,2) DEFAULT 0,
    total_deductions DECIMAL(15,2) DEFAULT 0,
    total_net DECIMAL(15,2) DEFAULT 0,
    processed_by INT UNSIGNED NULL,
    approved_by INT UNSIGNED NULL,
    created_at DATETIME NOT NULL,
    updated_at DATETIME NOT NULL,
    INDEX idx_tenant_id (tenant_id),
    INDEX idx_status (status),
    INDEX idx_start_date (start_date)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE payroll_components (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    tenant_id INT UNSIGNED NOT NULL,
    name VARCHAR(100) NOT NULL,
    component_type ENUM('earning', 'deduction', 'tax', 'bonus', 'overtime') NOT NULL,
    calculation_type ENUM('fixed', 'percentage', 'hourly') DEFAULT 'fixed',
    is_taxable TINYINT(1) DEFAULT 1,
    is_active TINYINT(1) DEFAULT 1,
    created_at DATETIME NOT NULL,
    updated_at DATETIME NOT NULL,
    INDEX idx_tenant_id (tenant_id),
    INDEX idx_component_type (component_type)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE payroll_employees (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    tenant_id INT UNSIGNED NOT NULL,
    employee_id INT UNSIGNED NOT NULL,
    basic_salary DECIMAL(12,2) NOT NULL,
    payment_method ENUM('bank_transfer', 'check', 'cash') DEFAULT 'bank_transfer',
    is_active TINYINT(1) DEFAULT 1,
    created_at DATETIME NOT NULL,
    updated_at DATETIME NOT NULL,
    INDEX idx_tenant_id (tenant_id),
    INDEX idx_employee_id (employee_id),
    UNIQUE KEY unique_tenant_employee (tenant_id, employee_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE payroll_runs (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    tenant_id INT UNSIGNED NOT NULL,
    period_id INT UNSIGNED NOT NULL,
    employee_id INT UNSIGNED NOT NULL,
    basic_salary DECIMAL(12,2) NOT NULL,
    total_earnings DECIMAL(12,2) DEFAULT 0,
    total_deductions DECIMAL(12,2) DEFAULT 0,
    gross_salary DECIMAL(12,2) NOT NULL,
    net_salary DECIMAL(12,2) NOT NULL,
    overtime_hours DECIMAL(5,2) DEFAULT 0,
    overtime_amount DECIMAL(10,2) DEFAULT 0,
    bonus_amount DECIMAL(10,2) DEFAULT 0,
    deduction_amount DECIMAL(10,2) DEFAULT 0,
    tax_amount DECIMAL(10,2) DEFAULT 0,
    days_worked DECIMAL(5,2),
    days_absent DECIMAL(5,2),
    status ENUM('draft', 'approved', 'paid') DEFAULT 'draft',
    payment_reference VARCHAR(100),
    paid_at DATETIME NULL,
    notes TEXT,
    created_at DATETIME NOT NULL,
    updated_at DATETIME NOT NULL,
    INDEX idx_tenant_id (tenant_id),
    INDEX idx_period_id (period_id),
    INDEX idx_employee_id (employee_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE payroll_slips (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    tenant_id INT UNSIGNED NOT NULL,
    payroll_run_id INT UNSIGNED NOT NULL,
    employee_id INT UNSIGNED NOT NULL,
    period_id INT UNSIGNED NOT NULL,
    slip_path VARCHAR(255),
    generated_at DATETIME,
    emailed_at DATETIME NULL,
    created_at DATETIME NOT NULL,
    updated_at DATETIME NOT NULL,
    INDEX idx_tenant_id (tenant_id),
    INDEX idx_employee_id (employee_id),
    INDEX idx_period_id (period_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================
-- DOCUMENTS & POLICIES
-- ============================================================

CREATE TABLE documents (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    tenant_id INT UNSIGNED NOT NULL,
    employee_id INT UNSIGNED NULL,
    title VARCHAR(255) NOT NULL,
    category ENUM('policy', 'handbook', 'contract', 'form', 'template', 'other') DEFAULT 'other',
    file_path VARCHAR(255) NOT NULL,
    file_size INT UNSIGNED,
    description TEXT,
    is_public TINYINT(1) DEFAULT 0,
    uploaded_by INT UNSIGNED,
    created_at DATETIME NOT NULL,
    updated_at DATETIME NOT NULL,
    INDEX idx_tenant_id (tenant_id),
    INDEX idx_employee_id (employee_id),
    INDEX idx_category (category)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE policy_documents (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    tenant_id INT UNSIGNED NOT NULL,
    title VARCHAR(255) NOT NULL,
    content_html TEXT,
    category VARCHAR(100),
    version VARCHAR(50),
    effective_date DATE,
    requires_acknowledgment TINYINT(1) DEFAULT 0,
    is_active TINYINT(1) DEFAULT 1,
    created_by INT UNSIGNED,
    created_at DATETIME NOT NULL,
    updated_at DATETIME NOT NULL,
    INDEX idx_tenant_id (tenant_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE policy_acknowledgments (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    tenant_id INT UNSIGNED NOT NULL,
    policy_id INT UNSIGNED NOT NULL,
    employee_id INT UNSIGNED NOT NULL,
    acknowledged_at DATETIME NOT NULL,
    created_at DATETIME NOT NULL,
    INDEX idx_tenant_id (tenant_id),
    INDEX idx_policy_id (policy_id),
    INDEX idx_employee_id (employee_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================
-- NOTIFICATIONS
-- ============================================================

CREATE TABLE notifications (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    tenant_id INT UNSIGNED NOT NULL,
    user_id INT UNSIGNED NOT NULL,
    type VARCHAR(100) NOT NULL,
    title VARCHAR(255) NOT NULL,
    message TEXT,
    link VARCHAR(255),
    is_read TINYINT(1) DEFAULT 0,
    read_at DATETIME NULL,
    created_at DATETIME NOT NULL,
    INDEX idx_tenant_id (tenant_id),
    INDEX idx_user_id (user_id),
    INDEX idx_is_read (is_read),
    INDEX idx_created_at (created_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================
-- ACTIVITY LOGS
-- ============================================================

CREATE TABLE activity_logs (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    tenant_id INT UNSIGNED NULL,
    user_id INT UNSIGNED NULL,
    entity_type VARCHAR(100),
    entity_id INT UNSIGNED,
    action VARCHAR(100) NOT NULL,
    description TEXT,
    ip_address VARCHAR(50),
    user_agent VARCHAR(255),
    created_at DATETIME NOT NULL,
    INDEX idx_tenant_id (tenant_id),
    INDEX idx_user_id (user_id),
    INDEX idx_entity_type (entity_type),
    INDEX idx_created_at (created_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================
-- SEED DATA
-- ============================================================

-- Default Plan
INSERT INTO plans (name, price_monthly, price_yearly, max_employees, max_jobs, max_candidates, max_documents, max_ai_tokens_per_month, payroll_enabled, ats_enabled, onboarding_enabled, performance_enabled, is_active, created_at, updated_at) VALUES
('Starter', 49.00, 490.00, 50, 10, 100, 500, 50000, 1, 1, 1, 1, 1, NOW(), NOW()),
('Professional', 99.00, 990.00, 200, 50, 500, 2000, 200000, 1, 1, 1, 1, 1, NOW(), NOW()),
('Enterprise', 199.00, 1990.00, -1, -1, -1, -1, -1, 1, 1, 1, 1, 1, NOW(), NOW());

-- Demo Company
INSERT INTO companies (name, domain, email, phone, address, city, state, country, postal_code, timezone, currency, status, created_at, updated_at) VALUES
('Demo Tech Corp', 'demo.splashhr.ai', 'admin@demo.splashhr.ai', '+1-555-0100', '123 Tech Street', 'San Francisco', 'CA', 'USA', '94102', 'America/Los_Angeles', 'USD', 'active', NOW(), NOW());

-- Demo Subscription
INSERT INTO tenant_subscriptions (tenant_id, plan_id, status, billing_cycle, start_date, renewal_date, ai_tokens_used, created_at, updated_at) VALUES
(1, 2, 'active', 'monthly', CURDATE(), DATE_ADD(CURDATE(), INTERVAL 1 MONTH), 0, NOW(), NOW());

-- Demo Users
INSERT INTO users (tenant_id, email, password, first_name, last_name, role, is_active, created_at, updated_at) VALUES
(1, 'admin@demo.splashhr.ai', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Sarah', 'Admin', 'hr_admin', 1, NOW(), NOW()),
(1, 'manager@demo.splashhr.ai', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'John', 'Manager', 'hr_manager', 1, NOW(), NOW()),
(1, 'employee@demo.splashhr.ai', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Jane', 'Employee', 'employee', 1, NOW(), NOW());

-- Demo API Key
INSERT INTO api_keys (tenant_id, api_key, name, is_active, created_at, updated_at) VALUES
(1, 'sk_demo_1234567890abcdef', 'Demo API Key', 1, NOW(), NOW());

-- Demo Departments
INSERT INTO departments (tenant_id, name, description, created_at, updated_at) VALUES
(1, 'Engineering', 'Software Development Team', NOW(), NOW()),
(1, 'Human Resources', 'HR Team', NOW(), NOW()),
(1, 'Sales', 'Sales Team', NOW(), NOW()),
(1, 'Marketing', 'Marketing Team', NOW(), NOW());

-- Demo Positions
INSERT INTO positions (tenant_id, title, description, department_id, level, created_at, updated_at) VALUES
(1, 'Software Engineer', 'Full-stack developer', 1, 'mid', NOW(), NOW()),
(1, 'Senior Software Engineer', 'Senior full-stack developer', 1, 'senior', NOW(), NOW()),
(1, 'HR Manager', 'Human Resources Manager', 2, 'manager', NOW(), NOW()),
(1, 'Sales Representative', 'Sales Rep', 3, 'mid', NOW(), NOW());

-- Demo Employees
INSERT INTO employees (tenant_id, employee_code, first_name, last_name, email, phone, date_of_birth, gender, department_id, position_id, employment_type, employment_status, hire_date, salary, work_email, created_at, updated_at) VALUES
(1, 'EMP001', 'Alice', 'Johnson', 'alice.johnson@demo.com', '+1-555-0101', '1990-05-15', 'female', 1, 1, 'full_time', 'active', '2023-01-15', 85000.00, 'alice@demo.splashhr.ai', NOW(), NOW()),
(1, 'EMP002', 'Bob', 'Smith', 'bob.smith@demo.com', '+1-555-0102', '1988-08-22', 'male', 1, 2, 'full_time', 'active', '2022-06-01', 120000.00, 'bob@demo.splashhr.ai', NOW(), NOW()),
(1, 'EMP003', 'Carol', 'Williams', 'carol.williams@demo.com', '+1-555-0103', '1992-03-10', 'female', 2, 3, 'full_time', 'active', '2023-03-20', 75000.00, 'carol@demo.splashhr.ai', NOW(), NOW());

-- Demo Jobs
INSERT INTO jobs (tenant_id, title, department_id, position_id, description_html, employment_type, location, remote_allowed, salary_min, salary_max, openings, status, posted_date, hiring_manager_id, created_by, created_at, updated_at) VALUES
(1, 'Senior Full-Stack Developer', 1, 2, '<p>We are seeking an experienced full-stack developer to join our engineering team.</p>', 'full_time', 'San Francisco, CA', 1, 100000.00, 140000.00, 2, 'open', CURDATE(), 1, 1, NOW(), NOW()),
(1, 'HR Coordinator', 2, 3, '<p>Looking for an organized HR professional to support our growing team.</p>', 'full_time', 'San Francisco, CA', 0, 60000.00, 75000.00, 1, 'open', CURDATE(), 1, 1, NOW(), NOW());

-- Demo Job Stages
INSERT INTO job_stages (tenant_id, job_id, name, position, is_final, created_at, updated_at) VALUES
(1, 1, 'Applied', 1, 0, NOW(), NOW()),
(1, 1, 'Phone Screen', 2, 0, NOW(), NOW()),
(1, 1, 'Technical Interview', 3, 0, NOW(), NOW()),
(1, 1, 'Final Interview', 4, 0, NOW(), NOW()),
(1, 1, 'Offer', 5, 1, NOW(), NOW());

-- Demo Candidates
INSERT INTO candidates (tenant_id, job_id, first_name, last_name, email, phone, location, years_of_experience, status, ai_score, ai_summary, skills, applied_at, created_at, updated_at) VALUES
(1, 1, 'David', 'Brown', 'david.brown@email.com', '+1-555-0201', 'San Francisco, CA', 5, 'screening', 85, 'Strong full-stack developer with 5 years experience in PHP, JavaScript, and MySQL. Previous work in SaaS applications.', 'PHP, JavaScript, MySQL, React, Laravel', NOW(), NOW(), NOW()),
(1, 1, 'Emma', 'Davis', 'emma.davis@email.com', '+1-555-0202', 'Oakland, CA', 7, 'interview', 92, 'Highly experienced senior developer with excellent background in building scalable web applications.', 'PHP, Python, JavaScript, PostgreSQL, AWS', NOW(), NOW(), NOW()),
(1, 2, 'Frank', 'Miller', 'frank.miller@email.com', '+1-555-0203', 'San Francisco, CA', 3, 'applied', 78, 'HR professional with experience in recruitment and employee relations.', 'HR Management, Recruitment, Employee Relations', NOW(), NOW(), NOW());

-- Demo Leave Types
INSERT INTO leave_types (tenant_id, name, description, days_per_year, carry_forward_allowed, max_carry_forward_days, requires_approval, is_paid, is_active, created_at, updated_at) VALUES
(1, 'Annual Leave', 'Paid annual vacation leave', 20, 1, 5, 1, 1, 1, NOW(), NOW()),
(1, 'Sick Leave', 'Paid sick leave', 10, 0, 0, 1, 1, 1, NOW(), NOW()),
(1, 'Personal Leave', 'Personal time off', 5, 0, 0, 1, 1, 1, NOW(), NOW());

-- Demo Attendance Records
INSERT INTO attendance_records (tenant_id, employee_id, date, check_in_time, check_out_time, total_hours, status, created_at, updated_at) VALUES
(1, 1, CURDATE(), '09:00:00', '17:30:00', 8.5, 'present', NOW(), NOW()),
(1, 2, CURDATE(), '08:45:00', '17:15:00', 8.5, 'present', NOW(), NOW()),
(1, 3, CURDATE(), '09:00:00', '17:00:00', 8.0, 'present', NOW(), NOW());

-- Demo Onboarding Template
INSERT INTO onboarding_templates (tenant_id, name, description, is_active, created_at, updated_at) VALUES
(1, 'Standard Employee Onboarding', 'Default onboarding template for all new employees', 1, NOW(), NOW());

-- Demo Onboarding Steps
INSERT INTO onboarding_steps (tenant_id, template_id, title, description, step_type, position, due_days_after_start, assigned_to_role, created_at, updated_at) VALUES
(1, 1, 'Complete Personal Information Form', 'Fill out all personal and tax information', 'form', 1, 0, 'employee', NOW(), NOW()),
(1, 1, 'Review Company Handbook', 'Read and acknowledge company policies', 'document', 2, 1, 'employee', NOW(), NOW()),
(1, 1, 'IT Setup', 'Setup computer, email, and access credentials', 'task', 3, 0, 'it', NOW(), NOW()),
(1, 1, 'Manager Introduction Meeting', 'First meeting with direct manager', 'meeting', 4, 1, 'manager', NOW(), NOW()),
(1, 1, 'Complete Security Training', 'Online security awareness training', 'training', 5, 3, 'employee', NOW(), NOW());

-- Demo Performance Cycle
INSERT INTO performance_cycles (tenant_id, name, description, start_date, end_date, self_review_deadline, manager_review_deadline, status, created_by, created_at, updated_at) VALUES
(1, 'Q1 2024 Performance Review', 'First quarter performance review cycle', '2024-01-01', '2024-03-31', '2024-04-10', '2024-04-20', 'active', 1, NOW(), NOW());

-- Demo Payroll Period
INSERT INTO payroll_periods (tenant_id, name, period_type, start_date, end_date, payment_date, status, created_at, updated_at) VALUES
(1, 'January 2024', 'monthly', '2024-01-01', '2024-01-31', '2024-02-05', 'draft', NOW(), NOW());

-- Demo Payroll Components
INSERT INTO payroll_components (tenant_id, name, component_type, calculation_type, is_taxable, is_active, created_at, updated_at) VALUES
(1, 'Basic Salary', 'earning', 'fixed', 1, 1, NOW(), NOW()),
(1, 'Health Insurance', 'deduction', 'fixed', 0, 1, NOW(), NOW()),
(1, 'Federal Tax', 'tax', 'percentage', 0, 1, NOW(), NOW()),
(1, 'Performance Bonus', 'bonus', 'fixed', 1, 1, NOW(), NOW());
