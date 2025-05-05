document.addEventListener('DOMContentLoaded', function() {
    const menuToggle = document.querySelector('.menu-toggle');
    const sidebar = document.getElementById('sidebar');
    const body = document.body;
    const themeToggle = document.getElementById('themeToggle');
    
    // Verificar preferência de tema salva
    const currentTheme = localStorage.getItem('theme');
    if (currentTheme === 'light') {
        body.classList.add('light-mode');
    } else if (currentTheme === 'dark') {
        body.classList.remove('light-mode');
    } else {
        // Usar preferência do sistema
        if (window.matchMedia('(prefers-color-scheme: light)').matches) {
            body.classList.add('light-mode');
        }
    }
    
    // Função para alternar o menu
    function toggleMenu() {
        const isOpen = body.classList.toggle('sidebar-open');
        
        // Atualizar atributos ARIA
        menuToggle.setAttribute('aria-expanded', isOpen);
        sidebar.setAttribute('aria-hidden', !isOpen);
        
        // Atualizar tooltip
        menuToggle.setAttribute('data-tooltip', isOpen ? 'Esconder Menu' : 'Mostrar Menu');
        
        // Efeito nos cards quando o menu abre
        if (isOpen) {
            animateCards();
        }
    }
    
    // Animar cards
    function animateCards() {
        document.querySelectorAll('.dashboard-card').forEach((card, index) => {
            setTimeout(() => {
                card.style.transform = 'translateY(-10px)';
                setTimeout(() => {
                    card.style.transform = '';
                }, 300);
            }, 100 * index);
        });
    }
    
    // Efeito inicial - mostrar sidebar automaticamente
    setTimeout(toggleMenu, 800);
    
    // Event listener para o botão de menu
    menuToggle.addEventListener('click', toggleMenu);
    
    // Fechar menu ao clicar fora em dispositivos móveis
    if (window.innerWidth < 992) {
        document.addEventListener('click', function(e) {
            if (body.classList.contains('sidebar-open') && 
                !sidebar.contains(e.target) && 
                !menuToggle.contains(e.target)) {
                toggleMenu();
            }
        });
    }
    
    // Alternar tema
    themeToggle.addEventListener('click', function() {
        if (body.classList.contains('light-mode')) {
            body.classList.remove('light-mode');
            localStorage.setItem('theme', 'dark');
        } else {
            body.classList.add('light-mode');
            localStorage.setItem('theme', 'light');
        }
    });
    
    // Efeito de hover nos cards
    const cards = document.querySelectorAll('.dashboard-card');
    cards.forEach((card) => {
        card.addEventListener('mouseenter', function() {
            cards.forEach(c => {
                if (c !== card) c.style.opacity = '0.7';
            });
        });
        
        card.addEventListener('mouseleave', function() {
            cards.forEach(c => {
                c.style.opacity = '1';
            });
        });
    });
    
    // Animação suave para o card de boas-vindas
    const welcomeCard = document.querySelector('.welcome-card');
    let ticking = false;
    
    window.addEventListener('scroll', function() {
        if (!ticking) {
            window.requestAnimationFrame(function() {
                let scrollPosition = window.scrollY;
                if (scrollPosition > 50) {
                    welcomeCard.style.transform = `translateY(${Math.min(scrollPosition * 0.1, 30)}px)`;
                } else {
                    welcomeCard.style.transform = 'translateY(0)';
                }
                ticking = false;
            });
            ticking = true;
        }
    });
    
    // Configurar links AJAX
    setupAjaxLinks();
    
    // Verificar se há notificações (exemplo)
    setTimeout(() => {
        addNotification('Bem-vindo de volta! Você tem 3 novas reservas para revisar.');
    }, 3000);
});

// Configurar links AJAX
function setupAjaxLinks() {
    document.querySelectorAll('.ajax-link').forEach(link => {
        link.addEventListener('click', function(e) {
            e.preventDefault();
            
            const url = this.getAttribute('href');
            const targetId = this.getAttribute('data-target');
            
            loadContent(url, targetId);
            
            // Atualizar link ativo no menu
            document.querySelectorAll('.sidebar a').forEach(a => {
                a.classList.remove('active');
            });
            this.classList.add('active');
        });
    });
}

// Carregar conteúdo via AJAX
function loadContent(url, targetId) {
    const target = document.getElementById(targetId);
    if (!target) return;
    
    // Mostrar indicador de carregamento
    document.documentElement.classList.add('loading');
    
    fetch(url)
        .then(response => response.text())
        .then(html => {
            // Extrair apenas o conteúdo principal
            const parser = new DOMParser();
            const doc = parser.parseFromString(html, 'text/html');
            const content = doc.querySelector('.main') || doc.body;
            
            target.innerHTML = content.innerHTML;
            
            // Inicializar scripts para o novo conteúdo
            setupAjaxLinks();
            
            // Atualizar URL sem recarregar a página
            history.pushState({}, '', url);
            
            // Animar entrada do novo conteúdo
            target.style.opacity = '0';
            setTimeout(() => {
                target.style.opacity = '1';
            }, 50);
        })
        .catch(error => {
            console.error('Erro ao carregar conteúdo:', error);
            target.innerHTML = '<div class="error-message">Erro ao carregar conteúdo. Por favor, tente novamente.</div>';
        })
        .finally(() => {
            // Esconder indicador de carregamento
            setTimeout(() => {
                document.documentElement.classList.remove('loading');
            }, 300);
        });
}

// Adicionar notificação
function addNotification(message) {
    const notifContainer = document.createElement('div');
    notifContainer.className = 'notification';
    notifContainer.innerHTML = `
        <div class="notification-content">
            <span class="notification-icon">🔔</span>
            <p>${message}</p>
        </div>
        <button class="notification-close" aria-label="Fechar notificação">×</button>
    `;
    
    document.body.appendChild(notifContainer);
    
    // Animar entrada
    setTimeout(() => {
        notifContainer.classList.add('show');
    }, 10);
    
    // Configurar botão de fechar
    notifContainer.querySelector('.notification-close').addEventListener('click', () => {
        notifContainer.classList.remove('show');
        setTimeout(() => {
            notifContainer.remove();
        }, 300);
    });
    
    // Auto-fechar após 5 segundos
    setTimeout(() => {
        if (document.body.contains(notifContainer)) {
            notifContainer.classList.remove('show');
            setTimeout(() => {
                if (document.body.contains(notifContainer)) {
                    notifContainer.remove();
                }
            }, 300);
        }
    }, 5000);
}

// Lidar com navegação do histórico
window.addEventListener('popstate', function() {
    const currentPath = window.location.pathname;
    const link = document.querySelector(`.sidebar a[href="${currentPath}"]`);
    
    if (link) {
        // Simular clique no link correspondente
        link.click();
    } else {
        // Fallback: recarregar a página
        window.location.reload();
    }
});