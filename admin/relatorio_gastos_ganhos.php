<?php
require '../conexao.php';

$mensagem = '';

// Pagar manutenção (se houver pedido para marcar como paga)
if (isset($_GET['marcar_pago'])) {
    $id_manutencao = (int) $_GET['marcar_pago'];

    $verifica = $conexao->query("SELECT M_custo, M_pago FROM manutencao WHERE M_id_manutencao = $id_manutencao");
    $dados = $verifica->fetch_assoc();

    if ($dados && !$dados['M_pago']) {
        $custo = $dados['M_custo'];
        $total_ganhos = $conexao->query("SELECT SUM(R_valor_pago) AS total FROM reservas WHERE R_estado IN ('confirmada', 'concluída')")->fetch_assoc()['total'] ?? 0;
        $total_gastos = $conexao->query("SELECT SUM(M_custo) AS total FROM manutencao WHERE M_pago = 1")->fetch_assoc()['total'] ?? 0;
        $saldo = $total_ganhos - $total_gastos;

        if ($saldo >= $custo) {
            $conexao->query("UPDATE manutencao SET M_pago = 1 WHERE M_id_manutencao = $id_manutencao");
            $mensagem = "Pago com sucesso!";
        } else {
            $mensagem = "Saldo insuficiente para pagar esta manutenção.";
        }
    }
}

// Filtros e ordenação
$filtro_tipo = $_GET['filtro_tipo'] ?? '';
$ordem = $_GET['ordem'] ?? 'data';

$where_clause = $filtro_tipo ? "WHERE M_tipo = '" . $conexao->real_escape_string($filtro_tipo) . "'" : '';
$order_clause = $ordem === 'valor' ? 'ORDER BY M_custo DESC' : 'ORDER BY M_data_inicio DESC';

// Dados para gráfico mensal
$ganhos_mensais = $conexao->query("SELECT DATE_FORMAT(R_data_checkin, '%Y-%m') AS mes, SUM(R_valor_pago) AS total FROM reservas WHERE R_estado IN ('confirmada', 'concluída') GROUP BY mes");
$gastos_mensais = $conexao->query("SELECT DATE_FORMAT(M_data_inicio, '%Y-%m') AS mes, SUM(M_custo) AS total FROM manutencao WHERE M_pago = 1 GROUP BY mes");

$dados_ganhos = [];
$dados_gastos = [];

if ($ganhos_mensais) {
    while ($linha = $ganhos_mensais->fetch_assoc()) {
        $dados_ganhos[$linha['mes']] = (float)$linha['total'];
    }
}

if ($gastos_mensais) {
    while ($linha = $gastos_mensais->fetch_assoc()) {
        $dados_gastos[$linha['mes']] = (float)$linha['total'];
    }
}

$meses = array_unique(array_merge(array_keys($dados_ganhos), array_keys($dados_gastos)));
sort($meses);

date_default_timezone_set('Europe/Lisbon');
$total_ganhos = array_sum($dados_ganhos);
$total_gastos = array_sum($dados_gastos);
$saldo = $total_ganhos - $total_gastos;
$lucro = $total_ganhos - ($conexao->query("SELECT SUM(M_custo) AS total FROM manutencao")->fetch_assoc()['total'] ?? 0);

// Dados detalhados com filtros
$gastos_detalhados = $conexao->query("SELECT * FROM manutencao $where_clause $order_clause");
$ganhos_detalhados = $conexao->query("SELECT r.R_id_reserva, r.R_preco_total, h.H_nome, c.C_nome FROM reservas r JOIN hospedes h ON r.R_id_hospede = h.H_id_hospede JOIN casas c ON r.R_id_casa = c.C_id_casa");

header("Content-Type: text/html; charset=UTF-8");
?>
<!DOCTYPE html>
<html lang="pt">
<head>
<link rel="stylesheet" href="admin.css">
    <meta charset="UTF-8">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <title>Relatório Financeiro</title>
</head>
<body>
    
    <h1>Relatório de Ganhos vs Gastos</h1>
    <h2>Saldo atual: <span style="color: <?= $saldo < 0 ? 'red' : 'green' ?>;">€<?= number_format($saldo, 2, ',', '.') ?></span></h2>

    <?php if ($mensagem): ?>
        <p><?= $mensagem ?></p>
    <?php endif; ?>
    <a href="admin.php">← Voltar</a>

    <form method="get">
        <label>Filtrar por tipo de manutenção:</label>
        <input type="text" name="filtro_tipo" value="<?= htmlspecialchars($filtro_tipo) ?>">
        <label>Ordenar por:</label>
        <select name="ordem">
            <option value="data" <?= $ordem === 'data' ? 'selected' : '' ?>>Data</option>
            <option value="valor" <?= $ordem === 'valor' ? 'selected' : '' ?>>Valor</option>
        </select>
        <button type="submit">Aplicar</button>
        <a href="exportar_gastos_csv.php" style="display:inline-block; padding:10px; background:#007bff; color:white; text-decoration:none; border-radius:5px;">
    Exportar Gastos (CSV)
</a>

    </form>

    <h3>Lucro: €<?= number_format($lucro, 2, ',', '.') ?></h3>

    <canvas id="graficoMensal"></canvas>

    <h3>Gastos Detalhados</h3>
    <table border="1">
        <tr><th>Tipo</th><th>Valor</th><th>Data início</th><th>Estado</th></tr>
        <?php while ($gasto = $gastos_detalhados->fetch_assoc()): ?>
        <tr>
            <td><?= htmlspecialchars($gasto['M_tipo']) ?></td>
            <td>€<?= number_format($gasto['M_custo'], 2, ',', '.') ?></td>
            <td><?= $gasto['M_data_inicio'] ?></td>
            <td>
                <?= $gasto['M_pago'] ? '<span style="color:green">Pago</span>' : '<span style="color:red">Por pagar</span>' ?>
                <?php if (!$gasto['M_pago']): ?>
                    <a href="?marcar_pago=<?= $gasto['M_id_manutencao'] ?>&filtro_tipo=<?= urlencode($filtro_tipo) ?>&ordem=<?= $ordem ?>">[Pagar]</a>
                <?php endif; ?>
            </td>
        </tr>
        <?php endwhile; ?>
        <a href="admin.php">← Voltar</a>
    </table>
    <a href="admin.php">← Voltar</a>

    <script>
        const ctx = document.getElementById('graficoMensal').getContext('2d');
        new Chart(ctx, {
            type: 'bar',
            data: {
                labels: <?= json_encode($meses) ?>,
                datasets: [
                    {
                        label: 'Ganhos',
                        data: <?= json_encode(array_map(fn($mes) => $dados_ganhos[$mes] ?? 0, $meses)) ?>,
                        backgroundColor: '#28A745'
                    },
                    {
                        label: 'Gastos',
                        data: <?= json_encode(array_map(fn($mes) => $dados_gastos[$mes] ?? 0, $meses)) ?>,
                        backgroundColor: '#FF5733'
                    }
                ]
            },
            options: {
                responsive: true,
                plugins: {
                    tooltip: {
                        callbacks: {
                            label: function(tooltipItem) {
                                return tooltipItem.dataset.label + ': €' + tooltipItem.raw.toFixed(2);
                            }
                        }
                    }
                }
            }
        });
        
    </script>
</body>
</html>
