<?php
session_start();
$current_user = isset($_SESSION['user_id']) ? getUserById($_SESSION['user_id']) : null;
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>SafePaws Rescue</title>
</head>
<body>
<h1>SafePaws Rescue</h1>
<br>

<?php if ($current_user): ?>
    <p>Hi, <?= htmlentities($current_user->first_name) ?>! <a href="auth/logout.php">Logout</a></p>
<?php else: ?>
    <p><a href="auth/login.php">Admin Login</a></p>
<?php endif; ?>

<p><a href="public/animals.php">Browse animals for adoption</a></p>
<p><a href="contact/index.php">Contact Us</a></p>
</body>
</html>
