<?php
require_once '../includes/auth.php';
require_once '../../includes/config.php';

// Start output buffering
ob_start();

// Mark notifications as read when admin views the page
$mark_read_query = "UPDATE admin_notifications SET is_read = TRUE WHERE is_read = FALSE";
$conn->query($mark_read_query);

// Get the current tab (default to blog reports)
$current_tab = isset($_GET['tab']) ? $_GET['tab'] : 'blog';

// Handle report actions
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['action'])) {
    if ($_POST['action'] === 'delete_content') {
        $report_id = (int)$_POST['report_id'];
        $content_type = $_POST['content_type'];
        $content_id = (int)$_POST['content_id'];

        try {
            // Start transaction
            $conn->begin_transaction();

            if ($content_type === 'post') {
                // Delete the blog post
                $stmt = $conn->prepare("DELETE FROM blogs WHERE blog_id = ?");
                $stmt->bind_param("i", $content_id);
                $stmt->execute();
            } elseif ($content_type === 'comment') {
                // Delete the comment
                $stmt = $conn->prepare("DELETE FROM comments WHERE comment_id = ?");
                $stmt->bind_param("i", $content_id);
                $stmt->execute();
            } elseif ($content_type === 'review') {
                // Delete the review
                $stmt = $conn->prepare("DELETE FROM reviews WHERE review_id = ?");
                $stmt->bind_param("i", $content_id);
                $stmt->execute();
            }

            // Delete the report
            $stmt = $conn->prepare("DELETE FROM reports WHERE report_id = ?");
            $stmt->bind_param("i", $report_id);
            $stmt->execute();

            // Commit transaction
            $conn->commit();

            // Redirect to refresh the page
            header("Location: index.php?tab=" . $current_tab);
            exit();
        } catch (Exception $e) {
            // If there's an error, rollback changes
            $conn->rollback();
            $error_message = "Error: " . $e->getMessage();
        }
    }
}

// Get reports based on content type
$sql = "SELECT r.*, u.username as reporter_name, 
        CASE 
            WHEN r.content_type = 'post' THEN (
                SELECT b.blog_id
                FROM blogs b 
                WHERE b.blog_id = r.content_id
            )
            WHEN r.content_type = 'comment' THEN (
                SELECT c.comment_id
                FROM comments c 
                WHERE c.comment_id = r.content_id
            )
            ELSE (
                SELECT rv.review_id
                FROM reviews rv 
                WHERE rv.review_id = r.content_id
            )
        END as content_exists,
        CASE 
            WHEN r.content_type = 'post' THEN (
                SELECT CONCAT(b.title, ' by ', u2.username)
                FROM blogs b 
                JOIN users u2 ON b.user_id = u2.user_id 
                WHERE b.blog_id = r.content_id
            )
            WHEN r.content_type = 'comment' THEN (
                SELECT CONCAT(SUBSTRING(c.content, 1, 50), '... by ', u2.username)
                FROM comments c 
                JOIN users u2 ON c.user_id = u2.user_id 
                WHERE c.comment_id = r.content_id
            )
            ELSE (
                SELECT CONCAT('Review by ', u2.username, ' for ', ts.name)
                FROM reviews rv
                JOIN users u2 ON rv.user_id = u2.user_id
                JOIN tourist_spots ts ON rv.spot_id = ts.spot_id
                WHERE rv.review_id = r.content_id
            )
        END as content_preview,
        CASE 
            WHEN r.content_type = 'post' THEN (
                SELECT b.content
                FROM blogs b 
                WHERE b.blog_id = r.content_id
            )
            WHEN r.content_type = 'comment' THEN (
                SELECT c.content
                FROM comments c 
                WHERE c.comment_id = r.content_id
            )
            ELSE (
                SELECT rv.comment
                FROM reviews rv
                WHERE rv.review_id = r.content_id
            )
        END as full_content,
        CASE 
            WHEN r.content_type = 'post' THEN (
                SELECT b.image_url
                FROM blogs b 
                WHERE b.blog_id = r.content_id
            )
            WHEN r.content_type = 'review' THEN (
                SELECT rv.image_url
                FROM reviews rv
                WHERE rv.review_id = r.content_id
            )
            ELSE NULL
        END as content_image
        FROM reports r
        JOIN users u ON r.reporter_id = u.user_id
        WHERE r.content_type " . ($current_tab === 'blog' ? "IN ('post', 'comment')" : "= 'review'") . "
        ORDER BY r.created_at DESC";

$result = $conn->query($sql);
$reports = $result->fetch_all(MYSQLI_ASSOC);


