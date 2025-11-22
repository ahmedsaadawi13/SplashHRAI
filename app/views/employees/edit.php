<!-- FILE: /app/views/employees/edit.php -->
<?php require_once APP_PATH . '/views/layouts/header.php'; ?>

<h1 style="margin-bottom: 20px;">Edit Employee</h1>

<div class="card">
    <form method="POST" action="<?php echo APP_URL; ?>/employees/edit/<?php echo $employee['id']; ?>">
        <input type="hidden" name="<?php echo CSRF_TOKEN_NAME; ?>" value="<?php echo $csrf_token; ?>">

        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 15px;">
            <div class="form-group">
                <label>First Name *</label>
                <input type="text" name="first_name" value="<?php echo Security::escape($employee['first_name']); ?>" required>
            </div>

            <div class="form-group">
                <label>Last Name *</label>
                <input type="text" name="last_name" value="<?php echo Security::escape($employee['last_name']); ?>" required>
            </div>

            <div class="form-group">
                <label>Email *</label>
                <input type="email" name="email" value="<?php echo Security::escape($employee['email']); ?>" required>
            </div>

            <div class="form-group">
                <label>Phone</label>
                <input type="text" name="phone" value="<?php echo Security::escape($employee['phone']); ?>">
            </div>

            <div class="form-group">
                <label>Department</label>
                <select name="department_id">
                    <option value="">Select...</option>
                    <?php foreach ($departments as $dept): ?>
                        <option value="<?php echo $dept['id']; ?>" <?php echo $employee['department_id'] == $dept['id'] ? 'selected' : ''; ?>>
                            <?php echo Security::escape($dept['name']); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div class="form-group">
                <label>Position</label>
                <select name="position_id">
                    <option value="">Select...</option>
                    <?php foreach ($positions as $pos): ?>
                        <option value="<?php echo $pos['id']; ?>" <?php echo $employee['position_id'] == $pos['id'] ? 'selected' : ''; ?>>
                            <?php echo Security::escape($pos['title']); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div class="form-group">
                <label>Employment Status</label>
                <select name="employment_status">
                    <option value="active" <?php echo $employee['employment_status'] == 'active' ? 'selected' : ''; ?>>Active</option>
                    <option value="on_leave" <?php echo $employee['employment_status'] == 'on_leave' ? 'selected' : ''; ?>>On Leave</option>
                    <option value="suspended" <?php echo $employee['employment_status'] == 'suspended' ? 'selected' : ''; ?>>Suspended</option>
                    <option value="terminated" <?php echo $employee['employment_status'] == 'terminated' ? 'selected' : ''; ?>>Terminated</option>
                </select>
            </div>

            <div class="form-group">
                <label>Salary</label>
                <input type="number" name="salary" step="0.01" value="<?php echo $employee['salary']; ?>">
            </div>
        </div>

        <div style="margin-top: 30px; display: flex; gap: 10px;">
            <button type="submit" class="btn btn-success">Update Employee</button>
            <a href="<?php echo APP_URL; ?>/employees/view/<?php echo $employee['id']; ?>" class="btn btn-secondary">Cancel</a>
        </div>
    </form>
</div>

<?php require_once APP_PATH . '/views/layouts/footer.php'; ?>
