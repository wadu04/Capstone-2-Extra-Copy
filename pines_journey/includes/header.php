<nav class="navbar navbar-expand-lg navbar-light bg-light shadow-sm">
    <div class="container">
        <a class="navbar-brand" href="../index.php">
            <span class="text-primary">Pines'</span><span class="text-success">Journey</span>
        </a>
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
            <span class="navbar-toggler-icon"></span>
        </button>
        <div class="collapse navbar-collapse" id="navbarNav">
            <ul class="navbar-nav me-auto">
                <li class="nav-item">
                    <a class="nav-link" href="../index.php">Home</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="../pages/map.php">Maps</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="../pages/spots.php">Tourist Spots</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="../pages/events.php">Events</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="../pages/blog.php">Blog</a>
                </li>
               
                <li class="nav-item">
                    <a class="nav-link" href="../pages/games.php">Games</a>
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
                                <li><a class="dropdown-item" href="../admin/dashboard.php">Admin Dashboard</a></li>
                            <?php endif; ?>
                            <li><a class="dropdown-item" href="../pages/profile.php">Profile</a></li>
                            <li><a class="dropdown-item" href="../pages/myblogs.php">My Blogs</a></li>
                            <li><a class="dropdown-item" href="../pages/myfavourites.php">Favourites</a></li>
                            <li><a class="dropdown-item" href="../pages/myqr-scans.php">Qr Scans</a></li>
                            <li><a class="dropdown-item" href="../includes/logout.php">Logout</a></li>
                        </ul>
                    </li>
                <?php else: ?>
                    <li class="nav-item">
                        <a class="nav-link" href="../pages/login.php">Login</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="../pages/register.php">Register</a>
                    </li>
                <?php endif; ?>
            </ul>
        </div>
    </div>
</nav>
