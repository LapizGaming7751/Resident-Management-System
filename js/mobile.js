// Unified Mobile Sidebar System - Fixes all sidebar issues
document.addEventListener('DOMContentLoaded', function() {
    const sidebar = document.getElementById('sidebar');
    const sidebarToggle = document.getElementById('sidebarToggle');
    const mobileToggle = document.querySelector('.sidebar-toggle');
    
    if (!sidebar) return;
    
    // Mobile sidebar toggle functionality
    function toggleMobileSidebar() {
        if (window.innerWidth <= 768) {
            if (sidebar.style.transform === 'translateX(-100%)' || sidebar.style.transform === '') {
                sidebar.style.transform = 'translateX(0)';
                sidebar.classList.add('show');
            } else {
                sidebar.style.transform = 'translateX(-100%)';
                sidebar.classList.remove('show');
            }
        }
    }
    
    // Desktop sidebar functionality (preserve existing)
    function toggleDesktopSidebar() {
        if (window.innerWidth > 768) {
            // Let existing desktop sidebar logic handle this
            return;
        }
    }
    
    // Unified toggle function
    function toggleSidebar() {
        if (window.innerWidth <= 768) {
            toggleMobileSidebar();
        } else {
            toggleDesktopSidebar();
        }
    }
    
    // Mobile toggle button handler
    if (mobileToggle) {
        mobileToggle.addEventListener('click', function(e) {
            e.preventDefault();
            e.stopPropagation();
            toggleMobileSidebar();
        });
    }
    
    // Desktop toggle button handler (preserve existing behavior)
    if (sidebarToggle) {
        sidebarToggle.addEventListener('click', function(e) {
            if (window.innerWidth > 768) {
                // Let existing desktop logic handle this
                return;
            } else {
                e.preventDefault();
                e.stopPropagation();
                toggleMobileSidebar();
            }
        });
    }
    
    // Close sidebar when clicking outside on mobile
    document.addEventListener('click', function(event) {
        if (window.innerWidth <= 768 && sidebar && sidebar.classList.contains('show')) {
            if (!sidebar.contains(event.target) && 
                !mobileToggle?.contains(event.target) && 
                !sidebarToggle?.contains(event.target)) {
                sidebar.style.transform = 'translateX(-100%)';
                sidebar.classList.remove('show');
            }
        }
    });
    
    // Handle window resize - CRITICAL FIX
    window.addEventListener('resize', function() {
        if (window.innerWidth > 768) {
            // Desktop: Reset mobile sidebar state
            sidebar.style.transform = '';
            sidebar.classList.remove('show');
        } else {
            // Mobile: Ensure sidebar is hidden on resize
            sidebar.style.transform = 'translateX(-100%)';
            sidebar.classList.remove('show');
        }
    });
    
    // Initialize sidebar state
    if (window.innerWidth <= 768) {
        sidebar.style.transform = 'translateX(-100%)';
        sidebar.classList.remove('show');
    }
});

// Mobile table improvements - add data labels for mobile view
document.addEventListener('DOMContentLoaded', function() {
    // Add data labels to table cells for mobile view
    const tables = document.querySelectorAll('.table-responsive table');
    tables.forEach(table => {
        const headers = table.querySelectorAll('thead th');
        const rows = table.querySelectorAll('tbody tr');
        
        rows.forEach(row => {
            const cells = row.querySelectorAll('td');
            cells.forEach((cell, index) => {
                if (headers[index]) {
                    cell.setAttribute('data-label', headers[index].textContent.trim());
                }
            });
        });
    });
});

// Mobile form improvements
document.addEventListener('DOMContentLoaded', function() {
    // Improve mobile form experience
    const forms = document.querySelectorAll('form');
    forms.forEach(form => {
        // Add mobile-friendly input handling
        const inputs = form.querySelectorAll('input, textarea, select');
        inputs.forEach(input => {
            // Prevent zoom on iOS
            if (input.type === 'text' || input.type === 'email' || input.type === 'password') {
                input.style.fontSize = '16px';
            }
        });
    });
});

