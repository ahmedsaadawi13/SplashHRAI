<?php
// FILE: /tests/AIHelperTest.php

require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../app/core/Database.php';
require_once __DIR__ . '/../app/helpers/AIHelper.php';
require_once __DIR__ . '/../app/helpers/Logger.php';

echo "Testing AIHelper...\n";

try {
    // Test Resume Analysis
    $resumeText = "John Doe - Senior PHP Developer with 8 years experience in building web applications using PHP, MySQL, JavaScript, and modern frameworks.";
    $jobDescription = "Looking for a senior PHP developer with MySQL and JavaScript experience.";

    echo "Testing AI Resume Analysis...\n";
    $analysis = AIHelper::analyzeResume($resumeText, $jobDescription, 1);

    echo "✓ Resume analysis completed\n";
    echo "  - AI Score: " . $analysis['score'] . "/100\n";
    echo "  - Skills: " . $analysis['skills'] . "\n";
    echo "  - Summary: " . $analysis['summary'] . "\n";
    echo "  - Match Score: " . $analysis['match_score'] . "/100\n";

    // Test Job Description Generation
    echo "\nTesting AI Job Description Generator...\n";
    $inputs = array(
        'title' => 'Full Stack Developer',
        'department' => 'Engineering',
        'employment_type' => 'full_time',
        'location' => 'San Francisco'
    );

    $description = AIHelper::generateJobDescription($inputs, 1);
    echo "✓ Job description generated (" . strlen($description) . " characters)\n";

    // Test Interview Questions
    echo "\nTesting AI Interview Questions Generator...\n";
    $questions = AIHelper::generateInterviewQuestions('PHP Developer', 'senior', 1);
    echo "✓ Interview questions generated (" . strlen($questions) . " characters)\n";

    echo "\n✓ All AIHelper tests passed\n";
    echo "\nNote: If AI_API_KEY is not configured, these are simulated responses.\n";
    echo "Configure AI_API_KEY in .env for real AI features.\n";

} catch (Exception $e) {
    echo "✗ AIHelper test failed: " . $e->getMessage() . "\n";
    exit(1);
}
