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
    
    // Verificar se a reserva existe e está pendente
    $stmt = $pdo->prepare("SELECT R_estado FROM reservas WHERE R_id_reserva = ?");
    $stmt->execute([$id]);
    $estado = $stmt->fetchColumn();
    
    if ($estado != 'pendente') {
        $_SESSION['erro'] = "Apenas reservas pendentes podem ser confirmadas!";
        header('Location: listar.php');
        exit();
    }
    
    // Confirmar reserva
    $stmt = $pdo->prepare("UPDATE reservas SET R_estado = 'confirmada' WHERE R_id_reserva = ?");
    $stmt->execute([$id]);
    
    $_SESSION['mensagem'] = "Reserva confirmada com sucesso!";
    
} catch (PDOException $e) {
    $_SESSION['erro'] = "Erro ao confirmar reserva: " . $e->getMessage();
}

header('Location: detalhes.php?id=' . $id);
exit();
?>