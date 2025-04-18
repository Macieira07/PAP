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
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/animate.css/4.1.1/animate.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <style>
        :root {
            --primary: #4e54c8;
            --primary-light: #8f94fb;
            --secondary: #ffd700;
            --dark: #111827;
            --dark-light: #1f2937;
            --light: #f8f9fa;
            --text: #e0e0e0;
            --text-light: #ffffff;
            --text-dark: #9e9e9e;
            --success: #10b981;
            --info: #0ea5e9;
            --warning: #f59e0b;
            --danger: #ef4444;
            --sidebar-width: 300px;
            --transition: all 0.4s cubic-bezier(0.16, 1, 0.3, 1);
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Montserrat', sans-serif;
            background: linear-gradient(135deg, var(--dark), var(--dark-light));
            background-attachment: fixed;
            color: var(--text);
            min-height: 100vh;
            overflow-x: hidden;
            position: relative;
        }

        body::before {
            content: "";
            position: absolute;
            width: 100%;
            height: 100%;
            top: 0;
            left: 0;
            background: 
                radial-gradient(circle at 10% 20%, rgba(78, 84, 200, 0.1) 0%, transparent 20%),
                radial-gradient(circle at 90% 50%, rgba(255, 215, 0, 0.05) 0%, transparent 25%),
                radial-gradient(circle at 50% 90%, rgba(78, 84, 200, 0.08) 0%, transparent 30%);
            z-index: -1;
        }

        /* Sidebar Estilizada */
        .sidebar {
            width: var(--sidebar-width);
            height: 100vh;
            background: linear-gradient(180deg, rgba(31, 41, 55, 0.95), rgba(17, 24, 39, 0.98));
            backdrop-filter: blur(20px);
            position: fixed;
            left: 0;
            top: 0;
            display: flex;
            flex-direction: column;
            z-index: 100;
            box-shadow: 5px 0 25px rgba(0, 0, 0, 0.3);
            transform: translateX(-100%);
            animation: slideInLeft 0.7s forwards 0.2s;
            border-right: 1px solid rgba(255, 215, 0, 0.1);
            overflow: hidden;
        }

        .sidebar::after {
            content: '';
            position: absolute;
            top: 0;
            right: 0;
            width: 3px;
            height: 100%;
            background: linear-gradient(180deg, transparent, var(--primary-light), transparent);
            opacity: 0.3;
        }

        .sidebar-header {
            padding: 35px 25px;
            border-bottom: 1px solid rgba(255, 255, 255, 0.05);
            display: flex;
            align-items: center;
            gap: 18px;
            position: relative;
        }

        .sidebar-header::before {
            content: '';
            position: absolute;
            bottom: 0;
            left: 0;
            width: 100%;
            height: 1px;
            background: linear-gradient(90deg, transparent, rgba(255, 215, 0, 0.2), transparent);
        }

        .sidebar-header h2 {
            color: var(--secondary);
            font-size: 1.6rem;
            font-weight: 600;
            margin: 0;
            background: linear-gradient(to right, var(--secondary), #ffe55c);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            animation: fadeIn 1s ease-in-out;
            letter-spacing: 0.5px;
        }

        .logo-icon {
            font-size: 2rem;
            animation: pulse 2s infinite;
            text-shadow: 0 0 15px rgba(255, 215, 0, 0.5);
            transform-origin: center;
        }

        .sidebar-nav {
            padding: 30px 20px;
            flex-grow: 1;
            overflow-y: auto;
        }

        .sidebar-nav::-webkit-scrollbar {
            width: 5px;
        }

        .sidebar-nav::-webkit-scrollbar-thumb {
            background: rgba(255, 215, 0, 0.2);
            border-radius: 10px;
        }

        .sidebar ul {
            list-style: none;
        }

        .sidebar li {
            margin-bottom: 10px;
            position: relative;
            overflow: hidden;
        }

        .sidebar li::before {
            content: '';
            position: absolute;
            left: 0;
            bottom: 0;
            width: 0;
            height: 1px;
            background: linear-gradient(90deg, transparent, var(--secondary), transparent);
            transition: var(--transition);
            z-index: 0;
        }

        .sidebar li:hover::before {
            width: 100%;
        }

        .sidebar a {
            text-decoration: none;
            color: var(--text);
            display: flex;
            align-items: center;
            gap: 15px;
            padding: 14px 18px;
            border-radius: 12px;
            transition: var(--transition);
            position: relative;
            z-index: 1;
            overflow: hidden;
        }

        .sidebar a::before {
            content: '';
            position: absolute;
            left: 0;
            top: 0;
            width: 100%;
            height: 100%;
            background: linear-gradient(90deg, rgba(78, 84, 200, 0.1), rgba(255, 215, 0, 0.05));
            opacity: 0;
            transition: var(--transition);
            z-index: -1;
            transform: translateX(-100%);
        }

        .sidebar a:hover::before {
            opacity: 1;
            transform: translateX(0);
        }

        .sidebar a:hover {
            color: var(--secondary);
            transform: translateX(8px);
        }

        .sidebar a.active {
            background: rgba(255, 215, 0, 0.12);
            color: var(--secondary);
            font-weight: 500;
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.1);
        }

        .sidebar a.active::after {
            content: '';
            position: absolute;
            right: 15px;
            width: 8px;
            height: 8px;
            background-color: var(--secondary);
            border-radius: 50%;
            box-shadow: 0 0 15px var(--secondary);
            animation: pulse 2s infinite;
        }

        .icon {
            font-size: 1.3rem;
            min-width: 30px;
            text-align: center;
            display: flex;
            justify-content: center;
            align-items: center;
            padding: 8px;
            border-radius: 10px;
            background: rgba(255, 255, 255, 0.05);
            transition: var(--transition);
        }

        .sidebar a:hover .icon {
            background: rgba(255, 215, 0, 0.15);
            transform: rotate(5deg);
        }

        .sidebar a.active .icon {
            background: rgba(255, 215, 0, 0.2);
            box-shadow: 0 0 10px rgba(255, 215, 0, 0.2);
        }

        .logout {
            margin-top: auto;
            margin-bottom: 20px;
            padding-top: 20px;
            border-top: 1px solid rgba(255, 255, 255, 0.05);
        }

        .logout a {
            color: #ff6b6b !important;
            border: 1px solid rgba(255, 107, 107, 0.1);
        }

        .logout a:hover {
            color: #ff4757 !important;
            border-color: rgba(255, 107, 107, 0.3);
            background: rgba(255, 107, 107, 0.05);
        }

        /* Conteúdo Principal */
        .main {
            margin-left: 0;
            padding: 40px;
            transition: var(--transition);
            min-height: 100vh;
        }

        .sidebar-open .main {
            margin-left: var(--sidebar-width);
        }

        /* Botão do Menu */
        .menu-toggle {
            position: fixed;
            top: 25px;
            left: 25px;
            width: 50px;
            height: 50px;
            background: rgba(17, 24, 39, 0.8);
            border: 1px solid rgba(78, 84, 200, 0.3);
            border-radius: 12px;
            display: flex;
            flex-direction: column;
            justify-content: center;
            align-items: center;
            gap: 6px;
            cursor: pointer;
            z-index: 999;
            transition: var(--transition);
            backdrop-filter: blur(10px);
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.2);
        }

        .menu-toggle:hover {
            background: rgba(78, 84, 200, 0.1);
            border-color: var(--primary-light);
            transform: scale(1.05);
        }

        .sidebar-open .menu-toggle {
            left: calc(var(--sidebar-width) + 25px);
        }

        .menu-toggle span {
            display: block;
            width: 24px;
            height: 2px;
            background-color: var(--primary-light);
            transition: var(--transition);
            border-radius: 2px;
        }

        .menu-toggle:hover span {
            background-color: var(--secondary);
        }

        .sidebar-open .menu-toggle span:nth-child(1) {
            transform: translateY(8px) rotate(45deg);
        }

        .sidebar-open .menu-toggle span:nth-child(2) {
            opacity: 0;
            transform: translateX(-10px);
        }

        .sidebar-open .menu-toggle span:nth-child(3) {
            transform: translateY(-8px) rotate(-45deg);
        }

        /* Animações personalizadas */
        @keyframes fadeIn {
            from { opacity: 0; }
            to { opacity: 1; }
        }

        @keyframes fadeInUp {
            from { 
                opacity: 0;
                transform: translateY(30px);
            }
            to { 
                opacity: 1;
                transform: translateY(0);
            }
        }

        @keyframes slideInLeft {
            from { transform: translateX(-100%); }
            to { transform: translateX(0); }
        }

        @keyframes pulse {
            0% { transform: scale(1); opacity: 1; }
            50% { transform: scale(1.15); opacity: 0.8; }
            100% { transform: scale(1); opacity: 1; }
        }

        /* Estilos para o formulário */
        .page-title {
            color: var(--text-light);
            font-size: 2rem;
            margin-bottom: 30px;
            position: relative;
            display: inline-block;
            animation: fadeInUp 0.8s ease-out;
        }
        
        .page-title::after {
            content: '';
            position: absolute;
            bottom: -5px;
            left: 0;
            width: 50%;
            height: 3px;
            background: linear-gradient(90deg, var(--primary), transparent);
            border-radius: 3px;
        }

        .form-container {
            background: linear-gradient(135deg, rgba(31, 41, 55, 0.75), rgba(17, 24, 39, 0.9));
            backdrop-filter: blur(15px);
            border-radius: 20px;
            padding: 30px;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.15);
            border: 1px solid rgba(78, 84, 200, 0.2);
            animation: fadeInUp 0.8s ease-out;
            max-width: 800px;
            position: relative;
            overflow: hidden;
            margin-bottom: 30px;
        }

        .form-container::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: linear-gradient(45deg, transparent 65%, rgba(78, 84, 200, 0.08) 100%);
            z-index: 0;
            pointer-events: none;
        }

        .form-group {
            margin-bottom: 25px;
            position: relative;
        }

        .form-label {
            display: block;
            color: var(--secondary);
            font-weight: 500;
            margin-bottom: 8px;
            font-size: 0.95rem;
            letter-spacing: 0.5px;
            transition: var(--transition);
        }

        .form-control {
            width: 100%;
            padding: 14px;
            background: rgba(17, 24, 39, 0.6);
            border: 1px solid rgba(78, 84, 200, 0.3);
            border-radius: 12px;
            color: var(--text-light);
            font-family: 'Montserrat', sans-serif;
            font-size: 1rem;
            transition: var(--transition);
            box-shadow: 0 3px 10px rgba(0, 0, 0, 0.1);
        }

        .form-control:focus {
            outline: none;
            border-color: var(--primary-light);
            background: rgba(17, 24, 39, 0.8);
            box-shadow: 0 0 0 3px rgba(78, 84, 200, 0.2);
            transform: translateY(-2px);
        }

        .form-control::placeholder {
            color: var(--text-dark);
        }

        .btn {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            padding: 12px 25px;
            border-radius: 12px;
            font-weight: 600;
            cursor: pointer;
            transition: var(--transition);
            border: none;
            font-size: 1rem;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        .btn-primary {
            background: linear-gradient(135deg, var(--primary), var(--primary-light));
            color: white;
            box-shadow: 0 5px 15px rgba(78, 84, 200, 0.3);
        }

        .btn-primary:hover {
            transform: translateY(-3px);
            box-shadow: 0 8px 20px rgba(78, 84, 200, 0.4);
            background: linear-gradient(135deg, var(--primary-light), var(--primary));
        }

        .btn-secondary {
            background: transparent;
            color: var(--text);
            border: 1px solid rgba(255, 255, 255, 0.1);
        }

        .btn-secondary:hover {
            background: rgba(255, 255, 255, 0.05);
            transform: translateY(-3px);
            color: var(--text-light);
            border-color: rgba(255, 255, 255, 0.2);
        }

        .actions {
            display: flex;
            gap: 15px;
            margin-top: 20px;
        }

        .back-link {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            color: var(--text);
            text-decoration: none;
            margin-top: 20px;
            font-weight: 500;
            transition: var(--transition);
            padding: 8px 12px;
            border-radius: 8px;
            background: rgba(255, 255, 255, 0.05);
            border: 1px solid rgba(255, 255, 255, 0.1);
        }

        .back-link:hover {
            color: var(--secondary);
            background: rgba(255, 255, 255, 0.08);
            transform: translateX(-5px);
        }

        /* Responsividade */
        @media (max-width: 1200px) {
            :root {
                --sidebar-width: 280px;
            }
        }

        @media (max-width: 992px) {
            .sidebar {
                transform: translateX(-100%);
                box-shadow: none;
            }
            
            .sidebar-open .sidebar {
                transform: translateX(0);
                box-shadow: 5px 0 25px rgba(0, 0, 0, 0.3);
            }
            
            .main {
                margin-left: 0;
                padding: 30px;
            }
            
            .sidebar-open .main {
                filter: blur(3px);
                pointer-events: none;
            }
            
            .page-title {
                font-size: 2.2rem;
            }
        }

        @media (max-width: 768px) {
            .main {
                padding: 20px;
                padding-top: 80px;
            }
            
            .page-title {
                font-size: 1.8rem;
            }
            
            .form-container {
                padding: 20px;
            }
            
            .actions {
                flex-direction: column;
            }
            
            .btn {
                width: 100%;
                justify-content: center;
            }
        }

        @media (max-width: 480px) {
            .page-title {
                font-size: 1.5rem;
            }
            
            .form-control {
                padding: 12px;
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