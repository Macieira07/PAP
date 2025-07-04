<?php
// Conexão com o banco de dados
include('../../conexao.php');

// Obter o ID da reserva a ser editada
$reserva_id = $_GET['id'] ?? null;

if ($reserva_id === null) {
    die("Reserva não encontrada.");
}

// Buscar detalhes da reserva
$stmt = $conexao->prepare("SELECT R_id_casa, R_id_hospede, R_data_checkin, R_data_checkout, R_estado, R_num_hospedes FROM reservas WHERE R_id_reserva = ?");
$stmt->bind_param("i", $reserva_id);
$stmt->execute();
$reserva = $stmt->get_result()->fetch_assoc();
$stmt->close();

if (!$reserva) {
    die("Reserva não encontrada.");
}

// Buscar casas e hóspedes
$casas = $conexao->query("SELECT C_id_casa, C_nome, C_preco_noite FROM casas WHERE C_estado = 'disponível'");
$hospedes = $conexao->query("SELECT H_id_hospede, H_nome FROM hospedes");

// Buscar datas ocupadas
// Substituir a consulta atual por esta:
$ocupadas = [];
$res = $conexao->query("
    SELECT R_id_casa, R_data_checkin, R_data_checkout 
    FROM reservas 
    WHERE R_estado != 'cancelada'
    AND (R_data_checkout > CURDATE() OR R_data_checkin > CURDATE())
");
while ($row = $res->fetch_assoc()) {
    $id_casa = $row['R_id_casa'];
    $checkin = new DateTime($row['R_data_checkin']);
    $checkout = new DateTime($row['R_data_checkout']);
    while ($checkin < $checkout) {
        $ocupadas[$id_casa][] = $checkin->format('Y-m-d');
        $checkin->modify('+1 day');
    }
}
// Remover duplicados
foreach ($ocupadas as &$datas) {
    $datas = array_unique($datas);
    sort($datas); // Ordenar as datas
}
unset($datas);
$ocupadas_json = json_encode($ocupadas);
?>

<!DOCTYPE html>
<html lang="pt">
<head>
  <meta charset="UTF-8">
      <link rel="icon" type="image/png" sizes="32x32" href="../assets/logos/favicon-32x32.png">
<link rel="icon" type="image/png" sizes="16x16" href="../assets/logos/favicon-16x16.png">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <link rel="stylesheet" href="../global.css">
  <title>Editar Reserva</title>
  <style>
    .flatpickr-day.ocupada {
    background-color: #ffcccc !important;
    color: #ff0000 !important;
    text-decoration: line-through;
    border-color: #ff0000 !important;
}

.flatpickr-day.ocupada:hover {
    background-color: #ffaaaa !important;
}

.event.busy {
    position: absolute;
    bottom: 2px;
    left: 50%;
    transform: translateX(-50%);
    width: 6px;
    height: 6px;
    background-color: #ff0000;
    border-radius: 50%;
}
    .ocupada {
      background-color: red !important;
      color: white;
    }
  </style>
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css">
</head>
<body>
<div class="modal-content" style="max-width: 480px; min-width: 320px;">
    <div class="modal-header" style="display: flex; justify-content: space-between; align-items: center; border-bottom: 1px solid var(--cor-borda); padding-bottom: 10px;">
        <h2 style="margin:0; color: var(--cor-titulo); font-size: 1.3rem;">Editar Reserva</h2>
        <button class="modal-close close-btn" onclick="fecharModalReserva()">×</button>
    </div>
    <div id="wizardBar" class="flex" style="gap:8px; margin: 18px 0 24px 0; justify-content:center;">
        <div id="wizardStep1Bar" class="badge badge-info" style="background: var(--cor-primaria); color: #fff;">1</div>
        <div style="width: 32px; height: 3px; background: var(--cor-borda); align-self: center;"></div>
        <div id="wizardStep2Bar" class="badge badge-info" style="background: var(--cor-borda); color: var(--cor-primaria);">2</div>
    </div>
    <form method="POST" action="processar_edicao_reserva.php" id="formEditarReserva">
        <input type="hidden" name="reserva_id" value="<?= $reserva_id ?>">
        <div id="wizardStep1">
            <div class="form-group">
                <label>Casa:</label>
                <select name="id_casa" id="id_casa" required>
                    <?php $casas->data_seek(0); while ($c = $casas->fetch_assoc()): ?>
                        <option value="<?= $c['C_id_casa'] ?>" data-preco="<?= $c['C_preco_noite'] ?>" <?= $c['C_id_casa'] == $reserva['R_id_casa'] ? 'selected' : '' ?>>
                            <?= htmlspecialchars($c['C_nome']) ?> (<?= number_format($c['C_preco_noite'], 2) ?>€/noite)
                        </option>
                    <?php endwhile; ?>
                </select>
            </div>
            <div class="form-group">
                <label>Hóspede:</label>
                <select name="id_hospede" id="id_hospede" required>
                    <?php $hospedes->data_seek(0); while ($h = $hospedes->fetch_assoc()): ?>
                        <option value="<?= $h['H_id_hospede'] ?>" <?= $h['H_id_hospede'] == $reserva['R_id_hospede'] ? 'selected' : '' ?>>
                            <?= htmlspecialchars($h['H_nome']) ?>
                        </option>
                    <?php endwhile; ?>
                </select>
            </div>
            <div class="form-group">
                <label>Check-in:</label>
                <input type="text" id="data_checkin" name="data_checkin" value="<?= $reserva['R_data_checkin'] ?>" required class="input" placeholder="Escolha a data de entrada">
            </div>
            <div class="form-group">
                <label>Check-out:</label>
                <input type="text" id="data_checkout" name="data_checkout" value="<?= $reserva['R_data_checkout'] ?>" required class="input" placeholder="Escolha a data de saída">
            </div>
            <div class="form-group">
                <label>Nº Hóspedes:</label>
                <input type="number" id="num_hospedes" name="num_hospedes" min="1" max="10" value="<?= $reserva['R_num_hospedes'] ?>" required>
            </div>
            <div class="form-footer flex-center">
                <button type="button" id="btnWizardProximoReserva" class="btn btn-primary" style="width: 100%;">Próximo</button>
            </div>
        </div>
        <div id="wizardStep2" style="display:none;">
            <div class="form-group">
                <label>Estado:</label>
                <select name="estado" id="estado" required>
                    <option value="pendente" <?= $reserva['R_estado'] == 'pendente' ? 'selected' : '' ?>>Pendente</option>
                    <option value="confirmada" <?= $reserva['R_estado'] == 'confirmada' ? 'selected' : '' ?>>Confirmada</option>
                </select>
            </div>
            <div class="form-group">
                <label>Método de Pagamento:</label>
                <select name="metodo_pagamento" id="metodo_pagamento" required>
                    <option value="mbway">MB Way</option>
                    <option value="dinheiro">Dinheiro</option>
                    <option value="transferencia">Transferência</option>
                    <option value="cartao">Cartão de Crédito</option>
                </select>
            </div>
            <div class="form-group">
                <label>Origem:</label>
                <select name="origem" id="origem" required>
                    <option value="presencial" <?= $reserva['R_origem'] == 'presencial' ? 'selected' : '' ?>>Presencial</option>
                    <option value="chamada" <?= $reserva['R_origem'] == 'chamada' ? 'selected' : '' ?>>Por Chamada</option>
                    <option value="online" <?= $reserva['R_origem'] == 'online' ? 'selected' : '' ?>>Online</option>
                </select>
            </div>
            <div class="form-group">
                <label>Preço total estimado:</label>
                <span id="preco_total" class="badge badge-info">0.00€</span>
            </div>
            <div class="form-footer flex" style="justify-content: space-between;">
                <button type="button" id="btnWizardAnteriorReserva" class="btn btn-secondary">Anterior</button>
                <button type="submit" class="btn btn-primary">Atualizar Reserva</button>
            </div>
        </div>
    </form>
</div>
<script src="https://cdn.jsdelivr.net/npm/flatpickr"></script>
<script>
function initFlatpickrEditarReserva() {
    const datasOcupadas = <?php echo json_encode(array_values($ocupadas)); ?>;
    flatpickr("#data_checkin", {
      dateFormat: "Y-m-d",
      minDate: "today",
      disable: datasOcupadas,
      onDayCreate: function(dObj, dStr, fp, dayElem) {
        const dateStr = dayElem.dateObj.toISOString().split('T')[0];
        if (datasOcupadas.includes(dateStr)) {
          dayElem.classList.add('flatpickr-day', 'disabled', 'ocupada');
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
          dayElem.classList.add('flatpickr-day', 'disabled', 'ocupada');
          dayElem.title = "Data já reservada";
        }
      }
    });
}
// Wizard JS
function initWizardReserva() {
    var btnProximo = document.getElementById('btnWizardProximoReserva');
    var btnAnterior = document.getElementById('btnWizardAnteriorReserva');
    if (btnProximo) {
        btnProximo.onclick = function() {
            // Validação dos campos obrigatórios do passo 1
            var camposObrigatorios = document.querySelectorAll('#wizardStep1 [required]');
            var valido = true;
            camposObrigatorios.forEach(function(campo) {
                if (!campo.value.trim()) {
                    campo.style.borderColor = 'red';
                    valido = false;
                } else {
                    campo.style.borderColor = '';
                }
            });
            if (!valido) {
                alert('Por favor, preencha todos os campos obrigatórios.');
                return;
            }
            // Verificar se as datas são válidas
            var checkin = document.querySelector('[name=data_checkin]').value;
            var checkout = document.querySelector('[name=data_checkout]').value;
            if (new Date(checkout) <= new Date(checkin)) {
                alert('A data de check-out deve ser posterior à data de check-in.');
                return;
            }
            // Avançar para o passo 2
            document.getElementById('wizardStep1').style.display = 'none';
            document.getElementById('wizardStep2').style.display = 'block';
            document.getElementById('wizardStep1Bar').style.background = 'var(--cor-borda)';
            document.getElementById('wizardStep2Bar').style.background = 'var(--cor-primaria)';
        };
    }
    if (btnAnterior) {
        btnAnterior.onclick = function() {
            document.getElementById('wizardStep2').style.display = 'none';
            document.getElementById('wizardStep1').style.display = 'block';
            document.getElementById('wizardStep1Bar').style.background = 'var(--cor-primaria)';
            document.getElementById('wizardStep2Bar').style.background = 'var(--cor-borda)';
        };
    }
}
// Atualizar preço total
const selectCasa = document.getElementById('id_casa');
const inputCheckin = document.getElementById('data_checkin');
const inputCheckout = document.getElementById('data_checkout');
const spanTotal = document.getElementById('preco_total');
function atualizarTotal() {
    const opc = selectCasa.selectedOptions[0];
    const precoNoite = parseFloat(opc.dataset.preco);
    const ci = new Date(inputCheckin.value);
    const co = new Date(inputCheckout.value);
    let total = 0;
    if (ci && co && co > ci) {
      const dias = (co - ci) / (1000 * 60 * 60 * 24);
      total = dias * precoNoite;
    }
    spanTotal.textContent = total.toFixed(2) + '€';
}
inputCheckin.addEventListener('change', atualizarTotal);
inputCheckout.addEventListener('change', atualizarTotal);
selectCasa.addEventListener('change', atualizarTotal);
window.onload = atualizarTotal;
</script>
</body>
</html>
