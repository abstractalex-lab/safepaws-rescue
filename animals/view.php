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

// Get the animal ID from the query string and validate it as a digit string
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
    $diff = (new DateTime($animal['date_of_birth']))->diff(new DateTime());
    if ($diff->y > 0) {
        $age = $diff->y . ' year' . ($diff->y === 1 ? '' : 's');
    } else {
        $age = $diff->m . ' month' . ($diff->m === 1 ? '' : 's');
    }
}

// Bootstrap badge color per status
$statusBadges = [
        'in_care'   => 'bg-secondary',
        'available' => 'bg-success',
        'pending'   => 'bg-warning text-dark',
        'adopted'   => 'bg-primary',
];
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlentities($animal['name']) ?> - SafePaws Admin</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
</head>
<body>
<?php include __DIR__ . '/../includes/navbar.php'; ?>

<div class="container mt-4">

    <nav aria-label="breadcrumb">
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="index.php">Animals</a></li>
            <li class="breadcrumb-item active"><?= htmlentities($animal['name']) ?></li>
        </ol>
    </nav>

    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2 class="mb-0">
            <?= htmlentities($animal['name']) ?>
            <span class="badge <?= $statusBadges[$animal['status']] ?? 'bg-light text-dark' ?> align-middle">
                <?= htmlentities($statusLabels[$animal['status']] ?? $animal['status']) ?>
            </span>
        </h2>
        <div class="btn-group">
            <a href="edit.php?id=<?= $animal['animal_id'] ?>" class="btn btn-warning">
                <i class="bi bi-pencil"></i> Edit
            </a>
            <a href="delete.php?id=<?= $animal['animal_id'] ?>" class="btn btn-danger">
                <i class="bi bi-trash"></i> Delete
            </a>
        </div>
    </div>

    <div class="row g-4">
        <!-- Left column: profile image and foster placement -->
        <div class="col-md-4">
            <div class="card">
                <?php if ($animal['profile_image']): ?>
                    <!-- Clicking the thumbnail opens the full-size image in a modal -->
                    <img src="../<?= htmlentities($animal['profile_image']) ?>" class="card-img-top"
                         alt="<?= htmlentities($animal['name']) ?>"
                         style="height:280px; object-fit:cover; cursor:pointer;"
                         data-bs-toggle="modal" data-bs-target="#imageModal">
                <?php else: ?>
                    <div class="card-body text-center text-muted py-5">
                        <i class="bi bi-image fs-1"></i>
                        <p class="mb-0 mt-2">No profile image uploaded</p>
                    </div>
                <?php endif; ?>
            </div>

            <div class="card mt-3">
                <div class="card-header"><i class="bi bi-house-heart"></i> Foster Placement</div>
                <div class="card-body">
                    <?php if ($animal['foster_first_name']): ?>
                        <p class="mb-1">
                            <strong><?= htmlentities($animal['foster_first_name'] . ' ' . $animal['foster_last_name']) ?></strong>
                            <?php if ($animal['foster_status'] !== 'active'): ?>
                                <span class="badge bg-secondary">Inactive</span>
                            <?php endif; ?>
                        </p>
                        <p class="mb-1 small text-muted">
                            <i class="bi bi-envelope"></i> <?= htmlentities($animal['foster_email']) ?>
                        </p>
                        <p class="mb-0 small text-muted">
                            <i class="bi bi-telephone"></i> <?= htmlentities($animal['foster_phone']) ?>
                        </p>
                    <?php else: ?>
                        <p class="text-muted mb-0">Not fostered</p>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <!-- Right column: details, description, and internal medical notes -->
        <div class="col-md-8">
            <div class="card">
                <div class="card-header"><i class="bi bi-info-circle"></i> Details</div>
                <div class="card-body">
                    <table class="table table-sm mb-0">
                        <tr>
                            <th style="width:35%;">Species</th>
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
                            <td>
                                <?php if ($animal['desexed']): ?>
                                    <span class="badge bg-success">Yes</span>
                                <?php else: ?>
                                    <span class="badge bg-secondary">No</span>
                                <?php endif; ?>
                            </td>
                        </tr>
                        <tr>
                            <th>Date of Birth</th>
                            <td>
                                <?php if ($animal['date_of_birth']): ?>
                                    <?= htmlentities($animal['date_of_birth']) ?>
                                    <span class="text-muted">(<?= htmlentities($age) ?>)</span>
                                <?php else: ?>
                                    <span class="text-muted">Unknown</span>
                                <?php endif; ?>
                            </td>
                        </tr>
                        <tr>
                            <th>Date Admitted</th>
                            <td><?= htmlentities($animal['date_admitted']) ?></td>
                        </tr>
                        <tr>
                            <th>Adoption Applications</th>
                            <td><span class="badge bg-info"><?= $applicationCount ?></span></td>
                        </tr>
                    </table>
                </div>
            </div>

            <div class="card mt-3">
                <div class="card-header"><i class="bi bi-card-text"></i> Description</div>
                <div class="card-body">
                    <?php if ($animal['description']): ?>
                        <?= nl2br(htmlentities($animal['description'])) ?>
                    <?php else: ?>
                        <span class="text-muted">None recorded</span>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Medical notes are admin-only and never shown on the public detail page -->
            <div class="card mt-3 border-warning">
                <div class="card-header bg-warning-subtle">
                    <i class="bi bi-clipboard-pulse"></i> Medical Notes
                    <span class="badge bg-secondary ms-2">Internal only</span>
                </div>
                <div class="card-body">
                    <?php if ($animal['medical_notes']): ?>
                        <?= nl2br(htmlentities($animal['medical_notes'])) ?>
                    <?php else: ?>
                        <span class="text-muted">None recorded</span>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>

    <div class="mt-4">
        <a href="index.php" class="btn btn-outline-secondary">
            <i class="bi bi-arrow-left"></i> Back to Animal List
        </a>
    </div>

    <?php if ($animal['profile_image']): ?>
        <!-- Full-size profile image, opened from the thumbnail above -->
        <div class="modal fade" id="imageModal" tabindex="-1" aria-hidden="true">
            <div class="modal-dialog modal-lg modal-dialog-centered">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title"><?= htmlentities($animal['name']) ?></h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body text-center">
                        <img src="../<?= htmlentities($animal['profile_image']) ?>"
                             class="img-fluid" alt="<?= htmlentities($animal['name']) ?>">
                    </div>
                </div>
            </div>
        </div>
    <?php endif; ?>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>