<!-- FILE: /app/views/employees/view.php -->
<?php require_once APP_PATH . '/views/layouts/header.php'; ?>

<div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px;">
    <h1><?php echo Security::escape($employee['first_name'] . ' ' . $employee['last_name']); ?></h1>
    <div>
        <a href="<?php echo APP_URL; ?>/employees/edit/<?php echo $employee['id']; ?>" class="btn">Edit</a>
        <a href="<?php echo APP_URL; ?>/employees" class="btn btn-secondary">Back to List</a>
    </div>
</div>

<div style="display: grid; grid-template-columns: 2fr 1fr; gap: 20px;">
    <div>
        <div class="card">
            <div class="card-header">Personal Information</div>
            <table>
                <tr><th>Employee Code:</th><td><?php echo Security::escape($employee['employee_code']); ?></td></tr>
                <tr><th>Email:</th><td><?php echo Security::escape($employee['email']); ?></td></tr>
                <tr><th>Phone:</th><td><?php echo Security::escape($employee['phone'] ?? 'N/A'); ?></td></tr>
                <tr><th>Date of Birth:</th><td><?php echo $employee['date_of_birth'] ? date('M j, Y', strtotime($employee['date_of_birth'])) : 'N/A'; ?></td></tr>
                <tr><th>Gender:</th><td><?php echo Security::escape($employee['gender'] ?? 'N/A'); ?></td></tr>
            </table>
        </div>

        <div class="card">
            <div class="card-header">Employment Details</div>
            <table>
                <tr><th>Department:</th><td><?php echo Security::escape($employee['department_name'] ?? 'N/A'); ?></td></tr>
                <tr><th>Position:</th><td><?php echo Security::escape($employee['position_title'] ?? 'N/A'); ?></td></tr>
                <tr><th>Employment Type:</th><td><?php echo Security::escape($employee['employment_type']); ?></td></tr>
                <tr><th>Status:</th><td><span class="badge badge-success"><?php echo Security::escape($employee['employment_status']); ?></span></td></tr>
                <tr><th>Hire Date:</th><td><?php echo $employee['hire_date'] ? date('M j, Y', strtotime($employee['hire_date'])) : 'N/A'; ?></td></tr>
                <tr><th>Manager:</th><td><?php echo $employee['manager_first_name'] ? Security::escape($employee['manager_first_name'] . ' ' . $employee['manager_last_name']) : 'N/A'; ?></td></tr>
            </table>
        </div>

        <div class="card">
            <div class="card-header">Documents</div>
            <?php if (!empty($documents)): ?>
                <table>
                    <thead>
                        <tr><th>Title</th><th>Category</th><th>Uploaded</th><th>Action</th></tr>
                    </thead>
                    <tbody>
                        <?php foreach ($documents as $doc): ?>
                        <tr>
                            <td><?php echo Security::escape($doc['title']); ?></td>
                            <td><?php echo Security::escape($doc['category']); ?></td>
                            <td><?php echo date('M j, Y', strtotime($doc['created_at'])); ?></td>
                            <td><a href="#" class="btn btn-secondary" style="padding: 4px 8px; font-size: 12px;">Download</a></td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            <?php else: ?>
                <p style="color: #7f8c8d; text-align: center; padding: 20px;">No documents uploaded</p>
            <?php endif; ?>
        </div>
    </div>

    <div>
        <div class="card">
            <div class="card-header">Quick Stats</div>
            <div style="padding: 10px 0;">
                <p><strong>Salary:</strong> $<?php echo number_format($employee['salary'], 2); ?></p>
                <p><strong>Work Email:</strong> <?php echo Security::escape($employee['work_email'] ?? 'N/A'); ?></p>
                <p><strong>Location:</strong> <?php echo Security::escape($employee['work_location'] ?? 'N/A'); ?></p>
            </div>
        </div>

        <div class="card">
            <div class="card-header">Notes</div>
            <?php if (!empty($notes)): ?>
                <?php foreach ($notes as $note): ?>
                <div style="padding: 10px; background: #f8f9fa; margin-bottom: 10px; border-radius: 4px;">
                    <p style="font-size: 13px; margin-bottom: 5px;"><?php echo nl2br(Security::escape($note['note'])); ?></p>
                    <p style="font-size: 11px; color: #7f8c8d;">
                        - <?php echo Security::escape($note['first_name'] . ' ' . $note['last_name']); ?>,
                        <?php echo date('M j, Y', strtotime($note['created_at'])); ?>
                    </p>
                </div>
                <?php endforeach; ?>
            <?php else: ?>
                <p style="color: #7f8c8d; font-size: 14px;">No notes</p>
            <?php endif; ?>
        </div>
    </div>
</div>

<?php require_once APP_PATH . '/views/layouts/footer.php'; ?>
