<?php
/*
 * ============================================================
 *   Página Passeios Culturais - Quinta Flores
 * ============================================================
 *
 *   Linguagens Utilizadas:
 *     - PHP (backend, lógica e includes)
 *     - HTML5 (estrutura)
 *     - CSS3 (estilos, arquivos externos)
 *     - JavaScript (interatividade, navegação, tema)
 *
 *   Bibliotecas e Frameworks:
 *     - Font Awesome & Remixicon (ícones)
 *     - Google Fonts (fontes personalizadas)
 *     - i18n (internacionalização, multi-idioma)
 *     - Chatbot customizado (JS/PHP)
 *
 *   Estrutura da Página:
 *     1. Configuração inicial PHP (i18n, includes, traduções)
 *     2. <head> com meta tags, fontes, ícones, CSS
 *     3. Hero Section (destaque)
 *     4. Passeios Section (lista de passeios)
 *     5. Paço de Calheiros Section
 *     6. Galeria Section
 *     7. Localização/Mapa Section
 *     8. Footer e scripts finais (tema, chatbot)
 *
 *   Autor: [Seu Nome ou Equipa]
 *   Última atualização: [Data]
 * ============================================================
 */
// ===================== 1. Configuração Inicial PHP =====================
// Get language from session or default to Portuguese
session_start();
$lang = isset($_GET['lang']) ? $_GET['lang'] : (isset($_SESSION['lang']) ? $_SESSION['lang'] : 'pt');
$_SESSION['lang'] = $lang;

// Load translations
$translations = require_once "../translations/passeios_culturais_{$lang}.php";

$nav_links = $translations['nav_links'];
include '../components/header.php';
?>
<!DOCTYPE html>
<html lang="<?php echo $lang; ?>">
<head>
    <script>
      // Aplica o tema salvo ANTES de carregar o CSS, evitando flash e bugs
      (function() {
        var savedTheme = localStorage.getItem('theme') || 'light';
        document.documentElement.setAttribute('data-theme', savedTheme);
      })();
    </script>
    <meta charset="UTF-8">
        <link rel="icon" type="image/png" sizes="32x32" href="../assets/logos/favicon-32x32.png">
