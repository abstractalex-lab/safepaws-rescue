<?php
// Include necessary files for database connection and helper functions
require_once __DIR__ . '/../connection.php';
require_once __DIR__ . '/../includes/animal_helpers.php';

$animalId = $_GET['animal_id'] ?? $_POST['animal_id'] ?? '';

if (!ctype_digit((string) $animalId)) {
    header("Location: animals.php");
    exit;
}

// Only available animals can be applied for
$stmt = $pdo->prepare(
    "SELECT a.animal_id, a.name, a.profile_image, s.species_name, b.breed_name
     FROM animals a
     JOIN breeds b ON a.breed_id = b.breed_id
     JOIN species s ON b.species_id = s.species_id
     WHERE a.animal_id = ? AND a.status = 'available'"
);
$stmt->execute([$animalId]);
$animal = $stmt->fetch();

if (!$animal) {
    header("Location: animals.php?notfound=1");
    exit;
}

// Housing options offered on the form, validated against this list on submit
$housingTypes = ['House', 'Apartment or unit', 'Townhouse', 'Acreage', 'Other'];

$ownershipTypes = [
    'own'   => 'I own my home',
    'rent'  => 'I rent my home',
    'other' => 'Other arrangement',
];

$errors = [];
$submitted = false;
$old = $_POST;



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

