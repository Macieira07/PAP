<?php
// Iniciar sessão
session_start();

// Função para escapar saída
function e($text) {
    return htmlspecialchars($text, ENT_QUOTES, 'UTF-8');
}

// Nome do administrador da sessão
$admin_nome = $_SESSION['admin_nome'] ?? 'Administrador';
?>
<!DOCTYPE html>
<html lang="pt-PT">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Painel da Quinta Flores</title>
    <link rel="stylesheet" href="style.css">
    <script>
        // Adicionar classe de carregamento
        document.documentElement.classList.add('loading');
        
        // Remover classe após carregamento
        window.addEventListener('load', function() {
            document.documentElement.classList.remove('loading');
        });
    </script>
</head>
<body>
    <!-- Indicador de carregamento -->
    <div class="loading-indicator"></div>

    <!-- Botão de alternar menu -->
    <button class="menu-toggle" aria-label="Alternar menu" aria-expanded="false" aria-controls="sidebar" data-tooltip="Mostrar Menu">
        <span></span>
        <span></span>
        <span></span>
    </button>

    <!-- Sidebar -->
    <div id="sidebar" class="sidebar" aria-hidden="true">
        <div class="sidebar-header">
            <span class="logo-icon">🌼</span>
            <h2>Quinta Flores</h2>
        </div>
        <nav class="sidebar-nav">
            <ul>
                <li><a href="admin_index.php" class="active"><span class="icon">🏠</span> Início</a></li>
                <li><a href="admin_funcionarios.php" class="ajax-link" data-target="main-content"><span class="icon">👷</span> Funcionários</a></li>
                <li><a href="admin_hospedes.php" class="ajax-link" data-target="main-content"><span class="icon">🧍</span> Hóspedes</a></li>
                <li><a href="admin_reservas.php" class="ajax-link" data-target="main-content"><span class="icon">📅</span> Reservas</a></li>
                <li class="logout"><a href="logout.php"><span class="icon">🚪</span> Sair</a></li>
            </ul>
        </nav>
    </div>

    <!-- Conteúdo principal -->
    <div id="main-content" class="main">
        <div class="welcome-card">
            <h1>Bem-vindo, <span class="admin-name"><?php echo e($admin_nome); ?></span>!</h1>
            <p class="welcome-message">Acesse todas as funcionalidades do sistema e gerencie facilmente a sua propriedade. Escolha uma das opções no menu para começar a administrar a Quinta Flores.</p>
        </div>
        
        <div class="dashboard-grid">
            <div class="dashboard-card">
                <div class="card-icon">👷</div>
                <h3>Funcionários</h3>
                <p>Gerencie sua equipe, cadastre novos funcionários e atribua funções específicas para otimizar o trabalho da propriedade.</p>
                <a href="funcionarios.php" class="link ajax-link" data-target="main-content">Acessar Agora →</a>
            </div>
            
            <div class="dashboard-card">
                <div class="card-icon">🧍</div>
                <h3>Hóspedes</h3>
                <p>Visualize informações detalhadas sobre seus clientes, histórico de estadias e preferências para melhorar a experiência de hospedagem.</p>
                <a href="admin_hospedes.php" class="link ajax-link" data-target="main-content">Acessar Agora →</a>
            </div>
            
            <div class="dashboard-card">
                <div class="card-icon">📅</div>
                <h3>Reservas</h3>
                <p>Controle todas as estadias agendadas, gerencie disponibilidade de quartos e acompanhe o fluxo de hóspedes da propriedade.</p>
                <a href="admin_reservas.php" class="link ajax-link" data-target="main-content">Acessar Agora →</a>
            </div>
        </div>
    </div>

    <!-- Botão de alternar tema -->
    <div class="theme-toggle" id="themeToggle" aria-label="Alternar tema" data-tooltip="Alternar tema">
        <span class="theme-icon light">☀️</span>
        <span class="theme-icon dark">🌙</span>
    </div>

    <script src="main.js"></script>
</body>
</html>