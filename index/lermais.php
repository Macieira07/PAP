<?php

/*
 * ============================================================
 *   Página Ler Mais - Quinta Flores
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
 *     1. Configuração inicial PHP (i18n, includes)
 *     2. <head> com meta tags, fontes, ícones, CSS
 *     3. Hero Section (destaque)
 *     4. Sobre Section (história, missão, valores)
 *     5. Alojamento Section (detalhes do espaço)
 *     6. Atividades Section (experiências)
 *     7. Galeria Section (fotos)
 *     8. Testemunhos Section
 *     9. FAQ Section
 *    10. Localização Section
 *    11. Contacto Section
 *    12. Footer e scripts finais (tema, chatbot)
 *
 *   Autor: [Seu Nome ou Equipa]
 *   Última atualização: [Data]
 * ============================================================
 */
// ===================== 1. Configuração Inicial PHP =====================
require_once '../i18n.php';
if (isset($_GET['lang'])) {
    I18n::setLanguage($_GET['lang']);
}
$nav_links = [
    ['href' => '#about', 'text' => 'Sobre'],
    ['href' => '#accommodation', 'text' => 'Acomodações'],
    ['href' => '#activities', 'text' => 'Experiências'],
    ['href' => '#gallery', 'text' => 'Galeria'],
    ['href' => '#testimonials', 'text' => 'Avaliações'],
    ['href' => '#localizacao', 'text' => 'Localização'],
    ['href' => '../login1/pagina_login.php', 'text' => 'Reservar', 'class' => 'nav__cta'],
];
include '../components/header.php';
?>
<!DOCTYPE html>
<html lang="<?= I18n::getCurrentLanguage() ?>" data-theme="light">
<head>
    <meta charset="UTF-8">
        <link rel="icon" type="image/png" sizes="32x32" href="../assets/logos/favicon-32x32.png">
<link rel="icon" type="image/png" sizes="16x16" href="../assets/logos/favicon-16x16.png">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= I18n::get('lermais_meta_title', 'Quinta Flores | Experiência Única em Ponte de Lima') ?></title>
    <link rel="icon" type="image/png" href="../assets/logos/logotipo1.png" sizes="1000x1000">
    <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@400;500;600;700&family=Montserrat:wght@300;400;500;600&display=swap" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/remixicon/fonts/remixicon.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/remixicon@4.0.0/fonts/remixicon.css" rel="stylesheet">
    <link rel="stylesheet" href="https://unpkg.com/aos@next/dist/aos.css" />
    <link rel="stylesheet" type="text/css" href="lermais.css">
    <link rel="stylesheet" href="../components/header.css">
    <link rel="stylesheet" href="../components/footer.css">
    <link rel="stylesheet" href="../components/bas.css"
    <link rel="stylesheet" type="text/css" href="../includes/chatbot.css">
