<?php
require '../conexao.php';

if (isset($_GET['id'])) {
    $id = (int)$_GET['id'];
    
    // Marcar notificação como lida
    $stmt = $conexao->prepare("UPDATE notificacoes SET lida = 1 WHERE id = ?");
    $stmt->bind_param("i", $id);
    
    if ($stmt->execute()) {
        echo 'sucesso';
    } else {
        echo 'erro';
    }
} else {
    echo 'erro';
}
?>