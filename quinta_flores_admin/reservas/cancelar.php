<?php
require_once '../includes/auth.php';
require_once '../includes/conexao.php';
verificarLogin();

if (!isset($_GET['id'])) {
    header('Location: listar.php');
    exit();
}

$id = $_GET['id'];

try {
    $pdo = conexao();
    
    // Verificar se a reserva existe e não está cancelada
    $stmt = $pdo->prepare("SELECT R_estado FROM reservas WHERE R_id_reserva = ?");
    $stmt->execute([$id]);
    $estado = $stmt->fetchColumn();
    
    if ($estado == 'cancelada') {
        $_SESSION['erro'] = "Esta reserva já está cancelada!";
        header('Location: listar.php');
        exit();
    }
    
    // Cancelar reserva
    $stmt = $pdo->prepare("UPDATE reservas SET R_estado = 'cancelada' WHERE R_id_reserva = ?");
    $stmt->execute([$id]);
    
    $_SESSION['mensagem'] = "Reserva cancelada com sucesso!";
    
} catch (PDOException $e) {
    $_SESSION['erro'] = "Erro ao cancelar reserva: " . $e->getMessage();
}

header('Location: detalhes.php?id=' . $id);
exit();
?>