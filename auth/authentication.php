<?php
/**
 * Authentication guard.
 *
 * Include this file (require_once) at the very top of any page that requires login, active admin user.
 * Must be run before any other output (refer to Week 6 for details). Call session_start() before requiring this file.
 *
 * Behavior:
 * - No user_id in session at all -> redirect to login.
 * - user_id present but no matching active user in the DB (deleted, or deactivated mid-session) -> destroy session,
 *   redirect to login.
 * - user_id present and matches an active user -> do nothing, let the including page continue executing normally.
 *
 */

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

/** @var PDO $pdo */
require_once __DIR__ . '/../connection.php';

// Absolute site-root path, independent of how deep the including page sits
// Normalize to forward slashes first - on Windows, DOCUMENT_ROOT and __DIR__
// often use different slash directions, which breaks a literal str_replace.
$documentRoot = str_replace('\\', '/', $_SERVER['DOCUMENT_ROOT']);
$currentDir   = str_replace('\\', '/', __DIR__);

$loginUrl = '/' . trim(str_replace($documentRoot, '', $currentDir), '/') . '/login.php';

$authenticated = false;

// Skip the DB check entirely if no user_id was ever set in session
if (isset($_SESSION['user_id'])) {
    $stmt = $pdo->prepare("SELECT * FROM users WHERE user_id = :id AND status = 'active'");
    $stmt->execute(['id' => $_SESSION['user_id']]);

    // Confirms a live, active match exists right now - not just that the session var is present
    if ($stmt->rowCount() === 1) {
        $authenticated = true;
    }
}

if (!$authenticated) {
    // Covers both "never logged in" and "session invalid/stale" -
    // destroy whatever session state exists either way before redirecting
    $_SESSION = [];
    session_destroy();

    header("Location: $loginUrl");
    exit;
}