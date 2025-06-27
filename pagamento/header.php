<?php
// header.php
require_once 'i18n.php';

if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

if (!isset($_SESSION['id'])) {
    header('Location: ../login1/login.php');
    exit();
}

define('SITE_NAME', I18n::get('site_name'));
define('PRIMARY_COLOR', '#10B981');
define('SECONDARY_COLOR', '#047857');
?>
<!DOCTYPE html>
<html lang="<?= I18n::getCurrentLanguage() ?>">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title><?= htmlspecialchars($page_title ?? 'Reserva') ?> - <?= SITE_NAME ?></title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" />
    <link rel="stylesheet" href="../public/css/admin.css" />
    <link rel="icon" type="image/png" sizes="32x32" href="../assets/logos/favicon-32x32.png" />
    <link rel="icon" type="image/png" sizes="16x16" href="../assets/logos/favicon-16x16.png" />

    <style>
        /* Estilos para o dropdown */
        .user-dropdown, .language-selector {
            position: relative;
            display: inline-block;
        }
        
        .user-btn, .language-btn {
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
        
        .dropdown-content, .language-dropdown {
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
        
        .dropdown-content a, .language-dropdown a {
            color: black;
            padding: 10px 16px;
            text-decoration: none;
            display: block;
            transition: background-color 0.2s ease-in-out;
        }
        
        .dropdown-content a:hover, .language-dropdown a:hover {
            background-color: #f0f0f0;
        }
        
        .user-dropdown.show .dropdown-content,
        .language-selector.show .language-dropdown {
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
        
        .header-right {
            display: flex;
            align-items: center;
            gap: 1.5rem;
        }
        
        .language-dropdown a {
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }
        
        .language-dropdown img {
            width: 20px;
            height: auto;
        }
        
        .language-dropdown a.active {
            background-color: <?= SECONDARY_COLOR ?>;
            color: white;
        }
    </style>
</head>
<body>
    <header class="main-header">
        <div class="container">
            <div class="header-content">
                <div class="logo">
                    <a href="../index.php" class="logo-link">
                        <img src="../assets/logos/logotipo1.png" alt="Logotipo <?= SITE_NAME ?>" class="logo-img" />
                        <span><?= SITE_NAME ?></span>
                    </a>
                </div>
                
                <div class="header-right">
                    <!-- Seletor de Idiomas -->
                    <div class="language-selector">
                        <button class="language-btn" type="button">
                            <i class="fas fa-globe"></i>
                            <?= strtoupper(I18n::getCurrentLanguage()) ?>
                            <i class="fas fa-caret-down"></i>
                        </button>
                        <div class="language-dropdown">
                            <a href="?lang=pt" class="<?= I18n::getCurrentLanguage() === 'pt' ? 'active' : '' ?>">
                                <img src="../assets/flags/portugal.png" alt="Português"> Português
                            </a>
                            <a href="?lang=en" class="<?= I18n::getCurrentLanguage() === 'en' ? 'active' : '' ?>">
                                <img src="../assets/flags/reino-unido.png" alt="English"> English
                            </a>
                                                        <a href="?lang=fr" class="<?= I18n::getCurrentLanguage() === 'fr' ? 'active' : '' ?>">
                                <img src="../assets/flags/franca.png" alt="Francais"> Français
                            </a>
                                                        <a href="?lang=es" class="<?= I18n::getCurrentLanguage() === 'es' ? 'active' : '' ?>">
                                <img src="../assets/flags/espanha.png" alt="Espanol"> Espanol   
                            </a>
                        </div>
                    </div>
                    
                    <nav class="user-nav">
                        <div class="user-dropdown">
                            <button class="user-btn" type="button" aria-haspopup="true" aria-expanded="false" aria-controls="user-menu">
                                <i class="fas fa-user-circle"></i>
                                <?= htmlspecialchars($_SESSION['nome']) ?>
                                <i class="fas fa-caret-down"></i>
                            </button>
                            <div class="dropdown-content" id="user-menu" role="menu" aria-hidden="true">
                                <a href="perfil.php" role="menuitem"><?= I18n::get('profile') ?></a>
                                <a href="minhas_reservas.php" role="menuitem"><?= I18n::get('my_reservations') ?></a>
                                <a href="logout.php" role="menuitem"><?= I18n::get('logout') ?></a>
                            </div>
                        </div>
                    </nav>
                </div>
            </div>
        </div>
    </header>
    <main>

    <script>
        // Mostrar/ocultar dropdowns
        document.querySelector('.user-btn').addEventListener('click', function() {
            document.querySelector('.user-dropdown').classList.toggle('show');
        });
        
        document.querySelector('.language-btn').addEventListener('click', function() {
            document.querySelector('.language-selector').classList.toggle('show');
        });
        
        // Fechar dropdowns ao clicar fora
        window.addEventListener('click', function(event) {
            if (!event.target.matches('.user-btn') && !event.target.matches('.language-btn')) {
                const dropdowns = document.querySelectorAll('.user-dropdown, .language-selector');
                dropdowns.forEach(function(dropdown) {
                    if (dropdown.classList.contains('show')) {
                        dropdown.classList.remove('show');
                    }
                });
            }
        });
    </script>