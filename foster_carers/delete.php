<?php
// foster_carers/delete.php
require_once __DIR__ . '/../auth/authentication.php';
require_once __DIR__ . '/../connection.php';
/** @var PDO $pdo */

$foster_carer_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

if ($foster_carer_id <= 0) {
    $_SESSION['error_message'] = 'Invalid foster carer ID.';
    header('Location: list.php');
    exit();
}

try {
    $stmt = $pdo->prepare("SELECT first_name, last_name FROM foster_carers WHERE foster_carer_id = :id");
    $stmt->execute([':id' => $foster_carer_id]);
    $carer = $stmt->fetch();

    if (!$carer) {
        $_SESSION['error_message'] = 'Foster carer not found.';
        header('Location: list.php');
        exit();
    }
} catch (PDOException $e) {
    error_log('Database error: ' . $e->getMessage());
    $_SESSION['error_message'] = 'Failed to load foster carer details.';
    header('Location: list.php');
    exit();
}

try {
    $stmt = $pdo->prepare("DELETE FROM foster_carers WHERE foster_carer_id = :id");
    $stmt->execute([':id' => $foster_carer_id]);

    $_SESSION['success_message'] = "Foster carer '{$carer['first_name']} {$carer['last_name']}' has been deleted successfully!";
    header('Location: list.php');
    exit;

} catch (PDOException $e) {
    if ($e->errorInfo[1] == 1451) {
        $_SESSION['error_message'] = "Cannot delete this foster carer because they have associated animals. Please reassign those first.";
    } else {
        $_SESSION['error_message'] = "Failed to delete foster carer. Please try again.";
        error_log('Database error: ' . $e->getMessage());
    }
    header('Location: list.php');
    exit;
}
?>
