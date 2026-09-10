<?php
/**
 * Add animal (admin).
 *
 * Species -> breed selection uses a cascading dropdown. Foster carer assignment is optional and limited to active
 * carers only. Profile image upload is optional; the original filename is never trusted - a new name is generated
 * server-side before saving into animal_profiles/.
 *
 * @var PDO $pdo
 * @var array $statusLabels
 * @var array $species
 * @var array $breeds
 * @var array $fosterCarers
 */

// Start session and include necessary files
session_start();
require_once __DIR__ . '/../auth/authentication.php';
require_once __DIR__ . '/../connection.php';
require_once __DIR__ . '/../includes/animal_helpers.php';
require_once __DIR__ . '/_form_data.php';

$errors = [];
$old = $_POST; // used to re-populate the form if validation fails

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name         = trim($_POST['name'] ?? '');
    $breedId      = $_POST['breed_id'] ?? '';
    $sex          = $_POST['sex'] ?? '';
    $desexed      = isset($_POST['desexed']) ? 1 : 0;
    $dob          = trim($_POST['date_of_birth'] ?? '');
    $dateAdmitted = trim($_POST['date_admitted'] ?? '');
    $description  = trim($_POST['description'] ?? '');
    $medicalNotes = trim($_POST['medical_notes'] ?? '');
    $status       = $_POST['status'] ?? '';
    $fosterCarerId = $_POST['foster_carer_id'] ?? '';

    // Required fields
    if ($name === '') {
        $errors[] = "Animal name is required.";
    }
    if (!ctype_digit((string) $breedId)) {
        $errors[] = "Please select a valid breed.";
    }
    if (!in_array($sex, ['male', 'female', 'unknown'], true)) {
        $errors[] = "Please select a valid sex.";
    }
    if ($dateAdmitted === '' || !DateTime::createFromFormat('Y-m-d', $dateAdmitted)) {
        $errors[] = "Date admitted must be a valid date.";
    } elseif ($dateAdmitted > date('Y-m-d')) {
        $errors[] = "Date admitted cannot be in the future.";
    }
    if (!array_key_exists($status, $statusLabels)) {
        $errors[] = "Please select a valid status.";
    }

    // Optional fields, validated only if provided
    if ($dob !== '') {
        if (!DateTime::createFromFormat('Y-m-d', $dob)) {
            $errors[] = "Date of birth must be a valid date.";
        } elseif ($dob > date('Y-m-d')) {
            $errors[] = "Date of birth cannot be in the future.";
        }
    } else {
        $dob = null;
    }

    if ($fosterCarerId !== '') {
        if (!ctype_digit((string) $fosterCarerId)) {
            $errors[] = "Please select a valid foster carer.";
        }
    } else {
        $fosterCarerId = null;
    }

    // Relationship checks - defends against a tampered POST bypassing the dropdown
    if (empty($errors) && $breedId !== '') {
        $check = $pdo->prepare("SELECT COUNT(*) FROM breeds WHERE breed_id = ?");
        $check->execute([$breedId]);
        if ($check->fetchColumn() == 0) {
            $errors[] = "Selected breed does not exist.";
        }
    }
    if (empty($errors) && $fosterCarerId !== null) {
        $check = $pdo->prepare("SELECT COUNT(*) FROM foster_carers WHERE foster_carer_id = ? AND status = 'active'");
        $check->execute([$fosterCarerId]);
        if ($check->fetchColumn() == 0) {
            $errors[] = "Selected foster carer is not valid or no longer active.";
        }
    }

    // Image upload - only save to disk until other fields have passed, prevents failed submission from leaving an orphaned file
    $profileImage = null;
    if (empty($errors) && !empty($_FILES['profile_image']['name'])) {
        $allowedExt = ['jpg', 'jpeg', 'png', 'gif'];
        $ext = strtolower(pathinfo($_FILES['profile_image']['name'], PATHINFO_EXTENSION));

        if ($_FILES['profile_image']['error'] !== UPLOAD_ERR_OK) {
            $errors[] = "There was a problem uploading the image.";
        } elseif (!in_array($ext, $allowedExt, true)) {
            $errors[] = "Profile image must be a JPG, PNG, or GIF file.";
        } elseif ($_FILES['profile_image']['size'] > 5 * 1024 * 1024) {
            $errors[] = "Profile image must be smaller than 5MB.";
        } else {

            // Server-generated filename, avoids path traversal and filename-collision issues
            $newFilename = 'animal_' . uniqid() . '.' . $ext;
            $destination = __DIR__ . '/../animal_profiles/' . $newFilename;

            if (move_uploaded_file($_FILES['profile_image']['tmp_name'], $destination)) {
                $profileImage = 'animal_profiles/' . $newFilename;
            } else {
                $errors[] = "Failed to save the uploaded image.";
            }
        }
    }

    if (empty($errors)) {
        try {
            $stmt = $pdo->prepare(
                "INSERT INTO animals
                    (name, breed_id, sex, desexed, date_of_birth, date_admitted,
                     description, medical_notes, status, profile_image, foster_carer_id)
                 VALUES
                    (:name, :breed_id, :sex, :desexed, :dob, :date_admitted,
                     :description, :medical_notes, :status, :profile_image, :foster_carer_id)"
            );
            $stmt->execute([
                'name'            => $name,
                'breed_id'        => $breedId,
                'sex'             => $sex,
                'desexed'         => $desexed,
                'dob'             => $dob,
                'date_admitted'   => $dateAdmitted,
                'description'     => $description !== '' ? $description : null,
                'medical_notes'   => $medicalNotes !== '' ? $medicalNotes : null,
                'status'          => $status,
                'profile_image'   => $profileImage,
                'foster_carer_id' => $fosterCarerId,
            ]);

            header("Location: index.php?added=1");
            exit;
        } catch (PDOException $e) {
            error_log("Insert animal failed: " . $e->getMessage());
            $errors[] = "Something went wrong while saving this animal. Please try again.";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Add Animal - SafePaws Admin</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
</head>
<body>
<?php include __DIR__ . '/../includes/navbar.php'; ?>

<div class="container mt-4">
    <div class="row justify-content-center">
        <div class="col-lg-9">

            <nav aria-label="breadcrumb">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="index.php">Animals</a></li>
                    <li class="breadcrumb-item active">Add Animal</li>
                </ol>
            </nav>

            <?php if (!empty($errors)): ?>
                <div class="alert alert-danger" role="alert">
                    <p class="mb-2"><i class="bi bi-exclamation-triangle"></i> Please fix the following:</p>
                    <ul class="mb-0">
                        <?php foreach ($errors as $error): ?>
                            <li><?= htmlentities($error) ?></li>
                        <?php endforeach; ?>
                    </ul>
                </div>
            <?php endif; ?>

            <form method="post" action="add.php" enctype="multipart/form-data">

                <div class="card mb-3">
                    <div class="card-header"><i class="bi bi-clipboard-heart"></i> Animal Details</div>
                    <div class="card-body">
                        <div class="row g-3">

                            <div class="col-md-6">
                                <label for="name" class="form-label">Name <span class="text-danger">*</span></label>
                                <input type="text" class="form-control" id="name" name="name"
                                       value="<?= htmlentities($old['name'] ?? '') ?>" required>
                            </div>

                            <div class="col-md-3">
                                <label for="sex" class="form-label">Sex <span class="text-danger">*</span></label>
                                <select class="form-select" id="sex" name="sex" required>
                                    <option value="male" <?= ($old['sex'] ?? '') === 'male' ? 'selected' : '' ?>>Male</option>
                                    <option value="female" <?= ($old['sex'] ?? '') === 'female' ? 'selected' : '' ?>>Female</option>
                                    <option value="unknown" <?= ($old['sex'] ?? '') === 'unknown' ? 'selected' : '' ?>>Unknown</option>
                                </select>
                            </div>

                            <div class="col-md-3 d-flex align-items-end">
                                <div class="form-check mb-2">
                                    <input class="form-check-input" type="checkbox" id="desexed" name="desexed"
                                            <?= isset($old['desexed']) ? 'checked' : '' ?>>
                                    <label class="form-check-label" for="desexed">Desexed</label>
                                </div>
                            </div>

                            <!-- Species drives the breed dropdown via js/animal-form.js -->
                            <div class="col-md-6">
                                <label for="species" class="form-label">Species <span class="text-danger">*</span></label>
                                <select class="form-select" id="species" onchange="updateBreeds()">
                                    <?php foreach ($species as $s): ?>
                                        <option value="<?= $s['species_id'] ?>"><?= htmlentities($s['species_name']) ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>

                            <div class="col-md-6">
                                <label for="breed_id" class="form-label">Breed <span class="text-danger">*</span></label>
                                <select class="form-select" id="breed_id" name="breed_id" required></select>
                            </div>

                            <div class="col-md-6">
                                <label for="date_of_birth" class="form-label">Date of Birth</label>
                                <input type="date" class="form-control" id="date_of_birth" name="date_of_birth"
                                       max="<?= date('Y-m-d') ?>"
                                       value="<?= htmlentities($old['date_of_birth'] ?? '') ?>">
                                <div class="form-text">Leave blank if unknown.</div>
                            </div>

                            <div class="col-md-6">
                                <label for="date_admitted" class="form-label">Date Admitted <span class="text-danger">*</span></label>
                                <input type="date" class="form-control" id="date_admitted" name="date_admitted"
                                       max="<?= date('Y-m-d') ?>"
                                       value="<?= htmlentities($old['date_admitted'] ?? date('Y-m-d')) ?>" required>
                            </div>

                        </div>
                    </div>
                </div>

                <div class="card mb-3">
                    <div class="card-header"><i class="bi bi-card-text"></i> Notes</div>
                    <div class="card-body">
                        <div class="mb-3">
                            <label for="description" class="form-label">Description</label>
                            <textarea class="form-control" id="description" name="description" rows="3"><?= htmlentities($old['description'] ?? '') ?></textarea>
                            <div class="form-text">Shown publicly on the adoption listing.</div>
                        </div>

                        <div class="mb-0">
                            <label for="medical_notes" class="form-label">
                                Medical Notes
                                <span class="badge bg-secondary">Internal only</span>
                            </label>
                            <textarea class="form-control" id="medical_notes" name="medical_notes" rows="3"><?= htmlentities($old['medical_notes'] ?? '') ?></textarea>
                            <div class="form-text">Never shown on the public pages.</div>
                        </div>
                    </div>
                </div>

                <div class="card mb-3">
                    <div class="card-header"><i class="bi bi-house-heart"></i> Status and Placement</div>
                    <div class="card-body">
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label for="status" class="form-label">Status <span class="text-danger">*</span></label>
                                <select class="form-select" id="status" name="status" required>
                                    <?php foreach ($statusLabels as $value => $label): ?>
                                        <option value="<?= $value ?>" <?= ($old['status'] ?? '') === $value ? 'selected' : '' ?>>
                                            <?= htmlentities($label) ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>

                            <div class="col-md-6">
                                <label for="foster_carer_id" class="form-label">Foster Carer</label>
                                <select class="form-select" id="foster_carer_id" name="foster_carer_id">
                                    <option value="">-- Not assigned --</option>
                                    <?php foreach ($fosterCarers as $fc): ?>
                                        <option value="<?= $fc['foster_carer_id'] ?>"
                                                <?= ($old['foster_carer_id'] ?? '') == $fc['foster_carer_id'] ? 'selected' : '' ?>>
                                            <?= htmlentities($fc['first_name'] . ' ' . $fc['last_name']) ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                                <div class="form-text">Only active carers can take a new placement.</div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="card mb-3">
                    <div class="card-header"><i class="bi bi-image"></i> Profile Image</div>
                    <div class="card-body">
                        <label for="profile_image" class="form-label">Upload an image</label>
                        <input type="file" class="form-control" id="profile_image" name="profile_image"
                               accept=".jpg,.jpeg,.png,.gif">
                        <div class="form-text">Optional. JPG, PNG or GIF, up to 5MB.</div>
                    </div>
                </div>

                <div class="d-flex justify-content-end gap-2 mb-5">
                    <a href="index.php" class="btn btn-outline-secondary">
                        <i class="bi bi-x-circle"></i> Cancel
                    </a>
                    <button type="submit" class="btn btn-primary">
                        <i class="bi bi-plus-circle"></i> Add Animal
                    </button>
                </div>

            </form>

        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<?php require __DIR__ . '/_form_scripts.php'; ?>
</body>
</html>
