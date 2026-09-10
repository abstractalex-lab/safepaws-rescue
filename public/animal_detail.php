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

// Include necessary files for database connection and helper functions
require_once __DIR__ . '/../connection.php';
require_once __DIR__ . '/../includes/animal_helpers.php';

$animalId = $_GET['id'] ?? '';

if (!ctype_digit((string) $animalId)) {
    header("Location: animals.php");
    exit;
}

// Explicit column list rather than a.*, prevents leaking internal fields out of the result
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

// Computed from DOB each time the page loads
$age = animalAge($animal['date_of_birth']);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($animal['name']) ?> - SafePaws Rescue</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
</head>
<body>
<?php include __DIR__ . '/../includes/navbar.php'; ?>

<div class="container mt-4">

    <nav aria-label="breadcrumb">
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="animals.php">Animals for Adoption</a></li>
            <li class="breadcrumb-item active"><?= htmlspecialchars($animal['name']) ?></li>
        </ol>
    </nav>

    <div class="row g-4">
        <div class="col-md-5">
            <div class="card">
                <?php if ($animal['profile_image']): ?>
                    <!-- Clicking the image opens it full-size in a modal -->
                    <img src="../<?= htmlspecialchars($animal['profile_image']) ?>" class="card-img-top"
                         alt="<?= htmlspecialchars($animal['name']) ?>"
                         style="height:320px; object-fit:cover; cursor:pointer;"
                         data-bs-toggle="modal" data-bs-target="#imageModal">
                <?php else: ?>
                    <div class="card-body text-center text-muted py-5">
                        <i class="bi bi-image fs-1"></i>
                        <p class="mb-0 mt-2">No photo available yet</p>
                    </div>
                <?php endif; ?>
            </div>
        </div>

        <div class="col-md-7">
            <h2 class="mb-1"><?= htmlspecialchars($animal['name']) ?></h2>
            <p class="text-muted">
                <span class="badge bg-success">Available for adoption</span>
            </p>

            <table class="table table-sm">
                <tr>
                    <th style="width:40%;">Species</th>
                    <td><?= htmlspecialchars($animal['species_name']) ?></td>
                </tr>
                <tr>
                    <th>Breed</th>
                    <td><?= htmlspecialchars($animal['breed_name']) ?></td>
                </tr>
                <tr>
                    <th>Sex</th>
                    <td><?= htmlspecialchars(ucfirst($animal['sex'])) ?></td>
                </tr>
                <tr>
                    <th>Desexed</th>
                    <td><?= $animal['desexed'] ? 'Yes' : 'No' ?></td>
                </tr>
                <tr>
                    <th>Age</th>
                    <td>
                        <?php if ($age): ?>
                            <?= htmlspecialchars($age) ?>
                        <?php else: ?>
                            <span class="text-muted">Unknown</span>
                        <?php endif; ?>
                    </td>
                </tr>
                <tr>
                    <th>In our care since</th>
                    <td><?= htmlspecialchars($animal['date_admitted']) ?></td>
                </tr>
            </table>
            <a href="apply.php?animal_id=<?= $animal['animal_id'] ?>" class="btn btn-primary btn-lg">
                <i class="bi bi-heart"></i> Apply to adopt <?= htmlspecialchars($animal['name']) ?>
            </a>
        </div>
    </div>

    <?php if ($animal['description']): ?>
        <div class="card mt-4">
            <div class="card-header">
                <i class="bi bi-card-text"></i> About <?= htmlspecialchars($animal['name']) ?>
            </div>
            <div class="card-body">
                <?= nl2br(htmlspecialchars($animal['description'])) ?>
            </div>
        </div>
    <?php endif; ?>

    <div class="mt-4 mb-5">
        <a href="animals.php" class="btn btn-outline-secondary">
            <i class="bi bi-arrow-left"></i> Back to All Animals
        </a>
    </div>

    <?php if ($animal['profile_image']): ?>
        <!-- Full-size photo, opened from the image above -->
        <div class="modal fade" id="imageModal" tabindex="-1" aria-hidden="true">
            <div class="modal-dialog modal-lg modal-dialog-centered">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title"><?= htmlspecialchars($animal['name']) ?></h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body text-center">
                        <img src="../<?= htmlspecialchars($animal['profile_image']) ?>"
                             class="img-fluid" alt="<?= htmlspecialchars($animal['name']) ?>">
                    </div>
                </div>
            </div>
        </div>
    <?php endif; ?>

</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
