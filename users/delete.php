<?php
// users/delete.php
require_once __DIR__ . '/../auth/authentication.php';
require_once __DIR__ . '/../connection.php';
require_once __DIR__ . '/../includes/csrf.php';
/** @var PDO $pdo */

if ($_SERVER['REQUEST_METHOD'] !== 'POST' || !csrf_verify($_POST['csrf_token'] ?? null)) {
    header('Location: list.php');
    exit();
}

$user_id = isset($_POST['id']) ? (int)$_POST['id'] : 0;

if ($user_id <= 0) {
    $_SESSION['error_message'] = 'Invalid user ID.';
    header('Location: list.php');
    exit();
}

if ($user_id === (int)$_SESSION['user_id']) {
    $_SESSION['error_message'] = 'You cannot delete your own account.';
    header('Location: list.php');
    exit();
}

try {
    $stmt = $pdo->prepare("SELECT username FROM users WHERE user_id = :id");
    $stmt->execute([':id' => $user_id]);
    $user = $stmt->fetch();

    if (!$user) {
        $_SESSION['error_message'] = 'User not found.';
        header('Location: list.php');
        exit();
    }

    $delete_stmt = $pdo->prepare("DELETE FROM users WHERE user_id = :id");
    $delete_stmt->execute([':id' => $user_id]);

    $_SESSION['success_message'] = "User '{$user['username']}' has been deleted successfully!";
    header('Location: list.php');
    exit;

} catch (PDOException $e) {
    error_log('Database error: ' . $e->getMessage());
    $_SESSION['error_message'] = 'Failed to delete user. Please try again.';
    header('Location: list.php');
    exit;
}
