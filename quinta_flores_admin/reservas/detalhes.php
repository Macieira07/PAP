<?php
require_once '../includes/auth.php';
require_once '../includes/conexao.php';
verificarLogin();

if (!isset($_GET['id'])) {
    header('Location: listar.php');
    exit();
}

$id = $_GET['id'];

try {
    $pdo = conexao();
    
    // Carregar dados da reserva
    $stmt = $pdo->prepare("SELECT r.*, h.H_nome, h.H_email, h.H_telefone, c.C_nome, c.C_preco_noite 
                          FROM reservas r
                          JOIN hospedes h ON r.R_id_hospede = h.H_id_hospede
                          JOIN casas c ON r.R_id_casa = c.C_id_casa
                          WHERE r.R_id_reserva = ?");
    $stmt->execute([$id]);
    $reserva = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$reserva) {
        header('Location: listar.php');
        exit();
    }
    
    // Carregar serviços da reserva
    $servicos = $pdo->prepare("SELECT s.S_nome_servico, rs.RS_preco_unitario 
                              FROM reservas_servicos rs
                              JOIN servicos s ON rs.RS_id_servico = s.S_id_servico
                              WHERE rs.RS_id_reserva = ?");
    $servicos->execute([$id]);
    $servicos = $servicos->fetchAll(PDO::FETCH_ASSOC);
    
    // Carregar pagamentos
    $pagamentos = $pdo->prepare("SELECT * FROM pagamentos WHERE P_id_reserva = ? ORDER BY P_data_pagamento DESC");
    $pagamentos->execute([$id]);
    $pagamentos = $pagamentos->fetchAll(PDO::FETCH_ASSOC);
    
} catch (PDOException $e) {
    die("Erro ao carregar reserva: " . $e->getMessage());
}

$titulo = "Detalhes da Reserva - Quinta Flores";
?>
<?php include '../includes/header.php'; ?>

<div class="welcome-card">
    <h1>Detalhes da Reserva #<?= $reserva['R_id_reserva'] ?></h1>
</div>

<div class="details-container">
    <div class="details-card">
        <h3>Informações da Reserva</h3>
        <div class="detail-row">
            <span class="detail-label">Estado:</span>
            <span class="detail-value status-badge <?= $reserva['R_estado'] ?>"><?= ucfirst($reserva['R_estado']) ?></span>
        </div>
        <div class="detail-row">
            <span class="detail-label">Casa:</span>
            <span class="detail-value"><?= htmlspecialchars($reserva['C_nome']) ?></span>
        </div>
        <div class="detail-row">
            <span class="detail-label">Check-in:</span>
            <span class="detail-value"><?= date('d/m/Y', strtotime($reserva['R_data_checkin'])) ?></span>
        </div>
        <div class="detail-row">
            <span class="detail-label">Check-out:</span>
            <span class="detail-value"><?= date('d/m/Y', strtotime($reserva['R_data_checkout'])) ?></span>
        </div>
        <div class="detail-row">
            <span class="detail-label">Noites:</span>
            <span class="detail-value"><?= date_diff(
                new DateTime($reserva['R_data_checkin']), 
                new DateTime($reserva['R_data_checkout'])
            )->format('%a') ?></span>
        </div>
        <div class="detail-row">
            <span class="detail-label">Hóspedes:</span>
            <span class="detail-value"><?= $reserva['R_num_hospedes'] ?></span>
        </div>
        <div class="detail-row">
            <span class="detail-label">Preço/noite:</span>
            <span class="detail-value"><?= number_format($reserva['C_preco_noite'], 2, ',', '.') ?> €</span>
        </div>
        <div class="detail-row">
            <span class="detail-label">Total:</span>
            <span class="detail-value"><?= number_format($reserva['R_preco_total'], 2, ',', '.') ?> €</span>
        </div>
        <div class="detail-row">
            <span class="detail-label">Método de Pagamento:</span>
            <span class="detail-value"><?= $reserva['R_metodo_pagamento'] ? htmlspecialchars($reserva['R_metodo_pagamento']) : 'Não especificado' ?></span>
        </div>
        <div class="detail-row">
            <span class="detail-label">Observações:</span>
            <span class="detail-value"><?= $reserva['R_observacoes'] ? htmlspecialchars($reserva['R_observacoes']) : 'Nenhuma' ?></span>
        </div>
    </div>
    
    <div class="details-card">
        <h3>Informações do Hóspede</h3>
        <div class="detail-row">
            <span class="detail-label">Nome:</span>
            <span class="detail-value"><?= htmlspecialchars($reserva['H_nome']) ?></span>
        </div>
        <div class="detail-row">
            <span class="detail-label">E-mail:</span>
            <span class="detail-value"><?= htmlspecialchars($reserva['H_email']) ?></span>
        </div>
        <div class="detail-row">
            <span class="detail-label">Telefone:</span>
            <span class="detail-value"><?= htmlspecialchars($reserva['H_telefone']) ?></span>
        </div>
        <div class="detail-row">
            <span class="detail-label">Reserva criada em:</span>
            <span class="detail-value"><?= date('d/m/Y H:i', strtotime($reserva['R_data_reserva'])) ?></span>
        </div>
    </div>
    
    <?php if (!empty($servicos)): ?>
    <div class="details-card">
        <h3>Serviços Adicionais</h3>
        <table class="servicos-table">
            <thead>
                <tr>
                    <th>Serviço</th>
                    <th>Preço</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($servicos as $servico): ?>
                <tr>
                    <td><?= htmlspecialchars($servico['S_nome_servico']) ?></td>
                    <td><?= number_format($servico['RS_preco_unitario'], 2, ',', '.') ?> €</td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
    <?php endif; ?>
    
    <?php if (!empty($pagamentos)): ?>
    <div class="details-card">
        <h3>Histórico de Pagamentos</h3>
        <table class="pagamentos-table">
            <thead>
                <tr>
                    <th>Data</th>
                    <th>Valor</th>
                    <th>Método</th>
                    <th>Estado</th>
                    <th>Referência</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($pagamentos as $pagamento): ?>
                <tr>
                    <td><?= date('d/m/Y H:i', strtotime($pagamento['P_data_pagamento'])) ?></td>
                    <td><?= number_format($pagamento['P_valor'], 2, ',', '.') ?> €</td>
                    <td><?= htmlspecialchars($pagamento['P_metodo']) ?></td>
                    <td><span class="status-badge <?= $pagamento['P_estado'] ?>"><?= ucfirst($pagamento['P_estado']) ?></span></td>
                    <td><?= $pagamento['P_referencia'] ? htmlspecialchars($pagamento['P_referencia']) : '-' ?></td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
    <?php endif; ?>
</div>

<div class="form-actions">
    <?php if ($reserva['R_estado'] == 'pendente'): ?>
        <a href="confirmar.php?id=<?= $reserva['R_id_reserva'] ?>" class="btn-confirmar">
            <i class="fas fa-check"></i> Confirmar Reserva
        </a>
    <?php endif; ?>
    
    <a href="editar.php?id=<?= $reserva['R_id_reserva'] ?>" class="btn-editar">
        <i class="fas fa-edit"></i> Editar Reserva
    </a>
    
    <?php if ($reserva['R_estado'] != 'cancelada'): ?>
        <a href="cancelar.php?id=<?= $reserva['R_id_reserva'] ?>" class="btn-cancelar" onclick="return confirm('Tem certeza que deseja cancelar esta reserva?')">
            <i class="fas fa-times"></i> Cancelar Reserva
        </a>
    <?php endif; ?>
    
    <a href="listar.php" class="btn-voltar">
        <i class="fas fa-arrow-left"></i> Voltar
    </a>
</div>

<?php include '../includes/footer.php'; ?>