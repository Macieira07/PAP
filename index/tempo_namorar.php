<?php
/*
 * ============================================================
 *   Página Tempo de Namorar - Quinta Flores
 * ============================================================
 *
 *   Linguagens Utilizadas:
 *     - PHP (backend, lógica e includes)
 *     - HTML5 (estrutura)
 *     - CSS3 (estilos, arquivos externos)
 *     - JavaScript (interatividade, tema)
 *
 *   Bibliotecas e Frameworks:
 *     - Font Awesome & Remixicon (ícones)
 *     - Google Fonts (fontes personalizadas)
 *     - i18n (internacionalização, multi-idioma)
 *     - Chatbot customizado (JS/PHP)
 *
 *   Estrutura da Página:
 *     1. Configuração inicial PHP (i18n, includes)
 *     2. <head> com meta tags, fontes, ícones, CSS
 *     3. Hero/Oferta Section (destaque da oferta)
 *     4. O que está incluído (lista de benefícios)
 *     5. Call to Action (reserva)
 *     6. Scripts finais (tema, chatbot)
 *
 *   Autor: [Seu Nome ou Equipa]
 *   Última atualização: [Data]
 * ============================================================
 */
// ===================== 1. Configuração Inicial PHP =====================
session_start();
require_once 'i18n.php'; // ajusta o caminho conforme localização do i18n.php

// Trocar linguagem via GET
if (isset($_GET['lang']) && in_array($_GET['lang'], ['pt', 'en', 'fr', 'es'])) {
    I18n::setLanguage($_GET['lang']);
    // Remove o parâmetro lang para evitar loops
    header('Location: ' . strtok($_SERVER['REQUEST_URI'], '?'));
    exit();
}

// Inicializa as traduções
I18n::init();

$page_title = I18n::get('romantic_getaway_title'); // chave que vais criar no ficheiro de idiomas

include '../components/header.php';
?>

<!DOCTYPE html>
<html lang="<?= I18n::getCurrentLanguage() ?>" data-theme="light">
<head>
    <meta charset="UTF-8">
    <link rel="icon" type="image/png" sizes="32x32" href="../assets/logos/favicon-32x32.png">
    <link rel="icon" type="image/png" sizes="16x16" href="../assets/logos/favicon-16x16.png">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $page_title ?> - Quinta Flores</title>
    <link rel="icon" type="image/png" href="../assets/logos/logotipo1.png" sizes="1000x1000">
    <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@400;500;600;700&family=Montserrat:wght@300;400;500;600&display=swap" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/remixicon@4.0.0/fonts/remixicon.css" rel="stylesheet">
    <link rel="stylesheet" href="https://unpkg.com/aos@next/dist/aos.css" />
    <link rel="stylesheet" type="text/css" href="lermais.css">
    <link rel="stylesheet" type="text/css" href="chatbot.css">
    <link rel="stylesheet" href="../components/header.css">
    <link rel="stylesheet" href="../components/footer.css">
</head>

<body>

    <main class="main-content">
        <section class="section">
            <div class="section__container">
                <a href="../index.php#activities" class="back-btn">
                    <i class="ri-arrow-left-line"></i>
                    <?= I18n::get('back_to_offers') ?>
                </a>

                <p class="section__subheader"><?= I18n::get('special_offer') ?></p>
                <h1 class="section__header"><?= I18n::get('romantic_getaway_title') ?></h1>

                <div class="offer-details">
                    <div class="offer-image">
                        <img src="../assets/images/tempo_namorar.avif" alt="<?= I18n::get('romantic_getaway_title') ?>">
                        <div class="offer-badge">2 <?= I18n::get('nights_badge') ?></div>
                        <div class="capacity-badge"><?= I18n::get('for_two_people') ?></div>
                    </div>
                    <div class="offer-content">
                        <h2 class="offer-title"><?= I18n::get('romantic_getaway_subtitle') ?></h2>
                        <p class="offer-price">260€</p>
                        <div class="code-highlight"><?= I18n::get('promo_code_highlight') ?> <strong>LOVE260</strong></div>
                        <p class="offer-description">
                            <?= I18n::get('romantic_getaway_description') ?>
                        </p>
                    </div>
                </div>

                <div class="includes-section">
                    <h3 class="includes-title"><?= I18n::get('whats_included') ?></h3>
                    <div class="includes-grid">
                        <div class="include-item">
                            <i class="ri-hotel-bed-line include-icon"></i>
                            <div class="include-content">
                                <h4><?= I18n::get('two_nights_title') ?></h4>
                                <p><?= I18n::get('two_nights_description') ?></p>
                            </div>
                        </div>
                        <div class="include-item">
                            <i class="ri-gift-line include-icon"></i>
                            <div class="include-content">
                                <h4><?= I18n::get('romantic_picnic_title') ?></h4>
                                <p><?= I18n::get('romantic_picnic_description') ?></p>
                            </div>
                        </div>
                        <div class="include-item">
                            <i class="ri-sun-foggy-line include-icon"></i>
                            <div class="include-content">
                                <h4><?= I18n::get('special_breakfast_title') ?></h4>
                                <p><?= I18n::get('special_breakfast_description') ?></p>
                            </div>
                        </div>
                        <div class="include-item">
                            <i class="ri-restaurant-line include-icon"></i>
                            <div class="include-content">
                                <h4><?= I18n::get('candlelight_dinner_title') ?></h4>
                                <p><?= I18n::get('candlelight_dinner_description') ?></p>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="cta-section">
                    <h3 class="cta-title"><?= I18n::get('romantic_cta_title') ?></h3>
                    <p class="cta-subtitle"><?= I18n::get('romantic_cta_subtitle') ?></p>
                    <a href="../login1/pagina_login.php" class="reserve-btn">
                        <?= I18n::get('book_now_button') ?>
                        <i class="ri-arrow-right-line"></i>
                    </a>
                </div>
            </div>
        </section>
    </main>
    <script>
    function toggleTheme() {
        const html = document.documentElement;
        const themeToggle = document.getElementById('themeToggle');
        const currentTheme = html.getAttribute('data-theme');
        const icon = themeToggle.querySelector('i');
        
        if (currentTheme === 'light') {
            html.setAttribute('data-theme', 'dark');
            icon.className = 'ri-moon-line';
            localStorage.setItem('theme', 'dark');
        } else {
            html.setAttribute('data-theme', 'light');
            icon.className = 'ri-sun-line';
            localStorage.setItem('theme', 'light');
        }
    }
    const savedTheme = localStorage.getItem('theme') || 'light';
    const themeToggle = document.getElementById('themeToggle');
    const icon = themeToggle.querySelector('i');
    document.documentElement.setAttribute('data-theme', savedTheme);
    icon.className = savedTheme === 'dark' ? 'ri-moon-line' : 'ri-sun-line';
    themeToggle.addEventListener('click', toggleTheme);
    </script>
        <script>
    function changeLanguage(lang) {
        const url = new URL(window.location.href);
        url.searchParams.set('lang', lang);
        window.location.href = url.toString();
    }
    </script>
<?php include '../components/footer.php'; ?>
<link rel="stylesheet" href="../chatbot/chatbot.css">
<script src="../chatbot/chatbot.js"></script>
<?php include '../chatbot/chatbot_config.php'; ?>
<?php include '../chatbot/chatbot.php'; ?>
</body>
</html>
