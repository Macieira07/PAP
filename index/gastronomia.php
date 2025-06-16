<?php 
require_once '../i18n.php';
if (isset($_GET['lang'])) {
    I18n::setLanguage($_GET['lang']);
}
$nav_links = [
    ['href' => '#sobre', 'text' => I18n::get('gastronomia_nav_sobre', 'Sobre')],
    ['href' => '#galeria', 'text' => I18n::get('gastronomia_nav_galeria', 'Galeria')],
    ['href' => '../login1/pagina_login.php', 'text' => I18n::get('gastronomia_nav_reservar', 'Reservar'), 'class' => 'nav__cta'],
];

include '../components/header.php'; 
?>
<html lang="<?= I18n::getCurrentLanguage() ?>" data-theme="light">
<head>
    <meta charset="UTF-8">
    <link rel="icon" type="image/png" sizes="32x32" href="../assets/logos/favicon-32x32.png">
    <link rel="icon" type="image/png" sizes="16x16" href="../assets/logos/favicon-16x16.png">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= I18n::get('gastronomia_meta_title', 'Gastronomia de Ponte de Lima | Quinta Flores') ?></title>
    <link rel="icon" type="image/png" href="../assets/logos/logotipo1.png" sizes="1000x1000">
    <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@400;500;600;700&family=Montserrat:wght@300;400;500;600&display=swap" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/remixicon@4.0.0/fonts/remixicon.css" rel="stylesheet">
    <link rel="stylesheet" href="https://unpkg.com/aos@next/dist/aos.css" />
    <link rel="stylesheet" href="gastronomia.css" />
    <link rel="stylesheet" href="../components/header.css">
