<?php
include('../../conexao.php');

// Buscar casas e hóspedes
$casas = $conexao->query("SELECT C_id_casa, C_nome, C_preco_noite FROM casas WHERE C_estado = 'disponível' ORDER BY C_nome");
$hospedes = $conexao->query("SELECT H_id_hospede, H_nome, H_telefone FROM hospedes ORDER BY H_nome");

// Buscar datas ocupadas (todas as casas)
$ocupadas = [];
$res = $conexao->query("SELECT R_data_checkin, R_data_checkout FROM reservas WHERE R_estado != 'cancelada'");
while ($row = $res->fetch_assoc()) {
    $checkin = new DateTime($row['R_data_checkin']);
    $checkout = new DateTime($row['R_data_checkout']);
    while ($checkin < $checkout) {
        $ocupadas[] = $checkin->format('Y-m-d');
        $checkin->modify('+1 day');
    }
}
$ocupadas = array_unique($ocupadas);

$ocupadas_json = json_encode($ocupadas);
$hoje = date('Y-m-d');
$amanha = date('Y-m-d', strtotime('+1 day'));

if (isset($_GET['modal'])) {
?>
<div class="modal-content" style="max-width: 480px; min-width: 320px;">
    <div class="modal-header" style="display: flex; justify-content: space-between; align-items: center; border-bottom: 1px solid var(--cor-borda); padding-bottom: 10px;">
        <h2 style="margin:0; color: var(--cor-titulo); font-size: 1.3rem;">Adicionar Reserva</h2>
        <button class="modal-close close-btn" onclick="fecharModalReserva()">×</button>
    </div>
    <div id="wizardBar" class="flex" style="gap:8px; margin: 18px 0 24px 0; justify-content:center;">
        <div id="wizardStep1Bar" class="badge badge-info" style="background: var(--cor-primaria); color: #fff;">1</div>
        <div style="width: 32px; height: 3px; background: var(--cor-borda); align-self: center;"></div>
        <div id="wizardStep2Bar" class="badge badge-info" style="background: var(--cor-borda); color: var(--cor-primaria);">2</div>
    </div>
    <form id="formAdicionarReserva" method="POST" class="mb-3">
        <div id="wizardStep1">
            <div class="form-group">
                <label>Casa:</label>
                <select name="id_casa" required aria-label="Selecionar casa">
                    <option value="">Selecione</option>
                    <?php while ($c = $casas->fetch_assoc()): ?>
                        <option value="<?= $c['C_id_casa'] ?>" data-preco="<?= $c['C_preco_noite'] ?>">
                            <?= htmlspecialchars($c['C_nome']) ?> (<?= number_format($c['C_preco_noite'], 2) ?>€/noite)
                        </option>
                    <?php endwhile; ?>
                </select>
                <div class="error-message" style="display:none;">Campo obrigatório</div>
            </div>
            <div class="form-group">
                <label>Check-in:</label>
                <input type="text" name="data_checkin" id="data_checkin" required autocomplete="off" placeholder="Escolha a data de entrada" aria-label="Data de check-in">
                <div class="error-message" style="display:none;">Campo obrigatório</div>
            </div>
            <div class="form-group">
                <label>Check-out:</label>
                <input type="text" name="data_checkout" id="data_checkout" required autocomplete="off" placeholder="Escolha a data de saída" aria-label="Data de check-out">
                <div class="error-message" style="display:none;">Campo obrigatório</div>
            </div>
            <div class="form-group">
                <label>Nº Hóspedes:</label>
                <input type="number" name="num_hospedes" id="num_hospedes" min="1" value="1" required aria-label="Número de hóspedes">
                <div class="error-message" style="display:none;">Campo obrigatório</div>
            </div>
            <div class="form-group">
                <label>Origem:</label>
                <select name="origem" required aria-label="Origem da reserva">
                    <option value="online">Online</option>
                    <option value="presencial">Presencial</option>
                    <option value="chamada">Chamada</option>
                </select>
                <div class="error-message" style="display:none;">Campo obrigatório</div>
            </div>
            <div class="form-footer flex-center">
                <button type="button" id="btnWizardProximoReserva" class="btn btn-primary" style="width: 100%;">Próximo</button>
            </div>
        </div>
        <div id="wizardStep2" style="display:none;">
            <div class="form-group">
                <label>Status:</label>
                <select name="estado" required aria-label="Status da reserva">
                    <option value="pendente" class="badge badge-warning">Pendente</option>
                    <option value="confirmada" class="badge badge-success">Confirmada</option>
                </select>
                <div class="error-message" style="display:none;">Campo obrigatório</div>
            </div>
            <div class="form-group">
                <label>Serviços:</label>
                <input type="text" name="servicos" placeholder="Ex: Limpeza, Decoração..." aria-label="Serviços adicionais">
            </div>
            <div class="form-group">
                <label>Valor Pago (€):</label>
                <input type="number" step="0.01" name="valor_pago" min="0" value="0.00" aria-label="Valor pago">
            </div>
            <div class="form-group">
                <label>Referência Pagamento:</label>
                <input type="text" name="referencia_pagamento" maxlength="100" aria-label="Referência de pagamento" placeholder="Opcional">
            </div>
            <div class="oferta-container">
                <h3 style="color: var(--cor-primaria);"><i class="fas fa-gift"></i> Código Promocional</h3>
                <div class="form-group">
                    <label for="codigo_oferta"><i class="fas fa-tag"></i> Tem código promocional?</label>
                    <input type="text" id="codigo_oferta" name="codigo_oferta" class="form-control" placeholder="Digite o código" aria-label="Código promocional">
                </div>
                <div id="detalhes-oferta" style="display: none; margin-top: 15px; padding: 15px; background-color: #f8f9fa; border-radius: 5px;">
                    <h4 id="titulo-oferta"></h4>
                    <p id="descricao-oferta"></p>
                    <p id="condicoes-oferta" style="font-weight: bold;"></p>
                </div>
            </div>
            <div class="form-footer flex" style="justify-content: space-between;">
                <button type="button" id="btnWizardAnteriorReserva" class="btn btn-secondary">Anterior</button>
                <button type="submit" class="btn btn-primary">Salvar Reserva</button>
            </div>
        </div>
    </form>
</div>
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css">
<style>
.flatpickr-day.reserved, .flatpickr-day.reserved:hover {
    background: #ffcccc !important;
    color: #ff0000 !important;
    border-radius: 50% !important;
    position: relative;
}
</style>
<script src="https://cdn.jsdelivr.net/npm/flatpickr"></script>
<script>
const datasOcupadas = <?php echo json_encode(array_values($ocupadas)); ?>;

flatpickr("#data_checkin", {
    dateFormat: "Y-m-d",
    minDate: "today",
    disable: datasOcupadas,
    onDayCreate: function(dObj, dStr, fp, dayElem) {
        const dateStr = dayElem.dateObj.toISOString().split('T')[0];
        if (datasOcupadas.includes(dateStr)) {
            dayElem.classList.add('reserved');
            dayElem.title = "Data já reservada";
        }
    }
});
flatpickr("#data_checkout", {
    dateFormat: "Y-m-d",
    minDate: "today",
    disable: datasOcupadas,
    onDayCreate: function(dObj, dStr, fp, dayElem) {
        const dateStr = dayElem.dateObj.toISOString().split('T')[0];
        if (datasOcupadas.includes(dateStr)) {
            dayElem.classList.add('reserved');
            dayElem.title = "Data já reservada";
        }
    }
});

// Wizard controlado pelo JS do arquivo principal
// Ofertas disponíveis (igual pagina1.php)
const ofertas = {
    'LOVE260': {
        nome: 'Pacote Amor',
        descricao: 'Desconto especial para casais apaixonados.',
        condicoes: 'Válido para reservas de 2 noites e 2 hóspedes.',
        noites: 2,
        hospedes: 2,
        preco: 260
    },
    'PARTY260': {
        nome: 'Pacote Festa',
        descricao: 'Ideal para grupos e celebrações.',
        condicoes: 'Inclui decoração temática gratuita. 2 noites, até 10 hóspedes.',
        noites: 2,
        hospedes: 4,
        max_hospedes: 10,
        preco: 260
    },
    'RETIRO240': {
        nome: 'Pacote Retiro',
        descricao: 'Desconto para estadias tranquilas.',
        condicoes: 'Válido apenas durante a semana. 4 noites, 10 hóspedes.',
        noites: 4,
        hospedes: 10,
        preco: 240
    }
};

const codigoOfertaInput = document.getElementById('codigo_oferta');
const detalhesOferta = document.getElementById('detalhes-oferta');
const tituloOferta = document.getElementById('titulo-oferta');
const descricaoOferta = document.getElementById('descricao-oferta');
const condicoesOferta = document.getElementById('condicoes-oferta');
const numHospedesInput = document.getElementById('num_hospedes');
const checkinInput = document.getElementById('data_checkin');
const checkoutInput = document.getElementById('data_checkout');

if (codigoOfertaInput) {
    codigoOfertaInput.addEventListener('change', function() {
        const codigo = this.value.toUpperCase();
        if (ofertas[codigo]) {
            const oferta = ofertas[codigo];
            tituloOferta.textContent = oferta.nome;
            descricaoOferta.textContent = oferta.descricao;
            condicoesOferta.textContent = oferta.condicoes;
            detalhesOferta.style.display = 'block';
            // Bloquear nº de hóspedes e noites conforme oferta
            numHospedesInput.value = oferta.hospedes;
            numHospedesInput.min = oferta.hospedes;
            numHospedesInput.max = oferta.max_hospedes || oferta.hospedes;
            numHospedesInput.readOnly = (oferta.hospedes === (oferta.max_hospedes || oferta.hospedes));
            // Bloquear datas
            checkinInput.addEventListener('change', function() {
                if (checkinInput.value) {
                    const checkinDate = new Date(checkinInput.value);
                    const checkoutDate = new Date(checkinDate);
                    checkoutDate.setDate(checkinDate.getDate() + oferta.noites);
                    checkoutInput.value = checkoutDate.toISOString().split('T')[0];
                    checkoutInput.readOnly = true;
                }
            });
        } else {
            detalhesOferta.style.display = 'none';
            numHospedesInput.readOnly = false;
            numHospedesInput.min = 1;
            numHospedesInput.max = 10;
            checkoutInput.readOnly = false;
        }
    });
}
</script>
<?php
    exit;
}
?>

