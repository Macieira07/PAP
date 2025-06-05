<?php 
$nav_links = [
    ['href' => '#about', 'text' => 'Sobre'],
    ['href' => '#rooms', 'text' => 'Ofertas'],
    ['href' => '#gallery', 'text' => 'Galeria'],
    ['href' => '#amenities', 'text' => 'Comodidades'],
    ['href' => '#testimonials', 'text' => 'Comentários'],
    ['href' => '#location', 'text' => 'Localização'],
    ['href' => '#contactos', 'text' => 'Contactos'],
];
include 'components/header.php'; ?>
<!DOCTYPE html>
<html lang="pt" data-theme="light">
  <head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <meta name="description" content="Alojamento local em Ponte de Lima - Aconchego, natureza e tradição minhota">  
    <link rel="preload" href="imagens/hero.webp" as="image">
    <link rel="preconnect" href="https://fonts.googleapis.com">
        <link rel="icon" type="image/png" sizes="32x32" href="../assets/logos/favicon-32x32.png">
<link rel="icon" type="image/png" sizes="16x16" href="../assets/logos/favicon-16x16.png">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <link rel="stylesheet" type="text/css" href="index/teste.css">
    <link rel="stylesheet" href="components/header.css">
    <link rel="stylesheet" href="components/footer.css">
    <link rel="stylesheet" type="text/css" href="../includes/chatbot.css">
    <link rel="stylesheet" type="text/css" href="assets/i18n/translator.css">
    <link href="https://cdn.jsdelivr.net/npm/remixicon@4.0.0/fonts/remixicon.css" rel="stylesheet"/>
    <title>QUINTA | FLORES</title>
    <link rel="icon" type="image/png" href="../assets/logos/logotipo1.png" sizes="1000x1000">
  </head>
  <body>
    <!-- Cabeçalho/Header  -->
    <!-- Hero Section -->
    <section class="hero">
        <div class="hero__content">
            <h1 class="hero__title">Bem-vindo à Quinta Flores</h1>
            <p class="hero__subtitle">Um espaço pensado para quem valoriza conforto, beleza e boas memórias</p>
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
        <label for="checkIn">Check In</label>
        <input type="date" id="checkIn" required>
      </div>
    </div>
    <div class="input__group">
      <span><i class="ri-calendar-line"></i></span>
      <div>
        <label for="checkOut">Check Out</label>
        <input type="date" id="checkOut" required>
      </div>
    </div>
    <div class="input__group">
      <span><i class="ri-user-line"></i></span>
      <div>
        <label for="guests">Hóspedes</label>
        <input type="number" id="guests" min="1" max="10" value="2" required>
      </div>
    </div>
    <div class="input__group input__btn">
      <button type="button" id="searchBtn" class="btn">Ver disponibilidade</button>
    </div>
  </form>
  <div id="availabilityResult" class="availability__message"></div>
</section>
<script>
// Função para obter o ícone de acordo com o tipo de mensagem
function getIconForType(type) {
  switch (type) {
    case 'success': return 'fa-check-circle';
    case 'error': return 'fa-exclamation-triangle';
    case 'info': return 'fa-info-circle';
    default: return 'fa-info-circle';
  }
}

// Função para mostrar mensagens flash
function showFlashMessage(message, type = 'success', duration = 4000) {
  // Remover mensagens existentes
  document.querySelectorAll('.flash-message').forEach(msg => msg.remove());

  // Criar o elemento da flash message
  const flash = document.createElement('div');
  flash.classList.add('flash-message', `flash-${type}`);
  flash.innerHTML = `
    <div style="display: flex; align-items: center; gap: 10px;">
      <i class="fa-solid ${getIconForType(type)}" style="font-size: 18px;"></i>
      <span>${message}</span>
    </div>
  `;
  document.body.appendChild(flash);

  // Mostrar animação de entrada
  setTimeout(() => {
    flash.style.transform = 'translateX(0)';
    flash.style.opacity = '1';
  }, 10);

  // Remover após duração definida
  setTimeout(() => {
    flash.classList.add('fade-out');
    setTimeout(() => {
      if (flash.parentNode) flash.remove();
    }, 400);
  }, duration);
}

