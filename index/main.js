// Configurações globais
const config = {
  colors: {
    primary: '#2C7A7B',
    secondary: '#F4A261',
    background: '#F9FAFB',
    cardBg: '#FFFFFF',
    textPrimary: '#1A202C',
    textSecondary: '#718096',
    success: '#38A169',
    error: '#E53E3E'
  },
  scrollReveal: {
    delay: 200,
    distance: '50px',
    origin: 'bottom'
  },
  smoothScroll: {
    duration: 800
  }
};

// Inicialização quando o DOM estiver pronto
document.addEventListener('DOMContentLoaded', () => {
  initMobileMenu();
  initLightbox();
  initLanguageSwitcher();
  initSmoothScroll();
  initScrollReveal();
});

// Menu Mobile
function initMobileMenu() {
  const menuBtn = document.getElementById('menu-btn');
  const navLinks = document.getElementById('nav-links');

  if (menuBtn && navLinks) {
    menuBtn.addEventListener('click', () => {
      const isOpen = navLinks.classList.toggle('open');
      menuBtn.setAttribute('aria-expanded', isOpen);
      
      // Fecha o menu quando um link é clicado (para mobile)
      navLinks.querySelectorAll('a').forEach(link => {
        link.addEventListener('click', () => {
          navLinks.classList.remove('open');
          menuBtn.setAttribute('aria-expanded', false);
        });
      });
    });
  }
}

// Lightbox
function initLightbox() {
  const lightbox = document.getElementById('fulImgBox');
  const lightboxImg = document.getElementById('fulImg');
  const lightboxClose = document.querySelector('.lightbox-close');

  if (lightbox && lightboxImg && lightboxClose) {
    // Adiciona evento para todas as imagens com data-lightbox
    document.querySelectorAll('[data-lightbox]').forEach(img => {
      img.addEventListener('click', () => {
        lightboxImg.src = img.dataset.lightbox || img.src;
        lightboxImg.alt = img.alt || 'Imagem ampliada';
        lightbox.style.display = 'flex';
        document.body.style.overflow = 'hidden';
      });
    });

    // Fecha o lightbox
    lightboxClose.addEventListener('click', () => closeLightbox(lightbox));
    
    // Fecha ao clicar fora da imagem
    lightbox.addEventListener('click', (e) => {
      if (e.target === lightbox) {
        closeLightbox(lightbox);
      }
    });
    
    // Fecha com ESC
    document.addEventListener('keydown', (e) => {
      if (e.key === 'Escape' && lightbox.style.display === 'flex') {
        closeLightbox(lightbox);
      }
    });
  }
}

function closeLightbox(lightbox) {
  lightbox.style.display = 'none';
  document.body.style.overflow = '';
}

// Internacionalização
function initLanguageSwitcher() {
  const flags = document.querySelectorAll('.flag-icon');
  
  flags.forEach(flag => {
    flag.addEventListener('click', () => {
      const lang = flag.dataset.language;
      localStorage.setItem('preferredLang', lang);
      updateContentLanguage(lang);
    });
  });
  
  // Carrega o idioma preferido do localStorage
  const preferredLang = localStorage.getItem('preferredLang') || 'pt';
  updateContentLanguage(preferredLang);
}

function updateContentLanguage(lang) {
  // Aqui você implementaria a mudança de idioma
  console.log(`Idioma alterado para: ${lang}`);
  // Exemplo: document.documentElement.lang = lang;
}

// Rolagem suave
function initSmoothScroll() {
  document.querySelectorAll('a[href^="#"]').forEach(anchor => {
    anchor.addEventListener('click', function(e) {
      e.preventDefault();
      
      const targetId = this.getAttribute('href').substring(1);
      const targetElement = document.getElementById(targetId);
      
      if (targetElement) {
        smoothScrollTo(targetElement, config.smoothScroll.duration);
      }
    });
  });
}

function smoothScrollTo(target, duration) {
  const startPosition = window.pageYOffset;
  const targetPosition = target.getBoundingClientRect().top + window.pageYOffset;
  const distance = targetPosition - startPosition;
  let startTime = null;

  function animation(currentTime) {
    if (startTime === null) startTime = currentTime;
    const timeElapsed = currentTime - startTime;
    const run = easeInOutQuad(timeElapsed, startPosition, distance, duration);

    window.scrollTo(0, run);

    if (timeElapsed < duration) {
      requestAnimationFrame(animation);
    }
  }

  function easeInOutQuad(t, b, c, d) {
    t /= d / 2;
    if (t < 1) return (c / 2) * t * t + b;
    t--;
    return (-c / 2) * (t * (t - 2) - 1) + b;
  }

  requestAnimationFrame(animation);
}

// ScrollReveal
function initScrollReveal() {
  if (typeof ScrollReveal !== 'undefined') {
    const sr = ScrollReveal();
    sr.reveal('.reveal', {
      delay: config.scrollReveal.delay,
      distance: config.scrollReveal.distance,
      origin: config.scrollReveal.origin,
      reset: true,
      viewFactor: 0.2
    });
  }
}

// Função para detectar clique fora de um elemento
function onClickOutside(element, callback) {
  document.addEventListener('click', (e) => {
    if (!element.contains(e.target)) {
      callback();
    }
  });
}


