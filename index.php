<?php 
require_once 'i18n.php';
if (isset($_GET['lang'])) {
    I18n::setLanguage($_GET['lang']);
}
$nav_links = [
    ['href' => '#about', 'text' => 'Sobre'],
    ['href' => '#rooms', 'text' => 'Ofertas'],
    ['href' => '#gallery', 'text' => 'Galeria'],
    ['href' => '#amenities', 'text' => 'Comodidades'],
    ['href' => '#testimonials', 'text' => 'Comentários'],
    ['href' => '#location', 'text' => 'Localização'],
    ['href' => '#contactos', 'text' => 'Contactos'],
];
include 'components/header.php'; 
$aboutData = json_decode(file_get_contents(__DIR__ . '/data/about.json'), true);

// Carregar todas as configurações de imagens
$settingsFile = __DIR__ . '/data/about.json';
$settings = file_exists($settingsFile) ? json_decode(file_get_contents($settingsFile), true) : [];

// Função para obter imagem, com fallback para uma imagem padrão
function getImage($key, $default) {
    global $settings;
    return 'assets/images/' . ($settings[$key] ?? $default);
}
?>
<!DOCTYPE html>
<html lang="pt" data-theme="light">
<head>
    <!-- Meta tags otimizadas para mobile -->
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no" />
    <meta name="description" content="Alojamento local em Ponte de Lima - Aconchego, natureza e tradição minhota">  
    <!-- Preload otimizado -->
    <link rel="preload" href="imagens/hero.webp" as="image" media="(max-width: 600px)">
    <link rel="preload" href="imagens/hero-large.webp" as="image" media="(min-width: 601px)">
    <!-- Fontes otimizadas -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <!-- Ícones -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/remixicon@4.0.0/fonts/remixicon.css"/>
    <!-- CSS -->
    <link rel="stylesheet" type="text/css" href="index/teste.css">
    <link rel="stylesheet" href="components/header.css">
    <link rel="stylesheet" href="components/footer.css">
    <link rel="stylesheet" type="text/css" href="assets/i18n/translator.css">
    <link rel="stylesheet" type="text/css" href="index/lermais.css"> <!-- Adicionado: CSS das bandeiras -->
    <title>QUINTA | FLORES</title>
    <!-- Favicon -->
    <link rel="icon" type="image/png" href="../assets/logos/logotipo1.png" sizes="1000x1000">
</head>
  <body>
    <!-- Hero Section -->
    <section class="hero">
        <div class="hero__content">
            <h1 class="hero__title"><?= I18n::get('hero_title', 'Bem-vindo à Quinta Flores') ?></h1>
            <p class="hero__subtitle"><?= I18n::get('hero_subtitle', 'Um espaço pensado para quem valoriza conforto, beleza e boas memórias') ?></p>
            <a href="../login1/pagina_login.php" class="hero__cta">Reservar Agora</a>
        </div>
        <a href="#about" class="scroll-down">
            <i class="ri-arrow-down-s-line"></i>
        </a>
    </section>
    
 <!-- Boking Section -->
 <section class="booking__container" id="booking">
  <form class="booking__form" id="bookingForm">
    <div class="input__group">
      <span><i class="ri-calendar-line"></i></span>
      <div>
        <label for="checkIn"><?= I18n::get('checkIn', 'Check In') ?></label>
        <input type="date" id="checkIn" required>
      </div>
    </div>
    <div class="input__group">
      <span><i class="ri-calendar-line"></i></span>
      <div>
        <label for="checkOut"><?= I18n::get('checkOut', 'Check Out') ?></label>
        <input type="date" id="checkOut" required>
      </div>
    </div>
    <div class="input__group">
      <span><i class="ri-user-line"></i></span>
      <div>
        <label for="guests"><?= I18n::get('guests', 'Hóspedes') ?></label>
        <input type="number" id="guests" min="1" max="10" value="2" required>
      </div>
    </div>
    <div class="input__group input__btn">
      <button type="button" id="searchBtn" class="btn"><?= I18n::get('searchBtn', 'Ver disponibilidade') ?></button>
    </div>
  </form>
  <div id="availabilityResult" class="availability__message"></div>
