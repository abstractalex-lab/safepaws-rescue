<?php
// foster_carers/view.php
require_once __DIR__ . '/../auth/authentication.php';
require_once __DIR__ . '/../connection.php';
/** @var PDO $pdo */

$foster_carer_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

if ($foster_carer_id <= 0) {
    $_SESSION['error_message'] = 'Invalid foster carer ID.';
    header('Location: list.php');
    exit();
}

try {
    $stmt = $pdo->prepare("SELECT * FROM foster_carers WHERE foster_carer_id = :id");
    $stmt->execute([':id' => $foster_carer_id]);
    $carer = $stmt->fetch();

    if (!$carer) {
        $_SESSION['error_message'] = 'Foster carer not found.';
        header('Location: list.php');
        exit();
    }

    $animal_stmt = $pdo->prepare("SELECT * FROM animals WHERE foster_carer_id = :id AND status != 'Adopted'");
    $animal_stmt->execute([':id' => $foster_carer_id]);
    $animals = $animal_stmt->fetchAll();

    $assigned_count = count($animals);
    $available_capacity = $carer['capacity'] - $assigned_count;

} catch (PDOException $e) {
    error_log('Database error: ' . $e->getMessage());
    $_SESSION['error_message'] = 'Failed to load foster carer details.';
    header('Location: list.php');
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>View Foster Carer - SafePaws</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
</head>
<body>
<?php include __DIR__ . '/../includes/navbar.php'; ?>

<div class="container mt-4">
    <div class="row">
        <div class="col-md-8">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h3><i class="bi bi-person-badge"></i> Foster Carer Details</h3>
                    <div>
                        <a href="edit.php?id=<?= $carer['foster_carer_id'] ?>"
                           class="btn btn-warning btn-sm">
                            <i class="bi bi-pencil"></i> Edit
                        </a>
                        <a href="list.php" class="btn btn-secondary btn-sm">
                            <i class="bi bi-arrow-left"></i> Back
                        </a>
                    </div>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6">
                            <dl>
                                <dt>Full Name</dt>
                                <dd><?= htmlspecialchars($carer['first_name'] . ' ' . $carer['last_name']) ?></dd>

                                <dt>Email</dt>
                                <dd>
                                    <a href="mailto:<?= htmlspecialchars($carer['email']) ?>">
                                        <i class="bi bi-envelope"></i> <?= htmlspecialchars($carer['email']) ?>
                                    </a>
                                </dd>

                                <dt>Phone</dt>
                                <dd><?= htmlspecialchars($carer['phone'] ?? 'N/A') ?></dd>

                                <dt>Suburb</dt>
                                <dd><?= htmlspecialchars($carer['suburb'] ?? 'N/A') ?></dd>
                            </dl>
                        </div>
                        <div class="col-md-6">
                            <dl>
                                <dt>Preferred Animal Type</dt>
                                <dd><?= htmlspecialchars($carer['preferred_animal_type'] ?? 'Any') ?></dd>

                                <dt>Fostering Capacity</dt>
                                <dd><?= $carer['capacity'] ?></dd>

                                <dt>Status</dt>
                                <dd>
                                    <?php if ($carer['status'] == 'active'): ?>
                                        <span class="badge bg-success">Active</span>
                                    <?php else: ?>
                                        <span class="badge bg-secondary">Inactive</span>
                                    <?php endif; ?>
                                </dd>

                                <dt>Current Assignments</dt>
                                <dd>
                                    <span class="badge bg-info"><?= $assigned_count ?> / <?= $carer['capacity'] ?></span>
                                    <?php if ($available_capacity > 0 && $carer['status'] == 'active'): ?>
                                        <span class="text-success">(<?= $available_capacity ?> spots available)</span>
                                    <?php elseif ($available_capacity <= 0 && $carer['status'] == 'active'): ?>
                                        <span class="text-warning">(Full)</span>
                                    <?php endif; ?>
                                </dd>
                            </dl>
                        </div>
                    </div>

                    <?php if (!empty($carer['notes'])): ?>
                        <dt>Notes</dt>
                        <dd><?= nl2br(htmlspecialchars($carer['notes'])) ?></dd>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <div class="col-md-4">
            <div class="card">
                <div class="card-header">
                    <h5><i class="bi bi-paw"></i> Currently Fostering</h5>
                </div>
                <div class="card-body">
                    <?php if (count($animals) > 0): ?>
                        <ul class="list-group">
                            <?php foreach ($animals as $animal): ?>
                                <li class="list-group-item d-flex justify-content-between align-items-center">
                                    <?= htmlspecialchars($animal['name'] ?? 'Unknown') ?>
                                    <span class="badge bg-primary rounded-pill">#<?= $animal['animal_id'] ?></span>
                                </li>
                            <?php endforeach; ?>
                        </ul>
                    <?php else: ?>
                        <p class="text-muted">No animals currently assigned</p>
                    <?php endif; ?>

                    <?php if ($carer['status'] == 'active' && $available_capacity > 0): ?>
                        <div class="mt-3">
                            <a href="../animals/index.php?foster_carer=<?= $carer['foster_carer_id'] ?>"
                               class="btn btn-sm btn-outline-primary">
                                <i class="bi bi-plus-circle"></i> Assign Animal
                            </a>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
