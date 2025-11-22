<!-- FILE: /app/views/attendance/index.php -->
<?php require_once APP_PATH . '/views/layouts/header.php'; ?>

<div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px;">
    <h1>Attendance Records</h1>
    <div>
        <form method="GET" action="<?php echo APP_URL; ?>/attendance" style="display: inline-flex; gap: 10px;">
            <input type="date" name="date" value="<?php echo $date; ?>">
            <button type="submit" class="btn">View Date</button>
        </form>
        <a href="<?php echo APP_URL; ?>/attendance/report" class="btn btn-secondary">View Report</a>
    </div>
</div>

<div class="card">
    <div class="card-header">Attendance for <?php echo date('F j, Y', strtotime($date)); ?></div>
    <table>
        <thead>
            <tr>
                <th>Employee</th>
                <th>Code</th>
                <th>Department</th>
                <th>Check In</th>
                <th>Check Out</th>
                <th>Hours</th>
                <th>Status</th>
            </tr>
        </thead>
        <tbody>
            <?php if (!empty($records)): ?>
                <?php foreach ($records as $record): ?>
                <tr>
                    <td><?php echo Security::escape($record['first_name'] . ' ' . $record['last_name']); ?></td>
                    <td><?php echo Security::escape($record['employee_code']); ?></td>
                    <td><?php echo Security::escape($record['department_name'] ?? 'N/A'); ?></td>
                    <td><?php echo $record['check_in_time'] ? date('g:i A', strtotime($record['check_in_time'])) : '-'; ?></td>
                    <td><?php echo $record['check_out_time'] ? date('g:i A', strtotime($record['check_out_time'])) : '-'; ?></td>
                    <td><?php echo $record['total_hours']; ?> hrs</td>
                    <td><span class="badge badge-success"><?php echo Security::escape($record['status']); ?></span></td>
                </tr>
                <?php endforeach; ?>
            <?php else: ?>
                <tr><td colspan="7" style="text-align: center; color: #7f8c8d;">No attendance records for this date</td></tr>
            <?php endif; ?>
        </tbody>
    </table>
</div>

<?php require_once APP_PATH . '/views/layouts/footer.php'; ?>
