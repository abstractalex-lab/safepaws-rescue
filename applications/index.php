<?php
/**
 * Adoption applications list (admin).
 *
 * Shows every application with the animal it relates to and its current status.
 * Searching and column sorting are handled client-side by DataTables.
 *
 * @var PDO $pdo
 */

// Start session and include necessary files
session_start();
require_once __DIR__ . '/../auth/authentication.php';
require_once __DIR__ . '/../connection.php';
require_once __DIR__ . '/../includes/application_helpers.php';

// animal_id is NOT NULL, so an inner join is appropriate here
$stmt = $pdo->query(
    "SELECT ap.*, a.name AS animal_name, s.species_name
     FROM adoption_applications ap
     JOIN animals a ON ap.animal_id = a.animal_id
     JOIN breeds b ON a.breed_id = b.breed_id
     JOIN species s ON b.species_id = s.species_id
     ORDER BY ap.application_date DESC"
);
$applications = $stmt->fetchAll();

// Counts for the summary cards, derived from the rows already fetched
$statusCounts = array_fill_keys(array_keys($applicationStatusLabels), 0);
foreach ($applications as $application) {
    if (isset($statusCounts[$application['status']])) {
        $statusCounts[$application['status']]++;
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Document</title>
    <!-- Link external CSS stylesheet here -->
    <link rel="stylesheet" href="style.css">
</head>
<body>

<!-- Visible web content goes here -->

<!-- Link external JavaScript file here -->
<script src="script.js"></script>
</body>
</html>

