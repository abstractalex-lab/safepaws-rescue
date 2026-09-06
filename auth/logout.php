<?php
/**
 * Logout page.
 *
 * Destroys the current session entirely and redirects back to the homepage.
 */

session_start();

$_SESSION = [];
session_destroy();

header("Location: ../index.php");
exit;