<?php
/**
 * Delete contact enquiry (admin).
 *
 * Action script with no output of its own. Performs the delete and
 * redirects back to the list with a flash message.
 *
 * Requires POST with a valid CSRF token, so a stray GET (prefetch,
 * pasted URL, crawler) can't destroy a record.
 *
 * Nothing references contact_messages, so no foreign key can block this.
 *
 * @var PDO $pdo
 */

// Require authentication and database connection
require_once __DIR__ . '/../auth/authentication.php';
require_once __DIR__ . '/../connection.php';
require_once __DIR__ . '/../includes/csrf.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST' || !csrf_verify($_POST['csrf_token'] ?? null)) {
    header('Location: list.php');
    exit();
}

$message_id = isset($_POST['id']) ? (int)$_POST['id'] : 0;

if ($message_id <= 0) {
    $_SESSION['error_message'] = 'Invalid message ID.';
    header('Location: list.php');
    exit();
}

try {
    $stmt = $pdo->prepare("SELECT name FROM contact_messages WHERE message_id = :id");
    $stmt->execute([':id' => $message_id]);
    $message = $stmt->fetch();

    if (!$message) {
        $_SESSION['error_message'] = 'Message not found.';
        header('Location: list.php');
        exit();
    }

    $delete_stmt = $pdo->prepare("DELETE FROM contact_messages WHERE message_id = :id");
    $delete_stmt->execute([':id' => $message_id]);

    $_SESSION['success_message'] = "Message from '{$message['name']}' has been deleted successfully!";
    header('Location: list.php');
    exit;

} catch (PDOException $e) {
    error_log('Database error: ' . $e->getMessage());
    $_SESSION['error_message'] = 'Failed to delete message. Please try again.';
    header('Location: list.php');
    exit;
}
