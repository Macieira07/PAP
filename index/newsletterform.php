<?php session_start(); ?>
<!DOCTYPE html>
<html lang="pt">
<head>
    <meta charset="UTF-8">
    <title>Newsletter - Quinta Flores</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            padding: 30px;
        }
        form {
            margin-top: 20px;
        }
        input[type="email"] {
            padding: 10px;
            width: 300px;
            font-size: 16px;
        }
        button {
            padding: 10px 20px;
            font-size: 16px;
        }
        .toast {
            position: fixed;
            top: 20px;
            right: 20px;
            padding: 15px 20px;
            border-radius: 5px;
            color: white;
            font-weight: bold;
            box-shadow: 0 0 10px rgba(0,0,0,0.2);
            display: none;
            z-index: 9999;
        }
        .toast.sucesso { background-color: #28a745; }
        .toast.erro    { background-color: #dc3545; }
        .toast.hide {
            opacity: 0;
            transition: opacity 0.5s ease;
        }
    </style>
</head>
<body>

<h2>Subscreve a nossa newsletter</h2>
<form id="newsletterForm">
    <input type="email" name="email" placeholder="O teu email..." required>
    <button type="submit">Subscrever</button>
</form>

<div id="toast" class="toast"></div>

<script>
const form = document.getElementById('newsletterForm');
const toast = document.getElementById('toast');

form.addEventListener('submit', function(e) {
    e.preventDefault(); // evita reload

    const formData = new FormData(form);

    fetch('newsletter_submeter.php', {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        toast.textContent = data.mensagem;
        toast.className = 'toast ' + (data.tipo === 'sucesso' ? 'sucesso' : 'erro');
        toast.style.display = 'block';

        setTimeout(() => {
            toast.classList.add('hide');
        }, 3500);

        setTimeout(() => {
            toast.style.display = 'none';
            toast.classList.remove('hide');
        }, 4000);

        if (data.tipo === 'sucesso') {
            form.reset();
        }
    })
    .catch(() => {
        toast.textContent = "Erro no servidor.";
        toast.className = 'toast erro';
        toast.style.display = 'block';
    });
});
</script>

</body>
</html>
