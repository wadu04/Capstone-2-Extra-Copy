<?php
require_once '../includes/config.php';

if (!isset($_GET['id'])) {
    header('Location: spots.php');
    exit();
}

$spot_id = $_GET['id'];

// Get spot details with average rating
$sql = "SELECT ts.*, 
        COALESCE(AVG(r.rating), 0) as avg_rating,
        COUNT(r.review_id) as review_count,
        SUM(CASE WHEN r.rating = 5 THEN 1 ELSE 0 END) as five_star,
        SUM(CASE WHEN r.rating = 4 THEN 1 ELSE 0 END) as four_star,
        SUM(CASE WHEN r.rating = 3 THEN 1 ELSE 0 END) as three_star,
        SUM(CASE WHEN r.rating = 2 THEN 1 ELSE 0 END) as two_star,
        SUM(CASE WHEN r.rating = 1 THEN 1 ELSE 0 END) as one_star
        FROM tourist_spots ts
        LEFT JOIN reviews r ON ts.spot_id = r.spot_id
        WHERE ts.spot_id = ?
        GROUP BY ts.spot_id";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $spot_id);
$stmt->execute();
$result = $stmt->get_result();
$spot = $result->fetch_assoc();

// Get all reviews for this spot
$sql = "SELECT r.*, u.username, u.profile_picture, r.image_url 
        FROM reviews r 
        JOIN users u ON r.user_id = u.user_id 
        WHERE r.spot_id = ? 
        ORDER BY r.created_at DESC";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $spot_id);
$stmt->execute();
$reviews = $stmt->get_result();

if (!$spot) {
    header('Location: spots.php');
    exit();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $spot['name']; ?> - Pines' Journey</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="../assets/css/style.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        .star-rating {
            color: #ffc107;
            font-size: 24px;
            cursor: pointer;
        }
        .review-star {
            color: #ffc107;
        }
        .carousel-inner {
            height: 400px;
        }
        .carousel-inner img {
            width: 100%;
            height: 400px;
            object-fit: cover;
        }
        .carousel-control-prev,
        .carousel-control-next {
            width: 5%;
            background: rgba(0, 0, 0, 0.3);
        }
        .carousel-control-prev:hover,
        .carousel-control-next:hover {
            background: rgba(0, 0, 0, 0.5);
        }
        .carousel-indicators {
            bottom: 0;
            margin-bottom: 0.5rem;
        }
        .carousel-indicators button {
            width: 10px;
            height: 10px;
            border-radius: 50%;
            margin: 0 4px;
        }
    </style>