</head>
<body>
    <!-- Hero Section -->
    <section class="hero">
        <div class="hero__content">
            <h1 class="hero__title"><?= I18n::get('lermais_hero_title', 'Descubra Mais Sobre o Nosso Alojamento e a Região') ?></h1>
            <p class="hero__subtitle"><?= I18n::get('lermais_hero_subtitle', 'Explore os encantos do nosso espaço e deixe-se surpreender por Ponte de Lima') ?></p>
            <a href="../login1/pagina_login.php" class="hero__cta"><?= I18n::get('lermais_reservar_btn', 'Reservar Agora') ?></a>
        </div>
        <a href="#about" class="scroll-down">
            <i class="ri-arrow-down-s-line"></i>
        </a>
    </section>
    <!-- About Section -->
    <section class="section" id="about">
        <div class="section__container">
            <h2 class="section-title" data-aos="fade-up"><?= I18n::get('lermais_about_title', 'Nossa História') ?></h2>
            <p class="section-subtitle" data-aos="fade-up" data-aos-delay="100"><?= I18n::get('lermais_about_subtitle', 'Descubra a história e os valores que tornam a Quinta Flores um destino especial em Ponte de Lima') ?></p>
            <div class="content-grid">
                <div class="info-card" data-aos="fade-up" data-aos-delay="200">
                    <i class="ri-book-open-line info-card__icon"></i>
                    <h3><?= I18n::get('lermais_about_historia', 'Nossa História') ?></h3>
                    <p><?= I18n::get('lermais_about_historia_text', 'A Quinta Flores é um projeto familiar que tem crescido graças aos elogios e apoio dos nossos hóspedes. Ao longo dos anos, a família Flores tem investido na melhoria e valorização do alojamento, tornando-o num espaço cada vez mais acolhedor e confortável. Aqui, juntamos esforço e dedicação para proporcionar uma experiência única, num ambiente tranquilo e em contacto com a natureza.') ?></p>
                </div>
                <div class="info-card" data-aos="fade-up" data-aos-delay="300">
                    <i class="ri-focus-3-line info-card__icon"></i>
                    <h3><?= I18n::get('lermais_about_missao', 'Nossa Missão') ?></h3>
                    <p><?= I18n::get('lermais_about_missao_text', 'Proporcionar aos nossos hóspedes uma estadia inesquecível, onde possam criar memórias duradouras e desfrutar de momentos de qualidade e paz. Na Quinta Flores, dedicamo-nos a oferecer um ambiente acolhedor e confortável, para que cada visita seja uma experiência única e relaxante, valorizando o bem-estar de todos que nos escolhem.') ?></p>
                </div>
                <div class="info-card" data-aos="fade-up" data-aos-delay="400">
                    <i class="ri-heart-line info-card__icon"></i>
                    <h3><?= I18n::get('lermais_about_valores', 'Nossos Valores') ?></h3>
                    <p><?= I18n::get('lermais_about_valores_text', 'Na Quinta Flores, valorizamos a hospitalidade, o conforto e o cuidado em cada detalhe. Priorizamos o ambiente familiar e o respeito pela natureza, criando um espaço de tranquilidade. O nosso objetivo é proporcionar experiências memoráveis que os hóspedes levem consigo para sempre.') ?></p>
                </div>
            </div>
        </div>
    </section>
    <!-- Accommodation Section -->
    <section class="section" id="accommodation" style="background-color: var(--gray-light);">
        <div class="section__container">
            <h2 class="section-title" data-aos="fade-up"><?= I18n::get('lermais_accommodation_title', 'Nossas Acomodações') ?></h2>
            <p class="section-subtitle" data-aos="fade-up" data-aos-delay="100"><?= I18n::get('lermais_accommodation_subtitle', 'Conforto e charme em cada detalhe, proporcionando uma estadia inesquecível') ?></p>

            <div class="content-grid">
                <div class="info-card" data-aos="fade-up" data-aos-delay="200">
                    <i class="ri-hotel-bed-line info-card__icon"></i>
                    <h3><?= I18n::get('lermais_accommodation_quartos', 'Quartos') ?></h3>
                    <ul class="features-list">
                        <li><?= I18n::get('lermais_accommodation_quartos_1', 'Quartos espaçosos com vista para o jardim') ?></li>
                        <li><?= I18n::get('lermais_accommodation_quartos_2', 'Ar condicionado e aquecimento') ?></li>
                        <li><?= I18n::get('lermais_accommodation_quartos_3', 'Casa de banho privada com equipamentos de qualidade') ?></li>
                        <li><?= I18n::get('lermais_accommodation_quartos_4', 'Wi-Fi de alta velocidade') ?></li>
                        <li><?= I18n::get('lermais_accommodation_quartos_5', 'Decoração tradicional portuguesa com toques contemporâneos') ?></li>
                    </ul>
                </div>
                <div class="info-card" data-aos="fade-up" data-aos-delay="300">
                    <i class="ri-community-line info-card__icon"></i>
                    <h3><?= I18n::get('lermais_accommodation_comum', 'Áreas Comuns') ?></h3>
                    <ul class="features-list">
                        <li><?= I18n::get('lermais_accommodation_comum_1', 'Piscina ao ar livre com espreguiçadeiras') ?></li>
                        <li><?= I18n::get('lermais_accommodation_comum_2', 'Jardins exuberantes para relaxar') ?></li>
                        <li><?= I18n::get('lermais_accommodation_comum_3', 'Área de churrasco totalmente equipada') ?></li>
                        <li><?= I18n::get('lermais_accommodation_comum_4', 'Espaço para camping') ?></li>
                        <li><?= I18n::get('lermais_accommodation_comum_5', 'Estacionamento privado') ?></li>
                        <li><?= I18n::get('lermais_accommodation_comum_6', 'Terraço panorâmico com vista para as montanhas') ?></li>
                    </ul>
                </div>
                <div class="info-card" data-aos="fade-up" data-aos-delay="400">
                    <i class="ri-home-heart-line info-card__icon"></i>
                    <h3><?= I18n::get('lermais_accommodation_suites', 'Suítes Especiais') ?></h3>
                    <ul class="features-list">
                        <li><?= I18n::get('lermais_accommodation_suites_1', 'Suítes familiares espaçosas') ?></li>
                        <li><?= I18n::get('lermais_accommodation_suites_2', 'Unidades com cozinha equipada') ?></li>
                        <li><?= I18n::get('lermais_accommodation_suites_3', 'Decoração de luxo nas suítes') ?></li>
                        <li><?= I18n::get('lermais_accommodation_suites_4', 'Opções de acomodação para grupos') ?></li>
                    </ul>
                </div>
            </div>
        </div>
    </section>
    <!-- Activities Section -->
    <section class="section" id="activities">
        <div class="section__container">
            <h2 class="section-title" data-aos="fade-up"><?= I18n::get('lermais_activities_title', 'Experiências & Atividades') ?></h2>
            <p class="section-subtitle" data-aos="fade-up" data-aos-delay="100"><?= I18n::get('lermais_activities_subtitle', 'Descubra o melhor da região com nossas experiências cuidadosamente selecionadas') ?></p>
            <div class="activities-grid">
                <div class="activity-card" data-aos="fade-up" data-aos-delay="200">
                    <img src="../assets/images/gastronomia.jpg" alt="Experiência Gastronômica" class="activity-card__image">
                    <div class="activity-card__content">
                        <h3 class="activity-card__title"><?= I18n::get('lermais_activities_gastronomia', 'Experiência Gastronômica') ?></h3>
                        <p class="activity-card__text"><?= I18n::get('lermais_activities_gastronomia_text', 'Desfrute de uma experiência gastronómica única no coração do Minho, com a oportunidade de saborear pratos tradicionais da região. Participe em workshops culinários e degustações de produtos típicos, incluindo os reconhecidos vinhos verdes, que irão enriquecer a sua estadia com os sabores autênticos do nosso território') ?></p>
                        <a href="gastronomia.php" class="activity-card__link"><?= I18n::get('lermais_activities_explore', 'Explore mais') ?> <i class="ri-arrow-right-line"></i></a>
                    </div>
                </div>
                <div class="activity-card" data-aos="fade-up" data-aos-delay="300">
                    <img src="../assets/images/trilhos_e_natureza.avif" alt="Trilhas e Natureza" class="activity-card__image">
                    <div class="activity-card__content">
                        <h3 class="activity-card__title"><?= I18n::get('lermais_activities_trilhos', 'Trilhas e Natureza') ?></h3>
                        <p class="activity-card__text"><?= I18n::get('lermais_activities_trilhos_text', 'Explore as belas trilhas da região e conheça as maravilhas naturais do Minho. Descubra detalhes sobre a fauna, flora e a rica paisagem local, enquanto passeia por caminhos tranquilos e desfruta da serenidade da natureza.') ?></p>
                        <a href="trilhos.php" class="activity-card__link"><?= I18n::get('lermais_activities_explore', 'Explore mais') ?> <i class="ri-arrow-right-line"></i></a>
                    </div>
                </div>
                <div class="activity-card" data-aos="fade-up" data-aos-delay="400">
                    <img src="../assets/images/passeios_culturais.avif" alt="Passeios Culturais" class="activity-card__image">
                    <div class="activity-card__content">
                        <h3 class="activity-card__title"><?= I18n::get('lermais_activities_culturais', 'Passeios Culturais') ?></h3>
                        <p class="activity-card__text"><?= I18n::get('lermais_activities_culturais_text', 'Descubra os monumentos históricos, as festas tradicionais e as feiras de artesanato que celebram a rica cultura de Ponte de Lima, oferecendo-lhe uma verdadeira imersão nas tradições locais e no património da região.') ?></p>
                        <a href="passeios_culturais.php" class="activity-card__link"><?= I18n::get('lermais_activities_explore', 'Explore mais') ?> <i class="ri-arrow-right-line"></i></a>
                    </div>
                </div>
            </div>
        </div>
    </section>
 <!-- Secção da Galeria -->
    <section class="section gallery-section" id="gallery">
        <div class="section__container">
            <h2 class="section-title" data-aos="fade-up"><?= I18n::get('lermais_gallery_title', 'A Nossa Galeria') ?></h2>
            <p class="section-subtitle" data-aos="fade-up" data-aos-delay="100"><?= I18n::get('lermais_gallery_subtitle', 'Imagens que capturam a essência e a beleza da Quinta Flores') ?></p>
            <div class="gallery">
                <div class="gallery-item" data-aos="fade-up" data-aos-delay="100">
                    <img src="../assets/images/entrada_3.jpg" alt="Entrada da Quinta">
                    <div class="gallery-caption"><?= I18n::get('lermais_gallery_entrada', 'Entrada Principal') ?></div>
                </div>
                <div class="gallery-item" data-aos="fade-up" data-aos-delay="200">
                    <img src="../assets/images/19.png" alt="Vista Principal">
                    <div class="gallery-caption"><?= I18n::get('lermais_gallery_vista', 'Vista Panorâmica') ?></div>
                </div>
                <div class="gallery-item" data-aos="fade-up" data-aos-delay="300">
                    <img src="../assets/images/churrasco.jpg" alt="Área Externa">
                    <div class="gallery-caption"><?= I18n::get('lermais_gallery_churrasco', 'Área de Churrasco') ?></div>
                </div>
                <div class="gallery-item" data-aos="fade-up" data-aos-delay="400">
                    <img src="../assets/images/natureza19.jpg" alt="Jardim">
                    <div class="gallery-caption"><?= I18n::get('lermais_gallery_jardins', 'Jardins Floridos') ?></div>
                </div>
                <div class="gallery-item" data-aos="fade-up" data-aos-delay="500">
                    <img src="../assets/images/piscina2.jpg" alt="Piscina">
                    <div class="gallery-caption"><?= I18n::get('lermais_gallery_piscina', 'Piscina Exterior') ?></div>
                </div>
                <div class="gallery-item" data-aos="fade-up" data-aos-delay="600">
                    <img src="../assets/images/14.png" alt="Área de Lazer">
                    <div class="gallery-caption"><?= I18n::get('lermais_gallery_entrada_quartos', 'Entrada Acolhedora para os Quartos') ?></div>
                </div>
                <div class="gallery-item" data-aos="fade-up" data-aos-delay="700">
                    <img src="../assets/images/11.png" alt="Quarto">
                    <div class="gallery-caption"><?= I18n::get('lermais_gallery_suite', 'Suite') ?></div>
                </div>
                <div class="gallery-item" data-aos="fade-up" data-aos-delay="800">
                    <img src="../assets/images/casa_de_banho_7.jpg" alt="Casa de Banho">
                    <div class="gallery-caption"><?= I18n::get('lermais_gallery_banho_suite', 'Casa de Banho (Suite)') ?></div>
                </div>
                <div class="gallery-item" data-aos="fade-up" data-aos-delay="900">
                    <img src="../assets/images/17.png" alt="Sala de Estar">
                    <div class="gallery-caption"><?= I18n::get('lermais_gallery_sala', 'Sala de Estar Aconchegante') ?></div>
                </div>
                <div class="gallery-item" data-aos="fade-up" data-aos-delay="1000">
                    <img src="../assets/images/13.png" alt="Cozinha Equipada">
                    <div class="gallery-caption"><?= I18n::get('lermais_gallery_cozinha', 'Cozinha Totalmente Equipada') ?></div>
                </div>
                <div class="gallery-item" data-aos="fade-up" data-aos-delay="1100">
                    <img src="../assets/images/entrada_2.jpg" alt="Espaço Infantil">
                    <div class="gallery-caption"><?= I18n::get('lermais_gallery_ajardinado', 'Espaço Ajardinado com Esculturas') ?></div>
                </div>
                <div class="gallery-item" data-aos="fade-up" data-aos-delay="1200">
                    <img src="../assets/images/natureza_2.jpg" alt="Pôr-do-Sol na Quinta">
                    <div class="gallery-caption"><?= I18n::get('lermais_gallery_pordosol', 'Pôr-do-Sol na Quinta') ?></div>
                </div>
            </div>
        </div>
    </section>
    <!-- Testimonials Section -->
    <section class="section" id="testimonials">
        <div class="section__container">
            <h2 class="section-title" data-aos="fade-up"><?= I18n::get('lermais_testimonials_title', 'O Que Dizem Nossos Hóspedes') ?></h2>
            <p class="section-subtitle" data-aos="fade-up" data-aos-delay="100"><?= I18n::get('lermais_testimonials_subtitle', 'Experiências autênticas compartilhadas por quem já se hospedou na Quinta Flores') ?></p>
            <div class="testimonials-slider" data-aos="fade-up" data-aos-delay="200">
                <div class="testimonial">
                    <img src="../Flux_Dev_A_casually_dressed_woman_in_her_midtwenties_to_early__3.jpg" alt="Maria Silva" class="testimonial__image">
                    <p class="testimonial__text">"<?= I18n::get('lermais_testimonial_1', 'Uma experiência incrível! A hospitalidade da equipe da Quinta Flores é incomparável. Os quartos são espaçosos e confortáveis, e as áreas comuns são perfeitas para relaxar. Voltaremos com certeza!') ?>"</p>
                    <p class="testimonial__author"><?= I18n::get('lermais_testimonial_author_1', 'Maria Silva') ?></p>
                    <div class="testimonial__rating">★★★★★</div>
                </div>
            </div>
        </div>
    </section>
    <!-- FAQ Section -->
    <section class="section" style="background-color: var(--gray-light);">
        <div class="section__container">
            <h2 class="section-title" data-aos="fade-up"><?= I18n::get('lermais_faq_title', 'Perguntas Frequentes') ?></h2>
            <p class="section-subtitle" data-aos="fade-up" data-aos-delay="100"><?= I18n::get('lermais_faq_subtitle', 'Respostas para as dúvidas mais comuns sobre a Quinta Flores') ?></p>

            <div class="faq-container">
                <div class="faq-item" data-aos="fade-up" data-aos-delay="200">
                    <div class="faq-question">
                        <?= I18n::get('lermais_faq_q1', 'O que acontece em caso de danos na propriedade ou problemas durante a estadia?') ?> <i class="ri-arrow-down-s-line faq-icon"></i>
                    </div>
                    <div class="faq-answer">
                        <?= I18n::get('lermais_faq_a1', 'Em caso de danos à propriedade durante a sua estadia, solicitamos que nos informe imediatamente para que possamos tomar as devidas providências. Dependendo da gravidade do dano, poderá ser cobrada uma taxa adicional para reparação. Caso haja qualquer problema durante a sua estadia, a nossa equipa está disponível 24 horas por dia para garantir que tudo seja resolvido rapidamente e com a máxima eficiência.') ?>
                    </div>
                </div>
                <div class="faq-item" data-aos="fade-up" data-aos-delay="300">
                    <div class="faq-question">
                        <?= I18n::get('lermais_faq_q2', 'Qual é a política de cancelamento?') ?> <i class="ri-arrow-down-s-line faq-icon"></i>
                    </div>
                    <div class="faq-answer">
                        <?= I18n::get('lermais_faq_a2', 'Para cancelar a sua reserva, é necessário ligar para o número +351 912 418 976 com 10 dias de antecedência. Caso a anulação seja feita dentro de um prazo inferior, será cobrado 50% do valor da reserva. Pedimos que esteja atento às condições e prazos para evitar custos adicionais.') ?>
                    </div>
                </div>
                <div class="faq-item" data-aos="fade-up" data-aos-delay="400">
                    <div class="faq-question">
                        <?= I18n::get('lermais_faq_q3', 'O que acontece em caso de danos na propriedade ou problemas durante a estadia?') ?> <i class="ri-arrow-down-s-line faq-icon"></i>
                    </div>
                    <div class="faq-answer">
                        <?= I18n::get('lermais_faq_a3', 'Em caso de danos à propriedade durante a sua estadia, solicitamos que informe imediatamente a nossa equipa de recepção para que possamos resolver a situação o mais rápido possível. Dependendo da gravidade do dano, pode haver custos adicionais associados. Pedimos aos nossos hóspedes que cuidem do alojamento com o mesmo zelo com que cuidam da sua própria casa. Se houver problemas durante a estadia, nossa equipa está disponível 24 horas por dia para ajudá-lo a resolver qualquer questão de forma rápida e eficiente.') ?>
                    </div>
                </div>
                <div class="faq-item" data-aos="fade-up" data-aos-delay="500">
                    <div class="faq-question">
                        <?= I18n::get('lermais_faq_q4', 'Aceitam animais de estimação?') ?> <i class="ri-arrow-down-s-line faq-icon"></i>
                    </div>
                    <div class="faq-answer">
                        <?= I18n::get('lermais_faq_a4', 'Aceitamos animais de porte pequeno apenas. Para garantir a sua estadia confortável e sem imprevistos, recomendamos que entre em contacto conosco com antecedência para confirmar a disponibilidade e as condições específicas para o seu animal.') ?>
                    </div>
                </div>
            </div>
        </div>
    </section>
  <!-- Location Section -->
    <section class="section" id='localizacao'>
        <div class="section__container">
            <h2 class="section-title" data-aos="fade-up"><?= I18n::get('lermais_location_title', 'Localização Privilegiada') ?></h2>
            <p class="section-subtitle" data-aos="fade-up" data-aos-delay="100"><?= I18n::get('lermais_location_subtitle', 'Descubra por que nossa localização é perfeita para explorar o melhor do Norte de Portugal') ?></p>

            <div class="content-grid">
                <div class="info-card" data-aos="fade-up" data-aos-delay="200">
                    <i class="ri-map-pin-line info-card__icon"></i>
                    <h3><?= I18n::get('lermais_location_pontos', 'Pontos de Interesse Próximos') ?></h3>
                    <ul class="features-list">
                        <li><?= I18n::get('lermais_location_pontos_1', 'Centro histórico de Ponte de Lima (5 min)') ?></li>
                        <li><?= I18n::get('lermais_location_pontos_2', 'Praia fluvial (10 min)') ?></li>
                        <li><?= I18n::get('lermais_location_pontos_3', 'Ecovia do Lima para caminhadas e ciclismo') ?></li>
                        <li><?= I18n::get('lermais_location_pontos_4', 'Área de Paisagem Protegida das Lagoas') ?></li>
                        <li><?= I18n::get('lermais_location_pontos_5', 'Festival Internacional de Jardins') ?></li>
                    </ul>
                </div>
                <div class="info-card" data-aos="fade-up" data-aos-delay="300">
                    <i class="ri-route-line info-card__icon"></i>
                    <h3><?= I18n::get('lermais_location_como_chegar', 'Como Chegar') ?></h3>
                    <ul class="features-list">
                        <li><?= I18n::get('lermais_location_como_chegar_1', '45 minutos do Aeroporto do Porto') ?></li>
                        <li><?= I18n::get('lermais_location_como_chegar_2', '30 minutos de Viana do Castelo') ?></li>
                        <li><?= I18n::get('lermais_location_como_chegar_3', '20 minutos de Braga') ?></li>
                        <li><?= I18n::get('lermais_location_como_chegar_4', 'Coordenadas GPS disponíveis') ?></li>
                        <li><?= I18n::get('lermais_location_como_chegar_5', 'Fácil acesso pela autoestrada A3') ?></li>
                    </ul>
                </div>
            </div>
            <!-- Mapa do Google -->
            <div class="map-container" data-aos="fade-up" data-aos-delay="400">
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
<!-- Contact Section -->
<section class="section contact-section" id="contact">
<div class="section__container">
    <!-- Flash Messages Container (adicionar antes do form) -->
    <div id="flash-message" class="flash-message" style="display: none;"></div>
    <h2 class="section-title" data-aos="fade-up"><?= I18n::get('lermais_contact_title', 'Entre em Contacto') ?></h2>
        <p class="section-subtitle" data-aos="fade-up" data-aos-delay="100"><?= I18n::get('lermais_contact_subtitle', 'Estamos sempre disponíveis para ajudar e responder suas perguntas') ?></p>
        <div class="contact-grid">
            <div class="contact-info" data-aos="fade-up" data-aos-delay="200">
                <h3><?= I18n::get('lermais_contact_info', 'Informações de Contato') ?></h3>
                <ul class="contact-details">
                    <li>
                        <i class="ri-map-pin-line contact-icon"></i>
                        <div>
                            <p>Travessa da seara 265 Calheiros</p>
                            <p>4990-575 Ponte de Lima Viana do Castelo, Portugal</p>
                        </div>
                    </li>
                    <li>
                        <i class="ri-phone-line contact-icon"></i>
                        <div>
                            <p>+351 912 418 976</p>
                        </div>
                    </li>
                    <li>
                        <i class="ri-mail-line contact-icon"></i>
                        <div>
                            <p>quinta.flores2019@gmail.com</p>
                        </div>
                    </li>
                    <li>
                        <i class="ri-time-line contact-icon"></i>
                        <div>
                            <p>Check-in: 15:00 </p>
                            <p>Check-out: até 11:00</p>
                        </div>
                    </li>
                </ul>
            </div>
            <div class="contact-form" data-aos="fade-up" data-aos-delay="300">
                <form id="contactForm" action="send_message.php" method="POST">
                    <div class="form-group">
                        <label for="name" class="form-label">
                            <?= I18n::get('lermais_contact_nome', 'Nome') ?> <i class="ri-user-line"></i>
                        </label>
                        <input 
                            type="text" 
                            id="name" 
                            name="name" 
                            class="form-input" 
                            required 
                            placeholder="<?= I18n::get('lermais_contact_nome_placeholder', 'Seu nome completo') ?>" 
                            autocomplete="name" 
                            title="<?= I18n::get('lermais_contact_nome_title', 'Introduza o seu nome completo') ?>"
                        >
                    </div>
                    <div class="form-group">
                        <label for="email" class="form-label">
                            <?= I18n::get('lermais_contact_email', 'Email') ?> <i class="ri-mail-line"></i>
                        </label>
                        <input 
                            type="email" 
                            id="email" 
                            name="email" 
                            class="form-input" 
                            required 
                            placeholder="<?= I18n::get('lermais_contact_email_placeholder', 'seuemail@gmail.com') ?>" 
                            autocomplete="email" 
                            title="<?= I18n::get('lermais_contact_email_title', 'Introduza o seu email, ex: exemplo@gmail.com') ?>"
                        >
                    </div>

                    <div class="form-group">
                        <label for="subject" class="form-label">
                            <?= I18n::get('lermais_contact_assunto', 'Assunto') ?> <i class="ri-pencil-line"></i>
                        </label>
                        <input 
                            type="text" 
                            id="subject" 
                            name="subject" 
                            class="form-input" 
                            required 
                            placeholder="<?= I18n::get('lermais_contact_assunto_placeholder', 'Resumo do assunto') ?>" 
                            title="<?= I18n::get('lermais_contact_assunto_title', 'Introduza o assunto da sua mensagem') ?>"
                        >
                    </div>
                    <div class="form-group">
                        <label for="message" class="form-label">
                            <?= I18n::get('lermais_contact_mensagem', 'Mensagem') ?> <i class="ri-message-line"></i>
                        </label>
                        <textarea 
                            id="message" 
                            name="message" 
                            class="form-textarea" 
                            required 
                            placeholder="<?= I18n::get('lermais_contact_mensagem_placeholder', 'Escreva a sua mensagem aqui') ?>" 
                            title="<?= I18n::get('lermais_contact_mensagem_title', 'Introduza a sua mensagem') ?>" 
                            rows="5"
                        ></textarea>
                    </div>
                    <button type="submit" class="form-button">
                        <span class="button-text"><?= I18n::get('lermais_contact_enviar', 'Enviar Mensagem') ?></span>
                        <span class="button-loading" style="display: none;">
                            <i class="ri-loader-4-line"></i> <?= I18n::get('lermais_contact_enviando', 'Enviando...') ?>
                        </span>
                    </button>
                </form>
            </div>
        </div>
    </div>
