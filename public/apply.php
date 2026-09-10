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

            // Redirect to a thank you page or show a success message
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
    <title>Document</title>
    <!-- Link external CSS stylesheet here -->
    <link rel="stylesheet" href="style.css">
</head>
<body>

<!-- Visible web content goes here -->

<!-- Link external JavaScript file here -->
<script src="script.js"></script>
</body>
</html>

