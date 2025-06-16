document.addEventListener('DOMContentLoaded', () => {
    // pegar os elementos
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

    // Funções auxiliares
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

    // Validação e envio do login
    if (loginForm) {
        loginForm.addEventListener('submit', function(e) {
            e.preventDefault();

            const emailElem = document.getElementById('loginEmail');
            const passwordElem = document.getElementById('loginPassword');
            const errorElement = document.getElementById('loginError');
            const csrfTokenElem = this.querySelector('input[name="csrf_token"]');

            hideMessage(errorElement);

            if (!emailElem || !passwordElem || !csrfTokenElem) {
                showMessage(errorElement, 'Erro interno: elementos do formulário não encontrados.');
                return;
            }

            const email = emailElem.value;
            const password = passwordElem.value;
            const csrfToken = csrfTokenElem.value;

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
            .then(res => res.json().catch(() => { throw new Error('Erro ao processar resposta do servidor'); }))
            .then(data => {
                if (data.redirect) {
                    window.location.href = data.redirect;
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
    }

    // Validação e envio do registro
    if (registerForm) {
        registerForm.addEventListener('submit', function(e) {
            e.preventDefault();

            const nameElem = document.getElementById('registerName');
            const emailElem = document.getElementById('registerEmail');
            const passwordElem = document.getElementById('registerPassword');
            const phoneElem = document.getElementById('registerPhone');  // Atenção: este input não existe no teu HTML
            const documentIdElem = document.getElementById('registerDocument');
            const acceptTermsElem = document.getElementById('acceptTerms');
            const csrfTokenElem = this.querySelector('input[name="csrf_token"]');
            const errorElement = document.getElementById('registerError');
            const successElement = document.getElementById('registerSuccess');

            hideMessage(errorElement);
            hideMessage(successElement);

            // O campo registerPhone não existe no teu HTML (repara nisso!)
            // Para evitar erro:
            const phoneValue = phoneElem ? phoneElem.value : '';

            if (!nameElem || !emailElem || !passwordElem || !documentIdElem || !acceptTermsElem || !csrfTokenElem) {
                showMessage(errorElement, 'Erro interno: elementos do formulário não encontrados.');
                return;
            }

            const name = nameElem.value;
            const email = emailElem.value;
            const password = passwordElem.value;
            const documentId = documentIdElem.value;
            const acceptTerms = acceptTermsElem.checked;
            const csrfToken = csrfTokenElem.value;

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
    }
});
