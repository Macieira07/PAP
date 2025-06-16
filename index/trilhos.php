<?php
require_once '../i18n.php';
// Initialize the translation system
I18n::init();
$nav_links = [
    ['href' => '#trilhos', 'text' => I18n::get('nav_trilhos', 'Trilhos')],
    ['href' => '#Localizacao', 'text' => I18n::get('nav_localizacao', 'Localização')],
    ['href' => '../login1/pagina_login.php', 'text' => I18n::get('nav_reservar', 'Reservar'), 'class' => 'nav__cta'],
]; 
// Set the language based on the URL parameter if provided
if (isset($_GET['lang'])) {
    I18n::setLanguage($_GET['lang']);
}
include '../components/header.php'; ?>
<!DOCTYPE html>
<html lang="pt" data-theme="light">
<head>
    <meta charset="UTF-8">
        <link rel="icon" type="image/png" sizes="32x32" href="../assets/logos/favicon-32x32.png">
<link rel="icon" type="image/png" sizes="16x16" href="../assets/logos/favicon-16x16.png">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo I18n::get('page_title', 'Trilhos e Natureza | Quinta Flores - Ponte de Lima'); ?></title>
    <link rel="icon" type="image/png" href="../assets/logos/logotipo1.png" sizes="1000x1000">
    <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@400;500;600;700&family=Montserrat:wght@300;400;500;600&display=swap" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/remixicon@4.0.0/fonts/remixicon.css" rel="stylesheet">
    <link rel="stylesheet" href="https://unpkg.com/aos@next/dist/aos.css" />
    <link rel="stylesheet" href="lermais.css" />
    <link rel="stylesheet" href="../components/header.css">
    <link rel="stylesheet" href="../components/footer.css">
    <link rel="stylesheet" href="../assets/css/style.css">
    <link rel="stylesheet" href="trilhos.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
