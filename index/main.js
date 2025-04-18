// Menu Mobile
const menuBtn = document.getElementById("menu-btn");
const navLinks = document.getElementById("nav-links");

menuBtn.addEventListener("click", () => {
  const isOpen = navLinks.classList.toggle("open");
  menuBtn.setAttribute("aria-expanded", isOpen);
});

// Lightbox
function setupLightbox() {
  const lightbox = document.getElementById("fulImgBox");
  const lightboxImg = document.getElementById("fulImg");

  document.querySelectorAll("[data-lightbox]").forEach(img => {
    img.addEventListener("click", () => {
      lightboxImg.src = img.dataset.lightbox || img.src;
      lightbox.style.display = "flex";
      document.body.style.overflow = "hidden";
    });
  });

  document.querySelector(".lightbox-close").addEventListener("click", () => {
    lightbox.style.display = "none";
    document.body.style.overflow = "";
  });
}

// Internacionalização
function setupLanguageSwitcher() {
  const flags = document.querySelectorAll(".flag-icon");
  
  flags.forEach(flag => {
    flag.addEventListener("click", () => {
      const lang = flag.dataset.language;
      localStorage.setItem("preferredLang", lang);
      // Redirecionamento ou carregamento de conteúdo
    });
  });
}

// Inicialização
document.addEventListener("DOMContentLoaded", () => {
  setupLightbox();
  setupLanguageSwitcher();
  
  if (typeof ScrollReveal !== 'undefined') {
    ScrollReveal().reveal(".reveal", {
      delay: 200,
      distance: "50px",
      origin: "bottom"
    });
  }
});



// Rolagem suave para as seções
document.querySelectorAll('a[href^="#"]').forEach(anchor => {
  anchor.addEventListener('click', function (e) {
    e.preventDefault();
    
    const targetId = this.getAttribute('href').substring(1);
    const targetElement = document.getElementById(targetId);
    
    if (targetElement) {
      targetElement.scrollIntoView({
        behavior: 'smooth',
        block: 'start'
      });
    }
  });
});
document.querySelectorAll('a[href^="#"]').forEach(anchor => {
  anchor.addEventListener('click', function (e) {
    e.preventDefault(); // Impede o comportamento padrão de salto instantâneo
    
    const targetId = this.getAttribute('href').substring(1); // Obtém o id da seção
    const targetElement = document.getElementById(targetId); // Busca o elemento correspondente
    
    if (targetElement) {
      smoothScrollTo(targetElement, 800); // Aqui você define a velocidade (em milissegundos)
    }
  });
});

// Função para rolagem suave personalizada
function smoothScrollTo(target, duration) {
  const startPosition = window.pageYOffset; // Posição atual da rolagem
  const targetPosition = target.getBoundingClientRect().top + window.pageYOffset; // Posição do destino
  const distance = targetPosition - startPosition; // Distância a ser percorrida
  let startTime = null;

  // Função de animação para a rolagem suave
  function animation(currentTime) {
    if (startTime === null) startTime = currentTime;
    const timeElapsed = currentTime - startTime;
    const run = easeInOutQuad(timeElapsed, startPosition, distance, duration);

    window.scrollTo(0, run); // Rola até a nova posição

    if (timeElapsed < duration) {
      requestAnimationFrame(animation); // Continua a animação até atingir a duração
    }
  }

  // Função de interpolação (easeInOut) para suavizar a rolagem
  function easeInOutQuad(t, b, c, d) {
    t /= d / 2;
    if (t < 1) return (c / 2) * t * t + b;
    t--;
    return (-c / 2) * (t * (t - 2) - 1) + b;
  }

  // Inicia a animação de rolagem suave
  requestAnimationFrame(animation);
}
