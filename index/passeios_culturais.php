<?php 
$nav_links = [
    ['href' => '#passeios', 'text' => 'Passeios Naturais'],
    ['href' => '#PacoCalheiros', 'text' => 'Paço de Calheiros'],
    ['href' => '#galeria', 'text' => 'Galeria'],
    ['href' => '#localizacao', 'text' => 'Localização'],
    ['href' => '../login1/pagina_login.php', 'text' => 'Reservar', 'class' => 'nav__cta'],
];
include '../components/header.php'; ?>
<!DOCTYPE html>
<html lang="pt" data-theme="light">
<head>
    <meta charset="UTF-8">
        <link rel="icon" type="image/png" sizes="32x32" href="../assets/logos/favicon-32x32.png">
<link rel="icon" type="image/png" sizes="16x16" href="../assets/logos/favicon-16x16.png">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Passeios Culturais | Quinta Flores - Ponte de Lima</title>
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
            <h1 class="hero__title">Passeios Culturais</h1>
            <p class="hero__subtitle">Descubra as riquezas naturais e culturais de Calheiros e Ponte de Lima</p>
            <a href="../login1/pagina_login.php" class="hero__cta">Reservar Agora</a>
        </div>
        <a href="#passeios" class="scroll-down">
            <i class="ri-arrow-down-s-line"></i>
        </a>
    </section>

    <!-- Passeios Section -->
    <section class="section" id="passeios">
        <div class="section__container">
            <h2 class="section-title" data-aos="fade-up">Passeios Naturais em Calheiros e Ponte de Lima</h2>
            <p class="section-subtitle" data-aos="fade-up" data-aos-delay="100">Explore trilhas deslumbrantes e paisagens culturais únicas na região do Minho</p>

            <div class="activities-grid">
                <div class="activity-card" data-aos="fade-up" data-aos-delay="200">
                    <img src="../assets/images/percurso_da_lagoa.jpg" alt="Percurso da Lagoa" class="activity-card__image">
                    <div class="activity-card__content">
                        <h3 class="activity-card__title">Percurso da Lagoa</h3>
                        <div class="activity-card__details">
                            <div class="activity-card__detail">
                                <i class="ri-map-pin-line"></i>
                                <span>Distância: 1,57 km (circular)</span>
                            </div>
                            <div class="activity-card__detail">
                                <i class="ri-time-line"></i>
                                <span>Duração: Aproximadamente 45 minutos</span>
                            </div>
                            <div class="activity-card__detail">
                                <i class="ri-bar-chart-line"></i>
                                <span>Dificuldade: Fácil</span>
                            </div>
                        </div>
                        <p class="activity-card__text">Destaques: Lagoa de S. Pedro d'Arcos, observação de aves, nenúfares em flor e passadiços de madeira. Ideal para famílias e amantes da natureza.</p>  
                    </div>
                </div>

                <div class="activity-card" data-aos="fade-up" data-aos-delay="300">
                    <img src="../assets/images/percurso_do_rio.jpg" alt="Percurso do Rio" class="activity-card__image">
                    <div class="activity-card__content">
                        <h3 class="activity-card__title">Percurso do Rio</h3>
                        <div class="activity-card__details">
                            <div class="activity-card__detail">
                                <i class="ri-map-pin-line"></i>
                                <span>Distância: 2,9 km (circular)</span>
                            </div>
                            <div class="activity-card__detail">
                                <i class="ri-time-line"></i>
                                <span>Duração: Cerca de 1h30</span>
                            </div>
                            <div class="activity-card__detail">
                                <i class="ri-bar-chart-line"></i>
                                <span>Dificuldade: Fácil</span>
                            </div>
                        </div>
                        <p class="activity-card__text">Destaques: Rio Estorãos, bosques autóctones, avifauna e paisagens ribeirinhas. Perfeito para caminhadas relaxantes.</p>
                    </div>
                </div>
                <div class="activity-card" data-aos="fade-up" data-aos-delay="400">
                    <img src="../assets/images/percurso_da_agua.jpg" alt="Percurso da Água" class="activity-card__image">
                    <div class="activity-card__content">
                        <h3 class="activity-card__title">Percurso da Água</h3>
                        <div class="activity-card__details">
                            <div class="activity-card__detail">
                                <i class="ri-map-pin-line"></i>
                                <span>Distância: 12,5 km (circular)</span>
                            </div>
                            <div class="activity-card__detail">
                                <i class="ri-time-line"></i>
                                <span>Duração: Aproximadamente 6 horas</span>
                            </div>
                            <div class="activity-card__detail">
                                <i class="ri-bar-chart-line"></i>
                                <span>Dificuldade: Moderada</span>
                            </div>
                        </div>
                        <p class="activity-card__text">Destaques: Lagoa do Mimoso, Quinta de Pentieiros, pontes históricas e paisagens culturais. Uma imersão na natureza e cultura local.</p>
                    </div>
                </div>

                <div class="activity-card" data-aos="fade-up" data-aos-delay="200">
                    <img src="../assets/images/passadicos_lagoas_bertiandos.jpg" alt="Passadiços e Lagoas de Bertiandos" class="activity-card__image">
                    <div class="activity-card__content">
                        <h3 class="activity-card__title">Passadiços e Lagoas de Bertiandos</h3>
                        <div class="activity-card__details">
                            <div class="activity-card__detail">
                                <i class="ri-map-pin-line"></i>
                                <span>Distância: 6,2 km (circular)</span>
                            </div>
                            <div class="activity-card__detail">
                                <i class="ri-time-line"></i>
                                <span>Duração: Cerca de 2 horas</span>
                            </div>
                            <div class="activity-card__detail">
                                <i class="ri-bar-chart-line"></i>
                                <span>Dificuldade: Baixa</span>
                            </div>
                        </div>
                        <p class="activity-card__text">Destaques: Passadiços de madeira, observação de aves e flora diversificada. Um passeio tranquilo em área protegida.</p>
                    </div>
                </div>

                <div class="activity-card" data-aos="fade-up" data-aos-delay="300">
                    <img src="../assets/images/trilho_das_portelas.jpg" alt="Trilho das Portelas" class="activity-card__image">
                    <div class="activity-card__content">
                        <h3 class="activity-card__title">PR14 - Trilho das Portelas</h3>
                        <div class="activity-card__details">
                            <div class="activity-card__detail">
                                <i class="ri-map-pin-line"></i>
                                <span>Distância: 14,7 km (circular)</span>
                            </div>
                            <div class="activity-card__detail">
                                <i class="ri-time-line"></i>
                                <span>Duração: Cerca de 5 horas</span>
                            </div>
                            <div class="activity-card__detail">
                                <i class="ri-bar-chart-line"></i>
                                <span>Dificuldade: Moderada</span>
                            </div>
                        </div>
                        <p class="activity-card__text">Destaques: Moinhos, riachos, miradouros naturais e vegetação autóctone. Ideal para quem busca uma conexão mais profunda com a natureza.</p>
                    </div>
                </div>

                <div class="activity-card" data-aos="fade-up" data-aos-delay="400">
                    <img src="../assets/images/rota_alto_minho.jpg" alt="Rota das Paisagens Protegidas" class="activity-card__image">
                    <div class="activity-card__content">
                        <h3 class="activity-card__title">Rota das Paisagens Protegidas do Alto Minho</h3>
                        <div class="activity-card__details">
                            <div class="activity-card__detail">
                                <i class="ri-map-pin-line"></i>
                                <span>Distância: Cerca de 100 km (dividida em etapas)</span>
                            </div>
                            <div class="activity-card__detail">
                                <i class="ri-time-line"></i>
                                <span>Duração: Variável</span>
                            </div>
                            <div class="activity-card__detail">
                                <i class="ri-bar-chart-line"></i>
                                <span>Dificuldade: Variada</span>
                            </div>
                        </div>
                        <p class="activity-card__text">Destaques: Conecta áreas protegidas como as Lagoas de Bertiandos, Corno de Bico e Serra d'Arga. Oferece experiências de natureza, cultura e gastronomia.</p>
                    </div>
                </div>
            </div>
        </div>
    </section>
    <!-- Paço de Calheiros Section -->
    <section class="section" id="PacoCalheiros" style="background-color: var(--gray-light);">
        <div class="section__container">
            <h2 class="section-title" data-aos="fade-up">Paço de Calheiros</h2>
            <p class="section-subtitle" data-aos="fade-up" data-aos-delay="100">Um marco cultural e histórico na região de Ponte de Lima</p>

            <div class="activities-grid">
                <div class="activity-card" data-aos="fade-up" data-aos-delay="200">
                    <img src="../assets/images/paco_calheiros.jpg" alt="Paço de Calheiros" class="activity-card__image">
                    <div class="activity-card__content">
                        <h3 class="activity-card__title">Paço de Calheiros</h3>
                        <p class="activity-card__text">Além dos percursos naturais, o Paço de Calheiros é uma atração imperdível. Esta casa senhorial do século XVII oferece vistas panorâmicas sobre o vale do Lima, jardins históricos e experiências de enoturismo. É um excelente ponto de partida para explorar os trilhos da região.</p>
                        <ul class="features-list">
                            <li>Arquitetura histórica do século XVII</li>
                            <li>Jardins paisagísticos com vista para o vale do Lima</li>
                            <li>Experiências de enoturismo com vinhos locais</li>
                            <li>Visitas guiadas à casa senhorial</li>
                            <li>Localização privilegiada para explorar os trilhos da região</li>
                        </ul>
                        
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Gallery Section -->
    <section class="section gallery-section" id="galeria">
        <div class="section__container">
            <h2 class="section-title" data-aos="fade-up">Galeria de Imagens</h2>
            <p class="section-subtitle" data-aos="fade-up" data-aos-delay="100">Momentos capturados nos passeios culturais de Calheiros e Ponte de Lima</p>

            <div class="gallery">
                <div class="gallery-item" data-aos="fade-up" data-aos-delay="100">
                    <img src="../assets/images/lagoa_Spedro_arcos.jpg" alt="Lagoa de S. Pedro d'Arcos">
                    <div class="gallery-caption">Lagoa de S. Pedro d'Arcos</div>
                </div>
                <div class="gallery-item" data-aos="fade-up" data-aos-delay="200">
                    <img src="../assets/images/passadicos.jpg" alt="Passadiços de madeira">
                    <div class="gallery-caption">Passadiços de madeira</div>
                </div>
                <div class="gallery-item" data-aos="fade-up" data-aos-delay="300">
                    <img src="../assets/images/rio_estoraos.jpg" alt="Rio Estorãos">
                    <div class="gallery-caption">Rio Estorãos</div>
                </div>
                <div class="gallery-item" data-aos="fade-up" data-aos-delay="400">
                    <img src="../assets/images/lagoa_mimoso.jpg" alt="Lagoa do Mimoso">
                    <div class="gallery-caption">Lagoa do Mimoso</div>
                </div>
                <div class="gallery-item" data-aos="fade-up" data-aos-delay="500">
                    <img src="../assets/images/ponte_historica.jpg" alt="Ponte histórica">
                    <div class="gallery-caption">Ponte histórica</div>
                </div>
                <div class="gallery-item" data-aos="fade-up" data-aos-delay="600">
                    <img src="../assets/images/passadicos_bertiandos.jpg" alt="Passadiços de Bertiandos">
                    <div class="gallery-caption">Passadiços de Bertiandos</div>
                </div>
                <div class="gallery-item" data-aos="fade-up" data-aos-delay="700">
                    <img src="../assets/images/moinho.avif" alt="Moinhos tradicionais">
                    <div class="gallery-caption">Moinhos tradicionais</div>
                </div>
                <div class="gallery-item" data-aos="fade-up" data-aos-delay="800">
                    <img src="../assets/images/paco_calheiros_vista.jpeg" alt="Vista do Paço de Calheiros">
                    <div class="gallery-caption">Vista do Paço de Calheiros</div>
                </div>
            </div>
        </div>
    </section>

    <!-- Map Section -->
    <section class="section" id="localizacao">
        <div class="section__container">
            <h2 class="section-title" data-aos="fade-up">Localização dos Passeios</h2>
            <p class="section-subtitle" data-aos="fade-up" data-aos-delay="100">Explore no mapa os principais pontos de interesse cultural e natural da região</p>

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

