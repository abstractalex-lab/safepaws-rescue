<?php
/**
 * Admin login page.
 *
 * Handles the login form and credential verification. On success, writes user_id into the session.
 * On failure (bad username, bad password, or an inactive account) all three cases return the same
 * generic error message to avoid leaking account existence/state (security by obscurity).
 */

session_start();

/** @var PDO $pdo */
require_once __DIR__ . '/../connection.php';

$error = null;

// Only process the form if it was submitted via POST
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username'] ?? '');
    $password = $_POST['password'] ?? '';

    if ($username !== '' && $password !== '') {
        // Only active accounts are ever fetched - inactive users fall straight
        // through to the generic error below, same as a wrong username.
        $stmt = $pdo->prepare(
            "SELECT * FROM users WHERE username = :username AND status = 'active'"
        );
        $stmt->execute(['username' => $username]);

        $authenticated = false;

        // Confirms a live, active match exists right now - not just that the username was typed correctly
        if ($stmt->rowCount() === 1) {
            $user = $stmt->fetchObject();

            // password_verify() re-derives the salt from the stored hash and compares safely
            if (password_verify($password, $user->password)) {
                $authenticated = true;
            }
        }

        if ($authenticated) {
            // Rotate the session ID on login to prevent session fixation
            session_regenerate_id(true);
            $_SESSION['user_id'] = $user->user_id;
            header("Location: ../index.php");
            exit;
        }

    }
    $error = "Invalid username or password.";
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - SafePaws Admin</title>
</head>
<body>
<h1>Admin Login</h1>

<?php if ($error): ?>
    <p style="color:red;"><?= htmlentities($error) ?></p>
<?php endif; ?>

<form method="post" action="login.php">
    <label for="username">Username</label><br>
    <input type="text" id="username" name="username" required>
    <br><br>

    <label for="password">Password</label><br>
    <input type="password" id="password" name="password" required>
    <br><br>

    <button type="submit">Login</button>
</form>
</body>
</html>