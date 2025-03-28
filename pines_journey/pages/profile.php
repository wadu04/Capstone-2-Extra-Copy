<?php
session_start();
require_once '../includes/config.php';
require_once '../vendor/autoload.php'; // Make sure PHPMailer is properly installed via composer

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$user_id = $_SESSION['user_id'];

error_log("Server timezone: " . date_default_timezone_get());
error_log("Current server time: " . date('Y-m-d H:i:s'));

// Function to generate OTP
function generateOTP() {
    return sprintf("%06d", mt_rand(0, 999999));
}

// Function to send OTP via email
function sendOTP($email, $otp) {
    $mail = new PHPMailer(true);
    
    try {
        // Server settings
        $mail->SMTPDebug = 0; // Change from 2 to 0 for production
        $mail->isSMTP();
        $mail->Host = 'smtp-relay.brevo.com';
        $mail->SMTPAuth = true;
        $mail->Username = '882945001@smtp-brevo.com';
        $mail->Password = 'bgmBAzXChEdGJRaZ';
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
        $mail->Port = 587;
        
        // Recipients
        $mail->setFrom('pinesjourneyy@gmail.com', 'Pines Journey');
        $mail->addAddress($email);
        
        // Content
        $mail->isHTML(true);
        $mail->Subject = 'Your Password Change Verification Code';
        $mail->Body = '
            <div style="font-family: Arial, sans-serif; max-width: 600px; margin: 0 auto; padding: 20px; border: 1px solid #eee; border-radius: 10px;">
                <h2 style="color: #333; text-align: center;">Password Change Request</h2>
                <p style="font-size: 16px; line-height: 1.5;">Hello,</p>
                <p style="font-size: 16px; line-height: 1.5;">We received a request to change your password. Use the verification code below to complete the process:</p>
                <div style="background-color: #f5f5f5; padding: 15px; text-align: center; font-size: 24px; letter-spacing: 5px; font-weight: bold; margin: 20px 0; border-radius: 5px;">' . $otp . '</div>
                <p style="font-size: 16px; line-height: 1.5;">This code will expire in 15 minutes.</p>
                <p style="font-size: 16px; line-height: 1.5;">If you did not request this change, please ignore this email or contact support if you have concerns.</p>
                <p style="font-size: 16px; line-height: 1.5;">Thank you,<br>Pines Journey Team</p>
            </div>
        ';
        
        error_log("Attempting to send email to: " . $email);
        error_log("Using SMTP: " . $mail->Host);
        error_log("Username: " . $mail->Username);
        
        $result = $mail->send();
        return true;
    } catch (Exception $e) {
        error_log("Mail Error: " . $mail->ErrorInfo);
        throw new Exception("Failed to send email: " . $mail->ErrorInfo);
    }
}

// Handle OTP request (AJAX)
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['request_otp'])) {
    header('Content-Type: application/json');
    
    try {
        // Fetch user email
        $stmt = $conn->prepare("SELECT email FROM users WHERE user_id = ?");
        $stmt->bind_param("i", $user_id);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($result->num_rows === 0) {
            throw new Exception("User not found");
        }
        
        $user_data = $result->fetch_assoc();
        $email = $user_data['email'];
        
        // Generate OTP
        $otp = generateOTP();
        $expires_at = date('Y-m-d H:i:s', strtotime('+15 minutes'));
        
        error_log("Generated new OTP: " . $otp);
        error_log("Expiration time set to: " . $expires_at);
        
        // Delete any existing OTPs for this user
        $stmt = $conn->prepare("DELETE FROM password_reset_otp WHERE user_id = ?");
        $stmt->bind_param("i", $user_id);
        $stmt->execute();
        
        // Try to send OTP first before storing in database
        if (!sendOTP($email, $otp)) {
            throw new Exception("Failed to send verification code");
        }
        
        // Store new OTP in database only if email was sent successfully
        $stmt = $conn->prepare("INSERT INTO password_reset_otp (user_id, otp_code, expires_at) VALUES (?, ?, ?)");
        $stmt->bind_param("iss", $user_id, $otp, $expires_at);
        
        if (!$stmt->execute()) {
            error_log("Failed to store OTP: " . $stmt->error);
            throw new Exception("Failed to store OTP");
        }
        
        // Add debug logging
        error_log("Stored OTP in database - User ID: " . $user_id . ", OTP: " . $otp . ", Expires: " . $expires_at);
        
        echo json_encode(['success' => true, 'message' => 'Verification code sent to your email']);
        
    } catch (Exception $e) {
        error_log("OTP Error: " . $e->getMessage());
        echo json_encode(['success' => false, 'message' => $e->getMessage()]);
    }
    
    exit();
}

