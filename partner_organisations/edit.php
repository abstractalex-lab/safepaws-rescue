<?php
// partner_organisations/edit.php
require_once __DIR__ . '/../auth/authentication.php';
require_once __DIR__ . '/../connection.php';
/** @var PDO $pdo */

$organisation_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

if ($organisation_id <= 0) {
    header('Location: list.php');
    exit();
}

// fetch existing organisation data
try {
    $stmt = $pdo->prepare("SELECT * FROM partner_organisations WHERE organisation_id = :id");
    $stmt->execute([':id' => $organisation_id]);
    $organisation = $stmt->fetch();

    if (!$organisation) {
        $_SESSION['error_message'] = 'Organisation not found.';
        header('Location: list.php');
        exit();
    }
} catch (PDOException $e) {
    error_log('Database error: ' . $e->getMessage());
    $_SESSION['error_message'] = 'Failed to load organisation details.';
    header('Location: list.php');
    exit();
}

$errors = [];
$form_data = $organisation; // pre-fill with existing data

// handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // sanitize and validate inputs
    $form_data = [
        'name' => trim($_POST['name'] ?? ''),
        'organisation_type' => trim($_POST['organisation_type'] ?? ''),
        'contact_person' => trim($_POST['contact_person'] ?? ''),
        'email' => trim($_POST['email'] ?? ''),
        'phone' => trim($_POST['phone'] ?? ''),
        'address' => trim($_POST['address'] ?? ''),
        'website' => trim($_POST['website'] ?? ''),
        'notes' => trim($_POST['notes'] ?? '')
    ];

    // validation
    if (empty($form_data['name'])) {
        $errors['name'] = 'Organisation name is required';
    }
    if (empty($form_data['organisation_type'])) {
        $errors['organisation_type'] = 'Organisation type is required';
    }
    if (!empty($form_data['email']) && !filter_var($form_data['email'], FILTER_VALIDATE_EMAIL)) {
        $errors['email'] = 'Please enter a valid email address';
    }
    if (!empty($form_data['website']) && !filter_var($form_data['website'], FILTER_VALIDATE_URL)) {
        $errors['website'] = 'Please enter a valid URL';
    }

    if (empty($errors)) {
        try {
            $sql = "UPDATE partner_organisations 
                    SET name = :name, organisation_type = :type, contact_person = :contact, 
                        email = :email, phone = :phone, address = :address, 
                        website = :website, notes = :notes
                    WHERE organisation_id = :id";

            $stmt = $pdo->prepare($sql);
            $stmt->execute([
                ':name' => $form_data['name'],
                ':type' => $form_data['organisation_type'],
                ':contact' => $form_data['contact_person'],
                ':email' => $form_data['email'],
                ':phone' => $form_data['phone'],
                ':address' => $form_data['address'],
                ':website' => $form_data['website'],
                ':notes' => $form_data['notes'],
                ':id' => $organisation_id
            ]);

            $_SESSION['success_message'] = "Organisation '{$form_data['name']}' has been updated successfully!";
            header('Location: list.php');
            exit;

        } catch (PDOException $e) {
            $errors['database'] = 'Failed to update organisation. Please try again.';
            error_log('Database error: ' . $e->getMessage());
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Partner Organisation - SafePaws</title>
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
                    <h3><i class="bi bi-building-gear"></i> Edit Partner Organisation</h3>
                </div>
                <div class="card-body">
                    <?php if (!empty($errors['database'])): ?>
                        <div class="alert alert-danger">
                            <i class="bi bi-exclamation-triangle"></i> <?= htmlspecialchars($errors['database']) ?>
                        </div>
                    <?php endif; ?>

                    <form method="POST" action="" novalidate>
                        <div class="mb-3">
                            <label for="name" class="form-label">Organisation Name <span class="text-danger">*</span></label>
                            <input type="text" class="form-control <?= isset($errors['name']) ? 'is-invalid' : '' ?>"
                                   id="name" name="name" value="<?= htmlspecialchars($form_data['name'] ?? '') ?>"
                                   required maxlength="255">
                            <?php if (isset($errors['name'])): ?>
                                <div class="invalid-feedback"><?= htmlspecialchars($errors['name']) ?></div>
                            <?php endif; ?>
                        </div>

                        <div class="mb-3">
                            <label for="organisation_type" class="form-label">Organisation Type <span class="text-danger">*</span></label>
                            <select class="form-select <?= isset($errors['organisation_type']) ? 'is-invalid' : '' ?>"
                                    id="organisation_type" name="organisation_type" required>
                                <option value="">Select Type</option>
                                <?php
                                $types = ['Veterinary Clinic', 'Animal Hospital', 'Pet Supply Store', 'Community Partner', 'Other'];
                                foreach ($types as $type):
                                    ?>
                                    <option value="<?= $type ?>" <?= ($form_data['organisation_type'] == $type) ? 'selected' : '' ?>>
                                        <?= $type ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                            <?php if (isset($errors['organisation_type'])): ?>
                                <div class="invalid-feedback"><?= htmlspecialchars($errors['organisation_type']) ?></div>
                            <?php endif; ?>
                        </div>

                        <div class="mb-3">
                            <label for="contact_person" class="form-label">Contact Person</label>
                            <input type="text" class="form-control" id="contact_person" name="contact_person"
                                   value="<?= htmlspecialchars($form_data['contact_person'] ?? '') ?>" maxlength="255">
                        </div>

                        <div class="mb-3">
                            <label for="email" class="form-label">Email</label>
                            <input type="email" class="form-control <?= isset($errors['email']) ? 'is-invalid' : '' ?>"
                                   id="email" name="email" value="<?= htmlspecialchars($form_data['email'] ?? '') ?>" maxlength="255">
                            <?php if (isset($errors['email'])): ?>
                                <div class="invalid-feedback"><?= htmlspecialchars($errors['email']) ?></div>
                            <?php endif; ?>
                        </div>

                        <div class="mb-3">
                            <label for="phone" class="form-label">Phone Number</label>
                            <input type="tel" class="form-control" id="phone" name="phone"
                                   value="<?= htmlspecialchars($form_data['phone'] ?? '') ?>" maxlength="50">
                        </div>

                        <div class="mb-3">
                            <label for="address" class="form-label">Address</label>
                            <textarea class="form-control" id="address" name="address" rows="2" maxlength="500"><?= htmlspecialchars($form_data['address'] ?? '') ?></textarea>
                        </div>

                        <div class="mb-3">
                            <label for="website" class="form-label">Website</label>
                            <input type="url" class="form-control <?= isset($errors['website']) ? 'is-invalid' : '' ?>"
                                   id="website" name="website" value="<?= htmlspecialchars($form_data['website'] ?? '') ?>" maxlength="255">
                            <?php if (isset($errors['website'])): ?>
                                <div class="invalid-feedback"><?= htmlspecialchars($errors['website']) ?></div>
                            <?php endif; ?>
                        </div>

                        <div class="mb-3">
                            <label for="notes" class="form-label">Notes</label>
                            <textarea class="form-control" id="notes" name="notes" rows="3" maxlength="1000"><?= htmlspecialchars($form_data['notes'] ?? '') ?></textarea>
                        </div>

                        <div class="d-flex justify-content-between">
                            <a href="list.php" class="btn btn-secondary">
                                <i class="bi bi-arrow-left"></i> Cancel
                            </a>
                            <button type="submit" class="btn btn-primary">
                                <i class="bi bi-save"></i> Update Organisation
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
