<?php
session_start();
require_once '../includes/config.php';

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$user_id = $_SESSION['user_id'];

// Fetch user's favorite blogs
$stmt = $conn->prepare("
    SELECT b.*, u.username as author_name,
           (SELECT COUNT(*) FROM comments WHERE blog_id = b.blog_id) as comment_count,
           (SELECT COUNT(*) FROM favorites WHERE blog_id = b.blog_id) as favorite_count
    FROM blogs b 
    JOIN favorites f ON b.blog_id = f.blog_id 
    JOIN users u ON b.user_id = u.user_id 
    WHERE f.user_id = ? 
    ORDER BY f.created_at DESC
");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$favorite_blogs = $stmt->get_result();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Favorite Blogs - Pines Journey</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="../assets/css/style.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        .card-img-top {
            width: 100%;
            height: 270px;
            object-fit: cover;
        }
        .card {
            transition: transform 0.2s;
        }
        .card:hover {
            transform: translateY(-5px);
        }
        .blog-stats {
            font-size: 0.9rem;
            color: #666;
        }
        .blog-date {
            font-size: 0.85rem;
            color: #888;
        }
        .author-name {
            font-size: 0.9rem;
            color: #0066cc;
        }
    </style>
</head>
<body>
    <?php include '../includes/header.php'; ?>

    <div class="container py-4">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h2>My Favorite Blogs</h2>
            <a href="blog.php" class="btn btn-primary">
                <i class="fas fa-search"></i> Browse Blogs
            </a>
        </div>

        <?php if ($favorite_blogs->num_rows > 0): ?>
            <div class="row row-cols-1 row-cols-md-2 row-cols-lg-3 g-4">
                <?php while ($blog = $favorite_blogs->fetch_assoc()): ?>
                    <div class="col">
                        <div class="card h-100 shadow-sm">
                            <?php if ($blog['image_url']): ?>
                                <img src="<?php echo $blog['image_url']; ?>" class="card-img-top" alt="<?php echo htmlspecialchars($blog['title']); ?>">
                            <?php else: ?>
                                <div class="card-img-top bg-light d-flex align-items-center justify-content-center">
                                    <i class="fas fa-image text-muted fa-3x"></i>
                                </div>
                            <?php endif; ?>
                            
                            <div class="card-body">
                                <h5 class="card-title"><?php echo htmlspecialchars($blog['title']); ?></h5>
                                <p class="author-name">
                                    <i class="fas fa-user"></i> <?php echo htmlspecialchars($blog['author_name']); ?>
                                </p>
                                <p class="card-text text-truncate"><?php echo htmlspecialchars($blog['content']); ?></p>
                                
                                <div class="blog-stats d-flex justify-content-between align-items-center mt-3">
                                    <span><i class="far fa-heart"></i> <?php echo $blog['favorite_count']; ?> likes</span>
                                    <span><i class="far fa-comment"></i> <?php echo $blog['comment_count']; ?> comments</span>
                                </div>
                                
                                <p class="blog-date mt-2 mb-0">
                                    <i class="far fa-calendar-alt"></i> 
                                    <?php echo date('F j, Y', strtotime($blog['created_at'])); ?>
                                </p>
                            </div>
                            
                            <div class="card-footer bg-transparent">
                                <div class="d-flex justify-content-end">
                                    <a href="blog-post.php?id=<?php echo $blog['blog_id']; ?>" class="btn btn-outline-primary btn-sm">
                                        <i class="fas fa-eye"></i> View Post
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>
                <?php endwhile; ?>
            </div>
        <?php else: ?>
            <div class="text-center py-5">
                <i class="far fa-heart fa-3x text-muted mb-3"></i>
                <h3>No Favorite Blogs Yet</h3>
                <p class="text-muted">Start exploring and favorite blogs that interest you!</p>
                <a href="blog.php" class="btn btn-primary mt-2">Browse Blogs</a>
            </div>
        <?php endif; ?>
    </div>

    <?php include('../includes/footer.php'); ?>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>