// Função para verificar disponibilidade
function checkAvailability() {
  const checkIn = document.getElementById('checkIn').value;
  const checkOut = document.getElementById('checkOut').value;
  const guests = document.getElementById('guests').value;
  const resultDiv = document.getElementById('availabilityResult');

  // Validações
  if (!checkIn || !checkOut) {
    showFlashMessage('Por favor, preencha as datas de check-in e check-out.', 'error');
    resultDiv.innerHTML = '';
    return;
  }

  const checkInDate = new Date(checkIn);
  const checkOutDate = new Date(checkOut);
  const today = new Date();
  today.setHours(0, 0, 0, 0);

  if (checkInDate < today) {
    showFlashMessage('A data de check-in não pode ser anterior a hoje.', 'error');
    return;
  }

  if (checkOutDate <= checkInDate) {
    showFlashMessage('A data de check-out deve ser posterior à de check-in.', 'error');
    return;
  }

  if (guests < 1 || guests > 10) {
    showFlashMessage('O número de hóspedes deve estar entre 1 e 10.', 'error');
    return;
  }

  // Estado de carregamento
  resultDiv.innerHTML = '<i class="fa-solid fa-spinner fa-spin"></i> Verificando disponibilidade...';
  resultDiv.className = 'availability__message checking';
  showFlashMessage('A verificar disponibilidade...', 'info', 2000);

  // Requisição simulada à API
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
      resultDiv.innerHTML = data.message;
      resultDiv.className = `availability__message ${data.available ? 'available' : 'unavailable'}`;

      if (data.available) {
        showFlashMessage('Disponibilidade confirmada! As datas estão livres.', 'success');
      } else {
        showFlashMessage('Sem disponibilidade para as datas selecionadas.', 'error');
      }
    })
    .catch(error => {
      console.error('Erro:', error);
      showFlashMessage('Ocorreu um erro ao verificar disponibilidade.', 'error');
      resultDiv.innerHTML = '';
    });
}

// Associar evento ao botão
document.getElementById('searchBtn').addEventListener('click', checkAvailability);
</script>
    <!-- About Section -->
    <section class="section__container about__container" id="about">
      <div class="about__image">
        <img src="assets/images/foto_principal_2.jpeg" alt="sobre nós">
      </div>
      <div class="about__content">
        <p class="section__subheader">Quinta Flores </p>
        <h2 class="section__header">Refúgio de Natureza e Conforto em Ponte de Lima
        </h2>
        <p class="section__description">
          Situada no coração da deslumbrante região de Ponte de Lima, a Quinta Flores é uma casa inteira pensada para receber até 10 pessoas num ambiente familiar, tranquilo e repleto de natureza. Aqui, poderá desfrutar de momentos de relaxamento e convívio, rodeado por paisagens serenas e ar puro.
        </p>
        <p class="section__description">
Ideal para famílias, grupos de amigos ou turistas à procura de um refúgio calmo, a Quinta Flores oferece um espaço acolhedor onde a boa comida e a hospitalidade genuína são parte da experiência. Para além do conforto da casa, proporcionamos um ambiente perfeito para explorar as muitas atividades e atrações da região, detalhadas no nosso site para facilitar a sua programação.
Venha descobrir um lugar onde a natureza e o bem-estar se encontram, e crie memórias inesquecíveis na Quinta Flores.
        </p>
          <a href="index/lermais.php" class="btn" style="text-decoration: none;">Ler Mais</a>
        </div>
      </div>
    </section>