</head>
<body>
    <div class="nav__container">
        <div class="logo">
            <a href="../index.html"><img src="../assets/logos/logotipo1.png" alt="Quinta Flores"></a>
        </div>
        
        <ul class="nav__links" id="navLinks">
            <?php foreach ($nav_links as $link): ?>
                <li>
                    <a href="<?php echo $link['href']; ?>" <?php if(isset($link['class'])) echo 'class="' . $link['class'] . '"'; ?>>
                        <?php echo $link['text']; ?>
                    </a>
                </li>
            <?php endforeach; ?>
        </ul>

        <button class="hamburger" id="hamburger">
            <i class="ri-menu-line"></i>
        </button>
    </div>
            </section>  

    <!-- Hero Section -->
    <section class="hero">
        <div class="hero__content">
            <h1 class="hero__title"><?= I18n::get('gastronomia_hero_title', 'Gastronomia de Ponte de Lima') ?></h1>
            <p class="hero__subtitle"><?= I18n::get('gastronomia_hero_subtitle', 'Capital do Sarrabulho e berço dos sabores autênticos do Alto Minho') ?></p>
        </div>
    </section>
    <!-- Main Content -->
    <section class="section" id="sobre">
        <div class="section__container">
            <h2 class="section-title" data-aos="fade-up"><?= I18n::get('gastronomia_sobre_title', 'Tesouros Gastronômicos Limianos') ?></h2>
            <p class="section-subtitle" data-aos="fade-up" data-aos-delay="100"><?= I18n::get('gastronomia_sobre_subtitle', 'Descubra os pratos que fazem de Ponte de Lima a capital gastronômica do Alto Minho') ?></p>

            <div class="content-grid">
                <div class="info-card" data-aos="fade-up" data-aos-delay="200">
                    <i class="ri-goblet-line info-card__icon"></i>
                    <h3><?= I18n::get('gastronomia_vinhos_title', 'Vinhos Verdes do Vale do Lima') ?></h3>
                    <p><?= I18n::get('gastronomia_vinhos_text', 'Ponte de Lima é coração da sub-região vitivinícola do Vale do Lima, famosa pelos vinhos verdes de casta Loureiro - frescos, aromáticos e com notas cítricas. Em 2025, a região foi reconhecida como "European Region of Gastronomy and Wine", destacando a excelência dos vinhos locais como o "Loureiro do Vale do Lima". Visite adegas tradicionais e participe de degustações guiadas.') ?></p>
                </div>
                <div class="info-card" data-aos="fade-up" data-aos-delay="300">
                    <i class="ri-restaurant-2-line info-card__icon"></i>
                    <h3><?= I18n::get('gastronomia_sarrabulho_title', 'Arroz de Sarrabulho') ?></h3>
                    <p><?= I18n::get('gastronomia_sarrabulho_text', 'O ex-líbris da gastronomia limiana, este prato tradicional leva arroz, sangue de porco, várias carnes e é temperado com cominhos. Durante a Feira do Porco e Delícias do Sarrabulho, servem-se mais de 10.000 doses em 50 restaurantes locais. O Clube de Gastronomia de Ponte de Lima já levou esta iguaria para eventos em Bruxelas, Paris e até Winnipeg, no Canadá.') ?></p>
                </div>
                <div class="info-card" data-aos="fade-up" data-aos-delay="400">
                    <i class="ri-cake-3-line info-card__icon"></i>
                    <h3><?= I18n::get('gastronomia_lampreia_title', 'Lampreia à Bordalesa') ?></h3>
                    <p><?= I18n::get('gastronomia_lampreia_text', 'Prato sazonal (entre janeiro e abril) que utiliza a lampreia do rio Lima, preparada com vinho tinto e pão de milho. Tradicionalmente servido nas feiras quinzenais da vila, é um dos pratos mais emblemáticos da região. A lampreia é pescada no rio Lima e preparada seguindo receitas centenárias que atraem gourmets de todo o país.') ?></p>
                </div>
            </div>
            <div class="content-grid">
                <div class="info-card" data-aos="fade-up" data-aos-delay="500">
                    <i class="ri-plant-line info-card__icon"></i>
                    <h3><?= I18n::get('gastronomia_bacalhau_title', 'Bacalhau de Cebolada') ?></h3>
                    <p><?= I18n::get('gastronomia_bacalhau_text', 'Herança das feiras tradicionais de Ponte de Lima, este prato mantém-se nos cardápios das tabernas e restaurantes locais. O bacalhau é cozinhado com cebola, alho, louro e azeite, resultando num sabor intenso e reconfortante. Recentemente, o Chef Paulo Santos recriou a receita histórica do "Bacalhau à Eça de Queirós", resgatando tradições culinárias.') ?></p>
                </div>

                <div class="info-card" data-aos="fade-up" data-aos-delay="600">
                    <i class="ri-community-line info-card__icon"></i>
                    <h3><?= I18n::get('gastronomia_naco_title', 'Naco de Minhota') ?></h3>
                    <p><?= I18n::get('gastronomia_naco_text', 'Prato típico da região, feito com carne de vaca mirandesa (raça autóctone) marinada em vinho verde e alhos, depois grelhada. Acompanha batata cozida e grelos. Em Ponte de Lima, o "Naco à Moda de Bertiandos" é uma variação famosa, com molho especial à base de vinho verde Loureiro.') ?></p>
                </div>

                <div class="info-card" data-aos="fade-up" data-aos-delay="700">
                    <i class="ri-store-2-line info-card__icon"></i>
                    <h3><?= I18n::get('gastronomia_feiras_title', 'Feiras e Mercados') ?></h3>
                    <p><?= I18n::get('gastronomia_feiras_text', 'A Feira Quinzenal de Ponte de Lima (desde 1125) e o Mercado Municipal são locais imperdíveis para provar iguarias locais. Não perca a Feira do Porco (março) e o Festival Gastronómico do Arroz de Sarrabulho (novembro), onde pode provar estas especialidades preparadas pelos melhores chefs da região.') ?></p>
                </div>
            </div>
        </div>
    </section>
    <!-- Gallery Section -->
    <section class="section gallery-section">
        <div class="section__container">
            <h2 class="section-title" data-aos="fade-up"><?= I18n::get('gastronomia_galeria_title', 'Sabores de Ponte de Lima') ?></h2>
            <p class="section-subtitle" data-aos="fade-up" data-aos-delay="100"><?= I18n::get('gastronomia_galeria_subtitle', 'Conheça visualmente as delícias que tornam esta região única') ?></p>

            <div class="gallery" id="galeria">
                <div class="gallery-item" data-aos="fade-up" data-aos-delay="100">
                    <img src="../assets/images/arroz_sarrabulho.jpg" alt="Arroz de Sarrabulho">
                    <div class="gallery-caption"><?= I18n::get('gastronomia_galeria_sarrabulho', 'Arroz de Sarrabulho - Prato emblemático') ?></div>
                </div>
                <div class="gallery-item" data-aos="fade-up" data-aos-delay="200">
                    <img src="../assets/images/lampreia.jpg" alt="Lampreia à Bordalesa">
                    <div class="gallery-caption"><?= I18n::get('gastronomia_galeria_lampreia', 'Lampreia do Rio Lima') ?></div>
                </div>
                <div class="gallery-item" data-aos="fade-up" data-aos-delay="300">
                    <img src="../uvas-verdes-com-camada-de-vinho-sobre-gesso-e-tecido_176474-10660.avif" alt="Vinhos Verdes">
                    <div class="gallery-caption"><?= I18n::get('gastronomia_galeria_vinhos', 'Vinhos Verdes do Vale do Lima') ?></div>
                </div>
                <div class="gallery-item" data-aos="fade-up" data-aos-delay="400">
                    <img src="../assets/images/leite-creme.webp" alt="Leite Creme">
                    <div class="gallery-caption"><?= I18n::get('gastronomia_galeria_leite_creme', 'Leite Creme queimado') ?></div>
                </div>
                <div class="gallery-item" data-aos="fade-up" data-aos-delay="500">
                    <img src="../assets/images/naco_de_minhota.jpg" alt="Naco de Minhota">
                    <div class="gallery-caption"><?= I18n::get('gastronomia_galeria_naco', 'Naco de Minhota') ?></div>
                </div>
                <div class="gallery-item" data-aos="fade-up" data-aos-delay="600">
                    <img src="../assets/images/bacalhau.jpg" alt="Doçaria Conventual">
                    <div class="gallery-caption"><?= I18n::get('gastronomia_galeria_bacalhau', 'Bacalhau') ?></div>
                </div>
            </div>
        </div>
    </section>

