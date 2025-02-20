<?php
session_start();
require_once '../includes/config.php';

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$user_id = $_SESSION['user_id'];

// Handle profile update
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['update_profile'])) {
    $username = trim($_POST['username'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $current_password = trim($_POST['current_password'] ?? '');
    $new_password = trim($_POST['new_password'] ?? '');
    $confirm_password = trim($_POST['confirm_password'] ?? '');
    
    $conn->begin_transaction();

    try {
        // Fetch current user data
        $stmt = $conn->prepare("SELECT username, email FROM users WHERE user_id = ?");
        $stmt->bind_param("i", $user_id);
        $stmt->execute();
        $current_data = $stmt->get_result()->fetch_assoc();

        // Update username if changed
        if (!empty($username) && $username !== $current_data['username']) {
            $stmt = $conn->prepare("UPDATE users SET username = ? WHERE user_id = ?");
            $stmt->bind_param("si", $username, $user_id);
            $stmt->execute();
        }

        // Update email if changed
        if (!empty($email) && $email !== $current_data['email']) {
            $stmt = $conn->prepare("UPDATE users SET email = ? WHERE user_id = ?");
            $stmt->bind_param("si", $email, $user_id);
            $stmt->execute();
        }

        // Handle password change if any password field is filled
        if (!empty($current_password) || !empty($new_password) || !empty($confirm_password)) {
            // Check if all password fields are filled
            if (empty($current_password) || empty($new_password) || empty($confirm_password)) {
                throw new Exception("All password fields are required when changing password.");
            }

            // Verify current password
            $stmt = $conn->prepare("SELECT password FROM users WHERE user_id = ?");
            $stmt->bind_param("i", $user_id);
            $stmt->execute();
            $result = $stmt->get_result()->fetch_assoc();

            if (!password_verify($current_password, $result['password'])) {
                throw new Exception("Current password is incorrect.");
            }

            if ($new_password !== $confirm_password) {
                throw new Exception("New passwords do not match.");
            }

            if (!preg_match("/^(?=.*[A-Za-z])(?=.*\d)[A-Za-z\d]{8,}$/", $new_password)) {
                throw new Exception("Password must be at least 8 characters long and contain at least one letter and one number.");
            }

            $hashed_password = password_hash($new_password, PASSWORD_DEFAULT);
            $stmt = $conn->prepare("UPDATE users SET password = ? WHERE user_id = ?");
            $stmt->bind_param("si", $hashed_password, $user_id);
            $stmt->execute();
        }

        // Handle profile picture upload if provided
        if (isset($_FILES['profile_picture']) && $_FILES['profile_picture']['error'] == 0) {
            $target_dir = "../uploads/default_pic/";
            if (!file_exists($target_dir)) {
                mkdir($target_dir, 0777, true);
            }
            
            $file_extension = strtolower(pathinfo($_FILES["profile_picture"]["name"], PATHINFO_EXTENSION));
            $target_file = $target_dir . "user_" . $user_id . "_" . time() . "." . $file_extension;
            
            $allowed_types = array('jpg', 'jpeg', 'png', 'gif');
            if (!in_array($file_extension, $allowed_types)) {
                throw new Exception("Only JPG, JPEG, PNG & GIF files are allowed.");
            }

            if ($_FILES["profile_picture"]["size"] > 5000000) {
                throw new Exception("File is too large. Maximum size is 5MB.");
            }

            if (move_uploaded_file($_FILES["profile_picture"]["tmp_name"], $target_file)) {
                $relative_path = str_replace("../", "/", $target_file);
                $stmt = $conn->prepare("UPDATE users SET profile_picture = ? WHERE user_id = ?");
                $stmt->bind_param("si", $relative_path, $user_id);
                $stmt->execute();
                $_SESSION['profile_picture'] = $relative_path;
            } else {
                throw new Exception("Failed to upload profile picture.");
            }
        }

        $conn->commit();
        $_SESSION['success_message'] = "Changes saved successfully!";
        header("Location: profile.php");
        exit();

    } catch (Exception $e) {
        $conn->rollback();
        $_SESSION['error_message'] = $e->getMessage();
        header("Location: profile.php");
        exit();
    }
}

// Fetch user data
$stmt = $conn->prepare("SELECT username, email, profile_picture FROM users WHERE user_id = ?");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$user_result = $stmt->get_result()->fetch_assoc();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Profile - Pines Journey</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="../assets/css/style.css">
    <style>
        .profile-picture {
            width: 200px;
            height: 200px;
            object-fit: cover;
            border-radius: 50%;
            margin: 0 auto 20px;
            display: block;
            border: 3px solid #e9ecef;
            box-shadow: 0 0 10px rgba(0,0,0,0.1);
        }
        .profile-section {
            background: #fff;
            padding: 40px;
            border-radius: 15px;
            box-shadow: 0 5px 15px rgba(0,0,0,0.05);
            margin-bottom: 20px;
        }
        .preview-image {
            max-width: 150px;
            margin: 10px 0;
            display: none;
        }
        .form-text {
            font-size: 0.875rem;
            color: #6c757d;
            margin-top: 4px;
        }
        .btn-change {
            padding: 4px 12px;
            font-size: 0.85rem;
            position: absolute;
            right: 0;
            top: 50%;
            transform: translateY(-50%);
            background-color: transparent;
            border: 1px solid #007bff;
            color: #007bff;
            border-radius: 20px;
            transition: all 0.3s ease;
        }
        .btn-change:hover {
            background-color: #007bff;
            color: white;
        }
        .form-group-wrapper {
            position: relative;
            margin-bottom: 1.5rem;
            padding-right: 80px;
        }
        .password-dots {
            letter-spacing: 3px;
            font-weight: bold;
            color: #495057;
        }
        .password-change-form {
            display: none;
            margin-top: 1rem;
            background: #f8f9fa;
            padding: 15px;
            border-radius: 8px;
            border: 1px solid #e9ecef;
        }
        .btn-save {
            background-color: #007bff;
            color: white;
            padding: 8px 24px;
            border-radius: 20px;
            transition: all 0.3s ease;
        }
        .btn-save:hover {
            background-color: #0056b3;
            transform: translateY(-1px);
        }
        .btn-cancel {
            background-color: white;
            border: 1px solid #dee2e6;
            padding: 8px 24px;
            border-radius: 20px;
            margin-right: 10px;
            transition: all 0.3s ease;
        }
        .btn-cancel:hover {
            background-color: #f8f9fa;
            border-color: #c1c9d0;
        }
        .profile-actions {
            margin-top: 2rem;
            padding-top: 1.5rem;
            border-top: 1px solid #e9ecef;
            text-align: left;
        }
        .form-control {
            border-radius: 8px;
            padding: 0.6rem 1rem;
            border: 1px solid #ced4da;
            transition: border-color 0.2s ease;
        }
        .form-control:focus {
            border-color: #80bdff;
            box-shadow: 0 0 0 0.2rem rgba(0,123,255,0.15);
        }
        .form-label {
            font-weight: 500;
            color: #495057;
            margin-bottom: 0.5rem;
        }
        .profile-picture-section {
            background: #f8f9fa;
            padding: 20px;
            border-radius: 12px;
            border: 1px dashed #dee2e6;
        }
        .upload-hint {
            color: #6c757d;
            font-size: 0.875rem;
            margin-top: 8px;
        }
        .section-title {
            color: #212529;
            font-size: 1.75rem;
            font-weight: 600;
            margin-bottom: 1.5rem;
            text-align: center;
        }
    </style>
</head>
<body>
    <?php include('../includes/header.php'); ?>

    <div class="container py-5">
        <?php if (isset($_SESSION['success_message'])): ?>
            <div class="alert alert-success"><?php echo $_SESSION['success_message']; unset($_SESSION['success_message']); ?></div>
        <?php endif; ?>
        
        <?php if (isset($_SESSION['error_message'])): ?>
            <div class="alert alert-danger"><?php echo $_SESSION['error_message']; unset($_SESSION['error_message']); ?></div>
        <?php endif; ?>

        <div class="profile-section">
            <h3 class="section-title">Account Settings</h3>
            
            <form action="" method="POST" enctype="multipart/form-data" id="profileForm">
                <div class="row">
                    <!-- Left Column - User Information -->
                    <div class="col-md-7">
                        <div class="form-group-wrapper">
                            <label for="username" class="form-label">Username</label>
                            <input type="text" class="form-control" id="username" name="username" 
                                   value="<?php echo htmlspecialchars($user_result['username']); ?>">
                        </div>
                        
                        <div class="form-group-wrapper">
                            <label for="email" class="form-label">Email</label>
                            <input type="email" class="form-control" id="email" name="email" 
                                   value="<?php echo htmlspecialchars($user_result['email']); ?>">
                            <button type="button" class="btn btn-change" onclick="toggleEmailEdit(this)">Change</button>
                        </div>

                        <div class="form-group-wrapper">
                            <label class="form-label">Password</label>
                            <div class="password-display">
                                <span class="password-dots">••••••••</span>
                                <button type="button" class="btn btn-change" onclick="togglePasswordForm()">Change</button>
                            </div>
                            
                            <div id="passwordChangeForm" class="password-change-form">
                                <div class="mb-3">
                                    <input type="password" class="form-control" name="current_password" 
                                           placeholder="Current Password">
                                </div>
                                <div class="mb-3">
                                    <input type="password" class="form-control" name="new_password" 
                                           placeholder="New Password">
                                </div>
                                <div class="mb-3">
                                    <input type="password" class="form-control" name="confirm_password" 
                                           placeholder="Confirm New Password">
                                </div>
                            </div>
                        </div>

                        <div class="profile-actions">
                            <button type="button" class="btn btn-cancel" onclick="cancelChanges()">Cancel</button>
                            <button type="submit" name="update_profile" class="btn btn-save">Save Changes</button>
                        </div>
                    </div>

                    <!-- Right Column - Profile Picture -->
                    <div class="col-md-5">
                        <div class="profile-picture-section">
                            <img src="../<?php echo $user_result['profile_picture'] ?: '/uploads/default_pic/default.jpg'; ?>" 
                                 alt="Profile Picture" class="profile-picture" id="currentProfilePic">
                            <img id="previewImage" class="preview-image">
                            <div class="mb-3">
                                <label for="profile_picture" class="form-label">Change Profile Picture</label>
                                <input type="file" class="form-control" id="profile_picture" name="profile_picture" 
                                       accept="image/*" onchange="previewFile()">
                                
                            </div>
                        </div>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <?php include('../includes/footer.php'); ?>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        function previewFile() {
            const preview = document.getElementById('previewImage');
            const currentPic = document.getElementById('currentProfilePic');
            const file = document.querySelector('input[type=file]').files[0];
            const reader = new FileReader();

            reader.onloadend = function () {
                preview.src = reader.result;
                preview.style.display = 'block';
                currentPic.style.display = 'none';
            }

            if (file) {
                reader.readAsDataURL(file);
            } else {
                preview.src = '';
                preview.style.display = 'none';
                currentPic.style.display = 'block';
            }
        }

        function toggleEmailEdit(button) {
            const emailInput = document.getElementById('email');
            emailInput.disabled = !emailInput.disabled;
            button.textContent = emailInput.disabled ? 'Change' : 'Cancel';
            if (!emailInput.disabled) {
                emailInput.focus();
            }
        }

        function togglePasswordForm() {
            const passwordForm = document.getElementById('passwordChangeForm');
            const isHidden = passwordForm.style.display === 'none' || !passwordForm.style.display;
            passwordForm.style.display = isHidden ? 'block' : 'none';
            if (isHidden) {
                passwordForm.querySelector('input[name="current_password"]').focus();
            }
        }

        function cancelChanges() {
            window.location.reload();
        }

        // Disable email field by default
        document.getElementById('email').disabled = true;
    </script>
</body>
</html>