</section>
<style>
  /* Flash message base */
  .flash-message {
    max-width: 600px;
    margin: 1rem auto;
    padding: 1rem 1.5rem;
    border-radius: 8px;
    font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
    font-size: 1rem;
    box-shadow: 0 4px 12px rgba(0,0,0,0.1);
    display: flex;
    align-items: center;
    gap: 1rem;
    opacity: 0;
    pointer-events: none;
    transform: translateY(-10px);
    transition: opacity 0.4s ease, transform 0.4s ease;
    position: relative;
    z-index: 10;
  }

  /* Mostrar flash */
  .flash-message.show {
    opacity: 1;
    pointer-events: auto;
    transform: translateY(0);
  }

  /* Ícones */
  .flash-message svg {
    flex-shrink: 0;
    width: 24px;
    height: 24px;
  }

  /* Sucesso */
  .flash-success {
    background: #e6ffed;
    color: #2c7a2c;
    border: 1.5px solid #2c7a2c;
  }
  .flash-success svg path {
    stroke: #2c7a2c;
    fill: none;
  }

  /* Erro */
  .flash-error {
    background: #ffe6e6;
    color: #a12b2b;
    border: 1.5px solid #a12b2b;
  }
  .flash-error svg path {
    stroke: #a12b2b;
    fill: none;
  }

  /* Botão fechar */
  .flash-close {
    position: absolute;
    top: 8px;
    right: 12px;
    background: transparent;
    border: none;
    font-size: 1.2rem;
    color: inherit;
    cursor: pointer;
    line-height: 1;
    padding: 0;
  }
  .flash-close:hover {
    color: #555;
  }
