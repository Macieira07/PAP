// Toggle sidebar on mobile
const menuToggle = document.querySelector('.menu-toggle');
const sidebar = document.querySelector('.sidebar');
const body = document.body;

if (menuToggle) {
    menuToggle.addEventListener('click', () => {
        sidebar.classList.toggle('active');
        body.classList.toggle('sidebar-open');
    });
}

// Toggle dark/light theme
const themeToggle = document.querySelector('.theme-toggle');

if (themeToggle) {
    themeToggle.addEventListener('click', () => {
        body.classList.toggle('dark-theme');
        
        // Save preference to localStorage
        const isDark = body.classList.contains('dark-theme');
        localStorage.setItem('darkTheme', isDark);
        
        // Change icon
        const icon = themeToggle.querySelector('i');
        if (isDark) {
            icon.classList.remove('fa-moon');
            icon.classList.add('fa-sun');
        } else {
            icon.classList.remove('fa-sun');
            icon.classList.add('fa-moon');
        }
    });
}

// Check for saved theme preference
if (localStorage.getItem('darkTheme') === 'true') {
    document.body.classList.add('dark-theme');
    const icon = document.querySelector('.theme-toggle i');
    if (icon) {
        icon.classList.remove('fa-moon');
        icon.classList.add('fa-sun');
    }
} else if (window.matchMedia && window.matchMedia('(prefers-color-scheme: dark)').matches) {
    // If no preference saved, check system preference
    document.body.classList.add('dark-theme');
    const icon = document.querySelector('.theme-toggle i');
    if (icon) {
        icon.classList.remove('fa-moon');
        icon.classList.add('fa-sun');
    }
}

// Close alerts after 5 seconds
setTimeout(() => {
    const alerts = document.querySelectorAll('.alert');
    alerts.forEach(alert => {
        alert.style.opacity = '0';
        setTimeout(() => {
            alert.remove();
        }, 300);
    });
}, 5000);

// Initialize date inputs with today's date
document.addEventListener('DOMContentLoaded', function() {
    const today = new Date().toISOString().split('T')[0];
    const dateInputs = document.querySelectorAll('input[type="date"]');
    
    dateInputs.forEach(input => {
        if (!input.value) {
            input.value = today;
        }
        
        // Set min date to today for check-in dates
        if (input.id === 'data_checkin' || input.name === 'data_checkin') {
            input.min = today;
        }
        
        // Set min date to tomorrow for check-out dates
        if (input.id === 'data_checkout' || input.name === 'data_checkout') {
            const tomorrow = new Date();
            tomorrow.setDate(tomorrow.getDate() + 1);
            input.min = tomorrow.toISOString().split('T')[0];
        }
    });
    
    // Update check-out min date when check-in changes
    const checkinInput = document.getElementById('data_checkin');
    const checkoutInput = document.getElementById('data_checkout');
    
    if (checkinInput && checkoutInput) {
        checkinInput.addEventListener('change', function() {
            const checkinDate = new Date(this.value);
            checkinDate.setDate(checkinDate.getDate() + 1);
            checkoutInput.min = checkinDate.toISOString().split('T')[0];
            
            // If current checkout date is before new min date, update it
            if (new Date(checkoutInput.value) < checkinDate) {
                checkoutInput.value = checkoutInput.min;
            }
        });
    }
});