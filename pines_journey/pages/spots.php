<?php
require_once '../includes/config.php';

// Get tourist spots with ratings
$search = isset($_GET['search']) ? $_GET['search'] : '';
$sql = "SELECT ts.*, 
        COALESCE(AVG(r.rating), 0) as avg_rating,
        COUNT(r.review_id) as review_count
        FROM tourist_spots ts
        LEFT JOIN reviews r ON ts.spot_id = r.spot_id";

if (!empty($search)) {
    $search = $conn->real_escape_string($search);
    $sql .= " WHERE ts.name LIKE '%$search%' OR ts.location LIKE '%$search%' OR ts.description LIKE '%$search%' OR ts.tips LIKE '%$search%'";
}

$sql .= " GROUP BY ts.spot_id ORDER BY ts.name ASC";
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
    <style>
        .search-container {
            position: relative;
            margin-right: 20px;
            width: 250px;
        }
        .search-input {
            width: 100%;
            padding: 8px 35px 8px 15px;
            border: 1px solid #ddd;
            border-radius: 20px;
            transition: all 0.3s ease;
        }
        .search-icon {
            position: absolute;
            right: 12px;
            top: 50%;
            transform: translateY(-50%);
            cursor: pointer;
            font-size: 1.2rem;
            color: #555;
        }
        .status-open {
            background-color: lightblue;
            padding: 3px 8px;
            border-radius: 4px;
            font-size: 0.9em;
        }
        .status-closed {
            background-color: #ff6b6b;
            color: white;
            padding: 3px 8px;
            border-radius: 4px;
            font-size: 0.9em;
        }
    </style>
</head>
<body>
    <?php include '../includes/header.php'; ?>

    <div class="container py-4">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h2>Tourist Spots in Baguio City</h2>
            <div class="search-container">
                <input type="text" class="search-input" id="searchInput" placeholder="Search spots...">
                <i class="fas fa-search search-icon" id="searchIcon"></i>
            </div>
        </div>
        
        <div class="row g-4">
            <?php while ($spot = $result->fetch_assoc()): ?>
            <div class="col-md-4">
                <div class="card h-100 shadow-sm">
                    <?php if ($spot['image_url']): ?>
                    <a href="spot-details.php?id=<?php echo $spot['spot_id']; ?>">
                        <img src="<?php echo $spot['image_url']; ?>" class="card-img-top" alt="<?php echo $spot['name']; ?>" style="height: 200px; object-fit: cover;">
                    </a>
                    <?php endif; ?>
                    <div class="card-body">
                        <h5 class="card-title"><?php echo $spot['name']; ?></h5>
                        <p class="card-text text-muted">
                            <i class="fas fa-map-marker-alt"></i> <?php echo $spot['location']; ?>
                        </p>
                        <div class="mb-2">
                            <span class="status-<?php echo $spot['status']; ?>">
                                <?php echo ucfirst($spot['status']); ?>
                            </span>
                        </div>
                        <div class="mb-3">
                            <small class="text-muted">
                                <i class="far fa-clock"></i> <?php echo $spot['opening_hours']; ?><br>
                                <i class="fas fa-ticket-alt"></i> Entrance Fee: ₱<?php echo number_format($spot['entrance_fee'], 2); ?>
                            </small>
                        </div>
                        <div class="mb-3">
                            <div class="text-warning">
                                <?php
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
                        <div class="d-flex justify-content-between align-items-center">
                            <a href="spot-details.php?id=<?php echo $spot['spot_id']; ?>" class="btn btn-primary">View Details</a>
                           
                        </div>
                    </div>
                </div>
            </div>
            <?php endwhile; ?>
        </div>
    </div>

    <?php include '../includes/footer.php'; ?>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const searchIcon = document.getElementById('searchIcon');
            const searchInput = document.getElementById('searchInput');

            // Handle search when user types
            searchInput.addEventListener('keypress', function(e) {
                if (e.key === 'Enter') {
                    const searchTerm = searchInput.value.trim();
                    if (searchTerm) {
                        window.location.href = `spots.php?search=${encodeURIComponent(searchTerm)}`;
                    } else {
                        // If search is empty, go back to main spots page
                        window.location.href = 'spots.php';
                    }
                }
            });

            // Clear search and return to main page when input is cleared
            searchInput.addEventListener('input', function() {
                if (this.value.trim() === '' && new URLSearchParams(window.location.search).has('search')) {
                    window.location.href = 'spots.php';
                }
            });

            // If there's a search parameter in URL, populate the search input
            const urlParams = new URLSearchParams(window.location.search);
            if (urlParams.has('search')) {
                searchInput.value = urlParams.get('search');
            }
        });
    </script>
</body>
</html>
