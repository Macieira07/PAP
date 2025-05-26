<?php
// header.php

// Começar sessão antes de qualquer output
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

// Verificar se o usuário está logado
if (!isset($_SESSION['id'])) {
    header('Location: ../login1/login.php');
    exit();
}

// Configurações do site
define('SITE_NAME', 'Quinta das Flores');
define('PRIMARY_COLOR', '#6A0DAD');
define('SECONDARY_COLOR', '#A56EFF');
?>
<!DOCTYPE html>
<html lang="pt">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title><?= htmlspecialchars($page_title ?? 'Reserva') ?> - <?= SITE_NAME ?></title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" />
    <link rel="stylesheet" href="global.css" />
    <link rel="icon" type="image/png" sizes="32x32" href="../assets/logos/favicon-32x32.png" />
    <link rel="icon" type="image/png" sizes="16x16" href="../assets/logos/favicon-16x16.png" />

    <style>
        /* Estilos para o dropdown */
        .user-dropdown {
            position: relative;
            display: inline-block;
        }
        .user-btn {
            background: none;
            border: none;
            color: inherit;
            font: inherit;
            cursor: pointer;
            display: flex;
            align-items: center;
            gap: 0.3rem;
            font-size: 1rem;
        }
        .user-btn i {
            pointer-events: none;
        }
        .dropdown-content {
            display: none;
            position: absolute;
            right: 0;
            background-color: white;
            min-width: 180px;
            box-shadow: 0px 8px 16px rgba(0,0,0,0.2);
            z-index: 9999;
            border-radius: 5px;
            padding: 0.5rem 0;
        }
        .dropdown-content a {
            color: black;
            padding: 10px 16px;
            text-decoration: none;
            display: block;
            transition: background-color 0.2s ease-in-out;
        }
        .dropdown-content a:hover {
            background-color: #f0f0f0;
        }
        .user-dropdown.show .dropdown-content {
            display: block;
        }

        .logo-link {
            display: flex;
            align-items: center;
            gap: 1rem;
            text-decoration: none;
            color: inherit;
            font-weight: bold;
            font-size: 1.2rem;
        }
        .logo-img {
            height: 80px;
            width: auto;
            display: block;
        }
    </style>
</head>
<body>
    <header class="main-header">
        <div class="container">
            <div class="header-content">
                <div class="logo">
                    <a href="../index.html" class="logo-link">
                        <img src="../assets/logos/logotipo1.png" alt="Logotipo Quinta das Flores" class="logo-img" />
                        <span><?= SITE_NAME ?></span>
                    </a>
                </div>
                <nav class="user-nav">
                    <div class="user-dropdown">
                        <button class="user-btn" type="button" aria-haspopup="true" aria-expanded="false" aria-controls="user-menu">
                            <i class="fas fa-user-circle"></i>
                            <?= htmlspecialchars($_SESSION['nome']) ?>
                            <i class="fas fa-caret-down"></i>
                        </button>
                        <div class="dropdown-content" id="user-menu" role="menu" aria-hidden="true">
                            <a href="perfil.php" role="menuitem">Perfil</a>
                            <a href="logout.php" role="menuitem">Sair</a>
                        </div>
                    </div>
                </nav>
            </div>
        </div>
    </header>
    <main>
