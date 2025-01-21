<?php
require_once '../includes/config.php';

if (!isset($_GET['id'])) {
    header("Location: blog.php");
    exit();
}

$blog_id = (int)$_GET['id'];

// Handle post actions
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isLoggedIn()) {
    $user_id = $_SESSION['user_id'];
    
    switch ($_POST['action']) {
        case 'edit_post':
            if (isset($_POST['blog_id'], $_POST['title'], $_POST['content'])) {
                $blog_id = (int)$_POST['blog_id'];
                $title = sanitize($_POST['title']);
                $content = sanitize($_POST['content']);
                
                // Verify ownership
                $stmt = $conn->prepare("UPDATE blogs SET title = ?, content = ? WHERE blog_id = ? AND user_id = ?");
                $stmt->bind_param("ssii", $title, $content, $blog_id, $user_id);
                $stmt->execute();
            }
            break;
            
        case 'delete_post':
            if (isset($_POST['blog_id'])) {
                $blog_id = (int)$_POST['blog_id'];
                $stmt = $conn->prepare("DELETE FROM blogs WHERE blog_id = ? AND user_id = ?");
                $stmt->bind_param("ii", $blog_id, $user_id);
                if ($stmt->execute() && $stmt->affected_rows > 0) {
                    header("Location: blog.php");
                    exit();
                }
            }
            break;
            
        case 'edit_comment':
            if (isset($_POST['comment_id'], $_POST['content'])) {
                $comment_id = (int)$_POST['comment_id'];
                $content = sanitize($_POST['content']);
                
                $stmt = $conn->prepare("UPDATE comments SET content = ? WHERE comment_id = ? AND user_id = ?");
                $stmt->bind_param("sii", $content, $comment_id, $user_id);
                $stmt->execute();
            }
            break;
            
        case 'delete_comment':
            if (isset($_POST['comment_id'])) {
                $comment_id = (int)$_POST['comment_id'];
                $stmt = $conn->prepare("DELETE FROM comments WHERE comment_id = ? AND user_id = ?");
                $stmt->bind_param("ii", $comment_id, $user_id);
                $stmt->execute();
            }
            break;
            
        case 'report':
            if (isset($_POST['content_type'], $_POST['content_id'], $_POST['report_type'])) {
                $content_type = $_POST['content_type'];
                $content_id = (int)$_POST['content_id'];
                $report_type = sanitize($_POST['report_type']);
                $description = isset($_POST['description']) ? sanitize($_POST['description']) : '';
                
                $stmt = $conn->prepare("INSERT INTO reports (reporter_id, content_type, content_id, report_type, description) 
                                      VALUES (?, ?, ?, ?, ?)");
                $stmt->bind_param("isiss", $user_id, $content_type, $content_id, $report_type, $description);
                if ($stmt->execute()) {
                    $report_id = $stmt->insert_id;
                    // Create notification for admin
                    $stmt = $conn->prepare("INSERT INTO admin_notifications (report_id) VALUES (?)");
                    $stmt->bind_param("i", $report_id);
                    $stmt->execute();
                }
            }
            break;
            
        case 'toggle_favorite':
            $stmt = $conn->prepare("SELECT favorite_id FROM favorites WHERE user_id = ? AND blog_id = ?");
            $stmt->bind_param("ii", $user_id, $blog_id);
            $stmt->execute();
            $result = $stmt->get_result();

            if ($result->num_rows > 0) {
                $stmt = $conn->prepare("DELETE FROM favorites WHERE user_id = ? AND blog_id = ?");
            } else {
                $stmt = $conn->prepare("INSERT INTO favorites (user_id, blog_id) VALUES (?, ?)");
            }
            $stmt->bind_param("ii", $user_id, $blog_id);
            $stmt->execute();
            break;
            
        case 'add_comment':
            if (isset($_POST['comment']) && !empty($_POST['comment'])) {
                $content = sanitize($_POST['comment']);
                
                $stmt = $conn->prepare("INSERT INTO comments (blog_id, user_id, content) VALUES (?, ?, ?)");
                $stmt->bind_param("iis", $blog_id, $user_id, $content);
                if ($stmt->execute()) {
                    $_SESSION['success_message'] = "Comment added successfully!";
                } else {
                    $_SESSION['error_message'] = "Error adding comment. Please try again.";
                }
                header("Location: blog-post.php?id=" . $blog_id);
                exit();
            }
            break;
    }
    
    if ($_POST['action'] != 'delete_post') {
        header("Location: blog-post.php?id=" . $blog_id);
        exit();
    }
}

// Fetch blog post with user info and favorite status
$stmt = $conn->prepare("SELECT b.*, u.username, u.profile_picture,
                       (SELECT COUNT(*) FROM favorites WHERE blog_id = b.blog_id) as favorite_count,
                       " . (isLoggedIn() ? "(SELECT COUNT(*) FROM favorites WHERE blog_id = b.blog_id AND user_id = ?) as is_favorited" : "0 as is_favorited") . "
                       FROM blogs b 
                       JOIN users u ON b.user_id = u.user_id 
                       WHERE b.blog_id = ?");
if (isLoggedIn()) {
    $stmt->bind_param("ii", $_SESSION['user_id'], $blog_id);
} else {
    $stmt->bind_param("i", $blog_id);
}
$stmt->execute();
$blog = $stmt->get_result()->fetch_assoc();

if (!$blog) {
    header("Location: blog.php");
    exit();
}

// Fetch comments with user info
$stmt = $conn->prepare("SELECT c.*, u.username, u.profile_picture FROM comments c 
                       JOIN users u ON c.user_id = u.user_id 
                       WHERE c.blog_id = ? ORDER BY c.created_at DESC");
$stmt->bind_param("i", $blog_id);
$stmt->execute();
$comments = $stmt->get_result();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($blog['title']); ?> - Pine's Journey</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="../assets/css/style.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        .comment { border-bottom: 1px solid #eee; padding: 1rem 0; }
        .comment:last-child { border-bottom: none; }
        .profile-picture { width: 40px; height: 40px; object-fit: cover; }
        .favorite-btn { cursor: pointer; }
        .favorite-btn.active { color: #dc3545; }
    </style>
</head>
<body>
    <?php include '../includes/header.php'; ?>

    <div class="container py-4">
        <div class="row justify-content-center">
            <div class="col-lg-8">
                <!-- Blog Post -->
                <div class="card shadow-sm mb-4">
                    <?php if ($blog['image_url']): ?>
                        <img src="<?php echo $blog['image_url']; ?>" class="card-img-top" alt="Blog Image">
                    <?php endif; ?>
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-start mb-3">
                            <h1 class="card-title"><?php echo htmlspecialchars($blog['title']); ?></h1>
                            
                            <!-- Blog Options Dropdown -->
                            <div class="dropdown">
                                <button class="btn btn-link text-dark" type="button" data-bs-toggle="dropdown">
                                    <i class="fas fa-ellipsis-v"></i>
                                </button>
                                <ul class="dropdown-menu dropdown-menu-end">
                                    <?php if (isLoggedIn() && $blog['user_id'] == $_SESSION['user_id']): ?>
                                        <li><button class="dropdown-item" data-bs-toggle="modal" data-bs-target="#editPostModal">
                                            <i class="fas fa-edit"></i> Edit Post
                                        </button></li>
                                        <li><button class="dropdown-item text-danger" data-bs-toggle="modal" data-bs-target="#deletePostModal">
                                            <i class="fas fa-trash"></i> Delete Post
                                        </button></li>
                                    <?php elseif (isLoggedIn()): ?>
                                        <li><button class="dropdown-item text-danger" data-bs-toggle="modal" data-bs-target="#reportModal" 
                                            data-content-type="post" data-content-id="<?php echo $blog_id; ?>">
                                            <i class="fas fa-flag"></i> Report Post
                                        </button></li>
                                    <?php endif; ?>
                                </ul>
                            </div>
                        </div>

                        <div class="d-flex justify-content-between align-items-center mb-3">
                            <div class="d-flex align-items-center">
                                <img src="../<?php echo $blog['profile_picture'] ?: 'uploads/default_pic/default.jpg'; ?>" 
                                     class="rounded-circle me-2 profile-picture" alt="Profile Picture">
                                <div>
                                    <p class="mb-0"><?php echo htmlspecialchars($blog['username']); ?></p>
                                    <small class="text-muted">
                                        <i class="far fa-clock"></i> <?php echo date('F d, Y', strtotime($blog['created_at'])); ?>
                                    </small>
                                </div>
                            </div>
                            <?php if (isLoggedIn()): ?>
                                <div class="favorite-btn <?php echo $blog['is_favorited'] ? 'active' : ''; ?>" 
                                     onclick="toggleFavorite(<?php echo $blog_id; ?>)">
                                    <i class="<?php echo $blog['is_favorited'] ? 'fas' : 'far'; ?> fa-heart"></i>
                                    <span class="favorite-count"><?php echo $blog['favorite_count']; ?></span>
                                </div>
                            <?php endif; ?>
                        </div>

                        <p class="card-text"><?php echo nl2br(htmlspecialchars($blog['content'])); ?></p>
                    </div>
                </div>

                <!-- Comments Section -->
                <div class="card shadow-sm">
                    <div class="card-body">
                        <h3 class="card-title mb-4">Comments</h3>
                        
                        <?php if (isLoggedIn()): ?>
                        <!-- Comment Form -->
                        <form method="POST" class="mb-4" id="commentForm">
                            <input type="hidden" name="action" value="add_comment">
                            <?php if (isset($_SESSION['error_message'])): ?>
                                <div class="alert alert-danger">
                                    <?php echo $_SESSION['error_message']; unset($_SESSION['error_message']); ?>
                                </div>
                            <?php endif; ?>
                            <?php if (isset($_SESSION['success_message'])): ?>
                                <div class="alert alert-success">
                                    <?php echo $_SESSION['success_message']; unset($_SESSION['success_message']); ?>
                                </div>
                            <?php endif; ?>
                            <div class="mb-3">
                                <textarea class="form-control" name="comment" rows="3" required 
                                    placeholder="Write a comment..."
                                    oninvalid="this.setCustomValidity('Please write your comment')"
                                    oninput="this.setCustomValidity('')"></textarea>
                            </div>
                            <button type="submit" class="btn btn-primary">Post Comment</button>
                        </form>
                        <?php else: ?>
                        <div class="alert alert-info">
                            Please <a href="login.php">login</a> to post a comment.
                        </div>
                        <?php endif; ?>

                        <!-- Comments List -->
                        <?php if ($comments->num_rows > 0): ?>
                            <?php while ($comment = $comments->fetch_assoc()): ?>
                                <div class="comment">
                                    <div class="d-flex justify-content-between">
                                        <div class="d-flex">
                                            <img src="../<?php echo $comment['profile_picture'] ?: 'uploads/default_pic/default.jpg'; ?>" 
                                                 class="rounded-circle me-2 profile-picture" alt="Profile Picture">
                                            <div>
                                                <div class="fw-bold"><?php echo htmlspecialchars($comment['username']); ?></div>
                                                <div class="comment-content-<?php echo $comment['comment_id']; ?>">
                                                    <?php echo nl2br(htmlspecialchars($comment['content'])); ?>
                                                </div>
                                                <small class="text-muted">
                                                    <?php echo date('F d, Y g:i A', strtotime($comment['created_at'])); ?>
                                                </small>
                                            </div>
                                        </div>
                                        
                                        <!-- Comment Options Dropdown -->
                                        <?php if (isLoggedIn()): ?>
                                        <div class="dropdown">
                                            <button class="btn btn-link text-dark" type="button" data-bs-toggle="dropdown">
                                                <i class="fas fa-ellipsis-v"></i>
                                            </button>
                                            <ul class="dropdown-menu dropdown-menu-end">
                                                <?php if ($comment['user_id'] == $_SESSION['user_id']): ?>
                                                    <li><button class="dropdown-item" onclick="editComment(<?php echo $comment['comment_id']; ?>, '<?php echo addslashes($comment['content']); ?>')">
                                                        <i class="fas fa-edit"></i> Edit
                                                    </button></li>
                                                    <li><button class="dropdown-item text-danger" onclick="deleteComment(<?php echo $comment['comment_id']; ?>)">
                                                        <i class="fas fa-trash"></i> Delete
                                                    </button></li>
                                                <?php else: ?>
                                                    <li><button class="dropdown-item text-danger" data-bs-toggle="modal" data-bs-target="#reportModal"
                                                        data-content-type="comment" data-content-id="<?php echo $comment['comment_id']; ?>">
                                                        <i class="fas fa-flag"></i> Report
                                                    </button></li>
                                                <?php endif; ?>
                                            </ul>
                                        </div>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            <?php endwhile; ?>
                        <?php else: ?>
                            <p class="text-muted">No comments yet. Be the first to comment!</p>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Edit Post Modal -->
    <div class="modal fade" id="editPostModal" tabindex="-1">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Edit Post</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <form method="POST">
                    <div class="modal-body">
                        <input type="hidden" name="action" value="edit_post">
                        <input type="hidden" name="blog_id" value="<?php echo $blog_id; ?>">
                        <div class="mb-3">
                            <label class="form-label">Title</label>
                            <input type="text" class="form-control" name="title" value="<?php echo htmlspecialchars($blog['title']); ?>" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Content</label>
                            <textarea class="form-control" name="content" rows="10" required><?php echo htmlspecialchars($blog['content']); ?></textarea>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-primary">Save Changes</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Delete Post Modal -->
    <div class="modal fade" id="deletePostModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Delete Post</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    Are you sure you want to delete this post? This action cannot be undone.
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <form method="POST" class="d-inline">
                        <input type="hidden" name="action" value="delete_post">
                        <input type="hidden" name="blog_id" value="<?php echo $blog_id; ?>">
                        <button type="submit" class="btn btn-danger">Delete</button>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- Report Modal -->
    <div class="modal fade" id="reportModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Report Content</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <form method="POST">
                    <div class="modal-body">
                        <input type="hidden" name="action" value="report">
                        <input type="hidden" name="content_type" id="reportContentType">
                        <input type="hidden" name="content_id" id="reportContentId">
                        <div class="mb-3">
                            <label class="form-label">Reason for Report</label>
                            <select class="form-select" name="report_type" required>
                                <option value="spam">Spam</option>
                                <option value="inappropriate">Inappropriate Content</option>
                                <option value="harassment">Harassment</option>
                                <option value="other">Other</option>
                            </select>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Description</label>
                            <textarea class="form-control" name="description" rows="3" placeholder="Please provide more details..."></textarea>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-danger">Submit Report</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <?php include '../includes/footer.php'; ?>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Handle comment editing
        function editComment(commentId, content) {
            const commentDiv = document.querySelector(`.comment-content-${commentId}`);
            const currentContent = content;
            
            commentDiv.innerHTML = `
                <form method="POST" class="edit-comment-form">
                    <input type="hidden" name="action" value="edit_comment">
                    <input type="hidden" name="comment_id" value="${commentId}">
                    <div class="mb-2">
                        <textarea class="form-control" name="content" required>${currentContent}</textarea>
                    </div>
                    <div>
                        <button type="submit" class="btn btn-primary btn-sm">Save</button>
                        <button type="button" class="btn btn-secondary btn-sm" onclick="cancelEdit(${commentId}, '${content}')">Cancel</button>
                    </div>
                </form>
            `;
        }

        function cancelEdit(commentId, content) {
            const commentDiv = document.querySelector(`.comment-content-${commentId}`);
            commentDiv.innerHTML = content;
        }

        function deleteComment(commentId) {
            if (confirm('Are you sure you want to delete this comment?')) {
                const form = document.createElement('form');
                form.method = 'POST';
                form.innerHTML = `
                    <input type="hidden" name="action" value="delete_comment">
                    <input type="hidden" name="comment_id" value="${commentId}">
                `;
                document.body.appendChild(form);
                form.submit();
            }
        }

        // Handle report modal
        document.getElementById('reportModal').addEventListener('show.bs.modal', function (event) {
            const button = event.relatedTarget;
            const contentType = button.getAttribute('data-content-type');
            const contentId = button.getAttribute('data-content-id');
            
            document.getElementById('reportContentType').value = contentType;
            document.getElementById('reportContentId').value = contentId;
        });

        // Handle favorites
        function toggleFavorite(blogId) {
            const form = document.createElement('form');
            form.method = 'POST';
            form.innerHTML = `<input type="hidden" name="action" value="toggle_favorite">`;
            document.body.appendChild(form);
            form.submit();
        }
    </script>
</body>
</html>