<?php
/**
 * Logout page.
 *
 * Destroys the current session entirely and redirects back to the homepage.
 */

// Start the session to access session variables
session_start();

// Clear all session variables and destroy the session
$_SESSION = [];
session_destroy();

// Redirect the user to the homepage after logout
header("Location: ../index.php");
exit;