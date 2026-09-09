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
            header("Location: ../dashboard.php");
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
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
</head>
<body class="bg-light">

<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-5 col-lg-4">
            <div class="text-center mt-5 mb-4">
                <h1 class="h3">
                    <i class="bi bi-heart-fill text-danger"></i> SafePaws Rescue
                </h1>
                <p class="text-muted">Staff login</p>
            </div>

            <div class="card shadow-sm">
                <div class="card-body p-4">

                    <?php if ($error): ?>
                        <div class="alert alert-danger" role="alert">
                            <i class="bi bi-exclamation-triangle"></i> <?= htmlentities($error) ?>
                        </div>
                    <?php endif; ?>

                    <form method="post" action="login.php">
                        <div class="mb-3">
                            <label for="username" class="form-label">Username</label>
                            <input type="text" class="form-control" id="username" name="username"
                                   required autofocus>
                        </div>

                        <div class="mb-3">
                            <label for="password" class="form-label">Password</label>
                            <input type="password" class="form-control" id="password" name="password" required>
                        </div>

                        <button type="submit" class="btn btn-primary w-100">
                            <i class="bi bi-box-arrow-in-right"></i> Login
                        </button>
                    </form>

                </div>
            </div>

            <p class="text-center mt-3">
                <a href="../index.php" class="text-muted">Back to SafePaws Rescue</a>
            </p>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>