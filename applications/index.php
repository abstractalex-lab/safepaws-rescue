<?php
/**
 * Adoption applications list (admin).
 *
 * Shows every application with the animal it relates to and its current status.
 * Searching and column sorting are handled client-side by DataTables.
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

// animal_id is NOT NULL, so an inner join is appropriate here
$stmt = $pdo->query(
    "SELECT ap.*, a.name AS animal_name, s.species_name
     FROM adoption_applications ap
     JOIN animals a ON ap.animal_id = a.animal_id
     JOIN breeds b ON a.breed_id = b.breed_id
     JOIN species s ON b.species_id = s.species_id
     ORDER BY ap.application_date DESC"
);
$applications = $stmt->fetchAll();

// Counts for the summary cards, derived from the rows already fetched
$statusCounts = array_fill_keys(array_keys($applicationStatusLabels), 0);
foreach ($applications as $application) {
    if (isset($statusCounts[$application['status']])) {
        $statusCounts[$application['status']]++;
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Adoption Applications - SafePaws Admin</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.datatables.net/1.13.6/css/dataTables.bootstrap5.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
</head>
<body>
<?php include __DIR__ . '/../includes/navbar.php'; ?>

<div class="container mt-4">
    <h2 class="mb-4"><i class="bi bi-file-earmark-text"></i> Adoption Applications</h2>

    <!-- Summary counts, one card per status -->
    <div class="row g-3 mb-4">
        <div class="col-md-3">
            <div class="card text-white bg-info h-100">
                <div class="card-body">
                    <h6 class="card-title">New</h6>
                    <p class="card-text display-6 mb-0"><?= $statusCounts['new'] ?></p>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card text-dark bg-warning h-100">
                <div class="card-body">
                    <h6 class="card-title">Under Review</h6>
                    <p class="card-text display-6 mb-0"><?= $statusCounts['under_review'] ?></p>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card text-white bg-success h-100">
                <div class="card-body">
                    <h6 class="card-title">Approved</h6>
                    <p class="card-text display-6 mb-0"><?= $statusCounts['approved'] ?></p>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card text-white bg-secondary h-100">
                <div class="card-body">
                    <h6 class="card-title">Rejected</h6>
                    <p class="card-text display-6 mb-0"><?= $statusCounts['rejected'] ?></p>
                </div>
            </div>
        </div>
    </div>

    <div class="card">
        <div class="card-body">
            <?php if (count($applications) === 0): ?>
                <p class="text-center text-muted mb-0">
                    <i class="bi bi-inbox"></i> No adoption applications have been submitted yet.
                </p>
            <?php else: ?>
                <table id="applicationsTable" class="table table-striped table-hover">
                    <thead>
                    <tr>
                        <th>Applicant</th>
                        <th>Animal</th>
                        <th>Suburb</th>
                        <th>Submitted</th>
                        <th>Status</th>
                        <th>Actions</th>
                    </tr>
                    </thead>
                    <tbody>
                    <?php foreach ($applications as $application): ?>
                        <tr>
                            <td>
                                <?= htmlspecialchars($application['applicant_name']) ?>
                                <br>
                                <span class="small text-muted"><?= htmlspecialchars($application['email']) ?></span>
                            </td>
                            <td>
                                <?= htmlspecialchars($application['animal_name']) ?>
                                <br>
                                <span class="small text-muted"><?= htmlspecialchars($application['species_name']) ?></span>
                            </td>
                            <td><?= htmlspecialchars($application['suburb'] ?? '') ?></td>
                            <td><?= htmlspecialchars(date('j M Y', strtotime($application['application_date']))) ?></td>
                            <td>
                                <span class="badge <?= $applicationStatusBadges[$application['status']] ?? 'bg-light text-dark' ?>">
                                    <?= htmlspecialchars($applicationStatusLabels[$application['status']] ?? $application['status']) ?>
                                </span>
                            </td>
                            <td>
                                <div class="btn-group" role="group">
                                    <a href="view.php?id=<?= $application['application_id'] ?>"
                                       class="btn btn-sm btn-info" title="View">
                                        <i class="bi bi-eye"></i>
                                    </a>
                                    <a href="delete.php?id=<?= $application['application_id'] ?>"
                                       class="btn btn-sm btn-danger" title="Delete">
                                        <i class="bi bi-trash"></i>
                                    </a>
                                </div>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                    </tbody>
                </table>
            <?php endif; ?>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
<script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.datatables.net/1.13.6/js/dataTables.bootstrap5.min.js"></script>

<script>
    // Only initialize when rows exist
    $(document).ready(function () {
        if ($('#applicationsTable').length) {
            $('#applicationsTable').DataTable({
                "pageLength": 15,
                "order": [], // rows already arrive newest-first from SQL
                "columnDefs": [
                    { "targets": [5], "orderable": false, "searchable": false } // Actions column
                ]
            });
        }
    });
</script>
</body>
</html>

