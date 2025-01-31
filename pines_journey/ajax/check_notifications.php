<?php
require_once '../includes/config.php';

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['unread_count' => 0, 'notifications' => []]);
    exit;
}

$user_id = $_SESSION['user_id'];

// Get unread count
$count_query = "SELECT COUNT(*) as unread_count FROM rewards WHERE user_id = ? AND is_read = 0";
$stmt = $conn->prepare($count_query);
$stmt->bind_param('i', $user_id);
$stmt->execute();
$unread_count = $stmt->get_result()->fetch_assoc()['unread_count'];

// Get notifications
$notifications_query = "SELECT r.*, u.username as admin_username 
                       FROM rewards r 
                       JOIN users u ON r.admin_id = u.user_id 
                       WHERE r.user_id = ? 
                       ORDER BY r.created_at DESC 
                       LIMIT 10";
$stmt = $conn->prepare($notifications_query);
$stmt->bind_param('i', $user_id);
$stmt->execute();
$notifications = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);

echo json_encode([
    'unread_count' => $unread_count,
    'notifications' => $notifications
]);
