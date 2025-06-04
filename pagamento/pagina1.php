<?php
session_start();
require_once '../conexao.php';
require_once 'i18n.php';
$page_title = I18n::get('make_reservation');

if (!isset($_SESSION['id'])) {
    header('Location: login.php');
    exit();
}
if (isset($_GET['lang']) && in_array($_GET['lang'], ['pt', 'en', 'fr','es'])) {
    I18n::setLanguage($_GET['lang']);
    // Recarrega a página para aplicar as mudanças
    header('Location: ' . strtok($_SERVER['REQUEST_URI'], '?'));
    exit();
}

$id_hospede = $_SESSION['id'];

// Consulta os períodos já reservados
$query = "SELECT R_data_checkin, R_data_checkout FROM reservas";
$resultado = $conexao->query($query);

$periodosOcupados = [];
while ($row = $resultado->fetch_assoc()) {
    $periodosOcupados[] = [
        'from' => $row['R_data_checkin'],
        'to' => $row['R_data_checkout']
    ];
}

function gerarDatasEntre($start, $end) {
    $dates = [];
    $current = strtotime($start);
    $end = strtotime($end);
    while ($current < $end) {
        $dates[] = date('Y-m-d', $current);
        $current = strtotime('+1 day', $current);
    }
    return $dates;
}

$todasDatasOcupadas = [];
foreach ($periodosOcupados as $periodo) {
    $todasDatasOcupadas = array_merge($todasDatasOcupadas, gerarDatasEntre($periodo['from'], $periodo['to']));
}
$todasDatasOcupadas = array_unique($todasDatasOcupadas);
sort($todasDatasOcupadas);

// PROCESSAMENTO DO FORMULÁRIO
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $checkin = $_POST['checkin'] ?? '';
    $checkout = $_POST['checkout'] ?? '';
    $num_hospedes = intval($_POST['num_hospedes'] ?? 2);

    $hoje = new DateTime();
    $amanha = (new DateTime())->modify('+1 day');
    $dataLimite = (new DateTime())->modify('+2 years');

    $dataCheckin = DateTime::createFromFormat('Y-m-d', $checkin);
    $dataCheckout = DateTime::createFromFormat('Y-m-d', $checkout);

    if (!$dataCheckin || !$dataCheckout) {
        $erro = I18n::get('invalid_dates');
    } elseif ($dataCheckin < $amanha || $dataCheckout <= $dataCheckin) {
        $erro = I18n::get('invalid_dates') . ' ' . I18n::get('checkin_after_tomorrow');
    } elseif ($dataCheckin > $dataLimite || $dataCheckout > $dataLimite) {
        $erro = I18n::get('reservation_limit') . ' ' . $dataLimite->format(I18n::get('date_format')) . '.';
    } else {
        $query = "SELECT * FROM reservas 
                  WHERE (R_data_checkin <= ? AND R_data_checkout > ?) 
                  OR (R_data_checkin < ? AND R_data_checkout >= ?)";
        $stmt = $conexao->prepare($query);
        $stmt->bind_param('ssss', $checkout, $checkin, $checkout, $checkin);
        $stmt->execute();
        $resultado = $stmt->get_result();

        if ($resultado->num_rows > 0) {
            $erro = I18n::get('dates_already_reserved');
        } else {
            $_SESSION['checkin'] = $checkin;
            $_SESSION['checkout'] = $checkout;
            $_SESSION['num_hospedes'] = $num_hospedes;
            header('Location: pagina2.php');
            exit();
        }
    }
}

$num_noites = 0;
if (!empty($_POST['checkin']) && !empty($_POST['checkout'])) {
    $checkin_date = new DateTime($_POST['checkin']);
    $checkout_date = new DateTime($_POST['checkout']);
    $num_noites = $checkin_date->diff($checkout_date)->days;
}

require_once 'header.php';
?>
<!DOCTYPE html>
<html lang="<?= I18n::getCurrentLanguage() ?>">
<head>
    <link rel="icon" type="image/png" sizes="32x32" href="../assets/logos/favicon-32x32.png">
    <link rel="icon" type="image/png" sizes="16x16" href="../assets/logos/favicon-16x16.png">
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= I18n::get('reservation') ?> - <?= SITE_NAME ?></title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css">
    <link rel="stylesheet" href="global.css">
    <link rel="stylesheet" href="../includes/chatbot.css">
    <link rel="icon" type="image/x-icon" href="../logotipos/logotipo2.png">
