<?php
/**
 * Add animal (admin).
 *
 * Species -> breed selection uses a cascading dropdown (vanilla JS,
 * breed data embedded directly since the dataset is small - no AJAX
 * needed). Foster carer assignment is optional and limited to active
 * carers only. Profile image upload is optional; the original filename
 * is never trusted - a new name is generated server-side before saving
 * into animal_profiles/.
 */

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
    <title>Add Animal - SafePaws Admin</title>
</head>
<body>
<!-- Your visible website content goes here -->


<!-- Link your external JavaScript file here -->
<script src="script.js"></script>
</body>
</html>

