<?php
// FILE: /app/helpers/AIHelper.php

class AIHelper {
    private static $db;
    private static $apiKey;
    private static $model;

    private static function init() {
        if (self::$db === null) {
            self::$db = Database::getInstance();
            self::$apiKey = AI_API_KEY;
            self::$model = AI_MODEL;
        }
    }

    private static function callAI($prompt, $systemPrompt = '') {
        self::init();

        // For demo/development: return simulated responses if no API key
        if (empty(self::$apiKey) || self::$apiKey === 'your_openai_api_key_here') {
            return self::simulateAIResponse($prompt);
        }

        $data = array(
            'model' => self::$model,
            'messages' => array(
                array('role' => 'system', 'content' => $systemPrompt),
                array('role' => 'user', 'content' => $prompt)
            ),
            'max_tokens' => AI_MAX_TOKENS,
            'temperature' => AI_TEMPERATURE
        );

        $ch = curl_init('https://api.openai.com/v1/chat/completions');
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
        curl_setopt($ch, CURLOPT_HTTPHEADER, array(
            'Content-Type: application/json',
            'Authorization: Bearer ' . self::$apiKey
        ));

        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        if ($httpCode !== 200) {
            Logger::error("AI API Error: HTTP {$httpCode} - {$response}");
            return self::simulateAIResponse($prompt);
        }

        $result = json_decode($response, true);
        $tokensUsed = $result['usage']['total_tokens'] ?? 0;

        return array(
            'response' => $result['choices'][0]['message']['content'] ?? '',
            'tokens' => $tokensUsed
        );
    }

    private static function simulateAIResponse($prompt) {
        // Simulate AI responses for demo purposes
        return array(
            'response' => 'This is a simulated AI response. Please configure AI_API_KEY in .env for real AI features.',
            'tokens' => 50
        );
    }

    private static function trackTokenUsage($tenantId, $tokens) {
        if ($tenantId) {
            $sql = "UPDATE tenant_subscriptions
                    SET ai_tokens_used = ai_tokens_used + ?
                    WHERE tenant_id = ? AND status = 'active'";
            self::$db->query($sql, array($tokens, $tenantId));
        }
    }

    // AI Resume Analyzer
    public static function analyzeResume($resumeText, $jobDescription = '', $tenantId = null) {
        $prompt = "Analyze the following resume and provide:\n";
        $prompt .= "1. A score from 0-100\n";
        $prompt .= "2. List of key skills (comma separated)\n";
        $prompt .= "3. Brief experience summary (max 200 characters)\n";
        $prompt .= "4. If job description provided, match score\n\n";
        $prompt .= "Resume:\n{$resumeText}\n\n";

        if ($jobDescription) {
            $prompt .= "Job Description:\n{$jobDescription}\n";
        }

        $prompt .= "\nRespond in JSON format: {\"score\": 0-100, \"skills\": \"skill1, skill2\", \"summary\": \"text\", \"match_score\": 0-100}";

        $systemPrompt = "You are an expert HR resume analyzer. Provide concise, accurate analysis.";
        $result = self::callAI($prompt, $systemPrompt);

        self::trackTokenUsage($tenantId, $result['tokens']);

        // Parse JSON response or create default
        $analysis = json_decode($result['response'], true);
        if (!$analysis) {
            $analysis = array(
                'score' => 75,
                'skills' => 'PHP, MySQL, JavaScript',
                'summary' => 'Experienced developer with relevant technical skills',
                'match_score' => 70
            );
        }

        return $analysis;
    }

    // AI Candidate Ranking
    public static function rankCandidates($candidates, $jobDescription, $tenantId = null) {
        if (empty($candidates)) {
            return array();
        }

        $candidateList = '';
        foreach ($candidates as $idx => $candidate) {
            $candidateList .= "Candidate " . ($idx + 1) . ":\n";
            $candidateList .= "Name: {$candidate['first_name']} {$candidate['last_name']}\n";
            $candidateList .= "Score: {$candidate['ai_score']}\n";
            $candidateList .= "Summary: {$candidate['ai_summary']}\n\n";
        }

        $prompt = "Rank these candidates for the following job. Return ranked IDs as JSON array.\n\n";
        $prompt .= "Job Description:\n{$jobDescription}\n\n";
        $prompt .= $candidateList;
        $prompt .= "\nRespond with JSON: {\"ranked_ids\": [id1, id2, id3]}";

        $systemPrompt = "You are an expert recruiter. Rank candidates based on fit for the role.";
        $result = self::callAI($prompt, $systemPrompt);

        self::trackTokenUsage($tenantId, $result['tokens']);

        $ranking = json_decode($result['response'], true);
        return $ranking['ranked_ids'] ?? array_column($candidates, 'id');
    }

    // AI Job Description Generator
    public static function generateJobDescription($inputs, $tenantId = null) {
        $prompt = "Generate a professional job description with the following details:\n";
        $prompt .= "Job Title: " . ($inputs['title'] ?? 'Not specified') . "\n";
        $prompt .= "Department: " . ($inputs['department'] ?? 'Not specified') . "\n";
        $prompt .= "Employment Type: " . ($inputs['employment_type'] ?? 'Full-time') . "\n";
        $prompt .= "Location: " . ($inputs['location'] ?? 'Not specified') . "\n";
        $prompt .= "Key Requirements: " . ($inputs['requirements'] ?? 'Standard qualifications') . "\n";
        $prompt .= "\nGenerate: Overview, Responsibilities (5 bullet points), Requirements (5 bullet points), Benefits section.";

        $systemPrompt = "You are an expert HR professional creating compelling job descriptions.";
        $result = self::callAI($prompt, $systemPrompt);

        self::trackTokenUsage($tenantId, $result['tokens']);

        return $result['response'];
    }

