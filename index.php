<?php
/**
 * Homepage.
 *
 * Shows a login link when logged out, or a greeting + logout link when logged in.
 * Session state is checked directly against the database to ensure that the user is still active.
 */

session_start();

/** @var PDO $pdo */
require_once __DIR__ . '/connection.php';

$current_user = null;
if (isset($_SESSION['user_id'])) {
    $stmt = $pdo->prepare("SELECT * FROM users WHERE user_id = :id AND status = 'active'");
    $stmt->execute(['id' => $_SESSION['user_id']]);
    if ($stmt->rowCount() === 1) {
        $current_user = $stmt->fetchObject();
    }
}
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
