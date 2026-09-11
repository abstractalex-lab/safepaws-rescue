<?php
/**
 * Partner organisation detail (admin).
 *
 * Read-only view of a single organisation, showing the fields the list
 * page has no room for - address, website and notes.
 *
 * Partner organisations are a standalone entity with no foreign keys to
 * or from other tables, so there are no related records to show here.
 *
 * @var PDO $pdo
 */

// include authentication, database connection, and CSRF helpers
require_once __DIR__ . '/../auth/authentication.php';
require_once __DIR__ . '/../connection.php';
require_once __DIR__ . '/../includes/csrf.php';

$organisation_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

if ($organisation_id <= 0) {
    $_SESSION['error_message'] = 'Invalid organisation ID.';
    header('Location: index.php');
    exit();
}

try {
    $stmt = $pdo->prepare("SELECT * FROM partner_organisations WHERE organisation_id = :id");
    $stmt->execute([':id' => $organisation_id]);
    $organisation = $stmt->fetch();

    if (!$organisation) {
        $_SESSION['error_message'] = 'Organisation not found.';
        header('Location: index.php');
        exit();
    }
} catch (PDOException $e) {
    error_log('Database error: ' . $e->getMessage());
    $_SESSION['error_message'] = 'Failed to load organisation details.';
    header('Location: index.php');
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>View Partner Organisation - SafePaws</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
</head>
<body>
<?php include __DIR__ . '/../includes/navbar.php'; ?>

<div class="container mt-4">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h3 class="mb-0"><i class="bi bi-building"></i> Organisation Details</h3>
                    <div>
                        <a href="edit.php?id=<?= $organisation['organisation_id'] ?>"
                           class="btn btn-warning btn-sm">
                            <i class="bi bi-pencil"></i> Edit
                        </a>
                        <a href="index.php" class="btn btn-secondary btn-sm">
                            <i class="bi bi-arrow-left"></i> Back
                        </a>
                    </div>
                </div>
                <div class="card-body">
                    <h4 class="mb-1"><?= htmlspecialchars($organisation['name'], ENT_QUOTES) ?></h4>
                    <p class="mb-4">
                        <span class="badge bg-info">
                            <?= htmlspecialchars($organisation['organisation_type'] ?? 'Not specified', ENT_QUOTES) ?>
                        </span>
                    </p>

                    <table class="table table-sm">
                        <tr>
                            <th style="width:30%;">Contact Person</th>
                            <td>
                                <?php if (!empty($organisation['contact_person'])): ?>
                                    <?= htmlspecialchars($organisation['contact_person'], ENT_QUOTES) ?>
                                <?php else: ?>
                                    <span class="text-muted">Not provided</span>
                                <?php endif; ?>
                            </td>
                        </tr>
                        <tr>
                            <th>Email</th>
                            <td>
                                <?php if (!empty($organisation['email'])): ?>
                                    <a href="mailto:<?= htmlspecialchars($organisation['email'], ENT_QUOTES) ?>">
                                        <i class="bi bi-envelope"></i>
                                        <?= htmlspecialchars($organisation['email'], ENT_QUOTES) ?>
                                    </a>
                                <?php else: ?>
                                    <span class="text-muted">Not provided</span>
                                <?php endif; ?>
                            </td>
                        </tr>
                        <tr>
                            <th>Phone</th>
                            <td>
                                <?php if (!empty($organisation['phone'])): ?>
                                    <?= htmlspecialchars($organisation['phone'], ENT_QUOTES) ?>
                                <?php else: ?>
                                    <span class="text-muted">Not provided</span>
                                <?php endif; ?>
                            </td>
                        </tr>
                        <tr>
                            <th>Address</th>
                            <td>
                                <?php if (!empty($organisation['address'])): ?>
                                    <?= nl2br(htmlspecialchars($organisation['address'], ENT_QUOTES)) ?>
                                <?php else: ?>
                                    <span class="text-muted">Not provided</span>
                                <?php endif; ?>
                            </td>
                        </tr>
                        <tr>
                            <th>Website</th>
                            <td>
                                <?php if (!empty($organisation['website'])): ?>
                                    <!-- rel="noopener" so the opened page can't reach back via window.opener -->
                                    <a href="<?= htmlspecialchars($organisation['website'], ENT_QUOTES) ?>"
                                       target="_blank" rel="noopener noreferrer">
                                        <i class="bi bi-box-arrow-up-right"></i>
                                        <?= htmlspecialchars($organisation['website'], ENT_QUOTES) ?>
                                    </a>
                                <?php else: ?>
                                    <span class="text-muted">Not provided</span>
                                <?php endif; ?>
                            </td>
                        </tr>
                    </table>

                    <h6 class="mt-4">Notes</h6>
                    <?php if (!empty($organisation['notes'])): ?>
                        <p><?= nl2br(htmlspecialchars($organisation['notes'], ENT_QUOTES)) ?></p>
                    <?php else: ?>
                        <p class="text-muted">No notes recorded.</p>
                    <?php endif; ?>
                </div>
                <div class="card-footer d-flex justify-content-end">
                    <button type="button"
                            onclick="confirmDelete(<?= (int)$organisation['organisation_id'] ?>, '<?= htmlspecialchars($organisation['name'], ENT_QUOTES) ?>')"
                            class="btn btn-danger btn-sm">
                        <i class="bi bi-trash"></i> Delete Organisation
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="deleteModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Confirm Delete</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <p>Are you sure you want to delete <strong id="deleteOrgName"></strong>?</p>
                <p class="text-danger"><i class="bi bi-exclamation-triangle"></i> This action cannot be undone.</p>
            </div>
            <form method="POST" action="delete.php">
                <?= csrf_field() ?>
                <input type="hidden" name="id" id="deleteOrgId">
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-danger">
                        <i class="bi bi-trash"></i> Delete
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script>
    function confirmDelete(id, name) {
        document.getElementById('deleteOrgName').textContent = name;
        document.getElementById('deleteOrgId').value = id;
        new bootstrap.Modal(document.getElementById('deleteModal')).show();
    }
</script>
</body>
</html>