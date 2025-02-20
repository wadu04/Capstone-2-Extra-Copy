<?php
session_start();
require_once '../includes/config.php';

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$user_id = $_SESSION['user_id'];

// Fetch user's QR scans with badges
$stmt = $conn->prepare("
    SELECT us.*, qc.badge, qc.content as qr_name
    FROM user_scans us 
    LEFT JOIN qr_codes qc ON us.qr_content = qc.content 
    WHERE us.user_id = ? 
    ORDER BY us.scanned_at DESC
");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$scans = $stmt->get_result();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My QR Scans - Pines Journey</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="../assets/css/style.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        .card-img-top {
            width: 100%;
            height: 270px;
            object-fit: contain;
            background-color: #f8f9fa;
            padding: 20px;
        }
        .card {
            transition: transform 0.2s;
        }
        .card:hover {
            transform: translateY(-5px);
        }
        .scan-date {
            font-size: 0.85rem;
            color: #888;
        }
        .points-badge {
            position: absolute;
            top: 10px;
            right: 10px;
            background: rgba(0, 0, 0, 0.7);
            color: white;
            padding: 5px 10px;
            border-radius: 15px;
            font-size: 0.9rem;
        }
    </style>
</head>
<body>
    <?php include '../includes/header.php'; ?>

    <div class="container py-4">
        <div class="mb-4">
            <h2>My QR Scans</h2>
        </div>

        <?php if ($scans->num_rows > 0): ?>
            <div class="row row-cols-1 row-cols-md-2 row-cols-lg-3 g-4">
                <?php while ($scan = $scans->fetch_assoc()): ?>
                    <div class="col">
                        <div class="card h-100 shadow-sm">
                            <div class="position-relative">
                                <?php if ($scan['badge']): ?>
                                    <img src="../uploads/qr_badge/<?php echo htmlspecialchars($scan['badge']); ?>" 
                                         class="card-img-top" alt="QR Badge">
                                <?php else: ?>
                                    <div class="card-img-top d-flex align-items-center justify-content-center">
                                        <i class="fas fa-qrcode fa-4x text-muted"></i>
                                    </div>
                                <?php endif; ?>
                            </div>
                            
                            <div class="card-body">
                                <h5 class="card-title">
                                    <?php echo $scan['qr_name'] ? htmlspecialchars($scan['qr_name']) : 'Unknown QR Code'; ?>
                                </h5>

                                
                                <p class="scan-date mt-3 mb-0">
                                    <i class="far fa-clock"></i> 
                                    Scanned on <?php echo date('F j, Y g:i A', strtotime($scan['scanned_at'])); ?>
                                </p>
                            </div>
                        </div>
                    </div>
                <?php endwhile; ?>
            </div>
        <?php else: ?>
            <div class="text-center py-5">
                <i class="fas fa-qrcode fa-3x text-muted mb-3"></i>
                <h3>No QR Scans Yet</h3>
                <p class="text-muted">Start your journey by scanning QR codes around Pines!</p>
            </div>
        <?php endif; ?>
    </div>

    <?php include('../includes/footer.php'); ?>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>