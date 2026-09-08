<?php
// foster_carers/add.php
require_once __DIR__ . '/../auth/authentication.php';
require_once __DIR__ . '/../connection.php';
/** @var PDO $pdo */

$errors = [];
$form_data = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $form_data = [
        'first_name' => trim($_POST['first_name'] ?? ''),
        'last_name' => trim($_POST['last_name'] ?? ''),
        'email' => trim($_POST['email'] ?? ''),
        'phone' => trim($_POST['phone'] ?? ''),
        'suburb' => trim($_POST['suburb'] ?? ''),
        'preferred_animal_type' => trim($_POST['preferred_animal_type'] ?? ''),
        'capacity' => (int)($_POST['capacity'] ?? 1),
        'status' => $_POST['status'] ?? 'active',
        'notes' => trim($_POST['notes'] ?? '')
    ];

    if (empty($form_data['first_name'])) {
        $errors['first_name'] = 'First name is required';
    }
    if (empty($form_data['last_name'])) {
        $errors['last_name'] = 'Last name is required';
    }
    if (empty($form_data['email'])) {
        $errors['email'] = 'Email address is required';
    } elseif (!filter_var($form_data['email'], FILTER_VALIDATE_EMAIL)) {
        $errors['email'] = 'Please enter a valid email address';
    }
    if ($form_data['capacity'] < 1) {
        $errors['capacity'] = 'Capacity must be at least 1';
    }
    if (!in_array($form_data['status'], ['active', 'inactive'])) {
        $errors['status'] = 'Invalid status';
    }

    if (empty($errors)) {
        try {
            $sql = "INSERT INTO foster_carers 
                    (first_name, last_name, email, phone, suburb, preferred_animal_type, capacity, status, notes) 
                    VALUES (:first_name, :last_name, :email, :phone, :suburb, :preferred_type, :capacity, :status, :notes)";

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
                ':notes' => $form_data['notes'] ?: null
            ]);

            $_SESSION['success_message'] = "Foster carer '{$form_data['first_name']} {$form_data['last_name']}' has been added successfully!";
            header('Location: list.php');
            exit;

        } catch (PDOException $e) {
            if ($e->errorInfo[1] == 1062) {
                $errors['email'] = 'This email is already registered. Please use a different email.';
            } else {
                $errors['database'] = 'Failed to add foster carer. Please try again.';
                error_log('Database error: ' . $e->getMessage());
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
    <title>Add Foster Carer - SafePaws</title>
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
                    <h3><i class="bi bi-person-plus"></i> Add New Foster Carer</h3>
                </div>
                <div class="card-body">
                    <?php if (!empty($errors['database'])): ?>
                        <div class="alert alert-danger">
                            <i class="bi bi-exclamation-triangle"></i> <?= htmlspecialchars($errors['database']) ?>
                        </div>
                    <?php endif; ?>

                    <form method="POST" action="" novalidate>
                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="first_name" class="form-label">First Name <span class="text-danger">*</span></label>
                                    <input type="text" class="form-control <?= isset($errors['first_name']) ? 'is-invalid' : '' ?>"
                                           id="first_name" name="first_name" value="<?= htmlspecialchars($form_data['first_name'] ?? '') ?>"
                                           required maxlength="100">
                                    <?php if (isset($errors['first_name'])): ?>
                                        <div class="invalid-feedback"><?= htmlspecialchars($errors['first_name']) ?></div>
                                    <?php endif; ?>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="last_name" class="form-label">Last Name <span class="text-danger">*</span></label>
                                    <input type="text" class="form-control <?= isset($errors['last_name']) ? 'is-invalid' : '' ?>"
                                           id="last_name" name="last_name" value="<?= htmlspecialchars($form_data['last_name'] ?? '') ?>"
                                           required maxlength="100">
                                    <?php if (isset($errors['last_name'])): ?>
                                        <div class="invalid-feedback"><?= htmlspecialchars($errors['last_name']) ?></div>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>

                        <div class="mb-3">
                            <label for="email" class="form-label">Email Address <span class="text-danger">*</span></label>
                            <input type="email" class="form-control <?= isset($errors['email']) ? 'is-invalid' : '' ?>"
                                   id="email" name="email" value="<?= htmlspecialchars($form_data['email'] ?? '') ?>"
                                   required maxlength="255">
                            <?php if (isset($errors['email'])): ?>
                                <div class="invalid-feedback"><?= htmlspecialchars($errors['email']) ?></div>
                            <?php endif; ?>
                        </div>

                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="phone" class="form-label">Phone Number</label>
                                    <input type="tel" class="form-control" id="phone" name="phone"
                                           value="<?= htmlspecialchars($form_data['phone'] ?? '') ?>" maxlength="50">
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="suburb" class="form-label">Suburb</label>
                                    <input type="text" class="form-control" id="suburb" name="suburb"
                                           value="<?= htmlspecialchars($form_data['suburb'] ?? '') ?>" maxlength="100">
                                </div>
                            </div>
                        </div>

                        <div class="mb-3">
                            <label for="preferred_animal_type" class="form-label">Preferred Animal Type</label>
                            <select class="form-select" id="preferred_animal_type" name="preferred_animal_type">
                                <option value="">Any</option>
                                <option value="Dog" <?= (isset($form_data['preferred_animal_type']) && $form_data['preferred_animal_type'] == 'Dog') ? 'selected' : '' ?>>Dogs</option>
                                <option value="Cat" <?= (isset($form_data['preferred_animal_type']) && $form_data['preferred_animal_type'] == 'Cat') ? 'selected' : '' ?>>Cats</option>
                                <option value="Rabbit" <?= (isset($form_data['preferred_animal_type']) && $form_data['preferred_animal_type'] == 'Rabbit') ? 'selected' : '' ?>>Rabbits</option>
                                <option value="Small Animal" <?= (isset($form_data['preferred_animal_type']) && $form_data['preferred_animal_type'] == 'Small Animal') ? 'selected' : '' ?>>Small Animals</option>
                                <option value="Other" <?= (isset($form_data['preferred_animal_type']) && $form_data['preferred_animal_type'] == 'Other') ? 'selected' : '' ?>>Other</option>
                            </select>
                        </div>

                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="capacity" class="form-label">Fostering Capacity</label>
                                    <input type="number" class="form-control <?= isset($errors['capacity']) ? 'is-invalid' : '' ?>"
                                           id="capacity" name="capacity" value="<?= htmlspecialchars($form_data['capacity'] ?? 1) ?>"
                                           min="1" max="10">
                                    <?php if (isset($errors['capacity'])): ?>
                                        <div class="invalid-feedback"><?= htmlspecialchars($errors['capacity']) ?></div>
                                    <?php endif; ?>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="status" class="form-label">Status</label>
                                    <select class="form-select <?= isset($errors['status']) ? 'is-invalid' : '' ?>"
                                            id="status" name="status">
                                        <option value="active" <?= (isset($form_data['status']) && $form_data['status'] == 'active') ? 'selected' : '' ?>>Active</option>
                                        <option value="inactive" <?= (isset($form_data['status']) && $form_data['status'] == 'inactive') ? 'selected' : '' ?>>Inactive</option>
                                    </select>
                                    <?php if (isset($errors['status'])): ?>
                                        <div class="invalid-feedback"><?= htmlspecialchars($errors['status']) ?></div>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>

                        <div class="mb-3">
                            <label for="notes" class="form-label">Notes</label>
                            <textarea class="form-control" id="notes" name="notes" rows="3"><?= htmlspecialchars($form_data['notes'] ?? '') ?></textarea>
                        </div>

                        <div class="d-flex justify-content-between">
                            <a href="list.php" class="btn btn-secondary">
                                <i class="bi bi-arrow-left"></i> Cancel
                            </a>
                            <button type="submit" class="btn btn-primary">
                                <i class="bi bi-plus-circle"></i> Add Foster Carer
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
