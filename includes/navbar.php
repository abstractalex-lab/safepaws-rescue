<?php
/**
 * Shared navigation bar.
 *
 * Included by every page, admin and public. Requires $pdo to be in scope
 *
 * Links are built from DOCUMENT_ROOT rather than relative "../" paths, so the navbar works identically
 * whether it's included from the project root (index.php) or from a module folder (animals/edit.php).
 *
 * @var PDO $pdo
 */

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Site-root-relative base path, e.g. "/Lab02_Group06".
$navDocRoot = str_replace('\\', '/', $_SERVER['DOCUMENT_ROOT']);
$navAppRoot = str_replace('\\', '/', dirname(__DIR__));
$base = '/' . trim(str_replace($navDocRoot, '', $navAppRoot), '/');

// Determine the current logged-in user, if any, to be used to show/hide admin links in the navbar
if (!isset($current_user)) {
    $current_user = null;

    if (isset($_SESSION['user_id'])) {
        try {
            $stmt = $pdo->prepare("SELECT * FROM users WHERE user_id = :id AND status = 'active'");
            $stmt->execute(['id' => $_SESSION['user_id']]);
            if ($stmt->rowCount() === 1) {
                $current_user = $stmt->fetchObject();
            }
        } catch (PDOException $e) {
            // Catch and log the error, but navbar will just show the login link
            error_log('Navbar user lookup failed: ' . $e->getMessage());
        }
    }
}
?>
<nav class="navbar navbar-expand-lg navbar-dark bg-dark">
    <div class="container-fluid">
        <a class="navbar-brand" href="<?= $base ?>/index.php">
            <i class="bi bi-heart-fill text-danger"></i> SafePaws Rescue
        </a>
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
            <span class="navbar-toggler-icon"></span>
        </button>
        <div class="collapse navbar-collapse" id="navbarNav">
            <ul class="navbar-nav me-auto">
                <li class="nav-item">
                    <a class="nav-link" href="<?= $base ?>/index.php">
                        <i class="bi bi-house"></i> Home
                    </a>
                </li>

                <!-- Public links - always visible -->
                <li class="nav-item">
                    <a class="nav-link" href="<?= $base ?>/public/animals.php">
                        <i class="bi bi-search-heart"></i> Adopt
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="<?= $base ?>/contact/index.php">
                        <i class="bi bi-envelope"></i> Contact Us
                    </a>
                </li>

                <!-- Admin links - only shown to a logged-in user -->
                <?php if ($current_user): ?>
                    <li class="nav-item">
                        <a class="nav-link" href="<?= $base ?>/animals/index.php">
                            <i class="bi bi-clipboard-heart"></i> Animals
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="<?= $base ?>/foster_carers/list.php">
                            <i class="bi bi-people"></i> Foster Carers
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="<?= $base ?>/partner_organisations/list.php">
                            <i class="bi bi-building"></i> Partners
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="<?= $base ?>/users/list.php">
                            <i class="bi bi-person-gear"></i> Users
                        </a>
                    </li>
                <?php endif; ?>
            </ul>

            <ul class="navbar-nav">
                <?php if ($current_user): ?>
                    <li class="nav-item d-flex align-items-center">
                        <span class="navbar-text me-3 d-flex align-items-center gap-1">
                            <i class="bi bi-person-circle"></i>
                            <?= htmlentities($current_user->first_name ?? 'Admin') ?>
                        </span>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link btn btn-outline-danger btn-sm" href="<?= $base ?>/auth/logout.php">
                            <i class="bi bi-box-arrow-right"></i> Logout
                        </a>
                    </li>
                <?php else: ?>
                    <li class="nav-item">
                        <a class="nav-link" href="<?= $base ?>/auth/login.php">
                            <i class="bi bi-box-arrow-in-right"></i> Login
                        </a>
                    </li>
                <?php endif; ?>
            </ul>
        </div>
    </div>
</nav>