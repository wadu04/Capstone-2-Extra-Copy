<?php
require_once '../../includes/config.php';

// Handle event deletion
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['delete_event'])) {
    $event_id = (int)$_POST['event_id'];
    $conn->query("DELETE FROM events WHERE event_id = $event_id");
    header("Location: index.php");
    exit();
}

// Get events with filter
$filter = isset($_GET['filter']) ? $_GET['filter'] : 'upcoming';
$sql = "SELECT * FROM events";
if ($filter === 'upcoming') {
    $sql .= " WHERE start_datetime >= NOW()";
} elseif ($filter === 'past') {
    $sql .= " WHERE end_datetime < NOW()";
}
$sql .= " ORDER BY start_datetime DESC";
$result = $conn->query($sql);

$page_title = "Events Management";
ob_start();
?>

<div class="card">
    <div class="card-header">
        <div class="d-flex justify-content-between align-items-center">
            <div>
                <h5 class="mb-0">Events</h5>
                <div class="btn-group mt-2">
                    <a href="?filter=all" class="btn btn-sm btn-outline-primary <?php echo $filter === 'all' ? 'active' : ''; ?>">All Events</a>
                    <a href="?filter=upcoming" class="btn btn-sm btn-outline-primary <?php echo $filter === 'upcoming' ? 'active' : ''; ?>">Upcoming</a>
                    <a href="?filter=past" class="btn btn-sm btn-outline-primary <?php echo $filter === 'past' ? 'active' : ''; ?>">Past</a>
                </div>
            </div>
            <a href="add.php" class="btn btn-primary">
                <i class="fas fa-plus"></i> Add New Event
            </a>
        </div>
    </div>
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-hover">
                <thead>
                    <tr>
                        <th>Title</th>
                        <th>Start Date & Time</th>
                        <th>End Date & Time</th>
                        <th>Location</th>
                        <th>Organizer</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php while ($event = $result->fetch_assoc()): ?>
                    <tr>
                        <td><?php echo $event['title']; ?></td>
                        <td><?php echo date('M d, Y h:i A', strtotime($event['start_datetime'])); ?></td>
                        <td><?php echo date('M d, Y h:i A', strtotime($event['end_datetime'])); ?></td>
                        <td><?php echo $event['location']; ?></td>
                        <td><?php echo $event['organizer_name']; ?></td>
                        <td class="table-actions">
                            <a href="edit.php?id=<?php echo $event['event_id']; ?>" class="btn btn-sm btn-primary">
                                <i class="fas fa-edit"></i>
                            </a>
                            <form method="POST" class="d-inline" onsubmit="return confirm('Are you sure you want to delete this event?');">
                                <input type="hidden" name="event_id" value="<?php echo $event['event_id']; ?>">
                                <button type="submit" name="delete_event" class="btn btn-sm btn-danger">
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
