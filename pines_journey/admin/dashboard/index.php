<?php
require_once '../../includes/config.php';

// Get statistics
$stats = [
    'users' => $conn->query("SELECT COUNT(*) as count FROM users")->fetch_assoc()['count'],
    'spots' => $conn->query("SELECT COUNT(*) as count FROM tourist_spots")->fetch_assoc()['count'],
    'events' => $conn->query("SELECT COUNT(*) as count FROM events")->fetch_assoc()['count']
];

// Get recent blogs with more details
$recent_blogs = $conn->query("
    SELECT b.*, u.username, 
           (SELECT COUNT(*) FROM comments WHERE blog_id = b.blog_id) as comment_count,
           (SELECT COUNT(*) FROM favorites WHERE blog_id = b.blog_id) as favorite_count
    FROM blogs b 
    JOIN users u ON b.user_id = u.user_id 
    ORDER BY b.created_at DESC 
    LIMIT 5
");

// Get upcoming events
$upcoming_events = $conn->query("
    SELECT * FROM events 
    WHERE start_datetime >= NOW() 
    ORDER BY start_datetime ASC 
    LIMIT 5
");

// Get most visited tourist spots with reviews
$popular_spots = $conn->query("
    SELECT ts.*, 
           COUNT(DISTINCT r.review_id) as review_count,
           AVG(r.rating) as avg_rating
    FROM tourist_spots ts
    LEFT JOIN reviews r ON ts.spot_id = r.spot_id
    GROUP BY ts.spot_id
    ORDER BY review_count DESC, avg_rating DESC
    LIMIT 5
");

// Get top users by scans
$top_users = $conn->query("
    SELECT u.username, COUNT(us.scan_id) as scan_count 
    FROM users u
    LEFT JOIN user_scans us ON u.user_id = us.user_id
    GROUP BY u.user_id, u.username
    ORDER BY scan_count DESC
    LIMIT 5
");

$page_title = "Dashboard";
ob_start();
?>

<div class="row g-4">
    <!-- Statistics Cards -->
    <div class="col-md-4">
        <div class="card stat-card h-100">
            <div class="card-body bg-primary text-white rounded">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h6 class="card-title mb-0">Total Users</h6>
                        <h2 class="mb-0"><?php echo $stats['users']; ?></h2>
                    </div>
                    <div class="icon">
                        <i class="fas fa-users fa-2x"></i>
                    </div>
                </div>
                <div class="mt-3">
                    <small>
                        <?php 
                        $today_users = $conn->query("SELECT COUNT(*) as count FROM users WHERE DATE(created_at) = CURDATE()")->fetch_assoc()['count'];
                        echo "+{$today_users} today";
                        ?>
                    </small>
                </div>
            </div>
        </div>
    </div>
    
    <div class="col-md-4">
        <div class="card stat-card h-100">
            <div class="card-body bg-success text-white rounded">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h6 class="card-title mb-0">Tourist Spots</h6>
                        <h2 class="mb-0"><?php echo $stats['spots']; ?></h2>
                    </div>
                    <div class="icon">
                        <i class="fas fa-map-marker-alt fa-2x"></i>
                    </div>
                </div>
                <div class="mt-3">
                    <small>
                        <?php 
                        $review_count = $conn->query("SELECT COUNT(*) as count FROM reviews")->fetch_assoc()['count'];
                        echo "{$review_count} reviews";
                        ?>
                    </small>
                </div>
            </div>
        </div>
    </div>
    
    <div class="col-md-4">
        <div class="card stat-card h-100">
            <div class="card-body bg-warning text-white rounded">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h6 class="card-title mb-0">Events</h6>
                        <h2 class="mb-0"><?php echo $stats['events']; ?></h2>
                    </div>
                    <div class="icon">
                        <i class="fas fa-calendar-alt fa-2x"></i>
                    </div>
                </div>
                <div class="mt-3">
                    <small>
                        <?php 
                        $upcoming = $conn->query("SELECT COUNT(*) as count FROM events WHERE start_datetime >= NOW()")->fetch_assoc()['count'];
                        echo "{$upcoming} upcoming";
                        ?>
                    </small>
                </div>
            </div>
        </div>
    </div>

    <!-- Top Users by Scans -->
    <div class="col-md-4">
        <div class="card">
            <div class="card-header">
                <h5 class="card-title mb-0">Top Users</h5>
            </div>
            <div class="card-body">
                <?php while ($user = $top_users->fetch_assoc()): ?>
                <div class="d-flex align-items-center mb-3">
                    <div class="flex-shrink-0">
                        <div class="bg-light rounded-circle p-2">
                            <i class="fas fa-qrcode text-primary"></i>
                        </div>
                    </div>
                    <div class="flex-grow-1 ms-3">
                        <h6 class="mb-0"><?php echo $user['username']; ?></h6>
                        <small class="text-muted">
                            <i class="fas fa-qrcode"></i> <?php echo $user['scan_count']; ?> scans
                        </small>
                    </div>
                </div>
                <?php endwhile; ?>
            </div>
        </div>
    </div>

    <!-- Popular Tourist Spots -->
    <div class="col-md-8">
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h5 class="card-title mb-0">Popular Tourist Spots</h5>
                <a href="../tourist_spots/index.php" class="btn btn-sm btn-primary">View All</a>
            </div>
            <div class="card-body">
                <?php while ($spot = $popular_spots->fetch_assoc()): ?>
                <div class="d-flex align-items-center mb-3">
                    <div class="flex-shrink-0">
                        <?php if ($spot['image_url']): ?>
                        <img src="../../uploads/<?php echo $spot['image_url']; ?>" 
                             alt="<?php echo $spot['name']; ?>" 
                             class="rounded"
                             style="width: 48px; height: 48px; object-fit: cover;">
                        <?php else: ?>
                        <div class="bg-light rounded" style="width: 48px; height: 48px;">
                            <i class="fas fa-image text-muted"></i>
                        </div>
                        <?php endif; ?>
                    </div>
                    <div class="flex-grow-1 ms-3">
                        <h6 class="mb-0"><?php echo $spot['name']; ?></h6>
                        <small class="text-muted">
                            <i class="fas fa-star text-warning"></i> 
                            <?php echo number_format($spot['avg_rating'], 1); ?> 
                            (<?php echo $spot['review_count']; ?> reviews)
                        </small>
                    </div>
                </div>
                <?php endwhile; ?>
            </div>
        </div>
    </div>

    <!-- Recent Blog Posts -->
    <div class="col-md-6">
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h5 class="card-title mb-0">Recent Blog Posts</h5>
                <a href="../blogs/index.php" class="btn btn-sm btn-primary">View All</a>
            </div>
            <div class="card-body">
                <div class="list-group list-group-flush">
                    <?php while ($blog = $recent_blogs->fetch_assoc()): ?>
                    <div class="list-group-item">
                        <div class="d-flex justify-content-between align-items-start">
                            <div>
                                <h6 class="mb-1"><?php echo $blog['title']; ?></h6>
                                <p class="mb-1 text-muted small"><?php echo substr($blog['content'], 0, 100); ?>...</p>
                                <small class="text-muted">
                                    <i class="fas fa-user"></i> <?php echo $blog['username']; ?> | 
                                    <i class="fas fa-calendar"></i> <?php echo date('M d, Y', strtotime($blog['created_at'])); ?> |
                                    <i class="fas fa-comments"></i> <?php echo $blog['comment_count']; ?> |
                                    <i class="fas fa-heart"></i> <?php echo $blog['favorite_count']; ?>
                                </small>
                            </div>
                            <div class="ms-2">
                                <a href="../blogs/view.php?id=<?php echo $blog['blog_id']; ?>" 
                                   class="btn btn-sm btn-outline-primary">View</a>
                            </div>
                        </div>
                    </div>
                    <?php endwhile; ?>
                </div>
            </div>
        </div>
    </div>

    <!-- Upcoming Events -->
    <div class="col-md-6">
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h5 class="card-title mb-0">Upcoming Events</h5>
                <a href="../events/index.php" class="btn btn-sm btn-primary">View All</a>
            </div>
            <div class="card-body">
                <?php while ($event = $upcoming_events->fetch_assoc()): ?>
                <div class="card mb-3">
                    <?php if ($event['image_url']): ?>
                    <img src="../../uploads/<?php echo $event['image_url']; ?>" 
                         class="card-img-top" alt="<?php echo $event['title']; ?>"
                         style="height: 150px; object-fit: cover;">
                    <?php endif; ?>
                    <div class="card-body">
                        <h5 class="card-title"><?php echo $event['title']; ?></h5>
                        <p class="card-text text-muted small"><?php echo substr($event['description'], 0, 100); ?>...</p>
                        <div class="mt-2">
                            <small class="text-muted">
                                <i class="far fa-calendar"></i> <?php echo date('M d, Y', strtotime($event['start_datetime'])); ?><br>
                                <i class="far fa-clock"></i> <?php echo date('h:i A', strtotime($event['start_datetime'])); ?><br>
                                <i class="fas fa-map-marker-alt"></i> <?php echo $event['location']; ?>
                            </small>
                        </div>
                        <div class="mt-2">
                            <a href="../events/edit.php?id=<?php echo $event['event_id']; ?>" 
                               class="btn btn-sm btn-outline-primary">Manage Event</a>
                        </div>
                    </div>
                </div>
                <?php endwhile; ?>
            </div>
        </div>
    </div>
</div>

<style>
.stat-card {
    transition: transform 0.3s ease;
}
.stat-card:hover {
    transform: translateY(-5px);
}
.stat-card .icon {
    opacity: 0.8;
}
.stat-card:hover .icon {
    opacity: 1;
}
.list-group-item {
    transition: background-color 0.3s ease;
}
.list-group-item:hover {
    background-color: rgba(0,0,0,0.02);
}
</style>

<?php
$content = ob_get_clean();
include '../includes/admin_layout.php';
?>