</section>
<script>
// Função para obter o ícone de acordo com o tipo de mensagem
function getIconForType(type) {
  switch (type) {
    case 'success': return 'fa-check-circle';        // Ícone para sucesso
    case 'error': return 'fa-exclamation-triangle';  // Ícone para erro
    case 'info': return 'fa-info-circle';            // Ícone para informação
    default: return 'fa-info-circle';                 // Ícone padrão
  }
}
document.addEventListener('DOMContentLoaded', () => {
  const checkInInput = document.getElementById('checkIn');
  const checkOutInput = document.getElementById('checkOut');
  // Bloquear datas anteriores a hoje
  const today = new Date().toISOString().split('T')[0];
  checkInInput.min = today;
  checkOutInput.min = today;
  // Atualizar o mínimo do checkOut quando o checkIn for selecionado
  checkInInput.addEventListener('change', () => {
    const checkInDate = checkInInput.value;
    if (checkInDate) {
      checkOutInput.min = checkInDate;  // Garante que checkOut não seja antes do checkIn
      // Se o checkOut atual for antes do checkIn, limpa o checkOut
      if (checkOutInput.value && checkOutInput.value < checkInDate) {
        checkOutInput.value = '';
      }
    }
  });
  // Liga o evento do botão para verificar disponibilidade
  document.getElementById('searchBtn').addEventListener('click', checkAvailability);
});
// Função para mostrar mensagens "flash" no ecrã, com botão para fechar manualmente
function showFlashMessage(message, type = 'success', duration = 4000) {
  // Remove mensagens existentes (não mostrar várias ao mesmo tempo)
  document.querySelectorAll('.flash-message').forEach(msg => msg.remove());

  // Cria o elemento da flash message
  const flash = document.createElement('div');
  flash.classList.add('flash-message', `flash-${type}`);
  flash.innerHTML = `
    <div style="display: flex; align-items: center; gap: 10px; flex: 1;">
      <i class="fa-solid ${getIconForType(type)}" style="font-size: 18px;"></i>
      <span>${message}</span>
    </div>
    <button class="close-btn" aria-label="Fechar">&times;</button>
  `;

  // Adiciona ao corpo do documento
  document.body.appendChild(flash);

  // Evento para fechar manualmente ao clicar no botão "×"
  flash.querySelector('.close-btn').addEventListener('click', () => {
    flash.classList.add('fade-out');
    setTimeout(() => flash.remove(), 400);
  });
  // Animação para mostrar a mensagem (entra da direita)
// Mostrar animação de entrada
setTimeout(() => {
  flash.classList.add('show');
}, 10);
// Remover após duração definida
setTimeout(() => {
  flash.classList.remove('show');
  flash.classList.add('fade-out');
  setTimeout(() => {
    if (flash.parentNode) flash.remove();
  }, 500);
}, duration);

}
// Função que verifica a disponibilidade ao clicar no botão
function checkAvailability() {
  const checkIn = document.getElementById('checkIn').value;
  const checkOut = document.getElementById('checkOut').value;
  const guests = document.getElementById('guests').value;

  // Validação: verifica se as datas foram preenchidas
  if (!checkIn || !checkOut) {
    showFlashMessage('Por favor, preencha as datas de check-in e check-out.', 'error');
    return;
  }

  // Converte as strings de data para objetos Date
  const checkInDate = new Date(checkIn);
  const checkOutDate = new Date(checkOut);
  const today = new Date();
  today.setHours(0, 0, 0, 0); // Zera horas para comparar só datas

  // Validação: check-in não pode ser anterior a hoje
  if (checkInDate < today) {
    showFlashMessage('A data de check-in não pode ser anterior a hoje.', 'error');
    return;
  }

  // Validação: check-out tem que ser depois do check-in
  if (checkOutDate <= checkInDate) {
    showFlashMessage('A data de check-out deve ser posterior à data de check-in.', 'error');
    return;
  }

  // Validação: número de hóspedes deve estar entre 1 e 10
  if (guests < 1 || guests > 10) {
    showFlashMessage('O número de hóspedes deve estar entre 1 e 10.', 'error');
    return;
  }
  // Mensagem de carregamento
  showFlashMessage('A verificar disponibilidade...', 'info', 2000);

  // Faz uma requisição POST para o servidor (exemplo)
  fetch('index/verificar_disponibilidade.php', {
    method: 'POST',
    headers: { 'Content-Type': 'application/json' },
    body: JSON.stringify({ checkIn, checkOut, guests })
  })
  .then(response => {
    if (!response.ok) {
      throw new Error('Erro ao comunicar com o servidor.');
    }
    return response.json();
  })
  .then(data => {
    if (data.available) {
      showFlashMessage('Disponibilidade confirmada! As datas estão livres.', 'success');
    } else {
      showFlashMessage('Sem disponibilidade para as datas selecionadas.', 'error');
    }
  })
  .catch(error => {
    console.error('Erro:', error);
    showFlashMessage('Ocorreu um erro ao verificar disponibilidade.', 'error');
  });
}
</script>

    <!-- About Section -->
    <section class="section__container about__container" id="about">
