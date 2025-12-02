<?php
// Admin Sidebar Template
// Usage: include 'sidebar.php' and set $current_page variable
$current_page = $current_page ?? '';
$access_level = $_SESSION['access_level'] ?? 1;
?>


<!-- Sidebar -->
<div id="sidebar" class="d-flex flex-column bg-white position-fixed">
    <div class="p-3" style="padding-top: 90px;">
        <!-- Dock Button (Desktop & Mobile) -->
        <button class="btn btn-sm btn-outline-secondary d-flex align-items-center justify-content-center rounded-circle sidebar-dock-btn" id="sidebarToggle" style="width: 32px; height: 32px; margin-bottom: 1rem;">
            <i class="bi bi-chevron-left" id="toggleIcon"></i>
        </button>
        
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h4 class="mb-0 text-center flex-grow-1 sidebar-text">
                <span class="d-none d-md-inline">Welcome,<br><?=$_SESSION['user']?></span>
                <span class="d-md-none">Welcome</span>
            </h4>
        </div>
        <hr class="my-3 sidebar-divider">

        <button onclick="window.location.href='announcements.php';" 
            class="btn <?= $current_page === 'announcements' ? 'btn-primary' : 'btn-outline-primary' ?> w-100 mb-2 sidebar-button" 
            <?= $current_page === 'announcements' ? 'disabled' : '' ?>>
            <i class="bi bi-newspaper me-2"></i>
            <span class="sidebar-text">Announcements</span>
        </button>

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
            if (mainContent) {
                mainContent.style.marginLeft = '70px';
                mainContent.classList.remove('expanded');
                mainContent.classList.add('collapsed');
            }
            toggleIcon.className = 'bi bi-chevron-right';
            sidebarTexts.forEach(t => t.style.display = 'none');
            document.querySelector('.sidebar-divider').style.display = 'none';
            sidebarButtons.forEach(b => { b.style.justifyContent = 'center'; b.style.padding = '8px'; });
            // Button stays in sidebar - no positioning needed
        } else {
            sidebar.style.width = '250px';
            if (mainContent) {
                mainContent.style.marginLeft = '250px';
                mainContent.classList.remove('collapsed');
                mainContent.classList.add('expanded');
            }
            toggleIcon.className = 'bi bi-chevron-left';
            sidebarTexts.forEach(t => t.style.display = 'inline');
            document.querySelector('.sidebar-divider').style.display = 'block';
            sidebarButtons.forEach(b => { b.style.justifyContent = 'flex-start'; b.style.padding = ''; });
            // Button stays in sidebar - no positioning needed
        }
    }

    function handleResize() {
        if (window.innerWidth < 768) {
            // Mobile: Use collapse/expand behavior like desktop, but no margin manipulation
            sidebar.style.transform = 'translateX(0)';
            if (!isCollapsed) {
                sidebar.style.width = '250px';
                toggleIcon.className = 'bi bi-chevron-left';
                sidebarTexts.forEach(t => t.style.display = 'inline');
                document.querySelector('.sidebar-divider').style.display = 'block';
                sidebarButtons.forEach(b => { b.style.justifyContent = 'flex-start'; b.style.padding = ''; });
            } else {
                sidebar.style.width = '70px';
                toggleIcon.className = 'bi bi-chevron-right';
                sidebarTexts.forEach(t => t.style.display = 'none');
                document.querySelector('.sidebar-divider').style.display = 'none';
                sidebarButtons.forEach(b => { b.style.justifyContent = 'center'; b.style.padding = '8px'; });
            }
        } else {
            // Desktop: collapse/expand behavior
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
            // Mobile: Use collapse/expand behavior instead of slide in/out
            toggleSidebar();
        } else {
            toggleSidebar();
        }
    });

    window.addEventListener('resize', handleResize);
    
    // Initialize sidebar state
    if (window.innerWidth <= 768) {
        // Mobile: Start with sidebar expanded and visible
        sidebar.style.transform = 'translateX(0)';
        sidebar.style.width = '250px';
        sidebar.classList.add('show');
        toggleIcon.className = 'bi bi-chevron-left';
        sidebarTexts.forEach(t => t.style.display = 'inline');
        document.querySelector('.sidebar-divider').style.display = 'block';
        sidebarButtons.forEach(b => { b.style.justifyContent = 'flex-start'; b.style.padding = ''; });
        if (mainContent) {
            mainContent.classList.remove('collapsed', 'expanded');
        }
    } else {
        // Desktop: Ensure sidebar starts visible
        sidebar.style.transform = 'translateX(0)';
        sidebar.classList.add('show');
        if (mainContent) {
            mainContent.classList.remove('collapsed');
            mainContent.classList.add('expanded');
        }
    }
    
    handleResize();
});

</script>