// Handle OTP verification (AJAX)
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['verify_otp'])) {
    header('Content-Type: application/json');
    
    try {
        $otp_code = trim($_POST['otp_code']);
        
        if (empty($otp_code)) {
            throw new Exception("Please enter the verification code");
        }
        
        // Debug logging
        error_log("Verifying OTP for user_id: " . $user_id . ", OTP: " . $otp_code);
        
        // Get current server time for debugging
        $current_time = date('Y-m-d H:i:s');
        error_log("Current server time: " . $current_time);
        
        // First, let's check if the OTP exists without expiration check
        $debug_stmt = $conn->prepare("SELECT otp_code, expires_at FROM password_reset_otp WHERE user_id = ? ORDER BY created_at DESC LIMIT 1");
        $debug_stmt->bind_param("i", $user_id);
        $debug_stmt->execute();
        $debug_result = $debug_stmt->get_result();
        if ($row = $debug_result->fetch_assoc()) {
            error_log("Latest OTP in DB - Code: " . $row['otp_code'] . ", Expires: " . $row['expires_at']);
            error_log("Submitted OTP: " . $otp_code);
            error_log("Comparison result: " . ($row['otp_code'] === $otp_code ? "Match" : "No Match"));
        }
        
        // Modified query to use explicit timestamp comparison
        $stmt = $conn->prepare("
            SELECT * FROM password_reset_otp 
            WHERE user_id = ? 
            AND otp_code = ? 
            AND expires_at > ?
        ");
        $stmt->bind_param("iss", $user_id, $otp_code, $current_time);
        $stmt->execute();
        $result = $stmt->get_result();
        
        // Debug logging
        error_log("OTP verification query result rows: " . $result->num_rows);
        
        if ($result->num_rows === 0) {
            throw new Exception("Invalid or expired verification code");
        }
        
        echo json_encode(['success' => true, 'message' => 'Verification successful']);
        
    } catch (Exception $e) {
        error_log("OTP Verification Error: " . $e->getMessage());
        echo json_encode(['success' => false, 'message' => $e->getMessage()]);
    }
    
    exit();
}

// Handle profile update
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['update_profile'])) {
    $username = trim($_POST['username'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $current_password = trim($_POST['current_password'] ?? '');
    $new_password = trim($_POST['new_password'] ?? '');
    $confirm_password = trim($_POST['confirm_password'] ?? '');
    $otp_code = trim($_POST['otp_code'] ?? '');
    
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
            if (empty($current_password) || empty($new_password) || empty($confirm_password) || empty($otp_code)) {
                throw new Exception("All password fields and verification code are required when changing password.");
            }

            // Debug logging for OTP verification
            error_log("Profile Update - Verifying OTP: " . $otp_code);
            
            // Get current server time
            $current_time = date('Y-m-d H:i:s');
            
            // Verify OTP with explicit timestamp comparison
            $stmt = $conn->prepare("
                SELECT * FROM password_reset_otp 
                WHERE user_id = ? 
                AND otp_code = ? 
                AND expires_at > ?
                ORDER BY created_at DESC 
                LIMIT 1
            ");
            $stmt->bind_param("iss", $user_id, $otp_code, $current_time);
            $stmt->execute();
            $otp_result = $stmt->get_result();
            
            // Debug logging
            error_log("OTP verification result rows: " . $otp_result->num_rows);
            
            if ($otp_result->num_rows === 0) {
                // Debug check for the latest OTP
                $debug_stmt = $conn->prepare("SELECT otp_code, expires_at FROM password_reset_otp WHERE user_id = ? ORDER BY created_at DESC LIMIT 1");
                $debug_stmt->bind_param("i", $user_id);
                $debug_stmt->execute();
                if ($row = $debug_stmt->get_result()->fetch_assoc()) {
                    error_log("Latest OTP in DB - Code: " . $row['otp_code'] . ", Expires: " . $row['expires_at']);
                    error_log("Current time: " . $current_time);
                }
                throw new Exception("Invalid or expired verification code.");
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
            
            // Delete the used OTP
            $stmt = $conn->prepare("DELETE FROM password_reset_otp WHERE user_id = ?");
            $stmt->bind_param("i", $user_id);
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
                $relative_path = str_replace("../", "", $target_file);
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
            background-color: #f8f9;
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
        .otp-verification {
            background: #e8f4ff;
            padding: 15px;
            border-radius: 8px;
            margin-bottom: 15px;
            display: none;
        }
        .otp-input {
            letter-spacing: 6px;
            font-size: 1.2rem;
            text-align: center;
        }
        .verification-message {
            font-size: 0.9rem;
            margin-top: 10px;
        }
        .spinner-border {
            width: 1rem;
            height: 1rem;
            margin-right: 5px;
            display: none;
        }
        .password-steps {
            display: flex;
            margin-bottom: 15px;
        }
        .step {
            flex: 1;
            text-align: center;
            position: relative;
            padding-bottom: 20px;
        }
        .step:not(:last-child):after {
            content: '';
            position: absolute;
            width: 100%;
            height: 2px;
            background: #e9ecef;
            top: 15px;
            left: 50%;
        }
        .step-circle {
            width: 30px;
            height: 30px;
            border-radius: 50%;
            background: #e9ecef;
            color: #adb5bd;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 5px;
            position: relative;
            z-index: 2;
            font-weight: bold;
        }
        .step.active .step-circle {
            background: #007bff;
            color: white;
        }
        .step-label {
            font-size: 0.75rem;
            color: #6c757d;
        }
        .step.active .step-label {
            color: #007bff;
            font-weight: 500;
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
                                <button type="button" class="btn btn-change" id="changePasswordBtn" onclick="startPasswordChange()">Change</button>
                            </div>
                            
                            <!-- Password Change Steps -->
                            <div id="passwordChangeSteps" style="display: none;">
                                <div class="password-steps mt-3 mb-3">
                                    <div class="step active" id="step1">
                                        <div class="step-circle">1</div>
                                        <div class="step-label">Verify Email</div>
                                    </div>
                                    <div class="step" id="step2">
                                        <div class="step-circle">2</div>
                                        <div class="step-label">Enter Code</div>
                                    </div>
                                    <div class="step" id="step3">
                                        <div class="step-circle">3</div>
                                        <div class="step-label">New Password</div>
                                    </div>
                                </div>
                                
                                <!-- Step 1: Email Verification -->
                                <div id="verificationStep" class="otp-verification">
                                    <p class="mb-3">For security, we'll send a verification code to your email address.</p>
                                    <button type="button" class="btn btn-primary btn-sm" id="sendOtpBtn" onclick="sendOTP()">
                                        <span class="spinner-border spinner-border-sm" id="otpSpinner"></span>
                                        Send Verification Code
                                    </button>
                                    <div class="verification-message" id="otpMessage"></div>
                                </div>
                                
                                <!-- Step 2: OTP Entry -->
                                <div id="otpEntryStep" class="otp-verification" style="display: none;">
                                    <label for="otpCode" class="form-label">Enter the 6-digit code sent to your email</label>
                                    <input type="text" class="form-control otp-input" id="otpCode" name="otp_code" maxlength="6" placeholder="------">
                                    <div class="d-flex justify-content-between align-items-center mt-3">
                                        <button type="button" class="btn btn-outline-secondary btn-sm" onclick="resendOTP()">Resend Code</button>
                                        <button type="button" class="btn btn-primary btn-sm" onclick="verifyOTP()">
                                            <span class="spinner-border spinner-border-sm" id="verifySpinner"></span>
                                            Verify Code
                                        </button>
                                    </div>
                                    <div class="verification-message" id="verifyMessage"></div>
                                </div>
                                
                                <!-- Step 3: Password Change Form -->
                                <div id="passwordChangeForm" class="password-change-form">
                                    <div class="mb-3">
                                        <label for="current_password" class="form-label">Current Password</label>
                                        <input type="password" class="form-control" id="current_password" name="current_password" placeholder="Enter your current password">
                                    </div>
                                    <div class="mb-3">
                                        <label for="new_password" class="form-label">New Password</label>
                                        <input type="password" class="form-control" id="new_password" name="new_password" placeholder="Enter your new password">
                                    </div>
                                    <div class="mb-3">
                                        <label for="confirm_password" class="form-label">Confirm New Password</label>
                                        <input type="password" class="form-control" id="confirm_password" name="confirm_password" placeholder="Confirm your new password">
                                        <div class="form-text">Password must be at least 8 characters long and contain at least one letter and one number.</div>
                                    </div>
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
                            <img src="../<?php echo htmlspecialchars($user_result['profile_picture'] ?: '/uploads/default_pic/default.jpg'); ?>" 
                                 alt="Profile Picture" class="profile-picture" id="currentProfilePic">
                            <img id="previewImage" class="preview-image">
                            <div class="mb-3">
                                <label for="profile_picture" class="form-label">Change Profile Picture</label>
                                <input type="file" class="form-control" id="profile_picture" name="profile_picture" 
                                       accept="image/*" onchange="previewFile()">
                                <p class="upload-hint">Accepted formats: JPG, JPEG, PNG, GIF (max 5MB)</p>
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

        function startPasswordChange() {
            document.getElementById('passwordChangeSteps').style.display = 'block';
            document.getElementById('verificationStep').style.display = 'block';
            document.getElementById('changePasswordBtn').style.display = 'none';
            
            // Set active step
            document.getElementById('step1').classList.add('active');
            document.getElementById('step2').classList.remove('active');
            document.getElementById('step3').classList.remove('active');
        }
        
        function sendOTP() {
            const spinner = document.getElementById('otpSpinner');
            const button = document.getElementById('sendOtpBtn');
            const messageDiv = document.getElementById('otpMessage');
            
            spinner.style.display = 'inline-block';
            button.disabled = true;
            messageDiv.textContent = '';
            messageDiv.className = 'verification-message';
            
            fetch('profile.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded',
                },
                body: 'request_otp=1'
            })
            .then(response => response.json())
            .then(data => {
                spinner.style.display = 'none';
                button.disabled = false;
                
                if (data.success) {
                    messageDiv.textContent = data.message;
                    messageDiv.className = 'verification-message text-success';
                    
                    // Show OTP entry field
                    document.getElementById('verificationStep').style.display = 'none';
                    document.getElementById('otpEntryStep').style.display = 'block';
                    
                    // Update steps
                    document.getElementById('step1').classList.remove('active');
                    document.getElementById('step2').classList.add('active');
                } else {
                    messageDiv.textContent = data.message;
                    messageDiv.className = 'verification-message text-danger';
                }
            })
            .catch(error => {
                spinner.style.display = 'none';
                button.disabled = false;
                messageDiv.textContent = 'An error occurred. Please try again.';
                messageDiv.className = 'verification-message text-danger';
            });
        }
        
        function resendOTP() {
            // Just call the sendOTP function again
            document.getElementById('verificationStep').style.display = 'block';
            document.getElementById('otpEntryStep').style.display = 'none';
            document.getElementById('step1').classList.add('active');
            document.getElementById('step2').classList.remove('active');
            sendOTP();
        }
        
        function verifyOTP() {
            const spinner = document.getElementById('verifySpinner');
            const messageDiv = document.getElementById('verifyMessage');
            const otpCode = document.getElementById('otpCode').value.trim();
            
            if (otpCode.length !== 6) {
                messageDiv.textContent = 'Please enter the 6-digit verification code';
                messageDiv.className = 'verification-message text-danger';
                return;
            }
            
            spinner.style.display = 'inline-block';
            messageDiv.textContent = '';
            messageDiv.className = 'verification-message';
            
            fetch('profile.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded',
                },
                body: 'verify_otp=1&otp_code=' + otpCode
            })
            .then(response => response.json())
            .then(data => {
                spinner.style.display = 'none';
                
                if (data.success) {
                    messageDiv.textContent = data.message;
                    messageDiv.className = 'verification-message text-success';
                    // Show password change form
                    document.getElementById('passwordChangeForm').style.display = 'block';
                    
                    // Update steps
                    document.getElementById('step2').classList.remove('active');
                    document.getElementById('step3').classList.add('active');
                } else {
                    messageDiv.textContent = data.message;
                    messageDiv.className = 'verification-message text-danger';
                }
            })
            .catch(error => {
                spinner.style.display = 'none';
                messageDiv.textContent = 'An error occurred. Please try again.';
                messageDiv.className = 'verification-message text-danger';
            });
        }
        
        function cancelChanges() {
            // Reset form to original values
            document.getElementById('profileForm').reset();
            
            // Hide password change form
            document.getElementById('passwordChangeSteps').style.display = 'none';
            document.getElementById('passwordChangeForm').style.display = 'none';
            document.getElementById('verificationStep').style.display = 'none';
            document.getElementById('otpEntryStep').style.display = 'none';
            document.getElementById('changePasswordBtn').style.display = 'inline-block';
            
            // Reset profile picture preview
            document.getElementById('previewImage').style.display = 'none';
            document.getElementById('currentProfilePic').style.display = 'block';
            
            // Reset email field if it was being edited
            const emailInput = document.getElementById('email');
            emailInput.disabled = true;
            const emailButton = emailInput.nextElementSibling;
            if (emailButton) {
                emailButton.textContent = 'Change';
            }
            
            // Clear any OTP messages
            document.getElementById('otpMessage').textContent = '';
            document.getElementById('verifyMessage').textContent = '';
        }
        
        // Validate password requirements
        document.getElementById('new_password').addEventListener('input', function() {
            const password = this.value;
            const confirmPassword = document.getElementById('confirm_password');
            
            if (confirmPassword.value && password !== confirmPassword.value) {
                confirmPassword.setCustomValidity("Passwords don't match");
            } else {
                confirmPassword.setCustomValidity('');
            }
            
            if (!password.match(/^(?=.*[A-Za-z])(?=.*\d)[A-Za-z\d]{8,}$/)) {
                this.setCustomValidity("Password must be at least 8 characters long and contain at least one letter and one number.");
            } else {
                this.setCustomValidity('');
            }
        });
        
        document.getElementById('confirm_password').addEventListener('input', function() {
            const password = document.getElementById('new_password').value;
            
            if (this.value !== password) {
                this.setCustomValidity("Passwords don't match");
            } else {
                this.setCustomValidity('');
            }
        });
        
        // Form submission validation
        document.getElementById('profileForm').addEventListener('submit', function(event) {
            const currentPassword = document.getElementById('current_password');
            const newPassword = document.getElementById('new_password');
            const confirmPassword = document.getElementById('confirm_password');
            const otpCode = document.getElementById('otpCode');
            
            // If any password field is filled, ensure all are filled
            if (currentPassword.value || newPassword.value || confirmPassword.value) {
                if (!currentPassword.value || !newPassword.value || !confirmPassword.value || !otpCode.value) {
                    event.preventDefault();
                    alert('All password fields and verification code are required when changing password.');
                    return;
                }
                
                // Password validation
                if (newPassword.value !== confirmPassword.value) {
                    event.preventDefault();
                    alert("New passwords don't match.");
                    return;
                }
                
                if (!newPassword.value.match(/^(?=.*[A-Za-z])(?=.*\d)[A-Za-z\d]{8,}$/)) {
                    event.preventDefault();
                    alert("Password must be at least 8 characters long and contain at least one letter and one number.");
                    return;
                }
            }
        });
        
        // Accessibility improvements
        document.querySelectorAll('input, button').forEach(element => {
            if (!element.hasAttribute('aria-label') && !element.hasAttribute('aria-labelledby')) {
                const label = element.closest('div').querySelector('label');
                if (label) {
                    element.setAttribute('aria-labelledby', label.id || '');
                    if (!label.id) {
                        label.id = 'label-' + Math.random().toString(36).substring(2, 9);
                        element.setAttribute('aria-labelledby', label.id);
                    }
                }
            }
        });
        
        // Auto-focus OTP input after sending code
        document.getElementById('sendOtpBtn').addEventListener('click', function() {
            setTimeout(() => {
                const otpInput = document.getElementById('otpCode');
                if (otpInput && otpInput.closest('div').style.display !== 'none') {
                    otpInput.focus();
                }
            }, 1000);
        });
        
        // Add countdown timer for OTP expiration
        let countdownTimer;
        function startCountdown() {
            clearInterval(countdownTimer);
            let timeLeft = 15 * 60; // 15 minutes in seconds
            const countdownElement = document.createElement('div');
            countdownElement.id = 'otpCountdown';
            countdownElement.className = 'mt-2 text-muted small';
            
            const containerElement = document.getElementById('otpEntryStep');
            const existingCountdown = document.getElementById('otpCountdown');
            if (existingCountdown) {
                containerElement.removeChild(existingCountdown);
            }
            
            containerElement.appendChild(countdownElement);
            
            countdownTimer = setInterval(() => {
                const minutes = Math.floor(timeLeft / 60);
                const seconds = timeLeft % 60;
                countdownElement.textContent = `Code expires in ${minutes}:${seconds < 10 ? '0' : ''}${seconds}`;
                
                if (timeLeft <= 0) {
                    clearInterval(countdownTimer);
                    countdownElement.textContent = 'Code has expired. Please request a new code.';
                    countdownElement.className = 'mt-2 text-danger small';
                }
                
                timeLeft--;
            }, 1000);
        }
        
        // Start countdown when OTP is sent
        document.getElementById('sendOtpBtn').addEventListener('click', function() {
            // Wait for the OTP to be sent successfully
            setTimeout(() => {
                const otpEntryStep = document.getElementById('otpEntryStep');
                if (otpEntryStep && otpEntryStep.style.display !== 'none') {
                    startCountdown();
                }
            }, 1000);
        });
    </script>
</body>
</html>