// Mobile card improvements
document.addEventListener('DOMContentLoaded', function() {
    // Add touch-friendly interactions
    const cards = document.querySelectorAll('.card');
    cards.forEach(card => {
        card.addEventListener('touchstart', function() {
            this.style.transform = 'scale(0.98)';
        });
        
        card.addEventListener('touchend', function() {
            this.style.transform = 'scale(1)';
        });
    });
});

// Enhanced mobile interactions for 360px screens
document.addEventListener('DOMContentLoaded', function() {
    // Improve touch interactions for ultra-narrow screens
    const buttons = document.querySelectorAll('.btn');
    buttons.forEach(button => {
        // Ensure minimum touch target size
        if (button.offsetHeight < 44) {
            button.style.minHeight = '44px';
            button.style.padding = '0.5rem 0.75rem';
        }
        
        // Add touch feedback
        button.addEventListener('touchstart', function() {
            this.style.opacity = '0.8';
        });
        
        button.addEventListener('touchend', function() {
            this.style.opacity = '1';
        });
    });
    
    // Improve form interactions
    const formControls = document.querySelectorAll('.form-control, .form-select');
    formControls.forEach(control => {
        // Ensure minimum touch target size
        if (control.offsetHeight < 44) {
            control.style.minHeight = '44px';
            control.style.padding = '0.6rem';
        }
        
        // Prevent zoom on iOS for inputs
        if (control.type === 'text' || control.type === 'email' || control.type === 'password') {
            control.style.fontSize = '16px';
        }
    });
    
    // Improve QR card interactions on mobile
    const qrCards = document.querySelectorAll('.qr-card-uniform');
    qrCards.forEach(card => {
        card.addEventListener('touchstart', function() {
            this.style.transform = 'scale(0.98)';
            this.style.transition = 'transform 0.1s ease';
        });
        
        card.addEventListener('touchend', function() {
            this.style.transform = 'scale(1)';
        });
    });
    
    // Improve navigation for narrow screens
    const navbar = document.querySelector('.navbar');
    if (navbar && window.innerWidth <= 360) {
        // Stack navigation buttons vertically on ultra-narrow screens
        const navButtons = navbar.querySelector('.nav-buttons');
        if (navButtons) {
            navButtons.style.flexDirection = 'column';
            navButtons.style.width = '100%';
            navButtons.style.gap = '0.25rem';
        }
    }
    
    // Improve table scrolling on mobile
    const tables = document.querySelectorAll('.table-responsive');
    tables.forEach(table => {
        table.style.webkitOverflowScrolling = 'touch';
        table.style.overflowX = 'auto';
    });
    
    // Improve modal interactions on mobile
    const modals = document.querySelectorAll('.modal');
    modals.forEach(modal => {
        modal.addEventListener('touchstart', function(e) {
            // Prevent modal from closing on touch outside
            if (e.target === modal) {
                e.stopPropagation();
            }
        });
    });
});

// Handle orientation changes for mobile
window.addEventListener('orientationchange', function() {
    setTimeout(function() {
        // Recalculate layout after orientation change
        const sidebar = document.getElementById('sidebar');
        if (sidebar && window.innerWidth <= 768) {
            sidebar.style.transform = 'translateX(-100%)';
        }
    }, 100);
});

// Improve scroll behavior on mobile
document.addEventListener('DOMContentLoaded', function() {
    // Smooth scrolling for mobile
    document.documentElement.style.scrollBehavior = 'smooth';
    
    // Prevent horizontal scroll on mobile
    document.body.style.overflowX = 'hidden';
    
    // Improve touch scrolling
    const scrollableElements = document.querySelectorAll('.chat-messages, .user-list, .table-responsive');
    scrollableElements.forEach(element => {
        element.style.webkitOverflowScrolling = 'touch';
    });
});