<div class="chatbot-container">
    <div class="chatbot-button" id="chatbotButton">
        <i class="fa-solid fa-comment-dots"></i>
    </div>
    <div class="chatbot-box" id="chatbotBox">
        <div class="chatbot-header">
            <div class="chatbot-title">
                <img src="assets/logos/logotipo1.png" alt="Quinta Flores" class="chatbot-logo">
                <span>Assistente Virtual da Quinta Flores</span>
            </div>
            <button class="chatbot-close" id="chatbotClose">
                <i class="fa-solid fa-xmark"></i>
            </button>
        </div>
        <div class="chatbot-messages" id="chatbotMessages">
            <div class="message bot-message">
                <img src="assets/logos/logotipo1.png" alt="Bot" class="message-avatar">
                <div class="message-content">
                    <p>Olá! Bem-vindo à Quinta Flores. Como posso ajudá-lo hoje?</p>
                </div>
            </div>
        </div>
        <div class="chatbot-input-container">
            <input type="text" id="chatbotInput" class="chatbot-input" placeholder="Digite sua mensagem...">
            <button id="chatbotSend" class="chatbot-send">
                <i class="fa-solid fa-paper-plane"></i>
            </button>
        </div>
        <div class="chatbot-suggestions">
            <button class="suggestion-button">
                <i class="fa-solid fa-calendar-check"></i> Reservas
            </button>
            <button class="suggestion-button">
                <i class="fa-solid fa-bed"></i> Acomodações
            </button>
            <button class="suggestion-button">
                <i class="fa-solid fa-bell-concierge"></i> Serviços
            </button>
            <button class="suggestion-button">
                <i class="fa-solid fa-map-location-dot"></i> Localização
            </button>
            <button class="suggestion-button">
                <i class="fa-solid fa-person-hiking"></i> Atividades
            </button>
            <button class="suggestion-button">
                <i class="fa-solid fa-euro-sign"></i> Preços
            </button>
        </div>
        <div class="chatbot-footer">
            <span>Quinta Flores - ChatBot</span>
        </div>
    </div>
