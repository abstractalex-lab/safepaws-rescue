<?php
/**
 * Adoption application detail (admin).
 *
 * Shows the full application alongside the animal it relates to, and lets
 * Emma change its status.
 *
 * Approving an application also moves the animal to "Adoption pending",
 * so the two writes are wrapped in a transaction - an approved application
 * pointing at an animal still marked available would be a broken state.
 * Emma can still set the animal to "Adopted" later from its edit page.
 *
 * @var PDO $pdo
 * @var array $statusLabels
 * @var array $applicationStatusLabels
 * @var array $applicationStatusBadges
 * @var array $ownershipLabels
 */

// Start session and include necessary files
session_start();
require_once __DIR__ . '/../auth/authentication.php';
require_once __DIR__ . '/../connection.php';
require_once __DIR__ . '/../includes/animal_helpers.php';
require_once __DIR__ . '/../includes/application_helpers.php';

// Get the application ID from either GET or POST, and validate it as a digit string
$applicationId = $_GET['id'] ?? $_POST['application_id'] ?? '';
if (!ctype_digit((string) $applicationId)) {
    header("Location: index.php?error=invalid");
    exit;
}

$error = null;
$success = null;

// Status change is submitted from the form on this page
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $newStatus = $_POST['status'] ?? '';

    if (!array_key_exists($newStatus, $applicationStatusLabels)) {
        $error = "Please select a valid status.";
    } else {
        try {
            // Wrap the two updates in a transaction to avoid leaving the system in a broken state
            $pdo->beginTransaction();
            $update = $pdo->prepare(
                "UPDATE adoption_applications SET status = :status WHERE application_id = :id"
            );
            $update->execute([
                'status' => $newStatus,
                'id'     => $applicationId,
            ]);

            // If the application is approved, also move the animal to "Adoption pending" if it's currently available
            if ($newStatus === 'approved') {
                $updateAnimal = $pdo->prepare(
                    "UPDATE animals a
                     JOIN adoption_applications ap ON ap.animal_id = a.animal_id
                     SET a.status = 'pending'
                     WHERE ap.application_id = :id AND a.status = 'available'"
                );
                $updateAnimal->execute(['id' => $applicationId]);
            }
            $pdo->commit();
            $success = "Application status updated.";
        } catch (PDOException $e)
        {
            // Roll back the transaction if any part fails, and log the error
            $pdo->rollBack();
            error_log("Application status update failed: " . $e->getMessage());
            $error = "Something went wrong while updating this application. Please try again.";
        }
    }
}

// Loaded after any update so the page reflects the new state
$stmt = $pdo->prepare(
    "SELECT ap.*, a.name AS animal_name, a.status AS animal_status, a.profile_image,
            s.species_name, b.breed_name
     FROM adoption_applications ap
     JOIN animals a ON ap.animal_id = a.animal_id
     JOIN breeds b ON a.breed_id = b.breed_id
     JOIN species s ON b.species_id = s.species_id
     WHERE ap.application_id = ?"
);
$stmt->execute([$applicationId]);
$application = $stmt->fetch();

