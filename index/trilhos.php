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
    <link rel="stylesheet" href="lermais.css" />
    <link rel="stylesheet" href="../components/header.css">
    <link rel="stylesheet" href="../components/footer.css">
</head>
<body>
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
</div>
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
        <link rel="stylesheet" href="../chatbot/chatbot.css">
<script src="../chatbot/chatbot.js"></script>
<?php include '../chatbot/chatbot_config.php'; ?>
<?php include '../chatbot/chatbot.php'; ?>
        </body>
        </html>