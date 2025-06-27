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
    <link rel="stylesheet" href="../public/css/admin.css">
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