<?php
/**
 * Animal list (admin).
 *
 * Displays all animals with their species, breed, and foster carer
 * (where assigned). Unfiltered for now - search/filter to be added
 * as a follow-up pass once this base query/display is confirmed working.
 */

session_start();
require_once __DIR__ . '/../auth/authentication.php';

/** @var PDO $pdo */
require_once __DIR__ . '/../connection.php';

// breed_id is NOT NULL on every animal, so an inner join is appropriate
// foster_carer_id is nullable, so that uses a LEFT JOIN
$stmt = $pdo->query(
    "SELECT a.*, s.species_name, b.breed_name,
            fc.first_name AS foster_first_name, fc.last_name AS foster_last_name
     FROM animals a
     JOIN breeds b ON a.breed_id = b.breed_id
     JOIN species s ON b.species_id = s.species_id
     LEFT JOIN foster_carers fc ON a.foster_carer_id = fc.foster_carer_id
     ORDER BY a.name"
);
$animals = $stmt->fetchAll();

// Maps the ENUM's internal snake_case values to the display wording from the brief
$statusLabels = [
    'in_care'   => 'In care',
    'available' => 'Available for adoption',
    'pending'   => 'Adoption pending',
    'adopted'   => 'Adopted',
];
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Animals - SafePaws Admin</title>
    <!-- Link your external CSS file here -->
    <link rel="stylesheet" href="style.css">
</head>
<body>

<!-- Your visible website content goes here -->

<!-- Link your external JavaScript file here -->
<script src="script.js"></script>
</body>
</html>
