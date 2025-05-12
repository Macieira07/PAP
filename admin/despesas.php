<?php
require '../conexao.php';

// Verificar se a tabela despesas tem os campos necessários
$result = $conexao->query("SHOW COLUMNS FROM despesas LIKE 'D_nome'");
if ($result->num_rows == 0) {
    // Adicionar campo se não existir
    $conexao->query("ALTER TABLE despesas ADD COLUMN D_nome VARCHAR(255) AFTER id");
}

// Inicialização das variáveis de filtro
$filtro_data_inicio = isset($_GET['data_inicio']) ? $_GET['data_inicio'] : '';
$filtro_data_fim = isset($_GET['data_fim']) ? $_GET['data_fim'] : '';
$filtro_categoria = isset($_GET['categoria']) ? $_GET['categoria'] : '';
$filtro_valor_min = isset($_GET['valor_min']) ? $_GET['valor_min'] : '';
$filtro_valor_max = isset($_GET['valor_max']) ? $_GET['valor_max'] : '';
$pesquisa = isset($_GET['pesquisa']) ? $_GET['pesquisa'] : '';
$ordenar_por = isset($_GET['ordenar_por']) ? $_GET['ordenar_por'] : 'D_data';
$ordem = isset($_GET['ordem']) ? $_GET['ordem'] : 'DESC';

// Obter saldo atual da conta
$saldoAtual = $conexao->query("SELECT saldo FROM conta_virtual WHERE id = 1")->fetch_assoc()['saldo'] ?? 0;

// Construção da query com filtros
$query = "SELECT * FROM despesas WHERE 1=1";

if (!empty($filtro_data_inicio)) {
    $query .= " AND DATE(D_data) >= '$filtro_data_inicio'";
}
if (!empty($filtro_data_fim)) {
    $query .= " AND DATE(D_data) <= '$filtro_data_fim'";
}
if (!empty($filtro_categoria)) {
    $query .= " AND D_categoria = '$filtro_categoria'";
}
if (!empty($filtro_valor_min)) {
    $query .= " AND D_valor >= $filtro_valor_min";
}
if (!empty($filtro_valor_max)) {
    $query .= " AND D_valor <= $filtro_valor_max";
}
if (!empty($pesquisa)) {
    $query .= " AND (D_fornecedor LIKE '%$pesquisa%' OR D_descricao LIKE '%$pesquisa%')";
}

// Adicionar ordenação
$query .= " ORDER BY $ordenar_por $ordem";

// Executar a consulta
$resultado = $conexao->query($query);

// Obter categorias para o filtro
$categorias = $conexao->query("SELECT DISTINCT D_categoria FROM despesas ORDER BY D_categoria");

// Calcular totais por categoria
$totalPorCategoria = [];
$totalQuery = "SELECT D_categoria, SUM(D_valor) as total FROM despesas WHERE 1=1";

if (!empty($filtro_data_inicio)) {
    $totalQuery .= " AND DATE(D_data) >= '$filtro_data_inicio'";
}
if (!empty($filtro_data_fim)) {
    $totalQuery .= " AND DATE(D_data) <= '$filtro_data_fim'";
}
if (!empty($pesquisa)) {
    $totalQuery .= " AND (D_fornecedor LIKE '%$pesquisa%' OR D_descricao LIKE '%$pesquisa%')";
}
$totalQuery .= " GROUP BY D_categoria";

$resultadoTotais = $conexao->query($totalQuery);
while ($row = $resultadoTotais->fetch_assoc()) {
    $totalPorCategoria[$row['D_categoria']] = $row['total'];
}

// Total de despesas filtradas
$totalDespesasFiltradas = array_sum($totalPorCategoria);

// Total geral de todas as despesas (sem filtros)
$totalDespesas = $conexao->query("SELECT SUM(D_valor) AS total FROM despesas")->fetch_assoc()['total'] ?? 0;

// Total a pagar em manutenções (não pagas)
$totalManutencoes = $conexao->query("SELECT SUM(M_custo) AS total FROM manutencao WHERE M_pago = 0")->fetch_assoc()['total'] ?? 0;