<link rel="icon" type="image/png" sizes="16x16" href="../assets/logos/favicon-16x16.png">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $translations['hero']['title']; ?> | Quinta Flores - Ponte de Lima</title>
    <link rel="icon" type="image/png" href="../assets/logos/logotipo1.png" sizes="1000x1000">
    <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@400;500;600;700&family=Montserrat:wght@300;400;500;600&display=swap" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" rel="stylesheet">

    <link href="https://cdn.jsdelivr.net/npm/remixicon@4.0.0/fonts/remixicon.css" rel="stylesheet">
    <link rel="stylesheet" href="https://unpkg.com/aos@next/dist/aos.css" />
    <link rel="stylesheet" href="chatbot.css"/>
     <link rel="stylesheet" href="../components/header.css">
     <link rel="stylesheet" href="../components/footer.css">
     <link rel="stylesheet" href="../components/base.css">
    <style>
        .hamburger {
            display: none;
            cursor: pointer;
            background: none;
            border: none;
            font-size: 1.5rem;
            color: var(--text-dark);
        }

        /* Hero Section */
        .hero {
            height: 100vh;
            width: 100%;
            background: linear-gradient(rgba(0,0,0,0.3), rgba(0,0,0,0.3)), url('../assets/images/passeios_culturais.avif');
            background-size: cover;
            background-position: center;
            display: flex;
            align-items: center;
            color: var(--white);
            position: relative;
        }

        .hero__content {
            max-width: 1300px;
            margin: 0 auto;
            padding: 0 2rem;
        }

        .hero__title {
            font-size: 3.5rem;
            margin-bottom: 1rem;
            animation: fadeIn 1s ease-in;
        }

        .hero__subtitle {
            font-size: 1.5rem;
            margin-bottom: 2rem;
            animation: fadeIn 1s ease-in 0.5s forwards;
            opacity: 0;
        }

        .hero__cta {
            display: inline-block;
            padding: 1rem 2rem;
            background-color: var(--primary-color);
            color: white;
            text-decoration: none;
            border-radius: 30px;
            font-weight: 600;
            transition: var(--transition);
            animation: fadeIn 1s ease-in 1s forwards;
            opacity: 0;
        }

        .hero__cta:hover {
            background-color: var(--primary-color-dark);
            transform: translateY(-5px);
        }

        .scroll-down {
            position: absolute;
            bottom: 30px;
            left: 50%;
            transform: translateX(-50%);
            font-size: 2rem;
            color: var(--white);
            animation: bounce 2s infinite;
        }

        /* Main Content */
        .section {
            padding: 6rem 2rem;
        }

        .section__container {
            max-width: 1300px;
            margin: 0 auto;
        }

        .section-title {
            color: var(--primary-color-dark);
            font-size: 2.5rem;
            margin-bottom: 1rem;
            text-align: center;
        }

        .section-subtitle {
            color: var(--text-light);
            font-size: 1.2rem;
            margin-bottom: 3rem;
            text-align: center;
            max-width: 700px;
            margin-left: auto;
            margin-right: auto;
        }

        /* Activities Section */
        .activities-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
            gap: 2rem;
        }

        .activity-card {
            background: var(--white);
            border-radius: var(--border-radius);
            overflow: hidden;
            box-shadow: var(--shadow);
            transition: var(--transition);
        }

        .activity-card:hover {
            transform: translateY(-8px);
            box-shadow: var(--shadow-hover);
        }

        .activity-card__image {
            height: 200px;
            width: 100%;
            object-fit: cover;
        }

        .activity-card__content {
            padding: 1.5rem;
        }

        .activity-card__title {
            color: var(--primary-color-dark);
            font-size: 1.3rem;
            margin-bottom: 0.7rem;
        }

        .activity-card__text {
            color: var(--text-dark);
            margin-bottom: 1rem;
        }

        .activity-card__details {
            margin-bottom: 1rem;
            padding: 0.8rem;
            background-color: var(--primary-color-light);
            border-radius: 8px;
        }

        .activity-card__detail {
            display: flex;
            margin-bottom: 0.5rem;
        }

        .activity-card__detail i {
            color: var(--primary-color);
            margin-right: 0.5rem;
            margin-top: 0.2rem;
        }

        .activity-card__link {
            display: inline-block;
            color: var(--primary-color);
            font-weight: 600;
            text-decoration: none;
            transition: var(--transition);
        }

        .activity-card__link:hover {
            color: var(--primary-color-dark);
            text-decoration: underline;
        }

        /* Features */
        .features-list {
            list-style: none;
            padding: 0;
        }

        .features-list li {
            margin-bottom: 1rem;
            padding-left: 2rem;
            position: relative;
        }

        .features-list li::before {
            content: "✓";
            color: var(--primary-color);
            position: absolute;
            left: 0;
            font-weight: bold;
        }

        /* Gallery Section */
        .gallery-section {
            background-color: var(--gray-light);
        }

        .gallery {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(250px, 1fr));
            gap: 1.5rem;
        }

        .gallery-item {
            position: relative;
            overflow: hidden;
            border-radius: var(--border-radius);
            height: 250px;
            cursor: pointer;
        }

        .gallery-item img {
            width: 100%;
            height: 100%;
            object-fit: cover;
            transition: var(--transition);
        }

        .gallery-item:hover img {
            transform: scale(1.1);
        }

        .gallery-caption {
            position: absolute;
            bottom: 0;
            left: 0;
            right: 0;
            background: linear-gradient(transparent, rgba(0,0,0,0.7));
            color: white;
            padding: 1rem;
            opacity: 0;
            transition: var(--transition);
        }

        .gallery-item:hover .gallery-caption {
            opacity: 1;
        }

        /* Map Section */
        .map-container {
            width: 100%;
            height: 500px;
            border-radius: var(--border-radius);
            overflow: hidden;
            box-shadow: var(--shadow);
            margin: 3rem 0;
        }

        .social-icons {
            display: flex;
            gap: 1rem;
            margin-top: 1.5rem;
        }

        .social-icon {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            width: 40px;
            height: 40px;
            border-radius: 50%;
            background-color: #333;
            color: var(--white);
            font-size: 1.2rem;
            transition: var(--transition);
        }

        .social-icon:hover {
            background-color: var(--primary-color);
            transform: translateY(-3px);
        }
        /* Theme Toggle */
        .theme-toggle {
            position: relative;
            margin-left: 15px;
            background: var(--primary-color);
            border: none;
            border-radius: 50%;
            width: 50px;
            height: 50px;
            cursor: pointer;
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            box-shadow: 0 2px 10px rgba(0,0,0,0.2);
            z-index: 1000;
            transition: var(--transition);
        }
        /* Animations */
        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(20px); }
            to { opacity: 1; transform: translateY(0); }
        }

        @keyframes bounce {
            0%, 20%, 50%, 80%, 100% { transform: translateY(0) translateX(-50%); }
            40% { transform: translateY(-20px) translateX(-50%); }
            60% { transform: translateY(-10px) translateX(-50%); }
        }

        /* Responsive Design */
        @media (max-width: 992px) {
            .hero__title {
                font-size: 3rem;
            }
        }

        @media (max-width: 768px) {
            .nav__links {
                position: fixed;
                top: 80px;
                left: 0;
                right: 0;
                background-color: var(--white);
                flex-direction: column;
                padding: 2rem;
                gap: 1.5rem;
                box-shadow: 0 10px 15px rgba(0,0,0,0.1);
                transform: translateY(-150%);
                transition: var(--transition);
                z-index: 999;
            }

            .nav__links.active {
                transform: translateY(0);
            }

            .hamburger {
                display: block;
            }

            .hero__title {
                font-size: 2.5rem;
            }

            .hero__subtitle {
                font-size: 1.2rem;
            }

            .section {
                padding: 4rem 1.5rem;
            }

            .section-title {
                font-size: 2rem;
            }

            [data-theme="dark"] .nav__links {
                background-color: #1e1e1e;
            }
        }
        /*linguagens traduções*/
        /* Language Selector Styles */
