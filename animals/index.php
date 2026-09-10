<?php
/**
 * Animal list (admin).
 *
 * Displays all animals with their species, breed, and foster carer (where assigned).
 *
 * @var PDO $pdo
 * @var array $statusLabels
 */

// Start session and include necessary files
session_start();
require_once __DIR__ . '/../auth/authentication.php';
require_once __DIR__ . '/../connection.php';
require_once __DIR__ . '/../includes/animal_helpers.php';

// breed_id is NOT NULL on every animal, so an inner join is appropriate
// foster_carer_id is nullable, so that uses a LEFT JOIN
$stmt = $pdo->query(
    "SELECT a.*, s.species_name, b.breed_name,
            fc.first_name AS foster_first_name, fc.last_name AS foster_last_name
     FROM animals a
     JOIN breeds b ON a.breed_id = b.breed_id
     JOIN species s ON b.species_id = s.species_id
     LEFT JOIN foster_carers fc ON a.foster_carer_id = fc.foster_carer_id
     ORDER BY a.name"
);

$animals = $stmt->fetchAll();

// Counts for the summary cards, derived from the rows already fetched
$statusCounts = array_fill_keys(array_keys($statusLabels), 0);
foreach ($animals as $animal) {
    if (isset($statusCounts[$animal['status']])) {
        $statusCounts[$animal['status']]++;
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
    <title>Animals - SafePaws Admin</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.datatables.net/1.13.6/css/dataTables.bootstrap5.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
</head>
<body>
<?php include __DIR__ . '/../includes/navbar.php'; ?>

<div class="container mt-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2 class="mb-0"><i class="bi bi-clipboard-heart"></i> Animals</h2>
        <a href="add.php" class="btn btn-primary">
            <i class="bi bi-plus-circle"></i> Add New Animal
        </a>
    </div>

    <!-- Summary counts, one card per status -->
    <div class="row g-3 mb-4">
        <div class="col-md-3">
            <div class="card text-white bg-secondary h-100">
                <div class="card-body">
                    <h6 class="card-title">In Care</h6>
                    <p class="card-text display-6 mb-0"><?= $statusCounts['in_care'] ?></p>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card text-white bg-success h-100">
                <div class="card-body">
                    <h6 class="card-title">Available</h6>
                    <p class="card-text display-6 mb-0"><?= $statusCounts['available'] ?></p>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card text-dark bg-warning h-100">
                <div class="card-body">
                    <h6 class="card-title">Adoption Pending</h6>
                    <p class="card-text display-6 mb-0"><?= $statusCounts['pending'] ?></p>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card text-white bg-primary h-100">
                <div class="card-body">
                    <h6 class="card-title">Adopted</h6>
                    <p class="card-text display-6 mb-0"><?= $statusCounts['adopted'] ?></p>
                </div>
            </div>
        </div>
    </div>

    <div class="card">
        <div class="card-body">
            <?php if (count($animals) === 0): ?>
                <p class="text-center text-muted mb-0">
                    <i class="bi bi-inbox"></i> No animals found.
                    <a href="add.php">Add your first animal</a>
                </p>
            <?php else: ?>
                <table id="animalsTable" class="table table-striped table-hover">
                    <thead>
                    <tr>
                        <th>Name</th>
                        <th>Species</th>
                        <th>Breed</th>
                        <th>Sex</th>
                        <th>Status</th>
                        <th>Foster Carer</th>
                        <th>Actions</th>
                    </tr>
                    </thead>
                    <tbody>
                    <?php foreach ($animals as $animal): ?>
                        <tr>
                            <td><?= htmlspecialchars($animal['name']) ?></td>
                            <td><?= htmlspecialchars($animal['species_name']) ?></td>
                            <td><?= htmlspecialchars($animal['breed_name']) ?></td>
                            <td><?= htmlspecialchars(ucfirst($animal['sex'])) ?></td>
                            <td>
                                <span class="badge <?= $statusBadges[$animal['status']] ?? 'bg-light text-dark' ?>">
                                    <?= htmlspecialchars($statusLabels[$animal['status']] ?? $animal['status']) ?>
                                </span>
                            </td>
                            <td>
                                <?php if ($animal['foster_first_name']): ?>
                                    <?= htmlspecialchars($animal['foster_first_name'] . ' ' . $animal['foster_last_name']) ?>
                                <?php else: ?>
                                    <span class="text-muted">Not fostered</span>
                                <?php endif; ?>
                            </td>
                            <td>
                                <div class="btn-group" role="group">
                                    <a href="view.php?id=<?= $animal['animal_id'] ?>"
                                       class="btn btn-sm btn-info" title="View">
                                        <i class="bi bi-eye"></i>
                                    </a>
                                    <a href="edit.php?id=<?= $animal['animal_id'] ?>"
                                       class="btn btn-sm btn-warning" title="Edit">
                                        <i class="bi bi-pencil"></i>
                                    </a>
                                    <a href="delete.php?id=<?= $animal['animal_id'] ?>"
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
    // Only initialize when rows exist - the empty state renders no table
    $(document).ready(function () {
        if ($('#animalsTable').length) {
            $('#animalsTable').DataTable({
                "pageLength": 15,
                "order": [[0, 'asc']],
                "columnDefs": [
                    { "targets": [6], "orderable": false, "searchable": false } // Actions column
                ]
            });
        }
    });
</script>
</body>
</html>