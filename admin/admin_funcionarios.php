<?php
    session_start();
    include('../conexao.php');
    $sql = "SELECT * FROM funcionarios";
    $resultado = $conexao->query($sql);
?>

<!DOCTYPE html>
<html lang="pt-PT">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Funcionários - Painel da Quinta Flores</title>
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

    .add-btn {
        display: inline-block;
        background-color: var(--purple);
        color: var(--white);
        padding: 0.75rem 1.5rem;
        border-radius: 0.5rem;
        text-decoration: none;
        margin-bottom: 1.5rem;
        transition: var(--transition);
        font-weight: 500;
    }

    .add-btn:hover {
        background-color: var(--purple-dark);
        transform: translateY(-2px);
    }

    .add-btn span {
        font-weight: bold;
        margin-right: 0.5rem;
    }

    .data-table {
        width: 100%;
        border-collapse: collapse;
        background-color: var(--white);
        box-shadow: 0 1px 3px rgba(0,0,0,0.1);
        border-radius: 0.5rem;
        overflow: hidden;
    }

    .data-table th, .data-table td {
        padding: 1rem;
        text-align: left;
        border-bottom: 1px solid var(--gray-100);
    }

    .data-table th {
        background-color: var(--purple);
        color: var(--white);
        font-weight: 600;
    }

    .data-table tr:hover {
        background-color: var(--gray-50);
    }

    .action-btn {
        display: inline-block;
        padding: 0.5rem 1rem;
        border-radius: 0.25rem;
        text-decoration: none;
        font-size: 0.875rem;
        font-weight: 500;
        transition: var(--transition);
    }

    .edit-btn {
        background-color: var(--purple-light);
        color: var(--purple-dark);
        margin-right: 0.5rem;
    }

    .edit-btn:hover {
        background-color: var(--purple);
        color: var(--white);
    }

    .delete-btn {
        background-color: #fee2e2;
        color: #dc2626;
    }

    .delete-btn:hover {
        background-color: #dc2626;
        color: var(--white);
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

        .data-table {
            display: block;
            overflow-x: auto;
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
        <h1 class="page-title">Gestão de Funcionários</h1>
        <a href="editar_funcionario.php" class="add-btn">
            <span>+</span> Adicionar Novo Funcionário
        </a>
        
        <table class="data-table">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Nome</th>
                    <th>Email</th>
                    <th>Cargo</th>
                    <th>Telefone</th>
                    <th>Ações</th>
                </tr>
            </thead>
            <tbody>
                <?php while($f = $resultado->fetch_assoc()) { ?>
                <tr>
                    <td><?= $f['F_id_funcionario'] ?></td>
                    <td><?= $f['F_nome'] ?></td>
                    <td><?= $f['F_email'] ?></td>
                    <td><?= $f['F_cargo'] ?></td>
                    <td><?= $f['F_telefone'] ?></td>
                    <td>
                        <a href="editar_funcionario.php?id=<?= $f['F_id_funcionario'] ?>" class="action-btn edit-btn">Editar</a>
                        <a href="excluir_funcionario.php?id=<?= $f['F_id_funcionario'] ?>" class="action-btn delete-btn" onclick="return confirm('Tem certeza que deseja excluir este funcionário?')">Excluir</a>
                    </td>
                </tr>
                <?php } ?>
            </tbody>
        </table>
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
            
            // Efeito de hover nas linhas da tabela
            const tableRows = document.querySelectorAll('.data-table tbody tr');
            tableRows.forEach(row => {
                row.addEventListener('mouseenter', function() {
                    this.style.transform = 'translateX(5px)';
                });
                
                row.addEventListener('mouseleave', function() {
                    this.style.transform = '';
                });
            });
        });
    </script>
</body>
</html>