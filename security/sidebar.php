<?php
// Security Sidebar Template
// This template provides the sidebar navigation for security pages
// Usage: include 'sidebar.php' and set $current_page variable to highlight current page
$current_page = $current_page ?? '';
?>


<!-- Sidebar -->
<div id="sidebar" class="d-flex flex-column bg-white position-fixed shadow-sm" style="width: 250px; height: calc(100vh - 70px); top: 70px; left: 0; z-index: var(--z-sidebar); transition: width 0.3s ease, transform 0.3s ease;">
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
        <button onclick="window.location.href='scanner.php';" class="btn <?= $current_page === 'scanner' ? 'btn-primary' : 'btn-outline-primary' ?> w-100 mb-2 sidebar-button" <?= $current_page === 'scanner' ? 'disabled' : '' ?>>
            <i class="bi bi-qr-code-scan me-2"></i>
            <span class="sidebar-text">Scanner</span>
        </button>
        <button onclick="window.location.href='logs.php';" class="btn <?= $current_page === 'logs' ? 'btn-primary' : 'btn-outline-primary' ?> w-100 mb-2 sidebar-button" <?= $current_page === 'logs' ? 'disabled' : '' ?>>
            <i class="bi bi-list-check me-2"></i>
            <span class="sidebar-text">Manage Logs</span>
        </button>
        <button onclick="window.location.href='chat_security.php';" class="btn <?= $current_page === 'chat' ? 'btn-primary' : 'btn-outline-primary' ?> w-100 mb-2 sidebar-button" <?= $current_page === 'chat' ? 'disabled' : '' ?>>
            <i class="bi bi-chat-dots-fill me-2"></i>
            <span class="sidebar-text">Security Chat</span>
        </button>
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
    
     // Toggle sidebar collapse/expand
     function toggleSidebar() {
         isCollapsed = !isCollapsed;
         
         if (isCollapsed) {
             // Collapse sidebar
             sidebar.style.width = '70px';
             sidebar.style.transform = 'translateX(0)';
             if (mainContent) {
                 mainContent.style.marginLeft = '70px';
                 mainContent.classList.remove('expanded');
                 mainContent.classList.add('collapsed');
             }
             toggleIcon.className = 'bi bi-chevron-right';
             
             // Hide text elements
             sidebarTexts.forEach(text => {
                 text.style.display = 'none';
             });
             
             // Hide divider
             const divider = document.querySelector('.sidebar-divider');
             if (divider) {
                 divider.style.display = 'none';
             }
             
             // Center buttons
             sidebarButtons.forEach(button => {
                 button.style.justifyContent = 'center';
                 button.style.padding = '8px';
             });
             
             // Button stays in sidebar - no positioning needed
         } else {
             // Expand sidebar
             sidebar.style.width = '250px';
             sidebar.style.transform = 'translateX(0)';
             if (mainContent) {
                 mainContent.style.marginLeft = '250px';
                 mainContent.classList.remove('collapsed');
                 mainContent.classList.add('expanded');
             }
             toggleIcon.className = 'bi bi-chevron-left';
             
             // Show text elements
             sidebarTexts.forEach(text => {
                 text.style.display = 'inline';
             });
             
             // Show divider
             const divider = document.querySelector('.sidebar-divider');
             if (divider) {
                 divider.style.display = 'block';
             }
             
             // Reset button styling
             sidebarButtons.forEach(button => {
                 button.style.justifyContent = 'flex-start';
                 button.style.padding = '';
             });
             
             // Button stays in sidebar - no positioning needed
         }
     }
    
     // Handle mobile behavior
     function handleMobileResize() {
         if (window.innerWidth < 768) {
             // Mobile: Use collapse/expand behavior like desktop, but no margin manipulation
             sidebar.style.transform = 'translateX(0)';
             if (!isCollapsed) {
                 sidebar.style.width = '250px';
                 toggleIcon.className = 'bi bi-chevron-left';
                 sidebarTexts.forEach(text => text.style.display = 'inline');
                 document.querySelector('.sidebar-divider').style.display = 'block';
                 sidebarButtons.forEach(button => { button.style.justifyContent = 'flex-start'; button.style.padding = ''; });
             } else {
                 sidebar.style.width = '70px';
                 toggleIcon.className = 'bi bi-chevron-right';
                 sidebarTexts.forEach(text => text.style.display = 'none');
                 document.querySelector('.sidebar-divider').style.display = 'none';
                 sidebarButtons.forEach(button => { button.style.justifyContent = 'center'; button.style.padding = '8px'; });
             }
         } else {
             // Desktop: collapse/expand behavior
             if (!isCollapsed) {
                 sidebar.style.width = '250px';
                 sidebar.style.transform = 'translateX(0)';
                 if (mainContent) {
                     mainContent.style.marginLeft = '250px';
                 }
                 toggleIcon.className = 'bi bi-chevron-left';
             } else {
                 sidebar.style.width = '70px';
                 sidebar.style.transform = 'translateX(0)';
                 if (mainContent) {
                     mainContent.style.marginLeft = '70px';
                 }
                 toggleIcon.className = 'bi bi-chevron-right';
             }
         }
     }
    
    // Toggle sidebar
    sidebarToggle.addEventListener('click', function() {
        if (window.innerWidth < 768) {
            // Mobile: Use collapse/expand behavior instead of slide in/out
            toggleSidebar();
        } else {
            // Desktop: collapse/expand
            toggleSidebar();
        }
    });
    
     // Handle window resize
     window.addEventListener('resize', handleMobileResize);
     
     // Initialize sidebar state
     if (window.innerWidth <= 768) {
         // Mobile: Start with sidebar expanded and visible
         sidebar.style.transform = 'translateX(0)';
         sidebar.style.width = '250px';
         sidebar.classList.add('show');
         toggleIcon.className = 'bi bi-chevron-left';
         sidebarTexts.forEach(text => text.style.display = 'inline');
         document.querySelector('.sidebar-divider').style.display = 'block';
         sidebarButtons.forEach(button => { button.style.justifyContent = 'flex-start'; button.style.padding = ''; });
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
     
     // Initialize
     handleMobileResize();
});

</script>
