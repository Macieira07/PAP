/*
============================================================
  Chatbot Interativo - Quinta Flores
============================================================

  Linguagens Utilizadas:
    - JavaScript (ES6+)
    - HTML5 (DOM)
    - CSS3 (estilos externos)

  Bibliotecas e Frameworks:
    - Nenhuma dependência externa obrigatória
    - Font Awesome & Remixicon (ícones)

  Estrutura do Script:
    1. Inicialização e eventos principais
    2. Funções de envio e resposta
    3. Respostas multilíngues e personalizadas
    4. Armazenamento local da conversa
    5. Efeitos visuais e sonoros

  Autor: [Seu Nome ou Equipa]
  Última atualização: [Data]
============================================================
*/
// ===================== 1. Inicialização e Eventos =====================
document.addEventListener('DOMContentLoaded', function () {
    const chatbotContainer = document.getElementById('chatbot-container');
    const chatbotToggle = document.getElementById('chatbot-toggle');
    const chatbotClose = document.getElementById('chatbot-close');
    const chatbotMessages = document.getElementById('chatbot-messages');
    const chatbotInput = document.getElementById('chatbot-input');
    const chatbotSend = document.getElementById('chatbot-send');
    const quickButtons = document.querySelectorAll('.quick-btn');

    let isChatbotOpen = false;

    // Alternar visibilidade do chatbot
    chatbotToggle.addEventListener('click', toggleChatbot);
    chatbotClose.addEventListener('click', toggleChatbot);

    function toggleChatbot() {
        isChatbotOpen = !isChatbotOpen;
        if (isChatbotOpen) {
            chatbotContainer.classList.add('chatbot-visible');
            chatbotInput.focus();
            // Mensagem de boas-vindas
            if (chatbotMessages.children.length === 0) {
                addBotMessage(getWelcomeMessage());
            }
        } else {
            chatbotContainer.classList.remove('chatbot-visible');
        }
    }
    //limpar o chat
    document.getElementById('chatbot-clear').addEventListener('click', () => {
        if (confirm('Tem certeza que deseja limpar a conversa?')) {
            chatbotMessages.innerHTML = '';
            // Se usares armazenamento local:
            localStorage.removeItem('chatbotConversation');
        }
    });


    // Enviar mensagem ao clicar no botão ou pressionar Enter
    chatbotSend.addEventListener('click', sendMessage);
    chatbotInput.addEventListener('keypress', function (e) {
        if (e.key === 'Enter') {
            sendMessage();
        }
    });

    // Botões rápidos
    quickButtons.forEach(button => {
        button.addEventListener('click', function () {
            const query = this.getAttribute('data-query');
            sendQuickQuery(query);
        });
    });
    //Quando o bot "está a pensar", mostra um balão com "Digitando..." antes da resposta aparecer.
    function showTypingIndicator() {
        const typingDiv = document.createElement('div');
        typingDiv.classList.add('message', 'bot-message', 'typing');
        typingDiv.textContent = 'Digitando...';
        chatbotMessages.appendChild(typingDiv);
        chatbotMessages.scrollTop = chatbotMessages.scrollHeight;
        return typingDiv;
    }

    // Efeitos sonoros sutis
    const sndSend = new Audio('https://cdn.pixabay.com/audio/2022/07/26/audio_124bfae7b2.mp3'); // som leve de envio
    const sndReceive = new Audio('https://cdn.pixabay.com/audio/2022/07/26/audio_124bfae7b2.mp3'); // som leve de recebimento (pode trocar por outro)
    function playSendSound() { try { sndSend.currentTime = 0; sndSend.play(); } catch (e) { } }
    function playReceiveSound() { try { sndReceive.currentTime = 0; sndReceive.play(); } catch (e) { } }

    function sendMessage() {
        const message = chatbotInput.value.trim();
        if (message) {
            addUserMessage(message);
            chatbotInput.value = '';
            playSendSound();
            processUserMessage(message);
        }
    }

    function sendQuickQuery(query) {
        addUserMessage(getQuickQueryText(query));
        processUserMessage(query);
    }
    function addUserMessage(text) {
        const messageDiv = document.createElement('div');
        messageDiv.classList.add('message', 'user-message');
        messageDiv.innerHTML = `<span class='avatar user-avatar'></span><span class='msg-text'>${text}</span>`;
        chatbotMessages.appendChild(messageDiv);
        scrollToBottom();
        saveConversation();
    }
    function addBotMessage(text) {
        const messageDiv = document.createElement('div');
        messageDiv.classList.add('message', 'bot-message');
        messageDiv.innerHTML = `<span class='avatar bot-avatar'></span><span class='msg-text'>${text}</span>`;
        chatbotMessages.appendChild(messageDiv);
        scrollToBottom();
        saveConversation();
    }
    function scrollToBottom() {
        chatbotMessages.scrollTop = chatbotMessages.scrollHeight;
    }

    function processUserMessage(message) {
        // Mostrar indicador de digitação
        const typingIndicator = document.createElement('div');
        typingIndicator.classList.add('message', 'bot-message', 'typing-indicator');
        typingIndicator.innerHTML = '<span></span><span></span><span></span>';
        chatbotMessages.appendChild(typingIndicator);
        scrollToBottom();

        setTimeout(() => {
            // Remove o indicador de digitação
            typingIndicator.remove();

            // Obter resposta do bot e mostrar com animação
            const response = getBotResponse(message.toLowerCase());
            addBotMessage(response);
            playReceiveSound();
        }, 1200); // duração do "typing" em ms
    }

    function getQuickQueryText(query) {
        const texts = {
            'reservas': 'Gostaria de informações sobre reservas',
            'preços': 'Quero saber sobre preços',
            'localização': 'Como chegar à Quinta Flores?',
            'contato': 'Quero entrar em contato',
            'valiacao': 'Gostaria de deixar uma avaliação'
        };
        return texts[query] || query;
    }

    function getQuickQueryText(query) {
        const texts = {
            'reservas': 'Gostaria de informações sobre reservas',
            'preços': 'Quero saber sobre preços',
            'localização': 'Como chegar à Quinta Flores?',
            'contato': 'Quero entrar em contato'
        };
        return texts[query] || query;
    }
    //Desativar input e botão "Enviar" enquanto o bot responde
    function disableInput() {
        chatbotInput.disabled = true;
        chatbotSend.disabled = true;
    }

    function enableInput() {
        chatbotInput.disabled = false;
        chatbotSend.disabled = false;
    }


    // 3. Estrutura multilíngue (exemplo, pode expandir depois)
    const chatbotLang = document.documentElement.lang || 'pt';
    const chatbotTexts = {
        pt: {
            welcome: '<b>Bem-vindo ao Chatbot da Quinta Flores!</b><br>Como posso ajudar você hoje? 😊',
            fallback: 'Desculpe, não entendi completamente sua pergunta. 😕<br><br>Você pode reformular ou escolher uma destas opções:<br>• Informações sobre reservas<br>• Preços e pacotes<br>• Como chegar<br>• Serviços oferecidos<br>• Atividades na região',
            greetings: 'Olá novamente! No que posso ajudar hoje? 😊',
            reservas: `Para fazer uma reserva ou verificar disponibilidade, você pode:<br>1. Usar nosso sistema online <a href="../login1/pagina_login.php" style="color: var(--chatbot-primary); font-weight: 600;">clicando aqui</a><br>2. Entrar em contato pelo telefone: <strong>+351 912 418 976</strong><br>3. Enviar um email para: <strong>quinta.flores2019@gmail.com</strong><br><br>Posso ajudar com mais alguma informação sobre reservas?`,
            precos: `Nossos preços variam conforme a temporada e tipo de acomodação:<br><br>• <strong>Tempo a Dois</strong>: 260€ (2 noites)<br>• <strong>Diversão em Grupo</strong>: 250€ (1-2 noites)<br>• <strong>Retiro Espiritual</strong>: 240€ (3 noites)<br><br>• <strong>Uma reserva normal fica por </strong>: 120€ por noite<br><br>Todos os preços são por grupo. Temos estes pacotes promocionais. Gostaria de verificar disponibilidade para alguma data?`,
            localizacao: `A Quinta Flores está localizada em:<br><br><strong>Travessa da seara 265 Calheiros<br>4990-575 Ponte de Lima, Viana do Castelo, Portugal</strong><br><br>Como chegar:<br>• <strong>Do Aeroporto do Porto</strong>: 45 minutos de carro pela A3<br>• <strong>De Viana do Castelo</strong>: 30 minutos<br>• <strong>De Braga</strong>: 20 minutos<br><br><a href="https://maps.app.goo.gl/example" target="_blank" style="color: var(--chatbot-primary); font-weight: 600;">Ver no Google Maps</a>`,
            contato: `Você pode entrar em contato conosco por:<br><br>• <strong>Telefone</strong>: +351 912 418 976<br>• <strong>Email</strong>: quinta.flores2019@gmail.com<br>• <strong>WhatsApp</strong>: +351 912 418 976<br><br>Horário de atendimento: 8h às 22h (todos os dias)`,
            servicos: `Nossas comodidades incluem:<br><br>• Piscina exterior<br>• Wi-Fi gratuito<br>• Estacionamento privado<br>• Cozinha totalmente equipada<br>• Área de churrasco<br>• Máquina de lavar roupa<br>• TV e área de lazer<br><br>Precisa de mais informações sobre algum serviço específico?`,
            atividades: `Na região de Ponte de Lima, recomendamos:<br><br>• <strong>Trilhas na natureza</strong>: Ecovia do Lima<br>• <strong>Gastronomia</strong>: Restaurantes com comida típica minhota<br>• <strong>Cultura</strong>: Centro histórico de Ponte de Lima<br>• <strong>Praia fluvial</strong>: A 10 minutos da Quinta<br>• <strong>Festival Internacional de Jardins</strong>: Evento anual<br><br>Posso sugerir atividades específicas para seu grupo?`,
            avaliacao: `Ficamos muito felizes em saber que deseja deixar uma avaliação! 😊<br><br>Pode fazê-lo de forma rápida através do nosso formulário no Google:<br><a href='https://docs.google.com/forms/d/e/1FAIpQLSfzD7UZqC1_SoZ5SUhd8EthQv97FC7C8KSiznylvtOGqdeaEg/viewform?usp=dialog', '_blank' target="_blank" style="color: var(--chatbot-primary); font-weight: 600;">Clique aqui para deixar a sua opinião</a><br><br>A sua opinião é muito importante para continuarmos a melhorar! Obrigado. 🙏`,
            animais: `<i class='fa-solid fa-dog' style='color:#ffe066; margin-right:6px;'></i> Aceitamos apenas animais de <strong>porte pequeno</strong> e mediante consulta prévia. Para garantir conforto e segurança, entre em contato antes de reservar.`,
            estacionamento: `<i class='fa-solid fa-square-parking' style='color:#4caf50; margin-right:6px;'></i> Temos estacionamento <strong>gratuito</strong> para até <strong>2 carros dentro da garagem</strong>. Vagas adicionais podem ser consultadas.`,
            cafe: `<i class='fa-solid fa-mug-hot' style='color:#ff9800; margin-right:6px;'></i> O pequeno-almoço <strong>não está incluído</strong>, mas pode ser solicitado à parte. Consulte-nos para opções e valores.`,
            refeicoes: `<i class='fa-solid fa-utensils' style='color:#ff9800; margin-right:6px;'></i> Em reservas normais <strong>nenhuma refeição está incluída</strong>. Podemos sugerir restaurantes típicos da região ou, se desejar, consultar opções de refeições à parte. Fale conosco para mais detalhes!`
        },
        en: {
            welcome: '<b>Welcome to Quinta Flores Chatbot!</b><br>How can I help you today? 😊',
            fallback: "Sorry, I didn't understand your question. 😕<br><br>You can rephrase or choose one of these options:<br>• Reservation info<br>• Prices and packages<br>• How to get here<br>• Services<br>• Activities in the region",
            greetings: 'Hello again! How can I help you today? 😊',
            reservas: `To make a reservation or check availability, you can:<br>1. Use our online system <a href="../login1/pagina_login.php" style="color: var(--chatbot-primary); font-weight: 600;">by clicking here</a><br>2. Contact us by phone: <strong>+351 912 418 976</strong><br>3. Send an email to: <strong>quinta.flores2019@gmail.com</strong><br><br>Can I help you with more information about reservations?`,
            precos: `Our prices vary according to the season and type of accommodation:<br><br>• <strong>Romantic Getaway</strong>: €260 (2 nights)<br>• <strong>Group Fun</strong>: €250 (1-2 nights)<br>• <strong>Spiritual Retreat</strong>: €240 (3 nights)<br><br>• <strong>A regular booking is</strong>: €120 per night<br><br>All prices are per group. We have these promotional packages. Would you like to check availability for a specific date?`,
            localizacao: `Quinta Flores is located at:<br><br><strong>Travessa da seara 265 Calheiros<br>4990-575 Ponte de Lima, Viana do Castelo, Portugal</strong><br><br>How to get here:<br>• <strong>From Porto Airport</strong>: 45 minutes by car via A3<br>• <strong>From Viana do Castelo</strong>: 30 minutes<br>• <strong>From Braga</strong>: 20 minutes<br><br><a href="https://maps.app.goo.gl/example" target="_blank" style="color: var(--chatbot-primary); font-weight: 600;">View on Google Maps</a>`,
            contato: `You can contact us by:<br><br>• <strong>Phone</strong>: +351 912 418 976<br>• <strong>Email</strong>: quinta.flores2019@gmail.com<br>• <strong>WhatsApp</strong>: +351 912 418 976<br><br>Service hours: 8am to 10pm (every day)`,
            servicos: `Our amenities include:<br><br>• Outdoor pool<br>• Free Wi-Fi<br>• Private parking<br>• Fully equipped kitchen<br>• Barbecue area<br>• Washing machine<br>• TV and leisure area<br><br>Do you need more information about any specific service?`,
            atividades: `In the Ponte de Lima region, we recommend:<br><br>• <strong>Nature trails</strong>: Ecovia do Lima<br>• <strong>Gastronomy</strong>: Restaurants with typical Minho cuisine<br>• <strong>Culture</strong>: Historic center of Ponte de Lima<br>• <strong>River beach</strong>: 10 minutes from Quinta<br>• <strong>International Garden Festival</strong>: Annual event<br><br>Can I suggest specific activities for your group?`,
            avaliacao: `We are very happy that you want to leave a review! 😊<br><br>You can do it quickly through our Google form:<br><a href='https://docs.google.com/forms/d/e/1FAIpQLSfzD7UZqC1_SoZ5SUhd8EthQv97FC7C8KSiznylvtOGqdeaEg/viewform?usp=dialog', '_blank' target="_blank" style="color: var(--chatbot-primary); font-weight: 600;">Click here to leave your opinion</a><br><br>Your feedback is very important for us to keep improving! Thank you. 🙏`,
            animais: `<i class='fa-solid fa-dog' style='color:#ffe066; margin-right:6px;'></i> We only accept <strong>small pets</strong> and upon prior consultation. For comfort and safety, please contact us before booking.`,
            estacionamento: `<i class='fa-solid fa-square-parking' style='color:#4caf50; margin-right:6px;'></i> We have <strong>free parking</strong> for up to <strong>2 cars inside the garage</strong>. Additional spaces can be requested.`,
            cafe: `<i class='fa-solid fa-mug-hot' style='color:#ff9800; margin-right:6px;'></i> Breakfast is <strong>not included</strong>, but can be requested separately. Contact us for options and prices.`,
            refeicoes: `<i class='fa-solid fa-utensils' style='color:#ff9800; margin-right:6px;'></i> For regular bookings <strong>no meals are included</strong>. We can suggest typical restaurants in the region or, if you wish, check meal options separately. Contact us for more details!`
        }
    };
    function getWelcomeMessage() {
        return chatbotTexts[chatbotLang]?.welcome || chatbotTexts.pt.welcome;
    }
    function getBotResponse(message) {
        const t = chatbotTexts[chatbotLang] || chatbotTexts.pt;
        if (window.chatbotResponses && window.chatbotResponses[message]) {
            return window.chatbotResponses[message];
        }
        if (message.includes('olá') || message.includes('oi') || message.includes('bom dia') || message.includes('hello') || message.includes('hi') || message.includes('good morning')) {
            return t.greetings;
        }
        if (message.includes('reserva') || message.includes('disponibilidade') || message.includes('book') || message.includes('reservation') || message.includes('availability')) {
            return t.reservas;
        }
        if (message.includes('preço') || message.includes('valor') || message.includes('custo') || message.includes('price')) {
            return t.precos;
        }
        if (message.includes('localização') || message.includes('chegar') || message.includes('mapa') || message.includes('address') || message.includes('location') || message.includes('how to get')) {
            return t.localizacao;
        }
        if (message.includes('contato') || message.includes('telefone') || message.includes('email') || message.includes('contact') || message.includes('phone')) {
            return t.contato;
        }
        if (message.includes('serviço') || message.includes('comodidade') || message.includes('amenity') || message.includes('service')) {
            return t.servicos;
        }
        if (message.includes('atividade') || message.includes('passeio') || message.includes('turismo') || message.includes('activity') || message.includes('tour')) {
            return t.atividades;
        }
        if (message.includes('avaliação') || message.includes('avaliar') || message.includes('feedback') || message.includes('review')) {
            return t.avaliacao;
        }
        if (message.includes('animal') || message.includes('animais') || message.includes('pet') || message.includes('cachorro') || message.includes('gato')) {
            return t.animais;
        }
        if (message.includes('estacionamento') || message.includes('carro') || message.includes('garagem') || message.includes('parking') || message.includes('car')) {
            return t.estacionamento;
        }
        if (message.includes('café da manhã') || message.includes('pequeno almoço') || message.includes('breakfast')) {
            return t.cafe;
        }
        if (message.includes('refeição') || message.includes('refeições') || message.includes('comida') || message.includes('alimentação') || message.includes('meal') || message.includes('food')) {
            return t.refeicoes;
        }
        return t.fallback;
    }

    // Carregar respostas personalizadas se existirem
    if (typeof loadChatbotResponses === 'function') {
        loadChatbotResponses();
    }

    // 1. Função para salvar/restaurar histórico
    function saveConversation() {
        const messages = Array.from(chatbotMessages.children).map(div => ({
            type: div.classList.contains('user-message') ? 'user' : 'bot',
            html: div.innerHTML
        }));
        localStorage.setItem('chatbotConversation', JSON.stringify(messages));
    }
    function loadConversation() {
        const data = localStorage.getItem('chatbotConversation');
        if (data) {
            chatbotMessages.innerHTML = '';
            JSON.parse(data).forEach(msg => {
                const div = document.createElement('div');
                div.className = 'message ' + (msg.type === 'user' ? 'user-message' : 'bot-message');
                div.innerHTML = msg.html;
                chatbotMessages.appendChild(div);
            });
            scrollToBottom();
        }
    }
    // Carregar histórico ao abrir
    if (chatbotMessages && localStorage.getItem('chatbotConversation')) {
        loadConversation();
    }

    // 2. Desabilitar botão de envio se input estiver vazio
    function toggleSendButton() {
        chatbotSend.disabled = chatbotInput.value.trim() === '';
    }
    chatbotInput.addEventListener('input', toggleSendButton);
    toggleSendButton();
});