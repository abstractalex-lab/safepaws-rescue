<?php
session_start();
require_once __DIR__ . '/../auth/authentication.php';

/** @var PDO $pdo */
require_once __DIR__ . '/../connection.php';
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Animals - SafePaws Admin</title>
    <!-- Link your external CSS file here -->
    <link rel="stylesheet" href="style.css">
</head>
<body>

<!-- Your visible website content goes here -->

<!-- Link your external JavaScript file here -->
<script src="script.js"></script>
</body>
</html>
