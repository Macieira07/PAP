    <?php
    require_once '../conexao.php';

    // Consultar médias gerais e totais
    $sql_dados = "SELECT 
        COUNT(*) AS total_avaliacoes,
        AVG(avaliacao_ambiente) AS ambiente,
        AVG(avaliacao_conforto) AS conforto,
        AVG(avaliacao_limpeza) AS limpeza,
        AVG(avaliacao_localizacao) AS localizacao,
        AVG(avaliacao_comodidades) AS comodidades,
        AVG(classificacao_experiencia) AS experiencia,
        SUM(CASE WHEN recomendaria = 'Sim' THEN 1 ELSE 0 END) AS total_recomendaria,
        SUM(CASE WHEN voltaria_reservar = 'Sim' THEN 1 ELSE 0 END) AS total_voltaria,
        SUM(CASE WHEN sentiu_bem_recebido = 'Sim' THEN 1 ELSE 0 END) AS total_bem_recebido,
        SUM(CASE WHEN correspondeu_expectativas = 'Sim' THEN 1 ELSE 0 END) AS total_expectativas
    FROM formulario_quinta_flores";

    $resultado_dados = $conexao->query($sql_dados);
    $dados = $resultado_dados->fetch_assoc();

    // Calcular percentuais
    $percent_recomendaria = $dados['total_avaliacoes'] > 0 ? round(($dados['total_recomendaria']) / $dados['total_avaliacoes']) * 100 : 0;
    $percent_voltaria = $dados['total_avaliacoes'] > 0 ? round(($dados['total_voltaria']) / $dados['total_avaliacoes']) * 100 : 0;
    $percent_bem_recebido = $dados['total_avaliacoes'] > 0 ? round(($dados['total_bem_recebido']) / $dados['total_avaliacoes']) * 100 : 0;
    $percent_expectativas = $dados['total_avaliacoes'] > 0 ? round(($dados['total_expectativas']) / $dados['total_avaliacoes']) * 100 : 0;

    // Comentários para análise qualitativa
    $sql_comentarios = "SELECT 
        aspetos_a_melhorar, 
        comentarios_adicionais,
        classificacao_experiencia,
        carimbo_data_hora
    FROM formulario_quinta_flores
    WHERE (aspetos_a_melhorar IS NOT NULL AND aspetos_a_melhorar != '') 
    OR (comentarios_adicionais IS NOT NULL AND comentarios_adicionais != '')
    ORDER BY carimbo_data_hora DESC
    LIMIT 20";

    $resultado_comentarios = $conexao->query($sql_comentarios);
    $comentarios = [];

    while ($linha = $resultado_comentarios->fetch_assoc()) {
        $comentarios[] = $linha;
    }

    // Para a tabela completa dos formulários
    $sql_formularios = "SELECT 
        id,
        carimbo_data_hora,
        nome_completo,
        classificacao_experiencia,
        gostou_estadia,
        recomendaria,
        voltaria_reservar,
        aspetos_a_melhorar
    FROM formulario_quinta_flores 
    ORDER BY carimbo_data_hora DESC 
    LIMIT 50";

    $resultado_formularios = $conexao->query($sql_formularios);
    ?>

    <!DOCTYPE html>
    <html lang="pt">
    <head>
        <link rel="stylesheet" href="admin.css">
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>Dashboard de Avaliações - Quinta Flores</title>
        <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
        <script src="https://cdn.jsdelivr.net/npm/chartjs-plugin-datalabels@2.0.0"></script>
        <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
        <style>
            .dashboard-container {
                display: grid;
                grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
                gap: 20px;
                margin-bottom: 30px;
            }
            
            .metric-card {
                background: white;
                border-radius: 5px;
                padding: 20px;
                box-shadow: 0 4px 6px rgba(0,0,0,0.05);
                border-left: 4px solid #4e73df;
            }
            
            .metric-card h3 {
                margin-top: 0;
                color: #5a6c7d;
                font-size: 16px;
            }
            
            .metric-value {
                font-size: 28px;
                font-weight: 700;
                margin: 10px 0;
                color: #2c3e50;
            }
            
            .metric-detail {
                color: #7a8a9b;
                font-size: 14px;
                display: flex;
                align-items: center;
            }
            
            .comments-container {
                display: grid;
                grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
                gap: 20px;
                margin-bottom: 30px;
            }
            
            .comment-card {
                background: white;
                border-radius: 10px;
                padding: 20px;
                box-shadow: 0 4px 6px rgba(0,0,0,0.05);
                border-left: 4px solid #f39c12;
            }
            
            .comment-header {
                display: flex;
                justify-content: space-between;
                margin-bottom: 10px;
                font-size: 14px;
                color: #5a6c7d;
            }
            
            .comment-rating {
                color: #f39c12;
                font-weight: bold;
            }
            
            .comment-text {
                color: #2c3e50;
                line-height: 1.5;
                font-style: italic;
            }
            
            .badge {
                display: inline-block;
                padding: 3px 8px;
                border-radius: 10px;
                font-size: 12px;
                font-weight: 600;
            }
            
            .badge-warning {
                background: #fef3c7;
                color: #92400e;
            }
            
            .badge-success {
                background: #d1fae5;
                color: #166534;
            }
            
            .table-responsive {
                overflow-x: auto;
            }
            
            #tabelaAvaliacoes {
                width: 100%;
                border-collapse: collapse;
            }
            
            #tabelaAvaliacoes th, #tabelaAvaliacoes td {
                padding: 12px 15px;
                text-align: left;
                border-bottom: 1px solid #ddd;
            }
            
            #tabelaAvaliacoes th {
                background-color: #f8f9fa;
                font-weight: 600;
            }
            
            #tabelaAvaliacoes tr:hover {
                background-color: #f5f5f5;
            }
            
            .star-rating {
                color: #f39c12;
            }
            
            .filter-container {
                background: white;
                padding: 15px;
                border-radius: 10px;
                margin-bottom: 20px;
                box-shadow: 0 4px 6px rgba(0,0,0,0.05);
            }
            
            .filter-row {
                display: flex;
                gap: 15px;
                flex-wrap: wrap;
            }
            
            .filter-group {
                flex: 1;
                min-width: 200px;
            }
            
            .filter-group label {
                display: block;
                margin-bottom: 5px;
                font-weight: 500;
                color: #5a6c7d;
            }
            
            .filter-group select, 
            .filter-group input {
                width: 100%;
                padding: 8px 12px;
                border: 1px solid #ddd;
                border-radius: 4px;
            }
            
            .btn-filter {
                background: #4e73df;
                color: white;
                border: none;
                padding: 8px 16px;
                border-radius: 4px;
                cursor: pointer;
                align-self: flex-end;
            }
            
            .btn-filter:hover {
                background: #2e59d9;
            }
            
            .section-title {
                color: #2c3e50;
                border-bottom: 1px solid #eee;
                padding-bottom: 10px;
                margin-bottom: 20px;
                font-size: 20px;
            }
            
            .tab-container {
                margin-bottom: 20px;
            }
            
            .tab-buttons {
                display: flex;
                border-bottom: 1px solid #ddd;
            }
            
            .tab-button {
                padding: 10px 20px;
                background: none;
                border: none;
                cursor: pointer;
                border-bottom: 3px solid transparent;
                font-weight: 500;
                color: #5a6c7d;
            }
            
            .tab-button.active {
                border-bottom-color: #4e73df;
                color: #4e73df;
            }
            
            .tab-content {
                display: none;
                padding: 20px 0;
            }
            
            .tab-content.active {
                display: block;
            }
            
            .chart-container {
                background: white;
                border-radius: 10px;
                padding: 20px;
                margin-bottom: 30px;
                box-shadow: 0 4px 6px rgba(0,0,0,0.05);
            }
            
            .chart-title {
                margin-top: 0;
                margin-bottom: 20px;
                color: #2c3e50;
                font-size: 18px;
            }
            
            .satisfaction-grid {
                display: grid;
                grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
                gap: 20px;
            }
        </style>
    </head>
    <body>
    <a href="admin.php">← Voltar</a>
    <div class="container">
        <h1><i class="fas fa-chart-line"></i> Dashboard de Avaliações</h1>
        
        <div class="dashboard-container">
            <div class="metric-card">
                <h3>Avaliações Recebidas</h3>
                <div class="metric-value"><?= $dados['total_avaliacoes'] ?></div>
                <div class="metric-detail">
                    <span>Total de feedbacks</span>
                </div>
            </div>
            
            <div class="metric-card">
                <h3>Média Geral</h3>
                <div class="metric-value"><?= round($dados['experiencia'], 1) ?>/5</div>
                <div class="metric-detail">
                    <span>Experiência dos hóspedes</span>
                </div>
            </div>
            
            <div class="metric-card">
                <h3>Recomendariam</h3>
                <div class="metric-value"><?= $percent_recomendaria ?>%</div>
                <div class="metric-detail">
                    <span>Taxa de recomendação</span>
                </div>
            </div>
            
            <div class="metric-card">
                <h3>Voltariam</h3>
                <div class="metric-value"><?= $percent_voltaria ?>%</div>
                <div class="metric-detail">
                    <span>Taxa de retorno</span>
                </div>
            </div>
        </div>
        
        <div class="tab-container">
            <div class="tab-buttons">
                <button class="tab-button active" onclick="openTab('analise')">Análise</button>
                <button class="tab-button" onclick="openTab('comentarios')">Comentários</button>
                <button class="tab-button" onclick="openTab('detalhes')">Detalhes</button>
            </div>
            
            <div id="analise" class="tab-content active">
                <div class="chart-container">
                    <h2 class="chart-title">Médias por Categoria</h2>
                    <canvas id="graficoCategorias" height="300"></canvas>
                </div>
            </div>
            
            <div id="comentarios" class="tab-content">
                <h2 class="section-title">Comentários e Sugestões</h2>
                
                <?php if (!empty($comentarios)): ?>
                    <div class="comments-container">
                        <?php foreach ($comentarios as $comentario): ?>
                            <div class="comment-card">
                                <div class="comment-header">
                                    <span class="comment-rating">
                                        <?= str_repeat('★', $comentario['classificacao_experiencia']) . str_repeat('☆', 5 - $comentario['classificacao_experiencia']) ?>
                                    </span>
                                    <span><?= date('d/m/Y', strtotime($comentario['carimbo_data_hora'])) ?></span>
                                </div>
                                
                                <?php if (!empty($comentario['aspetos_a_melhorar'])): ?>
                                    <div class="comment-text">
                                        <strong><i class="fas fa-exclamation-circle"></i> Aspectos a melhorar:</strong>
                                        <p><?= htmlspecialchars($comentario['aspetos_a_melhorar']) ?></p>
                                    </div>
                                <?php endif; ?>
                                
                                <?php if (!empty($comentario['comentarios_adicionais'])): ?>
                                    <div class="comment-text">
                                        <strong><i class="fas fa-comment"></i> Comentário:</strong>
                                        <p><?= htmlspecialchars($comentario['comentarios_adicionais']) ?></p>
                                    </div>
                                <?php endif; ?>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php else: ?>
                    <p>Nenhum comentário encontrado.</p>
                <?php endif; ?>
            </div>   
                    </div>
                </div>
                
                <div class="table-responsive">
                    <table id="tabelaAvaliacoes">
                        <thead>
                            <tr>
                                <th>Data</th>
                                <th>Hóspede</th>
                                <th>Classificação(0/5)</th>
                                <th>Gostou (0/10)</th>
                                <th>Recomendaria</th>
                                <th>Voltaria</th>
                                <th>Aspectos a Melhorar</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if ($resultado_formularios->num_rows > 0): ?>
                                <?php while ($linha = $resultado_formularios->fetch_assoc()): ?>
                                    <tr>
                                        <td><?= date('d/m/Y', strtotime($linha['carimbo_data_hora'])) ?></td>
                                        <td><?= htmlspecialchars($linha['nome_completo']) ?></td>
                                        <td class="star-rating">
                                            <?= str_repeat('★', $linha['classificacao_experiencia']) . str_repeat('☆', 5 - $linha['classificacao_experiencia']) ?>
                                        </td>
                                        <td><?= htmlspecialchars($linha['gostou_estadia']) ?></td>
                                        <td>
                                            <span class="badge <?= $linha['recomendaria'] == 'Sim' ? 'badge-success' : 'badge-warning' ?>">
                                                <?= htmlspecialchars($linha['recomendaria']) ?>
                                            </span>
                                        </td>
                                        <td><?= htmlspecialchars($linha['voltaria_reservar']) ?></td>
                                        <td><?= htmlspecialchars($linha['aspetos_a_melhorar']) ?></td>
                                    </tr>
                                <?php endwhile; ?>
                            <?php else: ?>
                                <tr>
                                    <td colspan="7">Nenhuma avaliação encontrada</td>
                                </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <script>
        // Função para abrir abas
        function openTab(tabName) {
            const tabContents = document.getElementsByClassName('tab-content');
            for (let i = 0; i < tabContents.length; i++) {
                tabContents[i].classList.remove('active');
            }
            
            const tabButtons = document.getElementsByClassName('tab-button');
            for (let i = 0; i < tabButtons.length; i++) {
                tabButtons[i].classList.remove('active');
            }
            
            document.getElementById(tabName).classList.add('active');
            event.currentTarget.classList.add('active');
        }
        
        // Dados para os gráficos
        const medias = {
            ambiente: <?= round($dados['ambiente'], 2) ?>,
            conforto: <?= round($dados['conforto'], 2) ?>,
            limpeza: <?= round($dados['limpeza'], 2) ?>,
            localizacao: <?= round($dados['localizacao'], 2) ?>,
            comodidades: <?= round($dados['comodidades'], 2) ?>
        };
        
        const satisfacao = {
            bemRecebido: <?= $percent_bem_recebido ?>,
            expectativas: <?= $percent_expectativas ?>,
            recomendaria: <?= $percent_recomendaria ?>
        };
        
        // Cores para os gráficos
        const cores = [
            'rgba(78, 115, 223, 0.8)',
            'rgba(54, 185, 204, 0.8)',
            'rgba(76, 175, 80, 0.8)',
            'rgba(255, 193, 7, 0.8)',
            'rgba(233, 30, 99, 0.8)'
        ];
        
        // Gráfico de categorias
        const ctxCategorias = document.getElementById('graficoCategorias').getContext('2d');
        new Chart(ctxCategorias, {
            type: 'bar',
            data: {
                labels: ['Ambiente', 'Conforto', 'Limpeza', 'Localização', 'Comodidades'],
                datasets: [{
                    label: 'Média de Avaliação',
                    data: [
                        medias.ambiente,
                        medias.conforto,
                        medias.limpeza,
                        medias.localizacao,
                        medias.comodidades
                    ],
                    backgroundColor: cores,
                    borderColor: cores.map(c => c.replace('0.8', '1')),
                    borderWidth: 1
                }]
            },
            options: {
                responsive: true,
                scales: {
                    y: {
                        beginAtZero: true,
                        max: 5,
                        title: {
                            display: true,
                            text: 'Média (1-5)'
                        }
                    }
                },
                plugins: {
                    legend: {
                        display: false
                    },
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
        
        // Gráficos de satisfação
        function criarGraficoSatisfacao(id, titulo, valor) {
            const ctx = document.getElementById(id).getContext('2d');
            return new Chart(ctx, {
                type: 'doughnut',
                data: {
                    labels: ['Sim', 'Não'],
                    datasets: [{
                        data: [valor, 100 - valor],
                        backgroundColor: [
                            'rgba(40, 167, 69, 0.8)',
                            'rgba(220, 53, 69, 0.8)'
                        ],
                        borderColor: [
                            'rgba(40, 167, 69, 1)',
                            'rgba(220, 53, 69, 1)'
                        ],
                        borderWidth: 1
                    }]
                },
                options: {
                    responsive: true,
                    plugins: {
                        legend: {
                            position: 'bottom'
                        },
                        title: {
                            display: false,
                            text: titulo
                        },
                        tooltip: {
                            callbacks: {
                                label: function(context) {
                                    return context.label + ': ' + context.raw + '%';
                                }
                            }
                        }
                    },
                    cutout: '70%'
                }
            });
        }
        
        criarGraficoSatisfacao('graficoRecepcao', 'Sentiu-se bem recebido', satisfacao.bemRecebido);
        criarGraficoSatisfacao('graficoExpectativas', 'Correspondeu às expectativas', satisfacao.expectativas);
        criarGraficoSatisfacao('graficoRecomendacao', 'Recomendaria a outros', satisfacao.recomendaria);
        
        // Função para filtrar a tabela
        function filtrarTabela() {
            const periodo = document.getElementById('filtro-periodo').value;
            const classificacao = document.getElementById('filtro-classificacao').value;
            const recomendacao = document.getElementById('filtro-recomendacao').value;
            
            // Construir a URL com os parâmetros de filtro
            let url = 'listar_avaliacoes.php?';
            
            if (periodo !== '0') {
                url += 'periodo=' + periodo + '&';
            }
            
            if (classificacao !== '0') {
                url += 'classificacao=' + classificacao + '&';
            }
            
            if (recomendacao !== 'todos') {
                url += 'recomendacao=' + recomendacao + '&';
            }
            
            // Remover o último & se existir
            if (url.endsWith('&')) {
                url = url.slice(0, -1);
            }
            
            // Recarregar a página com os filtros aplicados
            window.location.href = url;
        }
        
        // Ordenação da tabela
        const tabela = document.getElementById('tabelaAvaliacoes');
        const ths = tabela.querySelectorAll('th');
        let ordemAtual = {indice: -1, crescente: true};
        
        ths.forEach((th, idx) => {
            th.addEventListener('click', () => {
                const tipo = idx === 2 ? 'num' : 'string'; // A coluna de classificação é numérica
                ordenarTabela(idx, tipo);
            });
        });
        
        function ordenarTabela(colIdx, tipo) {
            const tbody = tabela.querySelector('tbody');
            const linhas = Array.from(tbody.querySelectorAll('tr'));
            
            const crescente = ordemAtual.indice === colIdx ? !ordemAtual.crescente : true;
            ordemAtual = {indice: colIdx, crescente: crescente};
            
            linhas.sort((a, b) => {
                let valA = a.children[colIdx].textContent.trim();
                let valB = b.children[colIdx].textContent.trim();
                
                if (tipo === 'num') {
                    // Para classificação por estrelas
                    valA = (a.children[colIdx].querySelectorAll('★').length || 0);
                    valB = (b.children[colIdx].querySelectorAll('★').length || 0);
                }
                
                if (valA < valB) return crescente ? -1 : 1;
                if (valA > valB) return crescente ? 1 : -1;
                return 0;
            });
            
            linhas.forEach(linha => tbody.appendChild(linha));
        }
    </script>
    <a href="admin.php">← Voltar</a>
    </body>
    </html>

    <?php $conexao->close(); ?>