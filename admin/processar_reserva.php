<?php
require '../conexao.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    die('Método inválido');
}

// Capturar dados
$data_checkin = $_POST['data_checkin'];
$data_checkout = $_POST['data_checkout'];
$id_casa = (int)$_POST['id_casa'];
$id_hospede = (int)$_POST['id_hospede'];
$num_hospedes = (int)$_POST['num_hospedes'];

// Verificar disponibilidade
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

// Obter preço da casa
$stmt = $conexao->prepare("SELECT C_preco_noite FROM casas WHERE C_id_casa = ?");
$stmt->bind_param("i", $id_casa);
$stmt->execute();
$stmt->bind_result($preco_noite);
$stmt->fetch();
$stmt->close();

// Calcular total
$checkin = new DateTime($data_checkin);
$checkout = new DateTime($data_checkout);
$dias = $checkout->diff($checkin)->days;
$preco_total = $dias * $preco_noite;

// Inserir reserva
$stmt = $conexao->prepare("INSERT INTO reservas 
                          (R_id_hospede, R_id_casa, R_data_checkin, R_data_checkout, 
                          R_num_hospedes, R_preco_total, R_estado) 
                          VALUES (?, ?, ?, ?, ?, ?, 'pendente')");
$stmt->bind_param("iissid", $id_hospede, $id_casa, $data_checkin, $data_checkout, $num_hospedes, $preco_total);
$stmt->execute();
$reserva_id = $stmt->insert_id;
$stmt->close();

header("Location: reserva_sucesso.php?id=$reserva_id");
exit;