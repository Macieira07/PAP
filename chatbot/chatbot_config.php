<?php

/*
 * ============================================================
 *   Configuração de Respostas do Chatbot - Quinta Flores
 * ============================================================
 *
 *   Linguagens Utilizadas:
 *     - PHP (backend, array de respostas)
 *     - JavaScript (uso via JSON)
 *
 *   Estrutura do Arquivo:
 *     1. Array de respostas personalizadas
 *     2. Exportação para JSON
 *     3. Script para carregar no JS
 *
 *   Autor: [Seu Nome ou Equipa]
 *   Última atualização: [Data]
 * ============================================================
 */
// ===================== 1. Array de Respostas Personalizadas =====================
$chatbotResponses = [
    'horário de check-in' => 'O horário de check-in é a partir das <strong>15:00</strong> e o check-out deve ser feito até as <strong>11:00</strong>.',
    'política de cancelamento' => 'Nossa política de cancelamento permite cancelamentos gratuitos até <strong>10 dias antes</strong> da data de check-in. Cancelamentos dentro deste período estão sujeitos a cobrança de 50% do valor da reserva.',
    'aceitam animais' => 'Aceitamos animais de estimação de <strong>pequeno porte</strong> mediante consulta prévia. É cobrada uma taxa adicional de limpeza no valor de €20.',
    'estacionamento' => 'Oferecemos <strong>estacionamento privativo gratuito</strong> para até 2 veículos. Espaços adicionais podem ser disponibilizados mediante consulta.',
    'café da manhã' => 'Oferecemos um café da manhã regional completo com produtos locais por <strong>€8 por pessoa</strong>. Pode ser solicitado no momento da reserva.',
    'acessibilidade' => 'Nossa propriedade possui acesso para cadeiras de rodas no piso térreo, incluindo um quarto adaptado. Por favor, informe-nos com antecedência para prepararmos sua estadia.',
    'eventos' => 'A Quinta Flores é o local perfeito para eventos como casamentos, aniversários e reuniões familiares. Temos capacidade para até <strong>50 pessoas</strong> em eventos externos. Solicite um orçamento personalizado.',
    'transporte' => 'Podemos ajudar a organizar transporte do aeroporto ou estações por um custo adicional. Também temos parcerias com empresas de aluguel de carros locais.',
    'pagamento' => 'Aceitamos pagamentos por <strong>transferência bancária, cartão de crédito ou MB Way</strong>. O pagamento integral é necessário para confirmar a reserva.',
    'kit primeiros socorros' => 'Sim, temos um <strong>kit de primeiros socorros</strong> disponível para os hóspedes, além de farmácias próximas em caso de necessidade.'
];
// Converter para JSON para uso no JavaScript
$chatbotResponsesJson = json_encode($chatbotResponses, JSON_UNESCAPED_UNICODE);
?>
<script>
// Carregar respostas personalizadas no JavaScript
function loadChatbotResponses() {
    window.chatbotResponses = <?php echo $chatbotResponsesJson; ?>;
}
</script>