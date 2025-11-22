<?php
// FILE: /config/config.php

// Load environment variables
if (file_exists(__DIR__ . '/../.env')) {
    $lines = file(__DIR__ . '/../.env', FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    foreach ($lines as $line) {
        if (strpos(trim($line), '#') === 0) continue;
        list($key, $value) = explode('=', $line, 2);
        $_ENV[trim($key)] = trim($value);
    }
}

// Database Configuration
define('DB_HOST', $_ENV['DB_HOST'] ?? 'localhost');
define('DB_NAME', $_ENV['DB_NAME'] ?? 'splashhr_ai');
define('DB_USER', $_ENV['DB_USER'] ?? 'root');
define('DB_PASS', $_ENV['DB_PASS'] ?? '');
define('DB_CHARSET', 'utf8mb4');

// Application Configuration
define('APP_NAME', $_ENV['APP_NAME'] ?? 'SplashHRAI');
define('APP_URL', $_ENV['APP_URL'] ?? 'http://localhost');
define('APP_ENV', $_ENV['APP_ENV'] ?? 'production');
define('APP_TIMEZONE', $_ENV['APP_TIMEZONE'] ?? 'UTC');

// Paths
define('ROOT_PATH', dirname(__DIR__));
define('APP_PATH', ROOT_PATH . '/app');
define('PUBLIC_PATH', ROOT_PATH . '/public');
define('STORAGE_PATH', ROOT_PATH . '/storage');
define('UPLOAD_PATH', STORAGE_PATH . '/uploads');
define('LOG_PATH', STORAGE_PATH . '/logs');

// Security
define('SESSION_LIFETIME', (int)($_ENV['SESSION_LIFETIME'] ?? 7200));
define('CSRF_TOKEN_NAME', $_ENV['CSRF_TOKEN_NAME'] ?? 'csrf_token');

// AI Configuration
define('AI_PROVIDER', $_ENV['AI_PROVIDER'] ?? 'openai');
define('AI_API_KEY', $_ENV['AI_API_KEY'] ?? '');
define('AI_MODEL', $_ENV['AI_MODEL'] ?? 'gpt-4');
define('AI_MAX_TOKENS', (int)($_ENV['AI_MAX_TOKENS'] ?? 2000));
define('AI_TEMPERATURE', (float)($_ENV['AI_TEMPERATURE'] ?? 0.7));

// File Upload
define('MAX_UPLOAD_SIZE', (int)($_ENV['MAX_UPLOAD_SIZE'] ?? 10485760));
define('ALLOWED_EXTENSIONS', $_ENV['ALLOWED_EXTENSIONS'] ?? 'pdf,doc,docx,jpg,jpeg,png,txt');

// Email
define('EMAIL_FROM', $_ENV['EMAIL_FROM'] ?? 'noreply@splashhr.ai');
define('EMAIL_FROM_NAME', $_ENV['EMAIL_FROM_NAME'] ?? 'SplashHRAI');

// API
define('API_RATE_LIMIT', (int)($_ENV['API_RATE_LIMIT'] ?? 100));
define('API_RATE_WINDOW', (int)($_ENV['API_RATE_WINDOW'] ?? 3600));

// Logging
define('LOG_LEVEL', $_ENV['LOG_LEVEL'] ?? 'error');

// Set timezone
date_default_timezone_set(APP_TIMEZONE);

// Error reporting
if (APP_ENV === 'development') {
    error_reporting(E_ALL);
    ini_set('display_errors', 1);
} else {
    error_reporting(0);
    ini_set('display_errors', 0);
}

// Session configuration
ini_set('session.cookie_httponly', 1);
ini_set('session.use_only_cookies', 1);
ini_set('session.cookie_samesite', 'Strict');
if (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on') {
    ini_set('session.cookie_secure', 1);
}