<!-- Ofertas Section -->
<section class="section__container rooms__container" id="rooms">
  <p class="section__subheader">Momentos inesquecíveis com condições especiais</p>
  <h2 class="section__header">Descobre as Nossas Ofertas Especiais</h2>

  <div class="rooms__grid">
    
    <div class="room__card">
      <div class="room__image">
        <img src="assets/images/amor.avif" alt="Oferta Tempo de Namorar">
        <div class="room__badge">2 noites</div>
      </div>
      <div class="room__content">
        <h3 class="room__title">Tempo a Dois</h3>
        <p class="room__price">Preço: 260€</p>
        <p class="room__description">
         Dois dias inesquecíveis para reacender a chama do amor
        </p>
        <a href="index/tempo_namorar.php" class="room__link">Planeie a sua experiência</a>
      </div>
    </div>

    <div class="room__card">
      <div class="room__image">
        <img src="assets/images/party.avif" alt="Oferta Festa com Amigos">
        <div class="room__badge">1 a 2 noites</div>
      </div>
      <div class="room__content">
        <h3 class="room__title">Diversão em Grupo</h3>
        <p class="room__price">Preço: 250€</p>
        <p class="room__description">
          O cenário perfeito para celebrar com os teus amigos ao máximo
        </p>
        <a href="../festa_amigos.php" class="room__link">Planeie a sua experiência</a>
      </div>
    </div>

    <div class="room__card">
      <div class="room__image">
        <img src="assets/images/religious.avif" alt="Oferta Retiro de Catequese">
        <div class="room__badge">3 noites</div>
      </div>
      <div class="room__content">
        <h3 class="room__title">	Retiro Espiritual </h3>
        <p class="room__price">Preço: 240€</p>
        <p class="room__description">
            Paz, reflexão e união num ambiente acolhedor com kits espirituais incluídos
        </p>
        <a href="index/retiro_catequese.php" class="room__link">Planeie a sua experiência</a>
      </div>
    </div>
  </div>
</section>

    <!-- Galeria Section -->
<section class="gallery-section" id="gallery">
  <p class="section__subheader">Algumas fotos do Alojamento</p>
  <h2 class="section__header">Galeria</h2>
    <div class="gallery-reveal">
      <button class="gallery-reveal-btn" id="revealBtn">
        <span>Ver Fotos</span>
        <i class="ri-arrow-down-s-line"></i>
      </button>
    </div>
    <div class="gallery-grid hidden" id="galleryGrid">
    <div class="gallery-item">
      <img src="assets/images/6.png" alt="Sala de Estar">
      <div class="gallery-hover-content">
        <h3>Sala de Estar</h3>
        <p>Espaçosa e luminosa</p>
      </div>
    </div>
    <div class="gallery-item">
      <img src="assets/images/7.png" alt="Sala de Jantar">
      <div class="gallery-hover-content">
        <h3>Sala de Jantar</h3>
        <p>Com mesa para 6 pessoas</p>
      </div>
    </div>
    <div class="gallery-item">
      <img src="assets/images/13.png" alt="Cozinha">
      <div class="gallery-hover-content">
        <h3>Cozinha</h3>
        <p>Totalmente Equipada</p>
      </div>
    </div>
    <div class="gallery-item">
      <img src="assets/images/12.png" alt="Cozinha">
      <div class="gallery-hover-content">
        <h3>Cozinha</h3>
        <p>Totalmente Equipada</p>
      </div>
    </div>
    <div class="gallery-item">
      <img src="assets/images/area_comum6.jpg" alt="Área de lazer">
      <div class="gallery-hover-content">
        <h3>Escadas</h3>
        <p>Sala para o Quarto</p>
      </div>
    </div>
    <div class="gallery-item">
      <img src="assets/images/14.png" alt="Entrada para os Quartos">
      <div class="gallery-hover-content">
        <h3>Entrada</h3>
        <p>para os quartos</p>
      </div>
    </div>
    <div class="gallery-item">
      <img src="assets/images/casa_banho_9.jpg" alt="Casa de banho">
      <div class="gallery-hover-content">
        <h3>Casa de banho</h3>
        <p>Partilhada</p>
      </div>
    </div>
    <div class="gallery-item">
      <img src="assets/images/quarto_3.jpg" alt="Quarto equipado">
      <div class="gallery-hover-content">
        <h3>Quarto</h3>
        <p>com 2 camas de casal</p>
      </div>
    </div>
    <div class="gallery-item">
      <img src="assets/images/quarto_3.jpg" alt="Quarto equipado">
      <div class="gallery-hover-content">
        <h3>Quarto</h3>
        <p>com 2 camas de casal</p>
      </div>
    </div>
    <div class="gallery-item">
      <img src="assets/images/quarto_3.jpg" alt="Suite">
      <div class="gallery-hover-content">
        <h3>Suite</h3>
        <p>com 1 cama de casal</p>
      </div>
    </div>
    <div class="gallery-item">
      <img src="assets/images/casa_de_banho_7.jpg" alt="Casa de Banho">
      <div class="gallery-hover-content">
        <h3>Suite</h3>
        <p>Casa de banho</p>
      </div>
    </div>
    <div class="gallery-item">
      <img src="assets/images/casa_de_banho6.jpg" alt="Casa de Banho">
      <div class="gallery-hover-content">
        <h3>Suite</h3>
        <p>Casa de banho</p>
      </div>
    </div>
        <div class="gallery-item">
      <img src="assets/images/foto_principal_4.jpg" alt="Casa vista de fora">
      <div class="gallery-hover-content">
        <h3>Casa</h3>
        <p>Vista de fora</p>
      </div>
    </div>
            <div class="gallery-item">
      <img src="assets/images/19.png" alt="Casa vista de cima">
      <div class="gallery-hover-content">
        <h3>Casa</h3>
        <p>Vista de cima</p>
      </div>
    </div>
            <div class="gallery-item">
      <img src="assets/images/natureza23.jpg" alt="Entrada para jardim e piscina">
      <div class="gallery-hover-content">
        <h3>Entrada</h3>
        <p>Para o jardim e a Piscina</p>
      </div>
    </div>
      <div class="gallery-item">
      <img src="assets/images/piscina2.jpg" alt="Piscina">
      <div class="gallery-hover-content">
        <h3>Piscina</h3>
        <p>Com direito a duas espreguiçadeiras e toalhas</p>
      </div>
    </div>
          <div class="gallery-item">
      <img src="assets/images/churrasco.jpg" alt="churrasco">
      <div class="gallery-hover-content">
        <h3>Churrasco</h3>
        <p>e momentos com todos</p>
      </div>
    </div>
          <div class="gallery-item">
      <img src="assets/images/foto_principal_3.png" alt="garagem">
      <div class="gallery-hover-content">
        <h3>Garagem</h3>
        <p>Para dois carros</p>
      </div>
    </div>
          <div class="gallery-item">
      <img src="assets/images/entrada_3.jpg" alt="entrada">
      <div class="gallery-hover-content">
        <h3>Entrada</h3>
        <p>2º</p>
      </div>
    </div>
          <div class="gallery-item">
      <img src="assets/images/entrada_2.jpg" alt="">
      <div class="gallery-hover-content">
        <h3></h3>
        <p></p>
      </div>
    </div>
  </div> 
  <script>
    // JavaScript modificado
    document.addEventListener('DOMContentLoaded', function() {
      const revealBtn = document.getElementById('revealBtn');
      const galleryGrid = document.getElementById('galleryGrid');
      let isRevealed = false;

      revealBtn.addEventListener('click', function() {
        isRevealed = !isRevealed;
        
        if(isRevealed) {
          galleryGrid.classList.remove('hidden');
          galleryGrid.classList.add('revealed');
          revealBtn.innerHTML = '<span>Ocultar Fotos</span><i class="ri-arrow-up-s-line"></i>';
          
          // Posicionar corretamente
          const section = document.getElementById('gallerySection');
          const rect = section.getBoundingClientRect();
        } else {
          galleryGrid.classList.remove('revealed');
          galleryGrid.classList.add('hidden');
          revealBtn.innerHTML = '<span>Ver Fotos</span><i class="ri-arrow-down-s-line"></i>';
        }
      });
    });
  </script>
      
    </div>
  </div>
