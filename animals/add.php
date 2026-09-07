<?php
/**
 * Add animal (admin).
 *
 * Species -> breed selection uses a cascading dropdown. Foster carer assignment is optional and limited to active
 * carers only. Profile image upload is optional; the original filename is never trusted - a new name is generated
 * server-side before saving into animal_profiles/.
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

// All species + all breeds (grouped by species in JS) for the cascading dropdown. Unknown/Mixed are always listed last.
$species = $pdo->query(
    "SELECT species_id, species_name
     FROM species
     ORDER BY (species_name = 'Unknown'), species_name"
)->fetchAll();
$breeds  = $pdo->query(
    "SELECT breed_id, species_id, breed_name
     FROM breeds
     ORDER BY (breed_name LIKE 'Mixed%' OR breed_name = 'Unknown'), breed_name"
)->fetchAll();

// Only active foster carers can receive a new assignment
$fosterCarers = $pdo->query(
    "SELECT foster_carer_id, first_name, last_name FROM foster_carers WHERE status = 'active' ORDER BY first_name"
)->fetchAll();

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
</head>
<body>
<h1>Add Animal</h1>
<br>

<?php if (!empty($errors)): ?>
    <ul style="color:red;">
        <?php foreach ($errors as $error): ?>
            <li><?= htmlentities($error) ?></li>
        <?php endforeach; ?>
    </ul>
<?php endif; ?>

<form method="post" action="add.php" enctype="multipart/form-data">

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
        <input type="checkbox" id="desexed" name="desexed" <?= isset($old['desexed']) ? 'checked' : '' ?>>
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
           value="<?= htmlentities($old['date_admitted'] ?? date('Y-m-d')) ?>" required>
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
            <option value="<?= $fc['foster_carer_id'] ?>"
                <?= ($old['foster_carer_id'] ?? '') == $fc['foster_carer_id'] ? 'selected' : '' ?>>
                <?= htmlentities($fc['first_name'] . ' ' . $fc['last_name']) ?>
            </option>
        <?php endforeach; ?>
    </select>
    <br><br>

    <label for="profile_image">Profile Image (optional)</label><br>
    <input type="file" id="profile_image" name="profile_image" accept=".jpg,.jpeg,.png,.gif">
    <br><br>

    <button type="submit">Add Animal</button>
</form>

<br>
<p><a href="index.php">Back to Animal List</a></p>

<script>
    // Breed data grouped by species, embedded directly since the dataset is small and static
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
        // Pre-select matching the previously submitted breed, so failed validation re-populates the form correctly
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

