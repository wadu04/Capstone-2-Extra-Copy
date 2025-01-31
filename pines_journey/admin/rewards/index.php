<?php
require_once '../../includes/config.php';

// Pagination settings
$items_per_page = 6;
$page = isset($_GET['page']) ? max(1, (int)$_GET['page']) : 1;
$offset = ($page - 1) * $items_per_page;

// Get total number of users
$count_query = "SELECT COUNT(*) as count FROM users WHERE role = 'user'";
$total_users = $conn->query($count_query)->fetch_assoc()['count'];
$total_pages = ceil($total_users / $items_per_page);

// Get top users based on QR scans and blog favorites with pagination
$query = "SELECT 
    u.user_id,
    u.username,
    u.email,
    (SELECT COUNT(*) FROM user_scans WHERE user_id = u.user_id) as qr_scans,
    (SELECT COUNT(*) FROM favorites f 
     JOIN blogs b ON f.blog_id = b.blog_id 
     WHERE b.user_id = u.user_id) as blog_favorites,
    (SELECT COUNT(*) FROM rewards WHERE user_id = u.user_id) as rewards_received
FROM users u
WHERE u.role = 'user'
ORDER BY qr_scans DESC, blog_favorites DESC
LIMIT ? OFFSET ?";

$stmt = $conn->prepare($query);
$stmt->bind_param("ii", $items_per_page, $offset);
$stmt->execute();
$result = $stmt->get_result();
$top_users = $result->fetch_all(MYSQLI_ASSOC);

$page_title = "Rewards Management";
ob_start();
?>


    
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/animate.css/4.1.1/animate.min.css">
    <link href="../../assets/css/admin.css" rel="stylesheet">
    <style>
        .reward-badge {
            width: 40px;
            height: 40px;
            object-fit: cover;
            border-radius: 50%;
        }
        .success-icon {
            color: #28a745;
            font-size: 4rem;
            margin-bottom: 1rem;
        }
        #successModal .modal-content {
            border: none;
            box-shadow: 0 0.5rem 1rem rgba(0, 0, 0, 0.15);
        }
        #successModal .modal-header {
            border-bottom: none;
            padding-bottom: 0;
        }
        #successModal .modal-footer {
            border-top: none;
            padding-top: 0;
        }
    </style>


  

    <div class="container-fluid">
        <div class="row">
            
               

                <div class="card">
                    <div class="card-header">
                        <h5 class="card-title mb-0">Top Users</h5>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-hover">
                                <thead>
                                    <tr>
                                        <th>Rank</th>
                                        <th>Username</th>
                                        <th>QR Scans</th>
                                        <th>Blog Favorites</th>
                                        <th>Rewards</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($top_users as $index => $user): ?>
                                    <tr>
                                        <td><?php echo $offset + $index + 1; ?></td>
                                        <td><?php echo htmlspecialchars($user['username']); ?></td>
                                        <td><?php echo $user['qr_scans']; ?></td>
                                        <td><?php echo $user['blog_favorites']; ?></td>
                                        <td><?php echo $user['rewards_received']; ?></td>
                                        <td>
                                            <button class="btn btn-sm btn-info view-details" data-user-id="<?php echo $user['user_id']; ?>">
                                                <i class="fas fa-eye"></i>
                                            </button>
                                            <button class="btn btn-sm btn-success give-reward" data-user-id="<?php echo $user['user_id']; ?>" data-username="<?php echo htmlspecialchars($user['username']); ?>">
                                                <i class="fas fa-gift"></i>
                                            </button>
                                        </td>
                                    </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                    <!-- Pagination -->
                    <div class="card-footer">
                        <nav aria-label="User navigation">
                            <ul class="pagination justify-content-center mb-0">
                                <?php if ($page > 1): ?>
                                <li class="page-item">
                                    <a class="page-link" href="?page=<?php echo ($page - 1); ?>" aria-label="Previous">
                                        <span aria-hidden="true">&laquo;</span>
                                    </a>
                                </li>
                                <?php endif; ?>
                                
                                <?php for ($i = 1; $i <= $total_pages; $i++): ?>
                                <li class="page-item <?php echo $i === $page ? 'active' : ''; ?>">
                                    <a class="page-link" href="?page=<?php echo $i; ?>"><?php echo $i; ?></a>
                                </li>
                                <?php endfor; ?>
                                
                                <?php if ($page < $total_pages): ?>
                                <li class="page-item">
                                    <a class="page-link" href="?page=<?php echo ($page + 1); ?>" aria-label="Next">
                                        <span aria-hidden="true">&raquo;</span>
                                    </a>
                                </li>
                                <?php endif; ?>
                            </ul>
                        </nav>
                    </div>
                </div>
            
        </div>
    </div>

    <!-- User Details Modal -->
    <div class="modal fade" id="userDetailsModal" tabindex="-1">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">User Details</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div id="userDetailsContent"></div>
                </div>
            </div>
        </div>
    </div>

    <!-- Give Reward Modal -->
    <div class="modal fade" id="giveRewardModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Give Reward</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <form id="rewardForm" enctype="multipart/form-data">
                        <input type="hidden" name="user_id" id="reward_user_id">
                        <div class="mb-3">
                            <label for="reward_title" class="form-label">Title</label>
                            <input type="text" class="form-control" id="reward_title" name="title" required>
                        </div>
                        <div class="mb-3">
                            <label for="reward_description" class="form-label">Description</label>
                            <textarea class="form-control" id="reward_description" name="description" rows="3" required></textarea>
                        </div>
                        <div class="mb-3">
                            <label for="reward_image" class="form-label">Image</label>
                            <input type="file" class="form-control" id="reward_image" name="image" accept="image/*" required>
                        </div>
                        <button type="submit" class="btn btn-primary">Give Reward</button>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <div class="modal fade" id="successModal" tabindex="-1">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header bg-success text-white">
                    <h5 class="modal-title">Success!</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body text-center">
                    <i class="fas fa-check-circle text-success fa-4x mb-3"></i>
                    <h4>Reward Given Successfully!</h4>
                    <p class="mb-0">The reward has been sent to <span id="rewarded_user"></span></p>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-success" data-bs-dismiss="modal">Close</button>
                </div>
            </div>
        </div>
    </div>


    <?php
$content = ob_get_clean();
include '../includes/admin_layout.php';
?>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="../../assets/js/rewards.js"></script>

