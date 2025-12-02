// Docker functionality
document.addEventListener('DOMContentLoaded', function() {
    const sidebar = document.getElementById('sidebar');
    const dockerBtn = document.getElementById('dockerBtn');
    const mainContent = document.querySelector('.main-content');

    if (!sidebar || !dockerBtn) return;

    // Initial state
    const initialState = localStorage.getItem('sidebarDocked') === 'true';
    let isDocked = initialState;

    function setSidebarState(docked) {
        isDocked = docked;
        
        // Update button
        dockerBtn.innerHTML = isDocked ? 
            '<i class="fas fa-chevron-left"></i>' : 
            '<i class="fas fa-chevron-right"></i>';
        
        // Update classes
        dockerBtn.classList.toggle('docked', isDocked);
        dockerBtn.classList.toggle('undocked', !isDocked);
        
        // Update sidebar width
        sidebar.style.width = isDocked ? '250px' : '70px';
        
        // Update main content
        if (mainContent && window.innerWidth > 768) {
            mainContent.style.marginLeft = isDocked ? '250px' : '70px';
        }
        
        // Update sidebar content visibility
        const sidebarTexts = document.querySelectorAll('.sidebar-text');
        const sidebarDivider = document.querySelector('.sidebar-divider');
        
        sidebarTexts.forEach(text => {
            text.style.display = isDocked ? 'inline' : 'none';
        });
        
        if (sidebarDivider) {
            sidebarDivider.style.display = isDocked ? 'block' : 'none';
        }
        
        // Update sidebar buttons
        const sidebarButtons = document.querySelectorAll('.sidebar-button');
        sidebarButtons.forEach(button => {
            button.style.justifyContent = isDocked ? 'flex-start' : 'center';
            button.style.padding = isDocked ? '' : '8px';
        });
        
        // Save state
        localStorage.setItem('sidebarDocked', isDocked);
    }

    // Set initial state
    setSidebarState(isDocked);

    // Handle clicks
    dockerBtn.addEventListener('click', () => {
        setSidebarState(!isDocked);
    });

    // Handle window resize
    window.addEventListener('resize', () => {
        if (window.innerWidth <= 768) {
            // Mobile: Reset sidebar and hide docker button
            sidebar.style.width = '250px';
            if (mainContent) mainContent.style.marginLeft = '0';
            dockerBtn.style.display = 'none';
            
            // Show all sidebar content
            document.querySelectorAll('.sidebar-text').forEach(el => 
                el.style.display = 'inline'
            );
            document.querySelector('.sidebar-divider').style.display = 'block';
            document.querySelectorAll('.sidebar-button').forEach(btn => {
                btn.style.justifyContent = 'flex-start';
                btn.style.padding = '';
            });
        } else {
            // Desktop: Restore state
            dockerBtn.style.display = 'flex';
            setSidebarState(isDocked);
        }
    });
});