<?php
/**
 * Delete partner organization (admin).
 *
 * Action script with no output of its own. Performs the delete and redirects back to the list with a flash message.
 *
 * Requires POST with a valid CSRF token, so a stray GET (prefetch, pasted URL, crawler) can't destroy a record.
 *
 * @var PDO $pdo
 */

// Include necessary files
require_once __DIR__ . '/../auth/authentication.php';
require_once __DIR__ . '/../connection.php';
require_once __DIR__ . '/../includes/csrf.php';

// Check for POST request and valid CSRF token
if ($_SERVER['REQUEST_METHOD'] !== 'POST' || !csrf_verify($_POST['csrf_token'] ?? null)) {
    header('Location: list.php');
    exit();
}

// Validate and sanitize the organization ID
$organisation_id = isset($_POST['id']) ? (int)$_POST['id'] : 0;
if ($organisation_id <= 0) {
    $_SESSION['error_message'] = 'Invalid organisation ID.';
    header('Location: list.php');
    exit();
}

// Fetch the name first, so the confirmation message can identify what was deleted
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

// Delete the organization
try {
    $stmt = $pdo->prepare("DELETE FROM partner_organisations WHERE organisation_id = :id");
    $stmt->execute([':id' => $organisation_id]);

    $_SESSION['success_message'] = "Organisation '{$organisation['name']}' has been deleted successfully!";
    header('Location: list.php');
    exit();

} catch (PDOException $e) {
    error_log('Database error: ' . $e->getMessage());
    $_SESSION['error_message'] = "Failed to delete organisation. Please try again.";
    header('Location: list.php');
    exit();
}