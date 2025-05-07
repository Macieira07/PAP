<?php
// marcar_como_lida.php
session_start();
require __DIR__ . '/../conexao.php';

// Verificar se o usuário está logado e é um funcionário
if (!isset($_SESSION['id']) || $_SESSION['tipo'] !== 'funcionario') {
    echo 'erro_autenticacao';
    exit;
}

if (isset($_GET['id'])) {
    $idNotificacao = (int)$_GET['id'];
    
    // Marcar a notificação como lida
    $stmt = $conexao->prepare("UPDATE notificacoes SET lida = 1 WHERE id = ?");
    $stmt->bind_param("i", $idNotificacao);
    
    if ($stmt->execute()) {
        echo 'sucesso';
    } else {
        echo 'erro';
    }
} else {
    echo 'id_nao_fornecido';
}
?>