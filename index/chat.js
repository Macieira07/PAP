document.addEventListener('DOMContentLoaded', function() {
    // Elements
    const chatWidget = document.getElementById('chatWidget');
    const chatButton = document.getElementById('chatButton');
    const chatWindow = document.getElementById('chatWindow');
    const minimizeChat = document.getElementById('minimizeChat');
    const closeChat = document.getElementById('closeChat');
    const chatInput = document.getElementById('chatInput');
    const sendMessage = document.getElementById('sendMessage');
    const chatMessages = document.getElementById('chatMessages');
    const chatTabs = document.querySelectorAll('.chat-tab');
    const chatPanels = document.querySelectorAll('.chat-panel');
    const quickReplies = document.querySelectorAll('.quick-reply-btn');
    const faqItems = document.querySelectorAll('.faq-item');

    // Initial state
    let isOpen = false;
    updateChatDate();

    // Open/Close chat window
    chatButton.addEventListener('click', () => {
        isOpen = !isOpen;
        chatWindow.style.display = isOpen ? 'flex' : 'none';
        chatButton.classList.toggle('active');
        if (isOpen) {
            document.getElementById('chatNotification').style.display = 'none';
        }
    });

    // Minimize chat
    minimizeChat.addEventListener('click', () => {
        isOpen = false;
        chatWindow.style.display = 'none';
        chatButton.classList.remove('active');
    });

    // Close chat
    closeChat.addEventListener('click', () => {
        isOpen = false;
        chatWindow.style.display = 'none';
        chatButton.classList.remove('active');
    });

    // Tab switching
    chatTabs.forEach(tab => {
        tab.addEventListener('click', () => {
            // Remove active class from all tabs and panels
            chatTabs.forEach(t => t.classList.remove('active'));
            chatPanels.forEach(p => p.classList.remove('active'));

            // Add active class to clicked tab and corresponding panel
            tab.classList.add('active');
            const panelId = tab.getAttribute('data-tab') + 'Panel';
            document.getElementById(panelId).classList.add('active');
        });
    });

    // Quick replies
    quickReplies.forEach(button => {
        button.addEventListener('click', () => {
            const message = button.textContent;
            addUserMessage(message);
            handleUserMessage(message);
        });
    });

    // FAQ accordion
    faqItems.forEach(item => {
        const question = item.querySelector('.faq-question');
        question.addEventListener('click', () => {
            item.classList.toggle('active');
        });
    });

    // Send message
    sendMessage.addEventListener('click', sendUserMessage);
    chatInput.addEventListener('keypress', (e) => {
        if (e.key === 'Enter' && !e.shiftKey) {
            e.preventDefault();
            sendUserMessage();
        }
    });

    // Auto-resize textarea
    chatInput.addEventListener('input', () => {
        chatInput.style.height = 'auto';
        chatInput.style.height = chatInput.scrollHeight + 'px';
    });

    // Helper functions
    function sendUserMessage() {
        const message = chatInput.value.trim();
        if (message) {
            addUserMessage(message);
            handleUserMessage(message);
            chatInput.value = '';
            chatInput.style.height = 'auto';
        }
    }

    function addUserMessage(message) {
        const messageElement = createMessageElement(message, 'sent');
        chatMessages.appendChild(messageElement);
        chatMessages.scrollTop = chatMessages.scrollHeight;
    }

    function addBotMessage(message) {
        const messageElement = createMessageElement(message, 'received');
        chatMessages.appendChild(messageElement);
        chatMessages.scrollTop = chatMessages.scrollHeight;
    }

    function createMessageElement(message, type) {
        const div = document.createElement('div');
        div.className = `message ${type}`;
        div.innerHTML = `
            <div class="message-bubble">
                <p>${message}</p>
                <span class="message-time">${getCurrentTime()}</span>
            </div>
        `;
        return div;
    }

    function handleUserMessage(message) {
        // Simples sistema de respostas baseado em palavras-chave
        const lowerMessage = message.toLowerCase();
        let response = '';

        if (lowerMessage.includes('reserva') || lowerMessage.includes('reservar')) {
            response = 'Para fazer uma reserva, por favor acesse a aba "Reserva" ou entre em contato pelo telefone +351 912 418 976.';
        } else if (lowerMessage.includes('preço') || lowerMessage.includes('valor') || lowerMessage.includes('custo')) {
            response = 'Os preços variam conforme a temporada e o número de hóspedes. Por favor, use nossa aba de reserva para verificar os valores específicos para suas datas.';
        } else if (lowerMessage.includes('localização') || lowerMessage.includes('endereço')) {
            response = 'Estamos localizados em Ponte de Lima, uma região encantadora do norte de Portugal. Para mais detalhes sobre como chegar, entre em contato conosco.';
        } else if (lowerMessage.includes('check-in') || lowerMessage.includes('check-out')) {
            response = 'O check-in é a partir das 15h00 e o check-out até as 11h00. Horários diferentes podem ser acordados previamente.';
        } else if (lowerMessage.includes('wifi') || lowerMessage.includes('internet')) {
            response = 'Sim, oferecemos Wi-Fi gratuito em todas as áreas da Quinta.';
        } else {
            response = 'Obrigado pelo seu contato! Como posso ajudar você hoje?';
        }

        setTimeout(() => addBotMessage(response), 1000);
    }

    function getCurrentTime() {
        const now = new Date();
        return now.toLocaleTimeString('pt-PT', { hour: '2-digit', minute: '2-digit' });
    }

    function updateChatDate() {
        const dateElement = document.getElementById('chatDate');
        const options = { weekday: 'long', day: 'numeric', month: 'long' };
        dateElement.textContent = new Date().toLocaleDateString('pt-PT', options);
    }
});