<?php
// Inicializa variáveis
$senha = '';
$hash = '';

// Processa o formulário quando enviado
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['senha'])) {
    $senha = $_POST['senha'];
    $hash = password_hash($senha, PASSWORD_DEFAULT);
}
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gerador Simples de Hash</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        body {
            font-family: Arial, sans-serif;
            max-width: 600px;
            margin: 0 auto;
            padding: 20px;
        }
        h1 {
            color: #6A0DAD;
        }
        .form-group {
            margin-bottom: 15px;
            position: relative;
        }
        label {
            display: block;
            margin-bottom: 5px;
        }
        input[type="password"],
        input[type="text"] {
            width: calc(100% - 40px);
            padding: 8px;
            border: 1px solid #ddd;
            border-radius: 4px;
        }
        button {
            background-color: #6A0DAD;
            color: white;
            border: none;
            padding: 10px 15px;
            border-radius: 4px;
            cursor: pointer;
        }
        .result {
            margin-top: 20px;
            background-color: #f5f5f5;
            padding: 15px;
            border-radius: 4px;
            word-break: break-all;
        }
        .password-toggle {
            position: absolute;
            right: 10px;
            top: 28px;
            cursor: pointer;
            color: #6A0DAD;
        }
    </style>
</head>
<body>
    <h1>Gerador Simples de Hash</h1>
    
    <form method="POST">
        <div class="form-group">
            <label for="senha">Digite a senha:</label>
            <input type="password" id="senha" name="senha" value="<?php echo htmlspecialchars($senha); ?>" required>
            <span class="password-toggle" onclick="togglePassword()">
                <i id="eyeIcon" class="fas fa-eye"></i>
            </span>
        </div>
        
        <button type="submit">Gerar Hash</button>
    </form>
    
    <?php if (!empty($hash)): ?>
    <div class="result">
        <h3>Hash gerado:</h3>
        <p><?php echo htmlspecialchars($hash); ?></p>
    </div>
    <?php endif; ?>

    <script>
        function togglePassword() {
            const senhaInput = document.getElementById('senha');
            const eyeIcon = document.getElementById('eyeIcon');
            
            if (senhaInput.type === 'password') {
                senhaInput.type = 'text';
                eyeIcon.classList.remove('fa-eye');
                eyeIcon.classList.add('fa-eye-slash');
            } else {
                senhaInput.type = 'password';
                eyeIcon.classList.remove('fa-eye-slash');
                eyeIcon.classList.add('fa-eye');
            }
        }
    </script>
</body>
</html>
