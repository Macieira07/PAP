<?php
require_once '../conexao.php';

// Consultar médias de cada avaliação
$sql_medias = "SELECT 
    AVG(avaliacao_ambiente) AS ambiente,
    AVG(avaliacao_conforto) AS conforto,
    AVG(avaliacao_limpeza) AS limpeza,
    AVG(avaliacao_localizacao) AS localizacao,
    AVG(avaliacao_comodidades) AS comodidades
FROM formulario_quinta_flores";
$resultado_medias = $conexao->query($sql_medias);
$medias = $resultado_medias->fetch_assoc();

// Para a tabela completa dos formulários
$sql_formularios = "SELECT * FROM formulario_quinta_flores ORDER BY carimbo_data_hora DESC";
$resultado_formularios = $conexao->query($sql_formularios);
?>

<!DOCTYPE html>
<html lang="pt">
<head>
        <link rel="stylesheet" href="admin.css">

    <meta charset="UTF-8">
    <title>Formulários - Quinta Flores</title>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
</head>
<body>

<h2>Formulários Recebidos - Quinta Flores</h2>

<?php if ($resultado_formularios->num_rows > 0): ?>
<table>
    <thead>
        <tr>
            <th>ID</th>
            <th>Data e Hora</th>
            <th>Nome Completo</th>
            <th>Email</th>
            <th>Data Estadia</th>
            <th>Classificação Experiência</th>
            <th>Gostou da Estadia</th>
            <th>Ambiente</th>
            <th>Conforto</th>
            <th>Limpeza</th>
            <th>Localização</th>
            <th>Comodidades</th>
            <th>Sentiu-se Bem Recebido</th>
            <th>Correspondeu às Expectativas</th>
            <th>Aspectos a Melhorar</th>
            <th>Recomendaria</th>
            <th>Voltaria a Reservar</th>
            <th>Comentários Adicionais</th>
        </tr>
    </thead>
    <tbody>
    <?php while ($linha = $resultado_formularios->fetch_assoc()): ?>
        <tr>
            <td><?= htmlspecialchars($linha['id']) ?></td>
            <td><?= htmlspecialchars($linha['carimbo_data_hora']) ?></td>
            <td><?= htmlspecialchars($linha['nome_completo']) ?></td>
            <td><?= htmlspecialchars($linha['email']) ?></td>
            <td><?= htmlspecialchars($linha['data_estadia']) ?></td>
            <td><?= htmlspecialchars($linha['classificacao_experiencia']) ?></td>
            <td><?= htmlspecialchars($linha['gostou_estadia']) ?></td>
            <td><?= htmlspecialchars($linha['avaliacao_ambiente']) ?></td>
            <td><?= htmlspecialchars($linha['avaliacao_conforto']) ?></td>
            <td><?= htmlspecialchars($linha['avaliacao_limpeza']) ?></td>
            <td><?= htmlspecialchars($linha['avaliacao_localizacao']) ?></td>
            <td><?= htmlspecialchars($linha['avaliacao_comodidades']) ?></td>
            <td><?= htmlspecialchars($linha['sentiu_bem_recebido']) ?></td>
            <td><?= htmlspecialchars($linha['correspondeu_expectativas']) ?></td>
            <td><?= htmlspecialchars($linha['aspetos_a_melhorar']) ?></td>
            <td><?= htmlspecialchars($linha['recomendaria']) ?></td>
            <td><?= htmlspecialchars($linha['voltaria_reservar']) ?></td>
            <td><?= htmlspecialchars($linha['comentarios_adicionais']) ?></td>
        </tr>
    <?php endwhile; ?>
    </tbody>
</table>
<?php else: ?>
<p>Não foram encontrados formulários submetidos.</p>
<?php endif; ?>

<h2>Médias das Avaliações</h2>

<!-- Botões para escolher gráfico -->
<div id="botoesGraficos" style="margin-bottom: 15px;">
    <button onclick="mostrarGrafico('ambiente')">Ambiente</button>
    <button onclick="mostrarGrafico('conforto')">Conforto</button>
    <button onclick="mostrarGrafico('limpeza')">Limpeza</button>
    <button onclick="mostrarGrafico('localizacao')">Localização</button>
    <button onclick="mostrarGrafico('comodidades')">Comodidades</button>
</div>

<!-- Containers para cada gráfico, inicialmente escondidos -->
<div>
    <canvas id="graficoAmbiente" width="250" height="150" style="display:none;"></canvas>
    <canvas id="graficoConforto" width="250" height="150" style="display:none;"></canvas>
    <canvas id="graficoLimpeza" width="250" height="150" style="display:none;"></canvas>
    <canvas id="graficoLocalizacao" width="250" height="150" style="display:none;"></canvas>
    <canvas id="graficoComodidades" width="250" height="150" style="display:none;"></canvas>
</div>

<script>
    const medias = <?= json_encode($medias) ?>;

    // Armazenar as instâncias dos gráficos para evitar recriar várias vezes
    const graficos = {};

    function criarGrafico(idCanvas, label, valor) {
        const ctx = document.getElementById(idCanvas).getContext('2d');
        return new Chart(ctx, {
            type: 'bar',
            data: {
                labels: [label],
                datasets: [{
                    label: 'Média',
                    data: [parseFloat(valor).toFixed(2)],
                    backgroundColor: '#4e73df',
                    borderColor: '#2e59d9',
                    borderWidth: 1
                }]
            },
            options: {
                responsive: false,
                scales: {
                    y: {
                        beginAtZero: true,
                        max: 5
                    }
                },
                plugins: {
                    legend: { display: false },
                    tooltip: {
                        callbacks: {
                            label: function(context) {
                                return context.parsed.y.toFixed(2);
                            }
                        }
                    }
                }
            }
        });
    }

    // Função para mostrar só o gráfico selecionado
    function mostrarGrafico(tipo) {
        const canvasIds = {
            ambiente: 'graficoAmbiente',
            conforto: 'graficoConforto',
            limpeza: 'graficoLimpeza',
            localizacao: 'graficoLocalizacao',
            comodidades: 'graficoComodidades'
        };

        // Esconder todos os canvas
        for (const id of Object.values(canvasIds)) {
            document.getElementById(id).style.display = 'none';
        }

        // Mostrar o canvas selecionado
        const canvasId = canvasIds[tipo];
        const canvas = document.getElementById(canvasId);
        canvas.style.display = 'block';

        // Criar gráfico se não existir
        if (!graficos[canvasId]) {
            graficos[canvasId] = criarGrafico(canvasId, tipo.charAt(0).toUpperCase() + tipo.slice(1), medias[tipo]);
        }
    }

    // Nenhum gráfico mostrado no início
</script>

</body>
</html>

<?php $conexao->close(); ?>
