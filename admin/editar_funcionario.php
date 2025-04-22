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
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/animate.css/4.1.1/animate.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <style>
        :root {
            --purple-light: #a78bfa;
            --purple: #7c3aed;
            --purple-dark: #6d28d9;
            --white: #ffffff;
            --gray-50: #fafafa;
            --gray-100: #f4f4f5;
            --gray-200: #e4e4e7;
            --gray-800: #27272a;
            --transition: all 0.3s ease;
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: 'Segoe UI', system-ui, sans-serif;
        }

        body {
            background-color: var(--gray-50);
            color: var(--gray-800);
            display: flex;
            min-height: 100vh;
        }

        .sidebar {
            width: 240px;
            background-color: var(--white);
            box-shadow: 0 1px 3px rgba(0,0,0,0.1);
            padding: 1.5rem 0;
            transition: var(--transition);
            z-index: 10;
        }

        .sidebar-header {
            display: flex;
            align-items: center;
            padding: 0 1.5rem 1.5rem;
            border-bottom: 1px solid var(--gray-100);
        }

        .logo-icon {
            font-size: 1.5rem;
            margin-right: 0.75rem;
        }

        .sidebar-header h2 {
            font-size: 1.25rem;
            font-weight: 600;
            color: var(--purple);
        }

        .sidebar-nav ul {
            list-style: none;
            padding: 1rem 0;
        }

        .sidebar-nav a {
            display: flex;
            align-items: center;
            padding: 0.75rem 1.5rem;
            color: var(--gray-800);
            text-decoration: none;
            transition: var(--transition);
            font-weight: 500;
        }

        .sidebar-nav a:hover, .sidebar-nav a.active {
            background-color: var(--gray-100);
            color: var(--purple);
        }

        .sidebar-nav .icon {
            margin-right: 0.75rem;
        }

        .logout {
            margin-top: 1.5rem;
        }

        .main {
            flex: 1;
            padding: 2rem;
        }

        .page-title {
            font-size: 1.75rem;
            margin-bottom: 1.5rem;
            color: var(--purple);
            font-weight: 600;
        }

        .form-container {
            background-color: var(--white);
            padding: 2rem;
            border-radius: 0.5rem;
            box-shadow: 0 1px 3px rgba(0,0,0,0.1);
            margin-bottom: 2rem;
            max-width: 800px;
        }

        .form-group {
            margin-bottom: 1.5rem;
        }

        .form-label {
            display: block;
            margin-bottom: 0.5rem;
            font-weight: 500;
            color: var(--gray-800);
        }

        .form-control {
            width: 100%;
            padding: 0.75rem;
            border: 1px solid var(--gray-200);
            border-radius: 0.5rem;
            background-color: var(--white);
            color: var(--gray-800);
            transition: var(--transition);
        }

        .form-control:focus {
            outline: none;
            border-color: var(--purple-light);
            box-shadow: 0 0 0 3px rgba(167, 139, 250, 0.2);
        }

        .button-group {
            display: flex;
            gap: 1rem;
            margin-top: 1.5rem;
        }

        .btn {
            padding: 0.75rem 1.5rem;
            border-radius: 0.5rem;
            font-weight: 500;
            cursor: pointer;
            transition: var(--transition);
            border: none;
            display: inline-flex;
            align-items: center;
            justify-content: center;
        }

        .btn-primary {
            background-color: var(--purple);
            color: var(--white);
        }

        .btn-primary:hover {
            background-color: var(--purple-dark);
            transform: translateY(-2px);
            box-shadow: 0 4px 6px rgba(124, 58, 237, 0.1);
        }

        .btn-secondary {
            background-color: var(--white);
            color: var(--gray-800);
            border: 1px solid var(--gray-200);
        }

        .btn-secondary:hover {
            background-color: var(--gray-100);
            transform: translateY(-2px);
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.05);
        }

        .menu-toggle {
            position: fixed;
            top: 1rem;
            left: 1rem;
            display: none;
            background: var(--white);
            border: none;
            width: 40px;
            height: 40px;
            border-radius: 50%;
            cursor: pointer;
            box-shadow: 0 1px 3px rgba(0,0,0,0.1);
            z-index: 20;
        }

        .menu-toggle span {
            display: block;
            width: 20px;
            height: 2px;
            background: var(--purple);
            margin: 4px auto;
            transition: var(--transition);
        }

        @media (max-width: 768px) {
            .menu-toggle {
                display: block;
            }
            
            .sidebar {
                position: fixed;
                left: -240px;
                height: 100vh;
            }
            
            body.sidebar-open .sidebar {
                left: 0;
            }
            
            .main {
                margin-left: 0;
                padding: 1rem;
            }

            .button-group {
                flex-direction: column;
            }
            
            .btn {
                width: 100%;
            }
        }
    </style>
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