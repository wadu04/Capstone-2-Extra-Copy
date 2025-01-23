<?php
session_start();
require_once '../../includes/config.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: ../login.php");
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $content = isset($_POST['content']) ? $_POST['content'] : uniqid('QR_', true);
    $points = isset($_POST['points']) ? (int)$_POST['points'] : 20;
    
    // Handle badge upload
    $badge = null;
    if (isset($_FILES['badge']) && $_FILES['badge']['error'] == 0) {
        $target_dir = "../../uploads/qr_badge/";
        $file_extension = strtolower(pathinfo($_FILES["badge"]["name"], PATHINFO_EXTENSION));
        $badge = uniqid('badge_', true) . '.' . $file_extension;
        $target_file = $target_dir . $badge;
        
        if (move_uploaded_file($_FILES["badge"]["tmp_name"], $target_file)) {
            $badge = $badge; // Keep the filename only
        } else {
            $success = false;
            $message = "Error uploading badge image.";
        }
    }

    $stmt = $conn->prepare("INSERT INTO qr_codes (content, points, badge) VALUES (?, ?, ?)");
    $stmt->bind_param("sis", $content, $points, $badge);
    
    if ($stmt->execute()) {
        $success = true;
        $message = "QR Code generated successfully!";
    } else {
        $success = false;
        $message = "Error generating QR Code: " . $conn->error;
    }
}

// Fetch existing QR codes
$qr_codes = [];
$result = $conn->query("SELECT * FROM qr_codes ORDER BY created_at DESC");
if ($result) {
    while ($row = $result->fetch_assoc()) {
        $qr_codes[] = $row;
    }
}
$page_title = "Qr Code Generator";
ob_start();

?>


    
   
    <style>
        .content-wrapperr {
            margin-top: 0;
            padding: 20px;
        }
        .qr-container {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));
            gap: 20px;
            padding: 20px;
        }
        .qr-item {
            border: 1px solid #ddd;
            padding: 15px;
            border-radius: 8px;
            text-align: center;
            background: white;
        }
        .qr-item img {
            max-width: 100%;
            height: auto;
            margin-bottom: 10px;
        }
        .download-btn {
            margin-top: 10px;
        }
    </style>


  

    <div class="content-wrapperr">
        <div class="container-fluid">
            
            
            <?php if (isset($message)): ?>
                <div class="alert alert-<?php echo $success ? 'success' : 'danger'; ?>">
                    <?php echo $message; ?>
                </div>
            <?php endif; ?>

            <div class="card mb-4">
                <div class="card-body">
                    <form method="POST" class="row g-3" enctype="multipart/form-data">
                        <div class="col-md-6">
                            <label for="content" class="form-label">QR Code Content</label>
                            <input type="text" class="form-control" id="content" name="content" placeholder="Enter content or leave blank for auto-generate">
                        </div>
                        <div class="col-md-4">
                            <label for="badge" class="form-label">Badge Image</label>
                            <input type="file" class="form-control" id="badge" name="badge" accept="image/*">
                        </div>
                        <div class="col-md-2">
                            <label class="form-label">&nbsp;</label>
                            <button type="submit" class="btn btn-primary w-100">Generate QR Code</button>
                        </div>
                    </form>
                </div>
            </div>

            <h3>Generated QR Codes</h3>
            <div class="qr-container">
                <?php foreach ($qr_codes as $qr): ?>
                    <div class="qr-item">
                        <?php 
                        $qr_url = "https://api.qrserver.com/v1/create-qr-code/?size=300x300&data=" . urlencode($qr['content']);
                        ?>
                        <img src="<?php echo $qr_url; ?>" alt="QR Code">
                        <p><strong>Content:</strong> <?php echo htmlspecialchars($qr['content']); ?></p>
                        <p><strong>Points:</strong> <?php echo htmlspecialchars($qr['points']); ?></p>
                        <p><strong>Created:</strong> <?php echo date('Y-m-d H:i:s', strtotime($qr['created_at'])); ?></p>
                        <a href="<?php echo $qr_url; ?>" class="btn btn-success btn-sm download-btn" download="qr_<?php echo htmlspecialchars($qr['content']); ?>.png">
                            Download QR Code
                        </a>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <?php
$content = ob_get_clean();
include '../includes/admin_layout.php';
?>