<?php
require '../conexao.php';

// Pagar manutenção (se houver pedido para marcar como paga)
$mensagem = '';
if (isset($_GET['marcar_pago'])) {
    $id_manutencao = (int) $_GET['marcar_pago'];

    // Verifica se a manutenção já está paga
    $verifica = $conexao->query("SELECT M_custo, M_pago FROM manutencao WHERE M_id_manutencao = $id_manutencao");
    $dados = $verifica->fetch_assoc();

    if ($dados && !$dados['M_pago']) {
        $custo = $dados['M_custo'];

        // Calcula saldo atual
        $total_ganhos = $conexao->query("SELECT SUM(R_valor_pago) AS total FROM reservas WHERE R_estado IN ('confirmada', 'concluída')")->fetch_assoc()['total'] ?? 0;
        $total_gastos = $conexao->query("SELECT SUM(M_custo) AS total FROM manutencao WHERE M_pago = 1")->fetch_assoc()['total'] ?? 0;
        $saldo = $total_ganhos - $total_gastos;

        // Verifica se há saldo suficiente
        if ($saldo >= $custo) {
            $conexao->query("UPDATE manutencao SET M_pago = 1 WHERE M_id_manutencao = $id_manutencao");
            $mensagem = "Pago com sucesso!";
        } else {
            $mensagem = "Saldo insuficiente para pagar esta manutenção.";
        }
    }
}

// Cálculo do saldo, ganhos e gastos
$total_ganhos = $conexao->query("SELECT SUM(R_valor_pago) AS total FROM reservas WHERE R_estado IN ('confirmada', 'concluída')")->fetch_assoc()['total'] ?? 0;
$total_gastos = $conexao->query("SELECT SUM(M_custo) AS total FROM manutencao WHERE M_pago = 1")->fetch_assoc()['total'] ?? 0;
$saldo = $total_ganhos - $total_gastos;
$lucro = $total_ganhos - ($conexao->query("SELECT SUM(M_custo) AS total FROM manutencao")->fetch_assoc()['total'] ?? 0);

