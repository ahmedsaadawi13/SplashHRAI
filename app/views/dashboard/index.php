<!-- FILE: /app/views/dashboard/index.php -->
<?php require_once APP_PATH . '/views/layouts/header.php'; ?>

<h1 style="margin-bottom: 30px;">Dashboard</h1>

<div class="stats-grid">
    <div class="stat-card">
        <h3>Total Employees</h3>
        <div class="stat-value"><?php echo $stats['total_employees']; ?></div>
        <p style="color: #27ae60; margin-top: 8px;"><?php echo $stats['active_employees']; ?> active</p>
    </div>

    <div class="stat-card">
        <h3>New Hires (30 days)</h3>
        <div class="stat-value"><?php echo $stats['new_hires_month']; ?></div>
    </div>

    <div class="stat-card">
        <h3>Open Jobs</h3>
        <div class="stat-value"><?php echo $stats['open_jobs']; ?></div>
        <p style="color: #7f8c8d; margin-top: 8px;"><?php echo $stats['total_jobs']; ?> total</p>
    </div>

    <div class="stat-card">
        <h3>Candidates</h3>
        <div class="stat-value"><?php echo $stats['total_candidates']; ?></div>
        <p style="color: #3498db; margin-top: 8px;"><?php echo $stats['new_applications']; ?> new</p>
    </div>

    <div class="stat-card">
        <h3>Present Today</h3>
        <div class="stat-value"><?php echo $stats['present_today']; ?></div>
    </div>

    <div class="stat-card">
        <h3>Pending Leave Requests</h3>
        <div class="stat-value"><?php echo $stats['pending_leave_requests']; ?></div>
    </div>
</div>

<div style="display: grid; grid-template-columns: 1fr 1fr; gap: 20px;">
    <?php if (!empty($pending_leaves)): ?>
    <div class="card">
        <div class="card-header">Pending Leave Requests</div>
        <table>
            <thead>
                <tr>
                    <th>Employee</th>
                    <th>Type</th>
                    <th>Dates</th>
                    <th>Action</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($pending_leaves as $leave): ?>
                <tr>
                    <td><?php echo Security::escape($leave['first_name'] . ' ' . $leave['last_name']); ?></td>
                    <td><?php echo Security::escape($leave['leave_type_name']); ?></td>
                    <td><?php echo date('M j', strtotime($leave['from_date'])); ?> - <?php echo date('M j', strtotime($leave['to_date'])); ?></td>
                    <td><a href="<?php echo APP_URL; ?>/leave" class="btn btn-secondary" style="padding: 4px 8px; font-size: 12px;">View</a></td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
    <?php endif; ?>

    <?php if (!empty($upcoming_interviews)): ?>
    <div class="card">
        <div class="card-header">Upcoming Interviews</div>
        <table>
            <thead>
                <tr>
                    <th>Candidate</th>
                    <th>Job</th>
                    <th>Scheduled</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($upcoming_interviews as $interview): ?>
                <tr>
                    <td><?php echo Security::escape($interview['first_name'] . ' ' . $interview['last_name']); ?></td>
                    <td><?php echo Security::escape($interview['job_title']); ?></td>
                    <td><?php echo date('M j, g:i A', strtotime($interview['scheduled_at'])); ?></td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
    <?php endif; ?>
</div>

<div class="card" style="margin-top: 20px;">
    <div class="card-header">Recent Activity</div>
    <table>
        <thead>
            <tr>
                <th>User</th>
                <th>Action</th>
                <th>Description</th>
                <th>Time</th>
            </tr>
        </thead>
        <tbody>
            <?php if (!empty($recent_activities)): ?>
                <?php foreach ($recent_activities as $activity): ?>
                <tr>
                    <td><?php echo $activity['first_name'] ? Security::escape($activity['first_name'] . ' ' . $activity['last_name']) : 'System'; ?></td>
                    <td><span class="badge badge-info"><?php echo Security::escape($activity['action']); ?></span></td>
                    <td><?php echo Security::escape($activity['description']); ?></td>
                    <td><?php echo date('M j, g:i A', strtotime($activity['created_at'])); ?></td>
                </tr>
                <?php endforeach; ?>
            <?php else: ?>
                <tr><td colspan="4" style="text-align: center; color: #7f8c8d;">No recent activity</td></tr>
            <?php endif; ?>
        </tbody>
    </table>
</div>

<?php require_once APP_PATH . '/views/layouts/footer.php'; ?>
