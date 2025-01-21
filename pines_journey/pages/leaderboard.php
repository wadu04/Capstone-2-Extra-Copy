<?php
session_start();
require_once '../includes/config.php';

// Get leaderboard data
$query = "SELECT u.username, l.total_points, l.last_updated 
          FROM leaderboard l 
          JOIN users u ON l.user_id = u.user_id 
          ORDER BY l.total_points DESC 
          LIMIT 100";
$result = $conn->query($query);
$leaderboard = [];
if ($result) {
    while ($row = $result->fetch_assoc()) {
        $leaderboard[] = $row;
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="../assets/css/style.css" rel="stylesheet">
    <title>QR Game Leaderboard</title>
    <style>
        .leaderboard-table {
            width: 100%;
            max-width: 800px;
            margin: 0 auto;
            border-collapse: collapse;
        }
        .leaderboard-table th, .leaderboard-table td {
            padding: 12px;
            text-align: left;
            border-bottom: 1px solid #ddd;
        }
        .leaderboard-table th {
            background-color: #f5f5f5;
        }
        .rank-1 { background-color: #ffd700; }
        .rank-2 { background-color: #c0c0c0; }
        .rank-3 { background-color: #cd7f32; }
    </style>
</head>
<body>
    <?php include '../includes/header.php'; ?>

    <div class="container mt-5">
        <h2 class="text-center mb-4">QR Game Leaderboard</h2>
        
        <table class="leaderboard-table">
            <thead>
                <tr>
                    <th>Rank</th>
                    <th>Username</th>
                    <th>Points</th>
                    <th>Last Updated</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($leaderboard as $index => $player): ?>
                    <tr class="<?php echo $index < 3 ? 'rank-' . ($index + 1) : ''; ?>">
                        <td><?php echo $index + 1; ?></td>
                        <td><?php echo htmlspecialchars($player['username']); ?></td>
                        <td><?php echo number_format($player['total_points']); ?></td>
                        <td><?php echo date('Y-m-d H:i:s', strtotime($player['last_updated'])); ?></td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>

    <?php include '../includes/footer.php'; ?>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>