<div class="about__image">
  <img src="<?= htmlspecialchars(getImage('imagem', 'foto_principal_2.jpeg')) ?>" alt="sobre nós">
</div>
      <div class="about__content">
        <p class="section__subheader"><?= I18n::get('about_subheader', 'Quinta Flores ') ?></p>
        <h2 class="section__header"><?= I18n::get('about_header', 'Refúgio de Natureza e Conforto em Ponte de Lima') ?></h2>
        <p class="section__description">
          <?= I18n::get('about_description', 'Situada no coração da deslumbrante região de Ponte de Lima, a Quinta Flores é uma casa inteira pensada para receber até 10 pessoas num ambiente familiar, tranquilo e repleto de natureza. Aqui, poderá desfrutar de momentos de relaxamento e convívio, rodeado por paisagens serenas e ar puro.') ?>
        </p>
        <p class="section__description">
<?= I18n::get('about_description_2', 'Ideal para famílias, grupos de amigos ou turistas à procura de um refúgio calmo, a Quinta Flores oferece um espaço acolhedor onde a boa comida e a hospitalidade genuína são parte da experiência. Para além do conforto da casa, proporcionamos um ambiente perfeito para explorar as muitas atividades e atrações da região, detalhadas no nosso site para facilitar a sua programação.') ?>
Venha descobrir um lugar onde a natureza e o bem-estar se encontram, e crie memórias inesquecíveis na Quinta Flores.
        </p>
          <a href="index/lermais.php" class="btn" style="text-decoration: none;">Ler Mais</a>
        </div>
      </div>
    </section>
<!-- Ofertas Section -->
<section class="section__container rooms__container" id="rooms">
  <p class="section__subheader"><?= I18n::get('rooms_subheader', 'Momentos inesquecíveis com condições especiais') ?></p>
  <h2 class="section__header"><?= I18n::get('rooms_header', 'Descobre as Nossas Ofertas Especiais') ?></h2>

  <div class="rooms__grid">
    
    <div class="room__card">
      <div class="room__image">
        <img src="<?= htmlspecialchars(getImage('room_amor', 'amor.avif')) ?>" alt="Oferta Tempo de Namorar">
        <div class="room__badge">2 noites</div>
      </div>
      <div class="room__content">
        <h3 class="room__title"><?= I18n::get('room_title_amor', 'Tempo a Dois') ?></h3>
        <p class="room__price"><?= I18n::get('room_price_amor', 'Preço: 260€') ?></p>
        <p class="room__description"><?= I18n::get('room_description_amor', 'Dois dias inesquecíveis para reacender a chama do amor') ?></p>
        <a href="index/tempo_namorar.php" class="room__link"><?= I18n::get('room_link_amor', 'Planeie a sua experiência') ?></a>
      </div>
    </div>

    <div class="room__card">
      <div class="room__image">
        <img src="<?= htmlspecialchars(getImage('room_party', 'party.avif')) ?>" alt="Oferta Festa com Amigos">
        <div class="room__badge">1 a 2 noites</div>
      </div>
      <div class="room__content">
        <h3 class="room__title"><?= I18n::get('room_title_party', 'Diversão em Grupo') ?></h3>
        <p class="room__price"><?= I18n::get('room_price_party', 'Preço: 250€') ?></p>
        <p class="room__description"><?= I18n::get('room_description_party', 'O cenário perfeito para celebrar com os teus amigos ao máximo') ?></p>
        <a href="../index/festa_amigos.php" class="room__link"><?= I18n::get('room_link_party', 'Planeie a sua experiência') ?></a>
      </div>
    </div>

    <div class="room__card">
      <div class="room__image">
        <img src="<?= htmlspecialchars(getImage('room_religious', 'religious.avif')) ?>" alt="Oferta Retiro de Catequese">
        <div class="room__badge">3 noites</div>
      </div>
      <div class="room__content">
        <h3 class="room__title"><?= I18n::get('room_title_religious', 'Retiro Espiritual') ?></h3>
        <p class="room__price"><?= I18n::get('room_price_religious', 'Preço: 240€') ?></p>
        <p class="room__description"><?= I18n::get('room_description_religious', 'Paz, reflexão e união num ambiente acolhedor com kits espirituais incluídos') ?></p>
        <a href="index/retiro_catequese.php" class="room__link"><?= I18n::get('room_link_religious', 'Planeie a sua experiência') ?></a>
      </div>
    </div>

  </div>
