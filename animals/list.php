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
</head>
<body>
<!-- Your visible website content goes here -->
<h1>Animals</h1>
<br>

<p><a href="add.php">Add New Animal</a></p>
<br>

<?php if (count($animals) === 0): ?>
    <p>No animals found.</p>
<?php else: ?>
<table border="1" cellpadding="6">
    <thead>
        <tr>
            <th>Name</th>
            <th>Species</th>
            <th>Breed</th>
            <th>Status</th>
            <th>Foster Carer</th>
            <th>Actions</th>
        </tr>
    </thead>
    <tbody>
        <?php foreach ($animals as $animal): ?>
            <tr>
                <td><?= htmlspecialchars($animal['name']) ?></td>
                <td><?= htmlspecialchars($animal['species_name']) ?></td>
                <td><?= htmlspecialchars($animal['breed_name']) ?></td>
                <td><?= htmlspecialchars($statusLabels[$animal['status']]) ?></td>
                <td><?= $animal['foster_first_name'] ? htmlspecialchars($animal['foster_first_name'] . ' ' . $animal['foster_last_name']) : 'N/A' ?></td>
                <td><a href="edit.php?id=<?= $animal['animal_id'] ?>">Edit</a></td>
            </tr>
        <?php endforeach; ?>
    </tbody>
</table>
<?php endif; ?>

</body>
</html>
