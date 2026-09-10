<?php
/**
 * Delete foster carer (admin).
 *
 * Action script with no output of its own. Performs the delete and redirects back to the list with a flash message.
 *
 * Requires POST with a valid CSRF token, so a stray GET (prefetch, pasted URL, crawler) can't destroy a record.
 *
 * animals.foster_carer_id is ON DELETE SET NULL, so deleting a carer never fails on a constraint.
 * It unassigns any animals currently placed with them. The success message reports how many, since
 * that side effect would otherwise be invisible.
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

// Validate and sanitize the foster carer ID
$foster_carer_id = isset($_POST['id']) ? (int)$_POST['id'] : 0;
if ($foster_carer_id <= 0) {
    $_SESSION['error_message'] = 'Invalid foster carer ID.';
    header('Location: list.php');
    exit();
}

// Fetch the name and the number of animals currently placed with this carer
try {
    $stmt = $pdo->prepare("SELECT first_name, last_name FROM foster_carers WHERE foster_carer_id = :id");
    $stmt->execute([':id' => $foster_carer_id]);
    $carer = $stmt->fetch();

    if (!$carer) {
        $_SESSION['error_message'] = 'Foster carer not found.';
        header('Location: list.php');
        exit();
    }

    // Count how many animals are currently assigned to this foster carer
    $countStmt = $pdo->prepare("SELECT COUNT(*) FROM animals WHERE foster_carer_id = :id");
    $countStmt->execute([':id' => $foster_carer_id]);
    $assigned_animals = (int) $countStmt->fetchColumn();

} catch (PDOException $e) {
    error_log('Database error: ' . $e->getMessage());
    $_SESSION['error_message'] = 'Failed to load foster carer details.';
    header('Location: list.php');
    exit();
}

// Delete the foster carer
try {
    $stmt = $pdo->prepare("DELETE FROM foster_carers WHERE foster_carer_id = :id");
    $stmt->execute([':id' => $foster_carer_id]);

    $message = "Foster carer '{$carer['first_name']} {$carer['last_name']}' has been deleted successfully!";

    // The SET NULL cascade already ran, so say what it did
    if ($assigned_animals > 0) {
        $message .= " {$assigned_animals} animal" . ($assigned_animals === 1 ? ' is' : 's are')
            . " now unassigned and will need a new placement.";
    }

    $_SESSION['success_message'] = $message;
    header('Location: list.php');
    exit;

} catch (PDOException $e) {
    error_log('Database error: ' . $e->getMessage());
    $_SESSION['error_message'] = "Failed to delete foster carer. Please try again.";
    header('Location: list.php');
    exit;
}