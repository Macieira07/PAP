<?php
require '../conexao.php';

$id = $_GET['id'];

$stmt = $conexao->prepare("DELETE FROM reservas WHERE R_id_reserva = ?");
$stmt->bind_param("i", $id);
$stmt->execute();

header("Location: reservas.php");
exit;
