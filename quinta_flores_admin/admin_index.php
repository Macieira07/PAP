<?php
require_once 'includes/auth.php';
require_once 'includes/conexao.php';
$pdo = conexao();

try {
    // Estatísticas
    $reservasAtivas = $pdo->query("SELECT COUNT(*) FROM reservas WHERE R_estado = 'confirmada'")->fetchColumn();
    $hospedesAtivos = $pdo->query("SELECT COUNT(*) FROM hospedes")->fetchColumn();
    $funcionariosAtivos = $pdo->query("SELECT COUNT(*) FROM funcionarios")->fetchColumn();
    
    // Taxa de ocupação
    $diasOcupados = $pdo->query("SELECT SUM(DATEDIFF(R_data_checkout, R_data_checkin)) FROM reservas WHERE R_estado = 'confirmada'")->fetchColumn();
    $taxaOcupacao = $diasOcupados > 0 ? min(100, round(($diasOcupados / 30) * 100, 0)) : 0;
    
    // Atividades recentes
    $atividades = $pdo->query("SELECT * FROM logs_acesso ORDER BY data DESC LIMIT 5")->fetchAll(PDO::FETCH_ASSOC);
    
    // Funcionários recentes
    $funcionarios = $pdo->query("SELECT * FROM funcionarios ORDER BY F_data_contratacao DESC LIMIT 5")->fetchAll(PDO::FETCH_ASSOC);
    
    // Reservas recentes
    $reservas = $pdo->query("SELECT r.*, h.H_nome FROM reservas r JOIN hospedes h ON r.R_id_hospede = h.H_id_hospede ORDER BY R_data_reserva DESC LIMIT 5")->fetchAll(PDO::FETCH_ASSOC);
    
    // Hóspedes recentes
    $hospedes = $pdo->query("SELECT * FROM hospedes ORDER BY H_data_criacao DESC LIMIT 5")->fetchAll(PDO::FETCH_ASSOC);

} catch (PDOException $e) {
    $erro = "Erro ao carregar dados: " . $e->getMessage();
}

$titulo = "Painel Administrativo - Quinta Flores";
?>

<?php include 'includes/header.php'; ?>
<!-- Coloque o link do CSS aqui, dentro da tag <head> -->
<link rel="stylesheet" href="global.css">

<div class="welcome-card">
    <h1>Bem-vindo, <span class="admin-name"><?= htmlspecialchars($_SESSION['admin_nome'] ?? 'Visitante') ?></span></h1>
    <p class="welcome-message">Gerencie o sistema da Quinta Flores de forma eficiente e intuitiva.</p>
</div>

<!-- Stats Grid -->
<div class="stats-grid">
    <div class="stat-card">
        <h4>Reservas Ativas</h4>
        <div class="stat-value"><?= $reservasAtivas ?></div>
        <div class="stat-trend up">
            <i class="fas fa-arrow-up"></i>
            12% desde ontem
        </div>
    </div>
    
    <div class="stat-card">
        <h4>Hóspedes Ativos</h4>
        <div class="stat-value"><?= $hospedesAtivos ?></div>
        <div class="stat-trend up">
            <i class="fas fa-arrow-up"></i>
            8% desde semana passada
        </div>
    </div>
</div>

<!-- Funcionários -->
<div class="dashboard-card">
    <i class="fas fa-user-tie fa-2x"></i>
    <h3>Funcionários Recentes</h3>
    <div class="table-container">
        <table>
            <thead>
                <tr>
                    <th>Nome</th>
                    <th>Cargo</th>
                    <th>Ações</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($funcionarios as $func): ?>
                <tr>
                    <td><?= htmlspecialchars($func['F_nome']) ?></td>
                    <td><?= htmlspecialchars($func['F_cargo']) ?></td>
                    <td>
                        <a href="funcionarios/editar.php?id=<?= $func['F_id_funcionario'] ?>" class="action-btn edit-btn">
                            <i class="fas fa-edit"></i>
                        </a>
                        <?php if ($_SESSION['admin_cargo'] ?? '' == 'administrador' && $func['F_id_funcionario'] != $_SESSION['admin_id']): ?>
                        <a href="funcionarios/excluir.php?id=<?= $func['F_id_funcionario'] ?>" class="action-btn delete-btn" onclick="return confirm('Tem certeza?')">
                            <i class="fas fa-trash"></i>
                        </a>
                        <?php endif; ?>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>

<!-- Reservas -->
<div class="dashboard-card">
    <i class="fas fa-calendar-check fa-2x"></i>
    <h3>Reservas Recentes</h3>
    <div class="table-container">
        <table>
            <thead>
                <tr>
                    <th>Hóspede</th>
                    <th>Data</th>
                    <th>Ações</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($reservas as $reserva): ?>
                <tr>
                    <td><?= htmlspecialchars($reserva['H_nome']) ?></td>
                    <td><?= date('d/m/Y', strtotime($reserva['R_data_reserva'])) ?></td>
                    <td>
                        <a href="reservas/detalhes.php?id=<?= $reserva['R_id_reserva'] ?>" class="action-btn view-btn">
                            <i class="fas fa-eye"></i>
                        </a>
                        <a href="reservas/editar.php?id=<?= $reserva['R_id_reserva'] ?>" class="action-btn edit-btn">
                            <i class="fas fa-edit"></i>
                        </a>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
        <a href="reservas/listar.php" class="see-all">Ver todas as reservas <i class="fas fa-arrow-right"></i></a>
    </div>
</div>

<!-- Hóspedes -->
<div class="dashboard-card">
    <i class="fas fa-user-friends fa-2x"></i>
    <h3>Hóspedes Recentes</h3>
    <div class="table-container">
        <table>
            <thead>
                <tr>
                    <th>Nome</th>
                    <th>E-mail</th>
                    <th>Ações</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($hospedes as $hospede): ?>
                <tr>
                    <td><?= htmlspecialchars($hospede['H_nome']) ?></td>
                    <td><?= htmlspecialchars($hospede['H_email']) ?></td>
                    <td>
                        <a href="hospedes/detalhes.php?id=<?= $hospede['H_id_hospede'] ?>" class="action-btn view-btn">
                            <i class="fas fa-eye"></i>
                        </a>
                        <a href="hospedes/editar.php?id=<?= $hospede['H_id_hospede'] ?>" class="action-btn edit-btn">
                            <i class="fas fa-edit"></i>
                        </a>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
        <a href="hospedes/listar.php" class="see-all">Ver todos os hóspedes <i class="fas fa-arrow-right"></i></a>
    </div>
</div>

<!-- Atividades Recentes -->
<div class="recent-activity">
    <h3>Atividades Recentes</h3>
    <ul class="activity-list">
        <?php foreach ($atividades as $atividade): ?>
        <li class="activity-item">
            <div class="activity-icon">
                <?php if ($atividade['tipo_usuario'] == 'hospede'): ?>
                    <i class="fas fa-user"></i>
                <?php else: ?>
                    <i class="fas fa-user-tie"></i>
                <?php endif; ?>
            </div>
            <div class="activity-content">
                <div class="activity-title">
                    <?php 
                    $tipo = $atividade['tipo_usuario'] == 'hospede' ? 'Hóspede' : 'Funcionário';
                    echo "$tipo #{$atividade['usuario_id']} - {$atividade['acao']}";
                    ?>
                </div>
                <div class="activity-time"><?= date('d/m/Y H:i', strtotime($atividade['data'])) ?></div>
            </div>
        </li>
        <?php endforeach; ?>
    </ul>
</div>
