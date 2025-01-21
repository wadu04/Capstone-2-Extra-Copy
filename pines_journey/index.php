<?php
require_once 'includes/config.php';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Pine's Journey - Discover Baguio City</title>
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Custom CSS -->
    <link href="assets/css/style.css" rel="stylesheet">
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
</head>
<body>
    <!-- Navigation -->
    <nav class="navbar navbar-expand-lg navbar-light bg-light shadow-sm">
        <div class="container">
            <a class="navbar-brand" href="index.php">
                <span class="text-primary">Pine's</span><span class="text-success">Journey</span>
            </a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav me-auto">
                    <li class="nav-item">
                        <a class="nav-link" href="index.php">Home</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="pages/map.php">Maps</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="pages/spots.php">Tourist Spots</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="pages/events.php">Events</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="pages/blog.php">Blog</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="pages/culture.php">Discover</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="pages/games.php">Games</a>
                    </li>
                </ul>
                <ul class="navbar-nav">
                    <?php if (isLoggedIn()): ?>
                        <li class="nav-item dropdown">
                            <a class="nav-link dropdown-toggle" href="#" id="navbarDropdown" role="button" data-bs-toggle="dropdown">
                                <i class="fas fa-user"></i> <?php echo $_SESSION['username']; ?>
                            </a>
                            <ul class="dropdown-menu">
                                <?php if (isAdmin()): ?>
                                    <li><a class="dropdown-item" href="admin/dashboard.php">Admin Dashboard</a></li>
                                <?php endif; ?>
                                <li><a class="dropdown-item" href="pages/profile.php">Profile</a></li>
                                <li><hr class="dropdown-divider"></li>
                                <li><a class="dropdown-item" href="includes/logout.php">Logout</a></li>
                            </ul>
                        </li>
                    <?php else: ?>
                        <li class="nav-item">
                            <a class="nav-link" href="pages/login.php">Login</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="pages/register.php">Register</a>
                        </li>
                    <?php endif; ?>
                </ul>
            </div>
        </div>
    </nav>

    <!-- Hero Section -->
    
    <div class="hero-section position-relative">
        <div class="hero-background"></div>
        <div class="container position-relative py-5">
            <div class="row min-vh-75 align-items-center">
                <div class="col-lg-8 text-white">
                    <h1 class="display-3 fw-bold mb-4 text-shadow">Welcome to Baguio City</h1>
                    <p class="lead fs-4 mb-4 text-shadow">Experience the charm of the Summer Capital of the Philippines. Discover cultural heritage, breathtaking views, and unforgettable adventures.</p>
                    <div class="mt-4 d-flex gap-3">
                        <a href="pages/map.php" class="btn btn-primary btn-lg px-4">
                            <i class="fas fa-map-marked-alt me-2"></i>Explore Map
                        </a>
                        <a href="pages/spots.php" class="btn btn-light btn-lg px-4">
                            <i class="fas fa-landmark me-2"></i>View Spots
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <style>
        .hero-section .text-shadow {
        text-shadow: 7px 7px 3px rgba(0,0,0,0.5);
    }
        .hero-section h1, .hero-section p {
            text-shadow: 7px 7px 3px rgba(0,0,0,0.5);
        }
    .hero-section {
        position: relative;
        overflow: hidden;
    }
    .hero-background {
        position: absolute;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        background-image: url('assets/css/images/herooo.jpg');
        background-size: cover;
        background-position: center;
        background-repeat: no-repeat;
        filter: brightness(0.7);
    }
    .min-vh-75 {
        min-height: 75vh;
    }
    .hero-section .btn {
        transition: all 0.3s ease;
    }
    .hero-section .btn:hover {
        transform: translateY(-2px);
        box-shadow: 0 5px 15px rgba(0,0,0,0.2);
    }
    </style>
    
    

   <?php
    // Get upcoming events (limit to 3)
    $sql = "SELECT * FROM events WHERE start_datetime >= NOW() ORDER BY start_datetime ASC LIMIT 3";
    $result = $conn->query($sql);
    ?>
    <div class="container py-5">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h2 class="mb-0">Upcoming Events</h2>
            <a href="pages/events.php" class="btn btn-primary">See All Events</a>
        </div>
        
        <div class="row g-4">
            <?php while ($event = $result->fetch_assoc()): ?>
            <div class="col-md-4">
                <div class="card event-card h-100 border-0 shadow-sm">
                    <?php if ($event['image_url']): ?>
                    <img src="events/<?php echo $event['image_url']; ?>" class="card-img-top" alt="<?php echo $event['title']; ?>">
                    <?php endif; ?>
                    <div class="card-body">
                        <h5 class="card-title"><?php echo $event['title']; ?></h5>
                        <div class="mb-3">
                            <span class="badge bg-primary mb-2">
                                <i class="far fa-calendar"></i> 
                                <?php echo date('M d, Y', strtotime($event['start_datetime'])); ?>
                            </span>
                            <div class="text-muted small">
                                <i class="far fa-clock"></i> <?php echo date('h:i A', strtotime($event['start_datetime'])); ?>
                            </div>
                        </div>
                        <p class="card-text text-muted">
                            <i class="fas fa-map-marker-alt"></i> <?php echo $event['location']; ?>
                        </p>
                        <a href="pages/event-details.php?id=<?php echo $event['event_id']; ?>" class="btn btn-outline-primary">
                            View Details
                        </a>
                    </div>
                </div>
            </div>
            <?php endwhile; ?>
        </div>
    </div>

    <style>
    .event-card {
        transition: transform 0.3s ease, box-shadow 0.3s ease;
    }
    .event-card:hover {
        transform: translateY(-5px);
        box-shadow: 0 5px 20px rgba(0,0,0,0.15) !important;
    }
    .event-card .card-img-top {
        height: 200px;
        object-fit: cover;
    }
    .badge {
        font-weight: 500;
    }
   </style>
   
    

    <!-- Footer -->
    <footer class="bg-light py-4 mt-5">
        <div class="container">
            <div class="row">
                <div class="col-md-6">
                    <h5><span class="text-primary">Bagui</span><span class="text-success">Xplore</span></h5>
                    <p>Your ultimate guide to exploring Baguio City's culture and attractions.</p>
                </div>
                <div class="col-md-6 text-md-end">
                    <p>&copy; 2025 Pine's Journey All rights reserved.</p>
                </div>
            </div>
        </div>
    </footer>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
