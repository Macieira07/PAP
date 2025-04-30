<?php
// Iniciar sessão
session_start();

// Função para escapar saída
function e($text) {
    return htmlspecialchars($text, ENT_QUOTES, 'UTF-8');
}

// Verificar se o administrador está logado
if (!isset($_SESSION['admin_nome'])) {
    header('Location: ../login1/pagina_login.php');
    exit;
}

// Nome do administrador da sessão
$admin_nome = $_SESSION['admin_nome'];
?>
<!DOCTYPE html>
<html lang="pt-PT">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gerir Funcionários - Quinta Flores</title>
    <link rel="stylesheet" href="admin.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
</head>
<body>

    <!-- Sidebar -->
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
                <li class="logout"><a href="../index.html"><span class="icon">🚪</span> Sair</a></li>
            </ul>
        </nav>
    </div>

    <!-- Conteúdo principal -->
    <div class="main">
        <h1>Gerir Funcionários</h1>
        <p>Adicione, edite ou remova funcionários da sua equipe.</p>

        <!-- Tabela de funcionários -->
        <table>
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Nome</th>
                    <th>Email</th>
                    <th>Função</th>
                    <th>Ações</th>
                </tr>
            </thead>
            <tbody>
                <!-- Exemplo de linha de funcionário -->
                <tr>
                    <td>1</td>
                    <td>João Silva</td>
                    <td>joao.silva@example.com</td>
                    <td>Gerente</td>
                    <td>
                        <a href="editar_funcionario.php?id=1">Editar</a>
                        <a href="excluir_funcionario.php?id=1">Excluir</a>
                    </td>
                </tr>
                <!-- ...outras linhas... -->
            </tbody>
        </table>
    </div>

    <!-- JavaScript -->
    <script src="main.js"></script>
</body>
</html>