<!-- FILE: /app/views/jobs/view.php -->
<?php require_once APP_PATH . '/views/layouts/header.php'; ?>

<div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px;">
    <h1><?php echo Security::escape($job['title']); ?></h1>
    <div>
        <span class="badge badge-<?php echo $job['status'] === 'open' ? 'success' : 'warning'; ?>">
            <?php echo Security::escape($job['status']); ?>
        </span>
        <a href="<?php echo APP_URL; ?>/jobs" class="btn btn-secondary">Back to Jobs</a>
    </div>
</div>

<div style="display: grid; grid-template-columns: 2fr 1fr; gap: 20px;">
    <div>
        <div class="card">
            <div class="card-header">Job Description</div>
            <div><?php echo $job['description_html']; ?></div>
        </div>

        <div class="card">
            <div class="card-header">Candidates (<?php echo count($candidates); ?>)</div>
            <table>
                <thead>
                    <tr>
                        <th>Name</th>
                        <th>Email</th>
                        <th>AI Score</th>
                        <th>Status</th>
                        <th>Applied</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (!empty($candidates)): ?>
                        <?php foreach ($candidates as $candidate): ?>
                        <tr>
                            <td><?php echo Security::escape($candidate['first_name'] . ' ' . $candidate['last_name']); ?></td>
                            <td><?php echo Security::escape($candidate['email']); ?></td>
                            <td><span class="badge badge-info"><?php echo $candidate['ai_score']; ?></span></td>
                            <td><span class="badge badge-warning"><?php echo Security::escape($candidate['status']); ?></span></td>
                            <td><?php echo date('M j, Y', strtotime($candidate['applied_at'])); ?></td>
                            <td><a href="<?php echo APP_URL; ?>/candidates/view/<?php echo $candidate['id']; ?>" class="btn btn-secondary" style="padding: 4px 8px; font-size: 12px;">View</a></td>
                        </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr><td colspan="6" style="text-align: center; color: #7f8c8d;">No candidates yet</td></tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>

    <div>
        <div class="card">
            <div class="card-header">Job Details</div>
            <table>
                <tr><th>Department:</th><td><?php echo Security::escape($job['department_name'] ?? 'N/A'); ?></td></tr>
                <tr><th>Employment Type:</th><td><?php echo Security::escape(str_replace('_', ' ', ucwords($job['employment_type']))); ?></td></tr>
                <tr><th>Location:</th><td><?php echo Security::escape($job['location']); ?></td></tr>
                <tr><th>Remote:</th><td><?php echo $job['remote_allowed'] ? 'Yes' : 'No'; ?></td></tr>
                <tr><th>Salary Range:</th><td>$<?php echo number_format($job['salary_min']); ?> - $<?php echo number_format($job['salary_max']); ?></td></tr>
                <tr><th>Openings:</th><td><?php echo $job['openings']; ?></td></tr>
                <tr><th>Posted:</th><td><?php echo $job['posted_date'] ? date('M j, Y', strtotime($job['posted_date'])) : 'N/A'; ?></td></tr>
            </table>
        </div>
    </div>
</div>

<?php require_once APP_PATH . '/views/layouts/footer.php'; ?>
