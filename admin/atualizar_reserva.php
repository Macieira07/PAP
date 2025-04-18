<?php
include('../conexao.php');
$id = intval($_POST['id']);
$hospede = $_POST['id_hospede'];
$casa = $_POST['id_casa'];
$checkin = $_POST['checkin'];
$checkout = $_POST['checkout'];
$estado = $_POST['estado'];

if ($id > 0) {
    $sql = "UPDATE reservas SET R_id_hospede=$hospede, R_id_casa=$casa, R_data_checkin='$checkin', R_data_checkout='$checkout', R_estado='$estado' WHERE R_id_reserva=$id";
} else {
    $sql = "INSERT INTO reservas (R_id_hospede, R_id_casa, R_data_checkin, R_data_checkout, R_estado) VALUES ($hospede, $casa, '$checkin', '$checkout', '$estado')";
}
$conexao->query($sql);
header("Location: admin_reservas.php");
?>
