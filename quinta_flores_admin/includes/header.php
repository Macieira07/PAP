<!DOCTYPE html>
<html lang="pt-PT">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $titulo ?? 'Painel Administrativo - Quinta Flores'; ?></title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="../assets/css/admin.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
</head>
<body>
    <?php if (isset($_SESSION['admin_logado'])): ?>
    <!-- Botão de menu -->
    <button class="menu-toggle" aria-label="Menu">
        <span></span>
        <span></span>
        <span></span>
    </button>

    <!-- Sidebar -->
    <div class="sidebar">
        <div class="sidebar-header">
            <span class="logo-icon">🌼</span>
            <h2>Quinta Flores</h2>
        </div>
        <nav class="sidebar-nav">
            <ul>
                <li>
                    <a href="../admin_index.php" class="<?= basename($_SERVER['PHP_SELF']) == 'admin_index.php' ? 'active' : '' ?>">
                        <i class="fas fa-home"></i>
                        <span>Início</span>
                    </a>
                </li>
                <li>
                    <a href="../funcionarios/listar.php" class="<?= strpos($_SERVER['PHP_SELF'], 'funcionarios/') !== false ? 'active' : '' ?>">
                        <i class="fas fa-users"></i>
                        <span>Funcionários</span>
                    </a>
                </li>
                <li>
                    <a href="../hospedes/listar.php" class="<?= strpos($_SERVER['PHP_SELF'], 'hospedes/') !== false ? 'active' : '' ?>">
                        <i class="fas fa-user-friends"></i>
                        <span>Hóspedes</span>
                    </a>
                </li>
                <li>
                    <a href="../reservas/listar.php" class="<?= strpos($_SERVER['PHP_SELF'], 'reservas/') !== false ? 'active' : '' ?>">
                        <i class="fas fa-calendar-alt"></i>
                        <span>Reservas</span>
                    </a>
                </li>
                <li>
                    <a href="../casas/listar.php" class="<?= strpos($_SERVER['PHP_SELF'], 'casas/') !== false ? 'active' : '' ?>">
                        <i class="fas fa-home"></i>
                        <span>Casas</span>
                    </a>
                </li>
                <li>
                    <a href="../relatorios/ocupacao.php" class="<?= strpos($_SERVER['PHP_SELF'], 'relatorios/') !== false ? 'active' : '' ?>">
                        <i class="fas fa-chart-bar"></i>
                        <span>Relatórios</span>
                    </a>
                </li>
                <li>
                    <a href="../configuracoes/perfil.php" class="<?= strpos($_SERVER['PHP_SELF'], 'configuracoes/') !== false ? 'active' : '' ?>">
                        <i class="fas fa-cog"></i>
                        <span>Configurações</span>
                    </a>
                </li>
                <li class="logout">
                    <a href="../logout.php">
                        <i class="fas fa-sign-out-alt"></i>
                        <span>Sair</span>
                    </a>
                </li>
            </ul>
        </nav>
    </div>
    <?php endif; ?>

    <!-- Conteúdo principal -->
    <div class="main">
        <?php if (isset($_SESSION['mensagem'])): ?>
            <div class="alert alert-success"><?= $_SESSION['mensagem']; unset($_SESSION['mensagem']); ?></div>
        <?php endif; ?>
        
        <?php if (isset($_SESSION['erro'])): ?>
            <div class="alert alert-danger"><?= $_SESSION['erro']; unset($_SESSION['erro']); ?></div>
        <?php endif; ?>