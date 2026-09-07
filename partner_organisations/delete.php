<?php
// partner_organisations/delete.php
require_once __DIR__ . '/../auth/authentication.php';
require_once __DIR__ . '/../connection.php';
/** @var PDO $pdo */

$organisation_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

if ($organisation_id <= 0) {
    header('Location: list.php');
    exit();
}

// check if organisation exists and get name for message
try {
    $stmt = $pdo->prepare("SELECT name FROM partner_organisations WHERE organisation_id = :id");
    $stmt->execute([':id' => $organisation_id]);
    $organisation = $stmt->fetch();

    if (!$organisation) {
        $_SESSION['error_message'] = 'Organisation not found.';
        header('Location: list.php');
        exit();
    }
} catch (PDOException $e) {
    error_log('Database error: ' . $e->getMessage());
    $_SESSION['error_message'] = 'Failed to load organisation details.';
    header('Location: list.php');
    exit();
}

// perform deletion
try {
    $stmt = $pdo->prepare("DELETE FROM partner_organisations WHERE organisation_id = :id");
    $stmt->execute([':id' => $organisation_id]);

    $_SESSION['success_message'] = "Organisation '{$organisation['name']}' has been deleted successfully!";
    header('Location: list.php');
    exit;

} catch (PDOException $e) {
    // check if it's a foreign key constraint error (MySQL error code 1451)
    if ($e->errorInfo[1] == 1451) {
        $_SESSION['error_message'] = "Cannot delete this organisation because it has associated records. Please remove those first.";
    } else {
        $_SESSION['error_message'] = "Failed to delete organisation. Please try again.";
        error_log('Database error: ' . $e->getMessage());
    }
    header('Location: list.php');
    exit;
}
?>
