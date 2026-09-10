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

// Include necessary files for database connection and helper functions
require_once __DIR__ . '/../connection.php';
require_once __DIR__ . '/../includes/animal_helpers.php';

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
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Animals for Adoption - SafePaws Rescue</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
</head>
<body>
<?php include __DIR__ . '/../includes/navbar.php'; ?>

<div class="container mt-4">

    <div class="mb-4">
        <h2><i class="bi bi-search-heart"></i> Animals Looking for a Home</h2>
        <p class="text-muted mb-0">
            Every animal below is available for adoption right now.
        </p>
    </div>

    <?php if (isset($_GET['notfound'])): ?>
        <!-- Set when animal_detail.php is asked for an animal that isn't available -->
        <div class="alert alert-info alert-dismissible fade show" role="alert">
            <i class="bi bi-info-circle"></i>
            That animal isn't available for adoption at the moment. Here's who is.
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    <?php endif; ?>

    <?php if (count($animals) === 0): ?>
        <div class="text-center text-muted py-5">
            <i class="bi bi-house-heart fs-1"></i>
            <p class="mt-3 mb-0">
                There are no animals available for adoption at the moment.
                Please check back soon.
            </p>
        </div>
    <?php else: ?>
        <div class="row g-4">
            <?php foreach ($animals as $animal): ?>
                <div class="col-md-6 col-lg-4">
                    <div class="card h-100">
                        <?php if ($animal['profile_image']): ?>
                            <img src="../<?= htmlspecialchars($animal['profile_image']) ?>"
                                 class="card-img-top" alt="<?= htmlspecialchars($animal['name']) ?>"
                                 style="height:220px; object-fit:cover;">
                        <?php else: ?>
                            <div class="card-img-top d-flex align-items-center justify-content-center
                                        bg-light text-muted" style="height:220px;">
                                <i class="bi bi-image fs-1"></i>
                            </div>
                        <?php endif; ?>

                        <div class="card-body d-flex flex-column">
                            <h5 class="card-title mb-1"><?= htmlspecialchars($animal['name']) ?></h5>

                            <p class="text-muted small mb-2">
                                <?= htmlspecialchars($animal['species_name']) ?> &middot;
                                <?= htmlspecialchars($animal['breed_name']) ?> &middot;
                                <?= htmlspecialchars(ucfirst($animal['sex'])) ?>
                                <?php $age = animalAge($animal['date_of_birth']); ?>
                                <?php if ($age): ?>
                                    &middot; <?= htmlspecialchars($age) ?>
                                <?php endif; ?>
                            </p>

                            <?php if ($animal['description']): ?>
                                <p class="card-text small"><?= nl2br(htmlspecialchars($animal['description'])) ?></p>
                            <?php endif; ?>

                            <!-- mt-auto keeps the button on the bottom edge whatever the description length -->
                            <a href="animal_detail.php?id=<?= $animal['animal_id'] ?>"
                               class="btn btn-outline-primary mt-auto align-self-start">
                                Find out more
                            </a>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>

    <div class="mt-4 mb-5">
        <a href="../index.php" class="btn btn-outline-secondary">
            <i class="bi bi-arrow-left"></i> Back to Home
        </a>
    </div>

</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
