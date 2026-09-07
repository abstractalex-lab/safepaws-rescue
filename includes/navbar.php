<?php
// includes/navbar.php
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
                        <i class="bi bi-speedometer2"></i> Dashboard
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="../animals/list.php">
                        <i class="bi bi-paw"></i> Animals
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="../foster_carers/list.php">
                        <i class="bi bi-people"></i> Foster Carers
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link active" href="list.php">
                        <i class="bi bi-building"></i> Partners
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="../applications/list.php">
                        <i class="bi bi-file-text"></i> Applications
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="../contact/list.php">
                        <i class="bi bi-envelope"></i> Messages
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="../users/list.php">
                        <i class="bi bi-person-gear"></i> Users
                    </a>
                </li>
            </ul>
            <ul class="navbar-nav">
                <li class="nav-item">
                    <span class="navbar-text me-3">
                        <i class="bi bi-person-circle"></i>
                        <?= htmlspecialchars($_SESSION['user_name'] ?? 'Admin') ?>
                    </span>
                </li>
                <li class="nav-item">
                    <a class="nav-link btn btn-outline-danger btn-sm" href="../auth/logout.php">
                        <i class="bi bi-box-arrow-right"></i> Logout
                    </a>
                </li>
            </ul>
        </div>
    </div>
</nav>
