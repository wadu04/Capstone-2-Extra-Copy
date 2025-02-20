<?php
require_once 'config.php';

header('Content-Type: application/json');

if (!isLoggedIn()) {
    echo json_encode(['success' => false, 'message' => 'Please login first']);
    exit();
}

// Get JSON data
$data = json_decode(file_get_contents('php://input'), true);
$review_id = (int)$data['review_id'];
$user_id = $_SESSION['user_id'];

// Check if already marked as helpful
$stmt = $conn->prepare("SELECT helpful_id FROM review_helpful WHERE user_id = ? AND review_id = ?");
$stmt->bind_param("ii", $user_id, $review_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows > 0) {
    // Remove helpful mark
    $stmt = $conn->prepare("DELETE FROM review_helpful WHERE user_id = ? AND review_id = ?");
    $stmt->bind_param("ii", $user_id, $review_id);
    $action = 'removed';
} else {
    // Add helpful mark
    $stmt = $conn->prepare("INSERT INTO review_helpful (user_id, review_id) VALUES (?, ?)");
    $stmt->bind_param("ii", $user_id, $review_id);
    $action = 'added';
}

if ($stmt->execute()) {
    // Get updated count
    $stmt = $conn->prepare("SELECT COUNT(*) as helpful_count FROM review_helpful WHERE review_id = ?");
    $stmt->bind_param("i", $review_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $count = $result->fetch_assoc()['helpful_count'];
    
    echo json_encode([
        'success' => true, 
        'action' => $action,
        'helpful_count' => $count
    ]);
} else {
    echo json_encode(['success' => false, 'message' => 'Database error']);
}
?>