</section>
<!--Comodidades -->
<section class="section__container comodidades" id="amenities">
  <p class="section__subheader">O que este espaço oferece</p>
  <h2 class="section__header">Comodidades</h2>
  <div class="comodidades__container">
    <ul class="comodidades__list">
      <li>
        <i class="fa-solid fa-wifi"></i>
        <span>Wi-Fi Gratuito</span>
      </li>
      <li>
        <i class="fa-solid fa-person-swimming"></i>
        <span>Piscina Exterior</span>
      </li>
      <li>
        <i class="fa-solid fa-square-parking"></i>
        <span>Estacionamento Privado</span>
      </li>
      <li>
        <i class="fa-solid fa-bed"></i>
        <span>Lençóis e Toalhas de Alta Qualidade</span>
      </li>
      <li>
        <i class="fa-solid fa-gamepad"></i>
        <span>Área de Lazer</span>
      </li>
      <li>
        <i class="fa-solid fa-fire"></i>
        <span>Lareira</span>
      </li>
      <li>
        <i class="fa-solid fa-soap"></i>
        <span>Máquina de Lavar Roupa</span>
      </li>
      <li>
        <i class="fa-solid fa-tv"></i>
        <span>Televisão</span>
      </li>
      <li>
        <i class="fa-solid fa-kitchen-set"></i>
        <span>Cozinha Totalmente Equipada</span>
      </li>
      <li>
        <i class="fa-solid fa-kit-medical"></i>
        <span>Kit de Primeiros Socorros</span>
      </li>
      <li>
        <i class="fa-solid fa-hot-tub-person"></i>
        <span>Jacuzzi</span>
      </li>
      <li>
        <i class="fa-solid fa-drumstick-bite"></i>
        <span>Churrasqueira</span>
      </li>
    </ul>
  </div>
