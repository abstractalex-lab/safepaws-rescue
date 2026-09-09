<?php
// contact/toggle_replied.php - Toggle replied status (login required)
require_once __DIR__ . '/../auth/authentication.php';
require_once __DIR__ . '/../connection.php';
require_once __DIR__ . '/../includes/csrf.php';
/** @var PDO $pdo */

if ($_SERVER['REQUEST_METHOD'] !== 'POST' || !csrf_verify($_POST['csrf_token'] ?? null)) {
    header('Location: list.php');
    exit();
}

$message_id = isset($_POST['id']) ? (int)$_POST['id'] : 0;
$action = $_POST['action'] ?? '';

if ($message_id <= 0) {
    $_SESSION['error_message'] = 'Invalid message ID.';
    header('Location: list.php');
    exit();
}

if (!in_array($action, ['mark_replied', 'mark_unreplied'], true)) {
    $_SESSION['error_message'] = 'Invalid action.';
    header('Location: list.php');
    exit();
}

try {
    $stmt = $pdo->prepare("SELECT name, replied FROM contact_messages WHERE message_id = :id");
    $stmt->execute([':id' => $message_id]);
    $message = $stmt->fetch();

    if (!$message) {
        $_SESSION['error_message'] = 'Message not found.';
        header('Location: list.php');
        exit();
    }

    $new_status = ($action === 'mark_replied') ? 1 : 0;

    $update_stmt = $pdo->prepare("UPDATE contact_messages SET replied = :status WHERE message_id = :id");
    $update_stmt->execute([
        ':status' => $new_status,
        ':id' => $message_id
    ]);

    $status_text = ($new_status) ? 'marked as replied' : 'marked as unreplied';
    $_SESSION['success_message'] = "Message from '{$message['name']}' has been {$status_text}!";
    header('Location: list.php');
    exit;

} catch (PDOException $e) {
    error_log('Database error: ' . $e->getMessage());
    $_SESSION['error_message'] = 'Failed to update message status. Please try again.';
    header('Location: list.php');
    exit;
}
