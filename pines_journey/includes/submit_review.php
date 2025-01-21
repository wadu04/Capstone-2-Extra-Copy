<?php
require_once 'config.php';
// Removed duplicate session_start() since it's already in config.php

if (!isset($_SESSION['user_id'])) {
    header('Location: ../pages/login.php');
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $user_id = $_SESSION['user_id'];
    $spot_id = $_POST['spot_id'];
    $rating = $_POST['rating'];
    $comment = !empty($_POST['comment']) ? trim($_POST['comment']) : '';

    // Check if user has already reviewed this spot
    $check_sql = "SELECT review_id FROM reviews WHERE user_id = ? AND spot_id = ?";
    $check_stmt = $conn->prepare($check_sql);
    $check_stmt->bind_param("ii", $user_id, $spot_id);
    $check_stmt->execute();
    $existing_review = $check_stmt->get_result()->fetch_assoc();

    if ($existing_review) {
        // Update existing review
        $sql = "UPDATE reviews SET rating = ?, comment = ?, created_at = CURRENT_TIMESTAMP WHERE user_id = ? AND spot_id = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("isii", $rating, $comment, $user_id, $spot_id);
    } else {
        // Insert new review
        $sql = "INSERT INTO reviews (user_id, spot_id, rating, comment) VALUES (?, ?, ?, ?)";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("iiis", $user_id, $spot_id, $rating, $comment);
    }

    if ($stmt->execute()) {
        header('Location: ../pages/spot-details.php?id=' . $spot_id . '&review=success');
        exit();
    } else {
        header('Location: ../pages/spot-details.php?id=' . $spot_id . '&review=error');
        exit();
    }
} else {
    header('Location: ../pages/spots.php');
    exit();
}
