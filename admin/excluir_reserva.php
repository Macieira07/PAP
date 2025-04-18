<?php
include('../conexao.php');
$id = intval($_GET['id']);
$conexao->query("DELETE FROM reservas WHERE R_id_reserva = $id");
header("Location: admin_reservas.php");
?>