<!-- Scripts -->
     <script>
    // Configura os event listeners para os botões de idioma
    function setupLanguageButtons() {
        // Para botões desktop
        document.querySelectorAll('.language-btn').forEach(btn => {
            btn.addEventListener('click', function() {
                const lang = this.getAttribute('data-lang');
                changeLanguage(lang);
            });
        });
    
        // Configuração do dropdown para mobile
        const dropdownBtn = document.getElementById('langDropdownBtn');
        if (dropdownBtn) {
            dropdownBtn.addEventListener('click', function() {
                document.getElementById('langDropdown').classList.toggle('show');
            });
        }
        // Fecha o dropdown ao clicar fora
        window.addEventListener('click', function(e) {
            if (!e.target.matches('.language-dropdown-btn') && !e.target.matches('.language-dropdown-btn *')) {
                const dropdown = document.getElementById('langDropdown');
                if (dropdown && dropdown.classList.contains('show')) {
                    dropdown.classList.remove('show');
                }
            }
        });
    }
    // Função para trocar o idioma
    function changeLanguage(lang) {
        console.log('Changing language to: ' + lang);
        
        // Atualiza os botões ativos
        document.querySelectorAll('.language-btn').forEach(btn => {
            btn.classList.remove('active');
            if (btn.getAttribute('data-lang') === lang) {
                btn.classList.add('active');
            }
        });
        // Atualiza a bandeira no menu dropdown
        const currentFlag = document.getElementById('currentLangFlag');
        if (currentFlag) {
            switch(lang) {
                case 'pt':
                    currentFlag.src = 'https://flagcdn.com/w20/pt.png';
                    break;
                case 'en':
                    currentFlag.src = 'https://flagcdn.com/w20/gb.png';
                    break;
                case 'es':
                    currentFlag.src = 'https://flagcdn.com/w20/es.png';
                    break;
                case 'fr':
                    currentFlag.src = 'https://flagcdn.com/w20/fr.png';
                    break;
            }
        }
        // Tenta encontrar e alterar o seletor do Google Translate
        try {
            // Espera o elemento estar disponível - múltiplas tentativas
            const selectGoogleElement = () => {
                // O seletor pode estar em vários locais dependendo da versão do Google Translate
                const select = document.querySelector('.goog-te-combo') || 
                              document.querySelector('.VIpgJd-ZVi9od-xl07Ob-lTBxed');
                
                if (select) {
                    select.value = lang;
                    // Aciona o evento change para o Google Translate detectar a mudança
                    const event = new Event('change', { bubbles: true });
                    select.dispatchEvent(event);
                    
                    // Salva a preferência
                    localStorage.setItem('preferredLanguage', lang);
                    console.log('Language changed successfully to: ' + lang);
                } else {
                    console.log('Google Translate element not found, retrying...');
                    // Tenta novamente após um curto intervalo
                    setTimeout(selectGoogleElement, 500);
                }
            };
            
            // Inicia a busca pelo elemento
            selectGoogleElement();
            
        } catch (error) {
            console.error('Error changing language:', error);
        }
    
        // Fecha o dropdown se estiver aberto
        const dropdown = document.getElementById('langDropdown');
        if (dropdown && dropdown.classList.contains('show')) {
            dropdown.classList.remove('show');
        }
    }