?>

<div class="container-fluid px-4">
    <?php if (isset($error_message)): ?>
        <div class="alert alert-danger mt-4">
            <?php echo $error_message; ?>
        </div>
    <?php endif; ?>

    <h1 class="mt-4">Reports Management</h1>
    
    <!-- Tab Navigation -->
    <ul class="nav nav-tabs mb-4">
        <li class="nav-item">
            <a class="nav-link <?php echo $current_tab === 'blog' ? 'active' : ''; ?>" href="?tab=blog">
                Blog Reports
            </a>
        </li>
        <li class="nav-item">
            <a class="nav-link <?php echo $current_tab === 'review' ? 'active' : ''; ?>" href="?tab=review">
                Review Reports
            </a>
        </li>
    </ul>

    <div class="card mb-4">
        <div class="card-header">
            <i class="fas fa-table me-1"></i>
            <?php echo ucfirst($current_tab); ?> Reports
        </div>
        <div class="card-body">
            <table id="reportsTable" class="table table-striped">
                <thead>
                    <tr>
                        <th>Reporter</th>
                        <th>Type</th>
                        <th>Content Preview</th>
                        <th>Report Type</th>
                        <th>Description</th>
                        <th>Status</th>
                        <th>Date</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($reports as $report): ?>
                        <?php if ($report['content_exists']): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($report['reporter_name']); ?></td>
                            <td><?php echo ucfirst($report['content_type']); ?></td>
                            <td><?php echo htmlspecialchars($report['content_preview']); ?></td>
                            <td><?php echo ucfirst(str_replace('_', ' ', $report['report_type'])); ?></td>
                            <td><?php echo htmlspecialchars($report['description']); ?></td>
                            <td>
                                <span class="badge bg-<?php 
                                    echo $report['status'] === 'pending' ? 'warning' : 
                                        ($report['status'] === 'reviewed' ? 'info' : 'success'); 
                                ?>">
                                    <?php echo ucfirst($report['status']); ?>
                                </span>
                            </td>
                            <td><?php echo date('Y-m-d H:i', strtotime($report['created_at'])); ?></td>
                            <td>
                                <button class="btn btn-primary btn-sm" data-bs-toggle="modal" data-bs-target="#viewModal<?php echo $report['report_id']; ?>">
                                    View
                                </button>
                                <form method="post" class="d-inline" onsubmit="return confirm('Are you sure you want to delete this content? This action cannot be undone.');">
                                    <input type="hidden" name="action" value="delete_content">
                                    <input type="hidden" name="report_id" value="<?php echo $report['report_id']; ?>">
                                    <input type="hidden" name="content_type" value="<?php echo $report['content_type']; ?>">
                                    <input type="hidden" name="content_id" value="<?php echo $report['content_exists']; ?>">
                                    <button type="submit" class="btn btn-danger btn-sm">Delete</button>
                                </form>
                            </td>
                        </tr>
                        <?php endif; ?>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- View Content Modals -->
<?php foreach ($reports as $report): ?>
<?php if ($report['content_exists']): ?>
<div class="modal fade" id="viewModal<?php echo $report['report_id']; ?>" tabindex="-1" aria-labelledby="viewModalLabel<?php echo $report['report_id']; ?>" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="viewModalLabel<?php echo $report['report_id']; ?>">
                    View <?php echo ucfirst($report['content_type']); ?> Content
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <?php if ($report['content_image']): ?>
                    <img src="../<?php echo $report['content_image']; ?>" class="img-fluid mb-3" alt="Content Image">
                <?php endif; ?>
                
                <div class="card">
                    <div class="card-body">
                        <h6 class="card-subtitle mb-2 text-muted">Content:</h6>
                        <p class="card-text"><?php echo nl2br(htmlspecialchars($report['full_content'])); ?></p>
                    </div>
                </div>

                <div class="mt-3">
                    <h6>Report Details:</h6>
                    <p><strong>Reporter:</strong> <?php echo htmlspecialchars($report['reporter_name']); ?></p>
                    <p><strong>Report Type:</strong> <?php echo ucfirst(str_replace('_', ' ', $report['report_type'])); ?></p>
                    <p><strong>Description:</strong> <?php echo htmlspecialchars($report['description']); ?></p>
                    <p><strong>Date Reported:</strong> <?php echo date('Y-m-d H:i', strtotime($report['created_at'])); ?></p>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>
<?php endif; ?>
<?php endforeach; ?>

<?php
$content = ob_get_clean();
include '../includes/admin_layout.php';
?>