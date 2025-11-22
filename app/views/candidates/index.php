<!-- FILE: /app/views/candidates/index.php -->
<?php require_once APP_PATH . '/views/layouts/header.php'; ?>

<div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px;">
    <h1>Candidates</h1>
    <a href="<?php echo APP_URL; ?>/candidates/create" class="btn btn-success">+ Add Candidate</a>
</div>

<div class="card">
    <table>
        <thead>
            <tr>
                <th>Name</th>
                <th>Email</th>
                <th>Job</th>
                <th>AI Score</th>
                <th>Status</th>
                <th>Applied</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            <?php if (!empty($candidates)): ?>
                <?php foreach ($candidates as $candidate): ?>
                <tr>
                    <td><?php echo Security::escape($candidate['first_name'] . ' ' . $candidate['last_name']); ?></td>
                    <td><?php echo Security::escape($candidate['email']); ?></td>
                    <td><?php echo Security::escape($candidate['job_title']); ?></td>
                    <td>
                        <span class="badge badge-<?php echo $candidate['ai_score'] >= 80 ? 'success' : ($candidate['ai_score'] >= 60 ? 'info' : 'warning'); ?>">
                            <?php echo $candidate['ai_score']; ?>
                        </span>
                    </td>
                    <td>
                        <?php
                        $statusClass = $candidate['status'] === 'hired' ? 'success' : ($candidate['status'] === 'rejected' ? 'danger' : 'warning');
                        echo '<span class="badge badge-' . $statusClass . '">' . Security::escape($candidate['status']) . '</span>';
                        ?>
                    </td>
                    <td><?php echo date('M j, Y', strtotime($candidate['applied_at'])); ?></td>
                    <td>
                        <a href="<?php echo APP_URL; ?>/candidates/view/<?php echo $candidate['id']; ?>" class="btn btn-secondary" style="padding: 4px 8px; font-size: 12px;">View</a>
                    </td>
                </tr>
                <?php endforeach; ?>
            <?php else: ?>
                <tr><td colspan="7" style="text-align: center; color: #7f8c8d;">No candidates found</td></tr>
            <?php endif; ?>
        </tbody>
    </table>
</div>

<?php require_once APP_PATH . '/views/layouts/footer.php'; ?>
