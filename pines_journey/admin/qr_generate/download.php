<?php
session_start();
require_once '../../includes/config.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: ../login.php");
    exit();
}

if (isset($_GET['qr_id'])) {
    $qr_id = (int)$_GET['qr_id'];
    
    // Get QR code content
    $stmt = $conn->prepare("SELECT content FROM qr_codes WHERE qr_id = ?");
    $stmt->bind_param("i", $qr_id);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($qr = $result->fetch_assoc()) {
        $qr_url = "https://api.qrserver.com/v1/create-qr-code/?size=300x300&data=" . urlencode($qr['content']);
        
        // Get QR code image content
        $qr_content = file_get_contents($qr_url);
        
        if ($qr_content !== false) {
            // Set headers for download
            header('Content-Type: image/png');
            header('Content-Disposition: attachment; filename="qr_' . $qr['content'] . '.png"');
            header('Content-Length: ' . strlen($qr_content));
            
            // Output QR code image
            echo $qr_content;
            exit();
        }
    }
}

// If something went wrong, redirect back
header("Location: qr-generate.php");
exit();
?>
