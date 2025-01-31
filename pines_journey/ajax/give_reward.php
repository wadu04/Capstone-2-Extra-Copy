<?php
require_once '../includes/config.php';

header('Content-Type: application/json');

if (!isset($_POST['user_id']) || !isset($_POST['title']) || !isset($_POST['description']) || !isset($_FILES['image'])) {
    echo json_encode(['success' => false, 'message' => 'Missing required fields']);
    exit;
}

$user_id = intval($_POST['user_id']);
$title = $_POST['title'];
$description = $_POST['description'];
$admin_id = $_SESSION['user_id']; // Current admin's user_id

// Handle image upload
$target_dir = "../uploads/rewards/";
if (!file_exists($target_dir)) {
    mkdir($target_dir, 0777, true);
}

$file_extension = strtolower(pathinfo($_FILES["image"]["name"], PATHINFO_EXTENSION));
$new_filename = uniqid() . '.' . $file_extension;
$target_file = $target_dir . $new_filename;

// Check file type
$allowed_types = ['jpg', 'jpeg', 'png', 'gif'];
if (!in_array($file_extension, $allowed_types)) {
    echo json_encode(['success' => false, 'message' => 'Invalid file type. Only JPG, JPEG, PNG & GIF files are allowed.']);
    exit;
}

// Move uploaded file
if (!move_uploaded_file($_FILES["image"]["tmp_name"], $target_file)) {
    echo json_encode(['success' => false, 'message' => 'Error uploading file']);
    exit;
}

// Insert reward into database
$query = "INSERT INTO rewards (title, description, image, user_id, admin_id) VALUES (?, ?, ?, ?, ?)";
$stmt = $conn->prepare($query);
$image_path = 'uploads/rewards/' . $new_filename;
$stmt->bind_param('sssii', $title, $description, $image_path, $user_id, $admin_id);

if ($stmt->execute()) {
    echo json_encode(['success' => true]);
} else {
    unlink($target_file); // Remove uploaded file if database insert fails
    echo json_encode(['success' => false, 'message' => 'Error saving reward']);
}
