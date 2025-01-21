<?php
// Enable error reporting for debugging
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Start output buffering
ob_start();

// Include database connection
require_once '../includes/config.php';

try {
    // Check database connection
    if (!isset($conn)) {
        throw new Exception("Database connection not established");
    }

    // Test database connection
    if ($conn->connect_error) {
        throw new Exception("Database connection failed: " . $conn->connect_error);
    }

    // Check if table exists
    $result = $conn->query("SHOW TABLES LIKE 'quiz_questions'");
    if ($result->num_rows === 0) {
        throw new Exception("quiz_questions table does not exist");
    }

    if ($_SERVER['REQUEST_METHOD'] === 'GET') {
        // Prepare and execute query
        $sql = "SELECT * FROM quiz_questions ORDER BY RAND() LIMIT 30";
        $result = $conn->query($sql);
        
        if (!$result) {
            throw new Exception("Query failed: " . $conn->error);
        }
        
        $questions = [];
        while ($row = $result->fetch_assoc()) {
            $questions[] = $row;
        }
        
        if (empty($questions)) {
            throw new Exception("No questions found in database");
        }
        
        // Format questions
        $formatted_questions = [];
        foreach ($questions as $q) {
            $formatted_questions[] = [
                'id' => $q['question_id'],
                'question' => $q['question'],
                'options' => [
                    $q['correct_answer'],
                    $q['option1'],
                    $q['option2'],
                    $q['option3']
                ],
                'correct_answer' => $q['correct_answer']
            ];
        }
        
        // Shuffle options for each question
        foreach ($formatted_questions as &$q) {
            shuffle($q['options']);
        }
        
        $response = [
            'success' => true,
            'questions' => $formatted_questions
        ];
    } else {
        throw new Exception("Invalid request method");
    }
} catch (Exception $e) {
    $response = [
        'success' => false,
        'error' => $e->getMessage(),
        'trace' => $e->getTraceAsString()
    ];
}

// Clear any previous output
ob_clean();

// Set JSON header
header('Content-Type: application/json');

// Ensure clean JSON output
echo json_encode($response, JSON_PRETTY_PRINT);
exit();
