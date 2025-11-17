<?php
// Resident Sidebar Template
// This template provides the sidebar navigation for resident pages
// Usage: include 'sidebar.php' and set $current_page variable to highlight current page
$current_page = $current_page ?? '';
?>


<!-- Sidebar -->
<div id="sidebar" class="d-flex flex-column bg-white position-fixed" style="width: 250px; height: calc(100vh - 70px); top: 70px; left: 0; z-index: var(--z-sidebar); transition: width 0.3s ease, transform 0.3s ease;">
    <!-- Docker Button -->
    <button class="docker-btn undocked" id="dockerBtn" aria-label="Toggle sidebar dock">
        <i class="fas fa-chevron-right"></i>
    </button>
    <div class="p-3">
    <script src="../js/docker.js"></script>
        
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h4 class="mb-0 text-center flex-grow-1 sidebar-text">
                <span class="d-none d-md-inline">Welcome,<br><?=$_SESSION['user']?></span>
                <span class="d-md-none">Welcome</span>
            </h4>
        </div>
        <hr class="my-3 sidebar-divider">
        <button onclick="window.location.href='announcements.php';" class="btn <?= $current_page === 'announcements' ? 'btn-primary' : 'btn-outline-primary' ?> w-100 mb-2 sidebar-button" <?= $current_page === 'announcements' ? 'disabled' : '' ?>>
            <i class="bi bi-newspaper me-2"></i>
            <span class="sidebar-text">Announcements</span>
        </button>
        <button onclick="window.location.href='manage.php';" class="btn <?= $current_page === 'manage' ? 'btn-primary' : 'btn-outline-primary' ?> w-100 mb-2 sidebar-button" <?= $current_page === 'manage' ? 'disabled' : '' ?>>
            <i class="bi bi-gear-fill me-2"></i>
            <span class="sidebar-text">Manage QR</span>
        </button>
        <button onclick="window.location.href='generateQR.php';" class="btn <?= $current_page === 'generate' ? 'btn-primary' : 'btn-outline-primary' ?> w-100 mb-2 sidebar-button" <?= $current_page === 'generate' ? 'disabled' : '' ?>>
            <i class="bi bi-plus-circle-fill me-2"></i>
            <span class="sidebar-text">Create QR</span>
        </button>
        <button onclick="window.location.href='chat_resident.php';" class="btn <?= $current_page === 'chat' ? 'btn-primary' : 'btn-outline-primary' ?> w-100 mb-2 sidebar-button" <?= $current_page === 'chat' ? 'disabled' : '' ?>>
            <i class="bi bi-chat-dots-fill me-2"></i>
            <span class="sidebar-text">Security Chat</span>
        </button>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const sidebar = document.getElementById('sidebar');
    const sidebarToggle = document.getElementById('sidebarToggle');
    const mobileToggle = document.getElementById('mobileSidebarToggle');
    const toggleIcon = document.getElementById('toggleIcon');
    const closeSidebar = document.getElementById('closeSidebar');
    const mainContent = document.querySelector('.main-content');
    const sidebarTexts = document.querySelectorAll('.sidebar-text');
    const sidebarButtons = document.querySelectorAll('.sidebar-button');
    
    const MOBILE_BP = 768;
    let isCollapsed = false;

    function isMobile() { return window.innerWidth < MOBILE_BP; }

    function openMobileSidebar() {
        if (!sidebar) return;
        sidebar.classList.add('mobile-open');
        sidebar.classList.remove('mobile-closed');
        sidebar.style.transform = 'translateX(0)';
        if (closeSidebar) closeSidebar.style.display = 'inline-flex';
    }

    function closeMobileSidebar() {
        if (!sidebar) return;
        sidebar.classList.remove('mobile-open');
        sidebar.classList.add('mobile-closed');
        sidebar.style.transform = 'translateX(-100%)';
        if (closeSidebar) closeSidebar.style.display = 'none';
    }

    function setDesktopCollapsed(collapsed) {
        if (!sidebar) return;
        isCollapsed = !!collapsed;
        if (isCollapsed) {
            sidebar.classList.add('collapsed');
            sidebar.classList.remove('expanded');
            if (mainContent) mainContent.classList.add('collapsed');
        } else {
            sidebar.classList.remove('collapsed');
            sidebar.classList.add('expanded');
            if (mainContent) {
                mainContent.classList.remove('collapsed');
                mainContent.classList.add('expanded');
            }
        }
    }

    function toggleDesktopCollapse() {
        setDesktopCollapsed(!isCollapsed);
        toggleIcon.className = isCollapsed ? 'bi bi-chevron-right' : 'bi bi-chevron-left';
    }

    function handleResize() {
        if (isMobile()) {
            // mobile: close overlay by default
            closeMobileSidebar();
            setDesktopCollapsed(false);
        } else {
            // desktop: ensure sidebar visible and apply collapsed state
            sidebar.classList.remove('mobile-open', 'mobile-closed');
            sidebar.style.transform = 'translateX(0)';
            setDesktopCollapsed(isCollapsed);
            if (closeSidebar) closeSidebar.style.display = 'none';
        }
    }

    // Event bindings
    if (sidebarToggle) {
        sidebarToggle.addEventListener('click', function(e) {
            if (isMobile()) {
                openMobileSidebar();
            } else {
                toggleDesktopCollapse();
            }
        });
    }

    if (mobileToggle) {
        mobileToggle.addEventListener('click', function(e) {
            e.stopPropagation();
            if (sidebar.classList.contains('mobile-open')) closeMobileSidebar();
            else openMobileSidebar();
        });
    }

    if (closeSidebar) {
        closeSidebar.addEventListener('click', function(e) {
            e.stopPropagation();
            closeMobileSidebar();
        });
    }

    document.addEventListener('click', function(e) {
        if (isMobile() && sidebar && sidebar.classList.contains('mobile-open')) {
            const clickedOutside = !sidebar.contains(e.target) && !(mobileToggle && mobileToggle.contains(e.target));
            if (clickedOutside) closeMobileSidebar();
        }
    });

    window.addEventListener('resize', handleResize);

    // Initialize
    handleResize();

});

</script>