<!DOCTYPE html>
<html lang="pt">
<head>
    <link rel="icon" type="image/png" sizes="32x32" href="../assets/logos/favicon-32x32.png">
    <link rel="icon" type="image/png" sizes="16x16" href="../assets/logos/favicon-16x16.png">
    <meta charset="UTF-8">
    <link rel="stylesheet" href="../global.css">
    <title>Adicionar Reserva</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        /* Personalização para datas reservadas */
        .flatpickr-day.flatpickr-disabled.ocupada {
            background-color: #ffcccc;
            color: #ff0000;
            text-decoration: line-through;
            position: relative;
        }

        .flatpickr-day.flatpickr-disabled.ocupada::after {
            content: '\f057'; /* Ícone X círculo */
            font-family: 'Font Awesome 6 Free';
            font-weight: 900;
            position: absolute;
            font-size: 18px;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            opacity: 0.5;
        }

        .form-group {
            margin-bottom: 15px;
        }

        .form-group label {
            display: flex;
            align-items: center;
            margin-bottom: 5px;
        }

        .form-group label i {
            margin-right: 8px;
            width: 16px;
        }

        .total-box {
            background-color: #f9f9f9;
            border: 1px solid #ddd;
            padding: 15px;
            margin: 20px 0;
            border-radius: 5px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }

        .total-box h3 {
            margin-top: 0;
            color: #333;
            border-bottom: 1px solid #ddd;
            padding-bottom: 5px;
        }

        button.button {
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
        }

        .button-small {
            display: inline-flex;
            align-items: center;
            padding: 5px 10px;
            font-size: 0.9em;
            background-color: #4CAF50;
            color: white;
            text-decoration: none;
            border-radius: 4px;
            margin-left: 10px;
        }

        .button-small i {
            margin-right: 4px;
        }

        /* Estilo para ofertas */
        .oferta-container {
            margin: 20px 0;
            padding: 15px;
            background-color: #f8f9fa;
            border-radius: 5px;
            border: 1px solid #ddd;
        }

        .oferta-container h3 {
            margin-top: 0;
            color: #333;
        }

        #detalhes-oferta {
            margin-top: 15px;
            padding: 15px;
            background-color: #fff;
            border-radius: 5px;
            border: 1px solid #eee;
        }
    </style>
