<!-- FILE: /app/views/candidates/create.php -->
<?php require_once APP_PATH . '/views/layouts/header.php'; ?>

<h1 style="margin-bottom: 20px;">Add New Candidate</h1>

<div class="card">
    <form method="POST" action="<?php echo APP_URL; ?>/candidates/create" enctype="multipart/form-data">
        <input type="hidden" name="<?php echo CSRF_TOKEN_NAME; ?>" value="<?php echo $csrf_token; ?>">

        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 15px;">
            <div class="form-group">
                <label>First Name *</label>
                <input type="text" name="first_name" required>
            </div>

            <div class="form-group">
                <label>Last Name *</label>
                <input type="text" name="last_name" required>
            </div>

            <div class="form-group">
                <label>Email *</label>
                <input type="email" name="email" required>
            </div>

            <div class="form-group">
                <label>Phone</label>
                <input type="text" name="phone">
            </div>

            <div class="form-group">
                <label>Job Position *</label>
                <select name="job_id" required>
                    <option value="">Select Job...</option>
                    <?php foreach ($jobs as $job): ?>
                        <option value="<?php echo $job['id']; ?>"><?php echo Security::escape($job['title']); ?></option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div class="form-group">
                <label>Years of Experience</label>
                <input type="number" name="years_of_experience" value="0" min="0">
            </div>

            <div class="form-group">
                <label>Resume (PDF, DOC, DOCX)</label>
                <input type="file" name="resume" accept=".pdf,.doc,.docx">
            </div>
        </div>

        <div style="margin-top: 30px; display: flex; gap: 10px;">
            <button type="submit" class="btn btn-success">Add Candidate</button>
            <a href="<?php echo APP_URL; ?>/candidates" class="btn btn-secondary">Cancel</a>
        </div>
    </form>
</div>

<?php require_once APP_PATH . '/views/layouts/footer.php'; ?>
