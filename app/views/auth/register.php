<!-- FILE: /app/views/auth/register.php -->
<?php require_once APP_PATH . '/views/layouts/header.php'; ?>

<div style="max-width: 500px; margin: 50px auto;">
    <div class="card">
        <div class="card-header" style="text-align: center;">
            <h2>Create Your Account</h2>
            <p style="color: #7f8c8d; font-size: 14px;">Start your 14-day free trial</p>
        </div>

        <?php if (isset($error)): ?>
            <div class="alert alert-error"><?php echo Security::escape($error); ?></div>
        <?php endif; ?>

        <form method="POST" action="<?php echo APP_URL; ?>/auth/register">
            <input type="hidden" name="<?php echo CSRF_TOKEN_NAME; ?>" value="<?php echo $csrf_token; ?>">

            <div class="form-group">
                <label>Company Name</label>
                <input type="text" name="company_name" value="<?php echo isset($data['company_name']) ? Security::escape($data['company_name']) : ''; ?>" required>
            </div>

            <div class="form-group">
                <label>Your First Name</label>
                <input type="text" name="first_name" value="<?php echo isset($data['first_name']) ? Security::escape($data['first_name']) : ''; ?>" required>
            </div>

            <div class="form-group">
                <label>Your Last Name</label>
                <input type="text" name="last_name" value="<?php echo isset($data['last_name']) ? Security::escape($data['last_name']) : ''; ?>" required>
            </div>

            <div class="form-group">
                <label>Email Address</label>
                <input type="email" name="email" value="<?php echo isset($data['email']) ? Security::escape($data['email']) : ''; ?>" required>
            </div>

            <div class="form-group">
                <label>Password (minimum 8 characters)</label>
                <input type="password" name="password" required>
            </div>

            <div class="form-group">
                <button type="submit" class="btn" style="width: 100%;">Create Account</button>
            </div>

            <div style="text-align: center; margin-top: 20px;">
                <p>Already have an account? <a href="<?php echo APP_URL; ?>/auth/login">Login here</a></p>
            </div>
        </form>
    </div>
</div>

<?php require_once APP_PATH . '/views/layouts/footer.php'; ?>
