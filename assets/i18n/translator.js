// Objeto para armazenar as traduções carregadas
let translations = {};
let currentLanguage = localStorage.getItem('selectedLanguage') || 'pt';

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
        console.error(`Erro ao carregar o arquivo de tradução para ${lang}:`, error);
        if (lang !== 'pt') {
            return loadTranslations('pt');
        }
        return null;
    }
}

// Função para atualizar os textos da página com base no idioma selecionado
function updateTexts(langData) {
    if (!langData) return;

    // Atualizar o atributo lang do documento HTML
    document.documentElement.lang = currentLanguage;
    
    // Atualizar todos os elementos com a classe 'lang' e atributo data-key
    document.querySelectorAll('.lang[data-key]').forEach(element => {
        const key = element.getAttribute('data-key');
        if (key && langData[key]) {
            if (element.tagName === 'INPUT' || element.tagName === 'TEXTAREA') {
                // Para campos de input e textarea, atualizar o placeholder
                element.placeholder = langData[key];
                
                // Se o campo estiver vazio, também atualizar o valor
                if (!element.value) {
                    element.value = langData[key];
                }
            } else {
                element.textContent = langData[key];
            }
        }
    });

    // Atualizar meta tags
    const descriptionMeta = document.querySelector('meta[name="description"]');
    if (descriptionMeta && langData.meta_description) {
        descriptionMeta.content = langData.meta_description;
    }

    // Atualizar placeholders específicos
    const placeholders = {
        name: langData.name || 'Nome',
        email: langData.email_placeholder || 'Email',
        phone: langData.phone_placeholder || 'Telefone',
        message: langData.message || 'Mensagem',
        chat: langData.chat_write || 'Escreva a sua mensagem...'
    };

    // Aplicar placeholders traduzidos
    Object.entries(placeholders).forEach(([key, value]) => {
        document.querySelectorAll(`[data-placeholder="${key}"]`).forEach(element => {
            element.placeholder = value;
        });
    });
}

// Função para alternar entre idiomas
async function changeLanguage(lang) {
    if (currentLanguage === lang) return;

    // Remover a classe 'active' de todas as bandeiras
    document.querySelectorAll('.language-flag').forEach(flag => {
        flag.classList.remove('active');
    });

    // Adicionar a classe 'active' à bandeira do idioma selecionado
    const selectedFlag = document.querySelector(`.language-flag[data-lang="${lang}"]`);
    if (selectedFlag) {
        selectedFlag.classList.add('active');
    }

    // Carregar as traduções
    if (!translations[lang]) {
        const langData = await loadTranslations(lang);
        if (langData) {
            updateTexts(langData);
            localStorage.setItem('selectedLanguage', lang);
            currentLanguage = lang;
        }
    } else {
        updateTexts(translations[lang]);
        localStorage.setItem('selectedLanguage', lang);
        currentLanguage = lang;
    }
}

// Inicializar a funcionalidade quando o DOM estiver pronto
document.addEventListener('DOMContentLoaded', async function() {
    // Carregar o idioma salvo ou o padrão (português)
    const savedLanguage = localStorage.getItem('selectedLanguage') || 'pt';
    
    // Carregar as traduções iniciais
    await loadTranslations(savedLanguage);
    
    // Atualizar a interface com o idioma carregado
    if (translations[savedLanguage]) {
        updateTexts(translations[savedLanguage]);
        
        // Atualizar a bandeira ativa
        document.querySelectorAll('.language-flag').forEach(flag => {
            if (flag.getAttribute('data-lang') === savedLanguage) {
                flag.classList.add('active');
            } else {
                flag.classList.remove('active');
            }
        });
    }

    // Adicionar eventos de clique às bandeiras
    document.querySelectorAll('.language-flag').forEach(flag => {
        flag.addEventListener('click', function() {
            const lang = this.getAttribute('data-lang');
            changeLanguage(lang);
        });
    });

    // Adicionar manipulador para o evento de envio do formulário
    const forms = document.querySelectorAll('form');
    forms.forEach(form => {
        form.addEventListener('submit', function(e) {
            // Evitar que campos de placeholder sejam enviados como valores
            const inputs = form.querySelectorAll('input[type="text"], input[type="email"], input[type="tel"], textarea');
            inputs.forEach(input => {
                if (input.value === input.placeholder) {
                    input.value = '';
                }
            });
        });
    });
});
