<?php
require_once '../../includes/config.php';

$blog_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

// Get blog data
$stmt = $conn->prepare("SELECT * FROM blogs WHERE blog_id = ?");
$stmt->bind_param("i", $blog_id);
$stmt->execute();
$blog = $stmt->get_result()->fetch_assoc();

if (!$blog) {
    header("Location: index.php");
    exit();
}

$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $title = sanitize($_POST['title']);
    $content = sanitize($_POST['content']);
    $image_url = sanitize($_POST['image_url']);

    $stmt = $conn->prepare("UPDATE blogs SET title = ?, content = ?, image_url = ? WHERE blog_id = ?");
    $stmt->bind_param("sssi", $title, $content, $image_url, $blog_id);
    
    if ($stmt->execute()) {
        $success = "Blog post updated successfully";
        // Refresh blog data
        $stmt = $conn->prepare("SELECT * FROM blogs WHERE blog_id = ?");
        $stmt->bind_param("i", $blog_id);
        $stmt->execute();
        $blog = $stmt->get_result()->fetch_assoc();
    } else {
        $error = "Failed to update blog post";
    }
}

$page_title = "Edit Blog Post";
ob_start();
?>

<div class="card">
    <div class="card-header">
        <div class="d-flex justify-content-between align-items-center">
            <h5 class="mb-0">Edit Blog Post</h5>
            <div>
                <a href="view.php?id=<?php echo $blog_id; ?>" class="btn btn-info">View Post</a>
                <a href="index.php" class="btn btn-secondary">Back to Blogs</a>
            </div>
        </div>
    </div>
    <div class="card-body">
        <?php if ($error): ?>
            <div class="alert alert-danger"><?php echo $error; ?></div>
        <?php endif; ?>
        <?php if ($success): ?>
            <div class="alert alert-success"><?php echo $success; ?></div>
        <?php endif; ?>

        <form method="POST" class="admin-form">
            <div class="mb-3">
                <label for="title" class="form-label">Title</label>
                <input type="text" class="form-control" id="title" name="title" value="<?php echo $blog['title']; ?>" required>
            </div>

            <div class="mb-3">
                <label for="content" class="form-label">Content</label>
                <textarea class="form-control" id="content" name="content" rows="10" required><?php echo $blog['content']; ?></textarea>
            </div>

            <div class="mb-3">
                <label for="image_url" class="form-label">Image URL</label>
                <input type="url" class="form-control" id="image_url" name="image_url" value="<?php echo $blog['image_url']; ?>">
            </div>

            <?php if ($blog['image_url']): ?>
            <div class="mb-3">
                <label class="form-label">Current Image</label><br>
                <img src="../..<?php echo str_replace("..", "", htmlspecialchars($blog['image_url'])); ?>" class="img-fluid rounded" style="max-height: 200px;" alt="Current blog image">
            </div>
            
            <?php endif; ?>

            <button type="submit" class="btn btn-primary">Update Blog Post</button>
        </form>
    </div>
</div>

<?php
$content = ob_get_clean();
include '../includes/admin_layout.php';
?>
