<?php
/**
 * Public animal detail.
 *
 * Does NOT include authentication.php, publicly available and accessible to view page.
 *
 * Restricted to animals with status 'available'. Requesting the id of an animal in any other state returns the same
 * "not found" redirect as a non-existent id, so the page can't be used to confirm which animals exist internally.
 *
 * @var PDO $pdo
 */

require_once __DIR__ . '/../connection.php';

$animalId = $_GET['id'] ?? '';

if (!ctype_digit((string) $animalId)) {
    header("Location: animals.php");
    exit;
}

// Explicit column list prevents leaking internal fields out of the result
// The status filter is part of the WHERE clause rather than a check after
// the fetch, so a non-available animal is indistinguishable from one that
// doesn't exist.
$stmt = $pdo->prepare(
    "SELECT a.animal_id, a.name, a.sex, a.desexed, a.date_of_birth, a.date_admitted,
            a.description, a.profile_image,
            s.species_name, b.breed_name
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

$age = null;
if ($animal['date_of_birth']) {
    try {
        $diff = (new DateTime($animal['date_of_birth']))->diff(new DateTime());
    } catch (Exception $e) {
        // Handle the exception if needed
    }
    $age = $diff->y > 0
        ? $diff->y . ' year' . ($diff->y === 1 ? '' : 's')
        : $diff->m . ' month' . ($diff->m === 1 ? '' : 's');
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlentities($animal['name']) ?> - SafePaws Rescue</title>
</head>
<body>
<h1><?= htmlentities($animal['name']) ?></h1>
<br>

<?php if ($animal['profile_image']): ?>
    <img src="../<?= htmlentities($animal['profile_image']) ?>"
         alt="<?= htmlentities($animal['name']) ?>" style="max-width:300px;">
    <br><br>
<?php endif; ?>

<table border="1" cellpadding="6">
    <tr>
        <th>Species</th>
        <td><?= htmlentities($animal['species_name']) ?></td>
    </tr>
    <tr>
        <th>Breed</th>
        <td><?= htmlentities($animal['breed_name']) ?></td>
    </tr>
    <tr>
        <th>Sex</th>
        <td><?= htmlentities(ucfirst($animal['sex'])) ?></td>
    </tr>
    <tr>
        <th>Desexed</th>
        <td><?= $animal['desexed'] ? 'Yes' : 'No' ?></td>
    </tr>
    <tr>
        <th>Age</th>
        <td><?= $age ? htmlentities($age) : '<em>Unknown</em>' ?></td>
    </tr>
    <tr>
        <th>In our care since</th>
        <td><?= htmlentities($animal['date_admitted']) ?></td>
    </tr>
</table>
<br>

<?php if ($animal['description']): ?>
    <h2>About <?= htmlentities($animal['name']) ?></h2>
    <p><?= nl2br(htmlentities($animal['description'])) ?></p>
    <br>
<?php endif; ?>

<?php
/*
 * TODO: link to the adoption application form once that module is built.
 * The form is publicly accessible and takes the animal id, e.g.
 *   <a href="../applications/apply.php?animal_id=<?= $animal['animal_id'] ?>">
 *       Apply to adopt <?= htmlentities($animal['name']) ?>
 *   </a>
 * Ownership of the applications module is still to be assigned.
 */
?>
<p><em>Adoption applications coming soon.</em></p>
<br>

<p><a href="animals.php">Back to All Animals</a></p>

</body>
</html>