</section>
<section class="gallery-section" id="gallery">
  <p class="section__subheader"><?= I18n::get('gallery_subheader', 'Algumas fotos do Alojamento') ?></p>
  <h2 class="section__header"><?= I18n::get('gallery_header', 'Galeria') ?></h2>
  
  <div class="gallery-reveal">
    <button class="gallery-reveal-btn" id="revealBtn">
      <span><?= I18n::get('gallery_reveal_btn', 'Ver Fotos') ?></span>
      <i class="ri-arrow-down-s-line"></i>
    </button>
  </div>

  <div class="gallery-grid hidden" id="galleryGrid">
    <?php
    // Define as chaves das imagens na ordem que queres mostrar, com info de alt, titulo, descrição i18n
    $galleryItems = [
      ['key' => 'gallery_6', 'alt' => 'Sala de Estar', 'title_key' => 'gallery_item_title_6', 'desc_key' => 'gallery_item_description_6'],
      ['key' => 'gallery_7', 'alt' => 'Sala de Jantar', 'title_key' => 'gallery_item_title_7', 'desc_key' => 'gallery_item_description_7'],
      ['key' => 'gallery_13', 'alt' => 'Cozinha', 'title_key' => 'gallery_item_title_13', 'desc_key' => 'gallery_item_description_13'],
      ['key' => 'gallery_12', 'alt' => 'Cozinha', 'title_key' => 'gallery_item_title_12', 'desc_key' => 'gallery_item_description_12'],
      ['key' => 'gallery_area_comum6', 'alt' => 'Escadas', 'title_key' => 'gallery_item_title_area_comum6', 'desc_key' => 'gallery_item_description_area_comum6'],
      ['key' => 'gallery_14', 'alt' => 'Entrada para os Quartos', 'title_key' => 'gallery_item_title_14', 'desc_key' => 'gallery_item_description_14'],
      ['key' => 'gallery_casa_banho_9', 'alt' => 'Casa de banho', 'title_key' => 'gallery_item_title_casa_banho_9', 'desc_key' => 'gallery_item_description_casa_banho_9'],
      ['key' => 'gallery_quarto_3', 'alt' => 'Quarto equipado', 'title_key' => 'gallery_item_title_quarto_3', 'desc_key' => 'gallery_item_description_quarto_3'],
      ['key' => 'gallery_quarto_3_2', 'alt' => 'Quarto equipado', 'title_key' => 'gallery_item_title_quarto_3_2', 'desc_key' => 'gallery_item_description_quarto_3_2'],
      ['key' => 'gallery_quarto_3_suite', 'alt' => 'Suite', 'title_key' => 'gallery_item_title_quarto_3', 'desc_key' => 'gallery_item_description_quarto_3'],
      ['key' => 'gallery_casa_de_banho_7', 'alt' => 'Casa de Banho', 'title_key' => 'gallery_item_title_casa_de_banho_7', 'desc_key' => 'gallery_item_description_casa_de_banho_7'],
      ['key' => 'gallery_casa_de_banho6', 'alt' => 'Casa de Banho', 'title_key' => 'gallery_item_title_casa_de_banho6', 'desc_key' => 'gallery_item_description_casa_de_banho6'],
      ['key' => 'gallery_foto_principal_4', 'alt' => 'Casa vista de fora', 'title_key' => 'gallery_item_title_foto_principal_4', 'desc_key' => 'gallery_item_description_foto_principal_4'],
      ['key' => 'gallery_19', 'alt' => 'Casa vista de cima', 'title_key' => 'gallery_item_title_19', 'desc_key' => 'gallery_item_description_19'],
      ['key' => 'gallery_natureza23', 'alt' => 'Entrada para jardim e piscina', 'title_key' => 'gallery_item_title_natureza23', 'desc_key' => 'gallery_item_description_natureza23'],
      ['key' => 'gallery_piscina2', 'alt' => 'Piscina', 'title_key' => 'gallery_item_title_piscina2', 'desc_key' => 'gallery_item_description_piscina2'],
      ['key' => 'gallery_churrasco', 'alt' => 'Churrasco', 'title_key' => 'gallery_item_title_churrasco', 'desc_key' => 'gallery_item_description_churrasco'],
      ['key' => 'gallery_foto_principal_3', 'alt' => 'Garagem', 'title_key' => 'gallery_item_title_foto_principal_3', 'desc_key' => 'gallery_item_description_foto_principal_3'],
      ['key' => 'gallery_entrada_3', 'alt' => 'Entrada', 'title_key' => 'gallery_item_title_entrada_3', 'desc_key' => 'gallery_item_description_entrada_3'],
      ['key' => 'gallery_entrada_2', 'alt' => '', 'title_key' => '', 'desc_key' => ''],
    ];

    foreach ($galleryItems as $item) {
      $imgSrc = getImage($item['key'], 'default.jpg');
      ?>
      <div class="gallery-item">
        <img src="<?= htmlspecialchars($imgSrc) ?>" alt="<?= htmlspecialchars($item['alt']) ?>">
        <div class="gallery-hover-content">
          <h3><?= $item['title_key'] ? I18n::get($item['title_key'], $item['alt']) : '' ?></h3>
          <p><?= $item['desc_key'] ? I18n::get($item['desc_key'], '') : '' ?></p>
        </div>
      </div>
      <?php
    }
    ?>
  </div>
  <script>
    document.addEventListener('DOMContentLoaded', function() {
      const revealBtn = document.getElementById('revealBtn');
      const galleryGrid = document.getElementById('galleryGrid');
      let isRevealed = false;

      revealBtn.addEventListener('click', function() {
        isRevealed = !isRevealed;

        if (isRevealed) {
          galleryGrid.classList.remove('hidden');
          galleryGrid.classList.add('revealed');
          revealBtn.innerHTML = '<span><?= I18n::get('gallery_reveal_btn_2', 'Ocultar Fotos') ?></span><i class="ri-arrow-up-s-line"></i>';
        } else {
          galleryGrid.classList.remove('revealed');
          galleryGrid.classList.add('hidden');
          revealBtn.innerHTML = '<span><?= I18n::get('gallery_reveal_btn_3', 'Ver Fotos') ?></span><i class="ri-arrow-down-s-line"></i>';
        }
      });
    });
  </script>
