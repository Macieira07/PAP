<?php

/*
 * ============================================================
 *   Página de Redefinição de Senha - Quinta Flores
 * ============================================================
 *
 *   Linguagens Utilizadas:
 *     - PHP (backend, lógica de redefinição)
 *     - HTML5 (estrutura)
 *     - CSS3 (estilos, arquivos externos)
 *     - JavaScript (interatividade, validação)
 *
 *   Bibliotecas e Frameworks:
 *     - PHPMailer (envio de emails)
 *     - Google Fonts (fontes personalizadas)
 *     - i18n (internacionalização, multi-idioma)
 *
 *   Estrutura da Página:
 *     1. Configuração inicial PHP (includes, sessão)
 *     2. <head> com meta tags, fontes, CSS
 *     3. Formulário de redefinição de senha
 *     4. Mensagens de erro/sucesso
 *     5. Scripts finais (JS)
 *
 *   Autor: [Seu Nome ou Equipa]
 *   Última atualização: [Data]
 * ============================================================
 */
// ===================== 1. Configuração Inicial PHP =====================
session_start();
require_once '../conexao.php';
require_once 'email_functions.php';

// Verifica se há um token na URL
$token = $_GET['token'] ?? '';

if (empty($token)) {
    die('Token inválido ou expirado.');
}

// Mensagens de status
$message = '';
$message_type = 'error';

// Processar formulário de redefinição
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['nova_senha'])) {
    $nova_senha = trim($_POST['nova_senha']);
    $confirmar_senha = trim($_POST['confirmar_senha']);

    // Validações
    if (empty($nova_senha) || empty($confirmar_senha)) {
        $message = 'Por favor, preencha todos os campos.';
    } elseif ($nova_senha !== $confirmar_senha) {
        $message = 'As senhas não coincidem.';
    } elseif (strlen($nova_senha) < 8) {
        $message = 'A senha deve ter pelo menos 8 caracteres.';
    } else {
        try {
            // Verifica se o token é válido e não expirou
            $stmt = $conexao->prepare('SELECT H_id_hospede FROM hospedes WHERE H_reset_token = ? AND H_reset_expires > NOW()');
            $stmt->bind_param('s', $token);
            $stmt->execute();
            $result = $stmt->get_result();

            if ($result->num_rows === 1) {
                $hospede = $result->fetch_assoc();
                $senha_hash = password_hash($nova_senha, PASSWORD_DEFAULT);

                // Atualiza a senha e limpa o token
                $update = $conexao->prepare('UPDATE hospedes SET H_senha = ?, H_reset_token = NULL, H_reset_expires = NULL WHERE H_id_hospede = ?');
                $update->bind_param('si', $senha_hash, $hospede['H_id_hospede']);

                if ($update->execute()) {
                    $message = 'Senha redefinida com sucesso! Você já pode fazer login com a nova senha.';
                    $message_type = 'success';
                } else {
                    throw new Exception('Erro ao atualizar a senha.');
                }
            } else {
                $message = 'Token inválido ou expirado. Solicite um novo link de recuperação.';
            }
        } catch (Exception $e) {
            error_log('Erro ao redefinir senha: ' . $e->getMessage());
            $message = 'Ocorreu um erro ao redefinir sua senha. Por favor, tente novamente.';
        }
    }
}

// Verifica se o token é válido (para mostrar o formulário)
$stmt = $conexao->prepare('SELECT H_email FROM hospedes WHERE H_reset_token = ? AND H_reset_expires > NOW()');
$stmt->bind_param('s', $token);
$stmt->execute();
$result = $stmt->get_result();
$token_valido = $result->num_rows === 1;
$email = $token_valido ? $result->fetch_assoc()['H_email'] : '';
?>
<!DOCTYPE html>
<html lang="pt">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Redefinir Senha - Quinta Flores</title>
    <style>
        :root {
            --primary-color: #10B981;
            --error-color: #EF4444;
            --success-color: #10B981;
            --text-dark: #111827;
            --text-light: #6B7280;
            --white: #FFFFFF;
            --light-bg: #F3F4F6;
        }
        
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background-color: var(--light-bg);
            display: flex;
            justify-content: center;
            align-items: center;
            min-height: 100vh;
            margin: 0;
            padding: 20px;
        }
        
        .container {
            background-color: var(--white);
            border-radius: 10px;
            box-shadow: 0 4px 20px rgba(0,0,0,0.1);
            padding: 30px;
            width: 100%;
            max-width: 500px;
        }
        
        h1 {
            color: var(--primary-color);
            text-align: center;
            margin-bottom: 20px;
        }
        
        .alert {
            padding: 15px;
            margin-bottom: 20px;
            border-radius: 5px;
        }
        
        .alert-success {
            background-color: rgba(16, 185, 129, 0.1);
            color: var(--success-color);
            border: 1px solid var(--success-color);
        }
        
        .alert-error {
            background-color: rgba(239, 68, 68, 0.1);
            color: var(--error-color);
            border: 1px solid var(--error-color);
        }
        
        .form-group {
            margin-bottom: 20px;
        }
        
        label {
            display: block;
            margin-bottom: 8px;
            font-weight: 600;
            color: var(--text-dark);
        }
        
        input[type="password"] {
            width: 100%;
            padding: 12px;
            border: 1px solid #ddd;
            border-radius: 5px;
            font-size: 16px;
        }
        
        button {
            background-color: var(--primary-color);
            color: white;
            border: none;
            padding: 12px;
            width: 100%;
            border-radius: 5px;
            font-size: 16px;
            cursor: pointer;
            transition: background-color 0.3s;
        }
        
        button:hover {
            background-color: #0E9F6E;
        }
        
        .text-center {
            text-align: center;
        }
        
        .mt-3 {
            margin-top: 15px;
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>Redefinir Senha</h1>
        
        <?php if (!empty($message)): ?>
            <div class="alert alert-<?= $message_type ?>">
                <?= htmlspecialchars($message) ?>
            </div>
        <?php endif; ?>
        
        <?php if ($token_valido && $message_type !== 'success'): ?>
            <form method="POST" action="">
                <div class="form-group">
                    <label for="nova_senha">Nova Senha</label>
                    <input type="password" id="nova_senha" name="nova_senha" required 
                           placeholder="Mínimo 8 caracteres">
                </div>
                
                <div class="form-group">
                    <label for="confirmar_senha">Confirmar Nova Senha</label>
                    <input type="password" id="confirmar_senha" name="confirmar_senha" required 
                           placeholder="Digite a senha novamente">
                </div>
                
                <input type="hidden" name="token" value="<?= htmlspecialchars($token) ?>">
                <button type="submit">Redefinir Senha</button>
            </form>
        <?php elseif (!$token_valido): ?>
            <div class="alert alert-error">
                Token inválido ou expirado. Por favor, solicite um novo link de recuperação.
            </div>
            <div class="text-center mt-3">
                <a href="recuperar_senha.php">Solicitar novo link</a>
            </div>
        <?php endif; ?>
        
        <?php if ($message_type === 'success'): ?>
            <div class="text-center mt-3">
                <a href="login.php">Ir para o login</a>
            </div>
        <?php endif; ?>
    </div>
</body>
</html>