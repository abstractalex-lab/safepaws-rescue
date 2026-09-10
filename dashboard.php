<?php
/**
 * Admin dashboard.
 *
 * The destination after a successful login.
 * Shows summary counts across the system and quick links into each management area.
 *
 * @var PDO $pdo
 */

session_start();
require_once __DIR__ . '/auth/authentication.php';
require_once __DIR__ . '/connection.php';

// Summary counts for the dashboard cards, with error handling to avoid breaking the page if a query fails
try {
    $animalsInCare = $pdo->query(
        "SELECT COUNT(*) FROM animals WHERE status IN ('in_care', 'available', 'pending')"
    )->fetchColumn();

    $animalsAvailable = $pdo->query(
        "SELECT COUNT(*) FROM animals WHERE status = 'available'"
    )->fetchColumn();

    $animalsFostered = $pdo->query(
        "SELECT COUNT(*) FROM animals WHERE foster_carer_id IS NOT NULL"
    )->fetchColumn();

    $pendingApplications = $pdo->query(
        "SELECT COUNT(*) FROM adoption_applications WHERE status IN ('new', 'under_review')"
    )->fetchColumn();

    $newMessages = $pdo->query(
        "SELECT COUNT(*) FROM contact_messages WHERE replied = 0"
    )->fetchColumn();

    $activeCarers = $pdo->query(
        "SELECT COUNT(*) FROM foster_carers WHERE status = 'active'"
    )->fetchColumn();

    $loadError = null;
} catch (PDOException $e) {
    error_log('Dashboard counts failed: ' . $e->getMessage());
    $loadError = 'Some dashboard figures could not be loaded.';
    $animalsInCare = $animalsAvailable = $animalsFostered = null;
    $pendingApplications = $newMessages = $activeCarers = null;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard - SafePaws Admin</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
</head>
<body>
<?php include __DIR__ . '/includes/navbar.php'; ?>

<div class="container mt-4">
    <h2 class="mb-1"><i class="bi bi-speedometer2"></i> Dashboard</h2>
    <p class="text-muted mb-4">
        Welcome back, <?= htmlspecialchars($current_user->first_name ?? 'Admin') ?>.
    </p>

    <?php if ($loadError): ?>
        <div class="alert alert-warning" role="alert">
            <i class="bi bi-exclamation-triangle"></i> <?= htmlspecialchars($loadError) ?>
        </div>
    <?php endif; ?>

    <div class="row g-3 mb-4">
        <div class="col-md-4">
            <div class="card text-white bg-secondary h-100">
                <div class="card-body">
                    <h6 class="card-title">Animals in Care</h6>
                    <p class="card-text display-6 mb-0"><?= $animalsInCare ?? '-' ?></p>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card text-white bg-success h-100">
                <div class="card-body">
                    <h6 class="card-title">Available for Adoption</h6>
                    <p class="card-text display-6 mb-0"><?= $animalsAvailable ?? '-' ?></p>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card text-white bg-info h-100">
                <div class="card-body">
                    <h6 class="card-title">Currently Fostered</h6>
                    <p class="card-text display-6 mb-0"><?= $animalsFostered ?? '-' ?></p>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card text-dark bg-warning h-100">
                <div class="card-body">
                    <h6 class="card-title">Pending Applications</h6>
                    <p class="card-text display-6 mb-0"><?= $pendingApplications ?? '-' ?></p>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card text-white bg-danger h-100">
                <div class="card-body">
                    <h6 class="card-title">Unanswered Messages</h6>
                    <p class="card-text display-6 mb-0"><?= $newMessages ?? '-' ?></p>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card text-white bg-primary h-100">
                <div class="card-body">
                    <h6 class="card-title">Active Foster Carers</h6>
                    <p class="card-text display-6 mb-0"><?= $activeCarers ?? '-' ?></p>
                </div>
            </div>
        </div>
    </div>

    <h5 class="mb-3">Manage</h5>
    <div class="row g-3">
        <div class="col-md-3">
            <a href="animals/index.php" class="btn btn-outline-dark w-100 py-3">
                <i class="bi bi-clipboard-heart d-block fs-3"></i> Animals
            </a>
        </div>
        <div class="col-md-3">
            <a href="foster_carers/list.php" class="btn btn-outline-dark w-100 py-3">
                <i class="bi bi-people d-block fs-3"></i> Foster Carers
            </a>
        </div>
        <div class="col-md-3">
            <a href="partner_organisations/list.php" class="btn btn-outline-dark w-100 py-3">
                <i class="bi bi-building d-block fs-3"></i> Partners
            </a>
        </div>
        <div class="col-md-3">
            <!-- TODO: Add an user list in the future, since now just a placeholder link -->
            <a href="users/list.php" class="btn btn-outline-dark w-100 py-3">
                <i class="bi bi-person-gear d-block fs-3"></i> Users
            </a>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>