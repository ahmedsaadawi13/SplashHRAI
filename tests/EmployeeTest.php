<?php
// FILE: /tests/EmployeeTest.php

require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../app/core/Database.php';
require_once __DIR__ . '/../app/core/Model.php';
require_once __DIR__ . '/../app/models/Employee.php';

echo "Testing Employee Model...\n";

try {
    $employeeModel = new Employee();

    // Test finding all employees
    $employees = $employeeModel->findAll(1); // tenant_id = 1
    echo "✓ Found " . count($employees) . " employees for tenant 1\n";

    // Test employee code generation
    $employeeCode = $employeeModel->generateEmployeeCode(1);
    echo "✓ Generated employee code: " . $employeeCode . "\n";

    // Test search
    $searchResults = $employeeModel->search(1, array('employment_status' => 'active'));
    echo "✓ Search found " . count($searchResults) . " active employees\n";

    echo "\n✓ All Employee model tests passed\n";

} catch (Exception $e) {
    echo "✗ Employee test failed: " . $e->getMessage() . "\n";
    exit(1);
}
