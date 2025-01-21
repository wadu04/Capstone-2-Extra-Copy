<?php
require_once '../includes/config.php';

// Get upcoming events
$sql = "SELECT * FROM events WHERE start_datetime >= NOW() ORDER BY start_datetime ASC";
$result = $conn->query($sql);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Events - Pine's Journey</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="../assets/css/style.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
</head>
<body>
    <?php include '../includes/header.php'; ?>

    <div class="container py-4">
        <h2 class="mb-4">Upcoming Events in Baguio City</h2>
        
        <div class="row g-4">
            <?php while ($event = $result->fetch_assoc()): ?>
            <div class="col-md-6">
                <div class="card h-100 shadow-sm">
                    <?php if ($event['image_url']): ?>
                    <img src="<?php echo $event['image_url']; ?>" class="card-img-top" alt="<?php echo $event['title']; ?>" style="height: 250px; object-fit: cover;">
                    <?php endif; ?>
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-start mb-2">
                            <h5 class="card-title mb-0"><?php echo $event['title']; ?></h5>
                        </div>
                        <div class="mb-3">
                            <span class="badge bg-primary mb-2">
                                <i class="far fa-calendar"></i> 
                                <?php echo date('M d, Y', strtotime($event['start_datetime'])); ?>
                            </span>
                            <div class="text-muted small">
                                <i class="far fa-clock"></i> Starts: <?php echo date('h:i A', strtotime($event['start_datetime'])); ?><br>
                                <i class="far fa-clock"></i> Ends: <?php echo date('M d, Y h:i A', strtotime($event['end_datetime'])); ?>
                            </div>
                        </div>
                        <p class="card-text text-muted">
                            <i class="fas fa-map-marker-alt"></i> <?php echo $event['location']; ?>
                        </p>
                        <p class="card-text"><?php echo substr($event['description'], 0, 150); ?>...</p>
                        <a href="event-details.php?id=<?php echo $event['event_id']; ?>" class="btn btn-primary">
                            View Details
                        </a>
                    </div>
                </div>
            </div>
            <?php endwhile; ?>
        </div>
    </div>

    <?php include '../includes/footer.php'; ?>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
