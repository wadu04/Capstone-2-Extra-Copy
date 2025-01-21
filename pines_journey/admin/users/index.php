<?php
require_once '../../includes/config.php';

// Handle user deletion
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['delete_user'])) {
    $user_id = (int)$_POST['user_id'];
    
    // Don't allow deleting self
    if ($user_id != $_SESSION['user_id']) {
        $conn->query("DELETE FROM comments WHERE user_id = $user_id");
        $conn->query("DELETE FROM favorites WHERE user_id = $user_id");
        $conn->query("DELETE FROM blogs WHERE user_id = $user_id");
        $conn->query("DELETE FROM users WHERE user_id = $user_id");
    }
    
    header("Location: index.php");
    exit();
}

// Get users with role filter
$role_filter = isset($_GET['role']) ? $_GET['role'] : '';
$sql = "SELECT * FROM users";
if ($role_filter) {
    $sql .= " WHERE role = '$role_filter'";
}
$sql .= " ORDER BY username";
$result = $conn->query($sql);

$page_title = "User Management";
ob_start();
?>

<div class="card">
    <div class="card-header">
        <div class="d-flex justify-content-between align-items-center">
            <h5 class="mb-0">Users</h5>
            <div>
                <a href="?role=" class="btn btn-sm btn-outline-primary <?php echo !$role_filter ? 'active' : ''; ?>">All</a>
                <a href="?role=user" class="btn btn-sm btn-outline-primary <?php echo $role_filter === 'user' ? 'active' : ''; ?>">Users</a>
                <a href="?role=admin" class="btn btn-sm btn-outline-primary <?php echo $role_filter === 'admin' ? 'active' : ''; ?>">Admins</a>
            </div>
        </div>
    </div>
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-hover">
                <thead>
                    <tr>
                        <th>Username</th>
                        <th>Email</th>
                        <th>Role</th>
                        <th>Joined Date</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php while ($user = $result->fetch_assoc()): ?>
                    <tr>
                        <td><?php echo $user['username']; ?></td>
                        <td><?php echo $user['email']; ?></td>
                        <td>
                            <span class="badge bg-<?php echo $user['role'] === 'admin' ? 'danger' : 'primary'; ?>">
                                <?php echo ucfirst($user['role']); ?>
                            </span>
                        </td>
                        <td><?php echo date('M d, Y', strtotime($user['created_at'])); ?></td>
                        <td class="table-actions">
                            <a href="edit.php?id=<?php echo $user['user_id']; ?>" class="btn btn-sm btn-primary">
                                <i class="fas fa-edit"></i>
                            </a>
                            <?php if ($user['user_id'] != $_SESSION['user_id']): ?>
                            <form method="POST" class="d-inline" onsubmit="return confirm('Are you sure you want to delete this user?');">
                                <input type="hidden" name="user_id" value="<?php echo $user['user_id']; ?>">
                                <button type="submit" name="delete_user" class="btn btn-sm btn-danger">
                                    <i class="fas fa-trash"></i>
                                </button>
                            </form>
                            <?php endif; ?>
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