</head>
<body>
    <!-- Hero Section -->
    <section class="hero">
        <div class="hero__content">
            <h1 class="hero__title"><?php echo I18n::get('hero_title', 'Trilhos e Natureza'); ?></h1>
            <p class="hero__subtitle"><?php echo I18n::get('hero_subtitle', 'Descubra os caminhos mais deslumbrantes da região de Ponte de Lima'); ?></p>
            <a href="../login1/pagina_login.php" class="hero__cta"><?php echo I18n::get('hero_cta', 'Reservar Agora'); ?></a>
        </div>
        <a href="#trilhos" class="scroll-down">
            <i class="ri-arrow-down-s-line"></i>
        </a>
    </section>
    <!-- Trilhos Section -->
    <section class="section" id="trilhos">
        <div class="section__container">
            <h2 class="section-title" data-aos="fade-up"><?php echo I18n::get('section_title', 'Nossos Trilhos'); ?></h2>
            <p class="section-subtitle" data-aos="fade-up" data-aos-delay="100"><?php echo I18n::get('section_subtitle', 'Explore as melhores trilhas da região'); ?></p>

            <div class="activities-grid">
                <div class="activity-card" data-aos="fade-up" data-aos-delay="200">
                    <img src="../assets/images/trilho_dos_moinhos.jpg" alt="Trilho dos Moinhos" class="activity-card__image">
                    <div class="activity-card__content">
                        <h3 class="activity-card__title"><?php echo I18n::get('moinhos_title', 'Trilho dos Moinhos'); ?></h3>
                        <div class="activity-card__details">
                            <div class="activity-card__detail">
                                <i class="ri-map-pin-line"></i>
                                <span><?php echo I18n::get('moinhos_distance', '5 km'); ?></span>
                            </div>
                            <div class="activity-card__detail">
                                <i class="ri-time-line"></i>
                                <span><?php echo I18n::get('moinhos_duration', '2 horas'); ?></span>
                            </div>
                            <div class="activity-card__detail">
                                <i class="ri-bar-chart-line"></i>
                                <span><?php echo I18n::get('moinhos_difficulty', 'Fácil'); ?></span>
                            </div>
                        </div>
                        <p class="activity-card__text"><?php echo I18n::get('moinhos_description', 'Um percurso encantador que leva você através de antigos moinhos de água e paisagens deslumbrantes.'); ?></p>
                        <ul class="features-list">
                            <?php foreach (I18n::get('moinhos_features', ['Paisagens deslumbrantes', 'Moinhos históricos', 'Natureza preservada']) as $feature): ?>
                                <li><?php echo $feature; ?></li>
                            <?php endforeach; ?>
                        </ul>
                    </div>
                </div>
                <div class="activity-card" data-aos="fade-up" data-aos-delay="300">
                    <img src="../assets/images/trilho_da_ribeira.webp" alt="Trilho da Ribeira de Calheiros" class="activity-card__image">
                    <div class="activity-card__content">
                        <h3 class="activity-card__title"><?php echo I18n::get('ribeira_title'); ?></h3>
                        <div class="activity-card__details">
                            <div class="activity-card__detail">
                                <i class="ri-map-pin-line"></i>
                                <span><?php echo I18n::get('ribeira_distance'); ?></span>
                            </div>
                            <div class="activity-card__detail">
                                <i class="ri-time-line"></i>
                                <span><?php echo I18n::get('ribeira_duration'); ?></span>
                            </div>
                            <div class="activity-card__detail">
                                <i class="ri-bar-chart-line"></i>
                                <span><?php echo I18n::get('ribeira_difficulty'); ?></span>
                            </div>
                        </div>
                        <p class="activity-card__text"><?php echo I18n::get('ribeira_description'); ?></p>
                        <ul class="features-list">
                            <?php foreach (I18n::get('ribeira_features') as $feature): ?>
                                <li><?php echo $feature; ?></li>
                            <?php endforeach; ?>
                        </ul>
                    </div>
                </div>

                <div class="activity-card" data-aos="fade-up" data-aos-delay="400">
                    <img src="../assets/images/peneda_geres.avif" alt="Parque Nacional da Peneda-Gerês" class="activity-card__image">
                    <div class="activity-card__content">
                        <h3 class="activity-card__title"><?php echo I18n::get('geres_title'); ?></h3>
                        <div class="activity-card__details">
                            <div class="activity-card__detail">
                                <i class="ri-map-pin-line"></i>
                                <span><?php echo I18n::get('geres_distance'); ?></span>
                            </div>
                            <div class="activity-card__detail">
                                <i class="ri-time-line"></i>
                                <span><?php echo I18n::get('geres_duration'); ?></span>
                            </div>
                            <div class="activity-card__detail">
                                <i class="ri-bar-chart-line"></i>
                                <span><?php echo I18n::get('geres_difficulty'); ?></span>
                            </div>
                        </div>
                        <p class="activity-card__text"><?php echo I18n::get('geres_description'); ?></p>
                        <ul class="features-list">
                            <?php foreach (I18n::get('geres_features') as $feature): ?>
                                <li><?php echo $feature; ?></li>
                            <?php endforeach; ?>
                        </ul>
                    </div>
                </div>

                <div class="activity-card" data-aos="fade-up" data-aos-delay="200">
                    <img src="../assets/images/serra_arga.webp" alt="Trilho da Serra de Arga" class="activity-card__image">
                    <div class="activity-card__content">
                        <h3 class="activity-card__title"><?php echo I18n::get('arga_title'); ?></h3>
                        <div class="activity-card__details">
                            <div class="activity-card__detail">
                                <i class="ri-map-pin-line"></i>
                                <span><?php echo I18n::get('arga_distance'); ?></span>
                            </div>
                            <div class="activity-card__detail">
                                <i class="ri-time-line"></i>
                                <span><?php echo I18n::get('arga_duration'); ?></span>
                            </div>
                            <div class="activity-card__detail">
                                <i class="ri-bar-chart-line"></i>
                                <span><?php echo I18n::get('arga_difficulty'); ?></span>
                            </div>
                        </div>
                        <p class="activity-card__text"><?php echo I18n::get('arga_description'); ?></p>
                        <ul class="features-list">
                            <?php foreach (I18n::get('arga_features') as $feature): ?>
                                <li><?php echo $feature; ?></li>
                            <?php endforeach; ?>
                        </ul>
                    </div>
                </div>

                <div class="activity-card" data-aos="fade-up" data-aos-delay="300">
                    <img src="../assets/images/rio_vez.jpg" alt="Trilho do Rio Vez" class="activity-card__image">
                    <div class="activity-card__content">
                        <h3 class="activity-card__title"><?php echo I18n::get('vez_title'); ?></h3>
                        <div class="activity-card__details">
                            <div class="activity-card__detail">
                                <i class="ri-map-pin-line"></i>
                                <span><?php echo I18n::get('vez_distance'); ?></span>
                            </div>
                            <div class="activity-card__detail">
                                <i class="ri-time-line"></i>
                                <span><?php echo I18n::get('vez_duration'); ?></span>
                            </div>
                            <div class="activity-card__detail">
                                <i class="ri-bar-chart-line"></i>
                                <span><?php echo I18n::get('vez_difficulty'); ?></span>
                            </div>
                        </div>
                        <p class="activity-card__text"><?php echo I18n::get('vez_description'); ?></p>
                        <ul class="features-list">
                            <?php foreach (I18n::get('vez_features') as $feature): ?>
                                <li><?php echo $feature; ?></li>
                            <?php endforeach; ?>
                        </ul>
                    </div>
                </div>

                <div class="activity-card" data-aos="fade-up" data-aos-delay="400">
                    <img src="../assets/images/estuario.jpg" alt="Reserva Natural do Estuário do Rio Lima" class="activity-card__image">
                    <div class="activity-card__content">
                        <h3 class="activity-card__title"><?php echo I18n::get('estuario_title'); ?></h3>
                        <div class="activity-card__details">
                            <div class="activity-card__detail">
                                <i class="ri-map-pin-line"></i>
                                <span><?php echo I18n::get('estuario_distance'); ?></span>
                            </div>
                            <div class="activity-card__detail">
                                <i class="ri-time-line"></i>
                                <span><?php echo I18n::get('estuario_duration'); ?></span>
                            </div>
                            <div class="activity-card__detail">
                                <i class="ri-bar-chart-line"></i>
                                <span><?php echo I18n::get('estuario_difficulty'); ?></span>
                            </div>
                        </div>
                        <p class="activity-card__text"><?php echo I18n::get('estuario_description'); ?></p>
                        <ul class="features-list">
                            <?php foreach (I18n::get('estuario_features') as $feature): ?>
                                <li><?php echo $feature; ?></li>
                            <?php endforeach; ?>
                        </ul>
                    </div>
                </div>
            </div>
        </div>
    </section>
    <!-- Trilhos Adicionais Section -->
    <section class="section" style="background-color: var(--gray-light);">
        <div class="section__container">
            <h2 class="section-title" data-aos="fade-up"><?php echo I18n::get('more_trails_title'); ?></h2>
            <p class="section-subtitle" data-aos="fade-up" data-aos-delay="100"><?php echo I18n::get('more_trails_subtitle'); ?></p>

            <div class="activities-grid">
                <div class="activity-card" data-aos="fade-up" data-aos-delay="200">
                    <img src="../assets/images/senhora_guia.jpg" alt="Trilho da Senhora da Guia" class="activity-card__image">
                    <div class="activity-card__content">
                        <h3 class="activity-card__title"><?php echo I18n::get('guia_title'); ?></h3>
                        <div class="activity-card__details">
                            <div class="activity-card__detail">
                                <i class="ri-map-pin-line"></i>
                                <span><?php echo I18n::get('guia_distance'); ?></span>
                            </div>
                            <div class="activity-card__detail">
                                <i class="ri-time-line"></i>
                                <span><?php echo I18n::get('guia_duration'); ?></span>
                            </div>
                            <div class="activity-card__detail">
                                <i class="ri-bar-chart-line"></i>
                                <span><?php echo I18n::get('guia_difficulty'); ?></span>
                            </div>
                        </div>
                        <p class="activity-card__text"><?php echo I18n::get('guia_description'); ?></p>
                        <ul class="features-list">
                            <?php foreach (I18n::get('guia_features') as $feature): ?>
                                <li><?php echo $feature; ?></li>
                            <?php endforeach; ?>
                        </ul>
                    </div>
                </div>

                <div class="activity-card" data-aos="fade-up" data-aos-delay="300">
                    <img src="../assets/images/rota_miradouros.jpg" alt="Rota dos Miradouros" class="activity-card__image">
                    <div class="activity-card__content">
                        <h3 class="activity-card__title"><?php echo I18n::get('miradouros_title'); ?></h3>
                        <div class="activity-card__details">
                            <div class="activity-card__detail">
                                <i class="ri-map-pin-line"></i>
                                <span><?php echo I18n::get('miradouros_distance'); ?></span>
                            </div>
                            <div class="activity-card__detail">
                                <i class="ri-time-line"></i>
                                <span><?php echo I18n::get('miradouros_duration'); ?></span>
                            </div>
                            <div class="activity-card__detail">
                                <i class="ri-bar-chart-line"></i>
                                <span><?php echo I18n::get('miradouros_difficulty'); ?></span>
                            </div>
                        </div>
                        <p class="activity-card__text"><?php echo I18n::get('miradouros_description'); ?></p>
                        <ul class="features-list">
                            <?php foreach (I18n::get('miradouros_features') as $feature): ?>
                                <li><?php echo $feature; ?></li>
                            <?php endforeach; ?>
                        </ul>
                    </div>
                </div>
            </div>
        </div>
    </section>
    <!-- Map Section -->
    <section class="section" id="Localizacao">
        <div class="section__container">
            <h2 class="section-title" data-aos="fade-up"><?php echo I18n::get('location_title'); ?></h2>
            <p class="section-subtitle" data-aos="fade-up" data-aos-delay="100"><?php echo I18n::get('location_subtitle'); ?></p>
    
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