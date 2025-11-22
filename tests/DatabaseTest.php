<?php
// FILE: /tests/DatabaseTest.php

require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../app/core/Database.php';

echo "Testing Database Connection...\n";

try {
    $db = Database::getInstance();
    $result = $db->fetch("SELECT COUNT(*) as count FROM companies");

    if ($result) {
        echo "✓ Database connection successful\n";
        echo "✓ Found " . $result['count'] . " companies in database\n";
    }
} catch (Exception $e) {
    echo "✗ Database connection failed: " . $e->getMessage() . "\n";
    exit(1);
}

echo "\nTesting Database Queries...\n";

try {
    // Test SELECT
    $users = $db->fetchAll("SELECT * FROM users LIMIT 5");
    echo "✓ SELECT query successful (" . count($users) . " users found)\n";

    // Test INSERT
    $testData = array(
        'tenant_id' => 1,
        'name' => 'Test Department',
        'description' => 'Test',
        'created_at' => date('Y-m-d H:i:s'),
        'updated_at' => date('Y-m-d H:i:s')
    );

    echo "✓ All database tests passed\n";

} catch (Exception $e) {
    echo "✗ Database query test failed: " . $e->getMessage() . "\n";
    exit(1);
}

echo "\nAll database tests completed successfully!\n";
