<?php
require_once '../includes/config.php';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Games - Pines'Journey</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="../assets/css/style.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        .games-container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 0 15px;
        }
        .game-card {
            transition: transform 0.3s;
            height: 100%;
            border: none;
            border-radius: 12px;
            overflow: hidden;
            box-shadow: 0 3px 10px rgba(0,0,0,0.1);
            background: white;
        }
        .game-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 5px 15px rgba(0,0,0,0.15);
        }
        .game-card .card-img-top {
            height: 160px;
            object-fit: cover;
            border-bottom: 2px solid #007bff;
        }
        .game-card .card-body {
            padding: 1rem;
        }
        .game-title {
            font-size: 1.1rem;
            font-weight: 600;
            margin-bottom: 0.5rem;
            color: #2c3e50;
        }
        .game-description {
            font-size: 0.9rem;
            color: #6c757d;
            margin-bottom: 1rem;
            line-height: 1.4;
        }
        .play-btn {
            width: 100%;
            padding: 0.5rem;
            font-size: 0.9rem;
            font-weight: 500;
            border-radius: 6px;
        }
        .games-header {
            text-align: center;
            margin-bottom: 2rem;
            padding: 1rem 0;
        }
        .games-header h2 {
            font-size: 2rem;
            font-weight: 700;
            color: #2c3e50;
            margin-bottom: 0.5rem;
        }
        .games-header p {
            color: #6c757d;
            max-width: 600px;
            margin: 0 auto;
            font-size: 1rem;
        }
        .nav-buttons {
            margin-bottom: 2rem;
        }
        .nav-buttons .btn {
            padding: 0.5rem 1.5rem;
            margin: 0 0.3rem;
            font-weight: 500;
            border-radius: 6px;
        }
        .game-stats {
            display: flex;
            justify-content: space-between;
            margin-top: 0.5rem;
            padding-top: 0.5rem;
            border-top: 1px solid #eee;
            font-size: 0.8rem;
        }
        .stat-item {
            text-align: center;
            color: #6c757d;
        }
        .stat-value {
            font-weight: 600;
            color: #007bff;
            font-size: 0.9rem;
        }
    </style>
