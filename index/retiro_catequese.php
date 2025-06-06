<?php
session_start();
require_once '../conexao.php';
require_once 'i18n.php'; // caminho correto para o i18n.php

// Só aqui podes usar a classe I18n
if (isset($_GET['lang']) && in_array($_GET['lang'], ['pt', 'en', 'fr', 'es'])) {
    I18n::setLanguage($_GET['lang']);
    header('Location: ' . strtok($_SERVER['REQUEST_URI'], '?'));
    exit();
}

$page_title = I18n::get('catechism_retreat_title');
include '../components/header.php';
?>


<!DOCTYPE html>
<html lang="<?= I18n::getCurrentLanguage() ?>" data-theme="light">
<head>
    <meta charset="UTF-8">
    <title><?= I18n::get('catechism_retreat_title') ?></title>
    <link rel="icon" type="image/png" sizes="32x32" href="../assets/logos/favicon-32x32.png">
    <link rel="icon" type="image/png" sizes="16x16" href="../assets/logos/favicon-16x16.png">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
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
    <main class="main-content" id="sobre">
        <section class="section">
            <div class="section__container">
                <a href="../index.php#activities" class="back-btn">
                    <i class="ri-arrow-left-line"></i>
                    <?= I18n::get('back_to_offers') ?>
                </a>

                <p class="section__subheader"><?= I18n::get('special_offer') ?></p>
                <h1 class="section__header"><?= I18n::get('catechism_retreat_title') ?></h1>

                <div class="offer-details">
                    <div class="offer-image">
                        <img src="../assets/images/retiro.avif" alt="<?= I18n::get('catechism_retreat_title') ?>">
                        <div class="offer-badge">4 <?= I18n::get('nights_badge') ?></div>
                        <div class="capacity-badge"><?= str_replace(':max', '10', I18n::get('capacity_badge')) ?></div>
                    </div>
                    <div class="offer-content">
                        <h2 class="offer-title"><?= I18n::get('retreat_title') ?></h2>
                        <p class="offer-price">240€</p>
                        <div class="code-highlight"><?= I18n::get('promo_code_highlight') ?> <strong>RETIRO240</strong></div>
                        <p class="offer-description"><?= I18n::get('catechism_retreat_description') ?></p>
                    </div>
                </div>

                <div class="includes-section" id="ofertas">
                    <h3 class="includes-title"><?= I18n::get('whats_included') ?></h3>
                    <div class="includes-grid">
                        <div class="include-item">
                            <i class="ri-hotel-bed-line include-icon"></i>
                            <div class="include-content">
                                <h4><?= I18n::get('retreat_nights_title') ?></h4>
                                <p><?= I18n::get('retreat_nights_description') ?></p>
                            </div>
                        </div>

                        <div class="include-item">
                            <i class="ri-gift-line include-icon"></i>
                            <div class="include-content">
                                <h4><?= I18n::get('retreat_candles_title') ?></h4>
                                <p><?= I18n::get('retreat_candles_description') ?></p>
                            </div>
                        </div>      

                        <div class="include-item">
                            <i class="ri-music-line include-icon"></i>
                            <div class="include-content">
                                <h4><?= I18n::get('retreat_photos_title') ?></h4>
                                <p><?= I18n::get('retreat_photos_description') ?></p>
                            </div>
                        </div>

                        <div class="include-item">
                            <i class="ri-lightbulb-line include-icon"></i>
                            <div class="include-content">
                                <h4><?= I18n::get('retreat_materials_title') ?></h4>
                                <p><?= I18n::get('retreat_materials_description') ?></p>
                            </div>
                        </div>

                        <div class="include-item">
                            <i class="ri-cup-line include-icon"></i>
                            <div class="include-content">
                                <h4><?= I18n::get('retreat_ambience_title') ?></h4>
                                <p><?= I18n::get('retreat_ambience_description') ?></p>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="cta-section" id="reserva">
                    <h3 class="cta-title"><?= I18n::get('retreat_cta_title') ?></h3>
                    <p class="cta-subtitle"><?= I18n::get('retreat_cta_subtitle') ?></p>
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
</body>
</html>
