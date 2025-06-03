<?php 
$nav_links = [
    ['href' => '#sobre', 'text' => 'Sobre'],
    ['href' => '#galeria', 'text' => 'Galeria'],
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
    <title>Gastronomia de Ponte de Lima | Quinta Flores</title>
    <link rel="icon" type="image/png" href="../assets/logos/logotipo1.png" sizes="1000x1000">
    <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@400;500;600;700&family=Montserrat:wght@300;400;500;600&display=swap" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" rel="stylesheet">

    <link href="https://cdn.jsdelivr.net/npm/remixicon@4.0.0/fonts/remixicon.css" rel="stylesheet">
    <link rel="stylesheet" href="https://unpkg.com/aos@next/dist/aos.css" />
    <link rel="stylesheet" href="chatbot.css" />
    <link rel="stylesheet" href="../components/header.css">
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

        /* Header & Navigation */
        .header {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            z-index: 1000;
            background-color: rgba(255,255,255,0.95);
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
            transition: var(--transition);
        }

        .header.scrolled {
            padding: 0.5rem 0;
            background-color: rgba(255,255,255,0.98);
        }

        .nav__container {
            max-width: 1300px;
            margin: 0 auto;
            padding: 1rem 2rem;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .logo {
            display: flex;
            align-items: center;
        }

        .logo img {
            height: 50px;
            transition: var(--transition);
        }

        .header.scrolled .logo img {
            height: 40px;
        }

        .nav__links {
            display: flex;
            gap: 2rem;
            list-style: none;
        }

        .nav__link {
            color: var(--text-dark);
            text-decoration: none;
            font-weight: 500;
            font-size: 1rem;
            transition: var(--transition);
            position: relative;
        }

        .nav__link:after {
            content: '';
            position: absolute;
            width: 0;
            height: 2px;
            bottom: -5px;
            left: 0;
            background-color: var(--primary-color);
            transition: var(--transition);
        }

        .nav__link:hover:after {
            width: 100%;
        }

        .nav__cta {
            padding: 0.7rem 1.5rem;
            background-color: var(--primary-color);
            color: white;
            border-radius: 30px;
            font-weight: 600;
            transition: var(--transition);
        }

        .nav__cta:hover {
            background-color: var(--primary-color-dark);
            transform: translateY(-3px);
        }

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
            height: 60vh;
            width: 100%;
            background: linear-gradient(rgba(0,0,0,0.5), rgba(0,0,0,0.5)), url('../assets/images/gastronomia.jpg');
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

        /* Content Styles */
        .content-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
            gap: 2rem;
            margin-bottom: 4rem;
        }

        .info-card {
            background: var(--white);
            border-radius: var(--border-radius);
            padding: 2rem;
            box-shadow: var(--shadow);
            transition: var(--transition);
            height: 100%;
        }

        .info-card:hover {
            transform: translateY(-8px);
            box-shadow: var(--shadow-hover);
        }

        .info-card__icon {
            font-size: 2.5rem;
            color: var(--primary-color);
            margin-bottom: 1rem;
        }

        .info-card h3 {
            color: var(--primary-color-dark);
            margin-bottom: 1rem;
            font-size: 1.5rem;
        }

        .info-card p {
            color: var(--text-dark);
            line-height: 1.7;
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

        /* Footer */
        .footer {
            background-color: #1a1a1a;
            color: var(--white);
            padding: 4rem 2rem 2rem;
        }

        .footer__container {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 2rem;
            margin-bottom: 3rem;
        }

        .footer__col h4 {
            color: var(--white);
            margin-bottom: 1.5rem;
            font-size: 1.3rem;
        }

        .footer__links {
            list-style: none;
            padding: 0;
        }

        .footer__link {
            margin-bottom: 0.8rem;
        }

        .footer__link a {
            color: #a3a3a3;
            text-decoration: none;
            transition: var(--transition);
        }

        .footer__link a:hover {
            color: var(--primary-color);
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

        .footer__bar {
            text-align: center;
            padding-top: 2rem;
            border-top: 1px solid #333;
            color: #a3a3a3;
            font-size: 0.9rem;
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

        .theme-toggle:hover {
            transform: scale(1.1);
        }

        /* Animations */
        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(20px); }
            to { opacity: 1; transform: translateY(0); }
        }

        /* Dark Mode */
        [data-theme="dark"] {
            background-color: #121212;
            color: #e0e0e0;
        }

        [data-theme="dark"] .header {
            background-color: rgba(18, 18, 18, 0.95);
            box-shadow: 0 2px 10px rgba(0,0,0,0.3);
        }

        [data-theme="dark"] .nav__link {
            color: #e0e0e0;
        }

        [data-theme="dark"] .info-card {
            background: #1e1e1e;
            color: #e0e0e0;
        }

        [data-theme="dark"] .gallery-section {
            background-color: #121212;
        }

        [data-theme="dark"] .info-card h3 {
            color: var(--primary-color);
        }

        [data-theme="dark"] .section-title {
            color: var(--primary-color);
        }

        [data-theme="dark"] .section-subtitle,
        [data-theme="dark"] .info-card p {
            color: #b0b0b0;
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

<!-- Language dropdown for mobile -->

        <div class="nav__container">
            <div class="logo">
                <a href="../index.html"><img src="../assets/logos/logotipo1.png" alt="Quinta Flores"></a>
            </div>
            
            <ul class="nav__links" id="navLinks">

            <button class="hamburger" id="hamburger">
                <i class="ri-menu-line"></i>
            </button>
        </div>
    </header>





    <!-- Hero Section -->
    <section class="hero">
        <div class="hero__content">
            <h1 class="hero__title">Gastronomia de Ponte de Lima</h1>
            <p class="hero__subtitle">Capital do Sarrabulho e berço dos sabores autênticos do Alto Minho</p>
        </div>
    </section>




    <!-- Main Content -->
    <section class="section" id="sobre">
        <div class="section__container">
            <h2 class="section-title" data-aos="fade-up">Tesouros Gastronômicos Limianos</h2>
            <p class="section-subtitle" data-aos="fade-up" data-aos-delay="100">Descubra os pratos que fazem de Ponte de Lima a capital gastronômica do Alto Minho</p>

            <div class="content-grid">
                <div class="info-card" data-aos="fade-up" data-aos-delay="200">
                    <i class="ri-goblet-line info-card__icon"></i>
                    <h3>Vinhos Verdes do Vale do Lima</h3>
                    <p>Ponte de Lima é coração da sub-região vitivinícola do Vale do Lima, famosa pelos vinhos verdes de casta Loureiro - frescos, aromáticos e com notas cítricas. Em 2025, a região foi reconhecida como "European Region of Gastronomy and Wine", destacando a excelência dos vinhos locais como o "Loureiro do Vale do Lima". Visite adegas tradicionais e participe de degustações guiadas.</p>
                </div>

                <div class="info-card" data-aos="fade-up" data-aos-delay="300">
                    <i class="ri-restaurant-2-line info-card__icon"></i>
                    <h3>Arroz de Sarrabulho</h3>
                    <p>O ex-líbris da gastronomia limiana, este prato tradicional leva arroz, sangue de porco, várias carnes e é temperado com cominhos. Durante a Feira do Porco e Delícias do Sarrabulho, servem-se mais de 10.000 doses em 50 restaurantes locais. O Clube de Gastronomia de Ponte de Lima já levou esta iguaria para eventos em Bruxelas, Paris e até Winnipeg, no Canadá.</p>
                </div>

                <div class="info-card" data-aos="fade-up" data-aos-delay="400">
                    <i class="ri-cake-3-line info-card__icon"></i>
                    <h3>Lampreia à Bordalesa</h3>
                    <p>Prato sazonal (entre janeiro e abril) que utiliza a lampreia do rio Lima, preparada com vinho tinto e pão de milho. Tradicionalmente servido nas feiras quinzenais da vila, é um dos pratos mais emblemáticos da região. A lampreia é pescada no rio Lima e preparada seguindo receitas centenárias que atraem gourmets de todo o país.</p>
                </div>
            </div>

            <div class="content-grid">
                <div class="info-card" data-aos="fade-up" data-aos-delay="500">
                    <i class="ri-plant-line info-card__icon"></i>
                    <h3>Bacalhau de Cebolada</h3>
                    <p>Herança das feiras tradicionais de Ponte de Lima, este prato mantém-se nos cardápios das tabernas e restaurantes locais. O bacalhau é cozinhado com cebola, alho, louro e azeite, resultando num sabor intenso e reconfortante. Recentemente, o Chef Paulo Santos recriou a receita histórica do "Bacalhau à Eça de Queirós", resgatando tradições culinárias.</p>
                </div>

                <div class="info-card" data-aos="fade-up" data-aos-delay="600">
                    <i class="ri-community-line info-card__icon"></i>
                    <h3>Naco de Minhota</h3>
                    <p>Prato típico da região, feito com carne de vaca mirandesa (raça autóctone) marinada em vinho verde e alhos, depois grelhada. Acompanha batata cozida e grelos. Em Ponte de Lima, o "Naco à Moda de Bertiandos" é uma variação famosa, com molho especial à base de vinho verde Loureiro.</p>
                </div>

                <div class="info-card" data-aos="fade-up" data-aos-delay="700">
                    <i class="ri-store-2-line info-card__icon"></i>
                    <h3>Feiras e Mercados</h3>
                    <p>A Feira Quinzenal de Ponte de Lima (desde 1125) e o Mercado Municipal são locais imperdíveis para provar iguarias locais. Não perca a Feira do Porco (março) e o Festival Gastronómico do Arroz de Sarrabulho (novembro), onde pode provar estas especialidades preparadas pelos melhores chefs da região.</p>
                </div>
            </div>
        </div>
    </section>






    <!-- Gallery Section -->
    <section class="section gallery-section">
        <div class="section__container">
            <h2 class="section-title" data-aos="fade-up">Sabores de Ponte de Lima</h2>
            <p class="section-subtitle" data-aos="fade-up" data-aos-delay="100">Conheça visualmente as delícias que tornam esta região única</p>

            <div class="gallery" id="galeria">
                <div class="gallery-item" data-aos="fade-up" data-aos-delay="100">
                    <img src="../assets/images/arroz_sarrabulho.jpg" alt="Arroz de Sarrabulho">
                    <div class="gallery-caption">Arroz de Sarrabulho - Prato emblemático</div>
                </div>
                <div class="gallery-item" data-aos="fade-up" data-aos-delay="200">
                    <img src="../assets/images/lampreia.jpg" alt="Lampreia à Bordalesa">
                    <div class="gallery-caption">Lampreia do Rio Lima</div>
                </div>
                <div class="gallery-item" data-aos="fade-up" data-aos-delay="300">
                    <img src="../uvas-verdes-com-camada-de-vinho-sobre-gesso-e-tecido_176474-10660.avif" alt="Vinhos Verdes">
                    <div class="gallery-caption">Vinhos Verdes do Vale do Lima</div>
                </div>
                <div class="gallery-item" data-aos="fade-up" data-aos-delay="400">
                    <img src="../assets/images/leite-creme.webp" alt="Leite Creme">
                    <div class="gallery-caption">Leite Creme queimado</div>
                </div>
                <div class="gallery-item" data-aos="fade-up" data-aos-delay="500">
                    <img src="../assets/images/naco_de_minhota.jpg" alt="Naco de Minhota">
                    <div class="gallery-caption">Naco de Minhota</div>
                </div>
                <div class="gallery-item" data-aos="fade-up" data-aos-delay="600">
                    <img src="../assets/images/bacalhau.jpg" alt="Doçaria Conventual">
                    <div class="gallery-caption">Bacalhau</div>
                </div>
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
     <script>
    // Sistema de tradução com Google Translate
    document.addEventListener('DOMContentLoaded', function() {
        // Cria o elemento do Google Translate
        const googleElement = document.createElement('div');
        googleElement.id = 'google_translate_element';
        googleElement.style.visibility = 'hidden'; // Usando visibility em vez de display para manter o elemento renderizado
        googleElement.style.position = 'absolute';
        googleElement.style.top = '-1000px'; // Posiciona fora da tela em vez de esconder completamente
        document.body.appendChild(googleElement);
    
        // Carrega o script do Google Translate
        loadGoogleTranslate();
    
        // Configura os listeners para os botões de idioma
        setupLanguageButtons();
    
        // Verifica se há preferência de idioma salva
        const savedLang = localStorage.getItem('preferredLanguage');
        if (savedLang) {
            // Aguarda um tempo para o Google Translate inicializar
            setTimeout(() => {
                changeLanguage(savedLang);
            }, 2000); // Tempo maior para garantir que o Google Translate esteja carregado
        }
    });
    
    // Carrega o script do Google Translate
    function loadGoogleTranslate() {
        const script = document.createElement('script');
        script.type = 'text/javascript';
        script.src = 'https://translate.google.com/translate_a/element.js?cb=googleTranslateElementInit';
        document.body.appendChild(script);
    }
    
    // Inicializa o Google Translate (deve ser global para o callback funcionar)
    window.googleTranslateElementInit = function() {
        new google.translate.TranslateElement({
            pageLanguage: 'pt',
            includedLanguages: 'en,es,fr,pt',
            layout: google.translate.TranslateElement.InlineLayout.SIMPLE,
            autoDisplay: false
        }, 'google_translate_element');
        
        // Verifica se há preferência de idioma após inicialização
        const savedLang = localStorage.getItem('preferredLanguage');
        if (savedLang) {
            setTimeout(() => {
                changeLanguage(savedLang);
            }, 500);
        }
    };
    
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
    <script src="chatbot.js"></script>
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
</body>
</html>