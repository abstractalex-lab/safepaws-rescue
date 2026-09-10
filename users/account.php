<?php
/**
 * My account (admin).
 *
 * Read-only view of the signed-in user's own staff account.
 * Changes are made through users/edit.php, which this page links to.
 *
 * The user is always taken from the session rather than a query string,
 * so this page can only ever show the person who is signed in.
 *
 * @var PDO $pdo
 */

// Start session and include necessary files
session_start();
require_once __DIR__ . '/../auth/authentication.php';
require_once __DIR__ . '/../connection.php';

// Lookup to fetch user details
$stmt = $pdo->prepare(
    "SELECT user_id, first_name, last_name, username, status
     FROM users
     WHERE user_id = ?"
);
$stmt->execute([$_SESSION['user_id']]);
$user = $stmt->fetch();

if (!$user) {
    // If the user is not found (which shouldn't happen), redirect to dashboard
    header("Location: ../dashboard.php");
    exit;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Account - SafePaws Admin</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
</head>
<body>
<?php include __DIR__ . '/../includes/navbar.php'; ?>

<div class="container mt-4">
    <div class="row justify-content-center">
        <div class="col-md-7">

            <h2 class="mb-4"><i class="bi bi-person-circle"></i> My Account</h2>

            <div class="card">
                <div class="card-header"><i class="bi bi-person"></i> Account Details</div>
                <div class="card-body">
                    <table class="table table-sm mb-0">
                        <tr>
                            <th style="width:35%;">First name</th>
                            <td><?= htmlspecialchars($user['first_name']) ?></td>
                        </tr>
                        <tr>
                            <th>Last name</th>
                            <td><?= htmlspecialchars($user['last_name']) ?></td>
                        </tr>
                        <tr>
                            <th>Username</th>
                            <td><?= htmlspecialchars($user['username']) ?></td>
                        </tr>
                        <tr>
                            <th>Password</th>
                            <td><span class="text-muted">Hidden</span></td>
                        </tr>
                        <tr>
                            <th>Status</th>
                            <td>
                                <?php if ($user['status'] === 'active'): ?>
                                    <span class="badge bg-success">Active</span>
                                <?php else: ?>
                                    <span class="badge bg-secondary">Inactive</span>
                                <?php endif; ?>
                            </td>
                        </tr>
                    </table>
                </div>
                <div class="card-footer d-flex justify-content-end">
                    <a href="edit.php?id=<?= $user['user_id'] ?>" class="btn btn-primary">
                        <i class="bi bi-pencil"></i> Edit My Details
                    </a>
                </div>
            </div>

            <div class="mt-4 mb-5">
                <a href="../dashboard.php" class="btn btn-outline-secondary">
                    <i class="bi bi-arrow-left"></i> Back to Dashboard
                </a>
            </div>

        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>