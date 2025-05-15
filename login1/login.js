        // Alternar entre formulários
        const loginForm = document.getElementById('loginForm');
        const registerForm = document.getElementById('registerForm');
        const headerTitle = document.getElementById('headerTitle');
        const headerSubtitle = document.getElementById('headerSubtitle');
        
        document.getElementById('showRegister').addEventListener('click', function(e) {
            e.preventDefault();
            loginForm.classList.remove('active');
            registerForm.classList.add('active');
            headerTitle.textContent = 'Criar Conta';
            headerSubtitle.textContent = 'Preencha os dados para começar';
        });
        
        document.getElementById('showLogin').addEventListener('click', function(e) {
            e.preventDefault();
            registerForm.classList.remove('active');
            loginForm.classList.add('active');
            headerTitle.textContent = 'Entrar';
            headerSubtitle.textContent = 'Acesse sua conta para continuar';
        });
        
        // Funções auxiliares para validação
        function validateEmail(email) {
            const re = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
            return re.test(String(email).toLowerCase());
        }

        function showMessage(element, message) {
            if (element) {
                element.textContent = message;
                element.style.display = 'block';
            }
        }

        function hideMessage(element) {
            if (element) {
                element.textContent = '';
                element.style.display = 'none';
            }
        }
        
        // Validação e envio do formulário de login
        document.getElementById('loginForm').addEventListener('submit', function(e) {
            e.preventDefault();
            
            const email = document.getElementById('loginEmail').value;
            const password = document.getElementById('loginPassword').value;
            const csrfToken = this.querySelector('input[name="csrf_token"]').value;
            const errorElement = document.getElementById('loginError');
            
            hideMessage(errorElement);
            
            if (!validateEmail(email)) {
                showMessage(errorElement, 'Por favor, insira um email válido.');
                return;
            }
            
            if (password.length < 8) {
                showMessage(errorElement, 'A senha deve ter pelo menos 8 caracteres.');
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
            .then(res => res.json().catch(error => {
                throw new Error('Erro ao processar resposta do servidor');
            }))
            .then(data => {
                if (data.redirect) {
                    window.location.href = data.redirect;
                } else if (data.error) {
                    showMessage(errorElement, data.error);
                } else {
                    showMessage(errorElement, 'Erro inesperado.');
                }
            })
            .catch(error => {
                showMessage(errorElement, 'Erro ao conectar com o servidor.');
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
            
            hideMessage(errorElement);
            hideMessage(successElement);
            
            if (!validateEmail(email)) {
                showMessage(errorElement, 'Por favor, insira um email válido.');
                return;
            }
            
            if (password.length < 8 || !/(?=.*[A-Z])(?=.*[0-9])/.test(password)) {
                showMessage(errorElement, 'A senha deve ter pelo menos 8 caracteres, incluindo uma letra maiúscula e um número.');
                return;
            }
            
            if (!acceptTerms) {
                showMessage(errorElement, 'Você deve aceitar os termos de uso e política de privacidade.');
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
                    showMessage(successElement, data.success);
                    this.reset();
                } else if (data.error) {
                    showMessage(errorElement, data.error);
                } else {
                    showMessage(errorElement, 'Erro inesperado.');
                }
            })
            .catch(() => {
                showMessage(errorElement, 'Erro ao conectar com o servidor.');
            });
        });


            // Quando o formulário for enviado, combine o código do país com o número
    document.getElementById("registerForm").addEventListener("submit", function(event) {
        const countryCode = document.getElementById("countryCode").value;
        const phoneNumber = document.getElementById("registerPhone").value;
        
        // Remove caracteres não numéricos do número de telefone
        const cleanNumber = phoneNumber.replace(/\D/g, '');
        
        // Combina o código do país com o número limpo
        document.getElementById("registerPhone").value = countryCode + cleanNumber;
    });