    // AI Interview Questions Generator
    public static function generateInterviewQuestions($role, $level = 'mid', $tenantId = null) {
        $prompt = "Generate 10 interview questions for a {$level}-level {$role} position.\n";
        $prompt .= "Include: 3 technical questions, 3 behavioral questions, 2 situational questions, 2 culture-fit questions.\n";
        $prompt .= "Format as numbered list.";

        $systemPrompt = "You are an expert interviewer and HR professional.";
        $result = self::callAI($prompt, $systemPrompt);

        self::trackTokenUsage($tenantId, $result['tokens']);

        return $result['response'];
    }

    // AI Email Generator
    public static function generateEmail($type, $context, $tenantId = null) {
        $prompts = array(
            'rejection' => "Write a polite candidate rejection email for: {$context}",
            'offer' => "Write a professional job offer email for: {$context}",
            'interview_invite' => "Write an interview invitation email for: {$context}",
            'onboarding_welcome' => "Write a warm onboarding welcome email for: {$context}"
        );

        $prompt = $prompts[$type] ?? "Write a professional HR email regarding: {$context}";
        $prompt .= "\nInclude subject line. Be professional, empathetic, and clear.";

        $systemPrompt = "You are an expert HR communication specialist.";
        $result = self::callAI($prompt, $systemPrompt);

        self::trackTokenUsage($tenantId, $result['tokens']);

        return $result['response'];
    }

    // AI Performance Review Summary
    public static function generateReviewSummary($reviewData, $tenantId = null) {
        $prompt = "Create a performance review summary based on:\n";
        $prompt .= "Employee: {$reviewData['employee_name']}\n";
        $prompt .= "Period: {$reviewData['period']}\n";
        $prompt .= "Self Review: {$reviewData['self_review']}\n";
        $prompt .= "Manager Review: {$reviewData['manager_review']}\n";
        $prompt .= "Achievements: {$reviewData['achievements']}\n";
        $prompt .= "\nProvide: Overall summary (150 words), Key strengths (3), Areas for improvement (3), Recommendations.";

        $systemPrompt = "You are an expert HR performance analyst.";
        $result = self::callAI($prompt, $systemPrompt);

        self::trackTokenUsage($tenantId, $result['tokens']);

        return $result['response'];
    }

    // AI HR Question Answering
    public static function answerHRQuestion($tenantId, $question) {
        $prompt = "Answer this HR-related question professionally: {$question}\n";
        $prompt .= "Keep answer concise and actionable.";

        $systemPrompt = "You are an expert HR consultant providing helpful, accurate advice.";
        $result = self::callAI($prompt, $systemPrompt);

        self::trackTokenUsage($tenantId, $result['tokens']);

        return $result['response'];
    }

    // AI Policy Generator
    public static function generatePolicy($topic, $tenantId = null) {
        $prompt = "Generate a comprehensive company policy document for: {$topic}\n";
        $prompt .= "Include: Purpose, Scope, Policy Statement, Procedures, Responsibilities.";

        $systemPrompt = "You are an expert HR policy writer.";
        $result = self::callAI($prompt, $systemPrompt);

        self::trackTokenUsage($tenantId, $result['tokens']);

        return $result['response'];
    }

    // AI Employee Summary
    public static function summarizeEmployeeHistory($employeeData, $tenantId = null) {
        $prompt = "Summarize this employee's history:\n";
        $prompt .= json_encode($employeeData, JSON_PRETTY_PRINT);
        $prompt .= "\nProvide: Career progression, key achievements, tenure highlights.";

        $systemPrompt = "You are an expert HR analyst.";
        $result = self::callAI($prompt, $systemPrompt);

        self::trackTokenUsage($tenantId, $result['tokens']);

        return $result['response'];
    }

    // AI Attrition Risk Prediction
    public static function predictAttritionRisk($employeeData, $tenantId = null) {
        $prompt = "Analyze attrition risk for employee:\n";
        $prompt .= "Tenure: {$employeeData['tenure_months']} months\n";
        $prompt .= "Performance Rating: {$employeeData['performance_rating']}\n";
        $prompt .= "Salary Growth: {$employeeData['salary_growth']}%\n";
        $prompt .= "Promotion History: {$employeeData['promotions']}\n";
        $prompt .= "\nProvide risk level (Low/Medium/High) and key factors.";

        $systemPrompt = "You are an expert HR analytics professional.";
        $result = self::callAI($prompt, $systemPrompt);

        self::trackTokenUsage($tenantId, $result['tokens']);

        return $result['response'];
    }

    // AI Feedback Enhancement
    public static function enhanceFeedback($rawFeedback, $tenantId = null) {
        $prompt = "Rewrite this performance feedback to be more professional, constructive, and empathetic:\n\n";
        $prompt .= $rawFeedback;

        $systemPrompt = "You are an expert HR communication coach.";
        $result = self::callAI($prompt, $systemPrompt);

        self::trackTokenUsage($tenantId, $result['tokens']);

        return $result['response'];
    }
}
