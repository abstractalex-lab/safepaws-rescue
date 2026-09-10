<?php
// contact/list.php - Admin management page (login required)
require_once __DIR__ . '/../auth/authentication.php';
require_once __DIR__ . '/../connection.php';
require_once __DIR__ . '/../includes/csrf.php';
/** @var PDO $pdo */

$success_message = $_SESSION['success_message'] ?? null;
$error_message = $_SESSION['error_message'] ?? null;
unset($_SESSION['success_message'], $_SESSION['error_message']);

$status_filter = $_GET['filter'] ?? '';

$sql = "SELECT * FROM contact_messages";
$params = [];

if ($status_filter === 'replied') {
    $sql .= " WHERE replied = 1";
} elseif ($status_filter === 'unreplied') {
    $sql .= " WHERE replied = 0";
}

$sql .= " ORDER BY submitted_date DESC";

$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$messages = $stmt->fetchAll();

$total_stmt = $pdo->query("SELECT COUNT(*) FROM contact_messages");
$total_count = $total_stmt->fetchColumn();

$unreplied_stmt = $pdo->query("SELECT COUNT(*) FROM contact_messages WHERE replied = 0");
$unreplied_count = $unreplied_stmt->fetchColumn();

$replied_stmt = $pdo->query("SELECT COUNT(*) FROM contact_messages WHERE replied = 1");
$replied_count = $replied_stmt->fetchColumn();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Contact Messages - SafePaws</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.datatables.net/1.13.6/css/dataTables.bootstrap5.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
</head>
<body>
<?php include __DIR__ . '/../includes/navbar.php'; ?>