</head>
<body>
    <div class="container">
        <h1 class="fade-in"><?= I18n::get('make_reservation') ?></h1>
        
        <div class="progress-steps">
            <div class="progress-step active">
                <span><?= I18n::get('dates') ?></span>
            </div>
            <div class="progress-step">
                <span><?= I18n::get('personal_info') ?></span>
            </div>
            <div class="progress-step">
                <span><?= I18n::get('payment') ?></span>
            </div>
            <div class="progress-step">
                <span><?= I18n::get('confirmation') ?></span>
            </div>
        </div>

        <?php if (!empty($erro)): ?>
            <div class="error-message" style="display: block;">
                <i class="fas fa-exclamation-circle"></i> <?= $erro ?>
            </div>
        <?php endif; ?>
        
        <form action="pagina1.php" method="POST" id="reservaForm" class="fade-in">
            <div class="resumo-reserva">
                <h3><i class="fas fa-calendar-check"></i> <?= I18n::get('reservation_summary') ?></h3>
                <div class="resumo-item">
                    <span><?= I18n::get('check_in') ?>:</span>
                    <span id="display-checkin"><?= isset($_POST['checkin']) ? date(I18n::get('date_format'), strtotime($_POST['checkin'])) : '--/--/----' ?></span>
                </div>
                <div class="resumo-item">
                    <span><?= I18n::get('check_out') ?>:</span>
                    <span id="display-checkout"><?= isset($_POST['checkout']) ? date(I18n::get('date_format'), strtotime($_POST['checkout'])) : '--/--/----' ?></span>
                </div>
                <div class="resumo-item">
                    <span><?= I18n::get('nights') ?>:</span>
                    <span id="display-noites"><?= $num_noites > 0 ? $num_noites : '--' ?></span>
                </div>
                <div class="resumo-item">
                    <span><?= I18n::get('guests') ?>:</span>
                    <span id="display-hospedes"><?= $_POST['num_hospedes'] ?? '--' ?></span>
                </div>
            </div>
            <div class="oferta-container">
                <h3><i class="fas fa-gift"></i> <?= I18n::get('promo_code') ?></h3>
                <div class="form-group">
                    <label for="codigo_oferta"><i class="fas fa-tag"></i> <?= I18n::get('have_promo_code') ?></label>
                    <input type="text" id="codigo_oferta" name="codigo_oferta" class="form-control" 
                           placeholder="<?= I18n::get('enter_promo_code') ?>" 
                           value="<?= isset($_POST['codigo_oferta']) ? htmlspecialchars($_POST['codigo_oferta']) : '' ?>">
                </div>
                <div id="detalhes-oferta" style="display: none; margin-top: 15px; padding: 15px; background-color: #f8f9fa; border-radius: 5px;">
                    <h4 id="titulo-oferta"></h4>
                    <p id="descricao-oferta"></p>
                    <p id="condicoes-oferta" style="font-weight: bold;"></p>
                </div>
            </div>
            <div class="form-group">
                <label for="checkin"><i class="far fa-calendar-alt"></i> <?= I18n::get('check_in_date') ?></label>
                <input type="text" id="checkin" name="checkin" class="form-control" placeholder="<?= I18n::get('select_date') ?>" required>
            </div>
            <div class="form-group">
                <label for="checkout"><i class="far fa-calendar-alt"></i> <?= I18n::get('check_out_date') ?></label>
                <input type="text" id="checkout" name="checkout" class="form-control" placeholder="<?= I18n::get('select_date') ?>" required>
            </div>
            <div class="form-group">
                <label for="num_hospedes"><i class="fas fa-users"></i> <?= I18n::get('number_of_guests') ?></label>
                <select id="num_hospedes" name="num_hospedes" class="form-control" required>
                    <?php for($i=1; $i<=10; $i++): ?>
                        <option value="<?= $i ?>" <?= ($i == ($_POST['num_hospedes'] ?? 2)) ? 'selected' : '' ?>>
                            <?= $i ?> <?= $i === 1 ? I18n::get('person') : I18n::get('people') ?>
                        </option>
                    <?php endfor; ?>
                </select>
            </div>
            <div class="form-actions">
                <a href="../index.html" class="btn btn-secondary">
                    <i class="fas fa-arrow-left"></i> <?= I18n::get('back') ?>
                </a>
                <button type="submit" class="btn btn-primary pulse">
                    <?= I18n::get('continue') ?> <i class="fas fa-arrow-right"></i>
                </button>
            </div>
        </form>
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

        <script>
            // Ofertas disponíveis
            const ofertas = {
                'LOVE260': {
                    nome: "<?= I18n::get('love_package') ?>",
                    descricao: "<?= I18n::get('love_package_description') ?>",
                    condicoes: "<?= I18n::get('love_package_conditions') ?>",
                    noites: 2,
                    hospedes: 2,
                    preco: 260
                },
                'PARTY260': {
                    nome: "<?= I18n::get('party_package') ?>",
                    descricao: "<?= I18n::get('party_package_description') ?>",
                    condicoes: "<?= I18n::get('party_package_conditions') ?>",
                    noites: 2,
                    hospedes: 4,
                    preco: 260,
                    max_hospedes: 10
                },
                'RETIRO240': {
                    nome: "<?= I18n::get('retreat_package') ?>",
                    descricao: "<?= I18n::get('retreat_package_description') ?>",
                    condicoes: "<?= I18n::get('retreat_package_conditions') ?>",
                    noites: 4,
                    hospedes: 10,
                    preco: 240
                }
            };

            // Verificar código de oferta
            document.getElementById('codigo_oferta').addEventListener('change', function() {
                const codigo = this.value.toUpperCase();
                const detalhesOferta = document.getElementById('detalhes-oferta');
                const tituloOferta = document.getElementById('titulo-oferta');
                const descricaoOferta = document.getElementById('descricao-oferta');
                const condicoesOferta = document.getElementById('condicoes-oferta');
                const numHospedesSelect = document.getElementById('num_hospedes');
                const checkinInput = document.getElementById('checkin');
                const checkoutInput = document.getElementById('checkout');
                
                if (ofertas[codigo]) {
                    const oferta = ofertas[codigo];
                    tituloOferta.textContent = oferta.nome;
                    descricaoOferta.textContent = oferta.descricao;
                    condicoesOferta.textContent = oferta.condicoes;
                    detalhesOferta.style.display = 'block';
                    
                    // Atualizar sessão com a oferta selecionada
                    fetch('atualizar_sessao.php', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/x-www-form-urlencoded',
                        },
                        body: `codigo_oferta=${codigo}`
                    });
                    
                    // Configurar número de hóspedes
                    numHospedesSelect.innerHTML = ''; // Limpa opções existentes
                    
                    // Define o mínimo e máximo de hóspedes conforme a oferta
                    const minHospedes = oferta.hospedes;
                    const maxHospedes = oferta.max_hospedes || minHospedes;
                    
                    for(let i = minHospedes; i <= maxHospedes; i++) {
                        const option = document.createElement('option');
                        option.value = i;
                        option.textContent = `${i} ${i === 1 ? '<?= I18n::get("person") ?>' : '<?= I18n::get("people") ?>'}`;
                        if (i === minHospedes) option.selected = true;
                        numHospedesSelect.appendChild(option);
                    }
                    
                    // Tornar o seletor de hóspedes readonly se não houver variação
                    if (minHospedes === maxHospedes) {
                        numHospedesSelect.disabled = true;
                    } else {
                        numHospedesSelect.disabled = false;
                    }
                    
                    // Atualizar display
                    document.getElementById('display-hospedes').textContent = minHospedes + ' <?= I18n::get("people") ?>';
                    
                    // Configurar o flatpickr para bloquear o número de noites
                    const checkinPicker = checkinInput._flatpickr;
                    const checkoutPicker = checkoutInput._flatpickr;
                    
                    // Limpar eventos anteriores para evitar duplicação
                    checkinPicker.config.onChange = [];
                    checkoutPicker.config.onChange = [];
                    
                    // Quando selecionar check-in, automaticamente definir checkout com o número de noites da oferta
                    checkinPicker.config.onChange.push(function(selectedDates) {
                        if (selectedDates.length > 0) {
                            const checkinDate = selectedDates[0];
                            const checkoutDate = new Date(checkinDate);
                            checkoutDate.setDate(checkoutDate.getDate() + oferta.noites);
                            
                            checkoutPicker.setDate(checkoutDate);
                            checkoutPicker.set('minDate', checkoutDate);
                            checkoutPicker.set('maxDate', checkoutDate);
                            
                            // Atualizar resumo
                            document.getElementById('display-checkin').textContent = formatarData(checkinPicker.input.value);
                            document.getElementById('display-checkout').textContent = formatarData(checkoutPicker.input.value);
                            document.getElementById('display-noites').textContent = oferta.noites;
                        }
                    });
                    
                    // Se já houver checkin selecionado, atualizar checkout
                    if (checkinInput.value) {
                        const checkinDate = new Date(checkinInput.value);
                        const checkoutDate = new Date(checkinDate);
                        checkoutDate.setDate(checkoutDate.getDate() + oferta.noites);
                        
                        checkoutPicker.setDate(checkoutDate);
                        checkoutPicker.set('minDate', checkoutDate);
                        checkoutPicker.set('maxDate', checkoutDate);
                        
                        document.getElementById('display-checkout').textContent = formatarData(checkoutPicker.input.value);
                        document.getElementById('display-noites').textContent = oferta.noites;
                    }
                    
                    // Desabilitar a edição manual do checkout
                    checkoutInput.readOnly = true;
                    
                } else if (codigo === '') {
                    detalhesOferta.style.display = 'none';
                    
                    // Restaurar opções padrão de hóspedes (1-10)
                    numHospedesSelect.innerHTML = '';
                    for(let i = 1; i <= 10; i++) {
                        const option = document.createElement('option');
                        option.value = i;
                        option.textContent = `${i} ${i === 1 ? '<?= I18n::get("person") ?>' : '<?= I18n::get("people") ?>'}`;
                        if (i === 2) option.selected = true; // Valor padrão
                        numHospedesSelect.appendChild(option);
                    }
                    
                    // Habilitar seletor de hóspedes
                    numHospedesSelect.disabled = false;
                    
                    // Habilitar edição do checkout
                    checkoutInput.readOnly = false;
                    
                    // Restaurar comportamento normal do flatpickr
                    const checkinPicker = checkinInput._flatpickr;
                    const checkoutPicker = checkoutInput._flatpickr;
                    
                    checkinPicker.config.onChange = [function(selectedDates) {
                        if (selectedDates.length > 0) {
                            const checkinDate = selectedDates[0];
                            checkoutPicker.set('minDate', new Date(checkinDate.getTime() + 86400000)); // +1 dia
                            checkoutPicker.set('maxDate', null);
                            
                            // Atualizar resumo
                            document.getElementById('display-checkin').textContent = formatarData(checkinPicker.input.value);
                            if (checkoutPicker.input.value) {
                                const diffTime = Math.abs(new Date(checkoutPicker.input.value) - checkinDate);
                                const diffDays = Math.ceil(diffTime / (1000 * 60 * 60 * 24));
                                document.getElementById('display-noites').textContent = diffDays;
                            }
                        }
                    }];
                    
                    // Limpar restrições do checkout
                    checkoutPicker.set('minDate', null);
                    checkoutPicker.set('maxDate', null);
                    
                    // Limpar sessão
                    fetch('atualizar_sessao.php', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/x-www-form-urlencoded',
                        },
                        body: 'codigo_oferta='
                    });
                } else {
                    detalhesOferta.style.display = 'block';
                    tituloOferta.textContent = '<?= I18n::get("invalid_code") ?>';
                    descricaoOferta.textContent = '<?= I18n::get("invalid_code_message") ?>';
                    condicoesOferta.textContent = '<?= I18n::get("check_code_try_again") ?>';
                }
            });

            document.addEventListener('DOMContentLoaded', function() {
                const chatbotButton = document.getElementById('chatbotButton');
                const chatbotBox = document.getElementById('chatbotBox');
                const chatbotClose = document.getElementById('chatbotClose');
                const chatbotInput = document.getElementById('chatbotInput');
                const chatbotSend = document.getElementById('chatbotSend');
                const chatbotMessages = document.getElementById('chatbotMessages');
                const suggestionButtons = document.querySelectorAll('.suggestion-button');

                // Mostrar chatbot box
                chatbotButton.addEventListener('click', function() {
                    chatbotBox.style.display = 'flex';
                    chatbotButton.style.display = 'none';
                });

                // Fechar chatbot box
                chatbotClose.addEventListener('click', function() {
                    chatbotBox.style.display = 'none';
                    chatbotButton.style.display = 'flex';
                });

                // Enviar mensagem ao pressionar Enter
                chatbotInput.addEventListener('keypress', function(e) {
                    if (e.key === 'Enter') {
                        sendMessage();
                    }
                });

                // Enviar mensagem ao clicar no botão
                chatbotSend.addEventListener('click', sendMessage);

                // Botões de sugestão
                suggestionButtons.forEach(button => {
                    button.addEventListener('click', function() {
                        const text = this.textContent.trim();
                        chatbotInput.value = text;
                        sendMessage();
                    });
                });

                // Respostas do chatbot
                const responses = {
                    saudacao: "<?= I18n::get('welcome_chatbot_message') ?>",
                    agradecimento: "Obrigado pelo seu contacto. Ficamos ao dispor para qualquer questão relacionada com a Quinta Flores ou com a região de Ponte de Lima. Desejamos-lhe uma excelente estadia connosco.",
                    despedida: "Agradecemos o seu contacto. Esperamos ter o prazer de o receber brevemente na Quinta Flores. Votos de um excelente dia.",
                    reservas: {
                        geral: "Para efetuar uma reserva na Quinta Flores, dispõe das seguintes opções:\n\n• Utilize o botão 'Reservar Agora' disponível no topo da página\n• Contacte-nos através do número: +351 912 418 976\n• Ou visite-nos presencialmente mediante agendamento.",
                        cancelamento: "A nossa política de cancelamento é flexível:\n\n• Cancelamentos até 7 dias antes da data de chegada – reembolso total\n• Cancelamentos entre 3 e 7 dias – taxa de 30%\n• Cancelamentos com menos de 3 dias – taxa de 50% do valor total da reserva.",
                        alteracao: "As alterações à reserva estão sujeitas a disponibilidade. Recomendamos que entre em contacto connosco com a maior antecedência possível para verificarmos as alternativas disponíveis.",
                        disponibilidade: "Para consultar a disponibilidade para datas específicas, utilize o formulário na página inicial ou contacte-nos diretamente.",
                        antecedencia: "Durante a época alta (junho a setembro) e em períodos festivos, recomendamos que efetue a sua reserva com 1 a 2 meses de antecedência."
                    },
                    acomodacoes: {
                        casaprincipal: "A Casa Principal é nossa maior acomodação com 3 quartos com 5 camas de casal, 3 casas de banho, sala de estar espaçosa, cozinha completa e varanda com vista para os jardins.",
                        geral: "Oferecemos acomodações confortáveis e bem equipadas. A Casa Principal comporta até 10 pessoas com todos os confortos necessários para uma estadia perfeita."
                    },
                    precos: {
                        geral: "A Quinta Flores apresenta um valor fixo de 120€ por noite, com capacidade máxima até 10 pessoas. Para eventos ou ocasiões especiais com número superior de participantes, solicitamos que entre em contacto connosco previamente."
                    },
                    servicos: {
                        geral: "A Quinta Flores disponibiliza diversos serviços pensados para proporcionar uma estadia confortável e memorável:\n\n• Receção disponível das 08h00 às 22h00\n• Piscina exterior com zona de solário\n• Estacionamento privativo gratuito\n• Jardins e zonas de lazer",
                        piscina: "A nossa piscina exterior encontra-se acessível diariamente. Dispõe de zona de solário com espreguiçadeiras e toalhas disponibilizadas gratuitamente aos hóspedes.",
                        wifi: "Disponibilizamos Wi-Fi gratuito de alta velocidade em toda a propriedade, incluindo nas zonas exteriores. A palavra-passe será fornecida no momento do check-in.",
                        limpeza: "O serviço de limpeza é sempre feito antes e depois da estadia. Caso pretenda limpeza diária, poderá ser solicitado por um valor adicional de 15€ por dia.",
                        recepcao: "A receção está disponível entre as 08h00 e as 22h00. Para chegadas fora deste horário, temos ao dispor um sistema de check-in automatizado, mediante pedido prévio."
                    },
                    localizacao: {
                        geral: "A Quinta Flores está situada a cerca de 3 km do centro histórico de Ponte de Lima, oferecendo um ambiente calmo e campestre com fácil acesso às principais atrações da região.",
                        como_chegar: "Como chegar à Quinta Flores:\n\n• De carro: pela A3, tome a saída para Ponte de Lima e siga em direção a Arcozelo. Após aproximadamente 2,5 km, encontrará sinalização com a nossa identificação à direita.",
                        arredores: "Nas proximidades da Quinta Flores poderá explorar vinícolas de Vinho Verde, percursos pedestres, atividades no Rio Lima e restaurantes típicos da gastronomia minhota.",
                        estacionamento: "Disponibilizamos estacionamento privado e gratuito dentro da propriedade, com capacidade para todos os nossos hóspedes."
                    },
                    atividades: {
                        geral: "A região do Minho oferece inúmeras atividades: passeios de bicicleta, degustação de vinhos, caminhadas, passeios a cavalo, canoagem no Rio Lima e visitas culturais. Se tiver interesse pode ver no nosso site mais atividades que pode fazer perto da Quinta Flores.",
                        cicloturismo: "Dispomos ainda de várias rotas para descobrir as paisagens únicas da região.",
                        gastronomia: "O Minho é famoso por sua gastronomia. Recomendamos restaurantes autênticos nas proximidades.",
                        criancas: "Para famílias com crianças, recomendamos: caça ao tesouro em nossos jardins, visita ao parque aventura, piqueniques à beira-rio e passeios de barco no Rio Lima."
                    },
                    fallback: "Sou um assistente virtual da Quinta Flores. Peço desculpa, mas não consegui compreender corretamente a sua pergunta. Poderia reformulá-la ou especificar melhor, por favor?"
                };

                // Função principal para enviar mensagem
                function sendMessage() {
                    const message = chatbotInput.value.trim();
                    if (message === '') return;

                    // Adicionar mensagem do usuário
                    addMessage(message, 'user');
                    chatbotInput.value = '';

                    // Simular digitação do bot
                    showTypingIndicator();

                    // Processar resposta com um pequeno delay
                    setTimeout(() => {
                        removeTypingIndicator();
                        const response = getResponse(message);
                        addMessage(response, 'bot');
                        chatbotMessages.scrollTop = chatbotMessages.scrollHeight;
                    }, 1000 + Math.random() * 1000);
                }

                // Função para adicionar mensagem à conversa
                function addMessage(text, sender) {
                    const messageDiv = document.createElement('div');
                    messageDiv.className = `message ${sender}-message`;
                    
                    let avatar;
                    if (sender === 'bot') {
                        avatar = document.createElement('img');
                        avatar.src = '../assets/logos/logotipo1.png';
                        avatar.alt = 'Bot';
                        avatar.className = 'message-avatar';
                    } else {
                        avatar = document.createElement('div');
                        avatar.className = 'message-avatar';
                        avatar.style.backgroundColor = '#8CB58E';
                        avatar.style.display = 'flex';
                        avatar.style.justifyContent = 'center';
                        avatar.style.alignItems = 'center';
                        avatar.style.color = 'white';
                        avatar.style.fontWeight = 'bold';
                        avatar.textContent = 'EU';
                    }

                    const contentDiv = document.createElement('div');
                    contentDiv.className = 'message-content';
                    contentDiv.innerHTML = formatMessageText(text);

                    if (sender === 'user') {
                        messageDiv.appendChild(contentDiv);
                        messageDiv.appendChild(avatar);
                    } else {
                        messageDiv.appendChild(avatar);
                        messageDiv.appendChild(contentDiv);
                    }

                    chatbotMessages.appendChild(messageDiv);
                }

                // Função para formatar o texto da mensagem
                function formatMessageText(text) {
                    return text.replace(/\n/g, '<br>');
                }

                // Função para mostrar indicador de digitação
                function showTypingIndicator() {
                    const typingDiv = document.createElement('div');
                    typingDiv.className = 'message bot-message typing-message';
                    
                    const avatar = document.createElement('img');
                    avatar.src = '../assets/logos/logotipo1.png';
                    avatar.alt = 'Bot';
                    avatar.className = 'message-avatar';
                    
                    const typingIndicator = document.createElement('div');
                    typingIndicator.className = 'typing-indicator';
                    for (let i = 0; i < 3; i++) {
                        const dot = document.createElement('span');
                        typingIndicator.appendChild(dot);
                    }
                    
                    typingDiv.appendChild(avatar);
                    typingDiv.appendChild(typingIndicator);
                    chatbotMessages.appendChild(typingDiv);
                    chatbotMessages.scrollTop = chatbotMessages.scrollHeight;
                }

                // Função para remover indicador de digitação
                function removeTypingIndicator() {
                    const typingMessage = document.querySelector('.typing-message');
                    if (typingMessage) {
                        typingMessage.remove();
                    }
                }

                // Função para determinar a resposta adequada
                function getResponse(message) {
                    const lowercaseMessage = message.toLowerCase();
                    
                    // 1. Verificar despedidas
                    if (/(adeus|tchau|até logo|até mais|até breve|goodbye|bye|até à próxima|ate a proxima)/i.test(lowercaseMessage)) {
                        return responses.despedida;
                    }
                    
                    // 2. Verificar agradecimentos explícitos
                    if (/(obrigado|obrigada|agradecido|agradecida|thanks|thank you|grato|grata|muito obrigado|muito obrigada)\b/i.test(lowercaseMessage)) {
                        return responses.agradecimento;
                    }
                    
                    // 3. Verificar saudações
                    if (/(olá|ola|oi|bom dia|boa tarde|boa noite|hello|hi|hey|saudações|saudacoes)\b/i.test(lowercaseMessage)) {
                        return responses.saudacao;
                    }
                    
                    // 4. Verificar reservas
                    if (/(reserva|reservar|booking|alugar|disponibilidade|marcar|fazer reserva|agendar|quero reservar)\b/i.test(lowercaseMessage)) {
                        if (/(cancelar|cancelamento|anular|desmarcar|cancelada|cancelar reserva)\b/i.test(lowercaseMessage)) {
                            return responses.reservas.cancelamento;
                        } else if (/(alterar|alteração|mudar|modificar|trocar|alterar reserva)\b/i.test(lowercaseMessage)) {
                            return responses.reservas.alteracao;
                        } else if (/(disponível|disponibilidade|tem vaga|vagas|datas livres|datas disponíveis)\b/i.test(lowercaseMessage)) {
                            return responses.reservas.disponibilidade;
                        } else if (/(antecedência|antecedencia|com antecedência|quando reservar|prazo para reservar|tempo antes)\b/i.test(lowercaseMessage)) {
                            return responses.reservas.antecedencia;
                        } else {
                            return responses.reservas.geral;
                        }
                    }
                    
                    // 5. Verificar acomodações
                    if (/(acomodação|acomodacoes|quarto|quartos|casa|alojamento|hospedagem|suite|suíte)\b/i.test(lowercaseMessage)) {
                        if (/(casa principal|principal|casa mãe|principal casa)\b/i.test(lowercaseMessage)) {
                            return responses.acomodacoes.casaprincipal;
                        } else {
                            return responses.acomodacoes.geral;
                        }
                    }
                    
                    // 6. Verificar preços
                    if (/(preço|preco|preços|precos|valor|valores|custo|quanto custa|tarifa|taxa|preço por noite)\b/i.test(lowercaseMessage)) {
                        return responses.precos.geral;
                    }
                    
                    // 7. Verificar serviços
                    if (/(serviço|servico|facilidade|comodidade|serviços|comodidades|infraestrutura)\b/i.test(lowercaseMessage)) {
                        if (/(piscina|nadar|piscinas|área de lazer aquática)\b/i.test(lowercaseMessage)) {
                            return responses.servicos.piscina;
                        } else if (/(wifi|internet|wi-fi|rede|conexão|conexao)\b/i.test(lowercaseMessage)) {
                            return responses.servicos.wifi;
                        } else if (/(limpeza|arrumação|arrumacao|faxina|serviço de limpeza)\b/i.test(lowercaseMessage)) {
                            return responses.servicos.limpeza;
                        } else if (/(recepção|recepcao|atendimento|balcão|front desk)\b/i.test(lowercaseMessage)) {
                            return responses.servicos.recepcao;
                        } else {
                            return responses.servicos.geral;
                        }
                    }
                    
                    // 8. Verificar localização
                    if (/(localização|localizacao|endereço|endereco|onde fica|como chegar|morada|situação|direção|direcao)\b/i.test(lowercaseMessage)) {
                        if (/(como chegar|chegar|direções|direcoes|rota|caminho|instruções|instrucoes|acesso)\b/i.test(lowercaseMessage)) {
                            return responses.localizacao.como_chegar;
                        } else if (/(arredores|proximidade|perto|próximo|proximo|vizinhança|vizinhanca|área|região|regiao)\b/i.test(lowercaseMessage)) {
                            return responses.localizacao.arredores;
                        } else if (/(estacionamento|parque|carro|vaga|garagem|parking)\b/i.test(lowercaseMessage)) {
                            return responses.localizacao.estacionamento;
                        } else {
                            return responses.localizacao.geral;
                        }
                    }
                    
                    // 9. Verificar atividades
                    if (/(atividade|atividades|fazer|lazer|passeio|passeios|entretenimento|diversão|diversao|programa)\b/i.test(lowercaseMessage)) {
                        if (/(bicicleta|bike|cicloturismo|bicicletas|ciclismo|andar de bicicleta)\b/i.test(lowercaseMessage)) {
                            return responses.atividades.cicloturismo;
                        } else if (/(comida|gastronomia|comer|restaurante|culinária|culinaria|prato|refeição|refeicao)\b/i.test(lowercaseMessage)) {
                            return responses.atividades.gastronomia;
                        } else if (/(criança|criancas|família|familia|kids|crianças|famílias|familias|filhos|filha|filho)\b/i.test(lowercaseMessage)) {
                            return responses.atividades.criancas;
                        } else {
                            return responses.atividades.geral;
                        }
                    }
                    
                    // 10. Se nenhuma das condições acima for atendida
                    return responses.fallback;
                }
            });
        </script>

        <script src="https://cdn.jsdelivr.net/npm/flatpickr"></script>
        <script src="https://cdn.jsdelivr.net/npm/flatpickr/dist/l10n/pt.js"></script>
        <script>
            const datasOcupadas = <?php echo json_encode($todasDatasOcupadas); ?>;
            
            document.addEventListener('DOMContentLoaded', function() {
                // Configuração do Flatpickr
                const checkinPicker = flatpickr("#checkin", {
                    locale: "<?= I18n::getCurrentLanguage() ?>",
                    minDate: "today",
                    dateFormat: "Y-m-d",
                    disable: datasOcupadas,
                    onChange: function(selectedDates, dateStr) {
                        document.getElementById('display-checkin').textContent = formatarData(dateStr);
                        atualizarResumo();
                        
                        // Configura o checkout para ser pelo menos um dia após o checkin
                        checkoutPicker.set('minDate', dateStr);
                    },
                    onOpen: function(selectedDates, dateStr, instance) {
                        instance.set('maxDate', new Date().fp_incr(730)); // 2 anos
                    },
                    onDayCreate: function(dObj, dStr, fp, dayElem) {
                        const dateStr = dayElem.dateObj.toISOString().split('T')[0];
                        if (datasOcupadas.includes(dateStr)) {
                            dayElem.classList.add('reserved');
                            dayElem.title = "<?= I18n::get('unavailable') ?>";
                        }
                    }
                });
                
                const checkoutPicker = flatpickr("#checkout", {
                    locale: "<?= I18n::getCurrentLanguage() ?>",
                    dateFormat: "Y-m-d",
                    disable: datasOcupadas,
                    onChange: function(selectedDates, dateStr) {
                        document.getElementById('display-checkout').textContent = formatarData(dateStr);
                        atualizarResumo();
                    },
                    onDayCreate: function(dObj, dStr, fp, dayElem) {
                        const dateStr = dayElem.dateObj.toISOString().split('T')[0];
                        if (datasOcupadas.includes(dateStr)) {
                            dayElem.classList.add('reserved');
                            dayElem.title = "<?= I18n::get('unavailable') ?>";
                        }
                    }
                });
                
                // Atualizar número de hóspedes no resumo
                document.getElementById('num_hospedes').addEventListener('change', function() {
                    const num = this.value;
                    document.getElementById('display-hospedes').textContent = num + (num == 1 ? ' <?= I18n::get("person") ?>' : ' <?= I18n::get("people") ?>');
                });
                
                // Validação do formulário
                document.getElementById('reservaForm').addEventListener('submit', function(e) {
                    const checkin = document.getElementById('checkin').value;
                    const checkout = document.getElementById('checkout').value;
                    const errorElement = document.querySelector('.error-message');
                    
                    if (!checkin || !checkout) {
                        e.preventDefault();
                        errorElement.style.display = 'block';
                        errorElement.innerHTML = '<i class="fas fa-exclamation-circle"></i> <?= I18n::get("required_field") ?>';
                        return false;
                    }
                    
                    const hoje = new Date();
                    const amanha = new Date(hoje);
                    amanha.setDate(amanha.getDate() + 1);
                    
                    const dataCheckin = new Date(checkin);
                    const dataCheckout = new Date(checkout);
                    const dataLimite = new Date(hoje);
                    dataLimite.setFullYear(dataLimite.getFullYear() + 2);
                    
                    if (dataCheckin < amanha || dataCheckout <= dataCheckin) {
                        e.preventDefault();
                        errorElement.style.display = 'block';
                        errorElement.innerHTML = '<i class="fas fa-exclamation-circle"></i> <?= I18n::get("invalid_dates") ?> <?= I18n::get("checkin_after_tomorrow") ?>';
                        return false;
                    }
                    
                    if (dataCheckin > dataLimite || dataCheckout > dataLimite) {
                        e.preventDefault();
                        errorElement.style.display = 'block';
                        errorElement.innerHTML = '<i class="fas fa-exclamation-circle"></i> <?= I18n::get("reservation_limit") ?> ' + formatarData(dataLimite.toISOString().split('T')[0]) + '.';
                        return false;
                    }
                    
                    return true;
                });
            });
            
            function atualizarResumo() {
                const checkin = document.getElementById('checkin').value;
                const checkout = document.getElementById('checkout').value;
                
                if (checkin && checkout) {
                    const diffTime = Math.abs(new Date(checkout) - new Date(checkin));
                    const diffDays = Math.ceil(diffTime / (1000 * 60 * 60 * 24));
                    document.getElementById('display-noites').textContent = diffDays;
                }
            }
            
            function formatarData(dataStr) {
                if (!dataStr) return '--/--/----';
                const [ano, mes, dia] = dataStr.split('-');
                return `${dia}/${mes}/${ano}`;
            }
        </script>
    </div>
    <?php require_once 'footer.php'; ?>
</body>
</html>