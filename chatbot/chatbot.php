<div id="chatbot-container" class="chatbot-hidden">
    <div class="chatbot-header">
        <h3><?= I18n::get('chatbot_title', 'Assistente Virtual Quinta Flores') ?></h3>
        <div class="chatbot-actions">
            <button id="chatbot-clear" class="chatbot-btn-clear" title="Limpar conversa">
                <i class="ri-refresh-line"></i>
            </button>
            <button id="chatbot-close" class="chatbot-btn-close" title="Fechar chatbot">
                <i class="ri-close-line"></i>
            </button>
        </div>
    </div>
    <div id="chatbot-messages" class="chatbot-messages"></div>
    <div class="chatbot-input-container">
        <input type="text" id="chatbot-input" placeholder="<?= I18n::get('chatbot_placeholder', 'Digite sua mensagem...') ?>" autocomplete="off">
        <button id="chatbot-send" class="chatbot-btn-send">
            <i class="ri-send-plane-fill"></i>
        </button>
    </div>
    <div class="chatbot-quick-actions">
        <button class="quick-btn" data-query="reservas"><?= I18n::get('chatbot_quick_reservas', 'Reservas') ?></button>
        <button class="quick-btn" data-query="preços"><?= I18n::get('chatbot_quick_precos', 'Preços') ?></button>
        <button class="quick-btn" data-query="localização"><?= I18n::get('chatbot_quick_localizacao', 'Localização') ?></button>
        <button class="quick-btn" data-query="contato"><?= I18n::get('chatbot_quick_contato', 'Contato') ?></button>
        <button class="quick-btn" data-query="Deixar Avaliação"><?= I18n::get('chatbot_quick_avaliacao', 'Deixar Avaliação') ?></button>
    </div>
</div>
<button id="chatbot-toggle" class="chatbot-toggle">
    <i class="ri-customer-service-2-fill"></i>
</button>
