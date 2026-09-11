<?php
/**
 * Add system user (admin).
 *
 * Creates a staff login for the administration area. Passwords are hashed
 * with password_hash() before storage and never held in plain text.
 *
 * Username uniqueness is checked before the insert for a field-level
 * error, and the unique constraint (error 1062) is caught as a backstop
 * against two admins submitting the same username at once.
 *
 * @var PDO $pdo
 */

// Include necessary files
require_once __DIR__ . '/../auth/authentication.php';
require_once __DIR__ . '/../connection.php';
require_once __DIR__ . '/../includes/csrf.php';

$errors = [];
$form_data = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!csrf_verify($_POST['csrf_token'] ?? null)) {
        $errors['database'] = 'Your session expired. Please try submitting the form again.';
    } else {
        $form_data = [
                'first_name' => trim($_POST['first_name'] ?? ''),
                'last_name' => trim($_POST['last_name'] ?? ''),
                'username' => trim($_POST['username'] ?? ''),
                'password' => $_POST['password'] ?? '',
                'confirm_password' => $_POST['confirm_password'] ?? '',
                'status' => $_POST['status'] ?? 'active'
        ];

        if (empty($form_data['first_name'])) {
            $errors['first_name'] = 'First name is required';
        }
        if (empty($form_data['last_name'])) {
            $errors['last_name'] = 'Last name is required';
        }
        if (empty($form_data['username'])) {
            $errors['username'] = 'Username is required';
        } elseif (!preg_match('/^[a-zA-Z0-9_]{3,50}$/', $form_data['username'])) {
            $errors['username'] = 'Username must be 3-50 characters (letters, numbers, underscore only)';
        }
        if (empty($form_data['password'])) {
            $errors['password'] = 'Password is required';
        } elseif (!preg_match('/^(?=.*\d)(?=.*[\W_]).{8,}$/', $form_data['password'])) {
            $errors['password'] = 'Password must be at least 8 characters and include a number and a special character';
        }
        if ($form_data['password'] !== $form_data['confirm_password']) {
            $errors['confirm_password'] = 'Passwords do not match';
        }
        if (!in_array($form_data['status'], ['active', 'inactive'], true)) {
            $errors['status'] = 'Invalid status';
        }

        if (empty($errors['username'])) {
            try {
                $check_stmt = $pdo->prepare("SELECT COUNT(*) FROM users WHERE username = :username");
                $check_stmt->execute([':username' => $form_data['username']]);
                if ($check_stmt->fetchColumn() > 0) {
                    $errors['username'] = 'This username is already taken. Please choose another.';
                }
            } catch (PDOException $e) {
                $errors['database'] = 'Failed to check username availability.';
                error_log('Database error: ' . $e->getMessage());
            }
        }

        if (empty($errors)) {
            try {
                $hashed_password = password_hash($form_data['password'], PASSWORD_DEFAULT);

                $sql = "INSERT INTO users (first_name, last_name, username, password, status) 
                        VALUES (:first_name, :last_name, :username, :password, :status)";

                $stmt = $pdo->prepare($sql);
                $stmt->execute([
                        ':first_name' => $form_data['first_name'],
                        ':last_name' => $form_data['last_name'],
                        ':username' => $form_data['username'],
                        ':password' => $hashed_password,
                        ':status' => $form_data['status']
                ]);

                $_SESSION['success_message'] = "User '{$form_data['username']}' has been added successfully!";
                header('Location: list.php');
                exit;

            } catch (PDOException $e) {
                if (($e->errorInfo[1] ?? null) == 1062) {
                    $errors['username'] = 'This username is already taken.';
                } else {
                    $errors['database'] = 'Failed to add user. Please try again.';
                    error_log('Database error: ' . $e->getMessage());
                }
            }
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Add User - SafePaws</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
</head>
<body>
<?php include __DIR__ . '/../includes/navbar.php'; ?>

<div class="container mt-4">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card">
                <div class="card-header">
                    <h3><i class="bi bi-person-plus"></i> Add New User</h3>
                </div>
                <div class="card-body">
                    <?php if (!empty($errors['database'])): ?>
                        <div class="alert alert-danger">
                            <i class="bi bi-exclamation-triangle"></i> <?= htmlspecialchars($errors['database'], ENT_QUOTES) ?>
                        </div>
                    <?php endif; ?>

                    <form method="POST" action="" novalidate>
                        <?= csrf_field() ?>

                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="first_name" class="form-label">First Name <span class="text-danger">*</span></label>
                                    <input type="text" class="form-control <?= isset($errors['first_name']) ? 'is-invalid' : '' ?>"
                                           id="first_name" name="first_name" value="<?= htmlspecialchars($form_data['first_name'] ?? '', ENT_QUOTES) ?>"
                                           required maxlength="100">
                                    <?php if (isset($errors['first_name'])): ?>
                                        <div class="invalid-feedback"><?= htmlspecialchars($errors['first_name'], ENT_QUOTES) ?></div>
                                    <?php endif; ?>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="last_name" class="form-label">Last Name <span class="text-danger">*</span></label>
                                    <input type="text" class="form-control <?= isset($errors['last_name']) ? 'is-invalid' : '' ?>"
                                           id="last_name" name="last_name" value="<?= htmlspecialchars($form_data['last_name'] ?? '', ENT_QUOTES) ?>"
                                           required maxlength="100">
                                    <?php if (isset($errors['last_name'])): ?>
                                        <div class="invalid-feedback"><?= htmlspecialchars($errors['last_name'], ENT_QUOTES) ?></div>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>

                        <div class="mb-3">
                            <label for="username" class="form-label">Username <span class="text-danger">*</span></label>
                            <input type="text" class="form-control <?= isset($errors['username']) ? 'is-invalid' : '' ?>"
                                   id="username" name="username" value="<?= htmlspecialchars($form_data['username'] ?? '', ENT_QUOTES) ?>"
                                   required maxlength="50" pattern="[a-zA-Z0-9_]{3,50}">
                            <div class="form-text">3-50 characters. Letters, numbers, and underscores only.</div>
                            <?php if (isset($errors['username'])): ?>
                                <div class="invalid-feedback"><?= htmlspecialchars($errors['username'], ENT_QUOTES) ?></div>
                            <?php endif; ?>
                        </div>

                        <div class="mb-3">
                            <label for="password" class="form-label">Password <span class="text-danger">*</span></label>
                            <input type="password" class="form-control <?= isset($errors['password']) ? 'is-invalid' : '' ?>"
                                   id="password" name="password" required minlength="8" maxlength="72">
                            <div class="form-text">Minimum 8 characters, including a number and a special character.</div>
                            <?php if (isset($errors['password'])): ?>
                                <div class="invalid-feedback"><?= htmlspecialchars($errors['password'], ENT_QUOTES) ?></div>
                            <?php endif; ?>
                        </div>

                        <div class="mb-3">
                            <label for="confirm_password" class="form-label">Confirm Password <span class="text-danger">*</span></label>
                            <input type="password" class="form-control <?= isset($errors['confirm_password']) ? 'is-invalid' : '' ?>"
                                   id="confirm_password" name="confirm_password" required minlength="8" maxlength="72">
                            <?php if (isset($errors['confirm_password'])): ?>
                                <div class="invalid-feedback"><?= htmlspecialchars($errors['confirm_password'], ENT_QUOTES) ?></div>
                            <?php endif; ?>
                        </div>

                        <div class="mb-3">
                            <label for="status" class="form-label">Status</label>
                            <select class="form-select <?= isset($errors['status']) ? 'is-invalid' : '' ?>"
                                    id="status" name="status">
                                <option value="active" <?= (($form_data['status'] ?? 'active') === 'active') ? 'selected' : '' ?>>Active</option>
                                <option value="inactive" <?= (($form_data['status'] ?? '') === 'inactive') ? 'selected' : '' ?>>Inactive</option>
                            </select>
                            <?php if (isset($errors['status'])): ?>
                                <div class="invalid-feedback"><?= htmlspecialchars($errors['status'], ENT_QUOTES) ?></div>
                            <?php endif; ?>
                        </div>

                        <div class="d-flex justify-content-between">
                            <a href="list.php" class="btn btn-secondary">
                                <i class="bi bi-arrow-left"></i> Cancel
                            </a>
                            <button type="submit" class="btn btn-primary">
                                <i class="bi bi-person-plus"></i> Add User
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