.language-selector {
    display: flex;
    align-items: center;
    margin-left: 2rem;
}

.language-btn {
    background: transparent;
    border: none;
    cursor: pointer;
    padding: 0.3rem;
    opacity: 0.7;
    transition: var(--transition);
    display: flex;
    align-items: center;
}

.language-btn.active {
    opacity: 1;
    font-weight: 600;
    border-bottom: 2px solid var(--primary-color);
}

.language-btn:hover {
    opacity: 1;
}

.language-btn img {
    width: 20px;
    height: 20px;
    margin-right: 5px;
}

/* Dropdown Language Menu for Mobile */
.language-dropdown {
    display: none;
    position: relative;
    margin-left: auto;
    margin-right: 1rem;
}

.language-dropdown-btn {
    background: transparent;
    border: none;
    display: flex;
    align-items: center;
    cursor: pointer;
    color: var(--text-dark);
}

.language-dropdown-content {
    display: none;
    position: absolute;
    right: 0;
    top: 100%;
    background-color: var(--white);
    min-width: 120px;
    box-shadow: var(--shadow);
    border-radius: var(--border-radius);
    z-index: 1000;
}

.language-dropdown-content.show {
    display: block;
}

.language-dropdown-content .language-btn {
    display: flex;
    width: 100%;
    padding: 0.7rem 1rem;
    justify-content: flex-start;
}

/* Media Queries */
@media (max-width: 992px) {
    .language-selector {
        display: none;
    }

    .language-dropdown {
        display: block;
    }
}

    </style>
