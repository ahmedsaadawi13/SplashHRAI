<!-- FILE: /app/views/auth/register_success.php -->
<?php require_once APP_PATH . '/views/layouts/header.php'; ?>

<div style="max-width: 500px; margin: 100px auto;">
    <div class="card">
        <div style="text-align: center;">
            <div style="font-size: 64px; color: #27ae60;">✓</div>
            <h2 style="margin: 20px 0;">Registration Successful!</h2>
            <p>Your account has been created successfully.</p>
            <p>You can now <a href="<?php echo APP_URL; ?>/auth/login">login</a> using your credentials.</p>
        </div>
    </div>
</div>

<?php require_once APP_PATH . '/views/layouts/footer.php'; ?>
