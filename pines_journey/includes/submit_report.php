<?php
require_once 'config.php';
require_once 'auth.php';

// Enable error reporting
error_reporting(E_ALL);
ini_set('display_errors', 1);

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!isLoggedIn()) {
        echo json_encode(['status' => 'error', 'message' => 'User not logged in']);
        exit;
    }

    // Log the POST data for debugging
    error_log("POST data: " . print_r($_POST, true));

    $content_type = $_POST['content_type'] ?? '';
    $content_id = isset($_POST['content_id']) ? (int)$_POST['content_id'] : 0;
    $report_type = $_POST['report_type'] ?? '';
    $description = $_POST['description'] ?? '';
    $reporter_id = $_SESSION['user_id'];

    // Validate required fields
    if (empty($content_type) || empty($content_id) || empty($report_type) || empty($description)) {
        echo json_encode([
            'status' => 'error', 
            'message' => 'Missing required fields',
            'debug' => [
                'content_type' => $content_type,
                'content_id' => $content_id,
                'report_type' => $report_type,
                'description' => $description,
                'reporter_id' => $reporter_id
            ]
        ]);
        exit;
    }

    try {
        // Start transaction
        $conn->begin_transaction();

        // Insert the report
        $report_query = "INSERT INTO reports (reporter_id, content_type, content_id, report_type, description) 
                        VALUES (?, ?, ?, ?, ?)";
        $stmt = $conn->prepare($report_query);
        $stmt->bind_param("isiss", $reporter_id, $content_type, $content_id, $report_type, $description);
        
        if (!$stmt->execute()) {
            throw new Exception("Database error: " . $stmt->error);
        }
        
        // Get the report ID
        $report_id = $conn->insert_id;

        // Create admin notification
        $notification_query = "INSERT INTO admin_notifications (report_id) VALUES (?)";
        $stmt = $conn->prepare($notification_query);
        $stmt->bind_param("i", $report_id);
        
        if (!$stmt->execute()) {
            throw new Exception("Database error: " . $stmt->error);
        }

        // Commit transaction
        $conn->commit();

        // Return success response
        echo json_encode(['status' => 'success', 'message' => 'Report submitted successfully']);
    } catch (Exception $e) {
        // Rollback transaction on error
        $conn->rollback();
        error_log("Report submission error: " . $e->getMessage());
        echo json_encode([
            'status' => 'error', 
            'message' => 'Error submitting report: ' . $e->getMessage(),
            'debug' => [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]
        ]);
    }
} else {
    echo json_encode(['status' => 'error', 'message' => 'Invalid request method']);
}
