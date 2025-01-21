<?php
require_once '../../includes/config.php';

// Handle spot deletion
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['delete_spot'])) {
    $spot_id = (int)$_POST['spot_id'];
    $conn->query("DELETE FROM tourist_spots WHERE spot_id = $spot_id");
    header("Location: index.php");
    exit();
}

// Get all tourist spots
$sql = "SELECT * FROM tourist_spots ORDER BY name";
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
    </div>
</div>

<?php
$content = ob_get_clean();
include '../includes/admin_layout.php';
?>
