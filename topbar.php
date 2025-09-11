<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
?>
<nav class="navbar navbar-expand-lg navbar-light bg-white shadow-sm">
    <div class="container-fluid">
        <a class="navbar-brand d-flex align-items-center" href="<?= (strpos($_SERVER['REQUEST_URI'], '/security/') !== false || strpos($_SERVER['REQUEST_URI'], '/resident/') !== false || strpos($_SERVER['REQUEST_URI'], '/admin/') !== false) ? '../' : '' ?>index.php">
            <img src="<?= (strpos($_SERVER['REQUEST_URI'], '/security/') !== false || strpos($_SERVER['REQUEST_URI'], '/resident/') !== false || strpos($_SERVER['REQUEST_URI'], '/admin/') !== false) ? '../' : '' ?>images/house-icon.svg" alt="Logo" width="40" height="40" class="d-inline-block align-text-top">
            <span class="fw-bold ms-2" style="font-size:1.35rem;color:#23235b;">Resident Management System</span>
        </a>
        <div class="d-flex align-items-center ms-auto">
            <?php if (isset($_SESSION['user']) && isset($_SESSION['type'])): ?>
                <div class="d-flex flex-column align-items-end me-3">
                    <span class="navbar-text fw-semibold text-primary">
                        <i class="bi bi-person-circle"></i> <?= htmlspecialchars($_SESSION['user']) ?>
                    </span>
                    <span class="badge bg-primary text-light mt-0" style="font-size:1rem;">
                        <?= ucfirst(htmlspecialchars($_SESSION['type'])) ?>
                    </span>
                </div>
                <a href="<?= (strpos($_SERVER['REQUEST_URI'], '/security/') !== false || strpos($_SERVER['REQUEST_URI'], '/resident/') !== false || strpos($_SERVER['REQUEST_URI'], '/admin/') !== false) ? '../' : '' ?>logout.php" class="btn btn-danger">Logout</a>
            <?php else: ?>
                <a href="<?= (strpos($_SERVER['REQUEST_URI'], '/security/') !== false || strpos($_SERVER['REQUEST_URI'], '/resident/') !== false || strpos($_SERVER['REQUEST_URI'], '/admin/') !== false) ? '../' : '' ?>resident/index.php" class="btn btn-outline-primary me-2">Sign In</a>
            <?php endif; ?>
        </div>
    </div>
</nav>
