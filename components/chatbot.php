        <div class="chatbot-container">
            <div class="chatbot-button" id="chatbotButton">
                <i class="fa-solid fa-comment-dots"></i>
            </div>
            <div class="chatbot-box" id="chatbotBox">
                <div class="chatbot-header">
                    <div class="chatbot-title">
                        <img src="../assets/logos/logotipo1.png" alt="<?= SITE_NAME ?>" class="chatbot-logo">
                        <span><?= I18n::get('virtual_assistant') ?> <?= SITE_NAME ?></span>
                    </div>
                    <button class="chatbot-close" id="chatbotClose">
                        <i class="fa-solid fa-xmark"></i>
                    </button>
                </div>
                <div class="chatbot-messages" id="chatbotMessages">
                    <div class="message bot-message">
                        <img src="../assets/logos/logotipo1.png" alt="Bot" class="message-avatar">
                        <div class="message-content">
                            <p><?= I18n::get('welcome_chatbot_message') ?></p>
                        </div>
                    </div>
                </div>
                <div class="chatbot-input-container">
                    <input type="text" id="chatbotInput" class="chatbot-input" placeholder="<?= I18n::get('type_your_message') ?>">
                    <button id="chatbotSend" class="chatbot-send">
                        <i class="fa-solid fa-paper-plane"></i>
                    </button>
                </div>

                <div class="chatbot-suggestions">
                    <button class="suggestion-button">
                        <i class="fa-solid fa-calendar-check"></i> <?= I18n::get('reservations') ?>
                    </button>
                    <button class="suggestion-button">
                        <i class="fa-solid fa-bed"></i> <?= I18n::get('accommodation') ?>
                    </button>
                    <button class="suggestion-button">
                        <i class="fa-solid fa-bell-concierge"></i> <?= I18n::get('services') ?>
                    </button>
                    <button class="suggestion-button">
                        <i class="fa-solid fa-map-location-dot"></i> <?= I18n::get('location') ?>
                    </button>
                    <button class="suggestion-button">
                        <i class="fa-solid fa-person-hiking"></i> <?= I18n::get('activities') ?>
                    </button>
                    <button class="suggestion-button">
                        <i class="fa-solid fa-euro-sign"></i> <?= I18n::get('prices') ?>
                    </button>
                </div>
                <div class="chatbot-footer">
                    <span><?= SITE_NAME ?> - <?= I18n::get('chatbot') ?></span>
                </div>
            </div>
        </div>