<!-- FILE: /app/views/employees/create.php -->
<?php require_once APP_PATH . '/views/layouts/header.php'; ?>

<h1 style="margin-bottom: 20px;">Add New Employee</h1>

<div class="card">
    <form method="POST" action="<?php echo APP_URL; ?>/employees/create">
        <input type="hidden" name="<?php echo CSRF_TOKEN_NAME; ?>" value="<?php echo $csrf_token; ?>">

        <h3 style="margin-bottom: 15px;">Personal Information</h3>

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
                <label>Date of Birth</label>
                <input type="date" name="date_of_birth">
            </div>

            <div class="form-group">
                <label>Gender</label>
                <select name="gender">
                    <option value="">Select...</option>
                    <option value="male">Male</option>
                    <option value="female">Female</option>
                    <option value="other">Other</option>
                </select>
            </div>
        </div>

        <h3 style="margin: 30px 0 15px;">Employment Information</h3>

        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 15px;">
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
                <label>Position</label>
                <select name="position_id">
                    <option value="">Select...</option>
                    <?php foreach ($positions as $pos): ?>
                        <option value="<?php echo $pos['id']; ?>"><?php echo Security::escape($pos['title']); ?></option>
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
                <label>Hire Date</label>
                <input type="date" name="hire_date" value="<?php echo date('Y-m-d'); ?>">
            </div>

            <div class="form-group">
                <label>Salary</label>
                <input type="number" name="salary" step="0.01" value="0">
            </div>

            <div class="form-group">
                <label>Work Email</label>
                <input type="email" name="work_email">
            </div>
        </div>

        <div style="margin-top: 30px; display: flex; gap: 10px;">
            <button type="submit" class="btn btn-success">Create Employee</button>
            <a href="<?php echo APP_URL; ?>/employees" class="btn btn-secondary">Cancel</a>
        </div>
    </form>
</div>

<?php require_once APP_PATH . '/views/layouts/footer.php'; ?>
