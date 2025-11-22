<?php
// FILE: /public/index.php

session_start();

// Load configuration
require_once __DIR__ . '/../config/config.php';

// Autoload core classes
spl_autoload_register(function ($class) {
    $paths = array(
        APP_PATH . '/core/' . $class . '.php',
        APP_PATH . '/models/' . $class . '.php',
        APP_PATH . '/helpers/' . $class . '.php'
    );

    foreach ($paths as $path) {
        if (file_exists($path)) {
            require_once $path;
            return;
        }
    }
});

// Create necessary directories if they don't exist
$directories = array(
    STORAGE_PATH,
    UPLOAD_PATH,
    LOG_PATH,
    STORAGE_PATH . '/cache'
);

foreach ($directories as $dir) {
    if (!is_dir($dir)) {
        mkdir($dir, 0755, true);
    }
}

// Start routing
try {
    $router = new Router();
} catch (Exception $e) {
    if (APP_ENV === 'development') {
        echo "Error: " . $e->getMessage();
    } else {
        echo "An error occurred. Please try again later.";
    }
    Logger::error("Router Error: " . $e->getMessage());
}
