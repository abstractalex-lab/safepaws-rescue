<?php
/**
 * Edit animal (admin).
 *
 * Mirrors add.php, with three differences:
 * - The update is wrapped in a transaction so the DB write and the old-image file deletion can't leave
 *   the record and filesystem out of sync.
 * - The profile image can be replaced or removed; the previous file is only deleted from disk after
 *   the DB update commits successfully.
 * - If the animal is currently assigned to a foster carer who has since gone inactive, that carer still
 *   appears (disabled) and remains valid on submit, so editing an unrelated field doesn't silently drop it.
 */

session_start();
require_once __DIR__ . '/../auth/authentication.php';

/** @var PDO $pdo */
require_once __DIR__ . '/../connection.php';

$statusLabels = [
    'in_care'   => 'In care',
    'available' => 'Available for adoption',
    'pending'   => 'Adoption pending',
    'adopted'   => 'Adopted',
];

$animalId = $_GET['id'] ?? $_POST['animal_id'] ?? '';

if (!ctype_digit((string) $animalId)) {
    header("Location: index.php?error=invalid");
    exit;
}

// Load the existing record first
$stmt = $pdo->prepare("SELECT * FROM animals WHERE animal_id = ?");
$stmt->execute([$animalId]);
$animal = $stmt->fetch();

// If the ID is valid but the record doesn't exist, redirect to the list with an error
if (!$animal) {
    header("Location: index.php?error=notfound");
    exit;
}

// Load species and breeds for the dropdowns, with "Unknown" and "Mixed" first
$species = $pdo->query(
    "SELECT species_id, species_name
     FROM species
     ORDER BY (species_name = 'Unknown'), species_name"
)->fetchAll();

$breeds = $pdo->query(
    "SELECT breed_id, species_id, breed_name
     FROM breeds
     ORDER BY (breed_name LIKE 'Mixed%' OR breed_name = 'Unknown'), breed_name"
)->fetchAll();

// Query active carers, plus the currently assigned one even if it has gone inactive
$carerStmt = $pdo->prepare(
    "SELECT foster_carer_id, first_name, last_name, status
     FROM foster_carers
     WHERE status = 'active' OR foster_carer_id = ?
     ORDER BY first_name"
);
$carerStmt->execute([$animal['foster_carer_id']]);
$fosterCarers = $carerStmt->fetchAll();

$errors = [];