</head>
<body>
    <div style="display: flex; align-items: center; gap: 10px;">
        <i class="fas fa-calendar-plus fa-2x"></i>
        <h1>Adicionar Reserva</h1>
    </div>

    <form method="POST" action="processar_reserva.php" id="reservaForm">
        <div class="form-group">
            <label for="id_casa"><i class="fas fa-home"></i> Casa:</label>
            <select name="id_casa" id="id_casa" required onchange="atualizarPreco(); atualizarDatas(); calcularTotal();">
                <option value="">-- Selecione --</option>
                <?php while ($c = $casas->fetch_assoc()): ?>
                    <option value="<?= $c['C_id_casa'] ?>" data-preco="<?= $c['C_preco_noite'] ?>">
                        <?= htmlspecialchars($c['C_nome']) ?> (<?= number_format($c['C_preco_noite'], 2) ?>€/noite)
                    </option>
                <?php endwhile; ?>
            </select>
        </div>

        <div class="form-group">
            <label for="data_checkin"><i class="fas fa-calendar-check"></i> Check-in:</label>
            <input type="text" id="data_checkin" name="data_checkin" placeholder="Selecione a data" required autocomplete="off">
        </div>

        <div class="form-group">
            <label for="data_checkout"><i class="fas fa-calendar-times"></i> Check-out:</label>
            <input type="text" id="data_checkout" name="data_checkout" placeholder="Selecione a data" required autocomplete="off">
        </div>

        <div class="form-group">
            <label for="id_hospede"><i class="fas fa-user"></i> Hóspede:</label>
            <select name="id_hospede" id="id_hospede" required>
                <option value="">-- Selecione --</option>
                <?php while ($h = $hospedes->fetch_assoc()): ?>
                    <option value="<?= $h['H_id_hospede'] ?>">
                        <?= htmlspecialchars($h['H_nome']) ?> - <?= htmlspecialchars($h['H_telefone']) ?>
                    </option>
                <?php endwhile; ?>
            </select>
            <a href="adicionar_hospede.php" class="button-small"><i class="fas fa-plus"></i> Novo</a>
        </div>

        <div class="form-group">
            <label for="num_hospedes"><i class="fas fa-users"></i> Número de Hóspedes:</label>
            <input type="number" id="num_hospedes" name="num_hospedes" min="1" value="1" required>
        </div>

        <!-- Seção de Ofertas -->
