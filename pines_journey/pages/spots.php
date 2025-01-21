<?php
require_once '../includes/config.php';

// Get tourist spots with ratings
$sql = "SELECT ts.*, 
        COALESCE(AVG(r.rating), 0) as avg_rating,
        COUNT(r.review_id) as review_count
        FROM tourist_spots ts
        LEFT JOIN reviews r ON ts.spot_id = r.spot_id
        GROUP BY ts.spot_id
        ORDER BY ts.name ASC";
$result = $conn->query($sql);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Tourist Spots - Pines' Journey</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="../assets/css/style.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
</head>
<body>
    <?php include '../includes/header.php'; ?>

    <div class="container py-4">
        <h2 class="mb-4">Tourist Spots in Baguio City</h2>
        
        <div class="row g-4">
            <?php while ($spot = $result->fetch_assoc()): ?>
            <div class="col-md-4">
                <div class="card h-100 shadow-sm">
                    <?php if ($spot['image_url']): ?>
                    <img src="<?php echo $spot['image_url']; ?>" class="card-img-top" alt="<?php echo $spot['name']; ?>" style="height: 200px; object-fit: cover;">
                    <?php endif; ?>
                    <div class="card-body">
                        <h5 class="card-title"><?php echo $spot['name']; ?></h5>
                        <p class="card-text text-muted">
                            <i class="fas fa-map-marker-alt"></i> <?php echo $spot['location']; ?>
                        </p>
                        <div class="mb-3">
                            <small class="text-muted">
                                <i class="far fa-clock"></i> <?php echo $spot['opening_hours']; ?><br>
                                <i class="fas fa-ticket-alt"></i> Entrance Fee: ₱<?php echo number_format($spot['entrance_fee'], 2); ?>
                            </small>
                        </div>
                        <div class="mb-3">
                            <div class="text-warning">
                                <?php
                              // Replace the existing star rating code with:
$rating = $spot['avg_rating'];  // Don't round it
for ($i = 1; $i <= 5; $i++) {
    if ($i <= floor($rating)) {
        echo '<i class="fas fa-star"></i>';
    } elseif ($i - $rating <= 0.5 && $i - $rating > 0) {
        echo '<i class="fas fa-star-half-alt"></i>';
    } else {
        echo '<i class="far fa-star"></i>';
    }
}
                                ?>
                                <span class="text-muted ms-2">
                                    (<?php echo number_format($spot['avg_rating'], 1); ?>) 
                                    <?php echo $spot['review_count']; ?> review<?php echo $spot['review_count'] != 1 ? 's' : ''; ?>
                                </span>
                            </div>
                        </div>
                        <a href="spot-details.php?id=<?php echo $spot['spot_id']; ?>" class="btn btn-primary">
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
