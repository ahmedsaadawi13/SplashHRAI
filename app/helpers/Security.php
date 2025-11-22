<?php
// FILE: /app/helpers/Security.php

class Security {

    public static function generateCsrfToken() {
        if (!isset($_SESSION[CSRF_TOKEN_NAME])) {
            $_SESSION[CSRF_TOKEN_NAME] = bin2hex(random_bytes(32));
        }
        return $_SESSION[CSRF_TOKEN_NAME];
    }

    public static function verifyCsrfToken($token) {
        if (!isset($_SESSION[CSRF_TOKEN_NAME])) {
            return false;
        }
        return hash_equals($_SESSION[CSRF_TOKEN_NAME], $token);
    }

    public static function hashPassword($password) {
        return password_hash($password, PASSWORD_DEFAULT);
    }

    public static function verifyPassword($password, $hash) {
        return password_verify($password, $hash);
    }

    public static function sanitize($data) {
        if (is_array($data)) {
            return array_map(array('Security', 'sanitize'), $data);
        }
        return htmlspecialchars(strip_tags(trim($data)), ENT_QUOTES, 'UTF-8');
    }

    public static function escape($data) {
        return htmlspecialchars($data, ENT_QUOTES, 'UTF-8');
    }

    public static function generateApiKey() {
        return 'sk_' . bin2hex(random_bytes(32));
    }

    public static function checkLoginThrottle($identifier) {
        $cacheFile = STORAGE_PATH . '/cache/login_attempts_' . md5($identifier) . '.txt';

        if (file_exists($cacheFile)) {
            $attempts = (int)file_get_contents($cacheFile);
            if ($attempts >= 5) {
                return false;
            }
        }

        return true;
    }

    public static function recordLoginAttempt($identifier, $success = false) {
        $cacheFile = STORAGE_PATH . '/cache/login_attempts_' . md5($identifier) . '.txt';

        if ($success) {
            if (file_exists($cacheFile)) {
                unlink($cacheFile);
            }
        } else {
            $attempts = file_exists($cacheFile) ? (int)file_get_contents($cacheFile) : 0;
            file_put_contents($cacheFile, $attempts + 1);
        }
    }

    public static function validateEmail($email) {
        return filter_var($email, FILTER_VALIDATE_EMAIL) !== false;
    }

    public static function validateDate($date, $format = 'Y-m-d') {
        $d = DateTime::createFromFormat($format, $date);
        return $d && $d->format($format) === $date;
    }

    public static function validateEnum($value, $allowed) {
        return in_array($value, $allowed, true);
    }

    public static function getClientIp() {
        if (isset($_SERVER['HTTP_CLIENT_IP'])) {
            return $_SERVER['HTTP_CLIENT_IP'];
        } elseif (isset($_SERVER['HTTP_X_FORWARDED_FOR'])) {
            return $_SERVER['HTTP_X_FORWARDED_FOR'];
        } else {
            return $_SERVER['REMOTE_ADDR'] ?? 'Unknown';
        }
    }

    public static function getUserAgent() {
        return $_SERVER['HTTP_USER_AGENT'] ?? 'Unknown';
    }
}
