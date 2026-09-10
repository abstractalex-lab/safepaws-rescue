<?php
/**
 * Public adoption application form.
 *
 * Does NOT include authentication.php, publicly available and accessible to view page.
 *
 * The animal is identified by ?animal_id= and must currently be available for adoption; applications
 * can't be submitted against animals in any other state. A given email address can only apply once per animal.
 *
 * @var PDO $pdo
 */

// Include necessary files for database connection and helper functions
require_once __DIR__ . '/../connection.php';
require_once __DIR__ . '/../includes/animal_helpers.php';

$animalId = $_GET['animal_id'] ?? $_POST['animal_id'] ?? '';

if (!ctype_digit((string) $animalId)) {
    header("Location: animals.php");
    exit;
}

// Only available animals can be applied for
$stmt = $pdo->prepare(
    "SELECT a.animal_id, a.name, a.profile_image, s.species_name, b.breed_name
     FROM animals a
     JOIN breeds b ON a.breed_id = b.breed_id
     JOIN species s ON b.species_id = s.species_id
     WHERE a.animal_id = ? AND a.status = 'available'"
);
$stmt->execute([$animalId]);
$animal = $stmt->fetch();

if (!$animal) {
    header("Location: animals.php?notfound=1");
    exit;
}

// Housing options offered on the form, validated against this list on submit
$housingTypes = ['House', 'Apartment or unit', 'Townhouse', 'Acreage', 'Other'];

$ownershipTypes = [
    'own'   => 'I own my home',
    'rent'  => 'I rent my home',
    'other' => 'Other arrangement',
];

$errors = [];
$submitted = false;
$old = $_POST;

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $applicantName = trim($_POST['applicant_name'] ?? '');
    $email         = trim($_POST['email'] ?? '');
    $phone         = trim($_POST['phone'] ?? '');
    $suburb        = trim($_POST['suburb'] ?? '');
    $housingType   = $_POST['housing_type'] ?? '';
    $homeOwnership = $_POST['home_ownership'] ?? '';
    $otherPets     = trim($_POST['other_pets'] ?? '');
    $reason        = trim($_POST['reason_for_adoption'] ?? '');

    // Required fields
    if ($applicantName === '') {
        $errors[] = "Your name is required.";
    }
    if ($email === '') {
        $errors[] = "An email address is required.";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors[] = "Please enter a valid email address.";
    }
    if ($phone === '') {
        $errors[] = "A contact phone number is required.";
    }
    if ($suburb === '') {
        $errors[] = "Your suburb is required.";
    }
    if (!in_array($housingType, $housingTypes, true)) {
        $errors[] = "Please select your housing type.";
    }
    if (!array_key_exists($homeOwnership, $ownershipTypes)) {
        $errors[] = "Please tell us whether you own or rent.";
    }
    if ($reason === '') {
        $errors[] = "Please tell us why you'd like to adopt " . $animal['name'] . ".";
    }

    // One application per email address per animal restriction, to avoid duplicates in the adoption process
    if (empty($errors)) {
        $dupe = $pdo->prepare(
            "SELECT COUNT(*) FROM adoption_applications WHERE animal_id = ? AND email = ?"
        );
        $dupe->execute([$animalId, $email]);
        if ($dupe->fetchColumn() > 0) {
            $errors[] = "You've already submitted an application for " . $animal['name']
                . ". Please contact us if you'd like to update it.";
        }
    }

    // If no validation errors, insert the application into the database
    if (empty($errors)) {
        try {
            $insert = $pdo->prepare(
                "INSERT INTO adoption_applications
                    (animal_id, applicant_name, email, phone, suburb, housing_type,
                     home_ownership, other_pets, reason_for_adoption)
                 VALUES
                    (:animal_id, :applicant_name, :email, :phone, :suburb, :housing_type,
                     :home_ownership, :other_pets, :reason)"
            );
            $insert->execute([
                'animal_id'      => $animalId,
                'applicant_name' => $applicantName,
                'email'          => $email,
                'phone'          => $phone,
                'suburb'         => $suburb,
                'housing_type'   => $housingType,
                'home_ownership' => $homeOwnership,
                'other_pets'     => $otherPets !== '' ? $otherPets : null,
                'reason'         => $reason,
            ]);

            // Shows a confirmation in place rather than redirecting
            $submitted = true;
        } catch (PDOException $e) {
            error_log("Adoption application insert failed: " . $e->getMessage());
            $errors[] = "Something went wrong while submitting your application. Please try again.";
        }
    }
}

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Apply to adopt <?= htmlspecialchars($animal['name']) ?> - SafePaws Rescue</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
</head>
<body>
<?php include __DIR__ . '/../includes/navbar.php'; ?>

