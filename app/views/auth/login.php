<!-- FILE: /app/views/auth/login.php -->
<?php require_once APP_PATH . '/views/layouts/header.php'; ?>

<div style="max-width: 400px; margin: 100px auto;">
    <div class="card">
        <div class="card-header" style="text-align: center;">
            <h2>Login to SplashHRAI</h2>
        </div>

        <?php if (isset($error)): ?>
            <div class="alert alert-error"><?php echo Security::escape($error); ?></div>
        <?php endif; ?>

        <form method="POST" action="<?php echo APP_URL; ?>/auth/login">
            <input type="hidden" name="<?php echo CSRF_TOKEN_NAME; ?>" value="<?php echo $csrf_token; ?>">

            <div class="form-group">
                <label>Email Address</label>
                <input type="email" name="email" required autofocus>
            </div>

            <div class="form-group">
                <label>Password</label>
                <input type="password" name="password" required>
            </div>

            <div class="form-group">
                <button type="submit" class="btn" style="width: 100%;">Login</button>
            </div>

            <div style="text-align: center; margin-top: 20px;">
                <p>Don't have an account? <a href="<?php echo APP_URL; ?>/auth/register">Register here</a></p>
            </div>
        </form>

        <div style="margin-top: 30px; padding-top: 20px; border-top: 1px solid #e1e8ed;">
            <p><strong>Demo Credentials:</strong></p>
            <p>Email: admin@demo.splashhr.ai<br>Password: password</p>
        </div>
    </div>
</div>

<?php require_once APP_PATH . '/views/layouts/footer.php'; ?>
