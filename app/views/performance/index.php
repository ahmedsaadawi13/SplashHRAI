<!-- FILE: /app/views/performance/index.php -->
<?php require_once APP_PATH . '/views/layouts/header.php'; ?>

<h1 style="margin-bottom: 20px;">Performance Cycles</h1>

<div class="card">
    <table>
        <thead>
            <tr>
                <th>Cycle Name</th>
                <th>Period</th>
                <th>Status</th>
                <th>Self Review Deadline</th>
                <th>Manager Review Deadline</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            <?php if (!empty($cycles)): ?>
                <?php foreach ($cycles as $cycle): ?>
                <tr>
                    <td><?php echo Security::escape($cycle['name']); ?></td>
                    <td><?php echo date('M j', strtotime($cycle['start_date'])); ?> - <?php echo date('M j, Y', strtotime($cycle['end_date'])); ?></td>
                    <td>
                        <?php
                        $statusClass = $cycle['status'] === 'completed' ? 'success' : ($cycle['status'] === 'active' ? 'info' : 'warning');
                        echo '<span class="badge badge-' . $statusClass . '">' . Security::escape($cycle['status']) . '</span>';
                        ?>
                    </td>
                    <td><?php echo date('M j, Y', strtotime($cycle['self_review_deadline'])); ?></td>
                    <td><?php echo date('M j, Y', strtotime($cycle['manager_review_deadline'])); ?></td>
                    <td>
                        <a href="<?php echo APP_URL; ?>/performance/cycle/<?php echo $cycle['id']; ?>" class="btn btn-secondary" style="padding: 4px 8px; font-size: 12px;">View</a>
                    </td>
                </tr>
                <?php endforeach; ?>
            <?php else: ?>
                <tr><td colspan="6" style="text-align: center; color: #7f8c8d;">No performance cycles found</td></tr>
            <?php endif; ?>
        </tbody>
    </table>
</div>

<?php require_once APP_PATH . '/views/layouts/footer.php'; ?>
