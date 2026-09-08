<?php
/**
 * Animal detail (admin).
 *
 * Read-only view of a single animal, shows information about the animal, its breed and species, and any
 * foster carer assigned to it. Also shows a count of adoption applications on record for this animal.
 *
 * Age is computed from date_of_birth, and animals with no recorded date of birth show "Unknown" instead.
 *
 * @var PDO $pdo
 * @var array $statusLabels
 */

// Start session and include necessary files
session_start();
require_once __DIR__ . '/../auth/authentication.php';
require_once __DIR__ . '/../connection.php';
require_once __DIR__ . '/../includes/animal_helpers.php';

// Get the animal ID from the query string and validate it as a positive integer
$animalId = $_GET['id'] ?? '';
if (!ctype_digit((string) $animalId)) {
    header("Location: index.php?error=invalid");
    exit;
}

// breed_id is NOT NULL on every animal, so an inner join is appropriate
// foster_carer_id is nullable, so that uses a LEFT JOIN
$stmt = $pdo->prepare(
    "SELECT a.*, s.species_name, b.breed_name,
            fc.first_name AS foster_first_name, fc.last_name AS foster_last_name,
            fc.email AS foster_email, fc.phone AS foster_phone, fc.status AS foster_status
     FROM animals a
     JOIN breeds b ON a.breed_id = b.breed_id
     JOIN species s ON b.species_id = s.species_id
     LEFT JOIN foster_carers fc ON a.foster_carer_id = fc.foster_carer_id
     WHERE a.animal_id = ?"
);
$stmt->execute([$animalId]);
$animal = $stmt->fetch();

if (!$animal) {
    header("Location: index.php?error=notfound");
    exit;
}

// Applications are shown as a count only
$appStmt = $pdo->prepare("SELECT COUNT(*) FROM adoption_applications WHERE animal_id = ?");
$appStmt->execute([$animalId]);
$applicationCount = (int) $appStmt->fetchColumn();

// Computed from DOB each time the page loads
$age = null;
if ($animal['date_of_birth']) {
    try {
        $dob = new DateTime($animal['date_of_birth']);
    } catch (Exception $e) {
        $dob = null;
    }
    $diff = $dob->diff(new DateTime());
    if ($diff->y > 0) {
        $age = $diff->y . ' year' . ($diff->y === 1 ? '' : 's');
    } else {
        $age = $diff->m . ' month' . ($diff->m === 1 ? '' : 's');
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlentities($animal['name']) ?> - SafePaws Admin</title>
</head>
<body>
<h1><?= htmlentities($animal['name']) ?></h1>
<br>

<?php if ($animal['profile_image']): ?>
    <img src="../<?= htmlentities($animal['profile_image']) ?>" alt="<?= htmlentities($animal['name']) ?>" style="max-width:300px;">
<?php else: ?>
    <em>No profile image uploaded.</em>
<?php endif; ?>
<br><br>

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
        <th>Date of Birth</th>
        <td>
            <?php if ($animal['date_of_birth']): ?>
                <?= htmlentities($animal['date_of_birth']) ?> (<?= htmlentities($age) ?>)
            <?php else: ?>
                <em>Unknown</em>
            <?php endif; ?>
        </td>
    </tr>
    <tr>
        <th>Date Admitted</th>
        <td><?= htmlentities($animal['date_admitted']) ?></td>
    </tr>
    <tr>
        <th>Status</th>
        <td><?= htmlentities($statusLabels[$animal['status']] ?? $animal['status']) ?></td>
    </tr>
    <tr>
        <th>Foster Carer</th>
        <td>
            <?php if ($animal['foster_first_name']): ?>
                <?= htmlentities($animal['foster_first_name'] . ' ' . $animal['foster_last_name']) ?>
                <?= $animal['foster_status'] !== 'active' ? ' (inactive)' : '' ?>
                <br>
                <?= htmlentities($animal['foster_email']) ?> | <?= htmlentities($animal['foster_phone']) ?>
            <?php else: ?>
                <em>Not fostered</em>
            <?php endif; ?>
        </td>
    </tr>
    <tr>
        <th>Description</th>
        <td>
            <?php if ($animal['description']): ?>
                <?= nl2br(htmlentities($animal['description'])) ?>
            <?php else: ?>
                <em>None recorded</em>
            <?php endif; ?>
        </td>
    </tr>
    <tr>
        <th>Medical Notes</th>
        <td>
            <?php if ($animal['medical_notes']): ?>
                <?= nl2br(htmlentities($animal['medical_notes'])) ?>
            <?php else: ?>
                <em>None recorded</em>
            <?php endif; ?>
        </td>
    </tr>
    <tr>
        <th>Adoption Applications</th>
        <td><?= $applicationCount ?></td>
    </tr>
</table>
<br>

<p>
    <a href="edit.php?id=<?= $animal['animal_id'] ?>">Edit</a> |
    <a href="delete.php?id=<?= $animal['animal_id'] ?>">Delete</a>
</p>
<br>

<p><a href="index.php">Back to Animal List</a></p>

</body>
</html>