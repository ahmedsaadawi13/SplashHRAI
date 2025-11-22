<?php
// FILE: /tests/run_all_tests.php

echo "===============================================\n";
echo "  SplashHRAI - Test Suite\n";
echo "===============================================\n\n";

$tests = array(
    'DatabaseTest.php',
    'EmployeeTest.php',
    'CandidateTest.php',
    'AIHelperTest.php'
);

$passed = 0;
$failed = 0;

foreach ($tests as $test) {
    echo "Running: " . $test . "\n";
    echo str_repeat('-', 47) . "\n";

    $output = array();
    $return_var = 0;

    exec("php " . __DIR__ . "/" . $test . " 2>&1", $output, $return_var);

    foreach ($output as $line) {
        echo $line . "\n";
    }

    if ($return_var === 0) {
        $passed++;
    } else {
        $failed++;
    }

    echo "\n";
}

echo "===============================================\n";
echo "Test Results:\n";
echo "  Passed: " . $passed . "\n";
echo "  Failed: " . $failed . "\n";
echo "===============================================\n";

if ($failed > 0) {
    exit(1);
}
