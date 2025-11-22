<!-- FILE: /app/views/leave/index.php -->
<?php require_once APP_PATH . '/views/layouts/header.php'; ?>

<div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px;">
    <h1>Leave Requests</h1>
    <a href="<?php echo APP_URL; ?>/leave/create" class="btn btn-success">+ Request Leave</a>
</div>

<div class="card">
    <table>
        <thead>
            <tr>
                <th>Employee</th>
                <th>Leave Type</th>
                <th>From</th>
                <th>To</th>
                <th>Days</th>
                <th>Status</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            <?php if (!empty($requests)): ?>
                <?php foreach ($requests as $request): ?>
                <tr>
                    <td><?php echo isset($request['first_name']) ? Security::escape($request['first_name'] . ' ' . $request['last_name']) : 'You'; ?></td>
                    <td><?php echo Security::escape($request['leave_type_name']); ?></td>
                    <td><?php echo date('M j, Y', strtotime($request['from_date'])); ?></td>
                    <td><?php echo date('M j, Y', strtotime($request['to_date'])); ?></td>
                    <td><?php echo $request['total_days']; ?></td>
                    <td>
                        <?php
                        $statusClass = $request['status'] === 'approved' ? 'success' : ($request['status'] === 'rejected' ? 'danger' : 'warning');
                        echo '<span class="badge badge-' . $statusClass . '">' . Security::escape($request['status']) . '</span>';
                        ?>
                    </td>
                    <td>
                        <?php if ($request['status'] === 'pending'): ?>
                            <form method="POST" action="<?php echo APP_URL; ?>/leave/approve/<?php echo $request['id']; ?>" style="display: inline;">
                                <input type="hidden" name="<?php echo CSRF_TOKEN_NAME; ?>" value="<?php echo Security::generateCsrfToken(); ?>">
                                <button type="submit" class="btn btn-success" style="padding: 4px 8px; font-size: 12px;">Approve</button>
                            </form>
                            <form method="POST" action="<?php echo APP_URL; ?>/leave/reject/<?php echo $request['id']; ?>" style="display: inline;">
                                <input type="hidden" name="<?php echo CSRF_TOKEN_NAME; ?>" value="<?php echo Security::generateCsrfToken(); ?>">
                                <button type="submit" class="btn btn-danger" style="padding: 4px 8px; font-size: 12px;">Reject</button>
                            </form>
                        <?php endif; ?>
                    </td>
                </tr>
                <?php endforeach; ?>
            <?php else: ?>
                <tr><td colspan="7" style="text-align: center; color: #7f8c8d;">No leave requests</td></tr>
            <?php endif; ?>
        </tbody>
    </table>
</div>

<?php require_once APP_PATH . '/views/layouts/footer.php'; ?>
