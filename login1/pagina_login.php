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
    <title>Acesso ao Sistema - QUINTA FLORES</title>
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
                    <input type="password" id="loginPassword" name="senha" class="form-control" placeholder="••••••••" required>
                    <i class="fas fa-lock left-icon"></i>
                    <span class="toggle-password" data-target="loginPassword">
                        <i class="fas fa-eye"></i>
                    </span>
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
                    <input type="password" id="registerPassword" name="password" class="form-control" placeholder="••••••••" required
                           pattern="(?=.*\d)(?=.*[a-z])(?=.*[A-Z]).{8,}"
                           title="Deve conter pelo menos 8 caracteres, incluindo uma maiúscula, uma minúscula e um número">
                    <i class="fas fa-lock left-icon"></i>
                    <span class="toggle-password" data-target="registerPassword">
                        <i class="fas fa-eye"></i>
                    </span>
                </div>

                <div class="form-group">
                    <label for="registerDocument">Tipo de Documento</label>
                    <i class="fas fa-id-card"></i>
                    <select id="registerTipoDocumento" name="tipo_documento" class="form-control" required>
                        <option value="NIF">NIF</option>
                        <option value="Passaporte">Passaporte</option>
                        <option value="DNI">DNI</option>
                        <option value="Outro">Outro</option>
                    </select>
                </div>
                <div class="form-group">
                    <label for="registerDocument">Número do Documento</label>
                    <i class="fas fa-id-card"></i>
                    <input type="text" id="registerDocument" name="documento" class="form-control" placeholder="Número do documento" required>
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
// Desabilita o botão de submit ao enviar o formulário de login
const loginForm = document.getElementById('loginForm');
if (loginForm) {
    loginForm.addEventListener('submit', function(e) {
        const btn = loginForm.querySelector('button[type="submit"]');
        if (btn) {
            btn.disabled = true;
            btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Aguarde...';
        }
    });
}
// Desabilita o botão de submit ao enviar o formulário de registro
const registerForm = document.getElementById('registerForm');
if (registerForm) {
    registerForm.addEventListener('submit', function(e) {
        const btn = registerForm.querySelector('button[type="submit"]');
        if (btn) {
            btn.disabled = true;
            btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Aguarde...';
        }
    });
}
// Função para mostrar/ocultar senha
const togglePasswordIcons = document.querySelectorAll('.toggle-password');
togglePasswordIcons.forEach(function(icon) {
    icon.addEventListener('click', function(e) {
        e.preventDefault();
        e.stopPropagation();
        
        const targetId = icon.getAttribute('data-target');
        const input = document.getElementById(targetId);
        const iTag = icon.querySelector('i');
        
        if (input && iTag) {
            if (input.type === 'password') {
                input.type = 'text';
                iTag.classList.remove('fa-eye');
                iTag.classList.add('fa-eye-slash');
                icon.setAttribute('title', 'Ocultar senha');
            } else {
                input.type = 'password';
                iTag.classList.remove('fa-eye-slash');
                iTag.classList.add('fa-eye');
                icon.setAttribute('title', 'Mostrar senha');
            }
        }
    });
    
    // Adicionar título inicial
    icon.setAttribute('title', 'Mostrar senha');
});
</script>
</body>
</html>
