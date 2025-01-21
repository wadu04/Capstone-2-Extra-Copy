<?php
require_once '../../includes/config.php';

// Handle blog deletion
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['delete_blog'])) {
    $blog_id = (int)$_POST['blog_id'];
    $conn->query("DELETE FROM comments WHERE blog_id = $blog_id");
    $conn->query("DELETE FROM favorites WHERE blog_id = $blog_id");
    $conn->query("DELETE FROM blogs WHERE blog_id = $blog_id");
    header("Location: index.php");
    exit();
}

// Get blogs with user info and stats
$sql = "SELECT b.*, u.username, 
        (SELECT COUNT(*) FROM comments WHERE blog_id = b.blog_id) as comment_count,
        (SELECT COUNT(*) FROM favorites WHERE blog_id = b.blog_id) as favorite_count
        FROM blogs b
        JOIN users u ON b.user_id = u.user_id
        ORDER BY b.created_at DESC";
$result = $conn->query($sql);

$page_title = "Blog Management";
ob_start();
?>

<div class="card">
    <div class="card-header">
        <h5 class="mb-0">Blog Posts</h5>
    </div>
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-hover">
                <thead>
                    <tr>
                        <th>Title</th>
                        <th>Author</th>
                        <th>Created</th>
                        <th>Comments</th>
                        <th>Favorites</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php while ($blog = $result->fetch_assoc()): ?>
                    <tr>
                        <td><?php echo $blog['title']; ?></td>
                        <td><?php echo $blog['username']; ?></td>
                        <td><?php echo date('M d, Y', strtotime($blog['created_at'])); ?></td>
                        <td><?php echo $blog['comment_count']; ?></td>
                        <td><?php echo $blog['favorite_count']; ?></td>
                        <td class="table-actions">
                            <a href="view.php?id=<?php echo $blog['blog_id']; ?>" class="btn btn-sm btn-info">
                                <i class="fas fa-eye"></i>
                            </a>
                            <a href="edit.php?id=<?php echo $blog['blog_id']; ?>" class="btn btn-sm btn-primary">
                                <i class="fas fa-edit"></i>
                            </a>
                            <form method="POST" class="d-inline" onsubmit="return confirm('Are you sure you want to delete this blog post?');">
                                <input type="hidden" name="blog_id" value="<?php echo $blog['blog_id']; ?>">
                                <button type="submit" name="delete_blog" class="btn btn-sm btn-danger">
                                    <i class="fas fa-trash"></i>
                                </button>
                            </form>
                        </td>
                    </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<?php
$content = ob_get_clean();
include '../includes/admin_layout.php';
?>