</head>
<body>
    <?php include '../includes/header.php'; ?>

    <div class="container py-4">
        <nav aria-label="breadcrumb" class="mb-4">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="spots.php">Tourist Spots</a></li>
                <li class="breadcrumb-item active" aria-current="page"><?php echo $spot['name']; ?></li>
            </ol>
        </nav>

        <div class="card shadow-sm">
            <div class="row g-0">
                <div class="col-md-6">
                    <?php
                    // Collect all available images
                    $images = array();
                    if (!empty($spot['img2'])) $images[] = $spot['img2'];
                    if (!empty($spot['img3'])) $images[] = $spot['img3'];
                    if (!empty($spot['img4'])) $images[] = $spot['img4'];
                    
                    if (count($images) > 0):
                    ?>
                    <div id="spotCarousel" class="carousel slide" data-bs-ride="carousel">
                        <div class="carousel-indicators">
                            <?php for($i = 0; $i < count($images); $i++): ?>
                            <button type="button" 
                                    data-bs-target="#spotCarousel" 
                                    data-bs-slide-to="<?php echo $i; ?>" 
                                    <?php echo $i === 0 ? 'class="active" aria-current="true"' : ''; ?>
                                    aria-label="Slide <?php echo $i + 1; ?>">
                            </button>
                            <?php endfor; ?>
                        </div>
                        <div class="carousel-inner rounded-start">
                            <?php foreach($images as $index => $image): ?>
                            <div class="carousel-item <?php echo $index === 0 ? 'active' : ''; ?>">
                                <img src="<?php echo $image; ?>" class="d-block" alt="Tourist spot image <?php echo $index + 1; ?>">
                            </div>
                            <?php endforeach; ?>
                        </div>
                        <?php if(count($images) > 1): ?>
                        <button class="carousel-control-prev" type="button" data-bs-target="#spotCarousel" data-bs-slide="prev">
                            <span class="carousel-control-prev-icon" aria-hidden="true"></span>
                            <span class="visually-hidden">Previous</span>
                        </button>
                        <button class="carousel-control-next" type="button" data-bs-target="#spotCarousel" data-bs-slide="next">
                            <span class="carousel-control-next-icon" aria-hidden="true"></span>
                            <span class="visually-hidden">Next</span>
                        </button>
                        <?php endif; ?>
                    </div>
                    <?php else: ?>
                        <?php if ($spot['image_url']): ?>
                        <img src="<?php echo $spot['image_url']; ?>" class="img-fluid rounded-start" alt="<?php echo $spot['name']; ?>" style="width: 100%; height: 400px; object-fit: cover;">
                        <?php endif; ?>
                    <?php endif; ?>
                </div>
                <div class="col-md-6">
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-start">
                            <div>
                                <h2 class="card-title"><?php echo $spot['name']; ?></h2>
                                <div class="mb-3">
                                    <div class="text-warning">
                                        <?php
                                        $rating = $spot['avg_rating']; // Don't round it
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
                                            <a href="#" data-bs-toggle="modal" data-bs-target="#allReviewsModal">See all</a>
                                        </span>
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                        <p class="card-text">
                            <i class="fas fa-map-marker-alt text-primary"></i> <?php echo $spot['location']; ?>
                        </p>
                        
                        <div class="mb-4">
                            <h5 class="text-primary">Opening Hours</h5>
                            <p><?php echo $spot['opening_hours']; ?></p>
                            
                            <h5 class="text-primary">Entrance Fee</h5>
                            <p>₱<?php echo number_format($spot['entrance_fee'], 2); ?></p>
                        </div>

                        <h5 class="text-primary">Description</h5>
                        <p class="card-text"><?php echo $spot['description']; ?></p>
                        
                        <h5 class="text-primary">Tips for Visitors</h5>
                        <p class="card-text"><?php echo $spot['tips']; ?></p>
                        
                        <div class="mt-4">
                            <a href="map.php?spot=<?php echo $spot['spot_id']; ?>" class="btn btn-primary">
                                <i class="fas fa-map-marker-alt"></i> View on Map
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Review Modal -->
    <div class="modal fade" 
         id="reviewModal" 
         tabindex="-1" 
         role="dialog"
         aria-labelledby="writeReviewModalLabel"
         aria-modal="true">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="writeReviewModalLabel">Review Your Experience</h5>
                    <button type="button" 
                            class="btn-close" 
                            data-bs-dismiss="modal" 
                            aria-label="Close">
                    </button>
                </div>
                <form id="reviewForm" action="../includes/submit_review.php" method="POST" enctype="multipart/form-data">
                    <div class="modal-body">
                        <input type="hidden" name="spot_id" value="<?php echo $spot_id; ?>">
                        <div class="mb-3 text-center">
                            <div class="star-rating">
                                <?php for ($i = 1; $i <= 5; $i++): ?>
                                <i class="far fa-star" data-rating="<?php echo $i; ?>"></i>
                                <?php endfor; ?>
                            </div>
                            <input type="hidden" name="rating" id="selectedRating" required>
                        </div>
                        <div class="mb-3">
                            <label for="comment" class="form-label">Your Review (Optional)</label>
                            <textarea class="form-control" id="comment" name="comment" rows="3" placeholder="Share your experience..."></textarea>
                        </div>
                        <div class="mb-3">
                            <label for="review_image" class="form-label">Add a Photo (Optional)</label>
                            <input type="file" class="form-control" id="review_image" name="review_image" accept="image/*">
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                        <button type="submit" class="btn btn-primary">Submit Review</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- All Reviews Modal -->
    <div class="modal fade" 
         id="allReviewsModal" 
         tabindex="-1" 
         role="dialog"
         aria-labelledby="reviewsModalLabel"
         aria-modal="true">
        <div class="modal-dialog modal-lg" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="reviewsModalLabel">Reviews for <?php echo $spot['name']; ?></h5>
                    <button type="button" 
                            class="btn-close" 
                            data-bs-dismiss="modal" 
                            aria-label="Close">
                    </button>
                </div>
                <div class="modal-body">
                    <div class="row mb-4">
                        <div class="col-md-4 text-center">
                            <h1 class="display-4"><?php echo number_format($spot['avg_rating'], 1); ?></h1>
                            <div class="text-warning mb-2">
                                <?php
                                $rating = $spot['avg_rating']; // Don't round it
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
                            </div>
                            <p class="text-muted"><?php echo $spot['review_count']; ?> review<?php echo $spot['review_count'] != 1 ? 's' : ''; ?></p>
                        </div>
                        <div class="col-md-8">
                            <?php
                            $ratings = [
                                ['label' => 'Excellent', 'count' => $spot['five_star']],
                                ['label' => 'Very Good', 'count' => $spot['four_star']],
                                ['label' => 'Average', 'count' => $spot['three_star']],
                                ['label' => 'Poor', 'count' => $spot['two_star']],
                                ['label' => 'Terrible', 'count' => $spot['one_star']]
                            ];
                            for ($i = 5; $i >= 1; $i--):
                                $percentage = $spot['review_count'] > 0 ? ($ratings[5-$i]['count'] / $spot['review_count']) * 100 : 0;
                            ?>
                            <div class="d-flex align-items-center mb-2">
                                <div class="text-muted" style="width: 100px;"><?php echo $ratings[5-$i]['label']; ?></div>
                                <div class="progress flex-grow-1 mx-2" style="height: 8px;">
                                    <div class="progress-bar bg-warning" role="progressbar" style="width: <?php echo $percentage; ?>%" 
                                         aria-valuenow="<?php echo $percentage; ?>" aria-valuemin="0" aria-valuemax="100"></div>
                                </div>
                                <div class="text-muted" style="width: 50px;"><?php echo $ratings[5-$i]['count']; ?></div>
                            </div>
                            <?php endfor; ?>
                        </div>
                    </div>

                    <?php if (isset($_SESSION['user_id'])): ?>
                    <div class="text-center mb-4">
                        <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#reviewModal" data-bs-dismiss="modal">
                            <i class="fas fa-pencil-alt me-2"></i>Write a Review
                        </button>
                    </div>
                    <?php endif; ?>

                    <div class="reviews-list">
                        <?php if ($reviews->num_rows > 0): ?>
                            <?php while ($review = $reviews->fetch_assoc()): ?>
                            <div class="border-bottom mb-3 pb-3">
                                <div class="d-flex align-items-center mb-2">
                                    <img src="../<?php echo $review['profile_picture']; ?>" alt="Profile" class="rounded-circle me-2" style="width: 40px; height: 40px; object-fit: cover;">
                                    <div class="flex-grow-1">
                                        <h6 class="mb-0"><?php echo htmlspecialchars($review['username']); ?></h6>
                                        <div class="text-warning">
                                            <?php for($i = 1; $i <= 5; $i++): ?>
                                                <?php if($i <= $review['rating']): ?>
                                                    <i class="fas fa-star"></i>
                                                <?php else: ?>
                                                    <i class="far fa-star"></i>
                                                <?php endif; ?>
                                            <?php endfor; ?>
                                        </div>
                                        <small class="text-muted"><?php echo date('F j, Y', strtotime($review['created_at'])); ?></small>
                                    </div>
                                    <!-- Add 3-dot menu for report -->
                                    <div class="dropdown">
                                        <button class="btn btn-link text-dark" type="button" id="reviewMenu<?php echo $review['review_id']; ?>" data-bs-toggle="dropdown" aria-expanded="false">
                                            <i class="fas fa-ellipsis-v"></i>
                                        </button>
                                        <ul class="dropdown-menu dropdown-menu-end" aria-labelledby="reviewMenu<?php echo $review['review_id']; ?>">
                                            <li><a class="dropdown-item" href="#" onclick="reportReview(<?php echo $review['review_id']; ?>, '<?php echo htmlspecialchars($review['username']); ?>')">
                                                <i class="fas fa-flag me-2"></i>Report Review
                                            </a></li>
                                        </ul>
                                    </div>
                                </div>
                                <?php if ($review['comment']): ?>
                                <p class="mb-0"><?php echo htmlspecialchars($review['comment']); ?></p>
                                <?php endif; ?>
                                <?php if (!empty($review['image_url'])): ?>
                                <div class="mt-2">
                                    <img src="<?php echo '../uploads/review_pic/' . $review['image_url']; ?>" 
                                         alt="Review Image" 
                                         class="img-fluid review-image" 
                                         style="max-width: 200px; height: auto; cursor: pointer;"
                                         data-bs-toggle="modal" 
                                         data-bs-target="#imageModal"
                                         onclick="showFullImage(this.src)">
                                </div>
                                <?php endif; ?>
                            </div>
                            <?php endwhile; ?>
                        <?php else: ?>
                            <p class="text-center">No reviews yet. Be the first to review!</p>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Report Review Modal -->
    <div class="modal fade" id="reportReviewModal" tabindex="-1" aria-labelledby="reportReviewModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="reportReviewModalLabel">Report Review</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form id="reportReviewForm">
                    <div class="modal-body">
                        <input type="hidden" id="reportReviewId" name="review_id">
                        <div class="mb-3">
                            <label for="reportType" class="form-label">Report Type</label>
                            <select class="form-select" id="reportType" name="report_type" required>
                                <option value="">Select a reason</option>
                                <option value="inappropriate">Inappropriate Content</option>
                                <option value="spam">Spam</option>
                                <option value="harassment">Harassment</option>
                                <option value="false_information">False Information</option>
                                <option value="other">Other</option>
                            </select>
                        </div>
                        <div class="mb-3">
                            <label for="reportDescription" class="form-label">Description</label>
                            <textarea class="form-control" id="reportDescription" name="description" rows="3" required></textarea>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-primary">Submit Report</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Image Modal -->
    <div class="modal fade" id="imageModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-lg modal-dialog-centered">
            <div class="modal-content bg-transparent border-0">
                <div class="modal-body text-center p-0">
                    <img id="modalImage" src="" alt="Full size review image" class="img-fluid" style="max-height: 90vh;">
                </div>
            </div>
        </div>
    </div>

    <?php include '../includes/footer.php'; ?>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const stars = document.querySelectorAll('.star-rating i');
            const ratingInput = document.getElementById('selectedRating');

            stars.forEach(star => {
                star.addEventListener('mouseover', function() {
                    const rating = this.dataset.rating;
                    stars.forEach(s => {
                        if (s.dataset.rating <= rating) {
                            s.classList.remove('far');
                            s.classList.add('fas');
                        } else {
                            s.classList.remove('fas');
                            s.classList.add('far');
                        }
                    });
                });

                star.addEventListener('click', function() {
                    ratingInput.value = this.dataset.rating;
                });
            });

            const starRating = document.querySelector('.star-rating');
            starRating.addEventListener('mouseout', function() {
                stars.forEach(s => {
                    if (s.dataset.rating <= ratingInput.value) {
                        s.classList.remove('far');
                        s.classList.add('fas');
                    } else {
                        s.classList.remove('fas');
                        s.classList.add('far');
                    }
                });
            });
        });
        
        // Initialize carousel with custom interval
        document.addEventListener('DOMContentLoaded', function() {
            var myCarousel = document.querySelector('#spotCarousel');
            if (myCarousel) {
                var carousel = new bootstrap.Carousel(myCarousel, {
                    interval: 5000, // Change slides every 5 seconds
                    wrap: true // Enable continuous loop
                });
            }
        });

        function showFullImage(imageSrc) {
            document.getElementById('modalImage').src = imageSrc;
        }

        function reportReview(reviewId, username) {
            if (!<?php echo isset($_SESSION['user_id']) ? 'true' : 'false'; ?>) {
                alert('Please login to report a review');
                return;
            }
            document.getElementById('reportReviewId').value = reviewId;
            document.getElementById('reportReviewModalLabel').textContent = `Report Review by ${username}`;
            new bootstrap.Modal(document.getElementById('reportReviewModal')).show();
        }

        document.getElementById('reportReviewForm').addEventListener('submit', function(e) {
            e.preventDefault();
            const formData = new FormData(this);
            
            fetch('../includes/submit_review_report.php', {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    alert('Report submitted successfully');
                    bootstrap.Modal.getInstance(document.getElementById('reportReviewModal')).hide();
                    this.reset();
                } else {
                    alert(data.message || 'Error submitting report');
                }
            })
            .catch(error => {
                console.error('Error:', error);
                alert('Error submitting report');
            });
        });
    </script>
</body>
</html>