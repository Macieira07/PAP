
<?php
session_start();
require_once '../conexao.php';

$token = $_GET['token'] ?? '';
$message = '';
$success = false;

if (!empty($token)) {
    // Verifica na tabela hospedes
    $stmt = $conexao->prepare("SELECT H_id_hospede FROM hospedes WHERE H_token_verificacao = ? AND H_token_expira > NOW()");
    $stmt->bind_param("s", $token);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows === 1) {
        $hospede = $result->fetch_assoc();
        $update = $conexao->prepare("UPDATE hospedes SET H_verificado_email = 'Sim', H_token_verificacao = NULL, H_token_expira = NULL WHERE H_id_hospede = ?");
        $update->bind_param("i", $hospede['H_id_hospede']);
        
        if ($update->execute()) {
            $message = "Email verificado com sucesso!";
            $success = true;
        } else {
            $message = "Erro ao atualizar o banco de dados.";
        }
    } else {
        $message = "Token inválido ou expirado.";
    }
} else {
    $message = "Link inválido.";
}
?>

<!DOCTYPE html>
<html lang="pt">
<head>
    <meta charset="UTF-8">
    <title>Verificação de Email</title>
</head>
<body>
    <?php if ($success): ?>
        <p style="color: green;"><?= $message ?></p>
        <a href="login.php">Ir para Login</a>
    <?php else: ?>
        <p style="color: red;"><?= $message ?></p>
    <?php endif; ?>
</body>
</html>