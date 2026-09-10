<?php
// contact/index.php - Public contact form (no login required)
require_once __DIR__ . '/../connection.php';
/** @var PDO $pdo */

$errors = [];
$success = false;
$form_data = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $form_data = [
        'name' => trim($_POST['name'] ?? ''),
        'email' => trim($_POST['email'] ?? ''),
        'phone' => trim($_POST['phone'] ?? ''),
        'message' => trim($_POST['message'] ?? '')
    ];

    if (empty($form_data['name'])) {
        $errors['name'] = 'Name is required';
    }
    if (empty($form_data['email'])) {
        $errors['email'] = 'Email address is required';
    } elseif (!filter_var($form_data['email'], FILTER_VALIDATE_EMAIL)) {
        $errors['email'] = 'Please enter a valid email address';
    }
    if (empty($form_data['message'])) {
        $errors['message'] = 'Message is required';
    }

    if (empty($errors)) {
        try {
            $sql = "INSERT INTO contact_messages (name, email, phone, message) 
                    VALUES (:name, :email, :phone, :message)";

            $stmt = $pdo->prepare($sql);
            $stmt->execute([
                ':name' => $form_data['name'],
                ':email' => $form_data['email'],
                ':phone' => $form_data['phone'] ?: null,
                ':message' => $form_data['message']
            ]);

            $success = true;
            $form_data = [];

        } catch (PDOException $e) {
            $errors['database'] = 'Failed to send your message. Please try again.';
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
    <title>Contact Us - SafePaws Rescue</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
</head>
<body>

<nav class="navbar navbar-expand-lg navbar-dark bg-dark">
    <div class="container-fluid">
        <a class="navbar-brand" href="../index.php">
            <i class="bi bi-heart-fill text-danger"></i> SafePaws Rescue
        </a>
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
            <span class="navbar-toggler-icon"></span>
        </button>
        <div class="collapse navbar-collapse" id="navbarNav">
            <ul class="navbar-nav me-auto">
                <li class="nav-item">
                    <a class="nav-link" href="../index.php">Home</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="../public/animals.php">Available Animals</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link active" href="index.php">Contact Us</a>
                </li>
            </ul>
            <ul class="navbar-nav">
                <li class="nav-item">
                    <a class="nav-link" href="../auth/login.php">
                        <i class="bi bi-box-arrow-in-right"></i> Admin Login
                    </a>
                </li>
            </ul>
        </div>
    </div>
</nav>

<div class="container mt-4">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card">
                <div class="card-header">
                    <h3><i class="bi bi-envelope"></i> Contact Us</h3>
                </div>
                <div class="card-body">
                    <?php if ($success): ?>
                        <div class="alert alert-success">
                            <h4 class="alert-heading"><i class="bi bi-check-circle"></i> Thank You!</h4>
                            <p>Your message has been sent successfully. We'll get back to you soon!</p>
                            <hr>
                            <p class="mb-0">You can <a href="index.php">send another message</a> or return to the <a href="../index.php">homepage</a>.</p>
                        </div>
                    <?php else: ?>
                        <?php if (!empty($errors['database'])): ?>
                            <div class="alert alert-danger">
                                <i class="bi bi-exclamation-triangle"></i> <?= htmlspecialchars($errors['database'], ENT_QUOTES) ?>
                            </div>
                        <?php endif; ?>

                        <p class="text-muted">Have questions about adopting an animal, fostering, or how you can help? Fill in the form below and we'll get back to you as soon as possible.</p>

                        <form method="POST" action="" novalidate>
                            <div class="mb-3">
                                <label for="name" class="form-label">Your Name <span class="text-danger">*</span></label>
                                <input type="text" class="form-control <?= isset($errors['name']) ? 'is-invalid' : '' ?>"
                                       id="name" name="name" value="<?= htmlspecialchars($form_data['name'] ?? '', ENT_QUOTES) ?>"
                                       required maxlength="100">
                                <?php if (isset($errors['name'])): ?>
                                    <div class="invalid-feedback"><?= htmlspecialchars($errors['name'], ENT_QUOTES) ?></div>
                                <?php endif; ?>
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

                            <div class="mb-3">
                                <label for="phone" class="form-label">Phone Number</label>
                                <input type="tel" class="form-control" id="phone" name="phone"
                                       value="<?= htmlspecialchars($form_data['phone'] ?? '', ENT_QUOTES) ?>" maxlength="50">
                                <div class="form-text">*Optional</div>
                            </div>

                            <div class="mb-3">
                                <label for="message" class="form-label">Message <span class="text-danger">*</span></label>
                                <textarea class="form-control <?= isset($errors['message']) ? 'is-invalid' : '' ?>"
                                          id="message" name="message" rows="5" required><?= htmlspecialchars($form_data['message'] ?? '', ENT_QUOTES) ?></textarea>
                                <?php if (isset($errors['message'])): ?>
                                    <div class="invalid-feedback"><?= htmlspecialchars($errors['message'], ENT_QUOTES) ?></div>
                                <?php endif; ?>
                            </div>

                            <div class="d-flex justify-content-between">
                                <a href="../index.php" class="btn btn-secondary">
                                    <i class="bi bi-arrow-left"></i> Back to Home
                                </a>
                                <button type="submit" class="btn btn-primary">
                                    <i class="bi bi-send"></i> Send Message
                                </button>
                            </div>
                        </form>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
