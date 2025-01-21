<?php
require_once '../../includes/config.php';

$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $title = sanitize($_POST['title']);
    $description = sanitize($_POST['description']);
    $schedule = sanitize($_POST['schedule']);
    $event_details = sanitize($_POST['event_details']);
    $start_date = $_POST['start_date'];
    $start_time = $_POST['start_time'];
    $end_date = $_POST['end_date'];
    $end_time = $_POST['end_time'];
    $start_datetime = date('Y-m-d H:i:s', strtotime("$start_date $start_time"));
    $end_datetime = date('Y-m-d H:i:s', strtotime("$end_date $end_time"));
    $location = sanitize($_POST['location']);
    $ticket_info = sanitize($_POST['ticket_info']);
    $organizer_name = sanitize($_POST['organizer_name']);
    $organizer_contact = sanitize($_POST['organizer_contact']);
    
    // Handle file upload
    $image_url = '';
    if(isset($_FILES['image']) && $_FILES['image']['error'] == 0) {
        $target_dir = "../../uploads/events/";
        if (!file_exists($target_dir)) {
            mkdir($target_dir, 0777, true);
        }
        $file_extension = strtolower(pathinfo($_FILES["image"]["name"], PATHINFO_EXTENSION));
        $new_filename = uniqid() . '.' . $file_extension;
        $target_file = $target_dir . $new_filename;
        
        if(move_uploaded_file($_FILES["image"]["tmp_name"], $target_file)) {
            $image_url = "../uploads/events/" . $new_filename;
        } else {
            $error = "Failed to upload image";
        }
    }

    if (!$error) {
        $stmt = $conn->prepare("INSERT INTO events (title, description, schedule, event_details, start_datetime, end_datetime, location, ticket_info, organizer_name, organizer_contact, image_url) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
        $stmt->bind_param("sssssssssss", $title, $description, $schedule, $event_details, $start_datetime, $end_datetime, $location, $ticket_info, $organizer_name, $organizer_contact, $image_url);
        
        if ($stmt->execute()) {
            $success = "Event added successfully";
        } else {
            $error = "Failed to add event";
        }
    }
}

$page_title = "Add Event";
ob_start();
?>

<div class="card">
    <div class="card-header">
        <div class="d-flex justify-content-between align-items-center">
            <h5 class="mb-0">Add Event</h5>
            <a href="index.php" class="btn btn-secondary">Back to Events</a>
        </div>
    </div>
    <div class="card-body">
        <?php if ($error): ?>
            <div class="alert alert-danger"><?php echo $error; ?></div>
        <?php endif; ?>
        <?php if ($success): ?>
            <div class="alert alert-success"><?php echo $success; ?></div>
        <?php endif; ?>

        <form method="POST" class="admin-form" enctype="multipart/form-data">
            <div class="row">
                <div class="col-md-8">
                    <div class="mb-3">
                        <label for="title" class="form-label">Event Title</label>
                        <input type="text" class="form-control" id="title" name="title" required>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="mb-3">
                        <label for="image" class="form-label">Event Image</label>
                        <input type="file" class="form-control" id="image" name="image" accept="image/*">
                    </div>
                </div>
            </div>

            <div class="mb-3">
                <label for="description" class="form-label">About This Event</label>
                <textarea class="form-control" id="description" name="description" rows="3" required></textarea>
            </div>

            <div class="row">
                <div class="col-md-3">
                    <div class="mb-3">
                        <label for="start_date" class="form-label">Start Date</label>
                        <input type="date" class="form-control" id="start_date" name="start_date" required>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="mb-3">
                        <label for="start_time" class="form-label">Start Time</label>
                        <input type="time" class="form-control" id="start_time" name="start_time" required>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="mb-3">
                        <label for="end_date" class="form-label">End Date</label>
                        <input type="date" class="form-control" id="end_date" name="end_date" required>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="mb-3">
                        <label for="end_time" class="form-label">End Time</label>
                        <input type="time" class="form-control" id="end_time" name="end_time" required>
                    </div>
                </div>
            </div>

            <div class="mb-3">
                <label for="schedule" class="form-label">Schedule Details</label>
                <textarea class="form-control" id="schedule" name="schedule" rows="3" required></textarea>
            </div>

            <div class="mb-3">
                <label for="event_details" class="form-label">Event Details</label>
                <textarea class="form-control" id="event_details" name="event_details" rows="3" required></textarea>
            </div>

            <div class="mb-3">
                <label for="location" class="form-label">Location</label>
                <input type="text" class="form-control" id="location" name="location" required>
            </div>

            <div class="mb-3">
                <label for="ticket_info" class="form-label">Ticket Information</label>
                <textarea class="form-control" id="ticket_info" name="ticket_info" rows="2" required></textarea>
            </div>

            <div class="row">
                <div class="col-md-6">
                    <div class="mb-3">
                        <label for="organizer_name" class="form-label">Organizer Name</label>
                        <input type="text" class="form-control" id="organizer_name" name="organizer_name" required>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="mb-3">
                        <label for="organizer_contact" class="form-label">Organizer Contact</label>
                        <input type="text" class="form-control" id="organizer_contact" name="organizer_contact" required>
                    </div>
                </div>
            </div>

            <button type="submit" class="btn btn-primary">Add Event</button>
        </form>
    </div>
</div>

<?php
$content = ob_get_clean();
include '../includes/admin_layout.php';
?>
