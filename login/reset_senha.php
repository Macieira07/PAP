<?php
// Ativar exibição de erros (apenas para desenvolvimento)
error_reporting(E_ALL);
ini_set('display_errors', 1);
ini_set('log_errors', 1);
ini_set('error_log', __DIR__ . '/reset_errors.log');

session_start();

// Verificar se o arquivo de conexão existe
if (!file_exists('../conexao.php')) {
    die("Erro: Arquivo de conexão não encontrado!");
}

require_once '../conexao.php';

// Verificar conexão com o banco de dados
if (!$conexao || $conexao->connect_error) {
    die("Erro na conexão com o banco de dados: " . ($conexao->connect_error ?? "Erro desconhecido"));
}

// Inicializar variáveis
$error = '';
$success = '';
$show_form = false;
$token = $_GET['token'] ?? '';

// Verificar se o token foi fornecido
if (empty($token)) {
    $error = "Token não fornecido!";
} else {
    try {
        // Verificar token no banco de dados
        $stmt = $conexao->prepare("SELECT H_id_hospede FROM hospedes WHERE H_reset_token = ? AND H_reset_expires > NOW()");
        
        if (!$stmt) {
            throw new Exception("Erro ao preparar consulta: " . $conexao->error);
        }
        
        $stmt->bind_param("s", $token);
        
        if (!$stmt->execute()) {
            throw new Exception("Erro ao executar consulta: " . $stmt->error);
        }
        
        $result = $stmt->get_result();
        
        if ($result->num_rows === 1) {
            $show_form = true;
            
            // Processar formulário se enviado
            if ($_SERVER['REQUEST_METHOD'] === 'POST') {
                $password = $_POST['password'] ?? '';
                $confirm_password = $_POST['confirm_password'] ?? '';
                
                if (empty($password) || empty($confirm_password)) {
                    $error = "Por favor, preencha ambos os campos!";
                } elseif ($password !== $confirm_password) {
                    $error = "As senhas não coincidem!";
                } elseif (strlen($password) < 8) {
                    $error = "A senha deve ter pelo menos 8 caracteres!";
                } else {
                    // Atualizar senha no banco de dados
                    $hashed_password = password_hash($password, PASSWORD_DEFAULT);
                    $update = $conexao->prepare("UPDATE hospedes SET H_senha = ?, H_reset_token = NULL, H_reset_expires = NULL WHERE H_reset_token = ?");
                    
                    if (!$update) {
                        throw new Exception("Erro ao preparar atualização: " . $conexao->error);
                    }
                    
                    $update->bind_param("ss", $hashed_password, $token);
                    
                    if ($update->execute()) {
                        $success = "Senha redefinida com sucesso!";
                        $show_form = false;
                    } else {
                        throw new Exception("Erro ao atualizar senha: " . $update->error);
                    }
                }
            }
        } else {
            $error = "Token inválido ou expirado!";
        }
    } catch (Exception $e) {
        error_log("ERRO: " . $e->getMessage());
        $error = "Ocorreu um erro ao processar sua solicitação. Por favor, tente novamente.";
    }
}
?>

<!DOCTYPE html>
<html lang="pt">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Redefinir Senha - Quinta Flores</title>
    <style>
        :root {
            --primary-color: #6A0DAD;
            --error-color: #dc3545;
            --success-color: #28a745;
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
        
        .form-group {
            margin-bottom: 20px;
        }
        
        label {
            display: block;
            margin-bottom: 8px;
            font-weight: 600;
        }
        
        input[type="password"] {
            width: 100%;
            padding: 12px;
            border: 1px solid #ddd;
            border-radius: 5px;
            font-size: 16px;
            transition: border 0.3s;
        }
        
        input[type="password"]:focus {
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
        <h1>Redefinir Senha</h1>
        
        <?php if (!empty($error)): ?>
            <div class="alert alert-error">
                <?= htmlspecialchars($error) ?>
            </div>
        <?php endif; ?>
        
        <?php if (!empty($success)): ?>
            <div class="alert alert-success">
                <?= htmlspecialchars($success) ?>
                <div class="mt-3">
                    <a href="login.php">Ir para a página de login</a>
                </div>
            </div>
        <?php endif; ?>
        
        <?php if ($show_form): ?>
    <form method="POST">
        <div class="form-group">
            <label for="password">Nova Senha</label>
            <input type="password" id="password" name="password" required minlength="8" placeholder="Mínimo 8 caracteres">
        </div>
        
        <div class="form-group">
            <label for="confirm_password">Confirmar Nova Senha</label>
            <input type="password" id="confirm_password" name="confirm_password" required minlength="8" placeholder="Digite novamente">
        </div>
        
        <button type="submit">Redefinir Senha</button>
    </form>
<?php endif; ?>

    </div>
</body>
</html>