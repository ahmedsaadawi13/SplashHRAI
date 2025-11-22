<!-- FILE: /app/views/jobs/create.php -->
<?php require_once APP_PATH . '/views/layouts/header.php'; ?>

<h1 style="margin-bottom: 20px;">Create New Job</h1>

<div class="card">
    <form method="POST" action="<?php echo APP_URL; ?>/jobs/create">
        <input type="hidden" name="<?php echo CSRF_TOKEN_NAME; ?>" value="<?php echo $csrf_token; ?>">

        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 15px;">
            <div class="form-group">
                <label>Job Title *</label>
                <input type="text" name="title" required>
            </div>

            <div class="form-group">
                <label>Department</label>
                <select name="department_id">
                    <option value="">Select...</option>
                    <?php foreach ($departments as $dept): ?>
                        <option value="<?php echo $dept['id']; ?>"><?php echo Security::escape($dept['name']); ?></option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div class="form-group">
                <label>Employment Type</label>
                <select name="employment_type">
                    <option value="full_time">Full Time</option>
                    <option value="part_time">Part Time</option>
                    <option value="contract">Contract</option>
                    <option value="intern">Intern</option>
                </select>
            </div>

            <div class="form-group">
                <label>Location</label>
                <input type="text" name="location" placeholder="e.g., San Francisco, CA">
            </div>

            <div class="form-group">
                <label>Minimum Salary</label>
                <input type="number" name="salary_min" step="1000">
            </div>

            <div class="form-group">
                <label>Maximum Salary</label>
                <input type="number" name="salary_max" step="1000">
            </div>

            <div class="form-group">
                <label>Number of Openings</label>
                <input type="number" name="openings" value="1" min="1">
            </div>

            <div class="form-group">
                <label style="display: flex; align-items: center; gap: 8px;">
                    <input type="checkbox" name="remote_allowed" style="width: auto;">
                    Remote Work Allowed
                </label>
            </div>
        </div>

        <div class="form-group">
            <label>Job Description</label>
            <textarea name="description_html" rows="10" placeholder="Enter job description with requirements, responsibilities, etc."></textarea>
        </div>

        <div style="margin-top: 30px; display: flex; gap: 10px;">
            <button type="submit" class="btn btn-success">Create Job</button>
            <a href="<?php echo APP_URL; ?>/jobs" class="btn btn-secondary">Cancel</a>
        </div>
    </form>
</div>

<?php require_once APP_PATH . '/views/layouts/footer.php'; ?>