</div>
<script>
    document.addEventListener('DOMContentLoaded', function() {
        const chatbotButton = document.getElementById('chatbotButton');
        const chatbotBox = document.getElementById('chatbotBox');
        const chatbotClose = document.getElementById('chatbotClose');
        const chatbotInput = document.getElementById('chatbotInput');
        const chatbotSend = document.getElementById('chatbotSend');
        const chatbotMessages = document.getElementById('chatbotMessages');
        const suggestionButtons = document.querySelectorAll('.suggestion-button');

        // Mostrar chatbot box
        chatbotButton.addEventListener('click', function() {
            chatbotBox.style.display = 'flex';
            chatbotButton.style.display = 'none';
        });

        // Fechar chatbot box
        chatbotClose.addEventListener('click', function() {
            chatbotBox.style.display = 'none';
            chatbotButton.style.display = 'flex';
        });

        // Enviar mensagem ao pressionar Enter
        chatbotInput.addEventListener('keypress', function(e) {
            if (e.key === 'Enter') {
                sendMessage();
            }
        });

        // Enviar mensagem ao clicar no botão
        chatbotSend.addEventListener('click', sendMessage);

        // Botões de sugestão
        suggestionButtons.forEach(button => {
            button.addEventListener('click', function() {
                const text = this.textContent.trim();
                chatbotInput.value = text;
                sendMessage();
            });
        });

        // Respostas do chatbot
        const responses = {
            saudacao: "Bem-vindo à Quinta Flores. Em que podemos ser úteis?",
            agradecimento: "Obrigado pelo seu contacto. Ficamos ao dispor para qualquer questão relacionada com a Quinta Flores ou com a região de Ponte de Lima. Desejamos-lhe uma excelente estadia connosco.",
            despedida: "Agradecemos o seu contacto. Esperamos ter o prazer de o receber brevemente na Quinta Flores. Votos de um excelente dia.",
            reservas: {
                geral: "Para efetuar uma reserva na Quinta Flores, dispõe das seguintes opções:\n\n• Utilize o botão 'Reservar Agora' disponível no topo da página\n• Contacte-nos através do número: +351 912 418 976\n• Ou visite-nos presencialmente mediante agendamento.",
                cancelamento: "A nossa política de cancelamento é flexível:\n\n• Cancelamentos até 7 dias antes da data de chegada – reembolso total\n• Cancelamentos entre 3 e 7 dias – taxa de 30%\n• Cancelamentos com menos de 3 dias – taxa de 50% do valor total da reserva.",
                alteracao: "As alterações à reserva estão sujeitas a disponibilidade. Recomendamos que entre em contacto connosco com a maior antecedência possível para verificarmos as alternativas disponíveis.",
                disponibilidade: "Para consultar a disponibilidade para datas específicas, utilize o formulário na página inicial ou contacte-nos diretamente.",
                antecedencia: "Durante a época alta (junho a setembro) e em períodos festivos, recomendamos que efetue a sua reserva com 1 a 2 meses de antecedência."
            },
            acomodacoes: {
                casaprincipal: "A Casa Principal é nossa maior acomodação com 3 quartos com 5 camas de casal, 3 casas de banho, sala de estar espaçosa, cozinha completa e varanda com vista para os jardins.",
                geral: "Oferecemos acomodações confortáveis e bem equipadas. A Casa Principal comporta até 10 pessoas com todos os confortos necessários para uma estadia perfeita."
            },
            precos: {
                geral: "A Quinta Flores apresenta um valor fixo de 120€ por noite, com capacidade máxima até 10 pessoas. Para eventos ou ocasiões especiais com número superior de participantes, solicitamos que entre em contacto connosco previamente."
            },
            servicos: {
                geral: "A Quinta Flores disponibiliza diversos serviços pensados para proporcionar uma estadia confortável e memorável:\n\n• Receção disponível das 08h00 às 22h00\n• Piscina exterior com zona de solário\n• Estacionamento privativo gratuito\n• Jardins e zonas de lazer",
                piscina: "A nossa piscina exterior encontra-se acessível diariamente. Dispõe de zona de solário com espreguiçadeiras e toalhas disponibilizadas gratuitamente aos hóspedes.",
                wifi: "Disponibilizamos Wi-Fi gratuito de alta velocidade em toda a propriedade, incluindo nas zonas exteriores. A palavra-passe será fornecida no momento do check-in.",
                limpeza: "O serviço de limpeza é sempre feito antes e depois da estadia. Caso pretenda limpeza diária, poderá ser solicitado por um valor adicional de 15€ por dia.",
                recepcao: "A receção está disponível entre as 08h00 e as 22h00. Para chegadas fora deste horário, temos ao dispor um sistema de check-in automatizado, mediante pedido prévio."
            },
            localizacao: {
                geral: "A Quinta Flores está situada a cerca de 3 km do centro histórico de Ponte de Lima, oferecendo um ambiente calmo e campestre com fácil acesso às principais atrações da região.",
                como_chegar: "Como chegar à Quinta Flores:\n\n• De carro: pela A3, tome a saída para Ponte de Lima e siga em direção a Arcozelo. Após aproximadamente 2,5 km, encontrará sinalização com a nossa identificação à direita.",
                arredores: "Nas proximidades da Quinta Flores poderá explorar vinícolas de Vinho Verde, percursos pedestres, atividades no Rio Lima e restaurantes típicos da gastronomia minhota.",
                estacionamento: "Disponibilizamos estacionamento privado e gratuito dentro da propriedade, com capacidade para todos os nossos hóspedes."
            },
            atividades: {
                geral: "A região do Minho oferece inúmeras atividades: passeios de bicicleta, degustação de vinhos, caminhadas, passeios a cavalo, canoagem no Rio Lima e visitas culturais. Se tiver interesse pode ver no nosso site mais atividades que pode fazer perto da Quinta Flores.",
                cicloturismo: "Dispomos ainda de várias rotas para descobrir as paisagens únicas da região.",
                gastronomia: "O Minho é famoso por sua gastronomia. Recomendamos restaurantes autênticos nas proximidades.",
                criancas: "Para famílias com crianças, recomendamos: caça ao tesouro em nossos jardins, visita ao parque aventura, piqueniques à beira-rio e passeios de barco no Rio Lima."
            },
            fallback: "Sou um assistente virtual da Quinta Flores. Peço desculpa, mas não consegui compreender corretamente a sua pergunta. Poderia reformulá-la ou especificar melhor, por favor?"
        };

        // Função principal para enviar mensagem
        function sendMessage() {
            const message = chatbotInput.value.trim();
            if (message === '') return;

            // Adicionar mensagem do usuário
            addMessage(message, 'user');
            chatbotInput.value = '';

            // Simular digitação do bot
            showTypingIndicator();

            // Processar resposta com um pequeno delay
            setTimeout(() => {
                removeTypingIndicator();
                const response = getResponse(message);
                addMessage(response, 'bot');
                chatbotMessages.scrollTop = chatbotMessages.scrollHeight;
            }, 1000 + Math.random() * 1000);
        }

        // Função para adicionar mensagem à conversa
        function addMessage(text, sender) {
            const messageDiv = document.createElement('div');
            messageDiv.className = `message ${sender}-message`;
            
            let avatar;
            if (sender === 'bot') {
                avatar = document.createElement('img');
                avatar.src = 'assets/logos/logotipo1.png';
                avatar.alt = 'Bot';
                avatar.className = 'message-avatar';
            } else {
                avatar = document.createElement('div');
                avatar.className = 'message-avatar';
                avatar.style.backgroundColor = '#8CB58E';
                avatar.style.display = 'flex';
                avatar.style.justifyContent = 'center';
                avatar.style.alignItems = 'center';
                avatar.style.color = 'white';
                avatar.style.fontWeight = 'bold';
                avatar.textContent = 'EU';
            }

            const contentDiv = document.createElement('div');
            contentDiv.className = 'message-content';
            contentDiv.innerHTML = formatMessageText(text);

            if (sender === 'user') {
                messageDiv.appendChild(contentDiv);
                messageDiv.appendChild(avatar);
            } else {
                messageDiv.appendChild(avatar);
                messageDiv.appendChild(contentDiv);
            }

            chatbotMessages.appendChild(messageDiv);
        }

        // Função para formatar o texto da mensagem
        function formatMessageText(text) {
            return text.replace(/\n/g, '<br>');
        }

        // Função para mostrar indicador de digitação
        function showTypingIndicator() {
            const typingDiv = document.createElement('div');
            typingDiv.className = 'message bot-message typing-message';
            
            const avatar = document.createElement('img');
            avatar.src = 'assets/logos/logotipo1.png';
            avatar.alt = 'Bot';
            avatar.className = 'message-avatar';
            
            const typingIndicator = document.createElement('div');
            typingIndicator.className = 'typing-indicator';
            for (let i = 0; i < 3; i++) {
                const dot = document.createElement('span');
                typingIndicator.appendChild(dot);
            }
            
            typingDiv.appendChild(avatar);
            typingDiv.appendChild(typingIndicator);
            chatbotMessages.appendChild(typingDiv);
            chatbotMessages.scrollTop = chatbotMessages.scrollHeight;
        }

        // Função para remover indicador de digitação
        function removeTypingIndicator() {
            const typingMessage = document.querySelector('.typing-message');
            if (typingMessage) {
                typingMessage.remove();
            }
        }

        // Função para determinar a resposta adequada (versão corrigida)
        function getResponse(message) {
            const lowercaseMessage = message.toLowerCase();
            
            // 1. Verificar despedidas (mais específico)
            if (/(adeus|tchau|até logo|até mais|até breve|goodbye|bye|até à próxima|ate a proxima)/i.test(lowercaseMessage)) {
                return responses.despedida;
            }
            
            // 2. Verificar agradecimentos explícitos
            if (/(obrigado|obrigada|agradecido|agradecida|thanks|thank you|grato|grata|muito obrigado|muito obrigada)\b/i.test(lowercaseMessage)) {
                return responses.agradecimento;
            }
            
            // 3. Verificar saudações
            if (/(olá|ola|oi|bom dia|boa tarde|boa noite|hello|hi|hey|saudações|saudacoes)\b/i.test(lowercaseMessage)) {
                return responses.saudacao;
            }
            
            // 4. Verificar reservas
            if (/(reserva|reservar|booking|alugar|disponibilidade|marcar|fazer reserva|agendar|quero reservar)\b/i.test(lowercaseMessage)) {
                if (/(cancelar|cancelamento|anular|desmarcar|cancelada|cancelar reserva)\b/i.test(lowercaseMessage)) {
                    return responses.reservas.cancelamento;
                } else if (/(alterar|alteração|mudar|modificar|trocar|alterar reserva)\b/i.test(lowercaseMessage)) {
                    return responses.reservas.alteracao;
                } else if (/(disponível|disponibilidade|tem vaga|vagas|datas livres|datas disponíveis)\b/i.test(lowercaseMessage)) {
                    return responses.reservas.disponibilidade;
                } else if (/(antecedência|antecedencia|com antecedência|quando reservar|prazo para reservar|tempo antes)\b/i.test(lowercaseMessage)) {
                    return responses.reservas.antecedencia;
                } else {
                    return responses.reservas.geral;
                }
            }
            
            // 5. Verificar acomodações
            if (/(acomodação|acomodacoes|quarto|quartos|casa|alojamento|hospedagem|suite|suíte)\b/i.test(lowercaseMessage)) {
                if (/(casa principal|principal|casa mãe|principal casa)\b/i.test(lowercaseMessage)) {
                    return responses.acomodacoes.casaprincipal;
                } else {
                    return responses.acomodacoes.geral;
                }
            }
            
            // 6. Verificar preços
            if (/(preço|preco|preços|precos|valor|valores|custo|quanto custa|tarifa|taxa|preço por noite)\b/i.test(lowercaseMessage)) {
                return responses.precos.geral;
            }
            
            // 7. Verificar serviços
            if (/(serviço|servico|facilidade|comodidade|serviços|comodidades|infraestrutura)\b/i.test(lowercaseMessage)) {
                if (/(piscina|nadar|piscinas|área de lazer aquática)\b/i.test(lowercaseMessage)) {
                    return responses.servicos.piscina;
                } else if (/(wifi|internet|wi-fi|rede|conexão|conexao)\b/i.test(lowercaseMessage)) {
                    return responses.servicos.wifi;
                } else if (/(limpeza|arrumação|arrumacao|faxina|serviço de limpeza)\b/i.test(lowercaseMessage)) {
                    return responses.servicos.limpeza;
                } else if (/(recepção|recepcao|atendimento|balcão|front desk)\b/i.test(lowercaseMessage)) {
                    return responses.servicos.recepcao;
                } else {
                    return responses.servicos.geral;
                }
            }
            
            // 8. Verificar localização
            if (/(localização|localizacao|endereço|endereco|onde fica|como chegar|morada|situação|direção|direcao)\b/i.test(lowercaseMessage)) {
                if (/(como chegar|chegar|direções|direcoes|rota|caminho|instruções|instrucoes|acesso)\b/i.test(lowercaseMessage)) {
                    return responses.localizacao.como_chegar;
                } else if (/(arredores|proximidade|perto|próximo|proximo|vizinhança|vizinhanca|área|região|regiao)\b/i.test(lowercaseMessage)) {
                    return responses.localizacao.arredores;
                } else if (/(estacionamento|parque|carro|vaga|garagem|parking)\b/i.test(lowercaseMessage)) {
                    return responses.localizacao.estacionamento;
                } else {
                    return responses.localizacao.geral;
                }
            }
            
            // 9. Verificar atividades
            if (/(atividade|atividades|fazer|lazer|passeio|passeios|entretenimento|diversão|diversao|programa)\b/i.test(lowercaseMessage)) {
                if (/(bicicleta|bike|cicloturismo|bicicletas|ciclismo|andar de bicicleta)\b/i.test(lowercaseMessage)) {
                    return responses.atividades.cicloturismo;
                } else if (/(comida|gastronomia|comer|restaurante|culinária|culinaria|prato|refeição|refeicao)\b/i.test(lowercaseMessage)) {
                    return responses.atividades.gastronomia;
                } else if (/(criança|criancas|família|familia|kids|crianças|famílias|familias|filhos|filha|filho)\b/i.test(lowercaseMessage)) {
                    return responses.atividades.criancas;
                } else {
                    return responses.atividades.geral;
                }
            }
            
            // 10. Se nenhuma das condições acima for atendida
            return responses.fallback;
        }
    });
