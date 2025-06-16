// Sistema de tradução para Quinta Flores
document.addEventListener('DOMContentLoaded', function() {
  // Carregar o idioma da URL ou do localStorage
  const urlParams = new URLSearchParams(window.location.search);
  const lang = urlParams.get('lang') || localStorage.getItem('language') || 'pt';
  
  // Objeto para armazenar as traduções
  let translations = {};
  
  // Função para carregar traduções
  async function loadTranslations(lang) {
    try {
      const response = await fetch(`translations/${lang}.json`);
      if (!response.ok) {
        throw new Error(`Erro ao carregar traduções: ${response.status}`);
      }
      
      translations = await response.json();
      console.log(`Traduções para ${lang} carregadas com sucesso:`, translations);
      
      // Aplicar as traduções
      applyTranslations();
      
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

    // Traduzir labels de formulários
    document.querySelectorAll('[data-translate-label]').forEach(element => {
      const key = element.getAttribute('data-translate-label');
      if (translations[key]) {
        element.innerHTML = translations[key];
      }
    });
  }
  
  // Carregar o idioma ao iniciar
  loadTranslations(lang);
});