</head>
<body>
    <!-- Hero Section -->
    <section class="hero">
        <div class="hero__content">
            <h1 class="hero__title"><?php echo $translations['hero']['title']; ?></h1>
            <p class="hero__subtitle"><?php echo $translations['hero']['subtitle']; ?></p>
            <a href="../login1/pagina_login.php" class="hero__cta"><?php echo $translations['hero']['cta']; ?></a>
        </div>
        <a href="#passeios" class="scroll-down">
            <i class="ri-arrow-down-s-line"></i>
        </a>
    </section>
    <!-- Passeios Section -->
    <section class="section" id="passeios">
        <div class="section__container">
            <h2 class="section-title" data-aos="fade-up"><?php echo $translations['passeios']['title']; ?></h2>
            <p class="section-subtitle" data-aos="fade-up" data-aos-delay="100"><?php echo $translations['passeios']['subtitle']; ?></p>

            <div class="activities-grid">
                <div class="activity-card" data-aos="fade-up" data-aos-delay="200">
                    <img src="../assets/images/percurso_da_lagoa.jpg" alt="<?php echo $translations['passeios']['percurso_lagoa']['title']; ?>" class="activity-card__image">
                    <div class="activity-card__content">
                        <h3 class="activity-card__title"><?php echo $translations['passeios']['percurso_lagoa']['title']; ?></h3>
                        <div class="activity-card__details">
                            <div class="activity-card__detail">
                                <i class="ri-map-pin-line"></i>
                                <span><?php echo $translations['passeios']['percurso_lagoa']['distance']; ?></span>
                            </div>
                            <div class="activity-card__detail">
                                <i class="ri-time-line"></i>
                                <span><?php echo $translations['passeios']['percurso_lagoa']['duration']; ?></span>
                            </div>
                            <div class="activity-card__detail">
                                <i class="ri-bar-chart-line"></i>
                                <span><?php echo $translations['passeios']['percurso_lagoa']['difficulty']; ?></span>
                            </div>
                        </div>
                        <p class="activity-card__text"><?php echo $translations['passeios']['percurso_lagoa']['description']; ?></p>
                    </div>
                </div>

                <div class="activity-card" data-aos="fade-up" data-aos-delay="300">
                    <img src="../assets/images/percurso_do_rio.jpg" alt="<?php echo $translations['passeios']['percurso_rio']['title']; ?>" class="activity-card__image">
                    <div class="activity-card__content">
                        <h3 class="activity-card__title"><?php echo $translations['passeios']['percurso_rio']['title']; ?></h3>
                        <div class="activity-card__details">
                            <div class="activity-card__detail">
                                <i class="ri-map-pin-line"></i>
                                <span><?php echo $translations['passeios']['percurso_rio']['distance']; ?></span>
                            </div>
                            <div class="activity-card__detail">
                                <i class="ri-time-line"></i>
                                <span><?php echo $translations['passeios']['percurso_rio']['duration']; ?></span>
                            </div>
                            <div class="activity-card__detail">
                                <i class="ri-bar-chart-line"></i>
                                <span><?php echo $translations['passeios']['percurso_rio']['difficulty']; ?></span>
                            </div>
                        </div>
                        <p class="activity-card__text"><?php echo $translations['passeios']['percurso_rio']['description']; ?></p>
                    </div>
                </div>

                <div class="activity-card" data-aos="fade-up" data-aos-delay="400">
                    <img src="../assets/images/percurso_da_agua.jpg" alt="<?php echo $translations['passeios']['percurso_agua']['title']; ?>" class="activity-card__image">
                    <div class="activity-card__content">
                        <h3 class="activity-card__title"><?php echo $translations['passeios']['percurso_agua']['title']; ?></h3>
                        <div class="activity-card__details">
                            <div class="activity-card__detail">
                                <i class="ri-map-pin-line"></i>
                                <span><?php echo $translations['passeios']['percurso_agua']['distance']; ?></span>
                            </div>
                            <div class="activity-card__detail">
                                <i class="ri-time-line"></i>
                                <span><?php echo $translations['passeios']['percurso_agua']['duration']; ?></span>
                            </div>
                            <div class="activity-card__detail">
                                <i class="ri-bar-chart-line"></i>
                                <span><?php echo $translations['passeios']['percurso_agua']['difficulty']; ?></span>
                            </div>
                        </div>
                        <p class="activity-card__text"><?php echo $translations['passeios']['percurso_agua']['description']; ?></p>
                    </div>
                </div>

                <div class="activity-card" data-aos="fade-up" data-aos-delay="200">
                    <img src="../assets/images/passadicos_lagoas_bertiandos.jpg" alt="<?php echo $translations['passeios']['passadicos_bertiandos']['title']; ?>" class="activity-card__image">
                    <div class="activity-card__content">
                        <h3 class="activity-card__title"><?php echo $translations['passeios']['passadicos_bertiandos']['title']; ?></h3>
                        <div class="activity-card__details">
                            <div class="activity-card__detail">
                                <i class="ri-map-pin-line"></i>
                                <span><?php echo $translations['passeios']['passadicos_bertiandos']['distance']; ?></span>
                            </div>
                            <div class="activity-card__detail">
                                <i class="ri-time-line"></i>
                                <span><?php echo $translations['passeios']['passadicos_bertiandos']['duration']; ?></span>
                            </div>
                            <div class="activity-card__detail">
                                <i class="ri-bar-chart-line"></i>
                                <span><?php echo $translations['passeios']['passadicos_bertiandos']['difficulty']; ?></span>
                            </div>
                        </div>
                        <p class="activity-card__text"><?php echo $translations['passeios']['passadicos_bertiandos']['description']; ?></p>
                    </div>
                </div>

                <div class="activity-card" data-aos="fade-up" data-aos-delay="300">
                    <img src="../assets/images/trilho_das_portelas.jpg" alt="<?php echo $translations['passeios']['trilho_portelas']['title']; ?>" class="activity-card__image">
                    <div class="activity-card__content">
                        <h3 class="activity-card__title"><?php echo $translations['passeios']['trilho_portelas']['title']; ?></h3>
                        <div class="activity-card__details">
                            <div class="activity-card__detail">
                                <i class="ri-map-pin-line"></i>
                                <span><?php echo $translations['passeios']['trilho_portelas']['distance']; ?></span>
                            </div>
                            <div class="activity-card__detail">
                                <i class="ri-time-line"></i>
                                <span><?php echo $translations['passeios']['trilho_portelas']['duration']; ?></span>
                            </div>
                            <div class="activity-card__detail">
                                <i class="ri-bar-chart-line"></i>
                                <span><?php echo $translations['passeios']['trilho_portelas']['difficulty']; ?></span>
                            </div>
                        </div>
                        <p class="activity-card__text"><?php echo $translations['passeios']['trilho_portelas']['description']; ?></p>
                    </div>
                </div>

                <div class="activity-card" data-aos="fade-up" data-aos-delay="400">
                    <img src="../assets/images/rota_alto_minho.jpg" alt="<?php echo $translations['passeios']['rota_paisagens']['title']; ?>" class="activity-card__image">
                    <div class="activity-card__content">
                        <h3 class="activity-card__title"><?php echo $translations['passeios']['rota_paisagens']['title']; ?></h3>
                        <div class="activity-card__details">
                            <div class="activity-card__detail">
                                <i class="ri-map-pin-line"></i>
                                <span><?php echo $translations['passeios']['rota_paisagens']['distance']; ?></span>
                            </div>
                            <div class="activity-card__detail">
                                <i class="ri-time-line"></i>
                                <span><?php echo $translations['passeios']['rota_paisagens']['duration']; ?></span>
                            </div>
                            <div class="activity-card__detail">
                                <i class="ri-bar-chart-line"></i>
                                <span><?php echo $translations['passeios']['rota_paisagens']['difficulty']; ?></span>
                            </div>
                        </div>
                        <p class="activity-card__text"><?php echo $translations['passeios']['rota_paisagens']['description']; ?></p>
                    </div>
                </div>
            </div>
        </div>
    </section>
    <!-- Paço de Calheiros Section -->
    <section class="section" id="PacoCalheiros" style="background-color: var(--gray-light);">
        <div class="section__container">
            <h2 class="section-title" data-aos="fade-up"><?php echo $translations['paco_calheiros']['title']; ?></h2>
            <p class="section-subtitle" data-aos="fade-up" data-aos-delay="100"><?php echo $translations['paco_calheiros']['subtitle']; ?></p>

            <div class="activities-grid">
                <div class="activity-card" data-aos="fade-up" data-aos-delay="200">
                    <img src="../assets/images/paco_calheiros.jpg" alt="<?php echo $translations['paco_calheiros']['title']; ?>" class="activity-card__image">
                    <div class="activity-card__content">
                        <h3 class="activity-card__title"><?php echo $translations['paco_calheiros']['title']; ?></h3>
                        <p class="activity-card__text"><?php echo $translations['paco_calheiros']['description']; ?></p>
                        <ul class="features-list">
                            <?php foreach ($translations['paco_calheiros']['features'] as $feature): ?>
                                <li><?php echo $feature; ?></li>
                            <?php endforeach; ?>
                        </ul>
                    </div>
                </div>
            </div>
        </div>
    </section>
    <!-- Gallery Section -->
    <section class="section gallery-section" id="galeria">
        <div class="section__container">
            <h2 class="section-title" data-aos="fade-up"><?php echo $translations['galeria']['title']; ?></h2>
            <p class="section-subtitle" data-aos="fade-up" data-aos-delay="100"><?php echo $translations['galeria']['subtitle']; ?></p>

            <div class="gallery">
                <div class="gallery-item" data-aos="fade-up" data-aos-delay="100">
                    <img src="../assets/images/lagoa_Spedro_arcos.jpg" alt="<?php echo $translations['galeria']['images'][0]; ?>">
                    <div class="gallery-caption"><?php echo $translations['galeria']['images'][0]; ?></div>
                </div>
                <div class="gallery-item" data-aos="fade-up" data-aos-delay="200">
                    <img src="../assets/images/passadicos.jpg" alt="<?php echo $translations['galeria']['images'][1]; ?>">
                    <div class="gallery-caption"><?php echo $translations['galeria']['images'][1]; ?></div>
                </div>
                <div class="gallery-item" data-aos="fade-up" data-aos-delay="300">
                    <img src="../assets/images/rio_estoraos.jpg" alt="<?php echo $translations['galeria']['images'][2]; ?>">
                    <div class="gallery-caption"><?php echo $translations['galeria']['images'][2]; ?></div>
                </div>
                <div class="gallery-item" data-aos="fade-up" data-aos-delay="400">
                    <img src="../assets/images/lagoa_mimoso.jpg" alt="<?php echo $translations['galeria']['images'][3]; ?>">
                    <div class="gallery-caption"><?php echo $translations['galeria']['images'][3]; ?></div>
                </div>
                <div class="gallery-item" data-aos="fade-up" data-aos-delay="500">
                    <img src="../assets/images/ponte_historica.jpg" alt="<?php echo $translations['galeria']['images'][4]; ?>">
                    <div class="gallery-caption"><?php echo $translations['galeria']['images'][4]; ?></div>
                </div>
                <div class="gallery-item" data-aos="fade-up" data-aos-delay="600">
                    <img src="../assets/images/passadicos_bertiandos.jpg" alt="<?php echo $translations['galeria']['images'][5]; ?>">
                    <div class="gallery-caption"><?php echo $translations['galeria']['images'][5]; ?></div>
                </div>
                <div class="gallery-item" data-aos="fade-up" data-aos-delay="700">
                    <img src="../assets/images/moinho.avif" alt="<?php echo $translations['galeria']['images'][6]; ?>">
                    <div class="gallery-caption"><?php echo $translations['galeria']['images'][6]; ?></div>
                </div>
                <div class="gallery-item" data-aos="fade-up" data-aos-delay="800">
                    <img src="../assets/images/paco_calheiros_vista.jpeg" alt="<?php echo $translations['galeria']['images'][7]; ?>">
                    <div class="gallery-caption"><?php echo $translations['galeria']['images'][7]; ?></div>
                </div>
            </div>
        </div>
    </section>
    <!-- Map Section -->
    <section class="section" id="localizacao">
        <div class="section__container">
            <h2 class="section-title" data-aos="fade-up"><?php echo $translations['localizacao']['title']; ?></h2>
            <p class="section-subtitle" data-aos="fade-up" data-aos-delay="100"><?php echo $translations['localizacao']['subtitle']; ?></p>

            <div class="map-container" data-aos="fade-up" data-aos-delay="200">
                <iframe 
                    src="https://www.google.com/maps/embed?pb=!1m18!1m12!1m3!1d47893.81180278912!2d-8.614690644970705!3d41.76716!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0xd25a6e2139e817f%3A0x400ebbde490c450!2sPonte%20de%20Lima!5e0!3m2!1spt-PT!2spt!4v1708106431705!5m2!1spt-PT!2spt" 
                    width="100%" 
                    height="100%" 
                    style="border:0;" 
                    allowfullscreen="" 
                    loading="lazy" 
                    referrerpolicy="no-referrer-when-downgrade">
                </iframe>
            </div>
        </div>
    </section>
    <!-- Scripts -->
    <script src="https://unpkg.com/aos@next/dist/aos.js"></script>
    <script>
        // Inicializar AOS (Animate On Scroll)
        AOS.init({
            duration: 800,
            once: true
        });
    </script>
    <?php include '../components/footer.php'; ?>
<link rel="stylesheet" href="../chatbot/chatbot.css">
<script src="../chatbot/chatbot.js"></script>
<?php include '../chatbot/chatbot_config.php'; ?>
<?php include '../chatbot/chatbot.php'; ?>
</body>
</html>