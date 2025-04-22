<?php
// Configurações iniciais
error_reporting(E_ALL);
ini_set('display_errors', 1);
ini_set('log_errors', 1);
ini_set('error_log', __DIR__ . '/email_test_errors.log');

// Carregar configurações
require_once 'init.php';
require_once 'mail_config.php';

// Inicializar variáveis
$result = '';
$status = '';
$error_details = '';

// Executar teste de email ao enviar formulário
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $to = filter_var($_POST['email'] ?? '', FILTER_SANITIZE_EMAIL);
    
    if (empty($to) || !filter_var($to, FILTER_VALIDATE_EMAIL)) {
        $result = "Por favor, informe um email válido para teste.";
        $status = "error";
    } else {
        try {
            $subject = "Teste de Configuração de Email - Quinta Flores";
            $message = "
                <html>
                <body>
                    <h2>Teste de Email</h2>
                    <p>Este é um email de teste para verificar se a configuração do PHPMailer está funcionando corretamente.</p>
                    <p>Data e hora do envio: " . date('d/m/Y H:i:s') . "</p>
                    <p><strong>Se você recebeu este email, a configuração está correta!</strong></p>
                </body>
                </html>
            ";
            
            if (enviarEmail($to, $subject, $message)) {
                $result = "Email enviado com sucesso para $to!";
                $status = "success";
            } else {
                throw new Exception("Falha ao enviar email");
            }
        } catch (Exception $e) {
            $result = "Erro ao enviar email.";
            $status = "error";
            $error_details = $e->getMessage();
        }
    }
}
?>

<!DOCTYPE html>
<html lang="pt">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Teste de Email - Quinta Flores</title>
    <style>
        :root {
            --primary-color: #6A0DAD;
            --success-color: #28a745;
            --error-color: #dc3545;
            --info-color: #17a2b8;
            --font-family: "Garamond";
        }
        
        body {
            font-family: var(--font-family);
            background-color: #f8f9fa;
            margin: 0;
            padding: 20px;
            color: #333;
        }
        
        .container {
            max-width: 600px;
            margin: 40px auto;
            background-color: white;
            border-radius: 10px;
            box-shadow: 0 0 20px rgba(0, 0, 0, 0.1);
            padding: 30px;
        }
        
        h1 {
            color: var(--primary-color);
            text-align: center;
            margin-bottom: 30px;
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
            box-sizing: border-box;
        }
        
        button {
            background-color: var(--primary-color);
            color: white;
            border: none;
            padding: 12px 20px;
            border-radius: 5px;
            font-size: 16px;
            cursor: pointer;
            font-weight: 500;
        }
        
        button:hover {
            background-color: #5a0a9c;
        }
        
        .result {
            margin-top: 20px;
            padding: 15px;
            border-radius: 5px;
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
        
        .details {
            margin-top: 10px;
            padding: 10px;
            background-color: #f8f9fa;
            border-radius: 5px;
            font-family: monospace;
            white-space: pre-wrap;
            overflow-x: auto;
        }
        
        .back-link {
            display: block;
            margin-top: 20px;
            text-align: center;
            color: var(--primary-color);
            text-decoration: none;
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>Teste de Email</h1>
        
        <form method="POST" action="">
            <div class="form-group">
                <label for="email">Email para Teste:</label>
                <input type="email" id="email" name="email" required 
                       placeholder="Digite o email para enviar o teste">
            </div>
            
            <button type="submit">Enviar Email de Teste</button>
        </form>
        
        <?php if (!empty($result)): ?>
            <div class="result <?= $status ?>">
                <?= htmlspecialchars($result) ?>
                
                <?php if (!empty($error_details)): ?>
                    <div class="details">
                        <?= htmlspecialchars($error_details) ?>
                    </div>
                <?php endif; ?>
            </div>
        <?php endif; ?>
        
        <a href="login.php" class="back-link">Voltar para Login</a>
    </div>
</body>
</html>