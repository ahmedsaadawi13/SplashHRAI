<!-- FILE: /app/views/payroll/index.php -->
<?php require_once APP_PATH . '/views/layouts/header.php'; ?>

<h1 style="margin-bottom: 20px;">Payroll Periods</h1>

<div class="card">
    <table>
        <thead>
            <tr>
                <th>Period Name</th>
                <th>Type</th>
                <th>Period</th>
                <th>Payment Date</th>
                <th>Total Net</th>
                <th>Status</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            <?php if (!empty($periods)): ?>
                <?php foreach ($periods as $period): ?>
                <tr>
                    <td><?php echo Security::escape($period['name']); ?></td>
                    <td><?php echo Security::escape(str_replace('_', ' ', ucwords($period['period_type']))); ?></td>
                    <td><?php echo date('M j', strtotime($period['start_date'])); ?> - <?php echo date('M j, Y', strtotime($period['end_date'])); ?></td>
                    <td><?php echo date('M j, Y', strtotime($period['payment_date'])); ?></td>
                    <td>$<?php echo number_format($period['total_net'], 2); ?></td>
                    <td>
                        <?php
                        $statusClass = $period['status'] === 'paid' ? 'success' : ($period['status'] === 'approved' ? 'info' : 'warning');
                        echo '<span class="badge badge-' . $statusClass . '">' . Security::escape($period['status']) . '</span>';
                        ?>
                    </td>
                    <td>
                        <a href="<?php echo APP_URL; ?>/payroll/period/<?php echo $period['id']; ?>" class="btn btn-secondary" style="padding: 4px 8px; font-size: 12px;">View</a>
                    </td>
                </tr>
                <?php endforeach; ?>
            <?php else: ?>
                <tr><td colspan="7" style="text-align: center; color: #7f8c8d;">No payroll periods found</td></tr>
            <?php endif; ?>
        </tbody>
    </table>
</div>

<?php require_once APP_PATH . '/views/layouts/footer.php'; ?>