</style>

<!-- Flash Messages Container -->
<div id="flash-message" class="flash-message" role="alert" aria-live="assertive" style="display: none;">
  <svg aria-hidden="true" viewBox="0 0 24 24" fill="none" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"></svg>
  <span id="flash-text"></span>
  <button class="flash-close" aria-label="Fechar mensagem" onclick="hideFlashMessage()">&times;</button>
</div>

<script>
  const flashDiv = document.getElementById('flash-message');
  const flashText = document.getElementById('flash-text');
  const flashIcon = flashDiv.querySelector('svg');

  function showFlashMessage(type, message) {
    flashText.textContent = message;

    if (type === 'sucesso') {
      flashDiv.className = 'flash-message flash-success show';
      flashIcon.innerHTML = `
        <circle cx="12" cy="12" r="10" stroke="#2c7a2c"></circle>
        <path d="M8 12.5l3 3 5-6" stroke="#2c7a2c"/>
      `;
    } else {
      flashDiv.className = 'flash-message flash-error show';
      flashIcon.innerHTML = `
        <circle cx="12" cy="12" r="10" stroke="#a12b2b"></circle>
        <line x1="15" y1="9" x2="9" y2="15" stroke="#a12b2b"/>
        <line x1="9" y1="9" x2="15" y2="15" stroke="#a12b2b"/>
      `;
    }

    flashDiv.style.display = 'flex';

    // Auto fechar depois de 6s
    clearTimeout(flashDiv._timeout);
    flashDiv._timeout = setTimeout(() => {
      hideFlashMessage();
    }, 6000);
  }

  function hideFlashMessage() {
    flashDiv.classList.remove('show');
    setTimeout(() => {
      flashDiv.style.display = 'none';
      flashText.textContent = '';
      flashIcon.innerHTML = '';
    }, 400);
  }

  // Atualização do teu script de envio para usar showFlashMessage

  document.getElementById('contactForm').addEventListener('submit', function(e) {
    e.preventDefault();

    const form = this;
    flashDiv.style.display = 'none';

    const submitButton = form.querySelector('button[type="submit"]');
    const buttonText = submitButton.querySelector('.button-text');
    const buttonLoading = submitButton.querySelector('.button-loading');

    buttonText.style.display = 'none';
    buttonLoading.style.display = 'inline-block';
    submitButton.disabled = true;

    const formData = new FormData(form);

    fetch(form.action, {
      method: 'POST',
      body: formData
    })
    .then(response => response.json())
    .then(data => {
      if (data.tipo === 'sucesso') {
        showFlashMessage('sucesso', data.mensagem);
        form.reset();
      } else {
        showFlashMessage('erro', data.mensagem);
      }
    })
    .catch(() => {
      showFlashMessage('erro', 'Erro ao enviar a mensagem. Tente novamente.');
    })
    .finally(() => {
      submitButton.disabled = false;
      buttonText.style.display = 'inline-block';
      buttonLoading.style.display = 'none';

      flashDiv.scrollIntoView({ behavior: 'smooth', block: 'start' });
    });
  });
