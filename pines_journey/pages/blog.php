<?php
require_once '../includes/config.php';

// Handle blog post creation
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isLoggedIn()) {
    if (isset($_POST['action'])) {
        switch ($_POST['action']) {
            case 'create_post':
                $title = sanitize($_POST['title']);
                $content = sanitize($_POST['content']);
                $user_id = $_SESSION['user_id'];
                
                // Check if image was uploaded
                if (!isset($_FILES['image']) || $_FILES['image']['error'] != 0) {
                    echo "<script>alert('Please upload an image for your blog post.'); window.history.back();</script>";
                    exit;
                }

                // Handle file upload
                $image_url = '';
                if(isset($_FILES['image']) && $_FILES['image']['error'] == 0) {
                    $target_dir = "../uploads/blog/";
                    if (!file_exists($target_dir)) {
                        mkdir($target_dir, 0777, true);
                    }
                    $file_extension = strtolower(pathinfo($_FILES["image"]["name"], PATHINFO_EXTENSION));
                    $new_filename = uniqid() . '.' . $file_extension;
                    $target_file = $target_dir . $new_filename;
                    
                    if(move_uploaded_file($_FILES["image"]["tmp_name"], $target_file)) {
                        $image_url = "../uploads/blog/" . $new_filename;
                    }
                }

                $stmt = $conn->prepare("INSERT INTO blogs (user_id, title, content, image_url) VALUES (?, ?, ?, ?)");
                $stmt->bind_param("isss", $user_id, $title, $content, $image_url);
                $stmt->execute();
                break;

            case 'add_comment':
                $blog_id = (int)$_POST['blog_id'];
                $content = sanitize($_POST['comment']);
                $user_id = $_SESSION['user_id'];

                $stmt = $conn->prepare("INSERT INTO comments (blog_id, user_id, content) VALUES (?, ?, ?)");
                $stmt->bind_param("iis", $blog_id, $user_id, $content);
                $stmt->execute();
                break;

            case 'toggle_favorite':
                $blog_id = (int)$_POST['blog_id'];
                $user_id = $_SESSION['user_id'];

                // Check if already favorited
                $stmt = $conn->prepare("SELECT favorite_id FROM favorites WHERE user_id = ? AND blog_id = ?");
                $stmt->bind_param("ii", $user_id, $blog_id);
                $stmt->execute();
                $result = $stmt->get_result();

                if ($result->num_rows > 0) {
                    // Remove favorite
                    $stmt = $conn->prepare("DELETE FROM favorites WHERE user_id = ? AND blog_id = ?");
                    $stmt->bind_param("ii", $user_id, $blog_id);
                } else {
                    // Add favorite
                    $stmt = $conn->prepare("INSERT INTO favorites (user_id, blog_id) VALUES (?, ?)");
                    $stmt->bind_param("ii", $user_id, $blog_id);
                }
                $stmt->execute();
                break;

            case 'report':
                if (isset($_POST['content_type'], $_POST['content_id'], $_POST['report_type'])) {
                    $content_type = $_POST['content_type'];
                    $content_id = (int)$_POST['content_id'];
                    $report_type = sanitize($_POST['report_type']);
                    $description = isset($_POST['description']) ? sanitize($_POST['description']) : '';
                    $user_id = $_SESSION['user_id'];

                    $stmt = $conn->prepare("INSERT INTO reports (reporter_id, content_type, content_id, report_type, description) VALUES (?, ?, ?, ?, ?)");
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
        }
        header("Location: blog.php");
        exit();
    }
}

// Get blog posts with user info, favorite counts, and profile pictures
$sql = "SELECT b.*, u.username, u.profile_picture,
        (SELECT COUNT(*) FROM favorites WHERE blog_id = b.blog_id) as favorite_count,
        " . (isLoggedIn() ? "(SELECT COUNT(*) FROM favorites WHERE blog_id = b.blog_id AND user_id = " . $_SESSION['user_id'] . ") as is_favorited" : "0 as is_favorited") . ",
        (SELECT COUNT(*) FROM comments WHERE blog_id = b.blog_id) as comment_count
        FROM blogs b
        JOIN users u ON b.user_id = u.user_id";

// Add search functionality
if (isset($_GET['search']) && !empty($_GET['search'])) {
    $search = '%' . $conn->real_escape_string($_GET['search']) . '%';
    $sql .= " WHERE b.title LIKE ? OR b.content LIKE ?";
}

$sql .= " ORDER BY b.created_at DESC";

if (isset($_GET['search']) && !empty($_GET['search'])) {
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ss", $search, $search);
    $stmt->execute();
    $result = $stmt->get_result();
} else {
    $result = $conn->query($sql);
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Blog - Pine's Journey</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="../assets/css/style.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        .dropdown-menu-end {
            right: 0;
            left: auto;
        }
        .report-btn {
            color: #dc3545;
        }
        .card-img-top {
            width: 100%;
            height: 270px;
            object-fit: cover;
        }
        .card {
            height: auto;
        }
        .favorite-btn {
            cursor: pointer;
        }
        .favorite-btn.active {
            color: #dc3545;
        }
        .favorite-btn i {
            transition: color 0.3s ease;
        }
        .search-container {
            position: relative;
            width: 350px;
            margin: 0 auto 2rem auto;
        }
        .search-input {
            width: 100%;
            padding: 8px 35px 8px 15px;
            border: 1px solid #ddd;
            border-radius: 20px;
            transition: all 0.3s ease;
            font-size: 1rem;
            box-shadow: 0 2px 5px rgba(0,0,0,0.1);
        }
        .search-input:focus {
            outline: none;
            border-color: #0d6efd;
            box-shadow: 0 3px 8px rgba(13,110,253,0.2);
        }
        .search-icon {
            position: absolute;
            right: 12px;
            top: 50%;
            transform: translateY(-50%);
            cursor: pointer;
            font-size: 1.1rem;
            color: #555;
            transition: color 0.3s ease;
        }
        .search-icon:hover {
            color: #0d6efd;
        }
    </style>
</head>
<body>
    <?php include '../includes/header.php'; ?>

    <div class="container py-4">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h2>Travel Blog</h2>
            <?php if (isLoggedIn()): ?>
            <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#createPostModal">
                <i class="fas fa-plus"></i> Create Post
            </button>
            <?php endif; ?>
        </div>

        <!-- Centered Search Container -->
        <div class="search-container">
            <input type="text" class="search-input" id="searchInput" placeholder="Search blogs...">
            <i class="fas fa-search search-icon" id="searchIcon"></i>
        </div>

        <div class="row g-4">
            <?php while ($blog = $result->fetch_assoc()): ?>
            <div class="col-md-6">
                <div class="card h-100 shadow-sm">
                    <div class="position-relative">
                        <?php if ($blog['image_url']): ?>
                            <img src="<?php echo $blog['image_url']; ?>" class="card-img-top" alt="Blog Image">
                        <?php endif; ?>
                        <!-- Three-dot menu for blog post -->
                        <div class="position-absolute top-0 end-0 m-2">
                            <div class="dropdown">
                                <button class="btn btn-light btn-sm" type="button" data-bs-toggle="dropdown" aria-expanded="false">
                                    <i class="fas fa-ellipsis-v"></i>
                                </button>
                                <ul class="dropdown-menu dropdown-menu-end">
                                    <li><button class="dropdown-item report-btn" data-bs-toggle="modal" data-bs-target="#reportModal" 
                                        data-content-type="post" data-content-id="<?php echo $blog['blog_id']; ?>">
                                        <i class="fas fa-flag"></i> Report Post
                                    </button></li>
                                </ul>
                            </div>
                        </div>
                    </div>
                    <div class="card-body">
                        <h5 class="card-title"><?php echo htmlspecialchars($blog['title']); ?></h5>
                        <p class="text-muted small">
                            <img src="../<?php echo $blog['profile_picture'] ?: 'uploads/default_pic/default.jpg'; ?>" 
                                 class="rounded-circle me-2" style="width: 30px; height: 30px; object-fit: cover;">
                            <?php echo htmlspecialchars($blog['username']); ?> | 
                            <i class="far fa-clock"></i> <?php echo date('M d, Y', strtotime($blog['created_at'])); ?>
                        </p>
                        <p class="card-text"><?php echo substr(htmlspecialchars($blog['content']), 0, 150); ?>...</p>
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <?php if (isLoggedIn()): ?>
                                <span class="me-3 favorite-btn <?php echo $blog['is_favorited'] ? 'active' : ''; ?>" 
                                      data-blog-id="<?php echo $blog['blog_id']; ?>" 
                                      onclick="toggleFavorite(this, <?php echo $blog['blog_id']; ?>)">
                                    <i class="<?php echo $blog['is_favorited'] ? 'fas' : 'far'; ?> fa-heart"></i>
                                    <span class="favorite-count"><?php echo $blog['favorite_count']; ?></span>
                                </span>
                                <?php else: ?>
                                <span class="me-3">
                                    <i class="far fa-heart"></i> <?php echo $blog['favorite_count']; ?>
                                </span>
                                <?php endif; ?>
                                <span>
                                    <i class="far fa-comment"></i> <?php echo $blog['comment_count']; ?>
                                </span>
                            </div>
                            <a href="blog-post.php?id=<?php echo $blog['blog_id']; ?>" class="btn btn-primary">
                                Read More
                            </a>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Blog Modal -->
            <div class="modal fade" id="blogModal<?php echo $blog['blog_id']; ?>" tabindex="-1" aria-hidden="true">
                <div class="modal-dialog modal-lg">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h5 class="modal-title"><?php echo $blog['title']; ?></h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                        </div>
                        <div class="modal-body">
                            <?php if ($blog['image_url']): ?>
                            <img src="<?php echo $blog['image_url']; ?>" class="img-fluid rounded mb-3" alt="<?php echo $blog['title']; ?>">
                            <?php endif; ?>

                            <div class="d-flex justify-content-between align-items-center mb-3">
                                <p class="text-muted mb-0">
                                    <i class="fas fa-user"></i> <?php echo $blog['username']; ?> | 
                                    <i class="far fa-clock"></i> <?php echo date('F d, Y', strtotime($blog['created_at'])); ?>
                                </p>
                                <?php if (isLoggedIn()): ?>
                                <form method="POST" class="d-inline">
                                    <input type="hidden" name="action" value="toggle_favorite">
                                    <input type="hidden" name="blog_id" value="<?php echo $blog['blog_id']; ?>">
                                    <button type="submit" class="btn btn-outline-danger btn-sm">
                                        <i class="far fa-heart"></i> Favorite (<?php echo $blog['favorite_count']; ?>)
                                    </button>
                                </form>
                                <?php endif; ?>
                            </div>

                            <div class="blog-content mb-4" style="white-space: pre-wrap;">
                                <?php echo $blog['content']; ?>
                            </div>

                            <hr>

                            <!-- Comments Section -->
                            <h6 class="mb-3">Comments (<?php echo $blog['comment_count']; ?>)</h6>
                            <?php
                            $comments_sql = "SELECT c.*, u.username FROM comments c 
                                           JOIN users u ON c.user_id = u.user_id 
                                           WHERE c.blog_id = ? ORDER BY c.created_at DESC";
                            $stmt = $conn->prepare($comments_sql);
                            $stmt->bind_param("i", $blog['blog_id']);
                            $stmt->execute();
                            $comments = $stmt->get_result();
                            ?>

                            <?php while ($comment = $comments->fetch_assoc()): ?>
                            <div class="comment mb-2">
                                <div class="d-flex justify-content-between align-items-start">
                                    <div>
                                        <strong><?php echo htmlspecialchars($comment['username']); ?></strong>
                                        <p><?php echo htmlspecialchars($comment['content']); ?></p>
                                    </div>
                                    <!-- Three-dot menu for comment -->
                                    <div class="dropdown">
                                        <button class="btn btn-light btn-sm" type="button" data-bs-toggle="dropdown" aria-expanded="false">
                                            <i class="fas fa-ellipsis-v"></i>
                                        </button>
                                        <ul class="dropdown-menu dropdown-menu-end">
                                            <li><button class="dropdown-item report-btn" data-bs-toggle="modal" data-bs-target="#reportModal" 
                                                data-content-type="comment" data-content-id="<?php echo $comment['comment_id']; ?>">
                                                <i class="fas fa-flag"></i> Report Comment
                                            </button></li>
                                        </ul>
                                    </div>
                                </div>
                            </div>
                            <?php endwhile; ?>

                            <?php if (isLoggedIn()): ?>
                            <form method="POST" class="mt-3">
                                <input type="hidden" name="action" value="add_comment">
                                <input type="hidden" name="blog_id" value="<?php echo $blog['blog_id']; ?>">
                                <div class="mb-3">
                                    <textarea class="form-control" name="comment" rows="2" required placeholder="Write a comment..."></textarea>
                                </div>
                                <button type="submit" class="btn btn-primary">Post Comment</button>
                            </form>
                            <?php else: ?>
                            <p class="text-muted">Please <a href="login.php">login</a> to comment.</p>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>
            <?php endwhile; ?>
        </div>
    </div>

    <!-- Create Post Modal -->
    <?php if (isLoggedIn()): ?>
    <div class="modal fade" id="createPostModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Create New Post</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <form method="POST" enctype="multipart/form-data">
                        <input type="hidden" name="action" value="create_post">
                        <div class="mb-3">
                            <label for="title" class="form-label">Title</label>
                            <input type="text" class="form-control" id="title" name="title" required>
                        </div>
                        <div class="mb-3">
                            <label for="content" class="form-label">Content</label>
                            <textarea class="form-control" id="content" name="content" rows="5" required></textarea>
                        </div>
                        <div class="mb-3">
                            <label for="image" class="form-label">Image</label>
                            <input type="file" class="form-control" id="image" name="image" accept="image/*" required>
                        </div>
                        <button type="submit" class="btn btn-primary">Create Post</button>
                    </form>
                </div>
            </div>
        </div>
    </div>
    <?php endif; ?>

    <!-- Report Modal -->
    <div class="modal fade" id="reportModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Report Content</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form action="blog.php" method="POST">
                    <div class="modal-body">
                        <input type="hidden" name="action" value="report">
                        <input type="hidden" name="content_type" id="reportContentType">
                        <input type="hidden" name="content_id" id="reportContentId">
                        
                        <div class="mb-3">
                            <label class="form-label">Report Type</label>
                            <select class="form-select" name="report_type" required>
                                <option value="">Select a reason</option>
                                <option value="spam">Spam</option>
                                <option value="harassment">Harassment</option>
                                <option value="inappropriate">Inappropriate Content</option>
                                <option value="violence">Violence</option>
                                <option value="other">Other</option>
                            </select>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Description (Optional)</label>
                            <textarea class="form-control" name="description" rows="3" placeholder="Provide additional details about your report"></textarea>
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
        document.addEventListener('DOMContentLoaded', function() {
            const reportModal = document.getElementById('reportModal');
            if (reportModal) {
                reportModal.addEventListener('show.bs.modal', function(event) {
                    const button = event.relatedTarget;
                    const contentType = button.getAttribute('data-content-type');
                    const contentId = button.getAttribute('data-content-id');
                    
                    document.getElementById('reportContentType').value = contentType;
                    document.getElementById('reportContentId').value = contentId;
                });
            }
        });
    </script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const searchIcon = document.getElementById('searchIcon');
            const searchInput = document.getElementById('searchInput');

            // Handle search when user types
            searchInput.addEventListener('keypress', function(e) {
                if (e.key === 'Enter') {
                    const searchTerm = searchInput.value.trim();
                    if (searchTerm) {
                        window.location.href = `blog.php?search=${encodeURIComponent(searchTerm)}`;
                    } else {
                        window.location.href = 'blog.php';
                    }
                }
            });

            // Clear search and return to main page when input is cleared
            searchInput.addEventListener('input', function() {
                if (this.value.trim() === '' && new URLSearchParams(window.location.search).has('search')) {
                    window.location.href = 'blog.php';
                }
            });

            // If there's a search parameter in URL, populate the search input
            const urlParams = new URLSearchParams(window.location.search);
            if (urlParams.has('search')) {
                searchInput.value = urlParams.get('search');
            }
        });

        function toggleFavorite(element, blogId) {
            if (!element) return;
            
            fetch('blog.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded',
                },
                body: `action=toggle_favorite&blog_id=${blogId}`
            })
            .then(response => response.text())
            .then(() => {
                // Toggle active class
                element.classList.toggle('active');
                
                // Toggle heart icon
                const heartIcon = element.querySelector('i');
                heartIcon.classList.toggle('far');
                heartIcon.classList.toggle('fas');
                
                // Update favorite count
                const countElement = element.querySelector('.favorite-count');
                let currentCount = parseInt(countElement.textContent);
                countElement.textContent = element.classList.contains('active') ? currentCount + 1 : currentCount - 1;
            })
            .catch(error => console.error('Error:', error));
        }
    </script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const createPostForm = document.querySelector('#createPostModal form');
            createPostForm.addEventListener('submit', function(e) {
                const imageInput = this.querySelector('#image');
                if (!imageInput.files || imageInput.files.length === 0) {
                    e.preventDefault();
                    alert('Please select an image for your blog post.');
                    return false;
                }
            });
        });
    </script>
</body>
</html>
