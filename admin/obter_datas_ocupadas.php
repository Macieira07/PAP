<?php
require __DIR__ . '/../conexao.php';

$ocupadas = [];
$stmt = $conexao->prepare("SELECT R_data_checkin, R_data_checkout FROM reservas WHERE R_estado != 'cancelada'");
$stmt->execute();
$stmt->bind_result($data_checkin, $data_checkout);
while ($stmt->fetch()) {
    $checkin = new DateTime($data_checkin);
    $checkout = new DateTime($data_checkout);
    while ($checkin <= $checkout) {
        $ocupadas[] = $checkin->format('Y-m-d');
        $checkin->modify('+1 day');
    }
}
$stmt->close();

echo json_encode($ocupadas);
?>