<div class="oferta-container">
    <h3><i class="fas fa-gift"></i> Ofertas Especiais</h3>
    <div class="form-group">
        <label for="codigo_oferta"><i class="fas fa-tag"></i> Código Promocional:</label>
        <select id="codigo_oferta" name="codigo_oferta" class="form-control">
            <option value="">-- Selecione uma oferta --</option>
            <option value="LOVE260">LOVE260</option>
            <option value="PARTY260">PARTY260</option>
            <option value="RETIRO240">RETIRO240</option>
        </select>
    </div>
    <div id="detalhes-oferta" style="display: none;">
        <h4 id="titulo-oferta"></h4>
        <p id="descricao-oferta"></p>
        <p id="condicoes-oferta" style="font-weight: bold;"></p>
    </div>
</div>

        <!-- Serviços adicionais -->
        <fieldset style="margin-bottom:15px; padding: 10px; border: 1px solid #ddd; border-radius: 5px;">
            <legend><i class="fas fa-concierge-bell"></i> Serviços Adicionais</legend>

            <div class="form-group">
                <label for="decoracao_tematica"><i class="fas fa-palette"></i> Decoração Temática (130€):</label>
                <select id="decoracao_tematica" name="decoracao_tematica">
                    <option value="">Selecione um tema</option>
                    <option value="Romântico">Romântico</option>
                    <option value="Aniversário">Aniversário</option>
                    <option value="Natal">Natal</option>
                    <option value="Lua de Mel">Lua de Mel</option>
                    <option value="Outro">Outro</option>
                </select>
            </div>

            <div class="form-group">
                <label><i class="fas fa-broom"></i> Limpeza Diária (15€/noite):</label>
                <input type="checkbox" id="limpeza_diaria" name="limpeza_diaria">
            </div>

            <div class="form-group">
                <label><i class="fas fa-gift"></i> Cesto de Boas-Vindas (10€):</label>
                <input type="checkbox" id="cesto_boas_vindas" name="cesto_boas_vindas">
            </div>
        </fieldset>
        <div class="form-group">
    <label for="origem"><i class="fas fa-map-marker-alt"></i> Origem da Reserva:</label>
    <select name="origem" id="origem" required>
        <option value="presencial">Presencial</option>
        <option value="chamada">Por Chamada</option>
        <option value="online">Online</option>
    </select>
