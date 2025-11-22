<!-- FILE: /app/views/jobs/index.php -->
<?php require_once APP_PATH . '/views/layouts/header.php'; ?>

<div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px;">
    <h1>Jobs</h1>
    <a href="<?php echo APP_URL; ?>/jobs/create" class="btn btn-success">+ Create Job</a>
</div>

<div class="card">
    <table>
        <thead>
            <tr>
                <th>Title</th>
                <th>Department</th>
                <th>Type</th>
                <th>Location</th>
                <th>Candidates</th>
                <th>Status</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            <?php if (!empty($jobs)): ?>
                <?php foreach ($jobs as $job): ?>
                <tr>
                    <td><?php echo Security::escape($job['title']); ?></td>
                    <td><?php echo Security::escape($job['department_name'] ?? 'N/A'); ?></td>
                    <td><?php echo Security::escape(str_replace('_', ' ', ucwords($job['employment_type']))); ?></td>
                    <td><?php echo Security::escape($job['location']); ?></td>
                    <td><span class="badge badge-info"><?php echo $job['candidate_count']; ?></span></td>
                    <td>
                        <?php
                        $statusClass = $job['status'] === 'open' ? 'success' : ($job['status'] === 'closed' ? 'danger' : 'warning');
                        echo '<span class="badge badge-' . $statusClass . '">' . Security::escape($job['status']) . '</span>';
                        ?>
                    </td>
                    <td>
                        <a href="<?php echo APP_URL; ?>/jobs/view/<?php echo $job['id']; ?>" class="btn btn-secondary" style="padding: 4px 8px; font-size: 12px;">View</a>
                    </td>
                </tr>
                <?php endforeach; ?>
            <?php else: ?>
                <tr><td colspan="7" style="text-align: center; color: #7f8c8d;">No jobs found</td></tr>
            <?php endif; ?>
        </tbody>
    </table>
</div>

<?php require_once APP_PATH . '/views/layouts/footer.php'; ?>
