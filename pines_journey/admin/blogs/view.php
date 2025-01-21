<?php
require_once '../../includes/config.php';

$blog_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

// Get blog data with user info
$stmt = $conn->prepare("
    SELECT b.*, u.username,
    (SELECT COUNT(*) FROM comments WHERE blog_id = b.blog_id) as comment_count,
    (SELECT COUNT(*) FROM favorites WHERE blog_id = b.blog_id) as favorite_count
    FROM blogs b
    JOIN users u ON b.user_id = u.user_id
    WHERE b.blog_id = ?
");
$stmt->bind_param("i", $blog_id);
$stmt->execute();
$blog = $stmt->get_result()->fetch_assoc();

if (!$blog) {
    header("Location: index.php");
    exit();
}

// Get comments
$stmt = $conn->prepare("
    SELECT c.*, u.username 
    FROM comments c
    JOIN users u ON c.user_id = u.user_id
    WHERE c.blog_id = ?
    ORDER BY c.created_at DESC
");
$stmt->bind_param("i", $blog_id);
$stmt->execute();
$comments = $stmt->get_result();

$page_title = "View Blog Post";
ob_start();
?>

<div class="card">
    <div class="card-header">
        <div class="d-flex justify-content-between align-items-center">
            <h5 class="mb-0">View Blog Post</h5>
            <div>
                <a href="edit.php?id=<?php echo $blog_id; ?>" class="btn btn-primary">
                    <i class="fas fa-edit"></i> Edit
                </a>
                <a href="index.php" class="btn btn-secondary">Back to Blogs</a>
            </div>
        </div>
    </div>
    <div class="card-body">
        <?php if ($blog['image_url']): ?>
        <img src="../..<?php echo str_replace("..", "", htmlspecialchars($blog['image_url'])); ?>" class="img-fluid rounded mb-3" alt="<?php echo $blog['title']; ?>">
        <?php endif; ?>

        <h2><?php echo $blog['title']; ?></h2>
        
        <div class="text-muted mb-3">
            <small>
                Posted by <?php echo $blog['username']; ?> on 
                <?php echo date('F d, Y', strtotime($blog['created_at'])); ?> |
                <?php echo $blog['comment_count']; ?> Comments |
                <?php echo $blog['favorite_count']; ?> Favorites
            </small>
        </div>

        <div class="blog-content mb-4">
            <?php echo nl2br($blog['content']); ?>
        </div>

        <hr>

        <h5 class="mb-3">Comments (<?php echo $blog['comment_count']; ?>)</h5>
        <?php while ($comment = $comments->fetch_assoc()): ?>
        <div class="card mb-2">
            <div class="card-body">
                <div class="d-flex justify-content-between">
                    <div>
                        <strong><?php echo $comment['username']; ?></strong>
                        <small class="text-muted">
                            <?php echo date('M d, Y h:i A', strtotime($comment['created_at'])); ?>
                        </small>
                    </div>
                    <form method="POST" class="d-inline" onsubmit="return confirm('Delete this comment?');">
                        <input type="hidden" name="comment_id" value="<?php echo $comment['comment_id']; ?>">
                        <button type="submit" name="delete_comment" class="btn btn-sm btn-danger">
                            <i class="fas fa-trash"></i>
                        </button>
                    </form>
                </div>
                <p class="mb-0 mt-2"><?php echo $comment['content']; ?></p>
            </div>
        </div>
        <?php endwhile; ?>
    </div>
</div>

<?php
$content = ob_get_clean();
include '../includes/admin_layout.php';
?>
