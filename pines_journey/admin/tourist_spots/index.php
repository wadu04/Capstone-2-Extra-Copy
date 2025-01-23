<?php
require_once '../../includes/config.php';

// Handle spot deletion
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['delete_spot'])) {
    $spot_id = (int)$_POST['spot_id'];
    $conn->query("DELETE FROM tourist_spots WHERE spot_id = $spot_id");
    header("Location: index.php");
    exit();
}

// Pagination settings
$items_per_page = 5;
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$offset = ($page - 1) * $items_per_page;

// Get total number of spots
$count_result = $conn->query("SELECT COUNT(*) as total FROM tourist_spots");
$total_spots = $count_result->fetch_assoc()['total'];
$total_pages = ceil($total_spots / $items_per_page);

// Get tourist spots for current page
$sql = "SELECT * FROM tourist_spots ORDER BY name LIMIT $offset, $items_per_page";
$result = $conn->query($sql);

$page_title = "Tourist Spots Management";
ob_start();
?>

<div class="card">
    <div class="card-header">
        <div class="d-flex justify-content-between align-items-center">
            <h5 class="mb-0">Tourist Spots</h5>
            <a href="add.php" class="btn btn-primary">
                <i class="fas fa-plus"></i> Add New Spot
            </a>
        </div>
    </div>
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-hover">
                <thead>
                    <tr>
                        <th>Name</th>
                        <th>Location</th>
                        <th>Opening Hours</th>
                        <th>Entrance Fee</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php while ($spot = $result->fetch_assoc()): ?>
                    <tr>
                        <td><?php echo $spot['name']; ?></td>
                        <td><?php echo $spot['location']; ?></td>
                        <td><?php echo $spot['opening_hours']; ?></td>
                        <td>₱<?php echo number_format($spot['entrance_fee'], 2); ?></td>
                        <td class="table-actions">
                            <a href="edit.php?id=<?php echo $spot['spot_id']; ?>" class="btn btn-sm btn-primary">
                                <i class="fas fa-edit"></i>
                            </a>
                            <form method="POST" class="d-inline" onsubmit="return confirm('Are you sure you want to delete this tourist spot?');">
                                <input type="hidden" name="spot_id" value="<?php echo $spot['spot_id']; ?>">
                                <button type="submit" name="delete_spot" class="btn btn-sm btn-danger">
                                    <i class="fas fa-trash"></i>
                                </button>
                            </form>
                        </td>
                    </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
        </div>

        <?php if ($total_pages > 1): ?>
        <nav aria-label="Page navigation" class="mt-4">
            <ul class="pagination justify-content-center">
                <?php if ($page > 1): ?>
                    <li class="page-item">
                        <a class="page-link" href="?page=<?php echo ($page - 1); ?>">Previous</a>
                    </li>
                <?php endif; ?>

                <?php for ($i = 1; $i <= $total_pages; $i++): ?>
                    <li class="page-item <?php echo $page == $i ? 'active' : ''; ?>">
                        <a class="page-link" href="?page=<?php echo $i; ?>"><?php echo $i; ?></a>
                    </li>
                <?php endfor; ?>

                <?php if ($page < $total_pages): ?>
                    <li class="page-item">
                        <a class="page-link" href="?page=<?php echo ($page + 1); ?>">Next</a>
                    </li>
                <?php endif; ?>
            </ul>
        </nav>
        <?php endif; ?>
    </div>
</div>

<?php
$content = ob_get_clean();
include '../includes/admin_layout.php';
?>
