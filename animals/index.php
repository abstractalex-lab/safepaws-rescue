<?php
/**
 * Animal list (admin).
 *
 * Displays all animals with their species, breed, and foster carer (where assigned).
 * Unfiltered for now - search/filter to be added as a follow-up pass once this base query/display is confirmed working.
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
<h1>Animals</h1>
<br>

<p><a href="add.php">Add New Animal</a></p>
<br>

<?php if (count($animals) === 0): ?>
    <p>No animals found.</p>
<?php else: ?>
    <table border="1" cellpadding="6">
        <tr>
            <th>Name</th>
            <th>Species</th>
            <th>Breed</th>
            <th>Sex</th>
            <th>Status</th>
            <th>Foster Carer</th>
            <th>Actions</th>
        </tr>
        <?php foreach ($animals as $animal): ?>
            <tr>
                <td><?= htmlentities($animal['name']) ?></td>
                <td><?= htmlentities($animal['species_name']) ?></td>
                <td><?= htmlentities($animal['breed_name']) ?></td>
                <td><?= htmlentities(ucfirst($animal['sex'])) ?></td>
                <td><?= htmlentities($statusLabels[$animal['status']] ?? $animal['status']) ?></td>
                <td>
                    <?php if ($animal['foster_first_name']): ?>
                        <?= htmlentities($animal['foster_first_name'] . ' ' . $animal['foster_last_name']) ?>
                    <?php else: ?>
                        <em>Not fostered</em>
                    <?php endif; ?>
                </td>
                <td>
                    <a href="view.php?id=<?= $animal['animal_id'] ?>">View</a> |
                    <a href="edit.php?id=<?= $animal['animal_id'] ?>">Edit</a> |
                    <a href="delete.php?id=<?= $animal['animal_id'] ?>">Delete</a>
                </td>
            </tr>
        <?php endforeach; ?>
    </table>
<?php endif; ?>

</body>
</html>