</script>    
    <!-- Scripts -->
    <script src="https://unpkg.com/aos@next/dist/aos.js"></script>
    <script>
        // Inicializar AOS (Animate On Scroll)
        AOS.init({
            duration: 800,
            once: true
        });

        // Menu móvel
        document.addEventListener('DOMContentLoaded', () => {
            const hamburger = document.getElementById('hamburger');
            const navLinks = document.getElementById('navLinks');
            const header = document.getElementById('header');
            const navItems = document.querySelectorAll('.nav__link');

            // Função para controlar o menu
            hamburger.addEventListener('click', () => {
                navLinks.classList.toggle('active');
                hamburger.innerHTML = navLinks.classList.contains('active') 
                    ? '<i class="ri-close-line"></i>' 
                    : '<i class="ri-menu-line"></i>';
            });

            // Fechar menu ao clicar em um link
            navItems.forEach(item => {
                item.addEventListener('click', () => {
                    navLinks.classList.remove('active');
                    hamburger.innerHTML = '<i class="ri-menu-line"></i>';
                });
            });

            // Mudar estilo do header ao rolar
            window.addEventListener('scroll', () => {
                if (window.scrollY > 100) {
                    header.classList.add('scrolled');
                } else {
                    header.classList.remove('scrolled');
                }
            });

            // Função para alternar o tema
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

            // Inicializar tema baseado na preferência salva
            const savedTheme = localStorage.getItem('theme') || 'light';
            const themeToggle = document.getElementById('themeToggle');
            const icon = themeToggle.querySelector('i');
            
            document.documentElement.setAttribute('data-theme', savedTheme);
            icon.className = savedTheme === 'dark' ? 'ri-moon-line' : 'ri-sun-line';
            
            themeToggle.addEventListener('click', toggleTheme);
        });
    </script>
    <?php include '../components/footer.php'; ?>
</body>
</html>