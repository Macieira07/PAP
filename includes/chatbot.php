<link rel="stylesheet" href="chatbot.css">
    <div class="chatbot-container">
        <div class="chatbot-button" id="chatbotButton">
            <i class="fa-solid fa-comment-dots"></i>
        </div>
        <div class="chatbot-box" id="chatbotBox">
            <div class="chatbot-header">
                <div class="chatbot-title">
                    <img src="../assets/logos/logotipo1.png" alt="Quinta Flores" class="chatbot-logo">
                    <span>Assistente Virtual da Quinta Flores</span>
                </div>
                <button class="chatbot-close" id="chatbotClose">
                    <i class="fa-solid fa-xmark"></i>
                </button>
            </div>
            <div class="chatbot-messages" id="chatbotMessages">
                <div class="message bot-message">
                    <img src="../assets/logos/logotipo1.png" alt="Bot" class="message-avatar">
                    <div class="message-content">
                        <p>Olá! Bem-vindo à Quinta Flores. Como posso ajudá-lo hoje?</p>
                    </div>
                </div>
            </div>
            <div class="chatbot-input-container">
                <input type="text" id="chatbotInput" class="chatbot-input" placeholder="Digite sua mensagem...">
                <button id="chatbotSend" class="chatbot-send">
                    <i class="fa-solid fa-paper-plane"></i>
                </button>
            </div>
            <div class="chatbot-suggestions">
                <button class="suggestion-button">
                    <i class="fa-solid fa-calendar-check"></i> Reservas
                </button>
                <button class="suggestion-button">
                    <i class="fa-solid fa-bed"></i> Acomodações
                </button>
                <button class="suggestion-button">
                    <i class="fa-solid fa-bell-concierge"></i> Serviços
                </button>
                <button class="suggestion-button">
                    <i class="fa-solid fa-map-location-dot"></i> Localização
                </button>
                <button class="suggestion-button">
                    <i class="fa-solid fa-person-hiking"></i> Atividades
                </button>
                <button class="suggestion-button">
                    <i class="fa-solid fa-euro-sign"></i> Preços
                </button>
            </div>
            <div class="chatbot-footer">
                <span>Quinta Flores - ChatBot</span>
            </div>
        </div>
    </div>
    <script src="chatbot.js"></script>
