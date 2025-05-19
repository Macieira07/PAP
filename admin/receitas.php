<?php
// Inclui a conexão com o banco de dados
require '../conexao.php';

// Consultar todas as receitas com informações adicionais
$query = "
    SELECT r.*, 
           CASE 
               WHEN r.R_origem = 'reserva' THEN CONCAT('Reserva #', res.R_id_reserva, ' - ', h.H_nome)
               WHEN r.R_origem = 'servico' THEN CONCAT('Serviço: ', s.S_nome)
               WHEN r.R_origem = 'manutencao' THEN CONCAT('Manutenção #', m.M_id_manutencao, ' - ', c.C_nome)
               ELSE 'Outro'
           END AS origem_detalhada
    FROM receitas r
    LEFT JOIN reservas res ON r.R_origem_id = res.R_id_reserva
    LEFT JOIN hospedes h ON res.R_id_hospede = h.H_id_hospede
    LEFT JOIN servicos s ON r.R_origem_id = s.S_id_servico
    LEFT JOIN manutencao m ON r.R_origem_id = m.M_id_manutencao
    LEFT JOIN casas c ON m.M_id_casa = c.C_id_casa
    ORDER BY r.R_data DESC";
$resultado = $conexao->query($query);

// Processar formulário de reserva
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['adicionar_reserva'])) {
    $id_casa = $_POST['id_casa'];
    $id_hospede = $_POST['id_hospede'];
    $data_checkin = $_POST['data_checkin'];
    $data_checkout = $_POST['data_checkout'];
    $num_hospedes = $_POST['num_hospedes'];
    $preco_total = $_POST['preco_total'];

    $stmt = $conexao->prepare("INSERT INTO reservas (R_id_casa, R_id_hospede, R_data_checkin, R_data_checkout, R_num_hospedes, R_preco_total, R_valor_pago, R_estado) 
                               VALUES (?, ?, ?, ?, ?, ?, 0, 'pendente')");
    $stmt->bind_param("iissid", $id_casa, $id_hospede, $data_checkin, $data_checkout, $num_hospedes, $preco_total);

    if ($stmt->execute()) {
        $mensagem = "Reserva adicionada com sucesso!";
    } else {
        $erro = "Erro ao adicionar reserva: " . $conexao->error;
    }
}

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
?>
<!DOCTYPE html>
<html lang="pt">
<head>
    <link rel="icon" type="image/png" sizes="32x32" href="../assets/logos/favicon-32x32.png">
    <link rel="stylesheet" href="admin.css">
    <meta charset="UTF-8">
    <title>Listar Receitas</title>
    <script src="https://cdn.jsdelivr.net/npm/flatpickr"></script>
</head>
<body>
<div style="display: flex; align-items: center; gap: 10px;">
    <img src="https://img.icons8.com/?size=100&id=24836&format=png&color=000000" alt="Ícone Receitas" style="height: 50px;">
    <h1>Todas as Receitas</h1>
</div>

<?php if (isset($mensagem)): ?>
    <div class="success-message"><?= $mensagem ?></div>
<?php elseif (isset($erro)): ?>
    <div class="error-message"><?= $erro ?></div>
<?php endif; ?>

<table border="1">
    <thead>
        <tr>
            <th>Descrição</th>
            <th>Valor</th>
            <th>Data</th>
            <th>Tipo</th>
            <th>Origem</th>
            <th>Observações</th>
            <th>Método de Pagamento</th>
            <th>Ações</th>
        </tr>
    </thead>
    <tbody>
        <?php while ($receita = $resultado->fetch_assoc()): ?>
        <tr>
            <td><?= htmlspecialchars($receita['R_descricao']) ?></td>
            <td>€<?= number_format($receita['R_valor'], 2, ',', '.') ?></td>
            <td><?= htmlspecialchars($receita['R_data']) ?></td>
            <td><?= htmlspecialchars($receita['R_tipo']) ?></td>
            <td><?= htmlspecialchars($receita['origem_detalhada']) ?></td>
            <td><?= htmlspecialchars($receita['R_observacoes']) ?></td>
            <td><?= htmlspecialchars($receita['R_metodo_pagamento']) ?></td>
            <td>
                <!-- Link para Editar -->
                <a href="editar_receita.php?id=<?= $receita['R_id_receita'] ?>">Editar</a> |
                <!-- Link para Deletar -->
                <a href="eliminar_receita.php?id=<?= $receita['R_id_receita'] ?>" onclick="return confirm('Tem certeza que deseja deletar esta receita?')">Deletar</a>
            </td>
        </tr>
        <?php endwhile; ?>
    </tbody>
</table>

<h2>Adicionar Reserva</h2>
<form method="POST" action="">
    <input type="hidden" name="adicionar_reserva" value="1">
    <div>
        <label>Casa:</label>
        <select name="id_casa" id="id_casa" required onchange="atualizarPreco(); atualizarDatas();">
            <option value="">-- Selecione --</option>
            <?php while ($c = $casas->fetch_assoc()): ?>
                <option value="<?= $c['C_id_casa'] ?>" data-preco="<?= $c['C_preco_noite'] ?>">
                    <?= htmlspecialchars($c['C_nome']) ?> (<?= number_format($c['C_preco_noite'], 2) ?>€/noite)
                </option>
            <?php endwhile; ?>
        </select>
    </div>
    <div>
        <label>Check-in:</label>
        <input type="text" id="data_checkin" name="data_checkin" required>
    </div>
    <div>
        <label>Check-out:</label>
        <input type="text" id="data_checkout" name="data_checkout" required>
    </div>
    <div>
        <label>Hóspede:</label>
        <select name="id_hospede" required>
            <option value="">-- Selecione --</option>
            <?php while ($h = $hospedes->fetch_assoc()): ?>
                <option value="<?= $h['H_id_hospede'] ?>">
                    <?= htmlspecialchars($h['H_nome']) ?> - <?= htmlspecialchars($h['H_telefone']) ?>
                </option>
            <?php endwhile; ?>
        </select>
        <a href="adicionar_hospede.php" class="button-small"><i class="fas fa-plus"></i> Novo</a>
    </div>
    <div>
        <label>Número de Hóspedes:</label>
        <input type="number" name="num_hospedes" min="1" value="1" required>
    </div>
    <div class="total-box">
        <h3>Total Estimado</h3>
        <p>Preço por noite: <span id="preco_noite">0.00</span>€</p>
        <p>Noites: <span id="noites">0</span></p>
        <p><strong>Total: <span id="preco_total">0.00</span>€</strong></p>
    </div>
    <button type="submit">Reservar</button>
</form>

<script>
    const datasOcupadas = <?= $ocupadas_json ?>;
    let checkinPicker, checkoutPicker;

    function atualizarDatas() {
        const casaId = document.getElementById('id_casa').value;
        const datasDesativadas = datasOcupadas[casaId] || [];

        if (checkinPicker) checkinPicker.destroy();
        if (checkoutPicker) checkoutPicker.destroy();

        checkinPicker = flatpickr("#data_checkin", {
            dateFormat: "Y-m-d",
            disable: datasDesativadas,
            onChange: function (selectedDates) {
                if (selectedDates.length > 0) {
                    const minCheckout = new Date(selectedDates[0]);
                    minCheckout.setDate(minCheckout.getDate() + 1);
                    checkoutPicker.set("minDate", minCheckout);
                    calcularTotal();
                }
            }
        });

        checkoutPicker = flatpickr("#data_checkout", {
            dateFormat: "Y-m-d",
            disable: datasDesativadas,
            onChange: calcularTotal
        });
    }

    function atualizarPreco() {
        const casaSelect = document.getElementById('id_casa');
        const precoNoite = parseFloat(casaSelect.options[casaSelect.selectedIndex]?.dataset.preco || 0);
        document.getElementById('preco_noite').textContent = precoNoite.toFixed(2);
        calcularTotal(precoNoite);
    }

    function calcularTotal(precoNoite = 0) {
        const checkin = checkinPicker?.selectedDates[0];
        const checkout = checkoutPicker?.selectedDates[0];
        let noites = 0;

        if (checkin && checkout) {
            noites = Math.ceil((checkout - checkin) / (1000 * 60 * 60 * 24));
        }

        document.getElementById('noites').textContent = noites;
        document.getElementById('preco_total').textContent = (noites * precoNoite).toFixed(2);
    }

    atualizarDatas();
</script>
</body>
</html>

<?php
// Fechar a conexão
$conexao->close();
?>
