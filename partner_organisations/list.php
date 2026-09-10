<?php
// partner_organisations/list.php
require_once __DIR__ . '/../auth/authentication.php';
require_once __DIR__ . '/../connection.php';
require_once __DIR__ . '/../includes/csrf.php';
/** @var PDO $pdo */

$success_message = $_SESSION['success_message'] ?? null;
$error_message = $_SESSION['error_message'] ?? null;
unset($_SESSION['success_message'], $_SESSION['error_message']);

$search = isset($_GET['search']) ? '%' . $_GET['search'] . '%' : '%';
$type_filter = isset($_GET['type']) ? $_GET['type'] : '';

$sql = "SELECT * FROM partner_organisations WHERE name LIKE :search";
$params = [':search' => $search];

if (!empty($type_filter)) {
    $sql .= " AND organisation_type = :type";
    $params[':type'] = $type_filter;
}

$sql .= " ORDER BY name ASC";
$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$organisations = $stmt->fetchAll();

$type_stmt = $pdo->query("SELECT DISTINCT organisation_type FROM partner_organisations ORDER BY organisation_type");
$types = $type_stmt->fetchAll();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Partner Organisations - SafePaws</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.datatables.net/1.13.6/css/dataTables.bootstrap5.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
</head>
<body>
<?php include __DIR__ . '/../includes/navbar.php'; ?>

<div class="container mt-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2>Partner Organisations</h2>
        <a href="add.php" class="btn btn-primary">
            <i class="bi bi-plus-circle"></i> Add New Organisation
        </a>
    </div>

    <?php if ($success_message): ?>
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            <i class="bi bi-check-circle"></i> <?= htmlspecialchars($success_message, ENT_QUOTES) ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    <?php endif; ?>

    <?php if ($error_message): ?>
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <i class="bi bi-exclamation-triangle"></i> <?= htmlspecialchars($error_message, ENT_QUOTES) ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    <?php endif; ?>

    <div class="card mb-4">
        <div class="card-body">
            <form method="GET" class="row g-3">
                <div class="col-md-6">
                    <input type="text" class="form-control" name="search"
                           placeholder="Search organisations..."
                           value="<?= htmlspecialchars($_GET['search'] ?? '', ENT_QUOTES) ?>">
                </div>
                <div class="col-md-3">
                    <select class="form-select" name="type">
                        <option value="">All Types</option>
                        <?php foreach ($types as $type): ?>
                            <option value="<?= htmlspecialchars($type['organisation_type'], ENT_QUOTES) ?>"
                                    <?= (($_GET['type'] ?? '') === $type['organisation_type']) ? 'selected' : '' ?>>
                                <?= htmlspecialchars($type['organisation_type'], ENT_QUOTES) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="col-md-3">
                    <button type="submit" class="btn btn-secondary">
                        <i class="bi bi-search"></i> Search
                    </button>
                    <a href="list.php" class="btn btn-outline-secondary">
                        <i class="bi bi-arrow-counterclockwise"></i> Clear
                    </a>
                </div>
            </form>
        </div>
    </div>

    <div class="card">
        <div class="card-body">
            <table id="organisationsTable" class="table table-striped table-hover">
                <thead>
                <tr>
                    <th>ID</th>
                    <th>Organisation Name</th>
                    <th>Type</th>
                    <th>Contact Person</th>
                    <th>Email</th>
                    <th>Phone</th>
                    <th>Actions</th>
                </tr>
                </thead>
                <tbody>
                <?php foreach ($organisations as $org): ?>
                    <tr>
                        <td><?= $org['organisation_id'] ?></td>
                        <td><?= htmlspecialchars($org['name'], ENT_QUOTES) ?></td>
                        <td>
                                <span class="badge bg-info">
                                    <?= htmlspecialchars($org['organisation_type'], ENT_QUOTES) ?>
                                </span>
                        </td>
                        <td><?= htmlspecialchars($org['contact_person'] ?? 'N/A', ENT_QUOTES) ?></td>
                        <td>
                            <?php if (!empty($org['email'])): ?>
                                <a href="mailto:<?= htmlspecialchars($org['email'], ENT_QUOTES) ?>">
                                    <i class="bi bi-envelope"></i>
                                </a>
                            <?php else: ?>
                                N/A
                            <?php endif; ?>
                        </td>
                        <td><?= htmlspecialchars($org['phone'] ?? 'N/A', ENT_QUOTES) ?></td>
                        <td>
                            <div class="btn-group" role="group">
                                <a href="edit.php?id=<?= $org['organisation_id'] ?>"
                                   class="btn btn-sm btn-warning" title="Edit">
                                    <i class="bi bi-pencil"></i>
                                </a>
                                <button type="button"
                                        onclick="confirmDelete(<?= (int)$org['organisation_id'] ?>, '<?= htmlspecialchars($org['name'], ENT_QUOTES) ?>')"
                                        class="btn btn-sm btn-danger" title="Delete">
                                    <i class="bi bi-trash"></i>
                                </button>
                            </div>
                        </td>
                    </tr>
                <?php endforeach; ?>
                </tbody>
            </table>
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
<script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
<script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.datatables.net/1.13.6/js/dataTables.bootstrap5.min.js"></script>

<script>
    $(document).ready(function() {
        $('#organisationsTable').DataTable({
            "pageLength": 15,
            "order": [[0, 'asc']],
            "columnDefs": [
                { "orderable": false, "targets": 6 }
            ],
            "language": {
                "emptyTable": "No organisations found"
            }
        });
    });

    function confirmDelete(id, name) {
        document.getElementById('deleteOrgName').textContent = name;
        document.getElementById('deleteOrgId').value = id;
        new bootstrap.Modal(document.getElementById('deleteModal')).show();
    }
</script>
</body>
</html>