// Mostrar detalhes
if (isset($_GET['mostrar_gastos']) && $_GET['mostrar_gastos'] == '1') {
    $gastos_detalhados = $conexao->query("SELECT * FROM manutencao");
}
if (isset($_GET['mostrar_ganhos']) && $_GET['mostrar_ganhos'] == '1') {
    $ganhos_detalhados = $conexao->query("SELECT r.R_id_reserva, r.R_preco_total, h.H_nome, c.C_nome
                                         FROM reservas r
                                         JOIN hospedes h ON r.R_id_hospede = h.H_id_hospede
                                         JOIN casas c ON r.R_id_casa = c.C_id_casa");
}
?>
<!DOCTYPE html>
<html lang="pt">
<head>
    <meta charset="UTF-8">
    <link rel="stylesheet" href="admin.css">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <style>
        body { font-family: Arial; background: #f5f5f5; text-align: center; padding: 40px; }
        .grafico-container {
            width: 30%; 
            max-width: 500px; 
            margin: 20px auto;
            background: white; 
            padding: 20px;
            box-shadow: 0 0 10px rgba(0,0,0,0.1);
            border-radius: 10px;
            float: left;
        }
        .detalhes-gastos, .detalhes-ganhos {
            margin-top: 30px;
            background-color: white;
            padding: 20px;
            box-shadow: 0 0 10px rgba(0,0,0,0.1);
            border-radius: 10px;
            width: 50%;
            float: right;
            text-align: left;
            margin-left: 20px;
        }
        .lucro {
            font-size: 20px;
            font-weight: bold;
            margin-top: 20px;
            color: #333;
        }
        .clearfix { clear: both; }
        .pago { color: green; font-weight: bold; }
        .por-pagar { color: red; font-weight: bold; }
        .botao-pagar { background-color: yellow; color: black; padding: 5px 10px; border: none; cursor: pointer; border-radius: 5px; }
    </style>
</head>
<body>
    <div style="display: flex; align-items: center; gap: 10px;">
        <img src="https://img.icons8.com/?size=100&id=57714&format=png&color=000000" alt="Ícone Ganhos e Gastos" style="height: 50px;">
        <h1>Relatório de Ganhos vs Gastos</h1>
    </div>

    <h2 style="margin-top: 20px;">Saldo atual da conta: <span style="color: <?= $saldo < 0 ? 'red' : 'green' ?>;">€<?= number_format($saldo, 2, ',', '.') ?></span></h2>

    <?php if ($mensagem): ?>
        <div style="margin: 20px auto; padding: 10px; width: 50%; background-color: <?= strpos($mensagem, 'sucesso') !== false ? '#d4edda' : '#f8d7da' ?>; color: <?= strpos($mensagem, 'sucesso') !== false ? '#155724' : '#721c24' ?>; border: 1px solid <?= strpos($mensagem, 'sucesso') !== false ? '#c3e6cb' : '#f5c6cb' ?>; border-radius: 5px;">
            <?= $mensagem ?>
        </div>
    <?php endif; ?>

    <div class="grafico-container">
        <h2>Gastos vs. Ganhos</h2>
        <canvas id="graficoGastosGanhos"></canvas>
    </div>

    <div class="lucro">
        <h3>Lucro</h3>
        <p>Lucro: €<?= number_format($lucro, 2, ',', '.') ?></p>
    </div>

    <?php if (isset($gastos_detalhados)): ?>
        <div class="detalhes-gastos">
            <h3>Detalhes dos Gastos</h3>
            <table>
                <tr>
                    <th>Tipo</th>
                    <th>Custo (€)</th>
                    <th>Estado</th>
                </tr>
                <?php while ($gasto = $gastos_detalhados->fetch_assoc()): ?>
                    <tr>
                        <td><?= htmlspecialchars($gasto['M_tipo']) ?></td>
                        <td><?= number_format($gasto['M_custo'], 2, ',', '.') ?>€</td>
                        <td>
                            <?php if ($gasto['M_pago']): ?>
                                <span class="pago">Pago</span>
                            <?php else: ?>
                                <span class="por-pagar">Por pagar</span>
                                <a href="?mostrar_gastos=1&marcar_pago=<?= $gasto['M_id_manutencao'] ?>">
                                    <button class="botao-pagar">Pagar</button>
                                </a>
                            <?php endif; ?>
                        </td>
                    </tr>
                <?php endwhile; ?>
            </table>
        </div>
    <?php endif; ?>

    <?php if (isset($ganhos_detalhados)): ?>
        <div class="detalhes-ganhos">
            <h3>Detalhes dos Ganhos</h3>
            <table>
                <tr>
                    <th>Hóspede</th>
                    <th>Casa</th>
                    <th>Preço Total (€)</th>
                </tr>
                <?php while ($ganho = $ganhos_detalhados->fetch_assoc()): ?>
                    <tr>
                        <td><?= htmlspecialchars($ganho['H_nome']) ?></td>
                        <td><?= htmlspecialchars($ganho['C_nome']) ?></td>
                        <td><?= number_format($ganho['R_preco_total'], 2, ',', '.') ?>€</td>
                    </tr>
                <?php endwhile; ?>
            </table>
        </div>
    <?php endif; ?>

    <div class="clearfix"></div>

    <script>
        var gastos = <?= json_encode($total_gastos); ?>;
        var ganhos = <?= json_encode($total_ganhos); ?>;

        var ctx = document.getElementById('graficoGastosGanhos').getContext('2d');
        var graficoGastosGanhos = new Chart(ctx, {
            type: 'pie',
            data: {
                labels: ['Gastos', 'Ganhos'],
                datasets: [{
                    data: [gastos, ganhos],
                    backgroundColor: ['#FF5733', '#28A745'],
                    borderColor: ['#C70039', '#218838'],
                    borderWidth: 1
                }]
            },
            options: {
                responsive: true,
                plugins: {
                    legend: { position: 'top' },
                    tooltip: {
                        callbacks: {
                            label: function(tooltipItem) {
                                var label = tooltipItem.label || '';
                                var value = tooltipItem.raw;
                                var currency = '€' + value.toFixed(2);
                                return label + ': ' + currency;
                            }
                        }
                    }
                },
                onClick: function(e) {
                    var activePoint = graficoGastosGanhos.getElementsAtEventForMode(e, 'nearest', {intersect: true}, false);
                    if (activePoint.length > 0) {
                        var index = activePoint[0].index;
                        if (index === 0) {
                            window.location.href = "?mostrar_gastos=1";
                        } else if (index === 1) {
                            window.location.href = "?mostrar_ganhos=1";
                        }
                    }
                }
            }
        });
    </script>
</body>
</html>