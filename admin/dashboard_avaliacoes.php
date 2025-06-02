<?php
require_once '../conexao.php';
require_once '../vendor/autoload.php'; // Para gráficos (usaremos Chart.js)

// Consulta para obter dados para os gráficos
$query_geral = "SELECT experiencia_geral, COUNT(*) as total FROM avaliacoes_import GROUP BY experiencia_geral";
$result_geral = $conexao->query($query_geral);

$query_categorias = "SELECT 
    AVG(avalia_ambiente) as ambiente,
    AVG(avalia_conforto) as conforto,
    AVG(avalia_limpeza) as limpeza,
    AVG(avalia_localizacao) as localizacao,
    AVG(avalia_comodidades) as comodidades
FROM avaliacoes_import";
$result_categorias = $conexao->query($query_categorias);
$categorias = $result_categorias->fetch_assoc();

// Consulta para listagem das avaliações
$query_avaliacoes = "SELECT * FROM avaliacoes_import ORDER BY data_avaliacao DESC";
$result_avaliacoes = $conexao->query($query_avaliacoes);
?>

<!DOCTYPE html>
<html lang="pt">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard de Avaliações - Quinta Flores</title>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <style>
        body { font-family: Arial, sans-serif; margin: 0; padding: 20px; }
        .chart-container { width: 80%; margin: 20px auto; }
        .chart-box { background: white; border-radius: 8px; box-shadow: 0 2px 10px rgba(0,0,0,0.1); padding: 20px; margin-bottom: 30px; }
        table { width: 100%; border-collapse: collapse; margin-top: 20px; }
        th, td { padding: 12px; text-align: left; border-bottom: 1px solid #ddd; }
        th { background-color: #f2f2f2; }
        tr:hover { background-color: #f5f5f5; }
        .sync-btn { 
            background: #4285F4; color: white; border: none; padding: 10px 15px; 
            border-radius: 4px; cursor: pointer; font-size: 16px; margin-bottom: 20px;
        }
        .sync-btn:hover { background: #3367D6; }
    </style>
</head>
<body>
    <h1>Dashboard de Avaliações - Quinta Flores</h1>
    
    <button class="sync-btn" onclick="window.location.href='sync_avaliacoes.php'">
        Sincronizar com Google Forms
    </button>

    <div class="chart-box">
        <h2>Experiência Geral</h2>
        <div class="chart-container">
            <canvas id="chartGeral"></canvas>
        </div>
    </div>

    <div class="chart-box">
        <h2>Avaliação por Categorias</h2>
        <div class="chart-container">
            <canvas id="chartCategorias"></canvas>
        </div>
    </div>

    <h2>Últimas Avaliações</h2>
    <table>
        <thead>
            <tr>
                <th>Data</th>
                <th>Nome</th>
                <th>Experiência</th>
                <th>Recomenda?</th>
                <th>Comentários</th>
            </tr>
        </thead>
        <tbody>
            <?php while($row = $result_avaliacoes->fetch_assoc()): ?>
            <tr>
                <td><?= date('d/m/Y', strtotime($row['data_avaliacao'])) ?></td>
                <td><?= htmlspecialchars($row['nome_completo']) ?></td>
                <td><?= $row['experiencia_geral'] ?>/5</td>
                <td><?= $row['recomendaria'] ?></td>
                <td><?= htmlspecialchars(substr($row['comentarios'] ?? '', 0, 50)) . (strlen($row['comentarios'] ?? '') > 50 ? '...' : '') ?></td>
            </tr>
            <?php endwhile; ?>
        </tbody>
    </table>

    <script>
        // Gráfico de Experiência Geral
        const ctxGeral = document.getElementById('chartGeral').getContext('2d');
        const chartGeral = new Chart(ctxGeral, {
            type: 'pie',
            data: {
                labels: ['1 Estrela', '2 Estrelas', '3 Estrelas', '4 Estrelas', '5 Estrelas'],
                datasets: [{
                    data: [
                        <?php 
                        $data = ['1' => 0, '2' => 0, '3' => 0, '4' => 0, '5' => 0];
                        while($row = $result_geral->fetch_assoc()) {
                            $data[$row['experiencia_geral']] = $row['total'];
                        }
                        echo implode(', ', $data);
                        ?>
                    ],
                    backgroundColor: [
                        '#FF6384',
                        '#36A2EB',
                        '#FFCE56',
                        '#4BC0C0',
                        '#9966FF'
                    ]
                }]
            },
            options: {
                responsive: true,
                plugins: {
                    legend: { position: 'top' },
                    tooltip: {
                        callbacks: {
                            label: function(context) {
                                const label = context.label || '';
                                const value = context.raw || 0;
                                const total = context.dataset.data.reduce((a, b) => a + b, 0);
                                const percentage = Math.round((value / total) * 100);
                                return `${label}: ${value} (${percentage}%)`;
                            }
                        }
                    }
                }
            }
        });

        // Gráfico de Categorias
        const ctxCategorias = document.getElementById('chartCategorias').getContext('2d');
        const chartCategorias = new Chart(ctxCategorias, {
            type: 'radar',
            data: {
                labels: ['Ambiente', 'Conforto', 'Limpeza', 'Localização', 'Comodidades'],
                datasets: [{
                    label: 'Média de Avaliação',
                    data: [
                        <?= round($categorias['ambiente'], 2) ?>,
                        <?= round($categorias['conforto'], 2) ?>,
                        <?= round($categorias['limpeza'], 2) ?>,
                        <?= round($categorias['localizacao'], 2) ?>,
                        <?= round($categorias['comodidades'], 2) ?>
                    ],
                    backgroundColor: 'rgba(75, 192, 192, 0.2)',
                    borderColor: 'rgba(75, 192, 192, 1)',
                    borderWidth: 2,
                    pointBackgroundColor: 'rgba(75, 192, 192, 1)'
                }]
            },
            options: {
                responsive: true,
                scales: {
                    r: {
                        angleLines: { display: true },
                        suggestedMin: 0,
                        suggestedMax: 5
                    }
                }
            }
        });
    </script>
</body>
</html>