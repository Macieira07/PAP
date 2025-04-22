<?php
// Configuração de erros (desenvolvimento)
error_reporting(E_ALL);
ini_set('display_errors', 1);
ini_set('log_errors', 1);
ini_set('error_log', __DIR__ . '/recovery_errors.log');

// Iniciar sessão
session_start();

// Verificar arquivos necessários
if (!file_exists('../conexao.php') || !file_exists('mail_config.php')) {
    die("<h1>Erro: Arquivos essenciais não encontrados!</h1>
        <p>Verifique se os arquivos existem:</p>
        <ul>
            <li>../conexao.php</li>
            <li>mail_config.php</li>
        </ul>");
}

// Incluir arquivos necessários
require_once '../conexao.php';
require_once 'mail_config.php';

// Verificar conexão com banco de dados
if (!$conexao || $conexao->connect_error) {
    die("<h1>Erro de conexão com o banco de dados</h1>
        <p>" . ($conexao->connect_error ?? "Erro desconhecido") . "</p>");
}

// Inicializar variáveis de mensagem
$message = '';
$message_type = 'info'; // padrão: info (pode ser success, error, info)

// Processar formulário
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Validar email
    $email = filter_input(INPUT_POST, 'email', FILTER_SANITIZE_EMAIL);
    
    if (empty($email)) {
        $message = "Por favor, insira um email válido";
        $message_type = "error";
    } else {
        try {
            // Buscar usuário no banco de dados
            $stmt = $conexao->prepare("SELECT H_id_hospede, H_nome FROM hospedes WHERE H_email = ?");
            
            if (!$stmt) {
                throw new Exception("Erro ao preparar consulta: " . $conexao->error);
            }
            
            $stmt->bind_param("s", $email);
            
            if (!$stmt->execute()) {
                throw new Exception("Erro ao executar consulta: " . $stmt->error);
            }
            
            $result = $stmt->get_result();
            
            if ($result->num_rows === 1) {
                $hospede = $result->fetch_assoc();
                
                // Gerar token único
                $token = bin2hex(random_bytes(16));
                $expires = date('Y-m-d H:i:s', strtotime('+1 hour'));
                
                // Atualizar token no banco
                $update = $conexao->prepare("UPDATE hospedes SET H_reset_token = ?, H_reset_expires = ? WHERE H_id_hospede = ?");
                
                if (!$update) {
                    throw new Exception("Erro ao preparar atualização: " . $conexao->error);
                }
                
                $update->bind_param("ssi", $token, $expires, $hospede['H_id_hospede']);
                
                if (!$update->execute()) {
                    throw new Exception("Erro ao atualizar token: " . $update->error);
                }
                
                // Preparar link de recuperação
                $reset_link = "http://" . $_SERVER['HTTP_HOST'] . "/PAP/login/reset_senha.php?token=" . urlencode($token);
                
                // Criar email
                $subject = "Recuperação de Senha - Quinta Flores";
                $body = "
                    <h2>Recuperação de Senha</h2>
                    <p>Olá " . htmlspecialchars($hospede['H_nome']) . ",</p>
                    <p>Recebemos uma solicitação para redefinir sua senha.</p>
                    <p>Clique no link abaixo ou copie e cole no seu navegador:</p>
                    <p><a href='$reset_link'>$reset_link</a></p>
                    <p><strong>Este link expira em 1 hora.</strong></p>
                    <p>Se você não solicitou esta alteração, ignore este email.</p>
                    <p>Atenciosamente,<br>Equipe Quinta Flores</p>
                ";
                
                // Enviar email
                if (enviarEmail($email, $subject, $body)) {
                    $message = "Um email com instruções foi enviado para $email";
                    $message_type = "success";
                    
                    // Log para debug
                    error_log("Email de recuperação enviado para: $email");
                } else {
                    throw new Exception("Falha ao enviar email de recuperação");
                }
            } else {
                // Mensagem genérica por segurança
                $message = "Se este email estiver cadastrado, você receberá um link de recuperação em breve.";
                $message_type = "info";
            }
        } catch (Exception $e) {
            error_log("ERRO: " . $e->getMessage());
            $message = "Ocorreu um erro ao processar sua solicitação. Por favor, tente novamente.";
            $message_type = "error";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="pt">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Recuperar Senha - Quinta Flores</title>
    <style>
        :root {
            --primary-color: #6A0DAD;
            --error-color: #dc3545;
            --success-color: #28a745;
            --info-color: #17a2b8;
        }
        
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background-color: #f8f9fa;
            margin: 0;
            padding: 0;
            display: flex;
            justify-content: center;
            align-items: center;
            min-height: 100vh;
            color: #333;
        }
        
        .container {
            background-color: white;
            border-radius: 10px;
            box-shadow: 0 0 20px rgba(0, 0, 0, 0.1);
            width: 100%;
            max-width: 500px;
            padding: 30px;
            margin: 20px;
        }
        
        h1 {
            color: var(--primary-color);
            text-align: center;
            margin-bottom: 30px;
        }
        
        .alert {
            padding: 15px;
            margin-bottom: 20px;
            border-radius: 5px;
            font-size: 16px;
        }
        
        .alert-success {
            background-color: rgba(40, 167, 69, 0.1);
            color: var(--success-color);
            border: 1px solid var(--success-color);
        }
        
        .alert-error {
            background-color: rgba(220, 53, 69, 0.1);
            color: var(--error-color);
            border: 1px solid var(--error-color);
        }
        
        .alert-info {
            background-color: rgba(23, 162, 184, 0.1);
            color: var(--info-color);
            border: 1px solid var(--info-color);
        }
        
        .form-group {
            margin-bottom: 20px;
        }
        
        label {
            display: block;
            margin-bottom: 8px;
            font-weight: 600;
        }
        
        input[type="email"] {
            width: 100%;
            padding: 12px;
            border: 1px solid #ddd;
            border-radius: 5px;
            font-size: 16px;
            transition: border 0.3s;
        }
        
        input[type="email"]:focus {
            border-color: var(--primary-color);
            outline: none;
            box-shadow: 0 0 0 3px rgba(106, 13, 173, 0.2);
        }
        
        button {
            background-color: var(--primary-color);
            color: white;
            border: none;
            padding: 12px;
            width: 100%;
            border-radius: 5px;
            font-size: 16px;
            font-weight: 600;
            cursor: pointer;
            transition: background-color 0.3s;
        }
        
        button:hover {
            background-color: #5a0a9c;
        }
        
        .text-center {
            text-align: center;
        }
        
        .mt-3 {
            margin-top: 15px;
        }
        
        a {
            color: var(--primary-color);
            text-decoration: none;
            font-weight: 600;
        }
        
        a:hover {
            text-decoration: underline;
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>Recuperar Senha</h1>
        
        <?php if (!empty($message)): ?>
            <div class="alert alert-<?= $message_type ?>">
                <?= htmlspecialchars($message) ?>
            </div>
        <?php endif; ?>
        
        <form method="POST" action="">
            <div class="form-group">
                <label for="email">Email cadastrado</label>
                <input type="email" id="email" name="email" required placeholder="seu@email.com">
            </div>
            
            <button type="submit">Enviar Link de Recuperação</button>
        </form>
        
        <div class="text-center mt-3">
            <a href="login.php">Voltar para o login</a>
        </div>
    </div>
</body>
</html>