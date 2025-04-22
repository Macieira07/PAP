document.addEventListener('DOMContentLoaded', function() {
    // Core elements
    const menuToggle = document.querySelector('.menu-toggle');
    const sidebar = document.getElementById('sidebar');
    const body = document.body;
    const themeToggle = document.getElementById('themeToggle');
    
    // Preloader animation
    createPreloader();
    
    // Initialize theme preference
    initializeTheme();
    
    // Initialize animations
    initializeAnimations();
    
    // Initialize sidebar
    initializeSidebar();
    
    // Setup event listeners
    setupEventListeners();
    
    // Initialize charts (if they exist)
    setTimeout(() => {
        initializeCharts();
    }, 500);
    
    // Show welcome notification after a delay
    setTimeout(() => {
        addNotification('Bem-vindo ao painel administrativo modernizado!', 'success');
    }, 2000);
});

// Create preloader animation
function createPreloader() {
    const preloader = document.createElement('div');
    preloader.className = 'preloader';
    preloader.innerHTML = `
        <div class="preloader-content">
            <div class="preloader-spinner"></div>
            <p>Carregando...</p>
        </div>
    `;
    document.body.appendChildocument.body.appendChild(preloader);

    window.addEventListener('load', () => {
        preloader.classList.add('fade-out');
        setTimeout(() => {
            preloader.remove();
        }, 500);
    });
}

// Inicializa o tema baseado na preferência armazenada
function initializeTheme() {
    const savedTheme = localStorage.getItem('theme');
    if (savedTheme) {
        document.body.classList.toggle('dark-theme', savedTheme === 'dark');
    }

    themeToggle.addEventListener('click', () => {
        const isDark = document.body.classList.toggle('dark-theme');
        localStorage.setItem('theme', isDark ? 'dark' : 'light');
    });
}

// Inicializa as animações (usando, por exemplo, AOS.js)
function initializeAnimations() {
    if (window.AOS) {
        AOS.init();
    }
}

// Inicializa a barra lateral
function initializeSidebar() {
    menuToggle.addEventListener('click', () => {
        sidebar.classList.toggle('open');
        body.classList.toggle('sidebar-open');
    });
}

// Define os listeners de eventos principais
function setupEventListeners() {
    // Exemplo: fechar o sidebar ao clicar fora
    document.addEventListener('click', (e) => {
        if (!sidebar.contains(e.target) && !menuToggle.contains(e.target)) {
            sidebar.classList.remove('open');
            body.classList.remove('sidebar-open');
        }
    });
}

// Inicializa gráficos (verifica se as funções/libraries estão carregadas)
function initializeCharts() {
    if (typeof Chart !== 'undefined') {
        const chartElement = document.getElementById('myChart');
        if (chartElement) {
            new Chart(chartElement, {
                type: 'bar',
                data: {
                    labels: ['Janeiro', 'Fevereiro', 'Março', 'Abril'],
                    datasets: [{
                        label: 'Vendas',
                        data: [120, 190, 300, 500],
                        backgroundColor: 'rgba(54, 162, 235, 0.6)'
                    }]
                },
                options: {
                    responsive: true,
                    scales: {
                        y: {
                            beginAtZero: true
                        }
                    }
                }
            });
        }
    }
}

// Exibe uma notificação de boas-vindas
function addNotification(message, type = 'info') {
    const notification = document.createElement('div');
    notification.className = `notification ${type}`;
    notification.textContent = message;

    document.body.appendChild(notification);

    setTimeout(() => {
        notification.classList.add('fade-out');
        setTimeout(() => notification.remove(), 500);
    }, 4000);
}
