<?php
/**
 * Foster carers list (admin).
 *
 * Server-side search across name and email plus a status filter, with
 * DataTables layered on top for client-side sorting and paging.
 *
 * Status toggle and delete are both POST forms with a CSRF token, so
 * neither can be triggered by a stray GET request.
 *
 * @var PDO $pdo
 */

// Include necessary files
require_once __DIR__ . '/../auth/authentication.php';
require_once __DIR__ . '/../connection.php';
require_once __DIR__ . '/../includes/csrf.php';

$success_message = $_SESSION['success_message'] ?? null;
$error_message = $_SESSION['error_message'] ?? null;
unset($_SESSION['success_message'], $_SESSION['error_message']);

$search = isset($_GET['search']) ? '%' . $_GET['search'] . '%' : '%';
$status_filter = $_GET['status'] ?? '';

$sql = "SELECT * FROM foster_carers WHERE 
        (first_name LIKE :search OR last_name LIKE :search OR email LIKE :search)";
$params = [':search' => $search];

if (!empty($status_filter)) {
    $sql .= " AND status = :status";
    $params[':status'] = $status_filter;
}

$sql .= " ORDER BY last_name ASC, first_name ASC";
$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$foster_carers = $stmt->fetchAll();

$active_stmt = $pdo->query("SELECT COUNT(*) FROM foster_carers WHERE status = 'active'");
$active_count = $active_stmt->fetchColumn();

$total_stmt = $pdo->query("SELECT COUNT(*) FROM foster_carers");
$total_count = $total_stmt->fetchColumn();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Foster Carers - SafePaws</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.datatables.net/1.13.6/css/dataTables.bootstrap5.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
</head>
<body>
<?php include __DIR__ . '/../includes/navbar.php'; ?>

<div class="container mt-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2><i class="bi bi-people"></i> Foster Carers</h2>
        <a href="add.php" class="btn btn-primary">
            <i class="bi bi-plus-circle"></i> Add New Foster Carer
        </a>
    </div>

    <div class="row mb-4">
        <div class="col-md-3">
            <div class="card text-white bg-primary">
                <div class="card-body">
                    <h5 class="card-title">Total Carers</h5>
                    <p class="card-text display-6"><?= $total_count ?></p>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card text-white bg-success">
                <div class="card-body">
                    <h5 class="card-title">Active Carers</h5>
                    <p class="card-text display-6"><?= $active_count ?></p>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card text-white bg-warning">
                <div class="card-body">
                    <h5 class="card-title">Inactive Carers</h5>
                    <p class="card-text display-6"><?= $total_count - $active_count ?></p>
                </div>
            </div>
        </div>
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
                <div class="col-md-5">
                    <input type="text" class="form-control" name="search"
                           placeholder="Search by name or email..."
                           value="<?= htmlspecialchars($_GET['search'] ?? '', ENT_QUOTES) ?>">
                </div>
                <div class="col-md-3">
                    <select class="form-select" name="status">
                        <option value="">All Statuses</option>
                        <option value="active" <?= (($_GET['status'] ?? '') === 'active') ? 'selected' : '' ?>>Active</option>
                        <option value="inactive" <?= (($_GET['status'] ?? '') === 'inactive') ? 'selected' : '' ?>>Inactive</option>
                    </select>
                </div>
                <div class="col-md-4">
                    <button type="submit" class="btn btn-secondary">
                        <i class="bi bi-search"></i> Search
                    </button>
                    <a href="index.php" class="btn btn-outline-secondary">
                        <i class="bi bi-arrow-counterclockwise"></i> Clear
                    </a>
                </div>
            </form>
        </div>
    </div>

    <div class="card">
        <div class="card-body">
            <table id="fosterCarersTable" class="table table-striped table-hover">
                <thead>
                <tr>
                    <th>ID</th>
                    <th>Name</th>
                    <th>Email</th>
                    <th>Phone</th>
                    <th>Suburb</th>
                    <th>Preferred Type</th>
                    <th>Capacity</th>
                    <th>Status</th>
                    <th>Actions</th>
                </tr>
                </thead>
                <tbody>
                <?php foreach ($foster_carers as $carer): ?>
                    <tr>
                        <td><?= $carer['foster_carer_id'] ?></td>
                        <td><?= htmlspecialchars($carer['first_name'] . ' ' . $carer['last_name'], ENT_QUOTES) ?></td>
                        <td>
                            <a href="mailto:<?= htmlspecialchars($carer['email'], ENT_QUOTES) ?>">
                                <i class="bi bi-envelope"></i>
                            </a>
                        </td>
                        <td><?= htmlspecialchars($carer['phone'] ?? 'N/A', ENT_QUOTES) ?></td>
                        <td><?= htmlspecialchars($carer['suburb'] ?? 'N/A', ENT_QUOTES) ?></td>
                        <td><?= htmlspecialchars($carer['preferred_animal_type'] ?? 'Any', ENT_QUOTES) ?></td>
                        <td><?= $carer['capacity'] ?></td>
                        <td>
                            <?php if ($carer['status'] == 'active'): ?>
                                <span class="badge bg-success">Active</span>
                            <?php else: ?>
                                <span class="badge bg-secondary">Inactive</span>
                            <?php endif; ?>
                        </td>
                        <td>
                            <div class="btn-group" role="group">
                                <a href="view.php?id=<?= $carer['foster_carer_id'] ?>"
                                   class="btn btn-sm btn-info" title="View">
                                    <i class="bi bi-eye"></i>
                                </a>
                                <a href="edit.php?id=<?= $carer['foster_carer_id'] ?>"
                                   class="btn btn-sm btn-warning" title="Edit">
                                    <i class="bi bi-pencil"></i>
                                </a>
                                <form method="POST" action="toggle_status.php" class="d-inline">
                                    <?= csrf_field() ?>
                                    <input type="hidden" name="id" value="<?= $carer['foster_carer_id'] ?>">
                                    <button type="submit"
                                            class="btn btn-sm <?= $carer['status'] == 'active' ? 'btn-secondary' : 'btn-success' ?>"
                                            title="<?= $carer['status'] == 'active' ? 'Deactivate' : 'Activate' ?>">
                                        <i class="bi <?= $carer['status'] == 'active' ? 'bi-pause-circle' : 'bi-play-circle' ?>"></i>
                                    </button>
                                </form>
                                <button type="button"
                                        onclick="confirmDelete(<?= (int)$carer['foster_carer_id'] ?>, '<?= htmlspecialchars($carer['first_name'] . ' ' . $carer['last_name'], ENT_QUOTES) ?>')"
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
                <p>Are you sure you want to delete foster carer <strong id="deleteCarerName"></strong>?</p>
                <p class="text-danger"><i class="bi bi-exclamation-triangle"></i> This action cannot be undone.</p>
            </div>
            <form method="POST" action="delete.php">
                <?= csrf_field() ?>
                <input type="hidden" name="id" id="deleteCarerId">
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
        $('#fosterCarersTable').DataTable({
            "pageLength": 15,
            "order": [[0, 'asc']],
            "columnDefs": [
                { "targets": [8], "orderable": false, "searchable": false }
            ],
            "language": {
                "emptyTable": "No foster carers found"
            }
        });
    });

    function confirmDelete(id, name) {
        document.getElementById('deleteCarerName').textContent = name;
        document.getElementById('deleteCarerId').value = id;
        new bootstrap.Modal(document.getElementById('deleteModal')).show();
    }
</script>
</body>
</html>