// Total estimado de salários
$totalFuncionarios = $conexao->query("SELECT COUNT(*) AS total FROM funcionarios")->fetch_assoc()['total'] ?? 0;
$totalSalarios = 1000 * $totalFuncionarios; // Valor base por funcionário

// Total de receitas (dinheiro recebido)
$totalReceitas = $conexao->query("SELECT SUM(R_valor) AS total FROM receitas")->fetch_assoc()['total'] ?? 0;

// Soma geral de despesas
$totalGeralDespesas = $totalDespesas + $totalManutencoes + $totalSalarios;

// Buscar despesas futuras/planejadas
$despesasFuturas = $conexao->query("SELECT * FROM despesas WHERE DATE(D_data) > CURDATE() ORDER BY D_data ASC");

// Buscar despesas não pagas
$despesasNaoPagas = $conexao->query("SELECT * FROM despesas WHERE D_pago = 0 AND DATE(D_data) <= CURDATE() ORDER BY D_data ASC");
$totalNaoPago = $conexao->query("SELECT SUM(D_valor) AS total FROM despesas WHERE D_pago = 0 AND DATE(D_data) <= CURDATE()")->fetch_assoc()['total'] ?? 0;

// Buscar manutenções não pagas
$manutencoesNaoPagas = $conexao->query("SELECT * FROM manutencao WHERE M_pago = 0 ORDER BY M_data_inicio ASC");
$totalManutencoesNaoPagas = $conexao->query("SELECT SUM(M_custo) AS total FROM manutencao WHERE M_pago = 0")->fetch_assoc()['total'] ?? 0;

// Buscar receitas (dinheiro recebido)
$receitas = $conexao->query("SELECT * FROM receitas ORDER BY R_data DESC LIMIT 10");

// Buscar últimas movimentações
$movimentacoes = $conexao->query("SELECT * FROM movimentacoes ORDER BY data DESC LIMIT 10");

// Dados para gráfico de evolução mensal
$meses = [];
$valoresMensais = [];
for ($i = 5; $i >= 0; $i--) {
    $mes = date('Y-m', strtotime("-$i months"));
    $mesNome = date('M/Y', strtotime("-$i months"));
    $meses[] = $mesNome;
    
    $inicio = $mes . '-01';
    $fim = date('Y-m-t', strtotime($inicio));
    
    $totalMes = $conexao->query("SELECT SUM(D_valor) AS total FROM despesas WHERE DATE(D_data) BETWEEN '$inicio' AND '$fim'")->fetch_assoc()['total'] ?? 0;
    $valoresMensais[] = $totalMes;
}
?>

