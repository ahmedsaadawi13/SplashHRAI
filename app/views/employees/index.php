<!-- FILE: /app/views/employees/index.php -->
<?php require_once APP_PATH . '/views/layouts/header.php'; ?>

<div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px;">
    <h1>Employees</h1>
    <a href="<?php echo APP_URL; ?>/employees/create" class="btn btn-success">+ Add Employee</a>
</div>

<div class="card">
    <form method="GET" action="<?php echo APP_URL; ?>/employees" style="display: flex; gap: 10px; margin-bottom: 20px;">
        <input type="text" name="search" placeholder="Search employees..." value="<?php echo Security::escape($filters['search']); ?>" style="flex: 1;">
        <select name="department_id">
            <option value="">All Departments</option>
            <?php foreach ($departments as $dept): ?>
                <option value="<?php echo $dept['id']; ?>" <?php echo $filters['department_id'] == $dept['id'] ? 'selected' : ''; ?>>
                    <?php echo Security::escape($dept['name']); ?>
                </option>
            <?php endforeach; ?>
        </select>
        <select name="employment_status">
            <option value="">All Status</option>
            <option value="active" <?php echo $filters['employment_status'] == 'active' ? 'selected' : ''; ?>>Active</option>
            <option value="on_leave" <?php echo $filters['employment_status'] == 'on_leave' ? 'selected' : ''; ?>>On Leave</option>
            <option value="terminated" <?php echo $filters['employment_status'] == 'terminated' ? 'selected' : ''; ?>>Terminated</option>
        </select>
        <button type="submit" class="btn">Search</button>
    </form>

    <table>
        <thead>
            <tr>
                <th>Code</th>
                <th>Name</th>
                <th>Email</th>
                <th>Department</th>
                <th>Position</th>
                <th>Status</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            <?php if (!empty($employees)): ?>
                <?php foreach ($employees as $employee): ?>
                <tr>
                    <td><?php echo Security::escape($employee['employee_code']); ?></td>
                    <td><?php echo Security::escape($employee['first_name'] . ' ' . $employee['last_name']); ?></td>
                    <td><?php echo Security::escape($employee['email']); ?></td>
                    <td><?php echo Security::escape($employee['department_name'] ?? 'N/A'); ?></td>
                    <td><?php echo Security::escape($employee['position_title'] ?? 'N/A'); ?></td>
                    <td>
                        <?php
                        $statusClass = $employee['employment_status'] === 'active' ? 'success' : 'warning';
                        echo '<span class="badge badge-' . $statusClass . '">' . Security::escape($employee['employment_status']) . '</span>';
                        ?>
                    </td>
                    <td>
                        <a href="<?php echo APP_URL; ?>/employees/view/<?php echo $employee['id']; ?>" class="btn btn-secondary" style="padding: 4px 8px; font-size: 12px;">View</a>
                    </td>
                </tr>
                <?php endforeach; ?>
            <?php else: ?>
                <tr><td colspan="7" style="text-align: center; color: #7f8c8d;">No employees found</td></tr>
            <?php endif; ?>
        </tbody>
    </table>
</div>

<?php require_once APP_PATH . '/views/layouts/footer.php'; ?>
