<?php
$nav_links = [
    ['href' => '#trilhos', 'text' => 'Trilhos'],
    ['href' => '#Localizacao', 'text' => 'Localização'],
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
    <title>Trilhos e Natureza | Quinta Flores - Ponte de Lima</title>
    <link rel="icon" type="image/png" href="../assets/logos/logotipo1.png" sizes="1000x1000">
    <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@400;500;600;700&family=Montserrat:wght@300;400;500;600&display=swap" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" rel="stylesheet">

    <link href="https://cdn.jsdelivr.net/npm/remixicon@4.0.0/fonts/remixicon.css" rel="stylesheet">
    <link rel="stylesheet" href="https://unpkg.com/aos@next/dist/aos.css" />
    <link rel="stylesheet" href="chatbot.css" />
    <link rel="stylesheet" href="../components/header.css">
    <link rel="stylesheet" href="../components/footer.css">

    <style>
        :root {
            --primary-color: #10B981;
            --primary-color-dark: #047857;
            --primary-color-light: #D1FAE5;
            --accent-color: #FCD34D;
            --text-dark: #111827;
            --text-light: #6B7280;
            --white: #F9FAFB;
            --gray-light: #F3F4F6;
            --transition: all 0.3s ease-in-out;
            --shadow: 0 4px 20px rgba(0,0,0,0.1);
            --shadow-hover: 0 10px 25px rgba(0,0,0,0.15);
            --border-radius: 12px;
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: "Montserrat", sans-serif;
            line-height: 1.6;
            color: var(--text-dark);
            overflow-x: hidden;
            scroll-behavior: smooth;
        }

        h1, h2, h3, h4, h5 {
            font-family: "Playfair Display", serif;
            font-weight: 700;
        }
        /* Hero Section */
        .hero {
            height: 100vh;
            width: 100%;
            background: linear-gradient(rgba(0,0,0,0.3), rgba(0,0,0,0.3)), url('../assets/images/trilhos_e_natureza.avif');
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
    <!-- Cabeçalho -->
    <!-- Hero Section -->
    <section class="hero">
        <div class="hero__content">
            <h1 class="hero__title">Trilhos e Natureza</h1>
            <p class="hero__subtitle">Descubra os caminhos mais deslumbrantes da região de Ponte de Lima</p>
            <a href="../login1/pagina_login.php" class="hero__cta">Reservar Agora</a>
        </div>
        <a href="#trilhos" class="scroll-down">
            <i class="ri-arrow-down-s-line"></i>
        </a>
    </section>
    <!-- Trilhos Section -->
    <section class="section" id="trilhos">
        <div class="section__container">
            <h2 class="section-title" data-aos="fade-up">Trilhos na Natureza</h2>
            <p class="section-subtitle" data-aos="fade-up" data-aos-delay="100">Explore os melhores percursos naturais da região de Ponte de Lima e arredores</p>

            <div class="activities-grid">
                <div class="activity-card" data-aos="fade-up" data-aos-delay="200">
                    <img src="../assets/images/trilho_dos_moinhos.jpg" alt="Trilho dos Moinhos" class="activity-card__image">
                    <div class="activity-card__content">
                        <h3 class="activity-card__title">Trilho dos Moinhos (Ponte de Lima)</h3>
                        <div class="activity-card__details">
                            <div class="activity-card__detail">
                                <i class="ri-map-pin-line"></i>
                                <span>Distância: 12 km (circular)</span>
                            </div>
                            <div class="activity-card__detail">
                                <i class="ri-time-line"></i>
                                <span>Duração: 3-4 horas</span>
                            </div>
                            <div class="activity-card__detail">
                                <i class="ri-bar-chart-line"></i>
                                <span>Dificuldade: Fácil a moderada</span>
                            </div>
                        </div>
                        <p class="activity-card__text">Este trilho circular é bastante popular e leva os visitantes ao longo de uma zona de vegetação densa, atravessando moinhos antigos e rios. É uma ótima forma de conhecer a natureza local e a história da região.</p>
                        <ul class="features-list">
                            <li>Vistas deslumbrantes do Rio Lima</li>
                            <li>Moinhos de água tradicionais</li>
                            <li>Diversidade de fauna e flora locais</li>
                            <li>Percurso bem sinalizado</li>
                        </ul>
                    </div>
                </div>

                <div class="activity-card" data-aos="fade-up" data-aos-delay="300">
                    <img src="../assets/images/trilho_da_ribeira.webp" alt="Trilho da Ribeira de Calheiros" class="activity-card__image">
                    <div class="activity-card__content">
                        <h3 class="activity-card__title">Trilho da Ribeira de Calheiros</h3>
                        <div class="activity-card__details">
                            <div class="activity-card__detail">
                                <i class="ri-map-pin-line"></i>
                                <span>Distância: 6 km</span>
                            </div>
                            <div class="activity-card__detail">
                                <i class="ri-time-line"></i>
                                <span>Duração: 2 horas</span>
                            </div>
                            <div class="activity-card__detail">
                                <i class="ri-bar-chart-line"></i>
                                <span>Dificuldade: Fácil</span>
                            </div>
                        </div>
                        <p class="activity-card__text">Este é um dos trilhos de curta distância que passa pela Ribeira de Calheiros. Durante o percurso, os visitantes podem observar a fauna local e desfrutar da tranquilidade das águas do rio.</p>
                        <ul class="features-list">
                            <li>Paisagens ribeirinhas encantadoras</li>
                            <li>Ótima oportunidade para observação de aves</li>
                            <li>Vegetação diversificada da região</li>
                            <li>Percurso acessível para todas as idades</li>
                        </ul>
                    </div>
                </div>

                <div class="activity-card" data-aos="fade-up" data-aos-delay="400">
                    <img src="../assets/images/peneda_geres.avif" alt="Parque Nacional da Peneda-Gerês" class="activity-card__image">
                    <div class="activity-card__content">
                        <h3 class="activity-card__title">Parque Nacional da Peneda-Gerês</h3>
                        <div class="activity-card__details">
                            <div class="activity-card__detail">
                                <i class="ri-map-pin-line"></i>
                                <span>Distância: Variável (próximo de Ponte de Lima)</span>
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
                        <p class="activity-card__text">Embora não seja em Ponte de Lima, o Parque Nacional da Peneda-Gerês está a uma curta distância de lá. Oferece trilhos mais longos e desafiantes, incluindo o famoso Trilho de Mata da Albergaria.</p>
                        <ul class="features-list">
                            <li>Paisagens montanhosas deslumbrantes</li>
                            <li>Cascatas e rios cristalinos</li>
                            <li>Fauna selvagem (veados, águias, lobos)</li>
                            <li>Aldeias tradicionais preservadas</li>
                            <li>Maior bosque de carvalhos e castanheiros da região</li>
                        </ul>
                    </div>
                </div>

                <div class="activity-card" data-aos="fade-up" data-aos-delay="200">
                    <img src="../assets/images/serra_arga.webp" alt="Trilho da Serra de Arga" class="activity-card__image">
                    <div class="activity-card__content">
                        <h3 class="activity-card__title">Trilho da Serra de Arga</h3>
                        <div class="activity-card__details">
                            <div class="activity-card__detail">
                                <i class="ri-map-pin-line"></i>
                                <span>Distância: Variável</span>
                            </div>
                            <div class="activity-card__detail">
                                <i class="ri-time-line"></i>
                                <span>Duração: Variável</span>
                            </div>
                            <div class="activity-card__detail">
                                <i class="ri-bar-chart-line"></i>
                                <span>Dificuldade: Moderada a difícil</span>
                            </div>
                        </div>
                        <p class="activity-card__text">Este trilho leva os visitantes à Serra de Arga, uma montanha de grande beleza natural, situada um pouco fora de Ponte de Lima. A serra é famosa pela sua biodiversidade e pela vista deslumbrante que proporciona.</p>
                        <ul class="features-list">
                            <li>Vistas panorâmicas impressionantes</li>
                            <li>Rica fauna selvagem</li>
                            <li>Vegetação diversificada</li>
                            <li>Fontes de água naturais</li>
                        </ul>
                    </div>
                </div>

                <div class="activity-card" data-aos="fade-up" data-aos-delay="300">
                    <img src="../assets/images/rio_vez.jpg" alt="Trilho do Rio Vez" class="activity-card__image">
                    <div class="activity-card__content">
                        <h3 class="activity-card__title">Trilho do Rio Vez</h3>
                        <div class="activity-card__details">
                            <div class="activity-card__detail">
                                <i class="ri-map-pin-line"></i>
                                <span>Distância: Variável</span>
                            </div>
                            <div class="activity-card__detail">
                                <i class="ri-time-line"></i>
                                <span>Duração: Variável</span>
                            </div>
                            <div class="activity-card__detail">
                                <i class="ri-bar-chart-line"></i>
                                <span>Dificuldade: Fácil a moderada</span>
                            </div>
                        </div>
                        <p class="activity-card__text">Este é outro trilho de beleza ímpar, que segue ao longo do Rio Vez, perto de Ponte da Barca (também na região do Lima, perto de Ponte de Lima). O trilho oferece uma combinação de paisagens ribeirinhas e vegetação exuberante.</p>
                        <ul class="features-list">
                            <li>Beleza natural do Rio Vez</li>
                            <li>Zonas de banho naturais</li>
                            <li>Pequenas pontes de madeira pitorescas</li>
                            <li>Vegetação ribeirinha exuberante</li>
                        </ul>
                    </div>
                </div>

                <div class="activity-card" data-aos="fade-up" data-aos-delay="400">
                    <img src="../assets/images/estuario.jpg" alt="Reserva Natural do Estuário do Rio Lima" class="activity-card__image">
                    <div class="activity-card__content">
                        <h3 class="activity-card__title">Reserva Natural do Estuário do Rio Lima</h3>
                        <div class="activity-card__details">
                            <div class="activity-card__detail">
                                <i class="ri-map-pin-line"></i>
                                <span>Distância: Variável</span>
                            </div>
                            <div class="activity-card__detail">
                                <i class="ri-time-line"></i>
                                <span>Duração: Variável</span>
                            </div>
                            <div class="activity-card__detail">
                                <i class="ri-bar-chart-line"></i>
                                <span>Dificuldade: Fácil</span>
                            </div>
                        </div>
                        <p class="activity-card__text">Para quem gosta de observação de aves e vida selvagem, esta área protegida é um ótimo local para explorar a natureza local. Existem trilhos e passadiços que permitem aos visitantes percorrerem áreas de grande beleza natural junto ao rio.</p>
                        <ul class="features-list">
                            <li>Excelente para observação de aves</li>
                            <li>Vegetação ripícola diversificada</li>
                            <li>Estuário do Rio Lima</li>
                            <li>Passadiços bem conservados</li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>
    </section>
    <!-- Trilhos Adicionais Section -->
    <section class="section" style="background-color: var(--gray-light);">
        <div class="section__container">
            <h2 class="section-title" data-aos="fade-up">Mais Trilhos para Explorar</h2>
            <p class="section-subtitle" data-aos="fade-up" data-aos-delay="100">Descubra outros percursos naturais imperdíveis na região</p>

            <div class="activities-grid">
                <div class="activity-card" data-aos="fade-up" data-aos-delay="200">
                    <img src="../assets/images/senhora_guia.jpg" alt="Trilho da Senhora da Guia" class="activity-card__image">
                    <div class="activity-card__content">
                        <h3 class="activity-card__title">Trilho da Senhora da Guia</h3>
                        <div class="activity-card__details">
                            <div class="activity-card__detail">
                                <i class="ri-map-pin-line"></i>
                                <span>Distância: Curta</span>
                            </div>
                            <div class="activity-card__detail">
                                <i class="ri-time-line"></i>
                                <span>Duração: 1 hora</span>
                            </div>
                            <div class="activity-card__detail">
                                <i class="ri-bar-chart-line"></i>
                                <span>Dificuldade: Fácil</span>
                            </div>
                        </div>
                        <p class="activity-card__text">Este é um trilho de curta distância e de fácil acesso, que leva até um miradouro com uma vista fantástica sobre a região de Ponte de Lima e o Rio Lima. A caminhada é tranquila e oferece momentos de calma e relaxamento.</p>
                        <ul class="features-list">
                            <li>Vista panorâmica da região</li>
                            <li>Capela da Senhora da Guia</li>
                            <li>Paisagens deslumbrantes</li>
                            <li>Acesso fácil e rápido</li>
                        </ul>
                    </div>
                </div>

                <div class="activity-card" data-aos="fade-up" data-aos-delay="300">
                    <img src="../assets/images/rota_miradouros.jpg" alt="Rota dos Miradouros" class="activity-card__image">
                    <div class="activity-card__content">
                        <h3 class="activity-card__title">Rota dos Miradouros</h3>
                        <div class="activity-card__details">
                            <div class="activity-card__detail">
                                <i class="ri-map-pin-line"></i>
                                <span>Distância: Variável</span>
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
                        <p class="activity-card__text">Esta rota inclui vários miradouros ao longo de trilhos que permitem aos visitantes ter vistas panorâmicas de toda a região de Ponte de Lima e dos seus arredores. É possível incluir diferentes percursos que vão de fáceis a mais desafiantes.</p>
                        <ul class="features-list">
                            <li>Vistas deslumbrantes de Ponte de Lima</li>
                            <li>Panorâmicas do Rio Lima</li>
                            <li>Vistas da serra circundante</li>
                            <li>Opções para todos os níveis de dificuldade</li>
                        </ul>
                        
                    </div>
                </div>
            </div>
        </div>
    </section>
                <!-- Map Section -->
        <section class="section" id="Localizacao">
            <div class="section__container">
                <h2 class="section-title" data-aos="fade-up">Localização dos Trilhos</h2>
                <p class="section-subtitle" data-aos="fade-up" data-aos-delay="100">Explore no mapa os principais trilhos naturais da região</p>
        
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
        <!--CHATBOT-->
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
        <script src="chatbot.js"></script>
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