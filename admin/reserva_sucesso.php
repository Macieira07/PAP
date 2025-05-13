<?php
require '../conexao.php';

if (!isset($_GET['id'])) {
    die('Reserva não especificada.');
}
$reserva_id = (int)$_GET['id'];

$query = "SELECT r.*, h.H_nome, h.H_email, h.H_telefone, c.C_nome
          FROM reservas r
          JOIN hospedes h ON r.R_id_hospede = h.H_id_hospede
          JOIN casas c ON r.R_id_casa = c.C_id_casa
          WHERE r.R_id_reserva = ?";
$stmt = $conexao->prepare($query);
$stmt->bind_param("i", $reserva_id);
$stmt->execute();
$res = $stmt->get_result();
if ($res->num_rows === 0) {
    die('Reserva não encontrada.');
}
$r = $res->fetch_assoc();
$stmt->close();

$noites = (new DateTime($r['R_data_checkout']))->diff(new DateTime($r['R_data_checkin']))->days;
?>
<!DOCTYPE html>
<html lang="pt">
<head>
    <meta charset="UTF-8">
    <title>Reserva Confirmada</title>
    <style>
        body { font-family: Arial, sans-serif; max-width: 800px; margin: 0 auto; padding: 20px; }
        .success-box { background-color: #e8f5e9; padding: 20px; border-radius: 5px; margin-bottom: 20px; }
        .button { background-color: #4CAF50; color: white; padding: 10px 15px; text-decoration: none; border-radius: 4px; }
        .info-box { border: 1px solid #ddd; padding: 15px; margin-bottom: 15px; border-radius: 5px; }
    </style>
</head>
<body>
    <div class="success-box">
        <h1>Reserva #<?= $r['R_id_reserva'] ?> Confirmada!</h1>
        <p>Obrigado por escolher nosso alojamento. Detalhes da reserva abaixo:</p>
    </div>

    <div class="info-box">
        <h2>Informações do Hóspede</h2>
        <p><strong>Nome:</strong> <?= htmlspecialchars($r['H_nome']) ?></p>
        <p><strong>Telefone:</strong> <?= htmlspecialchars($r['H_telefone']) ?></p>
        <p><strong>Email:</strong> <?= htmlspecialchars($r['H_email']) ?></p>
    </div>

    <div class="info-box">
        <h2>Detalhes da Reserva</h2>
        <p><strong>Casa:</strong> <?= htmlspecialchars($r['C_nome']) ?></p>
        <p><strong>Check-in:</strong> <?= $r['R_data_checkin'] ?></p>
        <p><strong>Check-out:</strong> <?= $r['R_data_checkout'] ?></p>
        <p><strong>Noites:</strong> <?= $noites ?></p>
        <p><strong>Hóspedes:</strong> <?= $r['R_num_hospedes'] ?></p>
        <p><strong>Preço Total:</strong> <?= number_format($r['R_preco_total'], 2) ?>€</p>
    </div>

    <a href="gerar_pdf_reserva.php?id=<?= $r['R_id_reserva'] ?>" class="button">Baixar Recibo PDF</a>
    <a href="reservas.php" style="margin-left: 10px;">Voltar às Reservas</a>
</body>
</html>