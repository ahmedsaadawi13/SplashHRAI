<!-- FILE: /app/views/candidates/view.php -->
<?php require_once APP_PATH . '/views/layouts/header.php'; ?>

<div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px;">
    <h1><?php echo Security::escape($candidate['first_name'] . ' ' . $candidate['last_name']); ?></h1>
    <a href="<?php echo APP_URL; ?>/candidates" class="btn btn-secondary">Back to Candidates</a>
</div>

<div style="display: grid; grid-template-columns: 2fr 1fr; gap: 20px;">
    <div>
        <div class="card">
            <div class="card-header">Candidate Information</div>
            <table>
                <tr><th>Email:</th><td><?php echo Security::escape($candidate['email']); ?></td></tr>
                <tr><th>Phone:</th><td><?php echo Security::escape($candidate['phone'] ?? 'N/A'); ?></td></tr>
                <tr><th>Job Applied:</th><td><?php echo Security::escape($candidate['job_title']); ?></td></tr>
                <tr><th>Location:</th><td><?php echo Security::escape($candidate['location'] ?? 'N/A'); ?></td></tr>
                <tr><th>Years of Experience:</th><td><?php echo $candidate['years_of_experience']; ?></td></tr>
                <tr><th>Applied Date:</th><td><?php echo date('M j, Y', strtotime($candidate['applied_at'])); ?></td></tr>
                <tr><th>Status:</th><td><span class="badge badge-warning"><?php echo Security::escape($candidate['status']); ?></span></td></tr>
            </table>
        </div>

        <div class="card">
            <div class="card-header">AI Analysis</div>
            <div style="margin-bottom: 15px;">
                <h4>AI Score: <span class="badge badge-<?php echo $candidate['ai_score'] >= 80 ? 'success' : 'info'; ?>" style="font-size: 18px;"><?php echo $candidate['ai_score']; ?>/100</span></h4>
            </div>
            <div style="background: #f8f9fa; padding: 15px; border-radius: 4px; margin-bottom: 15px;">
                <h4 style="margin-bottom: 10px;">Summary:</h4>
                <p><?php echo Security::escape($candidate['ai_summary'] ?? 'No AI analysis available yet.'); ?></p>
            </div>
            <div>
                <h4 style="margin-bottom: 10px;">Skills:</h4>
                <p><?php echo Security::escape($candidate['skills'] ?? 'N/A'); ?></p>
            </div>
        </div>

        <div class="card">
            <div class="card-header">Interviews</div>
            <?php if (!empty($interviews)): ?>
                <table>
                    <thead>
                        <tr><th>Type</th><th>Interviewer</th><th>Scheduled</th><th>Status</th></tr>
                    </thead>
                    <tbody>
                        <?php foreach ($interviews as $interview): ?>
                        <tr>
                            <td><?php echo Security::escape($interview['interview_type']); ?></td>
                            <td><?php echo Security::escape($interview['interviewer_first_name'] . ' ' . $interview['interviewer_last_name']); ?></td>
                            <td><?php echo date('M j, Y g:i A', strtotime($interview['scheduled_at'])); ?></td>
                            <td><span class="badge badge-info"><?php echo Security::escape($interview['status']); ?></span></td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            <?php else: ?>
                <p style="text-align: center; color: #7f8c8d; padding: 20px;">No interviews scheduled</p>
            <?php endif; ?>
        </div>
    </div>

    <div>
        <div class="card">
            <div class="card-header">Actions</div>
            <form method="POST" action="<?php echo APP_URL; ?>/candidates/updateStatus/<?php echo $candidate['id']; ?>">
                <input type="hidden" name="<?php echo CSRF_TOKEN_NAME; ?>" value="<?php echo Security::generateCsrfToken(); ?>">
                <div class="form-group">
                    <label>Change Status</label>
                    <select name="status">
                        <option value="applied">Applied</option>
                        <option value="screening">Screening</option>
                        <option value="interview">Interview</option>
                        <option value="assessment">Assessment</option>
                        <option value="offer">Offer</option>
                        <option value="hired">Hired</option>
                        <option value="rejected">Rejected</option>
                    </select>
                </div>
                <div class="form-group">
                    <label>Reason (if rejected)</label>
                    <textarea name="reason" rows="3"></textarea>
                </div>
                <button type="submit" class="btn btn-success" style="width: 100%;">Update Status</button>
            </form>
        </div>

        <div class="card">
            <div class="card-header">Notes</div>
            <?php if (!empty($notes)): ?>
                <?php foreach ($notes as $note): ?>
                <div style="padding: 10px; background: #f8f9fa; margin-bottom: 10px; border-radius: 4px;">
                    <p style="font-size: 13px;"><?php echo nl2br(Security::escape($note['note'])); ?></p>
                    <p style="font-size: 11px; color: #7f8c8d; margin-top: 5px;">
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