</div>

        <div class="total-box">
            <h3><i class="fas fa-receipt"></i> Total Estimado</h3>
            <p><i class="fas fa-tag"></i> Preço por noite: <span id="preco_noite">0.00</span>€</p>
            <p><i class="fas fa-moon"></i> Noites: <span id="noites">0</span></p>
            <p><i class="fas fa-concierge-bell"></i> Serviços adicionais: <span id="preco_servicos">0.00</span>€</p>
            <p><i class="fas fa-gift"></i> Desconto: <span id="desconto_oferta">0.00</span>€</p>
            <p><strong><i class="fas fa-money-bill-wave"></i> Total: <span id="preco_total">0.00</span>€</strong></p>
        </div>

        <button type="submit" class="button"><i class="fas fa-check-circle"></i> Confirmar Reserva</button>
    </form>

    <script src="https://cdn.jsdelivr.net/npm/flatpickr"></script>
    <script>
        const casasOcupadas = <?= $ocupadas_json ?>;
        
        // Ofertas disponíveis
const ofertas = {
    'LOVE260': {
        nome: "Pacote Romântico",
        descricao: "2 noites para 2 pessoas com decoração romântica incluída.",
        condicoes: "Oferta válida para reservas de 2 noites, 2 pessoas.",
        noites: 2,
        hospedes: 2,
        preco: 260 // Preço fixo para esta oferta
    },
    'PARTY260': {
        nome: "Pacote Festa",
        descricao: "2 noites para até 4 pessoas com cesto de boas-vindas incluído.",
        condicoes: "Oferta válida para reservas de 2 noites, 4-10 pessoas.",
        noites: 2,
        hospedes: 4,
        max_hospedes: 10,
        preco: 260 // Preço fixo para esta oferta
    },
    'RETIRO240': {
        nome: "Pacote Retiro",
        descricao: "4 noites para até 10 pessoas com limpeza diária incluída.",
        condicoes: "Oferta válida para reservas de 4 noites, 10 pessoas.",
        noites: 4,
        hospedes: 10,
        preco: 240 // Preço fixo para esta oferta
    }
};

        const checkinPicker = flatpickr("#data_checkin", {
            dateFormat: "Y-m-d",
            minDate: "<?= $hoje ?>",
            disable: [],
            onChange: function(selectedDates, dateStr, instance) {
                atualizarDatas();
                calcularTotal();
            }
        });

        const checkoutPicker = flatpickr("#data_checkout", {
            dateFormat: "Y-m-d",
            minDate: "<?= $amanha ?>",
            disable: [],
            onChange: function(selectedDates, dateStr, instance) {
                calcularTotal();
            }
        });

        function atualizarDatas() {
            const casaSelect = document.getElementById('id_casa');
            const idCasa = casaSelect.value;
            if (!idCasa || !casasOcupadas[idCasa]) {
                checkinPicker.set('disable', []);
                checkoutPicker.set('disable', []);
                return;
            }

            const datasOcupadasCasa = casasOcupadas[idCasa];
            checkinPicker.set('disable', datasOcupadasCasa);
            checkoutPicker.set('disable', datasOcupadasCasa);

            // Após bloquear as datas, adiciona a classe 'ocupada' para a estilização vermelha
            setTimeout(() => {
                document.querySelectorAll('.flatpickr-day.flatpickr-disabled').forEach(dayEl => {
                    const dateStr = dayEl.dateObj.toISOString().slice(0,10);
                    if (datasOcupadasCasa.includes(dateStr)) {
                        dayEl.classList.add('ocupada');
                    }
                });
            }, 50);
        }

        function atualizarPreco() {
            const casaSelect = document.getElementById('id_casa');
            const precoNoiteSpan = document.getElementById('preco_noite');
            if (casaSelect.value) {
                const preco = parseFloat(casaSelect.selectedOptions[0].dataset.preco);
                precoNoiteSpan.textContent = preco.toFixed(2);
            } else {
                precoNoiteSpan.textContent = '0.00';
            }
        }

        function calcularTotal() {
    const casaSelect = document.getElementById('id_casa');
    if (!casaSelect.value) {
        atualizarPreco();
        atualizarNoites(0);
        atualizarServicos(0);
        atualizarTotal(0);
        return;
    }

    const precoNoite = parseFloat(casaSelect.selectedOptions[0].dataset.preco) || 0;
    const checkin = document.getElementById('data_checkin').value;
    const checkout = document.getElementById('data_checkout').value;

    if (!checkin || !checkout) {
        atualizarNoites(0);
        atualizarServicos(0);
        atualizarTotal(0);
        return;
    }

    const dtCheckin = new Date(checkin);
    const dtCheckout = new Date(checkout);

    if (dtCheckout <= dtCheckin) {
        atualizarNoites(0);
        atualizarServicos(0);
        atualizarTotal(0);
        return;
    }

    const diffTime = dtCheckout - dtCheckin;
    const diffDays = diffTime / (1000 * 60 * 60 * 24);

    atualizarNoites(diffDays);

    let servicos = 0;
    // Verificar se uma oferta está selecionada
    const codigoOferta = document.getElementById('codigo_oferta').value.toUpperCase();
    const ofertaSelecionada = ofertas[codigoOferta];
    
    // Só calcular serviços adicionais se NÃO houver oferta selecionada
    if (!ofertaSelecionada) {
        // Decoração temática fixa 130€ se selecionada
        const decoracao = document.getElementById('decoracao_tematica').value;
        if (decoracao) servicos += 130;

        // Limpeza diária 15€/noite
        if (document.getElementById('limpeza_diaria').checked) servicos += 15 * diffDays;

        // Cesto boas-vindas 10€
        if (document.getElementById('cesto_boas_vindas').checked) servicos += 10;
    }

    atualizarServicos(servicos);

    let total = 0;
    let desconto = 0;
    
    if (ofertaSelecionada) {
        // Verificar se a oferta atende aos requisitos (número de noites e hóspedes)
        const numHospedes = parseInt(document.getElementById('num_hospedes').value);
        
        if (diffDays >= ofertaSelecionada.noites && 
            numHospedes >= ofertaSelecionada.hospedes &&
            (!ofertaSelecionada.max_hospedes || numHospedes <= ofertaSelecionada.max_hospedes)) {
            
            // Usar preço fixo da oferta
            total = ofertaSelecionada.preco;
            
            // Calcular "desconto" (diferença entre preço normal e oferta)
            const precoNormal = precoNoite * ofertaSelecionada.noites;
            desconto = precoNormal - ofertaSelecionada.preco;
        } else {
            // Se não atender aos requisitos, calcular normalmente
            total = (precoNoite * diffDays) + servicos;
        }
    } else {
        // Cálculo normal sem oferta
        total = (precoNoite * diffDays) + servicos;
    }

    document.getElementById('desconto_oferta').textContent = desconto.toFixed(2);
    atualizarTotal(total);
}

        function atualizarNoites(n) {
            document.getElementById('noites').textContent = n;
        }

        function atualizarServicos(valor) {
            document.getElementById('preco_servicos').textContent = valor.toFixed(2);
        }

        function atualizarTotal(valor) {
            document.getElementById('preco_total').textContent = valor.toFixed(2);
        }

        // Verificar código de oferta
