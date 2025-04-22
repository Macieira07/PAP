document.addEventListener('DOMContentLoaded', function() {
    console.log('DOM carregado completamente');
    
    // Toggle entre login e registro
    const container = document.getElementById('container');
    const registerToggle = document.getElementById('registerToggle');
    const loginToggle = document.getElementById('loginToggle');

    if (registerToggle) registerToggle.addEventListener('click', switchForm);
    if (loginToggle) loginToggle.addEventListener('click', switchForm);

    function switchForm() {
        if (container) container.classList.toggle('active');
    }

    // Efeito de label flutuante - apenas para labels que ainda não foram ajustados
    document.querySelectorAll('.input-group input').forEach(input => {
        // Verifica estado inicial
        if (input.value.trim() !== '') {
            const label = input.previousElementSibling;
            if (label) {
                label.style.top = '-12px';
                label.style.fontSize = '12px';
                label.style.color = input.parentElement.parentElement.classList.contains('sign-up') ? 'var(--white)' : 'var(--primary-color)';
            }
        }
        
        // Adiciona evento para mudanças futuras
        input.addEventListener('input', () => {
            const label = input.previousElementSibling;
            if (!label) return;
            
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