<div class="container mt-4">
    <div class="row justify-content-center">
        <div class="col-lg-8">

            <nav aria-label="breadcrumb">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="animals.php">Animals for Adoption</a></li>
                    <li class="breadcrumb-item">
                        <a href="animal_detail.php?id=<?= $animal['animal_id'] ?>"><?= htmlspecialchars($animal['name']) ?></a>
                    </li>
                    <li class="breadcrumb-item active">Apply</li>
                </ol>
            </nav>

            <!-- The animal being applied for, shown on both the form and the confirmation -->
            <div class="card mb-4">
                <div class="row g-0 align-items-center">
                    <?php if ($animal['profile_image']): ?>
                        <div class="col-4">
                            <img src="../<?= htmlspecialchars($animal['profile_image']) ?>"
                                 class="img-fluid rounded-start" alt="<?= htmlspecialchars($animal['name']) ?>"
                                 style="height:150px; width:100%; object-fit:cover;">
                        </div>
                    <?php endif; ?>
                    <div class="<?= $animal['profile_image'] ? 'col-8' : 'col-12' ?>">
                        <div class="card-body">
                            <h5 class="card-title mb-1">
                                Adoption application for <?= htmlspecialchars($animal['name']) ?>
                            </h5>
                            <p class="card-text text-muted mb-0">
                                <?= htmlspecialchars($animal['species_name']) ?> &middot;
                                <?= htmlspecialchars($animal['breed_name']) ?>
                            </p>
                        </div>
                    </div>
                </div>
            </div>

            <?php if ($submitted): ?>
                <div class="alert alert-success" role="alert">
                    <h5 class="alert-heading"><i class="bi bi-check-circle"></i> Application received</h5>
                    <p>
                        Thank you for applying to adopt <?= htmlspecialchars($animal['name']) ?>.
                        Our team will review your application and get in touch using the contact
                        details you provided.
                    </p>
                    <hr>
                    <a href="animals.php" class="btn btn-outline-success">
                        Browse more animals
                    </a>
                </div>
            <?php else: ?>

                <?php if (!empty($errors)): ?>
                    <div class="alert alert-danger" role="alert">
                        <p class="mb-2"><i class="bi bi-exclamation-triangle"></i> Please fix the following:</p>
                        <ul class="mb-0">
                            <?php foreach ($errors as $error): ?>
                                <li><?= htmlspecialchars($error) ?></li>
                            <?php endforeach; ?>
                        </ul>
                    </div>
                <?php endif; ?>

                <form method="post" action="apply.php">
                    <input type="hidden" name="animal_id" value="<?= htmlspecialchars($animalId) ?>">

                    <div class="card mb-3">
                        <div class="card-header"><i class="bi bi-person"></i> Your Details</div>
                        <div class="card-body">
                            <div class="row g-3">
                                <div class="col-md-6">
                                    <label for="applicant_name" class="form-label">
                                        Full name <span class="text-danger">*</span>
                                    </label>
                                    <input type="text" class="form-control" id="applicant_name" name="applicant_name"
                                           value="<?= htmlspecialchars($old['applicant_name'] ?? '') ?>" required>
                                </div>

                                <div class="col-md-6">
                                    <label for="suburb" class="form-label">
                                        Suburb <span class="text-danger">*</span>
                                    </label>
                                    <input type="text" class="form-control" id="suburb" name="suburb"
                                           value="<?= htmlspecialchars($old['suburb'] ?? '') ?>" required>
                                </div>

                                <div class="col-md-6">
                                    <label for="email" class="form-label">
                                        Email address <span class="text-danger">*</span>
                                    </label>
                                    <input type="email" class="form-control" id="email" name="email"
                                           value="<?= htmlspecialchars($old['email'] ?? '') ?>" required>
                                </div>

                                <div class="col-md-6">
                                    <label for="phone" class="form-label">
                                        Phone number <span class="text-danger">*</span>
                                    </label>
                                    <input type="tel" class="form-control" id="phone" name="phone"
                                           value="<?= htmlspecialchars($old['phone'] ?? '') ?>" required>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="card mb-3">
                        <div class="card-header"><i class="bi bi-house"></i> Your Home</div>
                        <div class="card-body">
                            <div class="row g-3">
                                <div class="col-md-6">
                                    <label for="housing_type" class="form-label">
                                        Housing type <span class="text-danger">*</span>
                                    </label>
                                    <select class="form-select" id="housing_type" name="housing_type" required>
                                        <option value="">-- Please select --</option>
                                        <?php foreach ($housingTypes as $type): ?>
                                            <option value="<?= htmlspecialchars($type) ?>"
                                                <?= ($old['housing_type'] ?? '') === $type ? 'selected' : '' ?>>
                                                <?= htmlspecialchars($type) ?>
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>

                                <div class="col-md-6">
                                    <label for="home_ownership" class="form-label">
                                        Do you own or rent? <span class="text-danger">*</span>
                                    </label>
                                    <select class="form-select" id="home_ownership" name="home_ownership" required>
                                        <option value="">-- Please select --</option>
                                        <?php foreach ($ownershipTypes as $value => $label): ?>
                                            <option value="<?= $value ?>"
                                                <?= ($old['home_ownership'] ?? '') === $value ? 'selected' : '' ?>>
                                                <?= htmlspecialchars($label) ?>
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>

                                <div class="col-12">
                                    <label for="other_pets" class="form-label">Other pets</label>
                                    <textarea class="form-control" id="other_pets" name="other_pets" rows="2"><?= htmlspecialchars($old['other_pets'] ?? '') ?></textarea>
                                    <div class="form-text">
                                        Tell us about any pets you already have. Leave blank if you have none.
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="card mb-3">
                        <div class="card-header"><i class="bi bi-chat-heart"></i> Why This Animal</div>
                        <div class="card-body">
                            <label for="reason_for_adoption" class="form-label">
                                Why would you like to adopt <?= htmlspecialchars($animal['name']) ?>?
                                <span class="text-danger">*</span>
                            </label>
                            <textarea class="form-control" id="reason_for_adoption" name="reason_for_adoption"
                                      rows="4" required><?= htmlspecialchars($old['reason_for_adoption'] ?? '') ?></textarea>
                        </div>
                    </div>

                    <div class="d-flex justify-content-end gap-2 mb-5">
                        <a href="animal_detail.php?id=<?= $animal['animal_id'] ?>" class="btn btn-outline-secondary">
                            <i class="bi bi-x-circle"></i> Cancel
                        </a>
                        <button type="submit" class="btn btn-primary">
                            <i class="bi bi-send"></i> Submit Application
                        </button>
                    </div>

                </form>
            <?php endif; ?>

        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