</section>


<!--Comodidades -->
<section class="section__container comodidades" id="amenities">
  <p class="section__subheader"><?= I18n::get('amenities_subheader', 'O que este espaço oferece') ?></p>
  <h2 class="section__header"><?= I18n::get('amenities_header', 'Comodidades') ?></h2>
  <div class="comodidades__container">
    <ul class="comodidades__list">
      <li>
        <i class="fa-solid fa-wifi"></i>
        <span><?= I18n::get('amenities_wifi', 'Wi-Fi Gratuito') ?></span>
      </li>
      <li>
        <i class="fa-solid fa-person-swimming"></i>
        <span><?= I18n::get('amenities_piscina', 'Piscina Exterior') ?></span>
      </li>
      <li>
        <i class="fa-solid fa-square-parking"></i>
        <span><?= I18n::get('amenities_estacionamento', 'Estacionamento Privado') ?></span>
      </li>
      <li>
        <i class="fa-solid fa-bed"></i>
        <span><?= I18n::get('amenities_lençóis', 'Lençóis e Toalhas de Alta Qualidade') ?></span>
      </li>
      <li>
        <i class="fa-solid fa-gamepad"></i>
        <span><?= I18n::get('amenities_area_lazer', 'Área de Lazer') ?></span>
      </li>
      <li>
        <i class="fa-solid fa-fire"></i>
        <span><?= I18n::get('amenities_lareira', 'Lareira') ?></span>
      </li>
      <li>
        <i class="fa-solid fa-soap"></i>
        <span><?= I18n::get('amenities_máquina_lavar', 'Máquina de Lavar Roupa') ?></span>
      </li>
      <li>
        <i class="fa-solid fa-tv"></i>
        <span><?= I18n::get('amenities_televisão', 'Televisão') ?></span>
      </li>
      <li>
        <i class="fa-solid fa-kitchen-set"></i>
        <span><?= I18n::get('amenities_cozinha', 'Cozinha Totalmente Equipada') ?></span>
      </li>
      <li>
        <i class="fa-solid fa-kit-medical"></i>
        <span><?= I18n::get('amenities_kit_primeiros', 'Kit de Primeiros Socorros') ?></span>
      </li>
      <li>
        <i class="fa-solid fa-hot-tub-person"></i>
        <span><?= I18n::get('amenities_jacuzzi', 'Jacuzzi') ?></span>
      </li>
      <li>
        <i class="fa-solid fa-drumstick-bite"></i>
        <span><?= I18n::get('amenities_churrasqueira', 'Churrasqueira') ?></span>
      </li>
    </ul>
  </div>
