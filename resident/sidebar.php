<?php
// Resident Sidebar Template
// This template provides the sidebar navigation for resident pages
// Usage: include 'sidebar.php' and set $current_page variable to highlight current page
$current_page = $current_page ?? '';
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
    const toggleIcon = document.getElementById('toggleIcon');
    const closeSidebar = document.getElementById('closeSidebar');
    const mainContent = document.querySelector('.main-content');
    const dividerEl = document.querySelector('.sidebar-divider');
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
    
    // Close sidebar on mobile (only if close button exists)
    if (closeSidebar) {
        closeSidebar.addEventListener('click', function() {
            if (window.innerWidth < 768) {
                sidebar.style.transform = 'translateX(-100%)';
            } else {
                toggleSidebar();
            }
        });
    }
    
    // Close sidebar when clicking outside on mobile
    document.addEventListener('click', function(e) {
        if (window.innerWidth < 768 && 
            !sidebar.contains(e.target) && 
            !sidebarToggle.contains(e.target) &&
            sidebar.style.transform === 'translateX(0)') {
            sidebar.style.transform = 'translateX(-100%)';
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
         if (dividerEl) dividerEl.style.display = 'block';
         sidebarButtons.forEach(button => { button.style.justifyContent = 'flex-start'; button.style.padding = ''; });
         if (mainContent) {
             mainContent.classList.remove('collapsed', 'expanded');
         }
     } else {
         // Desktop: Ensure sidebar starts visible and docked
         sidebar.style.transform = 'translateX(0)';
         sidebar.style.width = '250px';
         sidebar.classList.add('show');
         if (mainContent) {
             mainContent.classList.remove('collapsed');
             mainContent.classList.add('expanded');
             mainContent.style.marginLeft = '250px';
         }
         toggleIcon.className = 'bi bi-chevron-left';
     }
     
     // Initialize
     handleMobileResize();
});

</script>
