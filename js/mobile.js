// Mobile sidebar toggle functionality
function toggleSidebar() {
    const sidebar = document.querySelector('.sidebar');
    if (sidebar) {
        sidebar.classList.toggle('show');
    }
}

// Close sidebar when clicking outside on mobile
document.addEventListener('click', function(event) {
    const sidebar = document.querySelector('.sidebar');
    const toggleBtn = document.querySelector('.sidebar-toggle');
    
    if (window.innerWidth <= 768 && sidebar && sidebar.classList.contains('show')) {
        if (!sidebar.contains(event.target) && !toggleBtn.contains(event.target)) {
            sidebar.classList.remove('show');
        }
    }
});

// Handle window resize
window.addEventListener('resize', function() {
    const sidebar = document.querySelector('.sidebar');
    if (window.innerWidth > 768 && sidebar) {
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
