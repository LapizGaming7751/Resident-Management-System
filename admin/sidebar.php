<?php
// Admin Sidebar Template
// Usage: include 'sidebar.php' and set $current_page variable
$current_page = $current_page ?? '';
$access_level = $_SESSION['access_level'] ?? 1;
?>
<!-- Sidebar Toggle Button -->
<button class="btn btn-primary position-fixed" id="sidebarToggle" style="top: 80px; left: 260px; z-index: 1050; width: 40px; height: 40px; padding: 0; display: flex; align-items: center; justify-content: center; transition: left 0.3s ease; border-radius: 50%; box-shadow: 0 2px 8px rgba(0,0,0,0.15);">
    <i class="bi bi-list" id="toggleIcon" style="font-size: 1.2rem; color: white;"></i>
</button>

<!-- Sidebar -->
<div id="sidebar" class="d-flex flex-column bg-white position-fixed" style="width: 250px; height: calc(100vh - 70px); top: 70px; left: 0; border-radius:0; box-shadow: 2px 0 8px rgba(0,0,0,0.1); justify-content:space-between; z-index: 1040; transition: width 0.3s ease, transform 0.3s ease;">
    <div class="p-3">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h4 class="mb-0 text-center flex-grow-1 sidebar-text">Welcome,<br><?=$_SESSION['user']?></h4>
        </div>
        <hr class="my-3 sidebar-divider">

        <button onclick="window.location.href='manage.php';" 
            class="btn <?= $current_page === 'logs' ? 'btn-primary' : 'btn-outline-primary' ?> w-100 mb-2 sidebar-button" 
            <?= $current_page === 'logs' ? 'disabled' : '' ?>>
            <i class="bi bi-journal-text me-2"></i>
            <span class="sidebar-text">Manage Logs</span>
        </button>

        <button onclick="window.location.href='registerResident.php';" 
            class="btn <?= $current_page === 'add_resident' ? 'btn-primary' : 'btn-outline-primary' ?> w-100 mb-2 sidebar-button" 
            <?= $current_page === 'add_resident' ? 'disabled' : '' ?>>
            <i class="bi bi-person-plus-fill me-2"></i>
            <span class="sidebar-text">Add Resident</span>
        </button>

        <button onclick="window.location.href='registerSecurity.php';" 
            class="btn <?= $current_page === 'add_security' ? 'btn-primary' : 'btn-outline-primary' ?> w-100 mb-2 sidebar-button" 
            <?= $current_page === 'add_security' ? 'disabled' : '' ?>>
            <i class="bi bi-shield-plus me-2"></i>
            <span class="sidebar-text">Add Security</span>
        </button>

        <?php if ($access_level >= 2) { ?>
        <button onclick="window.location.href='registerAdmin.php';" 
            class="btn <?= $current_page === 'add_admin' ? 'btn-primary' : 'btn-outline-primary' ?> w-100 mb-2 sidebar-button" 
            <?= $current_page === 'add_admin' ? 'disabled' : '' ?>>
            <i class="bi bi-person-plus-fill me-2"></i>
            <span class="sidebar-text">Add Admin</span>
        </button>
        <?php } ?>
    </div>
</div>


<script>
document.addEventListener('DOMContentLoaded', function() {
    const sidebar = document.getElementById('sidebar');
    const sidebarToggle = document.getElementById('sidebarToggle');
    const toggleIcon = document.getElementById('toggleIcon');
    const mainContent = document.querySelector('.main-content');
    const sidebarTexts = document.querySelectorAll('.sidebar-text');
    const sidebarButtons = document.querySelectorAll('.sidebar-button');
    
    let isCollapsed = false;

    function toggleSidebar() {
        isCollapsed = !isCollapsed;
        if (isCollapsed) {
            sidebar.style.width = '70px';
            if (mainContent) mainContent.style.marginLeft = '70px';
            toggleIcon.className = 'bi bi-chevron-right';
            sidebarTexts.forEach(t => t.style.display = 'none');
            document.querySelector('.sidebar-divider').style.display = 'none';
            sidebarButtons.forEach(b => { b.style.justifyContent = 'center'; b.style.padding = '8px'; });
            sidebarToggle.style.left = '80px';
        } else {
            sidebar.style.width = '250px';
            if (mainContent) mainContent.style.marginLeft = '250px';
            toggleIcon.className = 'bi bi-chevron-left';
            sidebarTexts.forEach(t => t.style.display = 'inline');
            document.querySelector('.sidebar-divider').style.display = 'block';
            sidebarButtons.forEach(b => { b.style.justifyContent = 'flex-start'; b.style.padding = ''; });
            sidebarToggle.style.left = '260px';
        }
    }

    function handleResize() {
        if (window.innerWidth < 768) {
            sidebar.style.width = '250px';
            sidebar.style.transform = 'translateX(-100%)';
            if (mainContent) mainContent.style.marginLeft = '0';
            toggleIcon.className = 'bi bi-list';
            sidebarTexts.forEach(t => t.style.display = 'inline');
        } else {
            sidebar.style.transform = 'translateX(0)';
            if (!isCollapsed) {
                sidebar.style.width = '250px';
                if (mainContent) mainContent.style.marginLeft = '250px';
                toggleIcon.className = 'bi bi-chevron-left';
            } else {
                sidebar.style.width = '70px';
                if (mainContent) mainContent.style.marginLeft = '70px';
                toggleIcon.className = 'bi bi-chevron-right';
            }
        }
    }

    sidebarToggle.addEventListener('click', function() {
        if (window.innerWidth < 768) {
            sidebar.style.transform = (sidebar.style.transform === 'translateX(0)') 
                ? 'translateX(-100%)' : 'translateX(0)';
        } else {
            toggleSidebar();
        }
    });

    window.addEventListener('resize', handleResize);
    handleResize();
});
</script>