<div class="container mt-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2><i class="bi bi-envelope"></i> Contact Messages</h2>
    </div>

    <div class="row mb-4">
        <div class="col-md-4">
            <div class="card text-white bg-primary">
                <div class="card-body">
                    <h5 class="card-title">Total Messages</h5>
                    <p class="card-text display-6"><?= $total_count ?></p>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card text-white bg-warning">
                <div class="card-body">
                    <h5 class="card-title">Unreplied</h5>
                    <p class="card-text display-6"><?= $unreplied_count ?></p>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card text-white bg-success">
                <div class="card-body">
                    <h5 class="card-title">Replied</h5>
                    <p class="card-text display-6"><?= $replied_count ?></p>
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
                <div class="col-md-4">
                    <select class="form-select" name="filter">
                        <option value="">All Messages</option>
                        <option value="unreplied" <?= (($_GET['filter'] ?? '') === 'unreplied') ? 'selected' : '' ?>>Unreplied</option>
                        <option value="replied" <?= (($_GET['filter'] ?? '') === 'replied') ? 'selected' : '' ?>>Replied</option>
                    </select>
                </div>
                <div class="col-md-4">
                    <button type="submit" class="btn btn-secondary">
                        <i class="bi bi-filter"></i> Filter
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
            <table id="messagesTable" class="table table-striped table-hover">
                <thead>
                <tr>
                    <th>ID</th>
                    <th>Name</th>
                    <th>Email</th>
                    <th>Phone</th>
                    <th>Message</th>
                    <th>Submitted</th>
                    <th>Status</th>
                    <th>Actions</th>
                </tr>
                </thead>
                <tbody>
                <?php foreach ($messages as $message): ?>
                    <tr>
                        <td><?= $message['message_id'] ?></td>
                        <td><?= htmlspecialchars($message['name'], ENT_QUOTES) ?></td>
                        <td>
                            <a href="mailto:<?= htmlspecialchars($message['email'], ENT_QUOTES) ?>">
                                <i class="bi bi-envelope"></i> <?= htmlspecialchars($message['email'], ENT_QUOTES) ?>
                            </a>
                        </td>
                        <td><?= htmlspecialchars($message['phone'] ?? 'N/A', ENT_QUOTES) ?></td>
                        <td>
                            <button type="button" class="btn btn-sm btn-outline-info"
                                    data-bs-toggle="modal"
                                    data-bs-target="#viewMessageModal<?= $message['message_id'] ?>">
                                <i class="bi bi-eye"></i> View
                            </button>
                        </td>
                        <td><?= date('d/m/Y H:i', strtotime($message['submitted_date'])) ?></td>
                        <td>
                            <?php if ($message['replied']): ?>
                                <span class="badge bg-success">Replied</span>
                            <?php else: ?>
                                <span class="badge bg-warning">Unreplied</span>
                            <?php endif; ?>
                        </td>
                        <td>
                            <div class="btn-group" role="group">
                                <?php if (!$message['replied']): ?>
                                    <form method="POST" action="toggle_replied.php" class="d-inline">
                                        <?= csrf_field() ?>
                                        <input type="hidden" name="id" value="<?= $message['message_id'] ?>">
                                        <input type="hidden" name="action" value="mark_replied">
                                        <button type="submit" class="btn btn-sm btn-success" title="Mark as Replied">
                                            <i class="bi bi-check-circle"></i>
                                        </button>
                                    </form>
                                <?php else: ?>
                                    <form method="POST" action="toggle_replied.php" class="d-inline">
                                        <?= csrf_field() ?>
                                        <input type="hidden" name="id" value="<?= $message['message_id'] ?>">
                                        <input type="hidden" name="action" value="mark_unreplied">
                                        <button type="submit" class="btn btn-sm btn-warning" title="Mark as Unreplied">
                                            <i class="bi bi-arrow-counterclockwise"></i>
                                        </button>
                                    </form>
                                <?php endif; ?>
                                <button type="button"
                                        onclick="confirmDelete(<?= (int)$message['message_id'] ?>, '<?= htmlspecialchars($message['name'], ENT_QUOTES) ?>')"
                                        class="btn btn-sm btn-danger" title="Delete">
                                    <i class="bi bi-trash"></i>
                                </button>
                            </div>
                        </td>
                    </tr>
                <?php endforeach; ?>
                </tbody>
            </table>

            <?php foreach ($messages as $message): ?>
                <div class="modal fade" id="viewMessageModal<?= $message['message_id'] ?>" tabindex="-1">
                    <div class="modal-dialog">
                        <div class="modal-content">
                            <div class="modal-header">
                                <h5 class="modal-title">Message from <?= htmlspecialchars($message['name'], ENT_QUOTES) ?></h5>
                                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                            </div>
                            <div class="modal-body">
                                <dl class="row">
                                    <dt class="col-sm-3">Name</dt>
                                    <dd class="col-sm-9"><?= htmlspecialchars($message['name'], ENT_QUOTES) ?></dd>

                                    <dt class="col-sm-3">Email</dt>
                                    <dd class="col-sm-9">
                                        <a href="mailto:<?= htmlspecialchars($message['email'], ENT_QUOTES) ?>">
                                            <?= htmlspecialchars($message['email'], ENT_QUOTES) ?>
                                        </a>
                                    </dd>

                                    <dt class="col-sm-3">Phone</dt>
                                    <dd class="col-sm-9"><?= htmlspecialchars($message['phone'] ?? 'Not provided', ENT_QUOTES) ?></dd>

                                    <dt class="col-sm-3">Submitted</dt>
                                    <dd class="col-sm-9"><?= date('d/m/Y H:i', strtotime($message['submitted_date'])) ?></dd>

                                    <dt class="col-sm-3">Status</dt>
                                    <dd class="col-sm-9">
                                        <?php if ($message['replied']): ?>
                                            <span class="badge bg-success">Replied</span>
                                        <?php else: ?>
                                            <span class="badge bg-warning">Unreplied</span>
                                        <?php endif; ?>
                                    </dd>

                                    <dt class="col-sm-3">Message</dt>
                                    <dd class="col-sm-9"><?= nl2br(htmlspecialchars($message['message'], ENT_QUOTES)) ?></dd>
                                </dl>
                            </div>
                            <div class="modal-footer">
                                <?php if (!$message['replied']): ?>
                                    <form method="POST" action="toggle_replied.php" class="d-inline">
                                        <?= csrf_field() ?>
                                        <input type="hidden" name="id" value="<?= $message['message_id'] ?>">
                                        <input type="hidden" name="action" value="mark_replied">
                                        <button type="submit" class="btn btn-success">
                                            <i class="bi bi-check-circle"></i> Mark as Replied
                                        </button>
                                    </form>
                                <?php endif; ?>
                                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                            </div>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
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
                <p>Are you sure you want to delete the message from <strong id="deleteMessageName"></strong>?</p>
                <p class="text-danger"><i class="bi bi-exclamation-triangle"></i> This action cannot be undone.</p>
            </div>
            <form method="POST" action="delete.php">
                <?= csrf_field() ?>
                <input type="hidden" name="id" id="deleteMessageId">
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
        $('#messagesTable').DataTable({
            "pageLength": 15,
            "order": [[0, 'desc']],
            "columnDefs": [
                { "targets": [4, 7], "orderable": false, "searchable": false }
            ],
            "language": {
                "emptyTable": "No messages found"
            }
        });
    });

    function confirmDelete(id, name) {
        document.getElementById('deleteMessageName').textContent = name;
        document.getElementById('deleteMessageId').value = id;
        new bootstrap.Modal(document.getElementById('deleteModal')).show();
    }
</script>
</body>
</html>
