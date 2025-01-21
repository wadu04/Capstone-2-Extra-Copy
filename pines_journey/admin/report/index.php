<?php
require_once '../includes/auth.php';
require_once '../../includes/config.php';

// Start output buffering
ob_start();

// Mark notifications as read when admin views the page
$mark_read_query = "UPDATE admin_notifications SET is_read = TRUE WHERE is_read = FALSE";
$conn->query($mark_read_query);

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
                // Delete the blog post only
                $stmt = $conn->prepare("DELETE FROM blogs WHERE blog_id = ?");
                $stmt->bind_param("i", $content_id);
                $stmt->execute();
            } else {
                // Delete the comment only
                $stmt = $conn->prepare("DELETE FROM comments WHERE comment_id = ?");
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
            header("Location: index.php");
            exit();
        } catch (Exception $e) {
            // If there's an error, rollback changes
            $conn->rollback();
            $error_message = "Error: " . $e->getMessage();
        }
    }
}

// Get all reports with related information
$sql = "SELECT r.*, u.username as reporter_name, 
        CASE 
            WHEN r.content_type = 'post' THEN (
                SELECT b.blog_id
                FROM blogs b 
                WHERE b.blog_id = r.content_id
            )
            ELSE (
                SELECT c.comment_id
                FROM comments c 
                WHERE c.comment_id = r.content_id
            )
        END as content_exists,
        CASE 
            WHEN r.content_type = 'post' THEN (
                SELECT CONCAT(b.title, ' by ', u2.username)
                FROM blogs b 
                JOIN users u2 ON b.user_id = u2.user_id 
                WHERE b.blog_id = r.content_id
            )
            ELSE (
                SELECT CONCAT(SUBSTRING(c.content, 1, 50), '... by ', u2.username)
                FROM comments c 
                JOIN users u2 ON c.user_id = u2.user_id 
                WHERE c.comment_id = r.content_id
            )
        END as content_preview,
        CASE 
            WHEN r.content_type = 'post' THEN (
                SELECT b.content
                FROM blogs b 
                WHERE b.blog_id = r.content_id
            )
            ELSE (
                SELECT c.content
                FROM comments c 
                WHERE c.comment_id = r.content_id
            )
        END as full_content,
        CASE 
            WHEN r.content_type = 'post' THEN (
                SELECT b.image_url
                FROM blogs b 
                WHERE b.blog_id = r.content_id
            )
            ELSE (
                SELECT NULL
            )
        END as image_url
        FROM reports r
        JOIN users u ON r.reporter_id = u.user_id
        ORDER BY r.created_at DESC";
$result = $conn->query($sql);
$reports = [];
while ($row = $result->fetch_assoc()) {
    $reports[] = $row;
}
?>

<div class="container-fluid px-4">
    <?php if (isset($error_message)): ?>
        <div class="alert alert-danger mt-4">
            <?php echo $error_message; ?>
        </div>
    <?php endif; ?>

    
    <div class="card mb-4">
        <div class="card-header">
            <i class="fas fa-flag me-1"></i>
            Reported Content
        </div>
        <div class="card-body">
            <table class="table table-striped">
                <thead>
                    <tr>
                        <th>Type</th>
                        <th>Content</th>
                        <th>Report Type</th>
                        <th>Reporter</th>
                        <th>Description</th>
                        <th>Date</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($reports as $report): 
                        $content_exists = !is_null($report['content_exists']);
                    ?>
                    <tr>
                        <td><?php echo ucfirst($report['content_type']); ?></td>
                        <td>
                            <?php if (!$content_exists): ?>
                                <span class="text-muted">[Content has been deleted]</span>
                            <?php else: ?>
                                <?php echo htmlspecialchars($report['content_preview']); ?>
                            <?php endif; ?>
                        </td>
                        <td><?php echo ucfirst($report['report_type']); ?></td>
                        <td><?php echo htmlspecialchars($report['reporter_name']); ?></td>
                        <td><?php echo htmlspecialchars($report['description']); ?></td>
                        <td><?php echo date('M d, Y h:i A', strtotime($report['created_at'])); ?></td>
                        <td>
                            <?php if ($content_exists): ?>
                                <!-- View Button -->
                                <button type="button" class="btn btn-primary btn-sm" data-bs-toggle="modal" 
                                        data-bs-target="#viewModal<?php echo $report['report_id']; ?>">
                                    <i class="fas fa-eye"></i> View
                                </button>
                                
                                <!-- Delete Button -->
                                <form action="index.php" method="POST" class="d-inline" onsubmit="return confirm('Are you sure you want to delete this content? This action cannot be undone.');">
                                    <input type="hidden" name="action" value="delete_content">
                                    <input type="hidden" name="report_id" value="<?php echo $report['report_id']; ?>">
                                    <input type="hidden" name="content_type" value="<?php echo $report['content_type']; ?>">
                                    <input type="hidden" name="content_id" value="<?php echo $report['content_id']; ?>">
                                    <button type="submit" class="btn btn-danger btn-sm">
                                        <i class="fas fa-trash"></i> Delete
                                    </button>
                                </form>

                            <?php else: ?>
                                <!-- Delete Report Button -->
                                <form action="index.php" method="POST" class="d-inline">
                                    <input type="hidden" name="action" value="delete_content">
                                    <input type="hidden" name="report_id" value="<?php echo $report['report_id']; ?>">
                                    <input type="hidden" name="content_type" value="<?php echo $report['content_type']; ?>">
                                    <input type="hidden" name="content_id" value="<?php echo $report['content_id']; ?>">
                                    <button type="submit" class="btn btn-secondary btn-sm">
                                        <i class="fas fa-times"></i> Dismiss Report
                                    </button>
                                </form>
                            <?php endif; ?>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<?php foreach ($reports as $report): ?>
