// Objeto para armazenar as traduções carregadas
let translations = {};
let currentLanguage = localStorage.getItem("selectedLanguage") || "pt";

// Função para carregar o arquivo de tradução de um idioma específico
async function loadTranslations(lang) {
  try {
    const response = await fetch(`assets/i18n/${lang}.json`);
    if (!response.ok) {
      throw new Error(`HTTP error! Status: ${response.status}`);
    }
    translations[lang] = await response.json();
    return translations[lang];
  } catch (error) {
    console.error(
      `Erro ao carregar o arquivo de tradução para ${lang}:`,
      error
    );
    // Em caso de erro, usar português como fallback
    if (lang !== "pt") {
      return loadTranslations("pt");
    }
    return null;
  }
}

// Função para atualizar os textos da página com base no idioma selecionado
function updateTexts(langData) {
  if (!langData) return;

  // Seleciona todos os elementos com a classe 'lang'
  const elements = document.querySelectorAll(".lang");

  // Atualiza o texto de cada elemento
  elements.forEach((element) => {
    const key = element.getAttribute("data-key");
    if (key && langData[key]) {
      element.textContent = langData[key];
    }
  });

  // Atualizamos também os placeholders dos inputs
  document.querySelector('input[placeholder="Check In"]').placeholder =
    langData.check_in || "Check In";
  document.querySelector('input[placeholder="Check Out"]').placeholder =
    langData.check_out || "Check Out";
  document.querySelector('input[placeholder="Guest"]').placeholder =
    langData.guests || "Guests";

  // Atualizar outras seções da página que não têm a classe 'lang'
  const aboutSubtitle = document.querySelector(".section__subheader");
  if (aboutSubtitle)
    aboutSubtitle.textContent = langData.about_subtitle || "SOBRE NÓS";

  const aboutTitle = document.querySelector(".section__header");
  if (aboutTitle)
    aboutTitle.textContent =
      langData.about_title ||
      "O melhor sítio para relaxar e passar as férias é aqui!";

  const aboutDescription = document.querySelector(".section__description");
  if (aboutDescription)
    aboutDescription.textContent =
      langData.about_description || "Se procura um local tranquilo...";

  const readMoreBtn = document.querySelector(".btn");
  if (readMoreBtn) readMoreBtn.textContent = langData.read_more || "Ler mais";

  const footerReservation = document.querySelector(
    ".footer__col .animated-button1 span"
  );
  if (footerReservation)
    footerReservation.textContent =
      langData.make_reservation || "Fazer Reserva";

  const copyright = document.querySelector(".footer__bar");
  if (copyright)
    copyright.textContent =
      langData.copyright ||
      "Copyright © 2025 QUINTA FLORES. Todos os direitos reservados.";
}

// Função para alternar entre idiomas
async function changeLanguage(lang) {
  // Remover a classe 'active' de todas as bandeiras
  document.querySelectorAll(".language-flag").forEach((flag) => {
    flag.classList.remove("active");
  });

  // Adicionar a classe 'active' à bandeira do idioma selecionado
  document
    .querySelector(`.language-flag[data-lang="${lang}"]`)
    .classList.add("active");

  // Carregar as traduções se ainda não estiverem carregadas
  if (!translations[lang]) {
    await loadTranslations(lang);
  }

  // Atualizar os textos na página
  updateTexts(translations[lang]);

  // Salvar a preferência de idioma no localStorage
  localStorage.setItem("selectedLanguage", lang);
  currentLanguage = lang;

  // Atualizar o atributo lang no elemento HTML
  document.documentElement.lang = lang;
}

// Inicializar a funcionalidade quando o DOM estiver pronto
document.addEventListener('DOMContentLoaded', function() {
    const flags = document.querySelectorAll('.language-flag');
    let currentLang = localStorage.getItem('lang') || 'pt';
    
    // Carregar o idioma inicial
    loadTranslations(currentLang);
    updateActiveFlag(currentLang);

    // Adicionar evento de clique para cada bandeira
    flags.forEach(flag => {
        flag.addEventListener('click', function() {
            const lang = this.getAttribute('data-lang');
            localStorage.setItem('lang', lang);
            loadTranslations(lang);
            updateActiveFlag(lang);
        });
    });

    function updateActiveFlag(lang) {
        flags.forEach(flag => {
            if (flag.getAttribute('data-lang') === lang) {
                flag.classList.add('active');
            } else {
                flag.classList.remove('active');
            }
        });
    }

    function loadTranslations(lang) {
        fetch(`assets/i18n/${lang}.json`)
            .then(response => response.json())
            .then(translations => {
                document.querySelectorAll('[data-key]').forEach(element => {
                    const key = element.getAttribute('data-key');
                    if (translations[key]) {
                        element.textContent = translations[key];
                    }
                });
            })
            .catch(error => console.error('Error loading translations:', error));
    }
});
