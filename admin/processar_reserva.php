<?php
// Ajuste do caminho para conexao.php (ficheiro está em ../conexao.php)
require __DIR__ . '/../conexao.php';

// Incluir a biblioteca FPDF (assumindo que está em admin/lib/fpdf186/fpdf.php)
require __DIR__ . '/lib/fpdf186/fpdf.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    die('Envio inválido.');
}

// Capturar dados
$data_checkin     = $_POST['data_checkin'];
$data_checkout    = $_POST['data_checkout'];
$id_casa          = (int)$_POST['id_casa'];
$id_hospede       = (int)$_POST['id_hospede'];
$num_hospedes     = max(1, min(10, (int)$_POST['num_hospedes']));
$metodo_pagamento = $_POST['metodo_pagamento'];

// Validar datas
$ci = new DateTime($data_checkin);
$co = new DateTime($data_checkout);
if ($co <= $ci) {
    die('Check-out deve ser posterior ao check-in.');
}

// Buscar preço da casa
$stmt = $conexao->prepare("SELECT C_preco_noite FROM casas WHERE C_id_casa = ?");
$stmt->bind_param("i", $id_casa);
$stmt->execute();
$stmt->bind_result($preco_noite);
if (!$stmt->fetch()) {
    die('Casa inválida.');
}
$stmt->close();

// Calcular total
$dias        = $co->diff($ci)->days;
$preco_total = $dias * $preco_noite;

// Verificar disponibilidade
$stmt = $conexao->prepare("
    SELECT COUNT(*) FROM reservas
    WHERE R_id_casa = ?
      AND (
        (R_data_checkin < ? AND R_data_checkout > ?)
        OR (R_data_checkin < ? AND R_data_checkout > ?)
        OR (R_data_checkin >= ? AND R_data_checkout <= ?)
      )
");
$stmt->bind_param(
    "issssss",
    $id_casa,
    $data_checkout, $data_checkout,
    $data_checkin,  $data_checkin,
    $data_checkin,  $data_checkout
);
$stmt->execute();
$stmt->bind_result($ocupadas);
$stmt->fetch();
$stmt->close();

if ($ocupadas > 0) {
    die('Já existe reserva para esse período.');
}

// Inserir reserva
$stmt = $conexao->prepare("
    INSERT INTO reservas
      (R_id_hospede, R_id_casa, R_data_checkin, R_data_checkout,
       R_num_hospedes, R_preco_total, R_metodo_pagamento, R_estado)
    VALUES (?, ?, ?, ?, ?, ?, ?, 'pendente')
");
$stmt->bind_param(
    "iissids",
    $id_hospede,
    $id_casa,
    $data_checkin,
    $data_checkout,
    $num_hospedes,
    $preco_total,
    $metodo_pagamento
);

if (!$stmt->execute()) {
    die("Erro ao criar reserva: " . $stmt->error);
}
$reserva_id = $stmt->insert_id;
$stmt->close();
$conexao->close();

// Redirecionar para página de sucesso
header("Location: reserva_sucesso.php?id={$reserva_id}");
exit;
