document.addEventListener('DOMContentLoaded', function() {
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
    chatbotInput.addEventListener('keypress', function(e) {
        if (e.key === 'Enter') {
            sendMessage();
        }
    });

    // Botões rápidos
    quickButtons.forEach(button => {
        button.addEventListener('click', function() {
            const query = this.getAttribute('data-query');
            sendQuickQuery(query);
        });
    });
    //Quando o bot “está a pensar”, mostra um balão com “Digitando...” antes da resposta aparecer.
    function showTypingIndicator() {
    const typingDiv = document.createElement('div');
    typingDiv.classList.add('message', 'bot-message', 'typing');
    typingDiv.textContent = 'Digitando...';
    chatbotMessages.appendChild(typingDiv);
    chatbotMessages.scrollTop = chatbotMessages.scrollHeight;
    return typingDiv;
    }


    function sendMessage() {
        const message = chatbotInput.value.trim();
        if (message) {
            addUserMessage(message);
            chatbotInput.value = '';
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
    messageDiv.textContent = text;
    chatbotMessages.appendChild(messageDiv);
    scrollToBottom();
}
function addBotMessage(text) {
    const messageDiv = document.createElement('div');
    messageDiv.classList.add('message', 'bot-message');
    messageDiv.innerHTML = text;
    chatbotMessages.appendChild(messageDiv);
    scrollToBottom();
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
    }, 1200); // duração do "typing" em ms
}

    function getWelcomeMessage() {
        return `Olá! Sou o assistente virtual da <strong>Quinta Flores</strong> em Ponte de Lima. 😊<br><br>
        Como posso ajudar você hoje? Pode me perguntar sobre:<br>
        • Reservas e disponibilidade<br>
        • Preços e ofertas especiais<br>
        • Localização e como chegar<br>
        • Serviços e comodidades<br>
        • Atividades na região`;
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
    //Desativar input e botão “Enviar” enquanto o bot responde
    function disableInput() {
  chatbotInput.disabled = true;
  chatbotSend.disabled = true;
}

function enableInput() {
  chatbotInput.disabled = false;
  chatbotSend.disabled = false;
}


    function getBotResponse(message) {
        // Verificar se há uma resposta específica no arquivo de configuração
        if (window.chatbotResponses && window.chatbotResponses[message]) {
            return window.chatbotResponses[message];
        }

        // Respostas padrão baseadas em palavras-chave
        if (message.includes('olá') || message.includes('oi') || message.includes('bom dia')) {
            return `Olá novamente! No que posso ajudar hoje? 😊`;
        }

        if (message.includes('reserva') || message.includes('disponibilidade') || message.includes('book')) {
            return `Para fazer uma reserva ou verificar disponibilidade, você pode:<br>
            1. Usar nosso sistema online <a href="../login1/pagina_login.php" style="color: var(--chatbot-primary); font-weight: 600;">clicando aqui</a><br>
            2. Entrar em contato pelo telefone: <strong>+351 912 418 976</strong><br>
            3. Enviar um email para: <strong>quinta.flores2019@gmail.com</strong><br><br>
            Posso ajudar com mais alguma informação sobre reservas?`;
        }

        if (message.includes('preço') || message.includes('valor') || message.includes('custo') || message.includes('price')) {
            return `Nossos preços variam conforme a temporada e tipo de acomodação:<br><br>
            • <strong>Tempo a Dois</strong>: 260€ (2 noites)<br>
            • <strong>Diversão em Grupo</strong>: 250€ (1-2 noites)<br>
            • <strong>Retiro Espiritual</strong>: 240€ (3 noites)<br><br>
            • <strong>Uma reserva normal fica por </strong>: 120€ por noite<br><br>
            Todos os preços são por grupo. Temos estes pacotes promocionais. Gostaria de verificar disponibilidade para alguma data?`;
        }

        if (message.includes('localização') || message.includes('chegar') || message.includes('mapa') || message.includes('address')) {
            return `A Quinta Flores está localizada em:<br><br>
            <strong>Travessa da seara 265 Calheiros<br>
            4990-575 Ponte de Lima, Viana do Castelo, Portugal</strong><br><br>
            Como chegar:<br>
            • <strong>Do Aeroporto do Porto</strong>: 45 minutos de carro pela A3<br>
            • <strong>De Viana do Castelo</strong>: 30 minutos<br>
            • <strong>De Braga</strong>: 20 minutos<br><br>
            <a href="https://maps.app.goo.gl/example" target="_blank" style="color: var(--chatbot-primary); font-weight: 600;">Ver no Google Maps</a>`;
        }

        if (message.includes('contato') || message.includes('telefone') || message.includes('email') || message.includes('contact')) {
            return `Você pode entrar em contato conosco por:<br><br>
            • <strong>Telefone</strong>: +351 912 418 976<br>
            • <strong>Email</strong>: quinta.flores2019@gmail.com<br>
            • <strong>WhatsApp</strong>: +351 912 418 976<br><br>
            Horário de atendimento: 8h às 22h (todos os dias)`;
        }

        if (message.includes('serviço') || message.includes('comodidade') || message.includes('amenity')) {
            return `Nossas comodidades incluem:<br><br>
            • Piscina exterior<br>
            • Wi-Fi gratuito<br>
            • Estacionamento privado<br>
            • Cozinha totalmente equipada<br>
            • Área de churrasco<br>
            • Máquina de lavar roupa<br>
            • TV e área de lazer<br><br>
            Precisa de mais informações sobre algum serviço específico?`;
        }

        if (message.includes('atividade') || message.includes('passeio') || message.includes('turismo') || message.includes('activity')) {
            return `Na região de Ponte de Lima, recomendamos:<br><br>
            • <strong>Trilhas na natureza</strong>: Ecovia do Lima<br>
            • <strong>Gastronomia</strong>: Restaurantes com comida típica minhota<br>
            • <strong>Cultura</strong>: Centro histórico de Ponte de Lima<br>
            • <strong>Praia fluvial</strong>: A 10 minutos da Quinta<br>
            • <strong>Festival Internacional de Jardins</strong>: Evento anual<br><br>
            Posso sugerir atividades específicas para seu grupo?`;
        }

        // Se não reconhecer a pergunta
        return `Desculpe, não entendi completamente sua pergunta. 😕<br><br>
        Você pode reformular ou escolher uma destas opções:<br>
        • Informações sobre reservas<br>
        • Preços e pacotes<br>
        • Como chegar<br>
        • Serviços oferecidos<br>
        • Atividades na região`;
    }

    // Carregar respostas personalizadas se existirem
    if (typeof loadChatbotResponses === 'function') {
        loadChatbotResponses();
    }
});