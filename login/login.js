document.addEventListener('DOMContentLoaded', function() {
    console.log('DOM carregado completamente');
    
    // Funções auxiliares
    const validateEmail = (email) => {
        const re = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
        return re.test(String(email).toLowerCase());
    };

    const showError = (element, message) => {
        if (element) {
            element.textContent = message;
            element.style.display = 'block';
        }
    };

    const hideError = (element) => {
        if (element) {
            element.textContent = '';
            element.style.display = 'none';
        }
    };

    // Toggle entre login e registro
    const container = document.getElementById('container');
    const registerToggle = document.getElementById('registerToggle');
    const loginToggle = document.getElementById('loginToggle');

    const switchForm = () => {
        if (container) container.classList.toggle('active');
    };

    if (registerToggle) registerToggle.addEventListener('click', switchForm);
    if (loginToggle) loginToggle.addEventListener('click', switchForm);

    // Manipulação do formulário de login
    const loginForm = document.getElementById('loginForm');
    if (loginForm) {
        loginForm.addEventListener('submit', async function(e) {
            e.preventDefault();
            
            const email = document.getElementById('loginEmail').value;
            const password = document.getElementById('loginPassword').value;
            const csrfToken = loginForm.querySelector('input[name="csrf_token"]').value;
            const errorElement = document.getElementById('loginError');
            
            hideError(errorElement);
            
            // Validações básicas
            if (!validateEmail(email)) {
                showError(errorElement, 'Por favor, insira um email válido.');
                return;
            }
            
            if (password.length < 8) {
                showError(errorElement, 'A senha deve ter pelo menos 8 caracteres.');
                return;
            }
            
            try {
                const formData = new FormData();
                formData.append('email', email);
                formData.append('senha', password);
                formData.append('csrf_token', csrfToken);
                
                const response = await fetch('login.php', {
                    method: 'POST',
                    body: formData,
                    headers: {
                        'Accept': 'application/json'
                    }
                });

                if (!response.ok) {
                    throw new Error(`Erro HTTP: ${response.status}`);
                }

                const data = await response.json();
                
                if (data.redirect) {
                    window.location.href = data.redirect;
                } else if (data.error) {
                    showError(errorElement, data.error);
                } else {
                    showError(errorElement, 'Resposta inesperada do servidor');
                }
            } catch (error) {
                console.error('Erro no login:', error);
                showError(errorElement, error.message || 'Erro ao conectar com o servidor');
            }
        });
    }

    // Manipulação do formulário de registro (opcional)
    const registerForm = document.getElementById('registerForm');
    if (registerForm) {
        registerForm.addEventListener('submit', async function(e) {
            e.preventDefault();
            
            const name = document.getElementById('registerName').value;
            const email = document.getElementById('registerEmail').value;
            const password = document.getElementById('registerPassword').value;
            const phone = document.getElementById('registerPhone').value;
            const documentId = document.getElementById('registerDocument').value;
            const acceptTerms = document.getElementById('acceptTerms').checked;
            const csrfToken = registerForm.querySelector('input[name="csrf_token"]').value;
            const errorElement = document.getElementById('registerError');
            const successElement = document.getElementById('registerSuccess');
            
            hideError(errorElement);
            hideError(successElement);
            
            // Validações
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
            
            try {
                const formData = new FormData();
                formData.append('nome', name);
                formData.append('email', email);
                formData.append('password', password);
                formData.append('telefone', phone);
                formData.append('documento', documentId);
                formData.append('aceitar_termos', 'on');
                formData.append('csrf_token', csrfToken);
                
                const response = await fetch('registar.php', {
                    method: 'POST',
                    body: formData,
                    headers: {
                        'Accept': 'application/json'
                    }
                });

                if (!response.ok) {
                    throw new Error(`Erro HTTP: ${response.status}`);
                }

                const data = await response.json();
                
                if (data.success) {
                    showError(successElement, data.success);
                    registerForm.reset();
                } else if (data.error) {
                    showError(errorElement, data.error);
                } else {
                    showError(errorElement, 'Resposta inesperada do servidor');
                }
            } catch (error) {
                console.error('Erro no registro:', error);
                showError(errorElement, error.message || 'Erro ao conectar com o servidor');
            }
        });
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
});