</section>
    <!-- Testimonials Section -->
    <section class="testimonials" id="testimonials">
      <p class="section__subheader"><?= I18n::get('testimonials_subheader', 'Comentários') ?></p>
      <h2 class="section__header"><?= I18n::get('testimonials_header', 'O Que Dizem os Nossos Hóspedes') ?></h2>
      <div class="testimonial__container">
        <div class="testimonial__grid">
          <div class="testimonial__card">
            <div class="testimonial__quote">"</div>
            <p class="testimonial__text">
              <?= I18n::get('testimonial_text_1', 'A Quinta Flores superou todas as nossas expectativas. O local é lindo, os quartos impecáveis e o pequeno-almoço delicioso com produtos regionais. Voltaremos com certeza!') ?>
            </p>
            <div class="testimonial__author">
              <img src="assets/images/Ana_Silva.jpg" alt="Ana Silva">
              <div class="author__info">
                <h4><?= I18n::get('testimonial_author_name_1', 'Ana Silva') ?></h4>
                <p><?= I18n::get('testimonial_author_location_1', 'Porto, Portugal') ?></p>
                <div class="rating">
                  <i class="ri-star-fill"></i>
                  <i class="ri-star-fill"></i>
                  <i class="ri-star-fill"></i>
                  <i class="ri-star-fill"></i>
                  <i class="ri-star-fill"></i>
                </div>
              </div>
            </div>
          </div>
          <div class="testimonial__card">
            <div class="testimonial__quote">"</div>
            <p class="testimonial__text">
              <?= I18n::get('testimonial_text_2', 'Foi nossa segunda estadia e foi tão boa quanto a primeira. A atenção da equipa é excepcional e adoramos os passeios sugeridos pela região. Recomendamos!') ?>
            </p>
            <div class="testimonial__author">
              <img src="assets/images/Carlos_Mendes.jpg" alt="Carlos Mendes">
              <div class="author__info">
                <h4><?= I18n::get('testimonial_author_name_2', 'Carlos Mendes') ?></h4>
                <p><?= I18n::get('testimonial_author_location_2', 'Lisboa, Portugal') ?></p>
                <div class="rating">
                  <i class="ri-star-fill"></i>
                  <i class="ri-star-fill"></i>
                  <i class="ri-star-fill"></i>
                  <i class="ri-star-fill"></i>
                  <i class="ri-star-fill"></i>
                </div>
              </div>
            </div>
          </div>    
          <div class="testimonial__card">
            <div class="testimonial__quote">"</div>
            <p class="testimonial__text">
              <?= I18n::get('testimonial_text_3', 'Perfect place to experience authentic Portuguese countryside. The owners are lovely and made us feel at home. The pool area is fantastic with amazing views.') ?>
            </p>
            <div class="testimonial__author">
              <img src="assets/images/Emma_Johnson.jpg" alt="Emma Johnson">
              <div class="author__info">
                <h4><?= I18n::get('testimonial_author_name_3', 'Emma Johnson') ?></h4>
                <p><?= I18n::get('testimonial_author_location_3', 'London, UK') ?></p>
                <div class="rating">
                  <i class="ri-star-fill"></i>
                  <i class="ri-star-fill"></i>
                  <i class="ri-star-fill"></i>
                  <i class="ri-star-fill"></i>
                  <i class="ri-star-fill"></i>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
