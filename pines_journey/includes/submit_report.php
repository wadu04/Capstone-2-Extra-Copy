<?php
require_once 'config.php';
require_once 'auth.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isLoggedIn()) {
    $content_type = $_POST['content_type'];
    $content_id = (int)$_POST['content_id'];
    $report_type = $_POST['report_type'];
    $description = $_POST['description'];
    $reporter_id = $_SESSION['user_id'];

    try {
        // Start transaction
        $conn->begin_transaction();

        // Insert the report
        $report_query = "INSERT INTO reports (reporter_id, content_type, content_id, report_type, description) 
                        VALUES (?, ?, ?, ?, ?)";
        $stmt = $conn->prepare($report_query);
        $stmt->bind_param("isiss", $reporter_id, $content_type, $content_id, $report_type, $description);
        $stmt->execute();
        
        // Get the report ID
        $report_id = $conn->insert_id;

        // Create admin notification
        $notification_query = "INSERT INTO admin_notifications (report_id) VALUES (?)";
        $stmt = $conn->prepare($notification_query);
        $stmt->bind_param("i", $report_id);
        $stmt->execute();

        // Commit transaction
        $conn->commit();

        // Return success response
        echo json_encode(['status' => 'success', 'message' => 'Report submitted successfully']);
    } catch (Exception $e) {
        // Rollback transaction on error
        $conn->rollback();
        echo json_encode(['status' => 'error', 'message' => 'Error submitting report: ' . $e->getMessage()]);
    }
} else {
    echo json_encode(['status' => 'error', 'message' => 'Invalid request or user not logged in']);
}