document.getElementById('codigo_oferta').addEventListener('change', function() {
    const codigo = this.value.toUpperCase();
    const detalhesOferta = document.getElementById('detalhes-oferta');
    const tituloOferta = document.getElementById('titulo-oferta');
    const descricaoOferta = document.getElementById('descricao-oferta');
    const condicoesOferta = document.getElementById('condicoes-oferta');
    const numHospedesInput = document.getElementById('num_hospedes');
    const servicosAdicionais = document.querySelectorAll('#decoracao_tematica, #limpeza_diaria, #cesto_boas_vindas');
            
            if (ofertas[codigo]) {
                const oferta = ofertas[codigo];
                tituloOferta.textContent = oferta.nome;
                descricaoOferta.textContent = oferta.descricao;
                condicoesOferta.textContent = oferta.condicoes;
                detalhesOferta.style.display = 'block';

            servicosAdicionais.forEach(servico => {
            servico.disabled = true;
            if (servico.type === 'checkbox') {
                servico.checked = false;
            } else if (servico.tagName === 'SELECT') {
                servico.selectedIndex = 0;
            }
        });
                
                // Configurar número mínimo de hóspedes
                numHospedesInput.min = oferta.hospedes;
                if (oferta.max_hospedes) {
                    numHospedesInput.max = oferta.max_hospedes;
                } else {
                    numHospedesInput.max = oferta.hospedes;
                }
                
                // Se a oferta tem número fixo de hóspedes, definir o valor
                if (!oferta.max_hospedes || oferta.hospedes === oferta.max_hospedes) {
                    numHospedesInput.value = oferta.hospedes;
                }
                
                // Configurar o flatpickr para bloquear o número de noites
                const checkinPicker = document.getElementById('data_checkin')._flatpickr;
                const checkoutPicker = document.getElementById('data_checkout')._flatpickr;
                
                // Limpar eventos anteriores para evitar duplicação
                checkinPicker.config.onChange = [];
                
                // Quando selecionar check-in, automaticamente definir checkout com o número de noites da oferta
                checkinPicker.config.onChange.push(function(selectedDates) {
                    if (selectedDates.length > 0) {
                        const checkinDate = selectedDates[0];
                        const checkoutDate = new Date(checkinDate);
                        checkoutDate.setDate(checkoutDate.getDate() + oferta.noites);
                        
                        checkoutPicker.setDate(checkoutDate);
                        checkoutPicker.set('minDate', checkoutDate);
                        checkoutPicker.set('maxDate', checkoutDate);
                    }
                });
                
                // Se já houver checkin selecionado, atualizar checkout
                if (document.getElementById('data_checkin').value) {
                    const checkinDate = new Date(document.getElementById('data_checkin').value);
                    const checkoutDate = new Date(checkinDate);
                    checkoutDate.setDate(checkoutDate.getDate() + oferta.noites);
                    
                    checkoutPicker.setDate(checkoutDate);
                    checkoutPicker.set('minDate', checkoutDate);
                    checkoutPicker.set('maxDate', checkoutDate);
                }
                
                // Desabilitar a edição manual do checkout
                document.getElementById('data_checkout').readOnly = true;
                
            } else if (codigo === '') {
                detalhesOferta.style.display = 'none';
                
                // Restaurar configurações padrão
                numHospedesInput.min = 1;
                numHospedesInput.max = 20;
                numHospedesInput.value = 1;
                
                // Habilitar edição do checkout
                document.getElementById('data_checkout').readOnly = false;
                
                // Restaurar comportamento normal do flatpickr
                const checkinPicker = document.getElementById('data_checkin')._flatpickr;
                const checkoutPicker = document.getElementById('data_checkout')._flatpickr;
                
                checkinPicker.config.onChange = [function(selectedDates) {
                    if (selectedDates.length > 0) {
                        const checkinDate = selectedDates[0];
                        checkoutPicker.set('minDate', new Date(checkinDate.getTime() + 86400000)); // +1 dia
                        checkoutPicker.set('maxDate', null);
                    }
                }];
                
                // Limpar restrições do checkout
                checkoutPicker.set('minDate', null);
                checkoutPicker.set('maxDate', null);
            } else {
                detalhesOferta.style.display = 'block';
                tituloOferta.textContent = 'Código inválido';
                descricaoOferta.textContent = 'O código promocional inserido não é válido.';
                condicoesOferta.textContent = 'Por favor, verifique o código e tente novamente.';
            }
            
            // Recalcular total após mudança na oferta
            calcularTotal();
        });

        // Atualizar preço e datas ao mudar a casa
        document.getElementById('id_casa').addEventListener('change', () => {
            atualizarPreco();
            atualizarDatas();
            calcularTotal();
        });

        // Atualizar total ao mudar serviços
        ['decoracao_tematica', 'limpeza_diaria', 'cesto_boas_vindas', 'num_hospedes'].forEach(id => {
            const el = document.getElementById(id);
            el.addEventListener('change', calcularTotal);
        });

        // Validação e sugestão de datas livres antes do submit
        document.getElementById('reservaForm').addEventListener('submit', function(e) {
            const casaId = document.getElementById('id_casa').value;
            const checkin = document.getElementById('data_checkin').value;
            const checkout = document.getElementById('data_checkout').value;

            if (!casaId || !checkin || !checkout) return; // HTML required já cobre

            const datasOcupadasCasa = casasOcupadas[casaId] || [];

            // Verificar se algum dia do intervalo está ocupado
            let dtCheckin = new Date(checkin);
            const dtCheckout = new Date(checkout);
            let conflito = false;

            while (dtCheckin < dtCheckout) {
                const dia = dtCheckin.toISOString().slice(0,10);
                if (datasOcupadasCasa.includes(dia)) {
                    conflito = true;
                    break;
                }
                dtCheckin.setDate(dtCheckin.getDate() + 1);
            }

            if (conflito) {
                e.preventDefault();

                // Encontrar o próximo intervalo livre a partir da data checkin desejada
                let dataInicio = new Date(checkin);
                let dataFim = new Date(checkout);
                const duracao = (new Date(checkout) - new Date(checkin)) / (1000*60*60*24);

                // Incrementar dataInicio até achar intervalo livre
                while (true) {
                    let livre = true;
                    for (let d = 0; d < duracao; d++) {
                        let diaTeste = new Date(dataInicio);
                        diaTeste.setDate(diaTeste.getDate() + d);
                        let diaStr = diaTeste.toISOString().slice(0,10);
                        if (datasOcupadasCasa.includes(diaStr)) {
                            livre = false;
                            break;
                        }
                    }
                    if (livre) break;
                    dataInicio.setDate(dataInicio.getDate() + 1);
                }
                dataFim = new Date(dataInicio);
                dataFim.setDate(dataFim.getDate() + duracao);

                alert(`As datas selecionadas estão ocupadas. O próximo intervalo livre para ${duracao} noites é de ${dataInicio.toISOString().slice(0,10)} até ${dataFim.toISOString().slice(0,10)}.`);

                // Atualizar os campos para as datas sugeridas
                document.getElementById('data_checkin')._flatpickr.setDate(dataInicio, true);
                document.getElementById('data_checkout')._flatpickr.setDate(dataFim, true);

                calcularTotal();
            }
        });

        // Inicialização
        atualizarPreco();
        atualizarDatas();
    </script>
</body>
</html>