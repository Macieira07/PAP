<?php
session_start();

// Registrar log de logout
require_once '../conexao.php';
try {
    $pdo = conexao();
    
    if (isset($_SESSION['admin_id'])) {
        $stmtLog = $pdo->prepare("INSERT INTO logs_acesso (usuario_id, tipo_usuario, acao, data) VALUES (?, 'funcionario', 'logout', NOW())");
        $stmtLog->execute([$_SESSION['admin_id']]);
    }
} catch (PDOException $e) {
    // Ignorar erros no logout
}

// Destruir a sessão
session_destroy();

// Redirecionar para a página de login
header('Location: login.php');
exit();
?>