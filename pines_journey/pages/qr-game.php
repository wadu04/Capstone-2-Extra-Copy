<?php
session_start();
require_once '../includes/config.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>QR Code Game - Scan & Earn Points!</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="../assets/css/style.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/jsqr@1.4.0/dist/jsQR.js"></script>
    <style>
        #video-container {
            width: 100%;
            max-width: 640px;
            margin: 0 auto;
        }
        #video {
            width: 100%;
            height: auto;
        }
        #result {
            margin-top: 20px;
            padding: 10px;
            border-radius: 5px;
        }
        .success { background-color: #d4edda; color: #155724; }
        .error { background-color: #f8d7da; color: #721c24; }
    </style>
</head>
<body>
    <?php include '../includes/header.php'; ?>

    <div class="container mt-5">
        <h2 class="text-center mb-4">QR Code Scanner Game</h2>
        <div id="video-container">
            <video id="video" playsinline></video>
        </div>
        <div id="result" style="display: none;"></div>
    </div>

    <script>
        let video = document.getElementById('video');
        let canvasElement = document.createElement('canvas');
        let canvas = canvasElement.getContext('2d');
        let scanning = false;

        navigator.mediaDevices.getUserMedia({ video: { facingMode: "environment" } })
            .then(function(stream) {
                video.srcObject = stream;
                video.setAttribute('playsinline', true);
                video.play();
                requestAnimationFrame(tick);
            });

        function tick() {
            if (video.readyState === video.HAVE_ENOUGH_DATA) {
                canvasElement.height = video.videoHeight;
                canvasElement.width = video.videoWidth;
                canvas.drawImage(video, 0, 0, canvasElement.width, canvasElement.height);
                let imageData = canvas.getImageData(0, 0, canvasElement.width, canvasElement.height);
                let code = jsQR(imageData.data, imageData.width, imageData.height, {
                    inversionAttempts: "dontInvert",
                });

                if (code && !scanning) {
                    scanning = true;
                    processQRCode(code.data);
                }
            }
            requestAnimationFrame(tick);
        }

        function processQRCode(content) {
            fetch('process_qr.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded',
                },
                body: 'qr_content=' + encodeURIComponent(content)
            })
            .then(response => response.json())
            .then(data => {
                let resultDiv = document.getElementById('result');
                resultDiv.style.display = 'block';
                resultDiv.textContent = data.message;
                resultDiv.className = data.success ? 'success' : 'error';
                
                if (data.success) {
                    setTimeout(() => {
                        scanning = false;
                        resultDiv.style.display = 'none';
                    }, 3000);
                } else {
                    scanning = false;
                }
            })
            .catch(error => {
                console.error('Error:', error);
                scanning = false;
            });
        }
    </script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

    <?php include '../includes/footer.php'; ?>
</body>
</html>