</script>
    <script src="https://unpkg.com/aos@next/dist/aos.js"></script>
    <script>
        // Initialize AOS (Animate On Scroll)
        AOS.init({
            duration: 800,
            once: true,
            easing: 'ease-in-out'
        });

        // Mobile Navigation Toggle
        const hamburger = document.getElementById('hamburger');
        const navLinks = document.getElementById('navLinks');

        hamburger.addEventListener('click', () => {
            navLinks.classList.toggle('active');
            hamburger.innerHTML = navLinks.classList.contains('active') 
                ? '<i class="ri-close-line"></i>' 
                : '<i class="ri-menu-line"></i>';
        });

        // Header Scroll Effect
        const header = document.getElementById('header');
        window.addEventListener('scroll', () => {
            if (window.scrollY > 50) {
                header.classList.add('scrolled');
            } else {
                header.classList.remove('scrolled');
            }
        });

        // Theme Toggle
        const themeToggle = document.getElementById('themeToggle');
        const themeIcon = document.getElementById('themeIcon');
        const html = document.documentElement;

        // Check for saved theme preference
        const currentTheme = localStorage.getItem('theme') || 'light';
        html.setAttribute('data-theme', currentTheme);
        themeIcon.className = currentTheme === 'dark' ? 'ri-sun-line' : 'ri-moon-line';

        themeToggle.addEventListener('click', () => {
            const theme = html.getAttribute('data-theme');
            const newTheme = theme === 'light' ? 'dark' : 'light';
            
            html.setAttribute('data-theme', newTheme);
            localStorage.setItem('theme', newTheme);
            themeIcon.className = newTheme === 'dark' ? 'ri-sun-line' : 'ri-moon-line';
        });

        // Smooth scrolling for navigation links
        document.querySelectorAll('a[href^="#"]').forEach(anchor => {
            anchor.addEventListener('click', function(e) {
                e.preventDefault();
                
                const targetId = this.getAttribute('href');
                if (targetId === '#') return;
                
                const targetElement = document.querySelector(targetId);
                if (targetElement) {
                    window.scrollTo({
                        top: targetElement.offsetTop - 80,
                        behavior: 'smooth'
                    });
                    
                    // Close mobile menu if open
                    if (navLinks.classList.contains('active')) {
                        navLinks.classList.remove('active');
                        hamburger.innerHTML = '<i class="ri-menu-line"></i>';
                    }
                }
            });
        });
    </script>
    <?php include '../components/footer.php'; ?>
    <link rel="stylesheet" href="../chatbot/chatbot.css">
<script src="../chatbot/chatbot.js"></script>
<?php include '../chatbot/chatbot_config.php'; ?>
<?php include '../chatbot/chatbot.php'; ?>
</body>
</html>