// On first load the form shows the stored record; after a failed submit re-populate the form with the submitted values
$old = $_SERVER['REQUEST_METHOD'] === 'POST' ? $_POST : $animal;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name          = trim($_POST['name'] ?? '');
    $breedId       = $_POST['breed_id'] ?? '';
    $sex           = $_POST['sex'] ?? '';
    $desexed       = isset($_POST['desexed']) ? 1 : 0;
    $dob           = trim($_POST['date_of_birth'] ?? '');
    $dateAdmitted  = trim($_POST['date_admitted'] ?? '');
    $description   = trim($_POST['description'] ?? '');
    $medicalNotes  = trim($_POST['medical_notes'] ?? '');
    $status        = $_POST['status'] ?? '';
    $fosterCarerId = $_POST['foster_carer_id'] ?? '';
    $removeImage   = isset($_POST['remove_image']);

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

    // Relationship checks - guards against a tampered POST, not just the dropdown
    if (empty($errors) && $breedId !== '') {
        $check = $pdo->prepare("SELECT COUNT(*) FROM breeds WHERE breed_id = ?");
        $check->execute([$breedId]);
        if ($check->fetchColumn() == 0) {
            $errors[] = "Selected breed does not exist.";
        }
    }
    if (empty($errors) && $fosterCarerId !== null) {
        // Looser than add.php: an inactive carer is acceptable here only if they're already assigned to this animal
        $check = $pdo->prepare(
            "SELECT COUNT(*) FROM foster_carers
             WHERE foster_carer_id = ? AND (status = 'active' OR foster_carer_id = ?)"
        );
        $check->execute([$fosterCarerId, $animal['foster_carer_id']]);
        if ($check->fetchColumn() == 0) {
            $errors[] = "Selected foster carer is not valid or no longer active.";
        }
    }

    // Check if new image is uploaded and validate it. Otherwise, keep the existing image path or remove it if requested.
    $profileImage = $animal['profile_image'];
    $imageToDelete = null;
    $uploadedPath = null;

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
                $uploadedPath  = $destination;                        // for rollback cleanup
                $imageToDelete = $animal['profile_image'];            // old file, deleted after commit
                $profileImage  = 'animal_profiles/' . $newFilename;
            } else {
                $errors[] = "Failed to save the uploaded image.";
            }
        }
    } elseif (empty($errors) && $removeImage && $animal['profile_image']) {
        $imageToDelete = $animal['profile_image'];
        $profileImage  = null;
    }

    // Start transaction and update the record if no validation errors
    if (empty($errors)) {
        try {
            $pdo->beginTransaction();
            $stmt = $pdo->prepare(
                "UPDATE animals SET
                    name            = :name,
                    breed_id        = :breed_id,
                    sex             = :sex,
                    desexed         = :desexed,
                    date_of_birth   = :dob,
                    date_admitted   = :date_admitted,
                    description     = :description,
                    medical_notes   = :medical_notes,
                    status          = :status,
                    profile_image   = :profile_image,
                    foster_carer_id = :foster_carer_id
                 WHERE animal_id = :animal_id"
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
                'animal_id'       => $animalId,
            ]);
            $pdo->commit();

            // Remove old image if there is one, after DB successfully updated
            if ($imageToDelete) {
                $oldPath = __DIR__ . '/../' . $imageToDelete;
                if (is_file($oldPath)) {
                    unlink($oldPath);
                }
            }

            header("Location: index.php?updated=1");
            exit;
        } catch (PDOException $e) {
            $pdo->rollBack();

            // If fail update, remove orphaned uploaded file to avoid cluttering the server
            if ($uploadedPath && is_file($uploadedPath)) {
                unlink($uploadedPath);
            }

            error_log("Update animal failed: " . $e->getMessage());
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
    <title>Edit Animal - SafePaws Admin</title>
</head>
<body>
<h1>Edit Animal</h1>
<br>

<?php if (!empty($errors)): ?>
    <ul style="color:red;">
        <?php foreach ($errors as $error): ?>
            <li><?= htmlentities($error) ?></li>
        <?php endforeach; ?>
    </ul>
<?php endif; ?>

<form method="post" action="edit.php" enctype="multipart/form-data">
    <input type="hidden" name="animal_id" value="<?= htmlentities($animalId) ?>">

    <label for="name">Name</label><br>
    <input type="text" id="name" name="name" value="<?= htmlentities($old['name'] ?? '') ?>" required>
    <br><br>

    <label for="species">Species</label><br>
    <select id="species" onchange="updateBreeds()">
        <?php foreach ($species as $s): ?>
            <option value="<?= $s['species_id'] ?>"><?= htmlentities($s['species_name']) ?></option>
        <?php endforeach; ?>
    </select>
    <br><br>

    <label for="breed_id">Breed</label><br>
    <select id="breed_id" name="breed_id" required></select>
    <br><br>

    <label for="sex">Sex</label><br>
    <select id="sex" name="sex" required>
        <option value="male" <?= ($old['sex'] ?? '') === 'male' ? 'selected' : '' ?>>Male</option>
        <option value="female" <?= ($old['sex'] ?? '') === 'female' ? 'selected' : '' ?>>Female</option>
        <option value="unknown" <?= ($old['sex'] ?? '') === 'unknown' ? 'selected' : '' ?>>Unknown</option>
    </select>
    <br><br>

    <label for="desexed">
        <input type="checkbox" id="desexed" name="desexed" <?= !empty($old['desexed']) ? 'checked' : '' ?>>
        Desexed
    </label>
    <br><br>

    <label for="date_of_birth">Date of Birth (leave blank if unknown)</label><br>
    <input type="date" id="date_of_birth" name="date_of_birth"
           max="<?= date('Y-m-d') ?>"
           value="<?= htmlentities($old['date_of_birth'] ?? '') ?>">
    <br><br>

    <label for="date_admitted">Date Admitted</label><br>
    <input type="date" id="date_admitted" name="date_admitted"
           max="<?= date('Y-m-d') ?>"
           value="<?= htmlentities($old['date_admitted'] ?? '') ?>" required>
    <br><br>

    <label for="description">Description</label><br>
    <textarea id="description" name="description" rows="3" cols="50"><?= htmlentities($old['description'] ?? '') ?></textarea>
    <br><br>

    <label for="medical_notes">Medical Notes</label><br>
    <textarea id="medical_notes" name="medical_notes" rows="3" cols="50"><?= htmlentities($old['medical_notes'] ?? '') ?></textarea>
    <br><br>

    <label for="status">Status</label><br>
    <select id="status" name="status" required>
        <?php foreach ($statusLabels as $value => $label): ?>
            <option value="<?= $value ?>" <?= ($old['status'] ?? '') === $value ? 'selected' : '' ?>>
                <?= htmlentities($label) ?>
            </option>
        <?php endforeach; ?>
    </select>
    <br><br>

    <label for="foster_carer_id">Foster Carer (optional)</label><br>
    <select id="foster_carer_id" name="foster_carer_id">
        <option value="">-- Not assigned --</option>
        <?php foreach ($fosterCarers as $fc): ?>
            <?php
            // An inactive carer only appears here if they were assigned to this animal
            // Greyed out so it can't be re-picked once changed, but still can submit if remains selected
            $isInactive = $fc['status'] !== 'active';
            $isSelected = ($old['foster_carer_id'] ?? '') == $fc['foster_carer_id'];
            ?>
            <option value="<?= $fc['foster_carer_id'] ?>"
                <?= $isSelected ? 'selected' : '' ?>
                <?= $isInactive ? 'disabled' : '' ?>>
                <?= htmlentities($fc['first_name'] . ' ' . $fc['last_name']) ?><?= $isInactive ? ' (inactive)' : '' ?>
            </option>
        <?php endforeach; ?>
    </select>
    <br><br>

    <label>Current Profile Image</label><br>
    <?php if ($animal['profile_image']): ?>
        <img src="../<?= htmlentities($animal['profile_image']) ?>" alt="<?= htmlentities($animal['name']) ?>" style="max-width:200px;">
        <br>
        <label for="remove_image">
            <input type="checkbox" id="remove_image" name="remove_image">
            Remove current image
        </label>
    <?php else: ?>
        <em>No image uploaded.</em>
    <?php endif; ?>
    <br><br>

    <label for="profile_image">Replace Profile Image (optional)</label><br>
    <input type="file" id="profile_image" name="profile_image" accept=".jpg,.jpeg,.png,.gif">
    <br><br>

    <button type="submit">Save Changes</button>
</form>

<br>
<p><a href="index.php">Back to Animal List</a></p>

<script>
    const breedsBySpecies = {};
    <?php foreach ($species as $s): ?>
    breedsBySpecies[<?= $s['species_id'] ?>] = [
        <?php foreach ($breeds as $b): ?>
        <?php if ($b['species_id'] == $s['species_id']): ?>
        { id: <?= $b['breed_id'] ?>, name: <?= json_encode($b['breed_name']) ?> },
        <?php endif; ?>
        <?php endforeach; ?>
    ];
    <?php endforeach; ?>

    const oldBreedId = <?= json_encode($old['breed_id'] ?? null) ?>;
    const oldSpeciesId = <?php
        $oldSpeciesId = null;
        if (!empty($old['breed_id'])) {
            foreach ($breeds as $b) {
                if ($b['breed_id'] == $old['breed_id']) {
                    $oldSpeciesId = $b['species_id'];
                    break;
                }
            }
        }
        echo json_encode($oldSpeciesId);
        ?>;
</script>
<script src="../js/animal-form.js"></script>
</body>
</html>