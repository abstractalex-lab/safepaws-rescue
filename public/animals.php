<?php
/**
 * Public animal listing.
 *
 * Does NOT include authentication.php, publicly available and accessible to view page.
 *
 * Only animals with status 'available' are listed. Internal fields (medical notes, foster carer details)
 * are never selected here, so they can't leak into the public page by accident.
 *
 * @var PDO $pdo
 */

require_once __DIR__ . '/../connection.php';

// Explicit column list rather than a.*, prevents leaking internal fields out of the result
$stmt = $pdo->query(
        "SELECT a.animal_id, a.name, a.sex, a.date_of_birth, a.description, a.profile_image,
            s.species_name, b.breed_name
     FROM animals a
     JOIN breeds b ON a.breed_id = b.breed_id
     JOIN species s ON b.species_id = s.species_id
     WHERE a.status = 'available'
     ORDER BY a.name"
);
$animals = $stmt->fetchAll();

/**
 * Rough age from a date of birth, or null when none is recorded.
 */
function animalAge(?string $dateOfBirth): ?string
{
    if (!$dateOfBirth) {
        return null;
    }

    $diff = (new DateTime($dateOfBirth))->diff(new DateTime());

    if ($diff->y > 0) {
        return $diff->y . ' year' . ($diff->y === 1 ? '' : 's');
    }

    return $diff->m . ' month' . ($diff->m === 1 ? '' : 's');
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Animals for Adoption - SafePaws Rescue</title>
</head>
<body>
<h1>Animals Looking for a Home</h1>
<br>

<?php if (count($animals) === 0): ?>
    <p>There are no animals available for adoption at the moment. Please check back soon.</p>
<?php else: ?>
    <?php foreach ($animals as $animal): ?>
        <div style="border:1px solid #ccc; padding:10px; margin-bottom:10px; max-width:600px;">
            <?php if ($animal['profile_image']): ?>
                <img src="../<?= htmlentities($animal['profile_image']) ?>"
                     alt="<?= htmlentities($animal['name']) ?>" style="max-width:200px;">
                <br>
            <?php endif; ?>

            <h2><?= htmlentities($animal['name']) ?></h2>
            <p>
                <?= htmlentities($animal['species_name']) ?> &middot;
                <?= htmlentities($animal['breed_name']) ?> &middot;
                <?= htmlentities(ucfirst($animal['sex'])) ?>
                <?php $age = animalAge($animal['date_of_birth']); ?>
                <?php if ($age): ?>
                    &middot; <?= htmlentities($age) ?>
                <?php endif; ?>
            </p>

            <?php if ($animal['description']): ?>
                <p><?= nl2br(htmlentities($animal['description'])) ?></p>
            <?php endif; ?>

            <p><a href="animal_detail.php?id=<?= $animal['animal_id'] ?>">Find out more</a></p>
        </div>
    <?php endforeach; ?>
<?php endif; ?>

<br>
<p><a href="../index.php">Back to Home</a></p>

</body>
</html>