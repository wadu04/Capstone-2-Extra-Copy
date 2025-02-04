<?php
session_start();
require_once '../../includes/config.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: ../login.php");
    exit();
}

// Handle QR code deletion
if (isset($_POST['delete_qr']) && isset($_POST['qr_id'])) {
    $qr_id = (int)$_POST['qr_id'];
    
    // First get the badge filename to delete
    $stmt = $conn->prepare("SELECT badge FROM qr_codes WHERE qr_id = ?");
    $stmt->bind_param("i", $qr_id);
    $stmt->execute();
    $result = $stmt->get_result();
    if ($badge_data = $result->fetch_assoc()) {
        if ($badge_data['badge']) {
            $badge_file = "../../uploads/qr_badge/" . $badge_data['badge'];
            if (file_exists($badge_file)) {
                unlink($badge_file);
            }
        }
    }
    
    // Then delete the QR code record
    $stmt = $conn->prepare("DELETE FROM qr_codes WHERE qr_id = ?");
    $stmt->bind_param("i", $qr_id);
    if ($stmt->execute()) {
        $success = true;
        $message = "QR Code deleted successfully!";
    } else {
        $success = false;
        $message = "Error deleting QR Code: " . $conn->error;
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && !isset($_POST['delete_qr'])) {
    $content = isset($_POST['content']) ? $_POST['content'] : uniqid('QR_', true);
    
    // Handle badge upload
    $badge = null;
    if (isset($_FILES['badge']) && $_FILES['badge']['error'] == 0) {
        $target_dir = "../../uploads/qr_badge/";
        if (!file_exists($target_dir)) {
            mkdir($target_dir, 0777, true);
        }
        
        $file_extension = strtolower(pathinfo($_FILES["badge"]["name"], PATHINFO_EXTENSION));
        $badge = uniqid('badge_', true) . '.' . $file_extension;
        $target_file = $target_dir . $badge;
        
        if (move_uploaded_file($_FILES["badge"]["tmp_name"], $target_file)) {
            $badge = $badge;
        } else {
            $success = false;
            $message = "Error uploading badge image.";
        }
    }

    $stmt = $conn->prepare("INSERT INTO qr_codes (content, badge) VALUES (?, ?)");
    $stmt->bind_param("ss", $content, $badge);
    
    if ($stmt->execute()) {
        $success = true;
        $message = "QR Code generated successfully!";
    } else {
        $success = false;
        $message = "Error generating QR Code: " . $conn->error;
    }
}

// Pagination
$items_per_page = 3;
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$offset = ($page - 1) * $items_per_page;

// Get total number of QR codes
$total_result = $conn->query("SELECT COUNT(*) as total FROM qr_codes");
$total_row = $total_result->fetch_assoc();
$total_items = $total_row['total'];
$total_pages = ceil($total_items / $items_per_page);

// Fetch QR codes for current page
$qr_codes = [];
$result = $conn->query("SELECT * FROM qr_codes ORDER BY created_at DESC LIMIT $offset, $items_per_page");
if ($result) {
    while ($row = $result->fetch_assoc()) {
        $qr_codes[] = $row;
    }
}

$page_title = "QR Code Generator";
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
        position: relative;
    }
    .qr-item img {
        max-width: 100%;
        height: auto;
        margin-bottom: 10px;
    }
    .btn-group {
        display: flex;
        gap: 5px;
        justify-content: center;
        margin-top: 10px;
    }
    .pagination {
        margin-top: 20px;
        justify-content: center;
    }
    .badge-preview {
        max-width: 50px;
        height: 50px;
        object-fit: cover;
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
                    <div class="col-md-8">
                        <label for="content" class="form-label">QR Code Content</label>
                        <input type="text" class="form-control" id="content" name="content" placeholder="Enter content or leave blank for auto-generate">
                    </div>
                    <div class="col-md-4">
                        <label for="badge" class="form-label">Badge Image</label>
                        <input type="file" class="form-control" id="badge" name="badge" accept="image/*" onchange="previewBadge(this)">
                        <img id="badgePreview" class="badge-preview d-none" alt="Badge Preview">
                    </div>
                    <div class="col-md-12">
                        <button type="submit" class="btn btn-primary">Generate QR Code</button>
                    </div>
                </form>
            </div>
        </div>

        
        <div class="qr-container">
            <?php foreach ($qr_codes as $qr): ?>
                <div class="qr-item">
                    <?php 
                    $qr_url = "https://api.qrserver.com/v1/create-qr-code/?size=300x300&data=" . urlencode($qr['content']);
                    ?>
                    <img src="<?php echo $qr_url; ?>" alt="QR Code">
                    <p><strong>Content:</strong> <?php echo htmlspecialchars($qr['content']); ?></p>
                    <div class="btn-group">
                        <a href="download.php?qr_id=<?php echo $qr['qr_id']; ?>" 
                           class="btn btn-success btn-sm">Download QR</a>
                        <form method="POST" style="display: inline;">
                            <input type="hidden" name="qr_id" value="<?php echo $qr['qr_id']; ?>">
                            <button type="submit" name="delete_qr" class="btn btn-danger btn-sm" 
                                    onclick="return confirm('Are you sure you want to delete this QR code?');">
                                Delete
                            </button>
                        </form>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>

        <?php if ($total_pages > 1): ?>
            <nav aria-label="Page navigation">
                <ul class="pagination">
                    <?php if ($page > 1): ?>
                        <li class="page-item">
                            <a class="page-link" href="?page=<?php echo $page-1; ?>">&laquo; Previous</a>
                        </li>
                    <?php endif; ?>
                    
                    <?php for ($i = 1; $i <= $total_pages; $i++): ?>
                        <li class="page-item <?php echo $page == $i ? 'active' : ''; ?>">
                            <a class="page-link" href="?page=<?php echo $i; ?>"><?php echo $i; ?></a>
                        </li>
                    <?php endfor; ?>
                    
                    <?php if ($page < $total_pages): ?>
                        <li class="page-item">
                            <a class="page-link" href="?page=<?php echo $page+1; ?>">Next &raquo;</a>
                        </li>
                    <?php endif; ?>
                </ul>
            </nav>
        <?php endif; ?>
    </div>
</div>

<script>
function previewBadge(input) {
    const preview = document.getElementById('badgePreview');
    if (input.files && input.files[0]) {
        const reader = new FileReader();
        reader.onload = function(e) {
            preview.src = e.target.result;
            preview.classList.remove('d-none');
        }
        reader.readAsDataURL(input.files[0]);
    }
}
</script>

<?php
$content = ob_get_clean();
include '../includes/admin_layout.php';
?>