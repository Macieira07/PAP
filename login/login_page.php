<!DOCTYPE html>

<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if (!isset($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}
$csrf_token = $_SESSION['csrf_token']; 
?>
<html lang="pt">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="Página de login e registro do sistema">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css" crossorigin="anonymous">
    <link href="https://fonts.googleapis.com/css2?family=Garamond:wght@400;500;600;700&display=swap" rel="stylesheet">
    <title>Página de Login</title>
    <link rel="icon" type="image/x-icon" href="../logotipos/logotipo2.png">
    <style>
        :root {
            --primary-color: #6A0DAD;
            --primary-color-dark: #A56EFF;
            --text-dark: #0c0a09;
            --text-light: #78716c;
            --white: #ffffff;
            --max-width: 1200px;
            --font-family: "Garamond";
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
            overflow: hidden;
        }

        .container {
            position: relative;
            width: 900px;
            height: 550px;
            background: var(--white);
            box-shadow: 0 15px 50px rgba(0, 0, 0, 0.1);
            overflow: hidden;
            border-radius: 10px;
        }

        .form-container {
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            display: flex;
        }

        .form-section {
            position: relative;
            width: 50%;
            height: 100%;
            padding: 40px;
            transition: 0.5s;
        }

        .sign-in {
            background: var(--white);
            z-index: 2;
        }

        .sign-up {
            background: var(--primary-color);
            color: var(--white);
            left: 50%;
            z-index: 1;
            opacity: 0;
        }

        .title {
            font-size: 32px;
            margin-bottom: 30px;
            color: var(--primary-color);
            font-weight: 600;
        }

        .sign-up .title {
            color: var(--white);
        }

        .input-group {
            position: relative;
            width: 100%;
            margin-bottom: 25px;
        }

        .input-group input {
            width: 100%;
            padding: 10px 0;
            font-size: 16px;
            color: var(--text-dark);
            border: none;
            border-bottom: 2px solid var(--text-light);
            outline: none;
            background: transparent;
        }

        .sign-up .input-group input {
            color: var(--white);
            border-bottom-color: rgba(255,255,255,0.5);
        }

        .input-group label {
            position: absolute;
            left: 0;
            top: 10px;
            font-size: 16px;
            color: var(--text-light);
            pointer-events: none;
            transition: 0.3s;
        }

        .sign-up .input-group label {
            color: rgba(255,255,255,0.7);
        }

        .input-group input:focus ~ label,
        .input-group input:valid ~ label {
            top: -12px;
            font-size: 12px;
            color: var(--primary-color);
        }

        .sign-up .input-group input:focus ~ label,
        .sign-up .input-group input:valid ~ label {
            color: var(--white);
        }

        .btn {
            position: relative;
            display: inline-block;
            padding: 12px 30px;
            background: var(--primary-color);
            color: var(--white);
            border: none;
            border-radius: 5px;
            font-size: 16px;
            cursor: pointer;
            transition: 0.3s;
            width: 100%;
            margin-top: 10px;
            font-weight: 500;
        }

        .sign-up .btn {
            background: var(--white);
            color: var(--primary-color);
        }

        .btn:hover {
            background: var(--primary-color-dark);
        }

        .sign-up .btn:hover {
            background: #f0f0f0;
        }

        .link {
            margin-top: 20px;
            font-size: 14px;
            text-align: center;
        }

        .link a {
            color: var(--primary-color);
            text-decoration: none;
            cursor: pointer;
            font-weight: 500;
        }

        .sign-up .link a {
            color: var(--white);
            text-decoration: underline;
        }

        .error-message {
            color: #ff3860;
            font-size: 14px;
            margin-bottom: 15px;
            padding: 10px;
            background: #ffebee;
            border-radius: 5px;
            display: none;
        }

        .sign-up .error-message {
            background: rgba(255, 0, 0, 0.1);
        }

        .success-message {
            color: #23d160;
            font-size: 14px;
            margin-bottom: 15px;
            padding: 10px;
            background: #effaf3;
            border-radius: 5px;
            display: none;
        }

        .toggle-container {
            position: absolute;
            top: 0;
            left: 50%;
            width: 50%;
            height: 100%;
            overflow: hidden;
            transition: 0.5s;
            z-index: 10;
        }

        .toggle {
            position: relative;
            width: 200%;
            height: 100%;
            left: -100%;
            transition: 0.5s;
        }

        .toggle-panel {
            position: absolute;
            width: 50%;
            height: 100%;
            padding: 40px;
            display: flex;
            flex-direction: column;
            justify-content: center;
            align-items: center;
            text-align: center;
        }

        .toggle-left {
            left: 0;
            background: var(--primary-color);
            color: var(--white);
        }

        .toggle-right {
            right: 0;
            background: var(--primary-color-dark);
            color: var(--white);
        }

        .toggle-btn {
            padding: 12px 30px;
            background: transparent;
            color: var(--white);
            border: 2px solid var(--white);
            border-radius: 5px;
            font-size: 16px;
            cursor: pointer;
            transition: 0.3s;
            margin-top: 20px;
            font-weight: 500;
        }

        .toggle-btn:hover {
            background: rgba(255, 255, 255, 0.2);
        }

        .terms-container {
            margin: 15px 0;
            font-size: 12px;
            display: flex;
            align-items: center;
            color: var(--white);
        }

        .terms-container input {
            margin-right: 10px;
        }

        .terms-container label a {
            color: var(--white);
            text-decoration: underline;
        }

        /* Efeito ativo */
        .container.active .sign-in {
            transform: translateX(100%);
            opacity: 0;
        }

        .container.active .sign-up {
            transform: translateX(-100%);
            opacity: 1;
            z-index: 5;
        }

        .container.active .toggle-container {
            left: 0;
        }

        .container.active .toggle {
            left: 0;
        }

        @media (max-width: 768px) {
            .container {
                width: 90%;
                height: auto;
                min-height: 500px;
            }

            .form-section {
                width: 100%;
                padding: 30px;
            }

            .toggle-container {
                display: none;
            }

            .container.active .sign-in {
                transform: translateX(100%);
                opacity: 0;
            }

            .container.active .sign-up {
                transform: translateX(0);
                opacity: 1;
            }
        }
    </style>
</head>
<body>
    <div class="container" id="container">
        <div class="form-container">
            <!-- Formulário de Login -->
            <div class="form-section sign-in">
                <form method="POST" action="login.php" id="loginForm">
                    <h2 class="title">Bem-vindo</h2>
                    <div id="loginError" class="error-message" style="display:none;"></div>
                    <div class="input-group">
                        <input type="email" id="loginEmail" name="email" required>
                        <label>Email</label>
                    </div>
                    <div class="input-group">
                        <input type="password" id="loginPassword" name="senha" required>
                        <label>Senha</label>
                    </div>
                    <a href="recuperar_senha.php" class="link">Esqueceu sua senha?</a>
                    <input type="hidden" name="csrf_token" value="<?php echo $csrf_token; ?>">
                    <button type="submit" class="btn">Entrar</button>
                    <div class="link">
                        Não tem conta? <a onclick="switchForm()">Criar conta</a>
                    </div>
                </form>
            </div>

            <!-- Formulário de Registro -->
            <div class="form-section sign-up">
                <form method="POST" action="registar.php" id="registerForm">
                    <h2 class="title">Criar Conta</h2>
                    <div id="registerError" class="error-message" style="display:none;"></div>
                    <div id="registerSuccess" class="success-message" style="display:none;"></div>
                    <div class="input-group">
                        <input type="text" id="registerName" name="nome" required>
                        <label>Nome Completo</label>
                    </div>
                    <div class="input-group">
                        <input type="email" id="registerEmail" name="email" required>
                        <label>Email</label>
                    </div>
                    <div class="input-group">
                        <input type="password" id="registerPassword" name="password" required 
                               pattern="(?=.*\d)(?=.*[a-z])(?=.*[A-Z]).{8,}" 
                               title="Deve conter pelo menos 8 caracteres, incluindo uma maiúscula, uma minúscula e um número">
                        <label>Senha</label>
                    </div>
                    <div class="input-group">
                        <input type="tel" id="registerPhone" name="telefone" required>
                        <label>Telefone</label>
                    </div>
                    <div class="input-group">
                        <input type="text" id="registerDocument" name="documento" required>
                        <label>Documento de Identificação</label>
                    </div>
                    <div class="terms-container">
                        <input type="checkbox" id="acceptTerms" name="aceitar_termos" required>
                        <label for="acceptTerms">Aceito os <a href="termos.html" target="_blank">termos de uso</a> e a <a href="privacidade.html" target="_blank">política de privacidade</a></label>
                    </div>
                    <input type="hidden" name="csrf_token" value="<?php echo $csrf_token; ?>">
                    <button type="submit" class="btn">Registrar</button>
                    <div class="link">
                        Já tem conta? <a onclick="switchForm()">Fazer Login</a>
                    </div>
                </form>
            </div>
        </div>

        <!-- Painel de Alternância -->
        <div class="toggle-container">
            <div class="toggle">
                <div class="toggle-panel toggle-left">
                    <h1>Bem-vindo de Volta!</h1>
                    <p>Entre com seus dados para acessar sua conta</p>
                    <button class="toggle-btn" id="loginToggle">Entrar</button>
                </div>
                <div class="toggle-panel toggle-right">
                    <h1>Olá, Visitante!</h1>
                    <p>Registre-se para começar sua jornada conosco</p>
                    <button class="toggle-btn" id="registerToggle">Registrar</button>
                </div>
            </div>
        </div>
    </div>

    <script>
        // Função para alternar entre login e registro
        function switchForm() {
            const container = document.getElementById('container');
            container.classList.toggle('active');
        }

        // Event listeners para os botões de toggle
        document.getElementById('registerToggle').addEventListener('click', switchForm);
        document.getElementById('loginToggle').addEventListener('click', switchForm);

        // Validação e envio do formulário de login
        document.getElementById('loginForm').addEventListener('submit', function(e) {
            e.preventDefault();
            
            const email = document.getElementById('loginEmail').value;
            const password = document.getElementById('loginPassword').value;
            const csrfToken = this.querySelector('input[name="csrf_token"]').value;
            const errorElement = document.getElementById('loginError');
            
            hideError(errorElement);
            
            if (!validateEmail(email)) {
                showError(errorElement, 'Por favor, insira um email válido.');
                return;
            }
            
            if (password.length < 8) {
                showError(errorElement, 'A senha deve ter pelo menos 8 caracteres.');
                return;
            }
            
            const formData = new FormData();
            formData.append('email', email);
            formData.append('senha', password);
            formData.append('csrf_token', csrfToken);
            
            fetch('login.php', {
                method: 'POST',
                body: formData
            })
            .then(res => res.json())
            .then(data => {
                if (data.redirect) {
                    window.location.href = data.redirect;
                } else if (data.error) {
                    showError(errorElement, data.error);
                } else {
                    showError(errorElement, 'Erro inesperado.');
                }
            })
            .catch(() => {
                showError(errorElement, 'Erro ao conectar com o servidor.');
            });
        });

        // Validação e envio do formulário de registro
        document.getElementById('registerForm').addEventListener('submit', function(e) {
            e.preventDefault();
            
            const name = document.getElementById('registerName').value;
            const email = document.getElementById('registerEmail').value;
            const password = document.getElementById('registerPassword').value;
            const phone = document.getElementById('registerPhone').value;
            const documentId = document.getElementById('registerDocument').value;
            const acceptTerms = document.getElementById('acceptTerms').checked;
            const csrfToken = this.querySelector('input[name="csrf_token"]').value;
            const errorElement = document.getElementById('registerError');
            const successElement = document.getElementById('registerSuccess');
            
            hideError(errorElement);
            hideError(successElement);
            
            if (!validateEmail(email)) {
                showError(errorElement, 'Por favor, insira um email válido.');
                return;
            }
            
            if (password.length < 8 || !/(?=.*[A-Z])(?=.*[0-9])/.test(password)) {
                showError(errorElement, 'A senha deve ter pelo menos 8 caracteres, incluindo uma letra maiúscula e um número.');
                return;
            }
            
            if (!acceptTerms) {
                showError(errorElement, 'Você deve aceitar os termos de uso e política de privacidade.');
                return;
            }
            
            const formData = new FormData();
            formData.append('nome', name);
            formData.append('email', email);
            formData.append('password', password);
            formData.append('telefone', phone);
            formData.append('documento', documentId);
            formData.append('aceitar_termos', 'on');
            formData.append('csrf_token', csrfToken);
            
            fetch('registar.php', {
                method: 'POST',
                body: formData
            })
            .then(res => res.json())
            .then(data => {
                if (data.success) {
                    showError(successElement, data.success);
                    this.reset();
                } else if (data.error) {
                    showError(errorElement, data.error);
                } else {
                    showError(errorElement, 'Erro inesperado.');
                }
            })
            .catch(() => {
                showError(errorElement, 'Erro ao conectar com o servidor.');
            });
        });

        // Funções auxiliares
        function validateEmail(email) {
            const re = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
            return re.test(String(email).toLowerCase());
        }

        function showError(element, message) {
            if (element) {
                element.textContent = message;
                element.style.display = 'block';
            }
        }

        function hideError(element) {
            if (element) {
                element.textContent = '';
                element.style.display = 'none';
            }
        }

        // Efeito de label flutuante
        document.querySelectorAll('.input-group input').forEach(input => {
            input.addEventListener('input', () => {
                const label = input.previousElementSibling;
                if (input.value.trim() !== '') {
                    label.style.top = '-12px';
                    label.style.fontSize = '12px';
                    label.style.color = input.parentElement.parentElement.classList.contains('sign-up') ? 'var(--white)' : 'var(--primary-color)';
                } else {
                    label.style.top = '10px';
                    label.style.fontSize = '16px';
                    label.style.color = input.parentElement.parentElement.classList.contains('sign-up') ? 'rgba(255,255,255,0.7)' : 'var(--text-light)';
                }
            });
        });
    </script>
</body>
</html>