<?php
require '../../conexao.php';
session_start();

// Verificar se o usuário está logado e tem permissão
if (!isset($_SESSION['usuario_id'])) {
    header("Location: ../../login.php");
    exit;
}

// Verificar ação e ID
$acao = $_GET['acao'] ?? '';
$id = $_GET['id'] ?? 0;

if (!in_array($acao, ['confirmar', 'cancelar']) || !is_numeric($id)) {
    $_SESSION['flash'] = ['msg' => 'Ação inválida.', 'type' => 'error'];
    header("Location: reservas.php");
    exit;
}

// Buscar a reserva
$stmt = $conexao->prepare("SELECT * FROM reservas WHERE R_id_reserva = ?");
$stmt->bind_param("i", $id);
$stmt->execute();
$reserva = $stmt->get_result()->fetch_assoc();

if (!$reserva) {
    $_SESSION['flash'] = ['msg' => 'Reserva não encontrada.', 'type' => 'error'];
    header("Location: reservas.php");
    exit;
}

// Processar a ação
if ($acao === 'confirmar') {
    // Atualizar status para confirmada
    $stmt = $conexao->prepare("UPDATE reservas SET R_estado = 'confirmada' WHERE R_id_reserva = ?");
    $stmt->bind_param("i", $id);
    
    if ($stmt->execute()) {
        // Registrar no histórico
        $descricao = "Reserva #$id confirmada";
        $stmt_hist = $conexao->prepare("INSERT INTO historico_reservas (HR_id_reserva, HR_acao, HR_descricao) VALUES (?, 'confirmacao', ?)");
        $stmt_hist->bind_param("is", $id, $descricao);
        $stmt_hist->execute();
        
        $_SESSION['flash'] = ['msg' => 'Reserva confirmada com sucesso!', 'type' => 'success'];
    } else {
        $_SESSION['flash'] = ['msg' => 'Erro ao confirmar reserva.', 'type' => 'error'];
    }
} elseif ($acao === 'cancelar') {
    // Atualizar status para cancelada
    $stmt = $conexao->prepare("UPDATE reservas SET R_estado = 'cancelada' WHERE R_id_reserva = ?");
    $stmt->bind_param("i", $id);
    
    if ($stmt->execute()) {
        // Registrar no histórico
        $descricao = "Reserva #$id cancelada";
        $stmt_hist = $conexao->prepare("INSERT INTO historico_reservas (HR_id_reserva, HR_acao, HR_descricao) VALUES (?, 'cancelamento', ?)");
        $stmt_hist->bind_param("is", $id, $descricao);
        $stmt_hist->execute();
        
        $_SESSION['flash'] = ['msg' => 'Reserva cancelada com sucesso!', 'type' => 'success'];
    } else {
        $_SESSION['flash'] = ['msg' => 'Erro ao cancelar reserva.', 'type' => 'error'];
    }
}

header("Location: reservas.php");
exit;