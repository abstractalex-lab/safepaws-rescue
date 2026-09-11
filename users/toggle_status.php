<?php
/**
 * Toggle system user active status (admin).
 *
 * Action script with no output of its own. Flips the user between active
 * and inactive, then redirects back to the list with a flash message.
 *
 * Requires POST with a valid CSRF token, and refuses to change the
 * account currently signed in - authentication.php re-checks status on
 * every request, so self-deactivating would lock the user out mid-session.
 *
 * @var PDO $pdo
 */

// Include necessary files
require_once __DIR__ . '/../auth/authentication.php';
require_once __DIR__ . '/../connection.php';
require_once __DIR__ . '/../includes/csrf.php';
/** @var PDO $pdo */

if ($_SERVER['REQUEST_METHOD'] !== 'POST' || !csrf_verify($_POST['csrf_token'] ?? null)) {
    header('Location: index.php');
    exit();
}

$user_id = isset($_POST['id']) ? (int)$_POST['id'] : 0;

if ($user_id <= 0) {
    $_SESSION['error_message'] = 'Invalid user ID.';
    header('Location: index.php');
    exit();
}

if ($user_id === (int)$_SESSION['user_id']) {
    $_SESSION['error_message'] = 'You cannot change your own status.';
    header('Location: index.php');
    exit();
}

try {
    $stmt = $pdo->prepare("SELECT status, username FROM users WHERE user_id = :id");
    $stmt->execute([':id' => $user_id]);
    $user = $stmt->fetch();

    if (!$user) {
        $_SESSION['error_message'] = 'User not found.';
        header('Location: index.php');
        exit();
    }

    $new_status = ($user['status'] == 'active') ? 'inactive' : 'active';

    $update_stmt = $pdo->prepare("UPDATE users SET status = :status WHERE user_id = :id");
    $update_stmt->execute([
        ':status' => $new_status,
        ':id' => $user_id
    ]);

    $status_text = ($new_status == 'active') ? 'activated' : 'deactivated';
    $_SESSION['success_message'] = "User '{$user['username']}' has been {$status_text} successfully!";
    header('Location: index.php');
    exit;

} catch (PDOException $e) {
    error_log('Database error: ' . $e->getMessage());
    $_SESSION['error_message'] = 'Failed to toggle user status. Please try again.';
    header('Location: index.php');
    exit;
}
