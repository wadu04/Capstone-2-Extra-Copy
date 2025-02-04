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
    
    // Validate rating
    if (!isset($_POST['rating']) || empty($_POST['rating'])) {
        $_SESSION['error'] = "Please select a star rating for your review.";
        header('Location: ../pages/spot-details.php?id=' . $spot_id);
        exit();
    }
    
    $rating = $_POST['rating'];
    $comment = !empty($_POST['comment']) ? trim($_POST['comment']) : '';
    $image_url = null;

    // Handle image upload if present
    if (isset($_FILES['review_image']) && $_FILES['review_image']['error'] === UPLOAD_ERR_OK) {
        $upload_dir = '../uploads/review_pic/';
        
        // Create directory if it doesn't exist
        if (!file_exists($upload_dir)) {
            mkdir($upload_dir, 0777, true);
        }

        // Generate unique filename
        $file_extension = strtolower(pathinfo($_FILES['review_image']['name'], PATHINFO_EXTENSION));
        $allowed_extensions = ['jpg', 'jpeg', 'png', 'gif'];

        if (in_array($file_extension, $allowed_extensions)) {
            $image_url = uniqid('review_') . '.' . $file_extension;
            $target_path = $upload_dir . $image_url;

            // Move uploaded file
            if (move_uploaded_file($_FILES['review_image']['tmp_name'], $target_path)) {
                // Success - continue with database insert
            } else {
                $_SESSION['error'] = "Failed to upload image.";
                header('Location: ../pages/spot-details.php?id=' . $spot_id);
                exit();
            }
        } else {
            $_SESSION['error'] = "Invalid file type. Only JPG, JPEG, PNG, and GIF are allowed.";
            header('Location: ../pages/spot-details.php?id=' . $spot_id);
            exit();
        }
    }

    // Check if user has already reviewed this spot
    $check_sql = "SELECT review_id FROM reviews WHERE user_id = ? AND spot_id = ?";
    $check_stmt = $conn->prepare($check_sql);
    $check_stmt->bind_param("ii", $user_id, $spot_id);
    $check_stmt->execute();
    $existing_review = $check_stmt->get_result()->fetch_assoc();

    if ($existing_review) {
        // Update existing review
        $sql = "UPDATE reviews SET rating = ?, comment = ?, image_url = ?, created_at = CURRENT_TIMESTAMP WHERE user_id = ? AND spot_id = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("isssi", $rating, $comment, $image_url, $user_id, $spot_id);
    } else {
        // Insert new review
        $sql = "INSERT INTO reviews (user_id, spot_id, rating, comment, image_url) VALUES (?, ?, ?, ?, ?)";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("iiiss", $user_id, $spot_id, $rating, $comment, $image_url);
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