</script>
                </div>
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
        //Menu móvel
        document.addEventListener('DOMContentLoaded', () => {
            // Verificar flash messages via URL params
const urlParams = new URLSearchParams(window.location.search);
const flashMessage = urlParams.get('flash_message');
const flashType = urlParams.get('flash_type');
if (flashMessage) {
    showFlashMessage(flashMessage, flashType);
}
function showFlashMessage(message, type) {
    const flashDiv = document.getElementById('flash-message');
    flashDiv.textContent = message;
    flashDiv.className = `flash-message flash-${type}`;
    flashDiv.style.display = 'block';
    
    setTimeout(() => {
        flashDiv.style.display = 'none';
    }, 5000);
}
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
            navItems.forEach(item => {
                item.addEventListener('click', () => {
                    navLinks.classList.remove('active');
                    hamburger.innerHTML = '<i class="ri-menu-line"></i>';
                });
            });
            window.addEventListener('scroll', () => {
                if (window.scrollY > 100) {
                    header.classList.add('scrolled');
                } else {
                    header.classList.remove('scrolled');
                }
            });
            const faqItems = document.querySelectorAll('.faq-item');
            faqItems.forEach(item => {
                const question = item.querySelector('.faq-question');
                question.addEventListener('click', () => {
                    item.classList.toggle('active');
                });
            });
            // Formulário de contato
            const contactForm = document.getElementById('contactForm');
            if (contactForm) {
                contactForm.addEventListener('submit', (e) => {
                    e.preventDefault();
                    // Aqui você adicionaria a lógica para enviar o formulário
                    alert('Mensagem enviada com sucesso! Entraremos em contato em breve.');
                    contactForm.reset();
                });
            }
        });
        //enviar emails
        document.getElementById("contactForm").addEventListener("submit", function(event) {
    let nome = document.getElementById("name").value;
    let email = document.getElementById("email").value;
    let assunto = document.getElementById("subject").value;
    let mensagem = document.getElementById("message").value;
    if (!nome || !email || !assunto || !mensagem) {
        alert("Por favor, preencha todos os campos.");
        event.preventDefault();  // Impede o envio do formulário
    }
});
    </script>
    <?php include '../components/footer.php'; ?>
<link rel="stylesheet" href="../chatbot/chatbot.css">
<script src="../chatbot/chatbot.js"></script>
<?php include '../chatbot/chatbot_config.php'; ?>
<?php include '../chatbot/chatbot.php'; ?>
</body>
</html> 