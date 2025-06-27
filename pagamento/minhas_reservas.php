<?php
session_start();
require_once '../conexao.php';
require_once 'header.php';
$page_title = I18n::get('my_reservations');

$id_hospede = $_SESSION['id'];

// Consulta as reservas do hóspede
$query = "SELECT r.*, c.C_nome as casa_nome 
          FROM reservas r
          JOIN casas c ON r.R_id_casa = c.C_id_casa
          WHERE r.R_id_hospede = ?
          ORDER BY r.R_data_checkin DESC";
$stmt = $conexao->prepare($query);
$stmt->bind_param("i", $id_hospede);
$stmt->execute();
$resultado = $stmt->get_result();
$reservas = $resultado->fetch_all(MYSQLI_ASSOC);
?>
<link rel="stylesheet" href="global.css">

<div class="page-title">
    <h1><i class="fas fa-calendar-alt"></i> <?= I18n::get('my_reservations') ?></h1>
</div>

<?php if (empty($reservas)): ?>
    <div class="no-results">
        <i class="fas fa-calendar-times"></i>
        <p><?= I18n::get('no_records') ?></p>
        <a href="pagina1.php" class="btn btn-primary"><?= I18n::get('make_reservation') ?></a>
    </div>
<?php else: ?>
    <div class="reservas-list">
        <?php foreach ($reservas as $reserva): ?>
            <div class="reserva-card">
                <div class="reserva-header">
                    <h3><?= htmlspecialchars($reserva['casa_nome']) ?></h3>
                    <span class="status-badge <?= $reserva['R_estado'] ?>">
                        <?= I18n::get($reserva['R_estado']) ?>
                    </span>
                </div>
                
                <div class="reserva-details">
                    <div class="detail">
                        <i class="fas fa-calendar-day"></i>
                        <span><?= I18n::get('check_in') ?>:</span>
                        <strong><?= date(I18n::get('date_format'), strtotime($reserva['R_data_checkin'])) ?></strong>
                    </div>
                    
                    <div class="detail">
                        <i class="fas fa-calendar-day"></i>
                        <span><?= I18n::get('check_out') ?>:</span>
                        <strong><?= date(I18n::get('date_format'), strtotime($reserva['R_data_checkout'])) ?></strong>
                    </div>
                    
                    <div class="detail">
                        <i class="fas fa-users"></i>
                        <span><?= I18n::get('guests') ?>:</span>
                        <strong><?= $reserva['R_num_hospedes'] ?></strong>
                    </div>
                    
                    <div class="detail">
                        <i class="fas fa-euro-sign"></i>
                        <span><?= I18n::get('total_price') ?>:</span>
                        <strong><?= I18n::get('currency') ?><?= number_format($reserva['R_preco_total'], 2, ',', '.') ?></strong>
                    </div>
                </div>
                
                <?php if (!empty($reserva['R_servicos'])): ?>
                <div class="reserva-servicos">
                    <h4><i class="fas fa-concierge-bell"></i> <?= I18n::get('additional_services') ?></h4>
                    <p><?= htmlspecialchars($reserva['R_servicos']) ?></p>
                </div>
                <?php endif; ?>
                
                <div class="reserva-actions">
                    <?php if ($reserva['R_estado'] == 'pendente'): ?>
                        <a href="pagina3.php?reserva=<?= $reserva['R_id_reserva'] ?>" class="btn btn-primary">
                            <i class="fas fa-credit-card"></i> <?= I18n::get('payment') ?>
                        </a>
                    <?php endif; ?>
                    
                    <?php 
                    $hoje = new DateTime();
                    $checkin = new DateTime($reserva['R_data_checkin']);
                    $diferenca = $hoje->diff($checkin)->days;
                    
                    if ($diferenca > 10 && $reserva['R_estado'] != 'cancelada'): ?>
                        <a href="cancelar_reserva.php?id=<?= $reserva['R_id_reserva'] ?>" class="btn btn-secondary">
                            <i class="fas fa-times"></i> <?= I18n::get('cancel') ?>
                        </a>
                    <?php endif; ?>
                </div>
            </div>
        <?php endforeach; ?>
    </div>
<?php endif; ?>

<?php require_once 'footer.php'; ?>