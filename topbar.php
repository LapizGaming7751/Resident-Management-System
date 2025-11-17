<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
?>
<!-- Add Font Awesome for docker button -->
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
<nav class="navbar navbar-expand-lg navbar-light bg-white shadow-sm">
    <div class="container-fluid">
        <a class="navbar-brand d-flex align-items-center" href="<?= (strpos($_SERVER['REQUEST_URI'], '/security/') !== false || strpos($_SERVER['REQUEST_URI'], '/resident/') !== false || strpos($_SERVER['REQUEST_URI'], '/admin/') !== false) ? '../' : '' ?>index.php">
            <img src="<?= (strpos($_SERVER['REQUEST_URI'], '/security/') !== false || strpos($_SERVER['REQUEST_URI'], '/resident/') !== false || strpos($_SERVER['REQUEST_URI'], '/admin/') !== false) ? '../' : '' ?>images/house-icon.svg" alt="Logo" width="40" height="40" class="d-inline-block align-text-top">
            <span class="fw-bold ms-2 fs-4 text-primary d-none d-sm-inline">Resident Management System</span>
            <span class="fw-bold ms-2 fs-5 text-primary d-sm-none">RMS</span>
        </a>
        <div class="d-flex align-items-center ms-auto">
            <?php if (isset($_SESSION['user']) && isset($_SESSION['type'])): ?>
                <div class="d-flex flex-column align-items-end me-2 me-md-3">
                    <span class="navbar-text fw-semibold text-primary d-none d-sm-inline">
                        <i class="bi bi-person-circle"></i> <?= htmlspecialchars($_SESSION['user']) ?>
                    </span>
                    <span class="navbar-text fw-semibold text-primary d-sm-none">
                        <i class="bi bi-person-circle"></i> <?= htmlspecialchars(substr($_SESSION['user'], 0, 8)) ?>...
                    </span>
                    <span class="badge bg-primary text-light mt-0 fs-6">
                        <?= ucfirst(htmlspecialchars($_SESSION['type'])) ?>
                    </span>
                </div>
                <a href="<?= (strpos($_SERVER['REQUEST_URI'], '/security/') !== false || strpos($_SERVER['REQUEST_URI'], '/resident/') !== false || strpos($_SERVER['REQUEST_URI'], '/admin/') !== false) ? '../' : '' ?>logout.php" class="btn btn-danger btn-sm" id="logoutBtn">
                    <span class="d-none d-sm-inline">Logout</span>
                    <i class="bi bi-box-arrow-right d-sm-none"></i>
                </a>
            <?php else: ?>
                <a href="<?= (strpos($_SERVER['REQUEST_URI'], '/security/') !== false || strpos($_SERVER['REQUEST_URI'], '/resident/') !== false || strpos($_SERVER['REQUEST_URI'], '/admin/') !== false) ? '../' : '' ?>resident/index.php" class="btn btn-outline-primary btn-sm me-2">
                    <span class="d-none d-sm-inline">Sign In</span>
                    <i class="bi bi-box-arrow-in-right d-sm-none"></i>
                </a>
            <?php endif; ?>
        </div>
    </div>
</nav>