</head>
<body>
    <?php include '../includes/header.php'; ?>

    <div class="games-container py-4">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h2 class="m-0">Fun Games</h2>
            <div class="dropdown">
                <button class="btn position-relative" type="button" id="notificationDropdown" data-bs-toggle="dropdown" aria-expanded="false" style="background: none; border: none;">
                    <i class="fas fa-bell text-dark"></i>
                    <span class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-danger notification-badge" style="display: none;">
                        0
                    </span>
                </button>
                <div class="dropdown-menu dropdown-menu-end p-3" aria-labelledby="notificationDropdown" style="min-width: 300px; max-height: 400px; overflow-y: auto;">
                    <div id="notificationContainer">
                        <div class="text-center text-muted empty-notification">
                            <i class="fas fa-inbox fa-2x mb-2"></i>
                            <p>Scan QR codes and Create a blog post in order to get reward</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="row g-3">
            <div class="col-lg-3 col-md-4 col-sm-6">
                <div class="card game-card">
                    <img src="../assets/images/games/qr.jpg" class="card-img-top" alt="QR Code Game">
                    <div class="card-body">
                        <h5 class="game-title">QR Code Explorer</h5>
                        <p class="game-description">Earn points by scanning QR codes at tourist spots and events</p>
                        <div class="game-stats">
                            
                            <div class="stat-item d-flex justify-content-center">
                                <div class="stat-value"><i class="fas fa-qrcode"></i></div>
                                <div class="mx-2">Scan & Earn points</div>
                            </div>
                        </div>
                        <a href="qr-game.php" class="btn btn-primary play-btn mt-2">
                            <i class="fas fa-play me-1"></i>Play Now
                        </a>
                    </div>
                </div>
            </div>

            <div class="col-lg-3 col-md-4 col-sm-6">
                <div class="card game-card">
                    <img src="../assets/images/games/trivia.jpg" class="card-img-top" alt="Baguio Quiz Game">
                    <div class="card-body">
                        <h5 class="game-title">Baguio City Quiz</h5>
                        <p class="game-description">Test your knowledge about Baguio City</p>
                        <div class="game-stats">
                            <div class="stat-item">
                                <div class="stat-value">30</div>
                                <div>Questions</div>
                            </div>
                          
                        </div>
                        <a href="quiz_game.php" class="btn btn-primary play-btn mt-2">
                            <i class="fas fa-play me-1"></i>Play Now
                        </a>
                    </div>
                </div>
            </div>

            <div class="col-lg-3 col-md-4 col-sm-6">
                <div class="card game-card">
                    <img src="../assets/images/games/memory.jpg" class="card-img-top" alt="Memory Match Game">
                    <div class="card-body">
                        <h5 class="game-title">Memory Match</h5>
                        <p class="game-description">Match pairs of Baguio landmarks!</p>
                        <div class="game-stats">
                            <div class="stat-item">
                            <div class="stat-value"><i class="fas fa-clock"></i></div>
                                <div>Matches</div>
                            </div>
                          
                        </div>
                        <a href="memory.php" class="btn btn-primary play-btn mt-2">
                            <i class="fas fa-play me-1"></i>Play Now
                        </a>
                    </div>
                </div>
            </div>

            <div class="col-lg-3 col-md-4 col-sm-6">
                <div class="card game-card">
                    <img src="../assets/images/games/puzzle.jpg" class="card-img-top" alt="Word Puzzle Game">
                    <div class="card-body">
                        <h5 class="game-title">Word Puzzle</h5>
                        <p class="game-description">Find hidden words about Baguio</p>
                        <div class="game-stats">
                            
                            <div class="stat-item">
                                <div class="stat-value"><i class="fas fa-puzzle-piece"></i></div>
                                <div>Solve</div>
                            </div>
                        </div>
                        <a href="word-puzzle.php" class="btn btn-primary play-btn mt-2">
                            <i class="fas fa-play me-1"></i>Play Now
                        </a>
                    </div>
                </div>
            </div>
            <div class="col-lg-3 col-md-4 col-sm-6">
                <div class="card game-card">
                    <img src="../assets/images/games/guess.jpg" class="card-img-top" alt="Baguio Quiz Game">
                    <div class="card-body">
                        <h5 class="game-title">Image Guessing Game</h5>
                        <p class="game-description">Test your knowledge of Baguio City by guessing landmarks, food, and culture.</p>
                        <div class="game-stats">
                            <div class="stat-item">
                                <div class="stat-value"></div>
                                <div class="stat-value"><i class="fas fa-image"></i></div>
                                <div>Guessing</div>
                            </div>
                          
                        </div>
                        <a href="guessing-game.php" class="btn btn-primary play-btn mt-2">
                            <i class="fas fa-play me-1"></i>Play Now
                        </a>
                    </div>
                </div>
            </div>

            <div class="col-lg-3 col-md-4 col-sm-6">
                <div class="card game-card">
                    <img src="../assets/images/games/lan.jpg" class="card-img-top" alt="Learn languages Game">
                    <div class="card-body">
                        <h5 class="game-title">Learn Local Languages</h5>
                        <p class="game-description">Learn Ilocano, Ibaloy, and Kankanaey languages through quizzes!</p>
                        <div class="game-stats">
                      
                            <div class="stat-item">
                                <div class="stat-value"><i class="fas fa-language"></i></div>
                                <div>Learn</div>
                            </div>
                        </div>
                        <a href="learn-languages.php" class="btn btn-primary play-btn mt-2">
                            <i class="fas fa-play me-1"></i>Play Now
                        </a>
                    </div>
                </div>
            </div>
            <div class="col-lg-3 col-md-4 col-sm-6">
                <div class="card game-card">
                    <img src="../assets/images/games/guess.jpg" class="card-img-top" alt="Baguio Quiz Game">
                    <div class="card-body">
                        <h5 class="game-title">Image Guessing Game</h5>
                        <p class="game-description">Test your knowledge of Baguio City by guessing landmarks, food, and culture.</p>
                        <div class="game-stats">
                            <div class="stat-item">
                                <div class="stat-value"></div>
                                <div class="stat-value"><i class="fas fa-image"></i></div>
                                <div>Guessing</div>
                            </div>
                          
                        </div>
                        <a href="panagbenga.php" class="btn btn-primary play-btn mt-2">
                            <i class="fas fa-play me-1"></i>Play Now
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <?php include '../includes/footer.php'; ?>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="../assets/js/notifications.js"></script>
</body>
</html>
