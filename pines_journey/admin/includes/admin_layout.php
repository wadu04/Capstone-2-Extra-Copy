<?php
require_once __DIR__ . '/auth.php';
require_once '../../includes/config.php';

// Check if user is logged in and is admin
if (!isLoggedIn() || !isAdmin()) {
    header("Location: ../../pages/login.php");
    exit();
}

// Get unread notifications count
$notifications_query = "SELECT COUNT(*) as count FROM admin_notifications WHERE is_read = FALSE";
$notifications_result = $conn->query($notifications_query);
$notifications_count = $notifications_result->fetch_assoc()['count'];

// Set default values for variables
if (!isset($page_title)) {
    $page_title = "Admin Dashboard";
}
if (!isset($content)) {
    $content = "";
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard - Pine's Journey</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="../../assets/css/style.css" rel="stylesheet">
    <link href="../../assets/css/admin.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
</head>
<body>
    <div class="d-flex">
        <!-- Sidebar -->
        <div class="sidebar bg-dark text-white">
            <div class="sidebar-heading p-3">
                <h5 class="mb-0">Pine's Journey Admin</h5>
            </div>
            <div class="list-group list-group-flush">
                <a href="../dashboard/index.php" class="list-group-item list-group-item-action bg-dark text-white">
                    <i class="fas fa-tachometer-alt me-2"></i> Dashboard
                </a>
                <a href="../users/index.php" class="list-group-item list-group-item-action bg-dark text-white">
                    <i class="fas fa-users me-2"></i> Users
                </a>
                <a href="../tourist_spots/index.php" class="list-group-item list-group-item-action bg-dark text-white">
                    <i class="fas fa-map-marker-alt me-2"></i> Tourist Spots
                </a>
                <a href="../events/index.php" class="list-group-item list-group-item-action bg-dark text-white">
                    <i class="fas fa-calendar-alt me-2"></i> Events
                </a>
                <a href="../blogs/index.php" class="list-group-item list-group-item-action bg-dark text-white">
                    <i class="fas fa-blog me-2"></i> Blogs
                </a>
                <a href="../qr_generate/qr-generate.php" class="list-group-item list-group-item-action bg-dark text-white">
                    <i class="fas fa-qrcode me-2"></i> QR Generate
                </a>
                <a href="../games/index.php" class="list-group-item list-group-item-action bg-dark text-white">
                    <i class="fas fa-gamepad me-2"></i>Rewards
                </a>
                <a href="../report/index.php" class="list-group-item list-group-item-action bg-dark text-white">
                    <i class="fas fa-flag me-2"></i> Report
                    <?php if ($notifications_count > 0): ?>
                        <span class="badge bg-danger rounded-pill ms-2"><?php echo $notifications_count; ?></span>
                    <?php endif; ?>
                </a>
                <a href="../../index.php" class="list-group-item list-group-item-action bg-dark text-white">
                    <i class="fas fa-home me-2"></i> Back to Site
                </a>
                <a href="../../includes/logout.php" class="list-group-item list-group-item-action bg-dark text-white">
                    <i class="fas fa-sign-out-alt me-2"></i> Logout
                </a>
            </div>
        </div>

        <!-- Content -->
        <div class="content-wrapper">
            <!-- Navbar -->
            <nav class="navbar navbar-expand-lg navbar-light bg-light border-bottom">
                <div class="container-fluid">
                    <button class="btn btn-dark" id="sidebarToggle">
                        <i class="fas fa-bars"></i>
                    </button>
                    <div class="ms-auto">
                        <span class="navbar-text">
                            Welcome, <?php echo $_SESSION['username']; ?>!
                        </span>
                    </div>
                </div>
            </nav>

            <!-- Page Content -->
            <div class="container-fluid p-4">
                <?php if (isset($page_title)): ?>
                    <h2 class="mb-4"><?php echo $page_title; ?></h2>
                <?php endif; ?>
                
                <?php echo $content; ?>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Toggle sidebar
        document.getElementById('sidebarToggle').addEventListener('click', function(e) {
            e.preventDefault();
            document.querySelector('.sidebar').classList.toggle('toggled');
            document.querySelector('.content-wrapper').classList.toggle('toggled');
        });
    </script>
</body>
</html>
