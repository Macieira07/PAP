<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
session_start();
require_once '../conexao.php';
$success = false;
$message = '';
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Dados do formulário
    $nome = $_POST['nome'] ?? '';
    $email = $_POST['email'] ?? '';
    $telefone = $_POST['telefone'] ?? '';
    $documento = $_POST['documento'] ?? '';

    // Verifica se o email já está registado
    $verifica = $conexao->prepare('SELECT H_id_hospede FROM hospedes WHERE H_email = ?');
    $verifica->bind_param('s', $email);
    $verifica->execute();
    $resultado = $verifica->get_result();

    if ($resultado->num_rows > 0) {
        $message = 'Este email já está registado.';
    } else {
        // Geração do token
        $token = bin2hex(random_bytes(16));
        $expira = date('Y-m-d H:i:s', strtotime('+1 hour'));

        // Inserção do hóspede na base de dados
        $stmt = $conexao->prepare('INSERT INTO hospedes (H_nome, H_email, H_telefone, H_documento_ident, H_token_verificacao, H_token_expira, H_verificado_email) 
                                   VALUES (?, ?, ?, ?, ?, ?, 0)');
        $stmt->bind_param('ssssss', $nome, $email, $telefone, $documento, $token, $expira);

        if ($stmt->execute()) {
            $link = "http://localhost/PAP/login1/verify.php?token=$token";

            $subject = 'Verifique seu email - Quinta Flores';
            $body = "<h2>Bem-vindo à Quinta Flores!</h2>
                     <p>A sua conta foi criada com sucesso! Obrigado por se registar. Por favor, clique no link abaixo para verificar o seu email:</p>
                     <p><a href='$link'>Verificar Email</a></p>";

            $headers = "MIME-Version: 1.0\r\n";
            $headers .= "Content-type:text/html;charset=UTF-8\r\n";
            $headers .= "From: Quinta Flores <noreply@quintaflores.com>\r\n";

            if (mail($email, $subject, $body, $headers)) {
                $success = true;
                $message = 'Registo efetuado com sucesso! Verifique o seu email para ativar a conta.';
            } else {
                $message = 'Erro ao enviar email de verificação.';
            }
        } else {
            $message = 'Erro ao registar hóspede.';
        }
    }
} elseif (isset($_GET['token'])) {
    $token = $_GET['token'];

    $verificaToken = $conexao->prepare('SELECT H_id_hospede FROM hospedes WHERE H_token_verificacao = ? AND H_token_expira > NOW()');
    $verificaToken->bind_param('s', $token);
    $verificaToken->execute();
    $resultado = $verificaToken->get_result();

    if ($resultado->num_rows > 0) {
        $atualiza = $conexao->prepare('UPDATE hospedes SET H_verificado_email = 1 WHERE H_token_verificacao = ?');
        $atualiza->bind_param('s', $token);
        $atualiza->execute();
        $success = true;
        $message = 'Email verificado com sucesso!';
    } else {
        $message = 'Token inválido ou expirado.';
    }
} else {
    $message = 'Acesso inválido.';
}
?>
<!DOCTYPE html>
<html lang="pt">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Verificação</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <style>
        :root {
            --primary-color: #10B981;
            --primary-color-dark: #047857;
            --secondary-color: #6A0DAD;
            --text-dark: #111827;
            --text-light: #6B7280;
            --white: #F9FAFB;
            --light-bg: #F3F4F6;
            --card-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
            --transition: all 0.3s ease;
            --border-radius: 10px;
            --font-family: 'Inter', sans-serif;
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: var(--font-family);
            background-color: var(--light-bg);
            color: var(--text-dark);
            line-height: 1.6;
            display: flex;
            justify-content: center;
            align-items: center;
            min-height: 100vh;
            padding: 20px;
        }

        .container {
            max-width: 500px;
            width: 100%;
            background-color: var(--white);
            border-radius: var(--border-radius);
            box-shadow: var(--card-shadow);
            padding: 40px;
            text-align: center;
            position: relative;
            overflow: hidden;
        }

        .container::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            height: 6px;
            background: linear-gradient(90deg, var(--primary-color), var(--secondary-color));
        }

        .icon {
            width: 80px;
            height: 80px;
            border-radius: 50%;
            background-color: <?php echo $success ? 'rgba(16, 185, 129, 0.1)' : 'rgba(239, 68, 68, 0.1)'; ?>;
            margin: 0 auto 24px;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .icon svg {
            width: 40px;
            height: 40px;
            fill: <?php echo $success ? 'var(--primary-color)' : '#EF4444'; ?>;
        }

        h2 {
            font-size: 24px;
            margin-bottom: 16px;
            font-weight: 600;
            color: <?php echo $success ? 'var(--primary-color-dark)' : '#B91C1C'; ?>;
        }

        p {
            color: var(--text-light);
            margin-bottom: 24px;
        }

        .button {
            display: inline-block;
            background-color: var(--primary-color);
            color: var(--white);
            padding: 12px 24px;
            border-radius: var(--border-radius);
            text-decoration: none;
            font-weight: 500;
            transition: var(--transition);
            border: none;
            cursor: pointer;
        }

        .button:hover {
            background-color: var(--primary-color-dark);
            transform: translateY(-2px);
        }

        .button-secondary {
            background-color: var(--white);
            color: var(--text-dark);
            border: 1px solid var(--text-light);
        }

        .button-secondary:hover {
            background-color: var(--light-bg);
            color: var(--text-dark);
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="icon">
            <?php if ($success): ?>
                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24">
                    <path d="M9 16.17L4.83 12l-1.42 1.41L9 19 21 7l-1.41-1.41L9 16.17z"/>
                </svg>
            <?php else: ?>
                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24">
                    <path d="M19 6.41L17.59 5 12 10.59 6.41 5 5 6.41 10.59 12 5 17.59 6.41 19 12 13.41 17.59 19 19 17.59 13.41 12 19 6.41z"/>
                </svg>
            <?php endif; ?>
        </div>
        
        <h2><?= htmlspecialchars($message) ?></h2>
        
        <?php if ($success): ?>
            <p>Sua verificação foi concluída com sucesso. Agora você pode prosseguir para a página de login.</p>
            <a href="login.php" class="button">Ir para o login</a>
        <?php else: ?>
            <p>Ocorreu um problema durante a verificação. Por favor, tente novamente.</p>
            <a href="javascript:history.back()" class="button button-secondary">Voltar</a>
        <?php endif; ?>
    </div>
</body>
</html>