</section>
    <!-- Testimonials Section -->
    <section class="section__container testimonials" id="testimonials">
      <p class="section__subheader">Comentários</p>
      <h2 class="section__header">O Que Dizem os Nossos Hóspedes</h2>
      <div class="testimonial__container">
        <div class="testimonial__grid">
          <div class="testimonial__card">
            <div class="testimonial__quote">"</div>
            <p class="testimonial__text">
              A Quinta Flores superou todas as nossas expectativas. O local é lindo, os quartos impecáveis e o pequeno-almoço delicioso com produtos regionais. Voltaremos com certeza!
            </p>
            <div class="testimonial__author">
              <img src="assets/images/Ana_Silva.jpg" alt="Ana Silva">
              <div class="author__info">
                <h4>Ana Silva</h4>
                <p>Porto, Portugal</p>
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
              Foi nossa segunda estadia e foi tão boa quanto a primeira. A atenção da equipa é excepcional e adoramos os passeios sugeridos pela região. Recomendamos!
            </p>
            <div class="testimonial__author">
              <img src="assets/images/Carlos_Mendes.jpg" alt="Carlos Mendes">
              <div class="author__info">
                <h4>Carlos Mendes</h4>
                <p>Lisboa, Portugal</p>
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
              Perfect place to experience authentic Portuguese countryside. The owners are lovely and made us feel at home. The pool area is fantastic with amazing views.
            </p>
            <div class="testimonial__author">
              <img src="assets/images/Emma_Johnson.jpg" alt="Emma Johnson">
              <div class="author__info">
                <h4>Emma Johnson</h4>
                <p>London, UK</p>
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
  <span>Avalie nos</span>
  <i class="ri-arrow-down-s-line"></i>
</button>

    </section>
    <!-- Location Section -->
    <section class="section__container location" id="location">
      <p class="section__subheader">Como chegar</p>
      <h2 class="section__header">Localização</h2>
      <div class="map__container">
        <div class="map__info">
          <h3>Quinta Flores</h3>
          <div class="location__detail">
            <i class="ri-map-pin-line"></i>
            <span>Travessa da seara 265 Calheiros Ponte de Lima 4990-575, Ponte de Lima, Portugal</span>
          </div>
          <div class="location__detail">
            <i class="ri-phone-line"></i>
            <span>+351 919 241 169</span>
          </div>
          <div class="location__detail">
            <i class="ri-mail-line"></i>
            <span>quinta.flores2019@gmail.com</span>
          </div>
          <div class="location__detail">
            <i class="ri-car-line"></i>
            <span>45 min do Aeroporto do Porto (OPO)</span>
          </div>
          <div class="location__detail">
            <i class="ri-train-line"></i>
            <span>Estação de Ponte de Lima a 10 min</span>
          </div>
        </div>
        <div class="map__frame">
          <iframe src="https://www.google.com/maps/embed?pb=!1m18!1m12!1m3!1d2974.2287591108334!2d-8.5626544!3d41.8018322!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0xd25a686068120f9%3A0x3c3da5552d60c2c5!2sTv.%20da%20Seara%2C%204990-575!5e0!3m2!1spt-PT!2spt!4v1747653205222!5m2!1spt-PT!2spt allowfullscreen="loading="lazy"></iframe>
        </div>
      </div>
    </section>
<script>
// Ícone de acordo com o tipo
function getIconForType(type) {
  switch (type) {
    case 'sucesso': return 'fa-check-circle';
    case 'erro': return 'fa-exclamation-triangle';
    case 'info': return 'fa-info-circle';
    default: return 'fa-info-circle';
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
    </body>
    </html>
