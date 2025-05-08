// Sistema de tradução para Quinta Flores
document.addEventListener('DOMContentLoaded', function() {
  // Carregar o idioma salvo no localStorage ou usar português como padrão
  const savedLanguage = localStorage.getItem('language') || 'pt';
  
  // Selecionar todas as bandeiras de idioma
  const languageFlags = document.querySelectorAll('.language-flag');
  
  // Objeto para armazenar as traduções
  let translations = {};
  
  // Função para carregar traduções
  async function loadTranslations(lang) {
    try {
      // O caminho correto para os arquivos de tradução
      const response = await fetch(`./translations/${lang}.json`);
      if (!response.ok) {
        throw new Error(`Erro ao carregar traduções: ${response.status}`);
      }
      
      translations = await response.json();
      console.log(`Traduções para ${lang} carregadas com sucesso:`, translations);
      
      // Aplicar as traduções
      applyTranslations();
      
      // Atualizar UI
      updateActiveFlag(lang);
      
      // Salvar a preferência no localStorage
      localStorage.setItem('language', lang);
      
      // Atualizar o atributo lang do HTML
      document.documentElement.setAttribute('lang', lang);
    } catch (error) {
      console.error(`Erro ao carregar traduções para ${lang}:`, error);
      // Se não for português e houver erro, tentar carregar português como fallback
      if (lang !== 'pt') {
        console.log('Tentando carregar português como fallback...');
        loadTranslations('pt');
      }
    }
  }
  
  // Função para aplicar as traduções aos elementos
  function applyTranslations() {
    // Traduzir elementos com atributo data-translate
    document.querySelectorAll('[data-translate]').forEach(element => {
      const key = element.getAttribute('data-translate');
      if (translations[key]) {
        element.textContent = translations[key];
      }
    });
    
    // Traduzir placeholders
    document.querySelectorAll('[data-translate-placeholder]').forEach(element => {
      const key = element.getAttribute('data-translate-placeholder');
      if (translations[key]) {
        element.setAttribute('placeholder', translations[key]);
      }
    });
    
    // Traduzir atributos title
    document.querySelectorAll('[data-translate-title]').forEach(element => {
      const key = element.getAttribute('data-translate-title');
      if (translations[key]) {
        element.setAttribute('title', translations[key]);
      }
    });
  }
  
  // Função para atualizar a bandeira ativa
  function updateActiveFlag(lang) {
    languageFlags.forEach(flag => {
      if (flag.getAttribute('alt').toLowerCase() === lang.toLowerCase()) {
        flag.classList.add('active');
      } else {
        flag.classList.remove('active');
      }
    });
  }
  
  // Adicionar evento de clique às bandeiras
  languageFlags.forEach(flag => {
    flag.addEventListener('click', function() {
      const lang = this.getAttribute('alt').toLowerCase();
      loadTranslations(lang);
    });
  });
  
  // Carregar o idioma salvo ao iniciar
  loadTranslations(savedLanguage);
});