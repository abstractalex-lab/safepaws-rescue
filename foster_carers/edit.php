<?php
// foster_carers/edit.php
require_once __DIR__ . '/../auth/authentication.php';
require_once __DIR__ . '/../connection.php';
require_once __DIR__ . '/../includes/csrf.php';
require_once __DIR__ . '/../includes/foster_carer_constants.php';
require_once __DIR__ . '/../includes/foster_carer_validation.php';
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
} catch (PDOException $e) {
    error_log('Database error: ' . $e->getMessage());
    $_SESSION['error_message'] = 'Failed to load foster carer details.';
    header('Location: list.php');
    exit();
}

$errors = [];
$form_data = $carer;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!csrf_verify($_POST['csrf_token'] ?? null)) {
        $errors['database'] = 'Your session expired. Please try submitting the form again.';
    } else {
        $form_data = collect_foster_carer_input($_POST);
        $errors = validate_foster_carer_input($form_data);

        if (empty($errors)) {
            try {
                $sql = "UPDATE foster_carers 
                        SET first_name = :first_name, last_name = :last_name, email = :email, 
                            phone = :phone, suburb = :suburb, preferred_animal_type = :preferred_type, 
                            capacity = :capacity, status = :status, notes = :notes
                        WHERE foster_carer_id = :id";

                $stmt = $pdo->prepare($sql);
                $stmt->execute([
                        ':first_name' => $form_data['first_name'],
                        ':last_name' => $form_data['last_name'],
                        ':email' => $form_data['email'],
                        ':phone' => $form_data['phone'] ?: null,
                        ':suburb' => $form_data['suburb'] ?: null,
                        ':preferred_type' => $form_data['preferred_animal_type'] ?: null,
                        ':capacity' => $form_data['capacity'],
                        ':status' => $form_data['status'],
                        ':notes' => $form_data['notes'] ?: null,
                        ':id' => $foster_carer_id
                ]);

                $_SESSION['success_message'] = "Foster carer '{$form_data['first_name']} {$form_data['last_name']}' has been updated successfully!";
                header('Location: list.php');
                exit;

            } catch (PDOException $e) {
                if (($e->errorInfo[1] ?? null) == 1062) {
                    $errors['email'] = 'This email is already registered. Please use a different email.';
                } else {
                    $errors['database'] = 'Failed to update foster carer. Please try again.';
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
    <title>Edit Foster Carer - SafePaws</title>
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
                    <h3><i class="bi bi-person-gear"></i> Edit Foster Carer</h3>
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
                            <label for="email" class="form-label">Email Address <span class="text-danger">*</span></label>
                            <input type="email" class="form-control <?= isset($errors['email']) ? 'is-invalid' : '' ?>"
                                   id="email" name="email" value="<?= htmlspecialchars($form_data['email'] ?? '', ENT_QUOTES) ?>"
                                   required maxlength="255">
                            <?php if (isset($errors['email'])): ?>
                                <div class="invalid-feedback"><?= htmlspecialchars($errors['email'], ENT_QUOTES) ?></div>
                            <?php endif; ?>
                        </div>

                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="phone" class="form-label">Phone Number</label>
                                    <input type="tel" class="form-control" id="phone" name="phone"
                                           value="<?= htmlspecialchars($form_data['phone'] ?? '', ENT_QUOTES) ?>" maxlength="50">
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="suburb" class="form-label">Suburb</label>
                                    <input type="text" class="form-control" id="suburb" name="suburb"
                                           value="<?= htmlspecialchars($form_data['suburb'] ?? '', ENT_QUOTES) ?>" maxlength="100">
                                </div>
                            </div>
                        </div>

                        <div class="mb-3">
                            <label for="preferred_animal_type" class="form-label">Preferred Animal Type</label>
                            <select class="form-select <?= isset($errors['preferred_animal_type']) ? 'is-invalid' : '' ?>"
                                    id="preferred_animal_type" name="preferred_animal_type">
                                <option value="">Any</option>
                                <?php foreach (FOSTER_ANIMAL_TYPES as $type): ?>
                                    <option value="<?= htmlspecialchars($type, ENT_QUOTES) ?>"
                                            <?= (($form_data['preferred_animal_type'] ?? '') === $type) ? 'selected' : '' ?>>
                                        <?= htmlspecialchars($type . 's', ENT_QUOTES) ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                            <?php if (isset($errors['preferred_animal_type'])): ?>
                                <div class="invalid-feedback"><?= htmlspecialchars($errors['preferred_animal_type'], ENT_QUOTES) ?></div>
                            <?php endif; ?>
                        </div>

                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="capacity" class="form-label">Fostering Capacity</label>
                                    <input type="number" class="form-control <?= isset($errors['capacity']) ? 'is-invalid' : '' ?>"
                                           id="capacity" name="capacity" value="<?= htmlspecialchars((string)($form_data['capacity'] ?? 1), ENT_QUOTES) ?>"
                                           min="1" max="10">
                                    <?php if (isset($errors['capacity'])): ?>
                                        <div class="invalid-feedback"><?= htmlspecialchars($errors['capacity'], ENT_QUOTES) ?></div>
                                    <?php endif; ?>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="status" class="form-label">Status</label>
                                    <select class="form-select <?= isset($errors['status']) ? 'is-invalid' : '' ?>"
                                            id="status" name="status">
                                        <option value="active" <?= ($form_data['status'] === 'active') ? 'selected' : '' ?>>Active</option>
                                        <option value="inactive" <?= ($form_data['status'] === 'inactive') ? 'selected' : '' ?>>Inactive</option>
                                    </select>
                                    <?php if (isset($errors['status'])): ?>
                                        <div class="invalid-feedback"><?= htmlspecialchars($errors['status'], ENT_QUOTES) ?></div>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>

                        <div class="mb-3">
                            <label for="notes" class="form-label">Notes</label>
                            <textarea class="form-control" id="notes" name="notes" rows="3"><?= htmlspecialchars($form_data['notes'] ?? '', ENT_QUOTES) ?></textarea>
                        </div>

                        <div class="d-flex justify-content-between">
                            <a href="list.php" class="btn btn-secondary">
                                <i class="bi bi-arrow-left"></i> Cancel
                            </a>
                            <button type="submit" class="btn btn-primary">
                                <i class="bi bi-save"></i> Update Foster Carer
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
