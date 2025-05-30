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
$query = "SELECT * FROM reservas WHERE R_id_reserva = ? AND R_id_hospede = ?";
$stmt = $conexao->prepare($query);
$stmt->bind_param("ii", $id_reserva, $id_hospede);
$stmt->execute();
$resultado = $stmt->get_result();

if ($resultado->num_rows === 0) {
    // Reserva não pertence ao hóspede ou não existe
    header('Location: minhas_reservas.php');
    exit();
}

// Elimina a reserva
$query_delete = "DELETE FROM reservas WHERE R_id_reserva = ?";
$stmt_delete = $conexao->prepare($query_delete);
$stmt_delete->bind_param("i", $id_reserva);
$stmt_delete->execute();

header('Location: minhas_reservas.php');
exit();
?>
