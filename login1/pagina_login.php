<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
if (!isset($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}
$csrf_token = $_SESSION['csrf_token']; 

?>
<!DOCTYPE html>
<html lang="pt">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="Página de login e registro do sistema">
    <title>Acesso ao Sistema</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/intl-tel-input/17.0.19/css/intlTelInput.min.css"/>

    <link rel="icon" type="image/png" sizes="32x32" href="../assets/logos/favicon-32x32.png">
    <link rel="icon" type="image/png" sizes="16x16" href="../assets/logos/favicon-16x16.png">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css" crossorigin="anonymous">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" type="text/css" href="login.css">
</head>
<body>
    <div class="container">
        <div class="card-header">
            <h1 class="card-title" id="headerTitle">Entrar</h1>
            <p class="card-subtitle" id="headerSubtitle">Acesse sua conta para continuar</p>
        </div>
        <div class="card-body">
            <!-- Formulário de Login -->
            <form id="loginForm" method="POST" action="login.php" class="form-section active">
                <div id="loginError" class="message error-message"></div>
                
                <div class="form-group">
                    <label for="loginEmail">Email</label>
                    <i class="fas fa-envelope"></i>
                    <input type="email" id="loginEmail" name="email" class="form-control" placeholder="seu@email.com" required>
                </div>
                <div class="form-group">
                    <label for="loginPassword">Senha</label>
                    <i class="fas fa-lock"></i>
                    <input type="password" id="loginPassword" name="senha" class="form-control" placeholder="••••••••" required>
                </div>
                

                <a href="recuperar_senha.php" class="extra-link">Esqueceu sua senha?</a>
                <input type="hidden" name="csrf_token" value="<?php echo $csrf_token; ?>">
                <button type="submit" class="btn">
                    <i class="fas fa-sign-in-alt"></i> Entrar
                </button>
                
                <div class="form-footer">
                    Não tem conta? <a href="#" id="showRegister">Criar conta</a>
                    <a href="../index.php" class="extra-link">Voltar</a>
                </div>
            </form>
            <!-- Formulário de Registro -->
            <form id="registerForm" method="POST" action="registar.php" class="form-section">
                <div id="registerError" class="message error-message"></div>
                <div id="registerSuccess" class="message success-message"></div>
                
                <div class="form-group">
                    <label for="registerName">Nome Completo</label>
                    <i class="fas fa-user"></i>
                    <input type="text" id="registerName" name="nome" class="form-control" placeholder="Seu nome completo" required>
                </div>
                <div class="form-group">
                    <label for="registerEmail">Email</label>
                    <i class="fas fa-envelope"></i>
                    <input type="email" id="registerEmail" name="email" class="form-control" placeholder="seu@email.com" required>
                </div>
                <div class="form-group">
                    <label for="registerPassword">Senha</label>
                    <i class="fas fa-lock"></i>
                    <input type="password" id="registerPassword" name="password" class="form-control" placeholder="••••••••" required
                           pattern="(?=.*\d)(?=.*[a-z])(?=.*[A-Z]).{8,}"
                           title="Deve conter pelo menos 8 caracteres, incluindo uma maiúscula, uma minúscula e um número">
                </div>
<div class="form-group">
    <label for="registerPhone">Telefone</label>
    <div class="phone-input-container">
        <select id="countryCode" class="country-code-select">
            <option value="+351" selected>Portugal (+351)</option>
            <option value="+55">Brasil (+55)</option>
            <option value="+1">Estados Unidos/Canadá (+1)</option>
            <option value="+44">Reino Unido (+44)</option>
            <option value="+34">Espanha (+34)</option>
            <option value="+33">França (+33)</option>
            <option value="+49">Alemanha (+49)</option>
            <option value="+244">Angola (+244)</option>
            <option value="+258">Moçambique (+258)</option>
            <option value="+238">Cabo Verde (+238)</option>
            <option value="+239">São Tomé e Príncipe (+239)</option>
            <option value="+245">Guiné-Bissau (+245)</option>
            <option value="+240">Guiné Equatorial (+240)</option>
            <option value="+670">Timor-Leste (+670)</option>
            <option value="+853">Macau (+853)</option>
        </select>
        <i class="fas fa-phone"></i>
        <input type="tel" id="registerPhone" name="telefone" class="form-control phone-with-code" placeholder="Seu número" required>
    </div>
</div>

                <div class="form-group">
                    <label for="registerDocument">Documento de Identificação</label>
                    <i class="fas fa-id-card"></i>
                    <input type="text" id="registerDocument" name="documento" class="form-control" placeholder="NIF " required>
                </div>
                
                <div class="checkbox-group">
                    <input type="checkbox" id="acceptTerms" name="aceitar_termos" required>
                    <label for="acceptTerms">
                        Aceito os <a href="termos.html" target="_blank">termos de uso</a> e a 
                        <a href="privacidade.html" target="_blank">política de privacidade</a>
                    </label>
                </div>
                
                <input type="hidden" name="csrf_token" value="<?php echo $csrf_token; ?>">
                <button type="submit" class="btn">
                    <i class="fas fa-user-plus"></i> Criar Conta
                </button>
                
                <div class="form-footer">
                    Já tem conta? <a href="#" id="showLogin">Fazer Login</a>
                    <a href="../index.php" class="extra-link">Voltar</a>
                </div>
            </form>
        </div>
    </div>
<script src="login.js"></script>
<script>
    const input = document.querySelector("#registerPhone");
    const iti = window.intlTelInput(input, {
        initialCountry: "pt", // Portugal por defeito
        preferredCountries: ["pt", "br", "us", "gb", "es", "fr", "de"],
        utilsScript: "https://cdnjs.cloudflare.com/ajax/libs/intl-tel-input/17.0.19/js/utils.min.js"
    });

    // Opcional: envia o número completo no formato internacional
    document.getElementById("registerForm").addEventListener("submit", function(event) {
        const numeroFormatado = iti.getNumber(); // exemplo: +351912345678
        input.value = numeroFormatado;
    });
</script>

</body>
</html>
