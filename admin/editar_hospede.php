<?php
session_start();
include('../conexao.php');

$nome = $apelido = $email = $telefone = "";
$id = isset($_GET['id']) ? intval($_GET['id']) : 0;

if ($id > 0) {
    $sql = "SELECT * FROM hospedes WHERE H_id_hospede = $id";
    $resultado = $conexao->query($sql);
    if ($resultado && $resultado->num_rows > 0) {
        $hospede = $resultado->fetch_assoc();
        $nome = $hospede['H_nome'];
        $apelido = $hospede['H_apelido'];
        $email = $hospede['H_email'];
        $telefone = $hospede['H_telefone'];
    }
}
?>

<!DOCTYPE html>
<html lang="pt-PT">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $id > 0 ? "Editar" : "Adicionar" ?> Hóspede - Quinta Flores</title>
    <link rel="stylesheet" href="admin.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/animate.css/4.1.1/animate.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@300;400;500;600;700&display=swap" rel="stylesheet">

</head>
<body>
    <div class="menu-toggle" data-tooltip="Mostrar Menu">
        <span></span>
        <span></span>
        <span></span>
    </div>

    <div class="sidebar">
        <div class="sidebar-header">
            <span class="logo-icon">🌼</span>
            <h2>Quinta Flores</h2>
        </div>
        <nav class="sidebar-nav">
            <ul>
                <li><a href="admin_index.php"><span class="icon">🏠</span> Início</a></li>
                <li><a href="admin_funcionarios.php"><span class="icon">👷</span> Funcionários</a></li>
                <li><a href="admin_hospedes.php" class="active"><span class="icon">🧍</span> Hóspedes</a></li>
                <li><a href="admin_reservas.php"><span class="icon">📅</span> Reservas</a></li>
                <li class="logout"><a href="logout.php"><span class="icon">🚪</span> Sair</a></li>
            </ul>
        </nav>
    </div>

    <div class="main">
        <h1 class="page-title"><?= $id > 0 ? "Editar" : "Adicionar Novo" ?> Hóspede</h1>
        
        <div class="form-container">
            <form method="POST" action="atualizar_hospede.php">
                <input type="hidden" name="id" value="<?= $id ?>">
                
                <div class="form-group">
                    <label for="nome" class="form-label">Nome</label>
                    <input type="text" id="nome" name="nome" class="form-control" value="<?= $nome ?>" required placeholder="Insira o nome">
                </div>
                
                <div class="form-group">
                    <label for="apelido" class="form-label">Apelido</label>
                    <input type="text" id="apelido" name="apelido" class="form-control" value="<?= $apelido ?>" required placeholder="Insira o apelido">
                </div>
                
                <div class="form-group">
                    <label for="email" class="form-label">Email</label>
                    <input type="email" id="email" name="email" class="form-control" value="<?= $email ?>" required placeholder="Insira o email">
                </div>
                
                <div class="form-group">
                    <label for="telefone" class="form-label">Telefone</label>
                    <input type="text" id="telefone" name="telefone" class="form-control" value="<?= $telefone ?>" required placeholder="Insira o telefone">
                </div>
                
                <div class="actions">
                    <button type="submit" class="btn btn-primary">Salvar Hóspede</button>
                    <a href="admin_hospedes.php" class="btn btn-secondary">Cancelar</a>
                </div>
            </form>
        </div>
        
        <a href="admin_hospedes.php" class="back-link">← Voltar para Lista de Hóspedes</a>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const menuToggle = document.querySelector('.menu-toggle');
            const body = document.body;
            
            // Efeito inicial - mostrar sidebar automaticamente
            setTimeout(() => {
                body.classList.toggle('sidebar-open');
                menuToggle.setAttribute('data-tooltip', 'Esconder Menu');
            }, 800);
            
            menuToggle.addEventListener('click', function() {
                body.classList.toggle('sidebar-open');
                
                // Atualizar tooltip do botão
                if (body.classList.contains('sidebar-open')) {
                    menuToggle.setAttribute('data-tooltip', 'Esconder Menu');
                } else {
                    menuToggle.setAttribute('data-tooltip', 'Mostrar Menu');
                }
            });
            
            // Efeito de foco nos campos do formulário
            const formControls = document.querySelectorAll('.form-control');
            formControls.forEach(control => {
                control.addEventListener('focus', function() {
                    const label = this.previousElementSibling;
                    if (label && label.classList.contains('form-label')) {
                        label.style.color = 'var(--primary-light)';
                    }
                });
                
                control.addEventListener('blur', function() {
                    const label = this.previousElementSibling;
                    if (label && label.classList.contains('form-label')) {
                        label.style.color = 'var(--secondary)';
                    }
                });
            });
        });
    </script>
</body>
</html>