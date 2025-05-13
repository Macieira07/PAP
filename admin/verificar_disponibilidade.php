<?php
include('../conexao.php');

$data_checkin = $_POST['data_checkin'];
$data_checkout = $_POST['data_checkout'];
$casa_id = $_POST['casa_id'];

// Verificar disponibilidade da casa
$query = "SELECT COUNT(*) AS reservas
          FROM reservas
          WHERE R_id_casa = ? 
          AND ((R_data_checkin <= ? AND R_data_checkout >= ?) 
          OR (R_data_checkin >= ? AND R_data_checkout <= ?))";

$stmt = $conexao->prepare($query);
$stmt->bind_param("issss", $casa_id, $data_checkin, $data_checkin, $data_checkout, $data_checkout);
$stmt->execute();
$result = $stmt->get_result();
$row = $result->fetch_assoc();

if ($row['reservas'] > 0) {
  echo json_encode(["disponibilidade" => false]);
} else {
  echo json_encode(["disponibilidade" => true]);
}
?>
