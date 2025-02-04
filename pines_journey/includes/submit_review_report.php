<?php
// Disable error reporting to prevent HTML errors in JSON response
error_reporting(0);
ini_set('display_errors', 0);

// Start session and include config at the beginning
session_start();
require_once 'config.php';

// Set JSON content type header
header('Content-Type: application/json');

// Function to return JSON response
function sendJsonResponse($success, $message) {
    echo json_encode(['success' => $success, 'message' => $message]);
    exit();
}

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    sendJsonResponse(false, 'Please login to report a review');
}

// Check request method
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    sendJsonResponse(false, 'Invalid request method');
}

// Validate required fields
if (!isset($_POST['review_id']) || !isset($_POST['report_type']) || !isset($_POST['description'])) {
    sendJsonResponse(false, 'Missing required fields');
}

// Sanitize and validate input
$review_id = (int)$_POST['review_id'];
$report_type = $_POST['report_type'];
$description = trim($_POST['description']);
$reporter_id = (int)$_SESSION['user_id'];

// Validate review exists
$check_review = $conn->prepare("SELECT review_id FROM reviews WHERE review_id = ?");
$check_review->bind_param("i", $review_id);
$check_review->execute();
$review_result = $check_review->get_result();

if ($review_result->num_rows === 0) {
    sendJsonResponse(false, 'Review not found');
}

// Check if user has already reported this review
$check_existing = $conn->prepare("SELECT report_id FROM reports WHERE reporter_id = ? AND content_type = 'review' AND content_id = ?");
$check_existing->bind_param("ii", $reporter_id, $review_id);
$check_existing->execute();
$existing_result = $check_existing->get_result();

if ($existing_result->num_rows > 0) {
    sendJsonResponse(false, 'You have already reported this review');
}

try {
    // Start transaction
    $conn->begin_transaction();

    // Insert the report
    $sql = "INSERT INTO reports (reporter_id, content_type, content_id, report_type, description, status) 
            VALUES (?, 'review', ?, ?, ?, 'pending')";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("iiss", $reporter_id, $review_id, $report_type, $description);
    $stmt->execute();

    $report_id = $conn->insert_id;

    // Create admin notification
    $notification_sql = "INSERT INTO admin_notifications (report_id, is_read) VALUES (?, 0)";
    $stmt = $conn->prepare($notification_sql);
    $stmt->bind_param("i", $report_id);
    $stmt->execute();

    // Commit transaction
    $conn->commit();

    sendJsonResponse(true, '');
} catch (Exception $e) {
    // Rollback transaction on error
    $conn->rollback();
    sendJsonResponse(false, '');
}