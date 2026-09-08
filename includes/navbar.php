<?php
// includes/navbar.php
// check if user is logged in for the navbar display
/** @var PDO $pdo */
$current_user = null;
if (isset($_SESSION['user_id'])) {
    try {
        $stmt = $pdo->prepare("SELECT * FROM users WHERE user_id = :id AND status = 'active'");
        $stmt->execute(['id' => $_SESSION['user_id']]);
        if ($stmt->rowCount() === 1) {
            $current_user = $stmt->fetchObject();
        }
    } catch (PDOException $e) {
        // silent fail
    }
}
?>
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
                    <a class="nav-link" href="../index.php">
                        <i class="bi bi-house"></i> Home
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="../partner_organisations/list.php">
                        <i class="bi bi-building"></i> Partners
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="../foster_carers/list.php">
                        <i class="bi bi-people"></i> Foster Carers
                    </a>
                </li>
            </ul>
            <ul class="navbar-nav">
                <?php if ($current_user): ?>
                    <li class="nav-item">
                        <span class="navbar-text me-3">
                            <i class="bi bi-person-circle"></i>
                            <?= htmlspecialchars($current_user->first_name ?? 'Admin') ?>
                        </span>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link btn btn-outline-danger btn-sm" href="../auth/logout.php">
                            <i class="bi bi-box-arrow-right"></i> Logout
                        </a>
                    </li>
                <?php else: ?>
                    <li class="nav-item">
                        <a class="nav-link" href="../auth/login.php">
                            <i class="bi bi-box-arrow-in-right"></i> Login
                        </a>
                    </li>
                <?php endif; ?>
            </ul>
        </div>
    </div>
</nav>

