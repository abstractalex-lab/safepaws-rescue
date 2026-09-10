<?php
/**
 * Delete adoption application (admin).
 *
 * GET shows a confirmation page with the application's details, after that run delete via POST,
 * prevents a stray GET (prefetch, pasted URL, crawler) from destroying a record.
 *
 * Nothing references adoption_applications, so unlike deleting an animal there's no foreign key to block this.
 * The animal's own status is left alone, so if the application was approved and the animal marked adopted,
 * deleting the application won't change that.
 *
 * @var PDO $pdo
 * @var array $applicationStatusLabels
 * @var array $applicationStatusBadges
 */

// Start session and include necessary files
session_start();
require_once __DIR__ . '/../auth/authentication.php';
require_once __DIR__ . '/../connection.php';
require_once __DIR__ . '/../includes/application_helpers.php';

// Get the application ID from either GET or POST, and validate it as a digit string
$applicationId = $_GET['id'] ?? $_POST['application_id'] ?? '';
if (!ctype_digit((string) $applicationId)) {
    header("Location: index.php?error=invalid");
    exit;
}

// Fetch the application with its animal, for display on the confirmation page
$stmt = $pdo->prepare(
    "SELECT ap.*, a.name AS animal_name, s.species_name
     FROM adoption_applications ap
     JOIN animals a ON ap.animal_id = a.animal_id
     JOIN breeds b ON a.breed_id = b.breed_id
     JOIN species s ON b.species_id = s.species_id
     WHERE ap.application_id = ?"
);
$stmt->execute([$applicationId]);
$application = $stmt->fetch();

// If the application doesn't exist, redirect back to the index with an error
if (!$application) {
    header("Location: index.php?error=notfound");
    exit;
}

// Handle the POST request to delete the application
$error = null;
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        $delete = $pdo->prepare("DELETE FROM adoption_applications WHERE application_id = ?");
        $delete->execute([$applicationId]);

        header("Location: index.php?deleted=1");
        exit;
    } catch (PDOException $e) {
        error_log("Delete application failed: " . $e->getMessage());
        $error = "Something went wrong while deleting this application. Please try again.";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Delete Application - SafePaws Admin</title>
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
                    <i class="bi bi-exclamation-octagon"></i> <?= htmlspecialchars($error) ?>
                </div>
            <?php endif; ?>

            <div class="card border-danger">
                <div class="card-header bg-danger text-white">
                    <i class="bi bi-exclamation-triangle"></i> Delete Adoption Application
                </div>
                <div class="card-body">

                    <p>Are you sure you want to delete this application? This cannot be undone.</p>

                    <table class="table table-sm mb-0">
                        <tr>
                            <th style="width:35%;">Applicant</th>
                            <td><?= htmlspecialchars($application['applicant_name']) ?></td>
                        </tr>
                        <tr>
                            <th>Email</th>
                            <td><?= htmlspecialchars($application['email']) ?></td>
                        </tr>
                        <tr>
                            <th>Animal</th>
                            <td>
                                <?= htmlspecialchars($application['animal_name']) ?>
                                <span class="text-muted">(<?= htmlspecialchars($application['species_name']) ?>)</span>
                            </td>
                        </tr>
                        <tr>
                            <th>Submitted</th>
                            <td><?= htmlspecialchars(date('j M Y, g:ia', strtotime($application['application_date']))) ?></td>
                        </tr>
                        <tr>
                            <th>Status</th>
                            <td>
                                <span class="badge <?= $applicationStatusBadges[$application['status']] ?? 'bg-light text-dark' ?>">
                                    <?= htmlspecialchars($applicationStatusLabels[$application['status']] ?? $application['status']) ?>
                                </span>
                            </td>
                        </tr>
                    </table>

                    <?php if ($application['status'] === 'approved'): ?>
                        <!-- Deleting the record doesn't undo the animal status change the
                             approval made, so it's worth saying so explicitly -->
                        <div class="alert alert-warning mt-3 mb-0" role="alert">
                            <i class="bi bi-info-circle"></i>
                            This application was approved. Deleting it won't change
                            <?= htmlspecialchars($application['animal_name']) ?>'s status - update
                            that from the animal's edit page if it needs to change.
                        </div>
                    <?php endif; ?>

                </div>
                <div class="card-footer d-flex justify-content-end gap-2">
                    <a href="view.php?id=<?= $application['application_id'] ?>" class="btn btn-outline-secondary">
                        <i class="bi bi-x-circle"></i> Cancel and go back
                    </a>
                    <form method="post" action="delete.php" class="d-inline">
                        <input type="hidden" name="application_id" value="<?= htmlspecialchars($applicationId) ?>">
                        <button type="submit" class="btn btn-danger">
                            <i class="bi bi-trash"></i> Yes, delete this application
                        </button>
                    </form>
                </div>
            </div>

        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>