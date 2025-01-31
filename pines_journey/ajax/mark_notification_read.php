<?php
require_once '../includes/config.php';

if (!isset($_SESSION['user_id']) || !isset($_POST['notification_id'])) {
    echo json_encode(['success' => false]);
    exit;
}

$user_id = $_SESSION['user_id'];
$notification_id = intval($_POST['notification_id']);

$query = "UPDATE rewards SET is_read = 1 
          WHERE id = ? AND user_id = ?";
$stmt = $conn->prepare($query);
$stmt->bind_param('ii', $notification_id, $user_id);

echo json_encode(['success' => $stmt->execute()]);
