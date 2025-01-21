<?php
session_start();
require_once '../includes/config.php';

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$user_id = $_SESSION['user_id'];

// Handle profile picture upload
if (isset($_FILES['profile_picture'])) {
    $target_dir = "../uploads/defualt_pic/";
    if (!file_exists($target_dir)) {
        mkdir($target_dir, 0777, true);
    }
    
    $file_extension = strtolower(pathinfo($_FILES["profile_picture"]["name"], PATHINFO_EXTENSION));
    $target_file = $target_dir . "user_" . $user_id . "_" . time() . "." . $file_extension;
    
    $allowed_types = array('jpg', 'jpeg', 'png', 'gif');
    if (in_array($file_extension, $allowed_types) && move_uploaded_file($_FILES["profile_picture"]["tmp_name"], $target_file)) {
        $relative_path = str_replace("../", "/", $target_file);
        $stmt = $conn->prepare("UPDATE users SET profile_picture = ? WHERE user_id = ?");
        $stmt->bind_param("si", $relative_path, $user_id);
        $stmt->execute();
        $_SESSION['success_message'] = "Profile picture updated successfully!";
    } else {
        $_SESSION['error_message'] = "Sorry, there was an error uploading your file.";
    }
    header("Location: profile.php");
    exit();
}

// Fetch user data
$stmt = $conn->prepare("SELECT username, email, profile_picture FROM users WHERE user_id = ?");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$user_result = $stmt->get_result()->fetch_assoc();

// Fetch user's blogs
$stmt = $conn->prepare("SELECT * FROM blogs WHERE user_id = ? ORDER BY created_at DESC");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$blogs = $stmt->get_result();

// Fetch user's QR points
$stmt = $conn->prepare("SELECT total_points FROM leaderboard WHERE user_id = ?");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$points_result = $stmt->get_result()->fetch_assoc();
$total_points = $points_result ? $points_result['total_points'] : 0;

// Fetch user's QR scans
$stmt = $conn->prepare("SELECT * FROM user_scans WHERE user_id = ? ORDER BY scanned_at DESC");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$scans = $stmt->get_result();
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
            margin-bottom: 20px;
        }
        .profile-section {
            background: #fff;
            padding: 20px;
            border-radius: 10px;
            box-shadow: 0 0 10px rgba(0,0,0,0.1);
            margin-bottom: 20px;
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

        <div class="row">
            <!-- Profile Information -->
            <div class="col-md-4">
                <div class="profile-section text-center">
                <img src="../<?php echo $user_result['profile_picture'] ?: '/uploads/default_pic/default.jpg'; ?>" 
                    
                         alt="Profile Picture" class="profile-picture">
                    
                    <form action="" method="POST" enctype="multipart/form-data" class="mb-4">
                        <div class="mb-3">
                            <label for="profile_picture" class="form-label">Change Profile Picture</label>
                            <input type="file" class="form-control" id="profile_picture" name="profile_picture" accept="image/*">
                        </div>
                        <button type="submit" class="btn btn-primary">Update Picture</button>
                    </form>

                    <h3><?php echo htmlspecialchars($user_result['username']); ?></h3>
                    <p class="text-muted"><?php echo htmlspecialchars($user_result['email']); ?></p>
                    
                    <div class="mt-4">
                        <h4>QR Points: <?php echo $total_points; ?></h4>
                    </div>
                </div>
            </div>

            <div class="col-md-8">
                <!-- User's Blogs -->
                <div class="profile-section">
                    <h3>My Blog Posts</h3>
                    <?php if ($blogs->num_rows > 0): ?>
                        <div class="list-group">
                            <?php while ($blog = $blogs->fetch_assoc()): ?>
                                <a href="blog-post.php?id=<?php echo $blog['blog_id']; ?>" 
                                   class="list-group-item list-group-item-action">
                                    <h5><?php echo htmlspecialchars($blog['title']); ?></h5>
                                    <small class="text-muted">Posted on <?php echo date('F j, Y', strtotime($blog['created_at'])); ?></small>
                                </a>
                            <?php endwhile; ?>
                        </div>
                    <?php else: ?>
                        <p>You haven't created any blog posts yet</p>
                        <a href="blog.php" class="btn btn-primary">Create Your First Blog</a>
                    <?php endif; ?>
                </div>

                <!-- QR Code Scans -->
                <div class="profile-section">
                    <h3>My QR Code Scans</h3>
                    <?php if ($scans->num_rows > 0): ?>
                        <div class="table-responsive">
                            <table class="table">
                                <thead>
                                    <tr>
                                        <th>QR Content</th>
                                        <th>Points Earned</th>
                                        <th>Scanned Date</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php while ($scan = $scans->fetch_assoc()): ?>
                                        <tr>
                                            <td><?php echo htmlspecialchars($scan['qr_content']); ?></td>
                                            <td><?php echo $scan['points_earned']; ?></td>
                                            <td><?php echo date('F j, Y g:i A', strtotime($scan['scanned_at'])); ?></td>
                                        </tr>
                                    <?php endwhile; ?>
                                </tbody>
                            </table>
                        </div>
                    <?php else: ?>
                        <p>You haven't scanned any QR codes yet.</p>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>

    <?php include('../includes/footer.php'); ?>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>