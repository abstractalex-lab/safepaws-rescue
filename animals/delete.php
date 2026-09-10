<?php
/**
 * Delete animal (admin).
 *
 * GET shows a confirmation page with the animal's details, after that run delete via POST,
 * prevents a stray GET (prefetch, pasted URL, crawler) can't destroy a record.
 *
 * adoption_applications.animal_id is ON DELETE RESTRICT, so deleting an
 * animal that has applications on record will fail at the database level.
 * That's deliberate - application history shouldn't disappear with the
 * animal - so the constraint violation is caught and reported in plain
 * language rather than surfacing as a raw SQL error.
 *
 * @var PDO $pdo
 * @var array $statusLabels
 */

// Start session and include necessary files
session_start();
require_once __DIR__ . '/../auth/authentication.php';
require_once __DIR__ . '/../connection.php';
require_once __DIR__ . '/../includes/animal_helpers.php';

// Get the animal ID from either GET or POST, and validate it as a digit string
$animalId = $_GET['id'] ?? $_POST['animal_id'] ?? '';
if (!ctype_digit((string) $animalId)) {
    header("Location: index.php?error=invalid");
    exit;
}

// Fetch the animal's details, including species and breed names, for display on the confirmation page
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

// Shown on the confirmation page so the user knows up front that this animal can't be deleted
$appStmt = $pdo->prepare("SELECT COUNT(*) FROM adoption_applications WHERE animal_id = ?");
$appStmt->execute([$animalId]);
$applicationCount = (int) $appStmt->fetchColumn();

$error = null;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        $delete = $pdo->prepare("DELETE FROM animals WHERE animal_id = ?");
        $delete->execute([$animalId]);

        // Delete image after removed the database record
        if ($animal['profile_image']) {
            $imagePath = __DIR__ . '/../' . $animal['profile_image'];
            if (is_file($imagePath)) {
                unlink($imagePath);
            }
        }

        header("Location: index.php?deleted=1");
        exit;
    } catch (PDOException $e) {
        // SQLSTATE 23000 is an integrity constraint violation; this means the RESTRICT foreign key on adoption_applications prevented the deletion
        // Catch that and show a user-friendly message instead of a raw SQL error
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
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
</head>
<body>
<?php include __DIR__ . '/../includes/navbar.php'; ?>

<div class="container mt-4">
    <div class="row justify-content-center">
        <div class="col-md-7">

            <?php if ($error): ?>
                <div class="alert alert-danger" role="alert">
                    <i class="bi bi-exclamation-octagon"></i> <?= htmlentities($error) ?>
                </div>
            <?php endif; ?>

            <div class="card border-danger">
                <div class="card-header bg-danger text-white">
                    <i class="bi bi-exclamation-triangle"></i> Delete Animal
                </div>
                <div class="card-body">

                    <?php if ($applicationCount > 0): ?>
                        <!-- Blocked by the RESTRICT foreign key, so no delete button is offered -->
                        <div class="alert alert-warning mb-3" role="alert">
                            <i class="bi bi-shield-exclamation"></i>
                            This animal has <?= $applicationCount ?> adoption
                            application<?= $applicationCount === 1 ? '' : 's' ?>
                            on record and cannot be deleted while those exist.
                        </div>
                    <?php else: ?>
                        <p>Are you sure you want to delete this animal? This cannot be undone.</p>
                    <?php endif; ?>

                    <div class="row g-3 align-items-center">
                        <?php if ($animal['profile_image']): ?>
                            <div class="col-4">
                                <img src="../<?= htmlentities($animal['profile_image']) ?>"
                                     class="img-fluid rounded" alt="<?= htmlentities($animal['name']) ?>">
                            </div>
                        <?php endif; ?>
                        <div class="<?= $animal['profile_image'] ? 'col-8' : 'col-12' ?>">
                            <table class="table table-sm mb-0">
                                <tr>
                                    <th style="width:40%;">Name</th>
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
                        </div>
                    </div>

                    <?php if ($animal['profile_image'] && $applicationCount === 0): ?>
                        <p class="text-muted small mt-3 mb-0">
                            <i class="bi bi-info-circle"></i>
                            The profile image shown above will also be permanently deleted.
                        </p>
                    <?php endif; ?>

                </div>
                <div class="card-footer d-flex justify-content-end gap-2">
                    <a href="index.php" class="btn btn-outline-secondary">
                        <i class="bi bi-x-circle"></i> Cancel and go back
                    </a>
                    <?php if ($applicationCount === 0): ?>
                        <form method="post" action="delete.php" class="d-inline">
                            <input type="hidden" name="animal_id" value="<?= htmlentities($animalId) ?>">
                            <button type="submit" class="btn btn-danger">
                                <i class="bi bi-trash"></i> Yes, delete this animal
                            </button>
                        </form>
                    <?php endif; ?>
                </div>
            </div>

        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>