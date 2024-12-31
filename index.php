<?php
session_start();
require "db.php";
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>PicConnect - Welcome</title>
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        .loader-container {
            height: 100vh;
            display: flex;
            justify-content: center;
            align-items: center;
            flex-direction: column;
        }
        .loader {
            width: 48px;
            height: 48px;
            border: 5px solid #0d6efd;
            border-bottom-color: transparent;
            border-radius: 50%;
            animation: rotation 1s linear infinite;
        }
        @keyframes rotation {
            0% { transform: rotate(0deg) }
            100% { transform: rotate(360deg) }
        }
    </style>
</head>
<body class="bg-light">
    <div class="loader-container">
        <img src="images/picconnect-logo.png" alt="PicConnect Logo" class="mb-4" height="60">
        <div class="loader mb-3"></div>
        <p class="text-muted">Please wait...</p>
    </div>

    <script>
        // Redirect after page load
        window.onload = function() {
            <?php if (validSession()): ?>
                window.location.href = 'homePage.php';
            <?php else: ?>
                window.location.href = 'login.php';
            <?php endif; ?>
        }
    </script>
</body>
</html> 