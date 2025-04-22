<?php
// Iniciar sessão
session_start();

// Função para escapar saída
function e($text) {
    return htmlspecialchars($text, ENT_QUOTES, 'UTF-8');
}

// Nome do administrador da sessão
$admin_nome = $_SESSION['admin_nome'] ?? 'Administrador';

// Incluir conexão com o banco de dados
include('../conexao.php');

// Consulta SQL para obter as reservas com nomes dos hóspedes
$sql = "SELECT reservas.*, hospedes.H_nome FROM reservas JOIN hospedes ON reservas.R_id_hospede = hospedes.H_id_hospede";
$resultado = $conexao->query($sql);
?>
<!DOCTYPE html>
<html lang="pt-PT">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="admin.css">
    <title>Quinta Flores - Reservas</title>
</head>
<body>
    <!-- Sidebar -->
    <div id="sidebar" class="sidebar">
        <div class="sidebar-header">
            <img src="../logotipos/logotipo1.png" width="170" height="170" alt="Quinta Flores Logo" class="logo-img">
        </div>
        <nav class="sidebar-nav">
            <ul>
                <li>
                    <a href="admin_index.php">
                        <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <path d="M3 9l9-7 9 7v11a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2z"></path>
                            <polyline points="9 22 9 12 15 12 15 22"></polyline>
                        </svg>
                        <span>Início</span>
                    </a>
                </li>
                <li>
                    <a href="admin_funcionarios.php">
                        <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <path d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2"></path>
                            <circle cx="9" cy="7" r="4"></circle>
                            <path d="M22 21v-2a4 4 0 0 0-3-3.87"></path>
                            <path d="M16 3.13a4 4 0 0 1 0 7.75"></path>
                        </svg>
                        <span>Funcionários</span>
                    </a>
                </li>
                <li>
                    <a href="admin_hospedes.php">
                        <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"></path>
                            <circle cx="12" cy="7" r="4"></circle>
                        </svg>
                        <span>Hóspedes</span>
                    </a>
                </li>
                <li>
                    <a href="admin_reservas.php">
                        <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <rect x="3" y="4" width="18" height="18" rx="2" ry="2"></rect>
                            <line x1="16" y1="2" x2="16" y2="6"></line>
                            <line x1="8" y1="2" x2="8" y2="6"></line>
                            <line x1="3" y1="10" x2="21" y2="10"></line>
                        </svg>
                        <span>Reservas</span>
                    </a>
                </li>
                <li class="logout">
                    <a href="../index.html">
                        <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4"></path>
                            <polyline points="16 17 21 12 16 7"></polyline>
                            <line x1="21" y1="12" x2="9" y2="12"></line>
                        </svg>
                        <span>Sair</span>
                    </a>
                </li>
            </ul>
        </nav>
    </div>

    <!-- Botão de alternar menu -->
    <button class="menu-toggle" aria-label="Alternar menu" aria-expanded="false" aria-controls="sidebar">
        <span></span>
        <span></span>
        <span></span>
    </button>

    <!-- Conteúdo principal -->
    <div id="main-content" class="main">
        <div class="welcome-card">
            <h1>Gerenciamento de <span class="admin-name">Reservas</span></h1>
            <p class="welcome-message">Visualize, edite ou adicione novas reservas no sistema.</p>
        </div>
        
        <div class="action-bar">
            <a href="nova_reserva.php" class="btn-primary">
                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <line x1="12" y1="5" x2="12" y2="19"></line>
                    <line x1="5" y1="12" x2="19" y2="12"></line>
                </svg>
                Nova Reserva
            </a>
        </div>
        
        <div class="table-container">
            <table class="data-table">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Hóspede</th>
                        <th>Casa</th>
                        <th>Check-in</th>
                        <th>Check-out</th>
                        <th>Estado</th>
                        <th>Ações</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if ($resultado && $resultado->num_rows > 0): ?>
                        <?php while($reserva = $resultado->fetch_assoc()): ?>
                            <tr>
                                <td><?php echo e($reserva['R_id_reserva']); ?></td>
                                <td><?php echo e($reserva['H_nome']); ?></td>
                                <td><?php echo e($reserva['R_id_casa']); ?></td>
                                <td><?php echo e($reserva['R_data_checkin']); ?></td>
                                <td><?php echo e($reserva['R_data_checkout']); ?></td>
                                <td>
                                    <span class="status-badge <?php echo strtolower(e($reserva['R_estado'])); ?>">
                                        <?php echo e($reserva['R_estado']); ?>
                                    </span>
                                </td>
                                <td class="actions">
                                    <a href="editar_reserva.php?id=<?php echo e($reserva['R_id_reserva']); ?>" class="btn-edit">
                                        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                            <path d="M20 14.66V20a2 2 0 0 1-2 2H4a2 2 0 0 1-2-2V6a2 2 0 0 1 2-2h5.34"></path>
                                            <polygon points="18 2 22 6 12 16 8 16 8 12 18 2"></polygon>
                                        </svg>
                                    </a>
                                    <a href="excluir_reserva.php?id=<?php echo e($reserva['R_id_reserva']); ?>" class="btn-delete" onclick="return confirm('Tem certeza que deseja excluir esta reserva?')">
                                        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                            <polyline points="3 6 5 6 21 6"></polyline>
                                            <path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"></path>
                                        </svg>
                                    </a>
                                </td>
                            </tr>
                        <?php endwhile; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="7" style="text-align: center;">Nenhuma reserva encontrada.</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>

    <script>
        // Menu toggle functionality
        const menuToggle = document.querySelector('.menu-toggle');
        const sidebar = document.getElementById('sidebar');
        
        menuToggle.addEventListener('click', function() {
            sidebar.classList.toggle('active');
            menuToggle.setAttribute('aria-expanded', 
                menuToggle.getAttribute('aria-expanded') === 'false' ? 'true' : 'false');
        });
        
        // Marcar item ativo no menu
        document.addEventListener('DOMContentLoaded', function() {
            const currentPage = window.location.pathname.split('/').pop();
            document.querySelectorAll('.sidebar-nav a').forEach(link => {
                const linkPage = link.getAttribute('href').split('/').pop();
                if (linkPage === currentPage) {
                    link.classList.add('active');
                }
            });
        });
    </script>
</body>
</html>