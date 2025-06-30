<?php
session_start();
require_once '../conexao.php';

if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    header('Location: pagina1.php');
    exit();
}

$id_reserva = (int)$_GET['id'];
$id_hospede = $_SESSION['id'];

// Verifica se a reserva pertence ao hóspede logado
$query = "SELECT R_data_checkin, R_preco_total FROM reservas WHERE R_id_reserva = ? AND R_id_hospede = ?";
$stmt = $conexao->prepare($query);
$stmt->bind_param("ii", $id_reserva, $id_hospede);
$stmt->execute();
$resultado = $stmt->get_result();

if ($resultado->num_rows === 0) {
    // Reserva não pertence ao hóspede ou não existe
    header('Location: minhas_reservas.php');
    exit();
}

$reserva = $resultado->fetch_assoc();

$hoje = new DateTime();
$checkin = new DateTime($reserva['R_data_checkin']);
$dias_para_checkin = (int)$hoje->diff($checkin)->format("%r%a"); // diferença em dias, pode ser negativa

// Verifica penalidade
$penalidade = 0;
if ($dias_para_checkin <= 10 && $dias_para_checkin >= 0) {
    $penalidade = $reserva['R_preco_total'] / 2;
}

// Elimina a reserva
$query_delete = "DELETE FROM reservas WHERE R_id_reserva = ?";
$stmt_delete = $conexao->prepare($query_delete);
$stmt_delete->bind_param("i", $id_reserva);
$stmt_delete->execute();

// Mostra mensagem de penalidade se houver, depois redireciona
if ($penalidade > 0) {
    echo "<script>alert('Cancelamento feito a menos de 10 dias do check-in. Será cobrada uma penalidade de 50% do valor total: " . number_format($penalidade, 2, ',', '.') . " €');</script>";
}

header('Location: minhas_reservas.php');
exit();
?>
