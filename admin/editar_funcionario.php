<?php
session_start();
include('../conexao.php');

// Inicializa todas variáveis no início
$id = isset($_GET['id']) ? intval($_GET['id']) : 0;
$nome = $email = $cargo = $telefone = "";

// Só consulta se tiver um ID válido
if ($id > 0) {
    $sql = "SELECT * FROM funcionarios WHERE F_id_funcionario = ?";
    $stmt = $conexao->prepare($sql);
    
    if ($stmt === false) {
        die("Erro na preparação da consulta: " . $conexao->error);
    }
    
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $resultado = $stmt->get_result();
    
    if ($resultado && $resultado->num_rows > 0) {
        $dados = $resultado->fetch_assoc();
        $nome = $dados['F_nome'];
        $email = $dados['F_email'];
        $cargo = $dados['F_cargo'];
        $telefone = $dados['F_telefone'];
    }
    
    $stmt->close();
}
?>

<!DOCTYPE html>
<html lang="pt-PT">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $id > 0 ? "Editar Funcionário" : "Adicionar Funcionário"; ?> - Painel da Quinta Flores</title>
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
                <li><a href="admin_funcionarios.php" class="active"><span class="icon">👷</span> Funcionários</a></li>
                <li><a href="admin_hospedes.php"><span class="icon">🧍</span> Hóspedes</a></li>
                <li><a href="admin_reservas.php"><span class="icon">📅</span> Reservas</a></li>
                <li class="logout"><a href="logout.php"><span class="icon">🚪</span> Sair</a></li>
            </ul>
        </nav>
    </div>

    <div class="main">
        <h1 class="page-title"><?php echo $id > 0 ? "Editar Funcionário" : "Adicionar Funcionário"; ?></h1>
        
        <div class="form-container">
            <form method="POST" action="atualizar_funcionario.php">
                <input type="hidden" name="id" value="<?php echo $id; ?>">
                
                <div class="form-group">
                    <label class="form-label" for="nome">Nome</label>
                    <input type="text" id="nome" name="nome" class="form-control" value="<?php echo $nome; ?>" placeholder="Nome completo" required>
                </div>
                
                <div class="form-group">
                    <label class="form-label" for="email">Email</label>
                    <input type="email" id="email" name="email" class="form-control" value="<?php echo $email; ?>" placeholder="email@exemplo.com" required>
                </div>
                
                <div class="form-group">
                    <label class="form-label" for="cargo">Cargo</label>
                    <input type="text" id="cargo" name="cargo" class="form-control" value="<?php echo $cargo; ?>" placeholder="Cargo ou função">
                </div>
                
                <div class="form-group">
                    <label class="form-label" for="telefone">Telefone</label>
                    <input type="text" id="telefone" name="telefone" class="form-control" value="<?php echo $telefone; ?>" placeholder="+351 999 999 999">
                </div>
                
                <div class="button-group">
                    <button type="submit" class="btn btn-primary">
                        <?php echo $id > 0 ? "Atualizar" : "Guardar"; ?>
                    </button>
                    <a href="admin_funcionarios.php" class="btn btn-secondary">Cancelar</a>
                </div>
            </form>
        </div>
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
            
            // Adicionar efeito de foco aos campos do formulário
            const formControls = document.querySelectorAll('.form-control');
            formControls.forEach(control => {
                control.addEventListener('focus', function() {
                    this.style.transform = 'translateY(-3px)';
                    this.style.boxShadow = '0 4px 6px rgba(124, 58, 237, 0.1)';
                });
                
                control.addEventListener('blur', function() {
                    this.style.transform = '';
                    this.style.boxShadow = '';
                });
            });
        });
    </script>
</body>
</html>