<!-- View Modal -->
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
                <?php if ($report['content_type'] === 'post'): ?>
                    <?php 
                        // Get blog post details
                        $blog_id = $report['content_id'];
                        $blog_query = "SELECT b.*, u.username, b.image_url,
                                    (SELECT COUNT(*) FROM comments WHERE blog_id = b.blog_id) as comment_count
                                    FROM blogs b 
                                    JOIN users u ON b.user_id = u.user_id 
                                    WHERE b.blog_id = ?";
                        $stmt = $conn->prepare($blog_query);
                        $stmt->bind_param("i", $blog_id);
                        $stmt->execute();
                        $blog = $stmt->get_result()->fetch_assoc();
                        
                        // Get comments for this blog
                        $comments_query = "SELECT c.*, u.username 
                                        FROM comments c 
                                        JOIN users u ON c.user_id = u.user_id 
                                        WHERE c.blog_id = ? 
                                        ORDER BY c.created_at DESC";
                        $stmt = $conn->prepare($comments_query);
                        $stmt->bind_param("i", $blog_id);
                        $stmt->execute();
                        $comments = $stmt->get_result();
                    ?>
                    
                    <div class="blog-post">
                        <h3><?php echo htmlspecialchars($blog['title']); ?></h3>
                        <p class="text-muted">Posted by <?php echo htmlspecialchars($blog['username']); ?> on <?php echo date('M d, Y h:i A', strtotime($blog['created_at'])); ?></p>
                        
                        <?php if (!empty($blog['image_url'])): ?>
                        <div class="blog-image mb-3">
                            <img src="../..<?php echo str_replace("..", "", htmlspecialchars($blog['image_url'])); ?>" class="img-fluid rounded" alt="Blog Image">
                        </div>
                        <?php endif; ?>
                        
                        <div class="blog-content">
                            <?php echo nl2br(htmlspecialchars($blog['content'])); ?>
                        </div>
                        
                        <hr>
                        <h4>Comments (<?php echo $blog['comment_count']; ?>)</h4>
                        <?php while ($comment = $comments->fetch_assoc()): ?>
                            <div class="comment mb-3 p-3 border rounded">
                                <p class="mb-1"><?php echo nl2br(htmlspecialchars($comment['content'])); ?></p>
                                <small class="text-muted">
                                    By <?php echo htmlspecialchars($comment['username']); ?> on 
                                    <?php echo date('M d, Y h:i A', strtotime($comment['created_at'])); ?>
                                </small>
                            </div>
                        <?php endwhile; ?>
                    </div>
                <?php else: ?>
                    <?php 
                        // Get comment details with associated blog post
                        $comment_id = $report['content_id'];
                        $comment_query = "SELECT c.*, u.username, b.title as blog_title, b.blog_id 
                                        FROM comments c 
                                        JOIN users u ON c.user_id = u.user_id 
                                        JOIN blogs b ON c.blog_id = b.blog_id 
                                        WHERE c.comment_id = ?";
                        $stmt = $conn->prepare($comment_query);
                        $stmt->bind_param("i", $comment_id);
                        $stmt->execute();
                        $comment = $stmt->get_result()->fetch_assoc();
                    ?>
                    
                    <div class="comment-details">
                        <h4>Comment on: <a href="#" class="text-decoration-none"><?php echo htmlspecialchars($comment['blog_title']); ?></a></h4>
                        <div class="comment mb-3 p-3 border rounded">
                            <p class="mb-1"><?php echo nl2br(htmlspecialchars($comment['content'])); ?></p>
                            <small class="text-muted">
                                By <?php echo htmlspecialchars($comment['username']); ?> on 
                                <?php echo date('M d, Y h:i A', strtotime($comment['created_at'])); ?>
                            </small>
                        </div>
                    </div>
                <?php endif; ?>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>
<?php endforeach; ?>

<?php
$content = ob_get_clean();
$page_title = "Report Management";
require_once '../includes/admin_layout.php';
?>