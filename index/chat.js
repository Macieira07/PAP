document.addEventListener('DOMContentLoaded', () => {
    initChat();
});

function initChat() {
    const chatButton = document.getElementById('chatButton');
    const chatWindow = document.getElementById('chatWindow');
    const minimizeChat = document.getElementById('minimizeChat');
    const closeChat = document.getElementById('closeChat');
    const chatInput = document.getElementById('chatInput');
    const sendMessage = document.getElementById('sendMessage');
    const chatMessages = document.getElementById('chatMessages');
    const chatTabs = document.querySelectorAll('.chat-tab');
    const quickReplies = document.querySelectorAll('.quick-reply-btn');
    const faqItems = document.querySelectorAll('.faq-item');

    // Sistema de respostas automáticas
    const autoResponses = {
        'default': 'Olá! Como posso ajudar você hoje?',
        'reserva': 'Para fazer uma reserva, você pode usar nossa aba de "Reserva" ou acessar diretamente nosso sistema de reservas. Posso ajudar você com isso?',
        'horario': 'Nosso horário de check-in é às 15h00 e o check-out às 11h00. Podemos ser flexíveis mediante disponibilidade.',
        'preço': 'Os preços variam conforme a temporada e o tipo de acomodação. Posso verificar as tarifas específicas para suas datas?',
        'localização': 'Estamos localizados em Ponte de Lima. Você gostaria de receber as coordenadas GPS ou instruções de como chegar?',
        'wifi': 'Sim, oferecemos WiFi gratuito em todas as áreas da propriedade.',
        'estacionamento': 'Sim, temos estacionamento gratuito para nossos hóspedes.',
        'animais': 'Aceitamos animais de estimação mediante consulta prévia.',
        'piscina': 'Nossa piscina está disponível de maio a setembro, dependendo das condições climáticas.'
    };

    // Mostrar/esconder chat
    chatButton.addEventListener('click', () => {
        chatWindow.classList.toggle('show');
        document.getElementById('chatNotification').style.display = 'none';
    });

    minimizeChat.addEventListener('click', () => {
        chatWindow.classList.remove('show');
    });

    closeChat.addEventListener('click', () => {
        chatWindow.classList.remove('show');
    });

    // Enviar mensagem
    function sendChatMessage(message, isUser = true) {
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

        // Se for mensagem do usuário, gerar resposta automática
        if (isUser) {
            setTimeout(() => {
                const response = getAutoResponse(message.toLowerCase());
                sendChatMessage(response, false);
            }, 1000);
        }
    }

    // Sistema de respostas automáticas
    function getAutoResponse(message) {
        const keywords = {
            'reserva': ['reserva', 'reservar', 'agendar', 'marcar', 'disponibilidade'],
            'horario': ['horário', 'check-in', 'check-out', 'chegada', 'saída'],
            'preço': ['preço', 'valor', 'custo', 'tarifa', 'diária'],
            'localização': ['localização', 'endereço', 'onde', 'chegar'],
            'wifi': ['wifi', 'internet', 'conexão'],
            'estacionamento': ['estacionamento', 'parque', 'estacionar', 'carro'],
            'animais': ['animal', 'pet', 'cachorro', 'gato'],
            'piscina': ['piscina', 'nadar']
        };

        for (const [key, terms] of Object.entries(keywords)) {
            if (terms.some(term => message.includes(term))) {
                return autoResponses[key];
            }
        }

        return autoResponses.default;
    }

    // Enviar mensagem ao clicar no botão ou pressionar Enter
    sendMessage.addEventListener('click', () => {
        const message = chatInput.value.trim();
        if (message) {
            sendChatMessage(message);
            chatInput.value = '';
        }
    });

    chatInput.addEventListener('keypress', (e) => {
        if (e.key === 'Enter' && !e.shiftKey) {
            e.preventDefault();
            sendMessage.click();
        }
    });

    // Respostas rápidas
    quickReplies.forEach(button => {
        button.addEventListener('click', () => {
            const message = button.textContent;
            sendChatMessage(message);
        });
    });

    // Alternar entre abas
    chatTabs.forEach(tab => {
        tab.addEventListener('click', () => {
            const targetId = tab.getAttribute('data-tab');
            
            // Atualizar classes ativas
            document.querySelector('.chat-tab.active').classList.remove('active');
            tab.classList.add('active');
            
            // Mostrar painel correspondente
            document.querySelector('.chat-panel.active').classList.remove('active');
            document.getElementById(`${targetId}Panel`).classList.add('active');
        });
    });

    // Toggle FAQ
    faqItems.forEach(item => {
        item.querySelector('.faq-question').addEventListener('click', () => {
            item.classList.toggle('active');
        });
    });

    // Inicializar formulário de reserva rápida
    const quickBookingForm = document.getElementById('quickBookingForm');
    if (quickBookingForm) {
        const today = new Date().toISOString().split('T')[0];
        document.getElementById('checkInDate').min = today;
        document.getElementById('checkOutDate').min = today;

        quickBookingForm.addEventListener('submit', (e) => {
            e.preventDefault();
            const checkIn = document.getElementById('checkInDate').value;
            const checkOut = document.getElementById('checkOutDate').value;
            const guests = document.getElementById('guestCount').value;

            sendChatMessage(`Solicitação de reserva: Check-in: ${checkIn}, Check-out: ${checkOut}, Hóspedes: ${guests}`);
            sendChatMessage('Obrigado pelo seu interesse! Vou verificar a disponibilidade e retornar em breve.', false);
        });
    }

    // Mensagem inicial após um pequeno delay
    setTimeout(() => {
        sendChatMessage('Olá! Bem-vindo à Quinta Flores. Como posso ajudar você hoje?', false);
    }, 1000);
}