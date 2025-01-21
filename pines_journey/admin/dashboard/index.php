<?php
require_once '../../includes/config.php';

// Get statistics
$stats = [
    'users' => $conn->query("SELECT COUNT(*) as count FROM users")->fetch_assoc()['count'],
    'spots' => $conn->query("SELECT COUNT(*) as count FROM tourist_spots")->fetch_assoc()['count'],
    'events' => $conn->query("SELECT COUNT(*) as count FROM events")->fetch_assoc()['count'],
    'blogs' => $conn->query("SELECT COUNT(*) as count FROM blogs")->fetch_assoc()['count']
];

// Get recent blogs
$recent_blogs = $conn->query("
    SELECT b.*, u.username 
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

$page_title = "Dashboard";
ob_start();
?>

<div class="row g-4">
    <!-- Statistics Cards -->
    <div class="col-md-3">
        <div class="card stat-card bg-primary text-white">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h6 class="card-title mb-0">Total Users</h6>
                        <h2 class="mb-0"><?php echo $stats['users']; ?></h2>
                    </div>
                    <div class="icon">
                        <i class="fas fa-users"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <div class="col-md-3">
        <div class="card stat-card bg-success text-white">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h6 class="card-title mb-0">Tourist Spots</h6>
                        <h2 class="mb-0"><?php echo $stats['spots']; ?></h2>
                    </div>
                    <div class="icon">
                        <i class="fas fa-map-marker-alt"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <div class="col-md-3">
        <div class="card stat-card bg-warning text-white">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h6 class="card-title mb-0">Events</h6>
                        <h2 class="mb-0"><?php echo $stats['events']; ?></h2>
                    </div>
                    <div class="icon">
                        <i class="fas fa-calendar-alt"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <div class="col-md-3">
        <div class="card stat-card bg-info text-white">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h6 class="card-title mb-0">Blog Posts</h6>
                        <h2 class="mb-0"><?php echo $stats['blogs']; ?></h2>
                    </div>
                    <div class="icon">
                        <i class="fas fa-blog"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Recent Blog Posts -->
    <div class="col-md-6">
        <div class="card">
            <div class="card-header">
                <h5 class="card-title mb-0">Recent Blog Posts</h5>
            </div>
            <div class="card-body">
                <div class="list-group list-group-flush">
                    <?php while ($blog = $recent_blogs->fetch_assoc()): ?>
                    <div class="list-group-item">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <h6 class="mb-1"><?php echo $blog['title']; ?></h6>
                                <small class="text-muted">
                                    by <?php echo $blog['username']; ?> | 
                                    <?php echo date('M d, Y', strtotime($blog['created_at'])); ?>
                                </small>
                            </div>
                            <a href="../blogs/view.php?id=<?php echo $blog['blog_id']; ?>" class="btn btn-sm btn-primary">
                                View
                            </a>
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
            <div class="card-header">
                <h5 class="card-title mb-0">Upcoming Events</h5>
            </div>
            <div class="card-body">
                <div class="list-group list-group-flush">
                    <?php while ($event = $upcoming_events->fetch_assoc()): ?>
                    <div class="list-group-item">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <h6 class="mb-1"><?php echo $event['title']; ?></h6>
                                <small class="text-muted">
                                    <i class="far fa-calendar"></i> 
                                    <?php echo date('M d, Y', strtotime($event['start_datetime'])); ?> |
                                    <i class="far fa-clock"></i>
                                    <?php echo date('h:i A', strtotime($event['start_datetime'])); ?>
                                </small>
                            </div>
                            <a href="../events/edit.php?id=<?php echo $event['event_id']; ?>" class="btn btn-sm btn-primary">
                                Edit
                            </a>
                        </div>
                    </div>
                    <?php endwhile; ?>
                </div>
            </div>
        </div>
    </div>
</div>

<?php
$content = ob_get_clean();
include '../includes/admin_layout.php';
?>
