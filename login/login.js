document.addEventListener('DOMContentLoaded', function() {
    console.log('DOM carregado completamente');
    
    // Selecionar elementos usando seletores mais específicos
    const container = document.getElementById('container');
    const registerBtn = document.getElementById('register');
    const loginBtn = document.getElementById('login');
    
    console.log('Elementos encontrados:', {
        container: !!container,
        registerBtn: !!registerBtn,
        loginBtn: !!loginBtn
    });
    
    // Adicionar event listeners para os botões de toggle
    if (registerBtn) {
        console.log('Adicionando event listener ao botão de registro');
        registerBtn.addEventListener('click', function(e) {
            e.preventDefault(); // Prevenir comportamento padrão do botão
            console.log('Botão registrar clicado');
            if (container) {
                container.classList.add('active');
                console.log('Classe active adicionada ao container');
            }
        });
    }
    
    if (loginBtn) {
        console.log('Adicionando event listener ao botão de login');
        loginBtn.addEventListener('click', function(e) {
            e.preventDefault(); // Prevenir comportamento padrão do botão
            console.log('Botão login clicado');
            if (container) {
                container.classList.remove('active');
                console.log('Classe active removida do container');
            }
        });
    }
    
    // Código para manipulação dos formulários
    const loginForm = document.getElementById('loginForm');
    const registerForm = document.getElementById('registerForm');
    
    // Validação e envio do formulário de login
    if (loginForm) {
        loginForm.addEventListener('submit', function(e) {
            e.preventDefault();
            
            const email = document.getElementById('loginEmail').value;
            const password = document.getElementById('loginPassword').value;
            const csrfToken = loginForm.querySelector('input[name="csrf_token"]').value;
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
    }
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