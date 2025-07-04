<!--
============================================================
  Componente Chatbot (HTML) - Quinta Flores
============================================================

  Linguagens Utilizadas:
    - HTML5 (estrutura)
    - PHP (i18n, includes)
    - CSS3 (estilos externos)
    - JavaScript (interatividade, chatbot.js)

  Bibliotecas e Frameworks:
    - Font Awesome & Remixicon (ícones)
    - Google Fonts (fontes personalizadas)
    - i18n (internacionalização, multi-idioma)

  Estrutura do Componente:
    1. Container principal do chatbot
    2. Cabeçalho com título e botões
    3. Área de mensagens
    4. Input e botões rápidos
    5. Botão flutuante de toggle

  Autor: [Seu Nome ou Equipa]
  Última atualização: [Data]
============================================================
-->
// ===================== 1. Container Principal =====================

<div id="chatbot-container" class="chatbot-hidden">
    <div class="chatbot-header">
        <h3><i class="fa-solid fa-leaf" style="margin-right:8px;"></i><?= I18n::get('chatbot_title', 'Assistente Virtual') ?></h3>
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
        <button class="quick-btn" data-query="reservas"><i class="fa-solid fa-calendar-check" style="margin-right:4px;"></i> <?= I18n::get('chatbot_quick_reservas', 'Reservas') ?></button>
        <button class="quick-btn" data-query="preços"><i class="fa-solid fa-euro-sign" style="margin-right:4px;"></i> <?= I18n::get('chatbot_quick_precos', 'Preços') ?></button>
        <button class="quick-btn" data-query="localização"><i class="fa-solid fa-map-location-dot" style="margin-right:4px;"></i> <?= I18n::get('chatbot_quick_localizacao', 'Localização') ?></button>
        <button class="quick-btn" data-query="contato"><i class="fa-solid fa-phone" style="margin-right:4px;"></i> <?= I18n::get('chatbot_quick_contato', 'Contato') ?></button>
        <button class="quick-btn" data-query="Deixar Avaliação"><i class="fa-solid fa-star" style="margin-right:4px;"></i> <?= I18n::get('chatbot_quick_avaliacao', 'Deixar Avaliação') ?></button>
        <button class="quick-btn" data-query="animais"><i class="fa-solid fa-dog" style="margin-right:4px;"></i> Animais</button>
        <button class="quick-btn" data-query="estacionamento"><i class="fa-solid fa-square-parking" style="margin-right:4px;"></i> Estacionamento</button>
        <button class="quick-btn" data-query="refeições"><i class="fa-solid fa-utensils" style="margin-right:4px;"></i> Refeições</button>
    </div>
</div>
<button id="chatbot-toggle" class="chatbot-toggle">
    <i class="ri-customer-service-2-fill"></i>
</button>
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css">
