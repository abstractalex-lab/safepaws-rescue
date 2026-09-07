<?php
// connection.php - New file
// Single point of DB connection (spec requirement).
// Reads credentials from config.local.php, which is NOT committed to git -
// each teammate creates their own copy from config.local.example.php.

$configPath = __DIR__ . '/config.local.php';

if (!file_exists($configPath)) {
    // Missing local config shouldn't leak a raw fatal error to anyone
    // running the app (marker, teammate on first clone, etc.)
    die(
        "Database configuration file not found. " .
        "Copy config.local.example.php to config.local.php and fill in your local credentials."
    );
}

require_once $configPath;

try {
    $pdo = new PDO(
        "mysql:host=" . DB_HOSTNAME . ";dbname=" . DB_NAME . ";charset=utf8mb4",
        DB_USERNAME,
        DB_PASSWORD,
        [
            PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        ]
    );
} catch (PDOException $e) {
    // Never expose raw PDO/SQL errors or credentials to the user
    error_log("DB connection failed: " . $e->getMessage()); // server-side log only
    die("Unable to connect to the database. Please contact the site administrator.");
}

// $pdo is defined globally
if (!isset($pdo)) {
    die("Database connection could not be established.");
}