<?php
// foster_carers/toggle_status.php
require_once __DIR__ . '/../auth/authentication.php';
require_once __DIR__ . '/../connection.php';
require_once __DIR__ . '/../includes/csrf.php';
/** @var PDO $pdo */

if ($_SERVER['REQUEST_METHOD'] !== 'POST' || !csrf_verify($_POST['csrf_token'] ?? null)) {
    header('Location: list.php');
    exit();
}

$foster_carer_id = isset($_POST['id']) ? (int)$_POST['id'] : 0;

if ($foster_carer_id <= 0) {
    $_SESSION['error_message'] = 'Invalid foster carer ID.';
    header('Location: list.php');
    exit();
}

try {
    $stmt = $pdo->prepare("SELECT status, first_name, last_name FROM foster_carers WHERE foster_carer_id = :id");
    $stmt->execute([':id' => $foster_carer_id]);
    $carer = $stmt->fetch();

    if (!$carer) {
        $_SESSION['error_message'] = 'Foster carer not found.';
        header('Location: list.php');
        exit();
    }

    $new_status = ($carer['status'] == 'active') ? 'inactive' : 'active';

    $update_stmt = $pdo->prepare("UPDATE foster_carers SET status = :status WHERE foster_carer_id = :id");
    $update_stmt->execute([
        ':status' => $new_status,
        ':id' => $foster_carer_id
    ]);

    $_SESSION['success_message'] = "Foster carer '{$carer['first_name']} {$carer['last_name']}' has been " .
        ($new_status == 'active' ? 'activated' : 'deactivated') . "!";
    header('Location: list.php');
    exit;

} catch (PDOException $e) {
    error_log('Database error: ' . $e->getMessage());
    $_SESSION['error_message'] = 'Failed to toggle status. Please try again.';
    header('Location: list.php');
    exit;
}
