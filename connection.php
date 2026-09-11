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
        '<div style="font-family:sans-serif;max-width:600px;margin:60px auto;padding:20px;'
        . 'border:1px solid #ccc;border-radius:6px;">'
        . '<h2>Setup required</h2>'
        . '<p>The database configuration file was not found.</p>'
        . '<p>Copy <code>config.local.example.php</code> to <code>config.local.php</code> '
        . 'and fill in your local MySQL credentials. The database name is <code>fit2104_a3</code>.</p>'
        . '<p>See <code>README.md</code> for full setup instructions.</p>'
        . '</div>'
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