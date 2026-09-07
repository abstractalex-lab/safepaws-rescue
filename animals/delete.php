<?php
/**
 * Delete animal (admin).
 *
 * GET shows a confirmation page with the animal's details; the actual
 * delete only runs on POST, so a stray GET (prefetch, pasted URL,
 * crawler) can't destroy a record.
 *
 * adoption_applications.animal_id is ON DELETE RESTRICT, so deleting an
 * animal that has applications on record will fail at the database level.
 * That's deliberate - application history shouldn't disappear with the
 * animal - so the constraint violation is caught and reported in plain
 * language rather than surfacing as a raw SQL error.
 */

session_start();
require_once __DIR__ . '/../auth/authentication.php';

/** @var PDO $pdo */
require_once __DIR__ . '/../connection.php';

$statusLabels = [
    'in_care'   => 'In care',
    'available' => 'Available for adoption',
    'pending'   => 'Adoption pending',
    'adopted'   => 'Adopted',
];

$animalId = $_GET['id'] ?? $_POST['animal_id'] ?? '';

if (!ctype_digit((string) $animalId)) {
    header("Location: index.php?error=invalid");
    exit;
}

$stmt = $pdo->prepare(
    "SELECT a.*, s.species_name, b.breed_name
     FROM animals a
     JOIN breeds b ON a.breed_id = b.breed_id
     JOIN species s ON b.species_id = s.species_id
     WHERE a.animal_id = ?"
);
$stmt->execute([$animalId]);
$animal = $stmt->fetch();

if (!$animal) {
    header("Location: index.php?error=notfound");
    exit;
}

// Shown on the confirmation page so the user knows up front that this
// animal can't be deleted, rather than finding out after clicking.
$appStmt = $pdo->prepare("SELECT COUNT(*) FROM adoption_applications WHERE animal_id = ?");
$appStmt->execute([$animalId]);
$applicationCount = (int) $appStmt->fetchColumn();

$error = null;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        $delete = $pdo->prepare("DELETE FROM animals WHERE animal_id = ?");
        $delete->execute([$animalId]);

        // The row is gone, so its image is now unreferenced. File deletion
        // can't be rolled back, so it deliberately happens only after delete succeeds
        if ($animal['profile_image']) {
            $imagePath = __DIR__ . '/../' . $animal['profile_image'];
            if (is_file($imagePath)) {
                unlink($imagePath);
            }
        }

        header("Location: index.php?deleted=1");
        exit;
    } catch (PDOException $e) {
        // SQLSTATE 23000 is an integrity constraint violation - here that
        // means the RESTRICT foreign key on adoption_applications.
        if ($e->getCode() === '23000') {
            $error = "This animal cannot be deleted because it has adoption applications on record. "
                . "Remove those applications first if the animal really needs to be deleted.";
        } else {
            $error = "Something went wrong while deleting this animal. Please try again.";
        }
        error_log("Delete animal failed: " . $e->getMessage());
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Delete Animal - SafePaws Admin</title>
</head>
<body>
<h1>Delete Animal</h1>
<br>

<?php if ($error): ?>
    <p style="color:red;"><?= htmlentities($error) ?></p>
    <br>
<?php endif; ?>

<p>Are you sure you want to delete this animal? This cannot be undone.</p>
<br>

<table border="1" cellpadding="6">
    <tr>
        <th>Name</th>
        <td><?= htmlentities($animal['name']) ?></td>
    </tr>
    <tr>
        <th>Species</th>
        <td><?= htmlentities($animal['species_name']) ?></td>
    </tr>
    <tr>
        <th>Breed</th>
        <td><?= htmlentities($animal['breed_name']) ?></td>
    </tr>
    <tr>
        <th>Status</th>
        <td><?= htmlentities($statusLabels[$animal['status']] ?? $animal['status']) ?></td>
    </tr>
    <tr>
        <th>Date Admitted</th>
        <td><?= htmlentities($animal['date_admitted']) ?></td>
    </tr>
</table>
<br>

<?php if ($animal['profile_image']): ?>
    <p>The profile image below will also be permanently deleted.</p>
    <img src="../<?= htmlentities($animal['profile_image']) ?>" alt="<?= htmlentities($animal['name']) ?>" style="max-width:200px;">
    <br><br>
<?php endif; ?>

<?php if ($applicationCount > 0): ?>
    <p style="color:red;">
        This animal has <?= $applicationCount ?> adoption application<?= $applicationCount === 1 ? '' : 's' ?>
        on record and cannot be deleted while those exist.
    </p>
    <br>
    <p><a href="index.php">Back to Animal List</a></p>
<?php else: ?>
    <form method="post" action="delete.php">
        <input type="hidden" name="animal_id" value="<?= htmlentities($animalId) ?>">
        <button type="submit">Yes, delete this animal</button>
    </form>
    <br>
    <p><a href="index.php">Cancel and go back</a></p>
<?php endif; ?>

</body>
</html>