document.addEventListener('DOMContentLoaded', () => {
    const chatWidget = document.querySelector('.chat-widget');
    const chatButton = document.getElementById('chatButton');
    const chatWindow = document.getElementById('chatWindow');
    const chatInput = document.getElementById('chatInput');
    const sendButton = document.getElementById('sendMessage');
    const chatMessages = document.getElementById('chatMessages');
    const quickReplies = document.querySelectorAll('.quick-reply-btn');

    // Sistema de respostas inteligentes
    const responses = {
        welcome: [
            "Olá! Bem-vindo à Quinta Flores. Como posso ajudar você hoje?",
            "Olá! É um prazer atendê-lo. Em que posso ser útil?",
            "Bem-vindo! Estou aqui para ajudar com informações sobre a Quinta Flores."
        ],
        reservas: {
            triggers: ['reserva', 'reservar', 'agendar', 'marcar', 'disponibilidade', 'vaga', 'quartos'],
            responses: [
                "Para fazer uma reserva, posso te ajudar de duas formas:\n1. Verificar disponibilidade imediatamente\n2. Informar sobre nossos tipos de acomodação\nO que você prefere?",
                "Ótimo! Temos várias opções de hospedagem disponíveis. Gostaria de saber as datas que você está planejando?"
            ]
        },
        precos: {
            triggers: ['preço', 'valor', 'custo', 'tarifa', 'diária', 'quanto', 'custa'],
            responses: [
                "Os preços variam conforme a temporada e o tipo de acomodação. Para dar um valor preciso, preciso saber:\n- Data prevista\n- Número de hóspedes\n- Tipo de acomodação\nPode me informar?",
                "Temos diferentes opções de valores para melhor atender seu orçamento. Quando você planeja se hospedar?"
            ]
        },
        comodidades: {
            triggers: ['wifi', 'internet', 'piscina', 'estacionamento', 'café', 'restaurante', 'serviços'],
            responses: [
                "Na Quinta Flores você encontra:\n✓ WiFi gratuito\n✓ Piscina\n✓ Estacionamento gratuito\n✓ Café da manhã\n✓ Área de churrasco\nGostaria de saber mais sobre algum destes serviços?",
                "Oferecemos diversas comodidades para seu conforto! Quer saber mais detalhes sobre algo específico?"
            ]
        },
        localizacao: {
            triggers: ['local', 'onde', 'endereço', 'localização', 'chegar', 'encontro'],
            responses: [
                "Estamos localizados em Ponte de Lima, um lugar tranquilo e aconchegante. Posso te enviar:\n1. Nosso endereço completo\n2. Coordenadas GPS\n3. Instruções de como chegar\nO que prefere?",
                "A Quinta Flores está situada em um local privilegiado em Ponte de Lima. Quer que eu envie as direções?"
            ]
        }
    };

    // Inicialização do chat
    function initChat() {
        chatButton.addEventListener('click', toggleChat);
        sendButton.addEventListener('click', sendMessage);
        chatInput.addEventListener('keypress', handleEnterPress);
        setupQuickReplies();
    }

    // Alternar visibilidade do chat
    function toggleChat() {
        const isVisible = chatWindow.style.display === 'flex';
        chatWindow.style.display = isVisible ? 'none' : 'flex';
        
        if (!isVisible) {
            chatWindow.classList.add('show');
            chatInput.focus();
            document.getElementById('chatNotification').style.display = 'none';
        }
    }

    // Enviar mensagem
    function sendMessage() {
        const message = chatInput.value.trim();
        if (message) {
            addMessage(message, true);
            chatInput.value = '';
            handleResponse(message);
        }
    }

    // Adicionar mensagem ao chat
    function addMessage(message, isUser = false) {
        const messageDiv = document.createElement('div');
        messageDiv.className = `message ${isUser ? 'sent' : 'received'}`;
        
        const time = new Date().toLocaleTimeString([], { hour: '2-digit', minute: '2-digit' });
        
        messageDiv.innerHTML = `
            <div class="message-bubble">
                <p>${message}</p>
                <span class="message-time">${time}</span>
            </div>
        `;
        
        chatMessages.appendChild(messageDiv);
        chatMessages.scrollTop = chatMessages.scrollHeight;
    }

    // Mostrar indicador de digitação
    function showTyping() {
        const typingDiv = document.createElement('div');
        typingDiv.className = 'typing-indicator';
        typingDiv.innerHTML = `
            <div class="typing-dot"></div>
            <div class="typing-dot"></div>
            <div class="typing-dot"></div>
        `;
        chatMessages.appendChild(typingDiv);
        chatMessages.scrollTop = chatMessages.scrollHeight;
    }

    // Remover indicador de digitação
    function hideTyping() {
        const typingIndicator = chatMessages.querySelector('.typing-indicator');
        if (typingIndicator) {
            typingIndicator.remove();
        }
    }

    // Processar resposta com base na mensagem do usuário
    function handleResponse(userMessage) {
        showTyping();
        
        // Simular tempo de digitação natural
        setTimeout(() => {
            hideTyping();
            
            // Encontrar categoria apropriada
            let responseText = '';
            const messageLower = userMessage.toLowerCase();
            
            // Verificar cada categoria de resposta
            for (const [category, data] of Object.entries(responses)) {
                if (category === 'welcome') continue;
                
                if (data.triggers.some(trigger => messageLower.includes(trigger))) {
                    responseText = data.responses[Math.floor(Math.random() * data.responses.length)];
                    break;
                }
            }
            
            // Se nenhuma categoria específica for encontrada, dar uma resposta genérica
            if (!responseText) {
                responseText = "Desculpe, não entendi completamente. Você poderia reformular sua pergunta? Posso ajudar com:\n- Reservas\n- Preços\n- Comodidades\n- Localização";
            }
            
            addMessage(responseText, false);
        }, 1000 + Math.random() * 1000); // Tempo de resposta variável para parecer mais natural
    }

    // Configurar respostas rápidas
    function setupQuickReplies() {
        quickReplies.forEach(button => {
            button.addEventListener('click', () => {
                const message = button.textContent;
                addMessage(message, true);
                handleResponse(message);
            });
        });
    }

    // Lidar com tecla Enter
    function handleEnterPress(e) {
        if (e.key === 'Enter' && !e.shiftKey) {
            e.preventDefault();
            sendMessage();
        }
    }

    // Auto-expandir textarea
    chatInput.addEventListener('input', function() {
        this.style.height = 'auto';
        this.style.height = (this.scrollHeight) + 'px';
    });

    // Inicializar chat
    initChat();

    // Mensagem de boas-vindas após um pequeno delay
    setTimeout(() => {
        const welcomeMessage = responses.welcome[Math.floor(Math.random() * responses.welcome.length)];
        addMessage(welcomeMessage, false);
    }, 1000);
});