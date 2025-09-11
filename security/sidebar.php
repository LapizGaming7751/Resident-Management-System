<?php
// Security Sidebar Template
// This template provides the sidebar navigation for security pages
// Usage: include 'sidebar.php' and set $current_page variable to highlight current page
$current_page = $current_page ?? '';
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
            
            // Move toggle button to collapsed position
            sidebarToggle.style.left = '80px';
        } else {
            // Expand sidebar
            sidebar.style.width = '250px';
            sidebar.style.transform = 'translateX(0)';
            if (mainContent) {
                mainContent.style.marginLeft = '250px';
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
            
            // Move toggle button to expanded position
            sidebarToggle.style.left = '260px';
        }
    }
    
    // Handle mobile behavior
    function handleMobileResize() {
        if (window.innerWidth < 768) {
            // Mobile: slide in/out behavior
            sidebar.style.width = '250px';
            sidebar.style.transform = 'translateX(-100%)';
            if (mainContent) {
                mainContent.style.marginLeft = '0';
            }
            toggleIcon.className = 'bi bi-list';
            
            // Show text elements on mobile
            sidebarTexts.forEach(text => {
                text.style.display = 'inline';
            });
            
            // Reset button styling
            sidebarButtons.forEach(button => {
                button.style.justifyContent = 'flex-start';
                button.style.padding = '';
            });
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
            // Mobile: slide in/out
            if (sidebar.style.transform === 'translateX(-100%)') {
                sidebar.style.transform = 'translateX(0)';
            } else {
                sidebar.style.transform = 'translateX(-100%)';
            }
        } else {
            // Desktop: collapse/expand
            toggleSidebar();
        }
    });
    
    // Handle window resize
    window.addEventListener('resize', handleMobileResize);
    
    // Initialize
    handleMobileResize();
});
</script>
