<!-- FILE: /app/views/layouts/header.php -->
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo isset($title) ? Security::escape($title) . ' - ' : ''; ?>SplashHRAI</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Oxygen, Ubuntu, Cantarell, sans-serif; background: #f5f7fa; color: #333; line-height: 1.6; }
        .container { max-width: 1200px; margin: 0 auto; padding: 20px; }
        .nav { background: #2c3e50; color: white; padding: 15px 0; box-shadow: 0 2px 4px rgba(0,0,0,0.1); }
        .nav .container { display: flex; justify-content: space-between; align-items: center; }
        .nav h1 { font-size: 24px; font-weight: 600; }
        .nav ul { list-style: none; display: flex; gap: 20px; }
        .nav a { color: white; text-decoration: none; padding: 8px 12px; border-radius: 4px; transition: background 0.3s; }
        .nav a:hover { background: rgba(255,255,255,0.1); }
        .card { background: white; border-radius: 8px; box-shadow: 0 2px 4px rgba(0,0,0,0.1); padding: 24px; margin-bottom: 20px; }
        .card-header { font-size: 20px; font-weight: 600; margin-bottom: 20px; padding-bottom: 12px; border-bottom: 2px solid #e1e8ed; }
        .btn { display: inline-block; padding: 10px 20px; background: #3498db; color: white; border: none; border-radius: 4px; cursor: pointer; text-decoration: none; font-size: 14px; transition: background 0.3s; }
        .btn:hover { background: #2980b9; }
        .btn-success { background: #27ae60; }
        .btn-success:hover { background: #229954; }
        .btn-danger { background: #e74c3c; }
        .btn-danger:hover { background: #c0392b; }
        .btn-secondary { background: #95a5a6; }
        .btn-secondary:hover { background: #7f8c8d; }
        .form-group { margin-bottom: 20px; }
        .form-group label { display: block; margin-bottom: 6px; font-weight: 500; }
        .form-group input, .form-group select, .form-group textarea { width: 100%; padding: 10px; border: 1px solid #ddd; border-radius: 4px; font-size: 14px; }
        .form-group textarea { resize: vertical; min-height: 100px; }
        .alert { padding: 12px 16px; border-radius: 4px; margin-bottom: 20px; }
        .alert-success { background: #d4edda; color: #155724; border: 1px solid #c3e6cb; }
        .alert-error { background: #f8d7da; color: #721c24; border: 1px solid #f5c6cb; }
        .alert-info { background: #d1ecf1; color: #0c5460; border: 1px solid #bee5eb; }
        table { width: 100%; border-collapse: collapse; }
        table th, table td { padding: 12px; text-align: left; border-bottom: 1px solid #e1e8ed; }
        table th { background: #f8f9fa; font-weight: 600; }
        table tr:hover { background: #f8f9fa; }
        .badge { display: inline-block; padding: 4px 8px; border-radius: 4px; font-size: 12px; font-weight: 500; }
        .badge-success { background: #d4edda; color: #155724; }
        .badge-warning { background: #fff3cd; color: #856404; }
        .badge-danger { background: #f8d7da; color: #721c24; }
        .badge-info { background: #d1ecf1; color: #0c5460; }
        .stats-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(250px, 1fr)); gap: 20px; margin-bottom: 30px; }
        .stat-card { background: white; padding: 20px; border-radius: 8px; box-shadow: 0 2px 4px rgba(0,0,0,0.1); }
        .stat-card h3 { font-size: 14px; color: #7f8c8d; margin-bottom: 8px; }
        .stat-card .stat-value { font-size: 32px; font-weight: 700; color: #2c3e50; }
        .pagination { display: flex; gap: 8px; margin-top: 20px; }
        .pagination a { padding: 8px 12px; border: 1px solid #ddd; border-radius: 4px; text-decoration: none; color: #333; }
        .pagination a:hover { background: #f8f9fa; }
        .pagination a.active { background: #3498db; color: white; border-color: #3498db; }
    </style>
</head>
<body>
    <?php if (isset($_SESSION['user_id'])): ?>
    <nav class="nav">
        <div class="container">
            <h1>SplashHRAI</h1>
            <ul>
                <li><a href="<?php echo APP_URL; ?>/dashboard">Dashboard</a></li>
                <li><a href="<?php echo APP_URL; ?>/employees">Employees</a></li>
                <li><a href="<?php echo APP_URL; ?>/jobs">Jobs</a></li>
                <li><a href="<?php echo APP_URL; ?>/candidates">Candidates</a></li>
                <li><a href="<?php echo APP_URL; ?>/attendance">Attendance</a></li>
                <li><a href="<?php echo APP_URL; ?>/leave">Leave</a></li>
                <li><a href="<?php echo APP_URL; ?>/performance">Performance</a></li>
                <li><a href="<?php echo APP_URL; ?>/payroll">Payroll</a></li>
                <li><a href="<?php echo APP_URL; ?>/auth/logout">Logout</a></li>
            </ul>
        </div>
    </nav>
    <?php endif; ?>
    <div class="container">
