<?php
session_start();
require_once '../includes/config.php';

header('Content-Type: application/json');

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'message' => 'Please log in to scan QR codes']);
    exit();
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST' || !isset($_POST['qr_content'])) {
    echo json_encode(['success' => false, 'message' => 'Invalid request']);
    exit();
}

$user_id = $_SESSION['user_id'];
$qr_content = $_POST['qr_content'];

// Check if QR code exists and is valid
$stmt = $conn->prepare("SELECT * FROM qr_codes WHERE content = ?");
$stmt->bind_param("s", $qr_content);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    echo json_encode(['success' => false, 'message' => 'Invalid QR code']);
    exit();
}

$qr_code = $result->fetch_assoc();

// Check if user has already scanned this QR code
$stmt = $conn->prepare("SELECT * FROM user_scans WHERE user_id = ? AND qr_content = ?");
$stmt->bind_param("is", $user_id, $qr_content);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows > 0) {
    echo json_encode(['success' => false, 'message' => 'You have already scanned this QR code']);
    exit();
}

// Start transaction
$conn->begin_transaction();

try {
    // Record the scan
    $stmt = $conn->prepare("INSERT INTO user_scans (user_id, qr_content, points_earned) VALUES (?, ?, ?)");
    $stmt->bind_param("isi", $user_id, $qr_content, $qr_code['points']);
    $stmt->execute();

    // Update or insert into leaderboard
    $stmt = $conn->prepare("INSERT INTO leaderboard (user_id, total_points) 
                           VALUES (?, ?) 
                           ON DUPLICATE KEY UPDATE total_points = total_points + ?");
    $stmt->bind_param("iii", $user_id, $qr_code['points'], $qr_code['points']);
    $stmt->execute();

    $conn->commit();
    echo json_encode([
        'success' => true, 
        'message' => 'Congratulations! You earned ' . $qr_code['points'] . ' points!'
    ]);
} catch (Exception $e) {
    $conn->rollback();
    echo json_encode(['success' => false, 'message' => 'Error processing QR code: ' . $e->getMessage()]);
}
