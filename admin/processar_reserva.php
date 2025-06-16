<?php
session_start();
require '../conexao.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    die('Método inválido');
}

// Evitar duplicação do processamento da mesma reserva
if (isset($_SESSION['reserva_processada']) && $_SESSION['reserva_processada'] === true) {
    header('Location: reservas.php');
    exit();
}

// Capturar dados do formulário
$data_checkin = $_POST['data_checkin'];
$data_checkout = $_POST['data_checkout'];
$id_casa = (int)$_POST['id_casa'];
$id_hospede = (int)$_POST['id_hospede'];
$num_hospedes = (int)$_POST['num_hospedes'];

// Validar datas
$checkin = DateTime::createFromFormat('Y-m-d', $data_checkin);
$checkout = DateTime::createFromFormat('Y-m-d', $data_checkout);

if (!$checkin || !$checkout || $checkin >= $checkout) {
    die('Datas inválidas.');
}

// Validar número de hóspedes
if ($num_hospedes < 1) {
    die('Número de hóspedes inválido.');
}

// Verificar disponibilidade da casa para o intervalo de datas
$stmt = $conexao->prepare("SELECT COUNT(*) FROM reservas 
                          WHERE R_id_casa = ? 
                          AND R_estado != 'cancelada'
                          AND ((R_data_checkin <= ? AND R_data_checkout >= ?) 
                          OR (R_data_checkin <= ? AND R_data_checkout >= ?))");
$stmt->bind_param("issss", $id_casa, $data_checkout, $data_checkin, $data_checkin, $data_checkout);
$stmt->execute();
$stmt->bind_result($count);
$stmt->fetch();
$stmt->close();

if ($count > 0) {
    die('A casa já está reservada para essas datas.');
}

// Obter preço por noite da casa
$stmt = $conexao->prepare("SELECT C_preco_noite FROM casas WHERE C_id_casa = ?");
$stmt->bind_param("i", $id_casa);
$stmt->execute();
$stmt->bind_result($preco_noite);
$stmt->fetch();
$stmt->close();

$dias = $checkout->diff($checkin)->days;
$preco_total = $dias * $preco_noite;

// Calcular serviços adicionais
$decoracao = (isset($_POST['decoracao_tematica']) && $_POST['decoracao_tematica'] !== '') ? 130 : 0;
$limpeza = (isset($_POST['limpeza_diaria'])) ? 15 * $dias : 0;
$cesto = (isset($_POST['cesto_boas_vindas'])) ? 10 : 0;

$preco_total += $decoracao + $limpeza + $cesto;

// Inserir reserva na base de dados
$origem = $_POST['origem'] ?? 'presencial'; // Default para presencial se não enviado

$stmt = $conexao->prepare("INSERT INTO reservas 
                          (R_id_hospede, R_id_casa, R_data_checkin, R_data_checkout, 
                          R_num_hospedes, R_preco_total, R_estado, R_origem) 
                          VALUES (?, ?, ?, ?, ?, ?, 'pendente', ?)");
$stmt->bind_param("iissids", $id_hospede, $id_casa, $data_checkin, $data_checkout, 
                 $num_hospedes, $preco_total, $origem);
$stmt->execute();
$reserva_id = $stmt->insert_id;
$stmt->close();

// Atualizar saldo na conta_virtual (assumindo id = 1)
$stmt = $conexao->prepare("UPDATE conta_virtual SET saldo = saldo + ? WHERE id = 1");
$stmt->bind_param("d", $preco_total);
$stmt->execute();
$stmt->close();

// Marcar sessão como processada para evitar duplicação
$_SESSION['reserva_processada'] = true;

// Redirecionar para página de sucesso
$_SESSION['flash'] = [
    'type' => 'success',
    'msg' => "Reserva #$reserva_id criada com sucesso!"
];
header("Location: reservas.php");
exit;