// If the application ID doesn't exist, redirect back to the list with an error
if (!$application) {
    header("Location: index.php?error=notfound");
    exit;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Application from <?= htmlspecialchars($application['applicant_name']) ?> - SafePaws Admin</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
</head>
<body>
<?php include __DIR__ . '/../includes/navbar.php'; ?>

<div class="container mt-4">

    <nav aria-label="breadcrumb">
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="index.php">Adoption Applications</a></li>
            <li class="breadcrumb-item active"><?= htmlspecialchars($application['applicant_name']) ?></li>
        </ol>
    </nav>

    <?php if ($success): ?>
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            <i class="bi bi-check-circle"></i> <?= htmlspecialchars($success) ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    <?php endif; ?>

    <?php if ($error): ?>
        <div class="alert alert-danger" role="alert">
            <i class="bi bi-exclamation-triangle"></i> <?= htmlspecialchars($error) ?>
        </div>
    <?php endif; ?>

    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2 class="mb-0">
            Application
            <span class="badge <?= $applicationStatusBadges[$application['status']] ?? 'bg-light text-dark' ?> align-middle">
                <?= htmlspecialchars($applicationStatusLabels[$application['status']] ?? $application['status']) ?>
            </span>
        </h2>
        <a href="delete.php?id=<?= $application['application_id'] ?>" class="btn btn-danger">
            <i class="bi bi-trash"></i> Delete
        </a>
    </div>

    <div class="row g-4">
        <!-- Left column: the animal this application is for -->
        <div class="col-md-4">
            <div class="card">
                <div class="card-header"><i class="bi bi-clipboard-heart"></i> Animal</div>
                <?php if ($application['profile_image']): ?>
                    <img src="../<?= htmlspecialchars($application['profile_image']) ?>" class="card-img-top"
                         alt="<?= htmlspecialchars($application['animal_name']) ?>"
                         style="height:200px; object-fit:cover;">
                <?php endif; ?>
                <div class="card-body">
                    <h5 class="card-title mb-1"><?= htmlspecialchars($application['animal_name']) ?></h5>
                    <p class="text-muted small mb-2">
                        <?= htmlspecialchars($application['species_name']) ?> &middot;
                        <?= htmlspecialchars($application['breed_name']) ?>
                    </p>
                    <p class="mb-3">
                        Current status:
                        <strong><?= htmlspecialchars($statusLabels[$application['animal_status']] ?? $application['animal_status']) ?></strong>
                    </p>
                    <a href="../animals/view.php?id=<?= $application['animal_id'] ?>"
                       class="btn btn-sm btn-outline-primary">
                        View animal record
                    </a>
                </div>
            </div>

            <div class="card mt-3">
                <div class="card-header"><i class="bi bi-arrow-repeat"></i> Update Status</div>
                <div class="card-body">
                    <form method="post" action="view.php">
                        <input type="hidden" name="application_id" value="<?= htmlspecialchars($applicationId) ?>">

                        <div class="mb-3">
                            <label for="status" class="form-label">Application status</label>
                            <select class="form-select" id="status" name="status" required>
                                <?php foreach ($applicationStatusLabels as $value => $label): ?>
                                    <option value="<?= $value ?>" <?= $application['status'] === $value ? 'selected' : '' ?>>
                                        <?= htmlspecialchars($label) ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                            <div class="form-text">
                                Approving also moves the animal to "Adoption pending" if it's
                                currently available. Mark it adopted from the animal's edit page
                                once it goes home.
                            </div>
                        </div>

                        <button type="submit" class="btn btn-primary w-100">
                            <i class="bi bi-check-circle"></i> Save Status
                        </button>
                    </form>
                </div>
            </div>
        </div>

        <!-- Right column: the applicant's submission -->
        <div class="col-md-8">
            <div class="card">
                <div class="card-header"><i class="bi bi-person"></i> Applicant</div>
                <div class="card-body">
                    <table class="table table-sm mb-0">
                        <tr>
                            <th style="width:35%;">Name</th>
                            <td><?= htmlspecialchars($application['applicant_name']) ?></td>
                        </tr>
                        <tr>
                            <th>Email</th>
                            <td>
                                <a href="mailto:<?= htmlspecialchars($application['email']) ?>">
                                    <?= htmlspecialchars($application['email']) ?>
                                </a>
                            </td>
                        </tr>
                        <tr>
                            <th>Phone</th>
                            <td><?= htmlspecialchars($application['phone']) ?></td>
                        </tr>
                        <tr>
                            <th>Suburb</th>
                            <td>
                                <?php if ($application['suburb']): ?>
                                    <?= htmlspecialchars($application['suburb']) ?>
                                <?php else: ?>
                                    <span class="text-muted">Not provided</span>
                                <?php endif; ?>
                            </td>
                        </tr>
                        <tr>
                            <th>Submitted</th>
                            <td><?= htmlspecialchars(date('j M Y, g:ia', strtotime($application['application_date']))) ?></td>
                        </tr>
                    </table>
                </div>
            </div>

            <div class="card mt-3">
                <div class="card-header"><i class="bi bi-house"></i> Living Situation</div>
                <div class="card-body">
                    <table class="table table-sm mb-0">
                        <tr>
                            <th style="width:35%;">Housing type</th>
                            <td>
                                <?php if ($application['housing_type']): ?>
                                    <?= htmlspecialchars($application['housing_type']) ?>
                                <?php else: ?>
                                    <span class="text-muted">Not provided</span>
                                <?php endif; ?>
                            </td>
                        </tr>
                        <tr>
                            <th>Own or rent</th>
                            <td>
                                <?php if ($application['home_ownership']): ?>
                                    <?= htmlspecialchars($ownershipLabels[$application['home_ownership']] ?? $application['home_ownership']) ?>
                                <?php else: ?>
                                    <span class="text-muted">Not provided</span>
                                <?php endif; ?>
                            </td>
                        </tr>
                        <tr>
                            <th>Other pets</th>
                            <td>
                                <?php if ($application['other_pets']): ?>
                                    <?= nl2br(htmlspecialchars($application['other_pets'])) ?>
                                <?php else: ?>
                                    <span class="text-muted">None reported</span>
                                <?php endif; ?>
                            </td>
                        </tr>
                    </table>
                </div>
            </div>

            <div class="card mt-3">
                <div class="card-header">
                    <i class="bi bi-chat-heart"></i>
                    Why they'd like to adopt <?= htmlspecialchars($application['animal_name']) ?>
                </div>
                <div class="card-body">
                    <?php if ($application['reason_for_adoption']): ?>
                        <?= nl2br(htmlspecialchars($application['reason_for_adoption'])) ?>
                    <?php else: ?>
                        <span class="text-muted">Not provided</span>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>

    <div class="mt-4 mb-5">
        <a href="index.php" class="btn btn-outline-secondary">
            <i class="bi bi-arrow-left"></i> Back to Applications
        </a>
    </div>

</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

</body>
</html>