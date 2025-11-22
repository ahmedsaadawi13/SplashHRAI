<?php
// FILE: /tests/CandidateTest.php

require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../app/core/Database.php';
require_once __DIR__ . '/../app/core/Model.php';
require_once __DIR__ . '/../app/models/Candidate.php';

echo "Testing Candidate Model...\n";

try {
    $candidateModel = new Candidate();

    // Test finding candidates by job
    $candidates = $candidateModel->getByJob(1, 1); // job_id = 1, tenant_id = 1
    echo "✓ Found " . count($candidates) . " candidates for job 1\n";

    // Test count
    $count = $candidateModel->count(1);
    echo "✓ Total candidates count: " . $count . "\n";

    echo "\n✓ All Candidate model tests passed\n";

} catch (Exception $e) {
    echo "✗ Candidate test failed: " . $e->getMessage() . "\n";
    exit(1);
}
