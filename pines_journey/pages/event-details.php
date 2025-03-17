<?php
require_once '../includes/config.php';

// Get event ID from URL
$event_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

// Get event details
$stmt = $conn->prepare("SELECT * FROM events WHERE event_id = ?");
$stmt->bind_param("i", $event_id);
$stmt->execute();
$event = $stmt->get_result()->fetch_assoc();

// Redirect if event not found
if (!$event) {
    header("Location: events.php");
    exit();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $event['title']; ?> - Pine's Journey</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="../assets/css/style.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
</head>
<body>
    <?php include '../includes/header.php'; ?>

    <div class="container py-4">
        <!-- <nav aria-label="breadcrumb" class="mb-4">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="events.php">Events</a></li>
                <li class="breadcrumb-item active" aria-current="page"><?php echo $event['title']; ?></li>
            </ol>
        </nav> -->

        <div class="row">
            <div class="col-lg-8">
                <h1 class="mb-4"><?php echo $event['title']; ?></h1>
                
                <?php if ($event['image_url']): ?>
                <img src="<?php echo $event['image_url']; ?>" class="img-fluid rounded mb-4" alt="<?php echo $event['title']; ?>">
                <?php endif; ?>

                <div class="mb-4">
                    <h5 class="text-primary">About This Event</h5>
                    <p><?php echo nl2br($event['description']); ?></p>
                </div>

                <div class="mb-4">
                    <h5 class="text-primary">Schedule</h5>
                    <p><?php echo nl2br($event['schedule']); ?></p>
                </div>

                <div class="mb-4">
                    <h5 class="text-primary">Event Details</h5>
                    <p><?php echo nl2br($event['event_details']); ?></p>
                </div>
            </div>

            <div class="col-lg-4">
                <div class="card mb-4">
                    <div class="card-body">
                        <h5 class="text-primary">Event Schedule</h5>
                        <div class="mb-3">
                            <strong>Starts:</strong><br>
                            <p>
                                <i class="far fa-calendar"></i> <?php echo date('F d, Y', strtotime($event['start_datetime'])); ?><br>
                                <i class="far fa-clock"></i> <?php echo date('h:i A', strtotime($event['start_datetime'])); ?>
                            </p>
                        </div>
                        <div class="mb-3">
                            <strong>Ends:</strong><br>
                            <p>
                                <i class="far fa-calendar"></i> <?php echo date('F d, Y', strtotime($event['end_datetime'])); ?><br>
                                <i class="far fa-clock"></i> <?php echo date('h:i A', strtotime($event['end_datetime'])); ?>
                            </p>
                        </div>
                    </div>
                </div>

                <div class="card mb-4">
                    <div class="card-body">
                        <h5 class="text-primary">Location</h5>
                        <p class="d-flex align-items-center justify-content-between">
                            <span class="d-flex align-items-center" style="flex: 1;">
                                <i class="fas fa-map-marker-alt"></i>
                                <span class="location-text" style="cursor: pointer; margin-left: 10px;" onclick="redirectToMap('<?php echo htmlspecialchars($event['location'], ENT_QUOTES); ?>')"><?php echo $event['location']; ?></span>
                            </span>
                            <button class="btn btn-sm btn-outline-primary ms-2" onclick="redirectToMap('<?php echo htmlspecialchars($event['location'], ENT_QUOTES); ?>')">
                                <i class="fas fa-chevron-right"></i>
                            </button>
                        </p>
                    </div>
                </div>

                <div class="card mb-4">
                    <div class="card-body">
                        <h5 class="text-primary">Ticket Information</h5>
                        <p><?php echo nl2br($event['ticket_info']); ?></p>
                    </div>
                </div>

                <div class="card">
                    <div class="card-body">
                        <h5 class="text-primary">Organizer Information</h5>
                        <p>
                            <strong>Name:</strong> <?php echo $event['organizer_name']; ?><br>
                            <strong>Contact:</strong> <?php echo $event['organizer_contact']; ?>
                        </p>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <?php include '../includes/footer.php'; ?>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        function redirectToMap(location) {
            window.location.href = 'map.php?search=' + encodeURIComponent(location);
        }
    </script>
</body>
</html>
