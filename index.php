<?php
/**
 * Public homepage.
 *
 * Does NOT include authentication.php, publicly available and accessible to view page.
 *
 * @var PDO $pdo
 */

session_start();
require_once __DIR__ . '/connection.php';

// A few available animals to preview on the landing page.
try {
    $stmt = $pdo->query(
            "SELECT a.animal_id, a.name, a.profile_image, s.species_name, b.breed_name
         FROM animals a
         JOIN breeds b ON a.breed_id = b.breed_id
         JOIN species s ON b.species_id = s.species_id
         WHERE a.status = 'available'
         ORDER BY a.date_admitted DESC
         LIMIT 3"
    );
    $featured = $stmt->fetchAll();
} catch (PDOException $e) {
    error_log('Homepage featured animals failed: ' . $e->getMessage());
    $featured = [];
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>SafePaws Rescue</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
</head>
<body>
<?php include __DIR__ . '/includes/navbar.php'; ?>

<div class="container mt-4">

    <div class="p-5 mb-4 bg-light rounded-3">
        <h1 class="display-5"><i class="bi bi-heart-fill text-danger"></i> SafePaws Rescue</h1>
        <p class="col-md-8 fs-5">
            We care for abandoned and surrendered animals and help them find foster
            carers and permanent homes. Every animal listed here is looking for someone.
        </p>
        <a href="public/animals.php" class="btn btn-primary btn-lg">
            <i class="bi bi-search-heart"></i> Meet the animals
        </a>
    </div>

    <?php if (!empty($featured)): ?>
        <h4 class="mb-3">Recently arrived</h4>
        <div class="row g-3 mb-4">
            <?php foreach ($featured as $animal): ?>
                <div class="col-md-4">
                    <div class="card h-100">
                        <?php if ($animal['profile_image']): ?>
                            <img src="<?= htmlentities($animal['profile_image']) ?>"
                                 class="card-img-top" alt="<?= htmlentities($animal['name']) ?>"
                                 style="height:200px; object-fit:cover;">
                        <?php endif; ?>
                        <div class="card-body">
                            <h5 class="card-title"><?= htmlentities($animal['name']) ?></h5>
                            <p class="card-text text-muted">
                                <?= htmlentities($animal['species_name']) ?> &middot;
                                <?= htmlentities($animal['breed_name']) ?>
                            </p>
                            <a href="public/animal_detail.php?id=<?= $animal['animal_id'] ?>"
                               class="btn btn-sm btn-outline-primary">Find out more</a>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>

    <div class="row g-3 mb-4">
        <div class="col-md-6">
            <div class="card h-100">
                <div class="card-body">
                    <h5 class="card-title"><i class="bi bi-house-heart"></i> Adopt an animal</h5>
                    <p class="card-text">
                        Browse the animals currently looking for a permanent home and
                        submit an adoption application online.
                    </p>
                    <a href="public/animals.php" class="btn btn-outline-primary">Browse animals</a>
                </div>
            </div>
        </div>
        <div class="col-md-6">
            <div class="card h-100">
                <div class="card-body">
                    <h5 class="card-title"><i class="bi bi-envelope"></i> Get in touch</h5>
                    <p class="card-text">
                        Questions about adopting, fostering, or surrendering an animal?
                        Send us a message and we'll get back to you.
                    </p>
                    <!-- TODO: Add a contact form in the future, but for now just link to the contact page. -->
                    <a href="contact/index.php" class="btn btn-outline-primary">Contact us</a>
                </div>
            </div>
        </div>
    </div>

</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>