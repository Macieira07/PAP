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
            $message = "Email verificado com sucesso! Agora você pode fazer login.";
            $success = true;
        } else {
            $message = "Erro ao atualizar o banco de dados.";
        }
    } else {
        $message = "Token inválido ou expirado. Solicite uma nova verificação.";
    }
} else {
    $message = "Link inválido ou parâmetros ausentes.";
}
?>

<!DOCTYPE html>
<html lang="pt">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Verificação de Email - Quinta Flores</title>
    <link rel="icon" type="image/x-icon" href="../logotipos/logotipo2.png">
    <style>
        :root {
            --primary-color: #10B981;
            --primary-color-dark: #047857;
            --text-dark: #111827;
            --text-light: #6B7280;
            --white: #F9FAFB;
            --success-color: #10B981;
            --error-color: #EF4444;
            --font-family: "Inter", sans-serif;
        }
        
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: var(--font-family);
        }
        
        body {
            display: flex;
            justify-content: center;
            align-items: center;
            min-height: 100vh;
            background: linear-gradient(45deg, var(--primary-color), var(--primary-color-dark));
            font-family: var(--font-family);
            color: #333;
        }
        
        .container {
            background-color: white;
            border-radius: 10px;
            box-shadow: 0 0 20px rgba(0, 0, 0, 0.1);
            width: 100%;
            max-width: 500px;
            padding: 40px;
        }
        
        h1 {
            color: var(--primary-color);
            text-align: center;
            margin-bottom: 20px;
        }
        
        .message {
            padding: 15px;
            margin: 20px 0;
            border-radius: 5px;
            text-align: center;
            font-size: 16px;
        }
        
        .success {
            background-color: rgba(40, 167, 69, 0.1);
            color: var(--success-color);
            border: 1px solid var(--success-color);
        }
        
        .error {
            background-color: rgba(220, 53, 69, 0.1);
            color: var(--error-color);
            border: 1px solid var(--error-color);
        }
        
        .btn {
            display: block;
            width: 100%;
            max-width: 200px;
            margin: 20px auto 0;
            padding: 12px;
            background-color: var(--primary-color);
            color: var(--white);
            border: none;
            border-radius: 5px;
            text-align: center;
            text-decoration: none;
            font-size: 16px;
            font-weight: 500;
            cursor: pointer;
            transition: background-color 0.3s;
        }
        
        .btn:hover {
            background-color: var(--primary-color-dark);
        }
        
        .icon {
            display: block;
            font-size: 48px;
            margin: 0 auto 20px;
            text-align: center;
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>Verificação de Email</h1>
        
        <?php if ($success): ?>
            <div class="message success">
                <span class="icon">✓</span>
                <?= htmlspecialchars($message) ?>
            </div>
            <a href="login.php" class="btn">Ir para Login</a>
        <?php else: ?>
            <div class="message error">
                <span class="icon">✗</span>
                <?= htmlspecialchars($message) ?>
            </div>
            <a href="login.php" class="btn">Voltar para Login</a>
        <?php endif; ?>
    </div>
</body>
</html>