<?php
// FILE: /app/helpers/Logger.php

class Logger {

    public static function log($message, $level = 'info') {
        $logFile = LOG_PATH . '/app.log';
        $timestamp = date('Y-m-d H:i:s');
        $logMessage = "[{$timestamp}] [{$level}] {$message}" . PHP_EOL;

        if (!is_dir(LOG_PATH)) {
            mkdir(LOG_PATH, 0755, true);
        }

        file_put_contents($logFile, $logMessage, FILE_APPEND);
    }

    public static function error($message) {
        self::log($message, 'ERROR');
    }

    public static function warning($message) {
        self::log($message, 'WARNING');
    }

    public static function info($message) {
        self::log($message, 'INFO');
    }

    public static function debug($message) {
        if (APP_ENV === 'development') {
            self::log($message, 'DEBUG');
        }
    }

    public static function security($message) {
        $logFile = LOG_PATH . '/security.log';
        $timestamp = date('Y-m-d H:i:s');
        $ip = Security::getClientIp();
        $logMessage = "[{$timestamp}] [IP: {$ip}] {$message}" . PHP_EOL;

        if (!is_dir(LOG_PATH)) {
            mkdir(LOG_PATH, 0755, true);
        }

        file_put_contents($logFile, $logMessage, FILE_APPEND);
    }

    public static function email($to, $subject, $body) {
        $logFile = LOG_PATH . '/email_log.txt';
        $timestamp = date('Y-m-d H:i:s');
        $logMessage = str_repeat('-', 50) . PHP_EOL;
        $logMessage .= "Timestamp: {$timestamp}" . PHP_EOL;
        $logMessage .= "To: {$to}" . PHP_EOL;
        $logMessage .= "Subject: {$subject}" . PHP_EOL;
        $logMessage .= "Body: {$body}" . PHP_EOL;
        $logMessage .= str_repeat('-', 50) . PHP_EOL . PHP_EOL;

        if (!is_dir(LOG_PATH)) {
            mkdir(LOG_PATH, 0755, true);
        }

        file_put_contents($logFile, $logMessage, FILE_APPEND);
    }
}
