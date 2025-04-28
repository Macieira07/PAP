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
    const quickBookingForm = document.getElementById('quickBookingForm');

    // Initial state
    let isOpen = false;
    updateChatDate();
    setMinDates();

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

    // Quick booking form submission
    quickBookingForm.addEventListener('submit', (e) => {
        e.preventDefault();
        const checkInDate = document.getElementById('checkInDate').value;
        const checkOutDate = document.getElementById('checkOutDate').value;
        const guestCount = document.getElementById('guestCount').value;
        
        // Switch to messages tab
        document.querySelector('.chat-tab[data-tab="messages"]').click();
        
        // Add user message
        const bookingMessage = `Gostaria de reservar de ${formatDate(checkInDate)} até ${formatDate(checkOutDate)} para ${guestCount} hóspedes`;
        addUserMessage(bookingMessage);
        
        // Simulate response
        setTimeout(() => {
            addBotMessage(`Recebemos sua solicitação de reserva para ${guestCount} hóspedes de ${formatDate(checkInDate)} a ${formatDate(checkOutDate)}. Em breve entraremos em contato para confirmar a disponibilidade.`);
        }, 1000);
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
        const lowerMessage = message.toLowerCase();
        let response = '';
        let delay = 1000;

        // Sistema de respostas aprimorado
        if (lowerMessage.includes('reserva') || lowerMessage.includes('reservar') || 
            lowerMessage.includes('disponibilidade') || lowerMessage.includes('disponível')) {
            response = 'Para fazer uma reserva, você pode usar nossa aba de "Reserva" ou me informar as datas que deseja. Posso verificar a disponibilidade para você!';
            
            // Sugere usar o formulário de reserva
            setTimeout(() => {
                addBotMessage('Gostaria que eu verificasse a disponibilidade para você? Posso ajudar com isso!');
            }, delay + 1000);
        } 
        else if (lowerMessage.includes('preço') || lowerMessage.includes('valor') || 
                 lowerMessage.includes('custo') || lowerMessage.includes('quanto custa')) {
            response = 'Os preços variam conforme a temporada e o número de hóspedes. Em média, nossas diárias variam entre €80 e €150 por noite.';
            
            // Oferece mais detalhes
            setTimeout(() => {
                addBotMessage('Para valores exatos, posso verificar as datas específicas que você está interessado. Quais seriam?');
            }, delay + 1000);
        } 
        else if (lowerMessage.includes('localização') || lowerMessage.includes('endereço') || 
                 lowerMessage.includes('como chegar') || lowerMessage.includes('onde fica')) {
            response = 'Estamos localizados em Ponte de Lima, uma região encantadora do norte de Portugal. O endereço exato é Rua da Quinta Flores, 123.';
            
            // Oferece mapa
            setTimeout(() => {
                addBotMessage('Posso enviar um link com a localização exata no Google Maps, se desejar!');
            }, delay + 1000);
        } 
        else if (lowerMessage.includes('check-in') || lowerMessage.includes('check out') || 
                 lowerMessage.includes('horário') || lowerMessage.includes('hora')) {
            response = 'O check-in é a partir das 15h00 e o check-out até as 11h00. Horários diferentes podem ser acordados previamente, dependendo da disponibilidade.';
        } 
        else if (lowerMessage.includes('wifi') || lowerMessage.includes('internet') || 
                 lowerMessage.includes('conexão') || lowerMessage.includes('wi-fi')) {
            response = 'Sim, oferecemos Wi-Fi gratuito de alta velocidade em todas as áreas da Quinta. A senha é fornecida no check-in.';
        } 
        else if (lowerMessage.includes('animais') || lowerMessage.includes('pet') || 
                 lowerMessage.includes('cão') || lowerMessage.includes('cachorro') || 
                 lowerMessage.includes('gato')) {
            response = 'Sim, aceitamos animais de estimação de pequeno e médio porte mediante consulta prévia. Há uma taxa adicional de €15 por animal por estadia.';
        } 
        else if (lowerMessage.includes('estacionamento') || lowerMessage.includes('carro') || 
                 lowerMessage.includes('parqueamento') || lowerMessage.includes('parque')) {
            response = 'Oferecemos estacionamento gratuito e privativo dentro da propriedade para todos os hóspedes. Temos vagas cobertas e descobertas.';
        } 
        else if (lowerMessage.includes('café da manhã') || lowerMessage.includes('pequeno almoço') || 
                 lowerMessage.includes('refeição') || lowerMessage.includes('comida')) {
            response = 'Oferecemos café da manhã continental incluído na diária, com produtos locais frescos. Também temos opção de menu à la carte mediante reserva prévia.';
        } 
        else if (lowerMessage.includes('piscina') || lowerMessage.includes('natação') || 
                 lowerMessage.includes('banho')) {
            response = 'Temos uma piscina ao ar livre disponível de maio a setembro, das 9h às 20h. É aquecida e possui área de espreguiçadeiras. Crianças devem ser supervisionadas.';
        } 
        else if (lowerMessage.includes('atividades') || lowerMessage.includes('passeios') || 
                 lowerMessage.includes('o que fazer') || lowerMessage.includes('lazer')) {
            response = 'Na região você pode fazer trilhas, visitar vinícolas, conhecer o centro histórico de Ponte de Lima, ou relaxar em nossa propriedade. Posso sugerir atividades conforme seus interesses!';
        } 
        else if (lowerMessage.includes('pagamento') || lowerMessage.includes('cartão') || 
                 lowerMessage.includes('dinheiro') || lowerMessage.includes('transferência')) {
            response = 'Aceitamos pagamento em dinheiro, cartões (Visa, Mastercard) e transferência bancária. Para reservas, pedimos 30% de sinal.';
        } 
        else if (lowerMessage.includes('cancelamento') || lowerMessage.includes('reembolso') || 
                 lowerMessage.includes('política')) {
            response = 'Cancelamentos até 7 dias antes da reserva têm reembolso total. Entre 7 e 2 dias, 50% do sinal é retido. Menos de 48h, não há reembolso.';
        } 
        else if (lowerMessage.includes('acessibilidade') || lowerMessage.includes('cadeirante') || 
                 lowerMessage.includes('deficiente') || lowerMessage.includes('mobilidade')) {
            response = 'Temos um quarto adaptado para cadeirantes no piso térreo, com banheiro acessível. Por favor, informe suas necessidades específicas para podermos ajudar melhor.';
        } 
        else if (lowerMessage.includes('família') || lowerMessage.includes('crianças') || 
                 lowerMessage.includes('bebê') || lowerMessage.includes('filhos')) {
            response = 'Somos family-friendly! Temos berço disponível (sem custo), cadeira alta e espaço para crianças brincarem. Também oferecemos jogos e brinquedos.';
        } 
        else if (lowerMessage.includes('evento') || lowerMessage.includes('casamento') || 
                 lowerMessage.includes('festa') || lowerMessage.includes('reunião')) {
            response = 'Temos espaço para eventos até 50 pessoas. Oferecemos pacotes completos para casamentos, aniversários e reuniões corporativas. Posso enviar mais informações!';
        } 
        else if (lowerMessage.includes('contato') || lowerMessage.includes('telefone') || 
                 lowerMessage.includes('email') || lowerMessage.includes('whatsapp')) {
            response = 'Você pode nos contatar por telefone (+351 912 418 976), email (quinta.flores19@gmail.com) ou WhatsApp. Estamos disponíveis das 8h às 22h.';
        } 
        else if (lowerMessage.includes('obrigado') || lowerMessage.includes('agradeço') || 
                 lowerMessage.includes('grato') || lowerMessage.includes('valeu')) {
            response = 'De nada! Estou aqui para ajudar. Se tiver mais alguma dúvida, é só perguntar!';
        } 
        else if (lowerMessage.includes('oi') || lowerMessage.includes('olá') || 
                 lowerMessage.includes('bom dia') || lowerMessage.includes('boa tarde') || 
                 lowerMessage.includes('boa noite')) {
            response = 'Olá! Bem-vindo à Quinta Flores. Como posso ajudar você hoje?';
        } 
        else {
            response = 'Obrigado pelo seu contato! Posso ajudar com informações sobre reservas, preços, localização ou outras dúvidas sobre a Quinta Flores. Sobre o que você gostaria de saber?';
            
            // Sugere opções
            setTimeout(() => {
                addBotMessage('Você pode perguntar sobre:');
                setTimeout(() => {
                    addBotMessage('• Disponibilidade para reservas');
                    setTimeout(() => {
                        addBotMessage('• Preços e formas de pagamento');
                        setTimeout(() => {
                            addBotMessage('• Localização e como chegar');
                            setTimeout(() => {
                                addBotMessage('• Serviços e comodidades oferecidos');
                            }, 500);
                        }, 500);
                    }, 500);
                }, 500);
            }, 1000);
        }

        setTimeout(() => addBotMessage(response), delay);
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

    function setMinDates() {
        const today = new Date();
        const tomorrow = new Date(today);
        tomorrow.setDate(tomorrow.getDate() + 1);
        
        const checkInDate = document.getElementById('checkInDate');
        const checkOutDate = document.getElementById('checkOutDate');
        
        // Format as YYYY-MM-DD
        const todayStr = today.toISOString().split('T')[0];
        const tomorrowStr = tomorrow.toISOString().split('T')[0];
        
        checkInDate.min = todayStr;
        checkOutDate.min = tomorrowStr;
        
        // Set initial values
        checkInDate.value = todayStr;
        checkOutDate.value = tomorrowStr;
    }

    function formatDate(dateStr) {
        const date = new Date(dateStr);
        return date.toLocaleDateString('pt-PT', { day: 'numeric', month: 'long' });
    }
});