<button class="gallery-reveal-btn" onclick="window.open('https://docs.google.com/forms/d/e/1FAIpQLSfzD7UZqC1_SoZ5SUhd8EthQv97FC7C8KSiznylvtOGqdeaEg/viewform?usp=dialog', '_blank')">
  <span><?= I18n::get('testimonials_btn', 'Avalie nos') ?></span>
  <i class="ri-arrow-down-s-line"></i>
</button>

    </section>
    <!-- Location Section -->
    <section class="section__container location" id="location">
      <p class="section__subheader"><?= I18n::get('location_subheader', 'Como chegar') ?></p>
      <h2 class="section__header"><?= I18n::get('location_header', 'Localização') ?></h2>
      <div class="map__container">
        <div class="map__info">
          <h3><?= I18n::get('location_h3', 'Quinta Flores') ?></h3>
          <div class="location__detail">
            <i class="ri-map-pin-line"></i>
            <span><?= I18n::get('location_detail_1', 'Travessa da seara 265 Calheiros Ponte de Lima 4990-575, Ponte de Lima, Portugal') ?></span>
          </div>
          <div class="location__detail">
            <i class="ri-phone-line"></i>
            <span><?= I18n::get('location_detail_2', '+351 919 241 169') ?></span>
          </div>
          <div class="location__detail">
            <i class="ri-mail-line"></i>
            <span><?= I18n::get('location_detail_3', 'quinta.flores2019@gmail.com') ?></span>
          </div>
          <div class="location__detail">
            <i class="ri-car-line"></i>
            <span><?= I18n::get('location_detail_4', '45 min do Aeroporto do Porto (OPO)') ?></span>
          </div>
          <div class="location__detail">
            <i class="ri-train-line"></i>
            <span><?= I18n::get('location_detail_5', 'Estação de Ponte de Lima a 10 min') ?></span>
          </div>
        </div>
        <div class="map__frame">
          <iframe src="https://www.google.com/maps/embed?pb=!1m18!1m12!1m3!1d2974.2287591108334!2d-8.5626544!3d41.8018322!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0xd25a686068120f9%3A0x3c3da5552d60c2c5!2sTv.%20da%20Seara%2C%204990-575!5e0!3m2!1spt-PT!2spt!4v1747653205222!5m2!1spt-PT!2spt allowfullscreen="loading="lazy"></iframe>
        </div>
      </div>
    </section>
<script>
// Esta função devolve um ícone diferente conforme o tipo de mensagem (sucesso, erro, info)
function getIconForType(type) {
  switch (type) {
    case 'sucesso': return 'fa-check-circle'; //icone de sucesso
    case 'erro': return 'fa-exclamation-triangle'; //icone para erro
    case 'info': return 'fa-info-circle';   //icone para informação
    default: return 'fa-info-circle'; //icone padrao
  }
}
// Mostrar a flash message
function showFlashMessage(message, tipo = 'sucesso', duration = 4000) {
  // Remover anteriores
  document.querySelectorAll('.flash-message').forEach(el => el.remove());
  const flash = document.createElement('div');
  flash.classList.add('flash-message', `flash-${tipo}`);
  flash.innerHTML = `
    <i class="fa-solid ${getIconForType(tipo)}"></i>
    <span>${message}</span>
  `;
  document.body.appendChild(flash);

  // Mostrar animação
  setTimeout(() => {
    flash.style.opacity = '1';
    flash.style.transform = 'translateX(0)';
  }, 10);

  // Ocultar automaticamente
  setTimeout(() => {
    flash.classList.add('fade-out');
    setTimeout(() => flash.remove(), 400);
  }, duration);
}
</script>
<?php include 'components/footer.php'; ?>
<div id="toast" class="toast"></div>
    <script src="assets/i18n/translator.js"></script>
    <script src="../includes/chatbot.js"></script>
    <!-- CHATBOT </body> -->
<link rel="stylesheet" href="chatbot/chatbot.css">
<script src="chatbot/chatbot.js"></script>
<?php include 'chatbot/chatbot_config.php'; ?>
<?php include 'chatbot/chatbot.php'; ?>
    </body>
    </html>
