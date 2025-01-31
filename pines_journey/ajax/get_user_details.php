<?php
require_once '../includes/config.php';

if (!isset($_POST['user_id'])) {
    die('User ID not provided');
}

$user_id = intval($_POST['user_id']);

// Get user's QR scans
$qr_query = "SELECT us.*, qc.content as qr_content, qc.points as points_earned
            FROM user_scans us
            JOIN qr_codes qc ON us.qr_content = qc.content
            WHERE us.user_id = ?
            ORDER BY us.scanned_at DESC";

$stmt = $conn->prepare($qr_query);
$stmt->bind_param('i', $user_id);
$stmt->execute();
$qr_scans = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);

// Get user's blog posts with favorites and comments
$blog_query = "SELECT b.*, 
              (SELECT COUNT(*) FROM favorites WHERE blog_id = b.blog_id) as favorite_count,
              (SELECT COUNT(*) FROM comments WHERE blog_id = b.blog_id) as comment_count
              FROM blogs b
              WHERE b.user_id = ?
              ORDER BY favorite_count DESC";

$stmt = $conn->prepare($blog_query);
$stmt->bind_param('i', $user_id);
$stmt->execute();
$blog_posts = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);

// Get user's rewards
$rewards_query = "SELECT r.*, u.username as admin_username 
                 FROM rewards r 
                 JOIN users u ON r.admin_id = u.user_id 
                 WHERE r.user_id = ? 
                 ORDER BY r.created_at DESC";
$stmt = $conn->prepare($rewards_query);
$stmt->bind_param('i', $user_id);
$stmt->execute();
$rewards = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);

?>

<div class="accordion" id="userDetailsAccordion">
    <!-- QR Scans Section -->
    <div class="accordion-item">
        <h2 class="accordion-header">
            <button class="accordion-button" type="button" data-bs-toggle="collapse" data-bs-target="#qrScansCollapse">
                QR Scans (<?php echo count($qr_scans); ?>)
            </button>
        </h2>
        <div id="qrScansCollapse" class="accordion-collapse collapse show">
            <div class="accordion-body">
                <div class="table-responsive">
                    <table class="table table-sm">
                        <thead>
                            <tr>
                                <th>QR Content</th>
                                <th>Points Earned</th>
                                <th>Scan Date</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($qr_scans as $scan): ?>
                            <tr>
                                <td><?php echo htmlspecialchars($scan['qr_content']); ?></td>
                                <td><?php echo $scan['points_earned']; ?></td>
                                <td><?php echo $scan['scanned_at']; ?></td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <!-- Blog Posts Section -->
    <div class="accordion-item">
        <h2 class="accordion-header">
            <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#blogPostsCollapse">
                Blog Posts (<?php echo count($blog_posts); ?>)
            </button>
        </h2>
        <div id="blogPostsCollapse" class="accordion-collapse collapse">
            <div class="accordion-body">
                <div class="table-responsive">
                    <table class="table table-sm">
                        <thead>
                            <tr>
                                <th>Title</th>
                                <th>Favorites</th>
                                <th>Comments</th>
                                <th>Date</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($blog_posts as $post): ?>
                            <tr>
                                <td><?php echo htmlspecialchars($post['title']); ?></td>
                                <td><?php echo $post['favorite_count']; ?></td>
                                <td><?php echo $post['comment_count']; ?></td>
                                <td><?php echo $post['created_at']; ?></td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <!-- Rewards Section -->
    <div class="accordion-item">
        <h2 class="accordion-header">
            <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#rewardsCollapse">
                Rewards (<?php echo count($rewards); ?>)
            </button>
        </h2>
        <div id="rewardsCollapse" class="accordion-collapse collapse">
            <div class="accordion-body">
                <div class="table-responsive">
                    <table class="table table-sm">
                        <thead>
                            <tr>
                                <th>Image</th>
                                <th>Title</th>
                                <th>Description</th>
                                <th>Given By</th>
                                <th>Date</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($rewards as $reward): ?>
                            <tr>
                                <td>
                                    <img src="../../<?php echo htmlspecialchars($reward['image']); ?>" 
                                         class="reward-badge" alt="Reward Badge">
                                </td>
                                <td><?php echo htmlspecialchars($reward['title']); ?></td>
                                <td><?php echo htmlspecialchars($reward['description']); ?></td>
                                <td><?php echo htmlspecialchars($reward['admin_username']); ?></td>
                                <td><?php echo $reward['created_at']; ?></td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>