<!DOCTYPE html>
<html lang="pt">
<head>
    <link rel="stylesheet" href="admin.css">
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gestão de Despesas</title>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <style>
        .dashboard {
            display: flex;
            flex-wrap: wrap;
            gap: 20px;
            margin-bottom: 30px;
        }
        .card {
            flex: 1;
            min-width: 200px;
            padding: 15px;
            border-radius: 8px;
            box-shadow: 0 0 10px rgba(0,0,0,0.1);
        }
        .card.success { background-color: #d4edda; }
        .card.warning { background-color: #fff3cd; }
        .card.danger { background-color: #f8d7da; }
        .card.info { background-color: #d1ecf1; }
        
        .card h3 {
            margin-top: 0;
            color: #333;
        }
        
        .card p {
            font-size: 1.5em;
            font-weight: bold;
            margin: 10px 0 0;
        }
        
        .filtros {
            background-color: #f8f9fa;
            padding: 15px;
            border-radius: 8px;
            margin-bottom: 20px;
        }
        
        .filtros form {
            display: flex;
            flex-wrap: wrap;
            gap: 10px;
            align-items: flex-end;
        }
        
        .filtros .grupo {
            flex: 1;
            min-width: 200px;
        }
        
        .filtros label {
            display: block;
            margin-bottom: 5px;
            font-weight: bold;
        }
        
        .filtros input, .filtros select {
            width: 100%;
            padding: 8px;
            border: 1px solid #ced4da;
            border-radius: 4px;
        }
        
        .filtros button {
            padding: 8px 16px;
            background-color: #007bff;
            color: white;
            border: none;
            border-radius: 4px;
            cursor: pointer;
        }
        
        .filtros button:hover {
            background-color: #0069d9;
        }
        
        .acoes {
            margin-bottom: 20px;
        }
        
        .acoes a {
            margin-right: 10px;
        }
        
        table {
            width: 100%;
            border-collapse: collapse;
        }
        
        table th, table td {
            padding: 12px;
            text-align: left;
            border-bottom: 1px solid #ddd;
        }
        
        table th {
            background-color: #f2f2f2;
        }
        
        table tr:nth-child(even) {
            background-color: #f8f9fa;
        }
        
        table tr:hover {
            background-color: #e9ecef;
        }
        
        .grafico-container {
            display: flex;
            gap: 20px;
            margin-top: 30px;
        }
        
        .grafico {
            flex: 1;
            min-width: 300px;
            box-shadow: 0 0 10px rgba(0,0,0,0.1);
            padding: 15px;
            border-radius: 8px;
        }

        .alerta {
            background-color: #f8d7da;
            color: #721c24;
            padding: 10px;
            border-radius: 4px;
            margin-bottom: 15px;
        }

        .badge {
            padding: 4px 8px;
            border-radius: 4px;
            font-size: 12px;
            font-weight: bold;
        }
        
        .badge-success { background-color: #d4edda; color: #155724; }
        .badge-warning { background-color: #fff3cd; color: #856404; }
        .badge-danger { background-color: #f8d7da; color: #721c24; }

        .saldo {
            font-size: 1.8em;
            font-weight: bold;
            margin: 20px 0;
            padding: 15px;
            border-radius: 8px;
            text-align: center;
        }
        
        .saldo.positivo {
            background-color: #d4edda;
            color: #155724;
        }
        
        .saldo.negativo {
            background-color: #f8d7da;
            color: #721c24;
        }
        
        .saldo.neutro {
            background-color: #e2e3e5;
            color: #383d41;
        }
        
        .erro {
            background-color: #f8d7da;
            color: #721c24;
            padding: 10px;
            border-radius: 4px;
            margin-bottom: 15px;
        }
    </style>
</head>
<body>
<div style="display: flex; align-items: center; justify-content: space-between;">
    <div style="display: flex; align-items: center; gap: 10px;">
        <img src="https://img.icons8.com/?size=100&id=2975&format=png&color=000000" alt="Ícone Despesas" style="height: 50px;">
        <h1>Gestão de Despesas</h1>
    </div>
    <div>
        <a href="?exportar=excel" class="btn btn-success">Exportar Excel</a>
        <a href="?exportar=pdf" class="btn btn-danger">Exportar PDF</a>
    </div>
</div>
<div class="saldo <?= $saldoAtual > 0 ? 'positivo' : ($saldoAtual < 0 ? 'negativo' : 'neutro') ?>">
    Saldo Disponível: <?= number_format($saldoAtual, 2, ',', '.') ?>€
</div
<?php if (!empty($mensagemErro)): ?>
<div class="erro">
    <?= $mensagemErro ?>
</div>
<?php endif; ?>

<div class="dashboard">
    <div class="card info">
        <h3>Total de Despesas</h3>
        <p><?= number_format($totalDespesas, 2, ',', '.') ?>€</p>
    </div>
    <div class="card success">
        <h3>Receitas</h3>
        <p><?= number_format($totalReceitas, 2, ',', '.') ?>€</p>
    </div>
    <div class="card warning">
        <h3>Despesas Pendentes</h3>
        <p><?= number_format($totalNaoPago + $totalManutencoesNaoPagas, 2, ',', '.') ?>€</p>
    </div>
    <div class="card danger">
        <h3>Salários Mensais</h3>
        <p><?= number_format($totalSalarios, 2, ',', '.') ?>€</p>
    </div>
</div>

<div class="acoes">
    <a href="admin.php">← Voltar</a> | 
    <a href="adicionar_despesa.php">+ Adicionar Despesa</a> |
    <a href="relatorios_despesas.php">Ver Relatórios</a>
</div>

<!-- Dashboard com cards de resumo -->
<div class="dashboard">
    <div class="card info">
        <h3>Total de Despesas</h3>
        <p><?= number_format($totalDespesas, 2, ',', '.') ?>€</p>
    </div>
    <div class="card success">
        <h3>Despesas do Mês</h3>
        <p><?= number_format($conexao->query("SELECT SUM(D_valor) AS total FROM despesas WHERE MONTH(D_data) = MONTH(CURRENT_DATE()) AND YEAR(D_data) = YEAR(CURRENT_DATE())")->fetch_assoc()['total'] ?? 0, 2, ',', '.') ?>€</p>
    </div>
    <div class="card warning">
        <h3>Despesas Futuras</h3>
        <p><?= number_format($conexao->query("SELECT SUM(D_valor) AS total FROM despesas WHERE D_data > CURRENT_DATE()")->fetch_assoc()['total'] ?? 0, 2, ',', '.') ?>€</p>
    </div>
    <div class="card danger">
        <h3>Não Pagas</h3>
        <p><?= number_format($totalNaoPago, 2, ',', '.') ?>€</p>
    </div>
</div>

<!-- Filtros -->
<div class="filtros">
    <form method="GET" action="">
        <div class="grupo">
            <label for="data_inicio">Data Início:</label>
            <input type="date" id="data_inicio" name="data_inicio" value="<?= $filtro_data_inicio ?>">
        </div>
        <div class="grupo">
            <label for="data_fim">Data Fim:</label>
            <input type="date" id="data_fim" name="data_fim" value="<?= $filtro_data_fim ?>">
        </div>
        <div class="grupo">
            <label for="categoria">Categoria:</label>
            <select id="categoria" name="categoria">
                <option value="">Todas</option>
                <?php while ($cat = $categorias->fetch_assoc()): ?>
                    <option value="<?= $cat['D_categoria'] ?>" <?= ($filtro_categoria == $cat['D_categoria']) ? 'selected' : '' ?>>
                        <?= $cat['D_categoria'] ?>
                    </option>
                <?php endwhile; ?>
            </select>
        </div>
        <div class="grupo">
            <label for="valor_min">Valor Mínimo:</label>
            <input type="number" id="valor_min" name="valor_min" step="0.01" value="<?= $filtro_valor_min ?>">
        </div>
        <div class="grupo">
            <label for="valor_max">Valor Máximo:</label>
            <input type="number" id="valor_max" name="valor_max" step="0.01" value="<?= $filtro_valor_max ?>">
        </div>
        <div class="grupo">
            <label for="pesquisa">Pesquisar:</label>
            <input type="text" id="pesquisa" name="pesquisa" placeholder="Nome, descrição ou fornecedor" value="<?= $pesquisa ?>">
        </div>
        <div class="grupo">
            <label for="ordenar_por">Ordenar por:</label>
            <select id="ordenar_por" name="ordenar_por">
                <option value="D_data" <?= ($ordenar_por == 'D_data') ? 'selected' : '' ?>>Data</option>
                <option value="D_valor" <?= ($ordenar_por == 'D_valor') ? 'selected' : '' ?>>Valor</option>
                <option value="D_nome" <?= ($ordenar_por == 'D_nome') ? 'selected' : '' ?>>Nome</option>
                <option value="D_categoria" <?= ($ordenar_por == 'D_categoria') ? 'selected' : '' ?>>Categoria</option>
            </select>
        </div>
        <div class="grupo">
            <label for="ordem">Ordem:</label>
            <select id="ordem" name="ordem">
                <option value="ASC" <?= ($ordem == 'ASC') ? 'selected' : '' ?>>Crescente</option>
                <option value="DESC" <?= ($ordem == 'DESC') ? 'selected' : '' ?>>Decrescente</option>
            </select>
        </div>
        <div class="grupo" style="display: flex; gap: 10px;">
            <button type="submit">Filtrar</button>
            <button type="button" onclick="location.href='despesas.php'">Limpar</button>
        </div>
    </form>
</div>

<h2>Manutenções Pendentes</h2>
<a href="adicionar_manutencao.php"><button>Adicionar Manutenção</button></a>
<table border="1" cellpadding="10">
    <tr>
        <th>ID</th>
        <th>Tipo</th>
        <th>Casa</th>
        <th>Valor (€)</th>
        <th>Data Início</th>
        <th>Data Fim</th>
        <th>Status</th>
        <th>Ações</th>
    </tr>
    <?php if ($manutencoesNaoPagas->num_rows > 0): ?>
        <?php while ($manutencao = $manutencoesNaoPagas->fetch_assoc()): ?>
            <tr>
                <td><?= $manutencao['M_id_manutencao'] ?></td>
                <td><?= $manutencao['M_tipo'] ?></td>
                <td><?= $manutencao['M_id_casa'] ?></td>
                <td><?= number_format($manutencao['M_custo'], 2, ',', '.') ?>€</td>
                <td><?= date('d/m/Y', strtotime($manutencao['M_data_inicio'])) ?></td>
                <td><?= $manutencao['M_data_fim'] ? date('d/m/Y', strtotime($manutencao['M_data_fim'])) : 'Em andamento' ?></td>
                <td>
                    <?= $manutencao['M_pago'] ? '<span class="badge badge-success">Pago</span>' : '<span class="badge badge-danger">Pendente</span>' ?>
                </td>
                <td>
                    <?php if (!$manutencao['M_pago']): ?>
                        <a href="pagar_manutencao.php?id=<?= $manutencao['M_id_manutencao'] ?>" onclick="return confirm('Tem certeza que deseja pagar esta manutenção?')">Pagar</a> |
                    <?php endif; ?>
                    <a href="editar_manutencao.php?id=<?= $manutencao['M_id_manutencao'] ?>">Editar</a> |
                    <a href="eliminar_manutencao.php?id=<?= $manutencao['M_id_manutencao'] ?>" onclick="return confirm('Tem certeza que deseja eliminar esta manutenção?')">Eliminar</a>
                </td>
            </tr>
        <?php endwhile; ?>
    <?php else: ?>
        <tr>
            <td colspan="8" style="text-align: center;">Nenhuma manutenção pendente.</td>
        </tr>
    <?php endif; ?>
</table>

<!-- Adicionar seção para Últimas Receitas -->
<h2>Últimas Receitas</h2>
<a href="adicionar_receita.php"><button>Adicionar Receita</button></a>
<table border="1" cellpadding="10">
    <tr>
        <th>ID</th>
        <th>Descrição</th>
        <th>Valor (€)</th>
        <th>Data</th>
        <th>Tipo</th>
        <th>Método</th>
    </tr>
    <?php if ($receitas->num_rows > 0): ?>
        <?php while ($receita = $receitas->fetch_assoc()): ?>
            <tr>
                <td><?= $receita['R_id_receita'] ?></td>
                <td><?= $receita['R_descricao'] ?></td>
                <td><?= number_format($receita['R_valor'], 2, ',', '.') ?>€</td>
                <td><?= date('d/m/Y', strtotime($receita['R_data'])) ?></td>
                <td><?= $receita['R_tipo'] ?></td>
                <td><?= $receita['R_metodo_pagamento'] ?></td>
            </tr>
        <?php endwhile; ?>
    <?php else: ?>
        <tr>
            <td colspan="6" style="text-align: center;">Nenhuma receita registrada.</td>
        </tr>
    <?php endif; ?>
</table>

<!-- Adicionar seção para Últimas Movimentações -->
<h2>Últimas Movimentações</h2>
<table border="1" cellpadding="10">
    <tr>
        <th>Data/Hora</th>
        <th>Tipo</th>
        <th>Descrição</th>
        <th>Valor (€)</th>
        <th>Origem</th>
    </tr>
    <?php if ($movimentacoes->num_rows > 0): ?>
        <?php while ($mov = $movimentacoes->fetch_assoc()): ?>
            <tr>
                <td><?= date('d/m/Y H:i', strtotime($mov['data'])) ?></td>
                <td><?= $mov['tipo'] == 'receita' ? '<span style="color:green">Receita</span>' : '<span style="color:red">Despesa</span>' ?></td>
                <td><?= $mov['descricao'] ?></td>
                <td><?= number_format($mov['valor'], 2, ',', '.') ?>€</td>
                <td><?= $mov['origem'] ?></td>
            </tr>
        <?php endwhile; ?>
    <?php else: ?>
        <tr>
            <td colspan="5" style="text-align: center;">Nenhuma movimentação registrada.</td>
        </tr>
    <?php endif; ?>
</table>


<!-- Alertas para despesas vencidas não pagas -->
<?php if ($totalNaoPago > 0): ?>
<div class="alerta">
    <strong>Atenção!</strong> Existem <?= $despesasNaoPagas->num_rows ?> despesas vencidas não pagas, totalizando <?= number_format($totalNaoPago, 2, ',', '.') ?>€.
    <a href="#despesas-nao-pagas">Ver detalhes</a>
</div>
<?php endif; ?>

<!-- Tabela de Despesas -->
<h2>Lista de Despesas <?= !empty($filtro_categoria) ? "- Categoria: $filtro_categoria" : "" ?></h2>
<p>Total filtrado: <strong><?= number_format($totalDespesasFiltradas, 2, ',', '.') ?>€</strong></p>

<table border="1" cellpadding="10">
    <tr>
        <th>ID</th>
        <th>Nome</th>
        <th>Categoria</th>
        <th>Valor (€)</th>
        <th>Data</th>
        <th>Fornecedor</th>
        <th>Status</th>
        <th>Descrição</th>
        <th>Ações</th>
    </tr>
    <?php if ($resultado->num_rows > 0): ?>
        <?php while ($despesa = $resultado->fetch_assoc()): ?>
            <tr>
                <td><?= $despesa['D_id_despeza'] ?></td>
                <td><?= $despesa['D_nome'] ?></td>
                <td><?= $despesa['D_categoria'] ?></td>
                <td><?= number_format($despesa['D_valor'], 2, ',', '.') ?>€</td>
                <td><?= date('d/m/Y', strtotime($despesa['D_data'])) ?></td>
                <td><?= $despesa['D_fornecedor'] ?? 'N/A' ?></td>
                <td>
                    <?php if ($despesa['D_pago']): ?>
                        <span class="badge badge-success">Pago</span>
                    <?php else: ?>
                        <?php if (strtotime($despesa['D_data']) < time()): ?>
                            <span class="badge badge-danger">Vencido</span>
                        <?php else: ?>
                            <span class="badge badge-warning">Pendente</span>
                        <?php endif; ?>
                    <?php endif; ?>
                </td>
                <td><?= $despesa['D_descricao'] ?></td>
                <td>
                    <a href="editar_despesa.php?id=<?= $despesa['D_id_despeza'] ?>">Editar</a> |
                    <a href="eliminar_despesa.php?id=<?= $despesa['D_id_despeza'] ?>" onclick="return confirm('Tem certeza que deseja eliminar esta despesa?')">Eliminar</a>
                    <?php if (!$despesa['D_pago']): ?>
                     | <a href="?pagar_id=<?= $despesa['D_id_despeza'] ?><?= !empty($_SERVER['QUERY_STRING']) ? '&' . $_SERVER['QUERY_STRING'] : '' ?>" onclick="return confirm('Tem certeza que quer pagar esta despesa?')">Pagar</a>
                    <?php endif; ?>
                    <?php if (!empty($despesa['D_anexo'])): ?>
                     | <a href="anexos/<?= $despesa['D_anexo'] ?>" target="_blank">Ver Anexo</a>
                    <?php endif; ?>
                </td>
            </tr>
        <?php endwhile; ?>
    <?php else: ?>
        <tr>
            <td colspan="9" style="text-align: center;">Nenhuma despesa encontrada com os filtros atuais.</td>
        </tr>
    <?php endif; ?>
</table>

<!-- Despesas Não Pagas -->
<h2 id="despesas-nao-pagas">Despesas Vencidas Não Pagas</h2>
<table border="1" cellpadding="10">
    <tr>
        <th>ID</th>
        <th>Nome</th>
        <th>Categoria</th>
        <th>Valor (€)</th>
        <th>Data Vencimento</th>
        <th>Dias Atrasados</th>
        <th>Ações</th>
    </tr>
    <?php if ($despesasNaoPagas->num_rows > 0): ?>
        <?php while ($despesa = $despesasNaoPagas->fetch_assoc()): 
            $diasAtraso = floor((time() - strtotime($despesa['D_data'])) / (60 * 60 * 24));
        ?>
            <tr>
                <td><?= $despesa['D_id_despeza'] ?></td>
                <td><?= $despesa['D_nome'] ?></td>
                <td><?= $despesa['D_categoria'] ?></td>
                <td><?= number_format($despesa['D_valor'], 2, ',', '.') ?>€</td>
                <td><?= date('d/m/Y', strtotime($despesa['D_data'])) ?></td>
                <td><?= $diasAtraso ?> dias</td>
                <td>
                    <a href="?pagar_id=<?= $despesa['D_id_despeza'] ?><?= !empty($_SERVER['QUERY_STRING']) ? '&' . $_SERVER['QUERY_STRING'] : '' ?>" onclick="return confirm('Tem certeza que quer pagar esta despesa?')">Pagar Agora</a>
                </td>
            </tr>
        <?php endwhile; ?>
    <?php else: ?>
        <tr>
            <td colspan="7" style="text-align: center;">Nenhuma despesa vencida pendente.</td>
        </tr>
    <?php endif; ?>
</table>

<!-- Despesas Futuras -->
<h2>Despesas Futuras Planejadas</h2>
<table border="1" cellpadding="10">
    <tr>
        <th>ID</th>
        <th>Nome</th>
        <th>Categoria</th>
        <th>Valor (€)</th>
        <th>Data Prevista</th>
        <th>Dias Restantes</th>
        <th>Ações</th>
    </tr>
    <?php if ($despesasFuturas->num_rows > 0): ?>
        <?php while ($despesa = $despesasFuturas->fetch_assoc()): 
            $diasRestantes = floor((strtotime($despesa['D_data']) - time()) / (60 * 60 * 24));
        ?>
            <tr>
                <td><?= $despesa['D_id_despeza'] ?></td>
                <td><?= $despesa['D_nome'] ?></td>
                <td><?= $despesa['D_categoria'] ?></td>
                <td><?= number_format($despesa['D_valor'], 2, ',', '.') ?>€</td>
                <td><?= date('d/m/Y', strtotime($despesa['D_data'])) ?></td>
                <td><?= $diasRestantes ?> dias</td>
                <td>
                    <a href="editar_despesa.php?id=<?= $despesa['D_id_despeza'] ?>">Editar</a> |
                    <a href="eliminar_despesa.php?id=<?= $despesa['D_id_despeza'] ?>" onclick="return confirm('Tem certeza que deseja eliminar esta despesa planejada?')">Eliminar</a>
                </td>
            </tr>
        <?php endwhile; ?>
    <?php else: ?>
        <tr>
            <td colspan="7" style="text-align: center;">Nenhuma despesa futura planejada.</td>
        </tr>
    <?php endif; ?>
</table>

<!-- Totais adicionais -->
<div style="margin-top: 30px;">
    <h3>Totais Gerais:</h3>
    <ul>
        <li>Total em Despesas Manuais: <strong><?= number_format($totalDespesas, 2, ',', '.') ?>€</strong></li>
        <li>Total em Manutenções: <strong><?= number_format($totalManutencoes, 2, ',', '.') ?>€</strong></li>
        <li>Total em Serviços: <strong><?= number_format($totalServicos, 2, ',', '.') ?>€</strong></li>
        <li>Total em Salários (<?= $totalFuncionarios ?> funcionários): <strong><?= number_format($totalSalarios, 2, ',', '.') ?>€</strong></li>
        <li><strong>Total Geral: <?= number_format($totalGeral, 2, ',', '.') ?>€</strong></li>
    </ul>
</div>

<!-- Gráficos -->
<div class="grafico-container">
    <div class="grafico">
        <h3>Despesas por Categoria</h3>
        <canvas id="graficoCategoria"></canvas>
    </div>
    <div class="grafico">
    <h3>Evolução de Despesas (Últimos 6 meses)</h3>
    <canvas id="graficoEvolucao"></canvas>
</div>

<script>
    // Gráfico de categorias
    const ctxCategoria = document.getElementById('graficoCategoria').getContext('2d');
    const graficoCategoria = new Chart(ctxCategoria, {
        type: 'pie',
        data: {
            labels: [
                <?php
                foreach ($totalPorCategoria as $categoria => $total) {
                    echo "'$categoria', ";
                }
                ?>
            ],
            datasets: [{
                data: [
                    <?php
                    foreach ($totalPorCategoria as $total) {
                        echo "$total, ";
                    }
                    ?>
                ],
                backgroundColor: [
                    'rgba(255, 99, 132, 0.6)',
                    'rgba(54, 162, 235, 0.6)',
                    'rgba(255, 206, 86, 0.6)',
                    'rgba(75, 192, 192, 0.6)',
                    'rgba(153, 102, 255, 0.6)',
                    'rgba(255, 159, 64, 0.6)'
                ]
            }]
        },
        options: {
            responsive: true,
            plugins: {
                legend: {
                    position: 'bottom',
                }
            }
        }
    });

    // Gráfico de evolução de despesas
    const ctxEvolucao = document.getElementById('graficoEvolucao').getContext('2d');
    const graficoEvolucao = new Chart(ctxEvolucao, {
        type: 'line', // Gráfico de linha
        data: {
            labels: [
                <?php
                // Supondo que tenha um array $meses com os últimos 6 meses
                foreach ($meses as $mes) {
                    echo "'$mes', ";
                }
                ?>
            ],
            datasets: [{
                label: 'Despesas',
                data: [
                    <?php
                    // Aqui estamos a passar os valores das despesas dos últimos 6 meses
                    foreach ($despesasUltimos6Meses as $despesa) {
                        echo "$despesa, ";
                    }
                    ?>
                ],
                borderColor: 'rgba(75, 192, 192, 1)', // Cor da linha
                backgroundColor: 'rgba(75, 192, 192, 0.2)', // Cor de fundo da linha
                fill: true, // Preencher o gráfico
                tension: 0.1 // Curvatura da linha
            }]
        },
        options: {
            responsive: true,
            plugins: {
                legend: {
                    position: 'top',
                },
                tooltip: {
                    mode: 'index',
                    intersect: false,
                }
            },
            scales: {
                x: {
                    title: {
                        display: true,
                        text: 'Meses'
                    }
                },
                y: {
                    title: {
                        display: true,
                        text: 'Despesas (€)'
                    }
                }
            }
        }
    });
</script>



    // Gráfico de evolução mensal
    <?php
    // Obter dados para o gráfico de evolução (últimos 6 meses)
    $meses = [];
    $valoresMensais = [];
    
    for ($i = 5; $i >= 0; $i--) {
        $mes = date('Y-m', strtotime("-$i months"));
        $mesNome = date('M/Y', strtotime("-$i months"));
        $meses[] = $mesNome;
        
        $inicio = $mes . '-01';
        $fim = date('Y-m-t', strtotime($inicio));
        
        $totalMes = $conexao->query("SELECT SUM(D_valor) AS total FROM despesas WHERE D_data BETWEEN '$inicio' AND '$fim'")->fetch_assoc()['total'] ?? 0;
        $valoresMensais[] = $totalMes;
    }
    ?>
    

    const ctxEvolucao = document.getElementById('graficoEvolucao').getContext('2d');
    const graficoEvolucao = new Chart(ctxEvolucao, {
        type: 'line',
        data: {
            labels: [<?php echo "'" . implode("', '", $meses) . "'"; ?>],
            datasets: [{
                label: 'Total de Despesas',
                data: [<?php echo implode(", ", $valoresMensais); ?>],
                fill: false,
                borderColor: 'rgb(75, 192, 192)',
                tension: 0.1
            }]
        },
        options: {
            responsive: true,
            scales: {
                y: {
                    beginAtZero: true
                }
            }
        }
    });
</script>

</body>
</html>