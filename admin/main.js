/* =================================================================
   PAINEL ADMINISTRATIVO - QUINTA FLORES
   JavaScript principal com todas as funcionalidades interativas
   Última atualização: Abril 2025
   ================================================================= */

// Aguarda o DOM estar completamente carregado
document.addEventListener('DOMContentLoaded', () => {
    // Inicializa todos os componentes
    initializeComponents();
});

/**
 * Função principal que inicializa todos os componentes do painel
 */
function initializeComponents() {
    // Componentes principais
    const sidebar = document.querySelector('.sidebar');
    const menuToggle = document.querySelector('.menu-toggle');
    const themeToggle = document.getElementById('themeToggle');
    
    // Inicializa cada funcionalidade
    initializePreloader();
    initializeSidebar(sidebar, menuToggle);
    initializeTheme(themeToggle);
    initializeTableInteractions();
    initializeNotifications();
    initializeAnimations();
}

/**
 * Inicializa o preloader da página
 * Mostra uma animação de carregamento até que todo o conteúdo esteja pronto
 */
function initializePreloader() {
    const preloader = createPreloaderElement();
    document.body.appendChild(preloader);

    // Remove o preloader quando a página estiver carregada
    window.addEventListener('load', () => {
        preloader.classList.add('fade-out');
        setTimeout(() => preloader.remove(), 500);
    });
}

/**
 * Cria o elemento do preloader com sua animação
 */
function createPreloaderElement() {
    const preloader = document.createElement('div');
    preloader.className = 'preloader';
    preloader.innerHTML = `
        <div class="preloader-content">
            <div class="preloader-spinner"></div>
            <p>Carregando...</p>
        </div>
    `;
    return preloader;
}

/**
 * Inicializa a funcionalidade da barra lateral
 * Controla a abertura/fechamento do menu e suas animações
 */
function initializeSidebar(sidebar, menuToggle) {
    if (!sidebar || !menuToggle) return;

    // Toggle do menu
    menuToggle.addEventListener('click', () => {
        document.body.classList.toggle('sidebar-open');
        updateMenuTooltip(menuToggle);
    });

    // Fecha o menu ao clicar fora
    document.addEventListener('click', (e) => {
        if (!sidebar.contains(e.target) && !menuToggle.contains(e.target)) {
            document.body.classList.remove('sidebar-open');
            updateMenuTooltip(menuToggle);
        }
    });

    // Mostra o menu automaticamente na primeira visita
    if (!localStorage.getItem('menuShown')) {
        setTimeout(() => {
            document.body.classList.add('sidebar-open');
            updateMenuTooltip(menuToggle);
            localStorage.setItem('menuShown', 'true');
        }, 800);
    }
}

/**
 * Atualiza a tooltip do botão do menu
 */
function updateMenuTooltip(menuToggle) {
    const isOpen = document.body.classList.contains('sidebar-open');
    menuToggle.setAttribute('data-tooltip', isOpen ? 'Fechar Menu' : 'Abrir Menu');
}

/**
 * Inicializa o sistema de temas (claro/escuro)
 */
function initializeTheme(themeToggle) {
    if (!themeToggle) return;

    const savedTheme = localStorage.getItem('theme') || 'light';
    document.body.classList.toggle('dark-theme', savedTheme === 'dark');
    updateThemeIcon(themeToggle, savedTheme);

    themeToggle.addEventListener('click', () => {
        const isDark = document.body.classList.toggle('dark-theme');
        localStorage.setItem('theme', isDark ? 'dark' : 'light');
        updateThemeIcon(themeToggle, isDark ? 'dark' : 'light');
    });
}

/**
 * Atualiza o ícone do botão de tema
 */
function updateThemeIcon(themeToggle, theme) {
    themeToggle.innerHTML = theme === 'dark' ? '☀️' : '🌙';
    themeToggle.setAttribute('title', `Mudar para tema ${theme === 'dark' ? 'claro' : 'escuro'}`);
}

/**
 * Inicializa as interações das tabelas
 * Adiciona efeitos hover e ordenação
 */
function initializeTableInteractions() {
    const tables = document.querySelectorAll('.data-table');
    
    tables.forEach(table => {
        // Efeito hover nas linhas
        const rows = table.querySelectorAll('tbody tr');
        rows.forEach(row => {
            row.addEventListener('mouseenter', () => {
                row.style.transform = 'translateX(5px)';
            });
            
            row.addEventListener('mouseleave', () => {
                row.style.transform = '';
            });
        });

        // Ordenação de colunas
        const headers = table.querySelectorAll('th');
        headers.forEach(header => {
            header.addEventListener('click', () => {
                const column = header.cellIndex;
                sortTable(table, column);
            });
        });
    });
}

/**
 * Ordena uma tabela pela coluna especificada
 */
function sortTable(table, column) {
    const tbody = table.querySelector('tbody');
    const rows = Array.from(tbody.querySelectorAll('tr'));
    const isNumeric = rows.every(row => !isNaN(row.cells[column].textContent));
    
    rows.sort((a, b) => {
        const aValue = a.cells[column].textContent;
        const bValue = b.cells[column].textContent;
        
        return isNumeric
            ? Number(aValue) - Number(bValue)
            : aValue.localeCompare(bValue);
    });
    
    rows.forEach(row => tbody.appendChild(row));
}

/**
 * Inicializa o sistema de notificações
 */
function initializeNotifications() {
    // Mostra notificação de boas-vindas após delay
    setTimeout(() => {
        showNotification('Bem-vindo ao painel administrativo!', 'success');
    }, 2000);
}

/**
 * Mostra uma notificação na tela
 */
function showNotification(message, type = 'info') {
    const notification = document.createElement('div');
    notification.className = `notification ${type}`;
    notification.textContent = message;
    
    document.body.appendChild(notification);
    
    // Remove a notificação após 4 segundos
    setTimeout(() => {
        notification.classList.add('fade-out');
        setTimeout(() => notification.remove(), 500);
    }, 4000);
}

/**
 * Inicializa as animações da página
 * Utiliza a biblioteca AOS se disponível
 */
function initializeAnimations() {
    if (typeof AOS !== 'undefined') {
        AOS.init({
            duration: 800,
            easing: 'ease-in-out',
            once: true
        });
    }
}
