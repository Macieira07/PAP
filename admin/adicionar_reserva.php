<?php
include('../conexao.php');

// Buscar casas e hóspedes
$casas = $conexao->query("SELECT C_id_casa, C_nome, C_preco_noite FROM casas WHERE C_estado = 'disponível' ORDER BY C_nome");
$hospedes = $conexao->query("SELECT H_id_hospede, H_nome, H_telefone FROM hospedes ORDER BY H_nome");

// Buscar datas ocupadas (organizadas por casa)
$ocupadas = [];
$stmt = $conexao->prepare("SELECT R_id_casa, R_data_checkin, R_data_checkout FROM reservas WHERE R_estado != 'cancelada'");
$stmt->execute();
$stmt->bind_result($id_casa, $data_checkin, $data_checkout);
while ($stmt->fetch()) {
    if (!isset($ocupadas[$id_casa])) {
        $ocupadas[$id_casa] = [];
    }

    $checkin = new DateTime($data_checkin);
    $checkout = new DateTime($data_checkout);
    while ($checkin <= $checkout) {
        $ocupadas[$id_casa][] = $checkin->format('Y-m-d');
        $checkin->modify('+1 day');
    }
}
$stmt->close();

$ocupadas_json = json_encode($ocupadas);
$hoje = date('Y-m-d');
$amanha = date('Y-m-d', strtotime('+1 day'));
?>

<!DOCTYPE html>
<html lang="pt">
<head>
    <meta charset="UTF-8">
    <link rel="stylesheet" href="admin.css">
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
            <select name="id_casa" id="id_casa" required onchange="atualizarPreco(); atualizarDatas();">
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
            <input type="text" id="data_checkin" name="data_checkin" placeholder="Selecione a data" required>
        </div>

        <div class="form-group">
            <label for="data_checkout"><i class="fas fa-calendar-times"></i> Check-out:</label>
            <input type="text" id="data_checkout" name="data_checkout" placeholder="Selecione a data" required>
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

        <div class="total-box">
            <h3><i class="fas fa-receipt"></i> Total Estimado</h3>
            <p><i class="fas fa-tag"></i> Preço por noite: <span id="preco_noite">0.00</span>€</p>
            <p><i class="fas fa-moon"></i> Noites: <span id="noites">0</span></p>
            <p><strong><i class="fas fa-money-bill-wave"></i> Total: <span id="preco_total">0.00</span>€</strong></p>
        </div>

        <button type="submit" class="button"><i class="fas fa-check-circle"></i> Confirmar Reserva</button>
    </form>

    <script src="https://cdn.jsdelivr.net/npm/flatpickr"></script>
    <script>
        const datasOcupadas = <?= $ocupadas_json ?>;

        let checkinPicker;
        let checkoutPicker;

        function atualizarDatas() {
            const casaId = document.getElementById('id_casa').value;
            const datasDesativadas = datasOcupadas[casaId] || [];

            if (checkinPicker) checkinPicker.destroy();
            if (checkoutPicker) checkoutPicker.destroy();

            // Opções comuns para ambos os pickers
            const configComum = {
                dateFormat: "Y-m-d",
                onDayCreate: function(dObj, dStr, fp, dayElem) {
                    // Marca visualmente as datas ocupadas
                    const dataFormatada = dayElem.dateObj.getFullYear() + '-' + 
                                         String(dayElem.dateObj.getMonth() + 1).padStart(2, '0') + '-' + 
                                         String(dayElem.dateObj.getDate()).padStart(2, '0');
                    
                    if (datasDesativadas.includes(dataFormatada)) {
                        dayElem.classList.add('ocupada');
                    }
                }
            };

            checkinPicker = flatpickr("#data_checkin", {
                ...configComum,
                minDate: "today",
                disable: datasDesativadas,
                onChange: function (selectedDates, dateStr) {
                    if (selectedDates.length > 0) {
                        const minCheckout = new Date(selectedDates[0]);
                        minCheckout.setDate(minCheckout.getDate() + 1);
                        checkoutPicker.set("minDate", minCheckout);
                        calcularTotal();
                    }
                }
            });

            checkoutPicker = flatpickr("#data_checkout", {
                ...configComum,
                minDate: "tomorrow",
                disable: datasDesativadas,
                onChange: calcularTotal
            });
        }

        function atualizarPreco() {
            const casaSelect = document.getElementById('id_casa');
            const precoNoite = casaSelect.options[casaSelect.selectedIndex]?.dataset.preco || 0;
            document.getElementById('preco_noite').textContent = parseFloat(precoNoite).toFixed(2);
            calcularTotal();
        }

        function calcularTotal() {
            const casaSelect = document.getElementById('id_casa');
            const precoNoite = parseFloat(casaSelect.options[casaSelect.selectedIndex]?.dataset.preco || 0);
            const checkin = checkinPicker?.selectedDates[0];
            const checkout = checkoutPicker?.selectedDates[0];

            let noites = 0;
            if (checkin && checkout) {
                noites = Math.ceil((checkout - checkin) / (1000 * 60 * 60 * 24));
            }

            document.getElementById('noites').textContent = noites;
            document.getElementById('preco_total').textContent = (noites * precoNoite).toFixed(2);
        }

        // Inicialização base
        atualizarDatas();
    </script>
</body>
</html>