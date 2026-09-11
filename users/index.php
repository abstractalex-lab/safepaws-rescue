<?php
/**
 * System users list (admin).
 *
 * Server-side search across name and username plus a status filter, with
 * DataTables layered on top for client-side sorting and paging.
 *
 * The signed-in user's own row shows no toggle or delete action, so an
 * admin can't deactivate or remove the account they're using. Password
 * hashes are never rendered.
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
$status_filter = isset($_GET['status']) ? $_GET['status'] : '';

$sql = "SELECT * FROM users WHERE 
        (first_name LIKE :search OR last_name LIKE :search OR username LIKE :search)";
$params = [':search' => $search];

if (!empty($status_filter)) {
    $sql .= " AND status = :status";
    $params[':status'] = $status_filter;
}

$sql .= " ORDER BY last_name ASC, first_name ASC";
$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$users = $stmt->fetchAll();

$total_stmt = $pdo->query("SELECT COUNT(*) FROM users");
$total_count = $total_stmt->fetchColumn();

$active_stmt = $pdo->query("SELECT COUNT(*) FROM users WHERE status = 'active'");
$active_count = $active_stmt->fetchColumn();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Users - SafePaws</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.datatables.net/1.13.6/css/dataTables.bootstrap5.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
</head>
<body>
<?php include __DIR__ . '/../includes/navbar.php'; ?>

<div class="container mt-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2><i class="bi bi-person-gear"></i> System Users</h2>
        <a href="add.php" class="btn btn-primary">
            <i class="bi bi-person-plus"></i> Add New User
        </a>
    </div>

    <div class="row mb-4">
        <div class="col-md-4">
            <div class="card text-white bg-primary">
                <div class="card-body">
                    <h5 class="card-title">Total Users</h5>
                    <p class="card-text display-6"><?= $total_count ?></p>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card text-white bg-success">
                <div class="card-body">
                    <h5 class="card-title">Active Users</h5>
                    <p class="card-text display-6"><?= $active_count ?></p>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card text-white bg-secondary">
                <div class="card-body">
                    <h5 class="card-title">Inactive Users</h5>
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
                           placeholder="Search by name or username..."
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
            <table id="usersTable" class="table table-striped table-hover">
                <thead>
                <tr>
                    <th>ID</th>
                    <th>Full Name</th>
                    <th>Username</th>
                    <th>Status</th>
                    <th>Actions</th>
                </tr>
                </thead>
                <tbody>
                <?php foreach ($users as $user): ?>
                    <tr>
                        <td><?= $user['user_id'] ?></td>
                        <td><?= htmlspecialchars($user['first_name'] . ' ' . $user['last_name'], ENT_QUOTES) ?></td>
                        <td>
                            <i class="bi bi-person-circle"></i>
                            <?= htmlspecialchars($user['username'], ENT_QUOTES) ?>
                        </td>
                        <td>
                            <?php if ($user['status'] == 'active'): ?>
                                <span class="badge bg-success">Active</span>
                            <?php else: ?>
                                <span class="badge bg-secondary">Inactive</span>
                            <?php endif; ?>
                        </td>
                        <td>
                            <div class="btn-group" role="group">
                                <a href="edit.php?id=<?= $user['user_id'] ?>"
                                   class="btn btn-sm btn-warning" title="Edit">
                                    <i class="bi bi-pencil"></i>
                                </a>
                                <?php if ((int)$user['user_id'] === (int)$_SESSION['user_id']): ?>
                                    <span class="btn btn-sm btn-outline-secondary disabled" title="This is your own account">
                                        <i class="bi bi-person-check"></i> You
                                    </span>
                                <?php else: ?>
                                    <form method="POST" action="toggle_status.php" class="d-inline">
                                        <?= csrf_field() ?>
                                        <input type="hidden" name="id" value="<?= $user['user_id'] ?>">
                                        <button type="submit"
                                                class="btn btn-sm <?= $user['status'] == 'active' ? 'btn-secondary' : 'btn-success' ?>"
                                                title="<?= $user['status'] == 'active' ? 'Deactivate' : 'Activate' ?>">
                                            <i class="bi <?= $user['status'] == 'active' ? 'bi-pause-circle' : 'bi-play-circle' ?>"></i>
                                        </button>
                                    </form>
                                    <button type="button"
                                            onclick="confirmDelete(<?= (int)$user['user_id'] ?>, '<?= htmlspecialchars($user['username'], ENT_QUOTES) ?>')"
                                            class="btn btn-sm btn-danger" title="Delete">
                                        <i class="bi bi-trash"></i>
                                    </button>
                                <?php endif; ?>
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
                <p>Are you sure you want to delete user <strong id="deleteUserName"></strong>?</p>
                <p class="text-danger"><i class="bi bi-exclamation-triangle"></i> This action cannot be undone.</p>
            </div>
            <form method="POST" action="delete.php">
                <?= csrf_field() ?>
                <input type="hidden" name="id" id="deleteUserId">
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
        $('#usersTable').DataTable({
            "pageLength": 15,
            "order": [[0, 'asc']],
            "columnDefs": [
                { "targets": [4], "orderable": false, "searchable": false }
            ],
            "language": {
                "emptyTable": "No users found"
            }
        });
    });

    function confirmDelete(id, username) {
        document.getElementById('deleteUserName').textContent = username;
        document.getElementById('deleteUserId').value = id;
        new bootstrap.Modal(document.getElementById('deleteModal')).show();
    }
</script>
</body>
</html>
