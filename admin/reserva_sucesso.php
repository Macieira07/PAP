<?php
// Ajuste do caminho para conexao.php
require __DIR__ . '/../conexao.php';

if (!isset($_GET['id'])) {
    die('Reserva não especificada.');
}
$reserva_id = (int)$_GET['id'];

// Buscar dados da reserva + hóspede + casa
$query = "
  SELECT r.R_id_reserva, r.R_data_checkin, r.R_data_checkout, r.R_num_hospedes,
         r.R_preco_total, r.R_metodo_pagamento,
         h.H_nome, h.H_apelido, h.H_email, h.H_telefone,
         c.C_nome
  FROM reservas r
  JOIN hospedes h ON r.R_id_hospede = h.H_id_hospede
  JOIN casas c    ON r.R_id_casa = c.C_id_casa
  WHERE r.R_id_reserva = ?
";
$stmt = $conexao->prepare($query);
$stmt->bind_param("i", $reserva_id);
$stmt->execute();
$res = $stmt->get_result();
if ($res->num_rows === 0) {
    die('Reserva não encontrada.');
}
$r = $res->fetch_assoc();
$stmt->close();
?>

<!DOCTYPE html>
<html lang="pt">
<head>
  <meta charset="UTF-8">
  <title>Reserva Confirmada</title>
</head>
<body>
  <h1>Reserva #<?= $r['R_id_reserva'] ?> Confirmada</h1>
  <p><strong>Hóspede:</strong> <?= htmlspecialchars($r['H_nome'] . ' ' . $r['H_apelido']) ?></p>
  <p><strong>Email:</strong> <?= htmlspecialchars($r['H_email']) ?></p>
  <p><strong>Telefone:</strong> <?= htmlspecialchars($r['H_telefone']) ?></p>
  <p><strong>Casa:</strong> <?= htmlspecialchars($r['C_nome']) ?></p>
  <p><strong>Check-in:</strong> <?= $r['R_data_checkin'] ?></p>
  <p><strong>Check-out:</strong> <?= $r['R_data_checkout'] ?></p>
  <p><strong>Noites:</strong> <?= (new DateTime($r['R_data_checkout']))->diff(new DateTime($r['R_data_checkin']))->days ?></p>
  <p><strong>Nº Hóspedes:</strong> <?= $r['R_num_hospedes'] ?></p>
  <p><strong>Método de Pagamento:</strong> <?= htmlspecialchars(ucfirst($r['R_metodo_pagamento'])) ?></p>
  <p><strong>Preço Total:</strong> <?= number_format($r['R_preco_total'],2) ?>€</p>

  <a href="gerar_pdf_reserva.php?id=<?= $r['R_id_reserva'] ?>" target="_blank">
    <button>Imprimir / Download PDF</button>
  </a>
  <p><a href="reservas.php">← Voltar à lista de reservas</a></p>
</body>
</html>
