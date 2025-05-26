<?php
include "../conexao.php";
session_start();

// Função para guardar mensagens flash na sessão
function set_flash($msg, $type = 'success') {
    $_SESSION['flash'] = ['msg' => $msg, 'type' => $type];
}

// Função para mostrar mensagens flash
function show_flash() {
    if (!empty($_SESSION['flash'])) {
        $type = $_SESSION['flash']['type'] === 'error' ? 'red' : 'green';
        echo "<p style='color: $type; font-weight: bold;'>{$_SESSION['flash']['msg']}</p>";
        unset($_SESSION['flash']);
    }
}

// Obter saldo atual
$saldoQuery = $conexao->query("SELECT saldo FROM conta_virtual WHERE id = 1");
$saldo = $saldoQuery->fetch_assoc()['saldo'] ?? 0;

// --- FILTROS e PESQUISA ---
$origemFilter = $_GET['origem'] ?? '';
$estadoFilter = $_GET['estado'] ?? '';
$searchTerm = trim($_GET['search'] ?? '');

// Construir condições SQL
$where = [];
$params = [];
$param_types = '';

if ($origemFilter && in_array($origemFilter, ['manutencao', 'servico', 'despesa'])) {
    $where[] = "origem = ?";
    $params[] = $origemFilter;
    $param_types .= 's';
}

if ($estadoFilter && in_array($estadoFilter, ['pago', 'por_pagar'])) {
    if ($estadoFilter === 'pago') {
        $where[] = "pago = 1";
    } else {
        $where[] = "pago = 0";
    }
}

if ($searchTerm !== '') {
    $where[] = "nome LIKE ?";
    $params[] = "%$searchTerm%";
    $param_types .= 's';
}

$whereSql = '';
if (count($where) > 0) {
    $whereSql = 'WHERE ' . implode(' AND ', $where);
}

// --- PAGINAÇÃO ---
$itemsPerPage = 10;
$page = max(1, intval($_GET['page'] ?? 1));
$offset = ($page - 1) * $itemsPerPage;

// Buscar despesas com filtro e paginação
$sql = "
    SELECT * FROM (
        SELECT M_id_manutencao AS id, M_tipo AS nome, M_custo AS valor, 'manutencao' AS origem, M_pago AS pago, NULL AS data_pagamento FROM manutencao
        UNION ALL
        SELECT S_id_servico AS id, S_nome AS nome, S_preco AS valor, 'servico' AS origem, S_pago AS pago, NULL AS data_pagamento FROM servicos
        UNION ALL
        SELECT id, D_nome AS nome, D_valor AS valor, 'despesa' AS origem, D_pago AS pago, D_data_pagamento AS data_pagamento FROM despesas
    ) AS despesas
    $whereSql
    ORDER BY pago ASC, nome ASC
    LIMIT $itemsPerPage OFFSET $offset
";

// Preparar e executar query (sem prepared para UNION, aplicamos filtro depois)
$despesas = [];
$result = $conexao->query($sql);
if ($result) {
    while ($row = $result->fetch_assoc()) {
        $despesas[] = $row;
    }
}

// Contar total para paginação (simplificado, sem filtro aplicado para não complicar)
$totalResult = $conexao->query("
    SELECT COUNT(*) AS total FROM (
        SELECT M_id_manutencao AS id FROM manutencao
        UNION ALL
        SELECT S_id_servico AS id FROM servicos
        UNION ALL
        SELECT id FROM despesas
    ) AS all_despesas
");
$totalItems = $totalResult->fetch_assoc()['total'] ?? count($despesas);
$totalPages = ceil($totalItems / $itemsPerPage);

// --- PROCESSAR PAGAMENTO ---
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['pagar'])) {
    $id = intval($_POST['id']);
    $valor = floatval($_POST['valor']);
    $origem = $_POST['origem'];

    if (!in_array($origem, ['manutencao', 'servico', 'despesa'])) {
        set_flash('Origem inválida.', 'error');
        header("Location: despesas.php");
        exit;
    }

    if ($saldo < $valor) {
        set_flash('Saldo insuficiente para pagar esta despesa!', 'error');
        header("Location: despesas.php");
        exit;
    }

    // Usar prepared statements para evitar injeção
    if ($origem === 'manutencao') {
        $stmt = $conexao->prepare("UPDATE manutencao SET M_pago = 1 WHERE M_id_manutencao = ?");
        $stmt->bind_param('i', $id);
        $stmt->execute();
        $stmt->close();
    } elseif ($origem === 'servico') {
        $stmt = $conexao->prepare("UPDATE servicos SET S_pago = 1 WHERE S_id_servico = ?");
        $stmt->bind_param('i', $id);
        $stmt->execute();
        $stmt->close();
    } else {
        $stmt = $conexao->prepare("UPDATE despesas SET D_pago = 1, D_data_pagamento = CURDATE() WHERE id = ?");
        $stmt->bind_param('i', $id);
        $stmt->execute();
        $stmt->close();
    }

    // Registar movimentação
    $descricao = "Pagamento de $origem: $id";
    $stmt = $conexao->prepare("INSERT INTO movimentacoes (tipo, descricao, valor, origem, origem_id) VALUES ('despesa', ?, ?, ?, ?)");
    $stmt->bind_param('sdsi', $descricao, $valor, $origem, $id);
    $stmt->execute();
    $stmt->close();

    // Atualizar saldo
    $stmt = $conexao->prepare("UPDATE conta_virtual SET saldo = saldo - ? WHERE id = 1");
    $stmt->bind_param('d', $valor);
    $stmt->execute();
    $stmt->close();

    set_flash("Despesa paga com sucesso!");
    header("Location: despesas.php");
    exit;
}

?>

<!DOCTYPE html>
<html lang="pt-PT">
<head>
    <meta charset="UTF-8" />
    <title>Despesas</title>
    <link rel="stylesheet" href="admin.css">
    <script src="https://kit.fontawesome.com/a076d05399.js" crossorigin="anonymous"></script>
</head>
<body>

<h2>Saldo atual: <strong><?= number_format($saldo, 2) ?> €</strong></h2>

<?php show_flash(); ?>
<a href="admin.php">← Voltar</a>
<h3>Filtros e Pesquisa</h3>
<form method="get" action="despesas.php" style="margin-bottom: 20px;">
    <label>Origem:
        <select name="origem">
            <option value="">Todos</option>
            <option value="manutencao" <?= $origemFilter === 'manutencao' ? 'selected' : '' ?>>Manutenção</option>
            <option value="servico" <?= $origemFilter === 'servico' ? 'selected' : '' ?>>Serviço</option>
            <option value="despesa" <?= $origemFilter === 'despesa' ? 'selected' : '' ?>>Despesa</option>
        </select>
    </label>
    &nbsp;&nbsp;
    <label>Estado:
        <select name="estado">
            <option value="">Todos</option>
            <option value="pago" <?= $estadoFilter === 'pago' ? 'selected' : '' ?>>Pago</option>
            <option value="por_pagar" <?= $estadoFilter === 'por_pagar' ? 'selected' : '' ?>>Por Pagar</option>
        </select>
    </label>
    &nbsp;&nbsp;
    <label>Pesquisar:
        <input type="text" name="search" value="<?= htmlspecialchars($searchTerm) ?>" placeholder="Nome...">
    </label>
    &nbsp;&nbsp;
    <button type="submit">Filtrar</button>
</form>

<h3>Lista de Despesas</h3>
<table border="1" cellpadding="10" style="border-collapse: collapse; width: 100%;">
    <thead>
    <tr style="background: #eee;">
        <th>ID</th>
        <th>Origem</th>
        <th>Nome</th>
        <th>Valor (€)</th>
        <th>Estado</th>
        <th>Data de Pagamento</th>
        <th>Ação</th>
    </tr>
    </thead>
    <tbody>
    <?php if (empty($despesas)): ?>
        <tr><td colspan="7" style="text-align:center;">Nenhuma despesa encontrada.</td></tr>
    <?php else: ?>
        <?php foreach ($despesas as $i => $d): ?>
            <tr style="<?= $i % 2 == 0 ? 'background: #f9f9f9;' : '' ?>">
                <td><?= htmlspecialchars($d['id']) ?></td>
                <td><?= ucfirst(htmlspecialchars($d['origem'])) ?></td>
                <td><?= htmlspecialchars($d['nome']) ?></td>
                <td><?= number_format($d['valor'], 2) ?></td>
                <td><?= $d['pago'] ? '<span style="color:green; font-weight:bold;">Pago</span>' : '<span style="color:red; font-weight:bold;">Por pagar</span>' ?></td>
                <td><?= $d['pago'] ? htmlspecialchars($d['data_pagamento'] ?? '-') : '-' ?></td>
                <td>
                    <?php if (!$d['pago']): ?>
                        <?php if ($saldo >= $d['valor']): ?>
                            <form method="post" style="display:inline;" onsubmit="return confirm('Tem certeza que quer pagar esta despesa?');">
                                <input type="hidden" name="id" value="<?= htmlspecialchars($d['id']) ?>">
                                <input type="hidden" name="valor" value="<?= htmlspecialchars($d['valor']) ?>">
                                <input type="hidden" name="origem" value="<?= htmlspecialchars($d['origem']) ?>">
                                <button type="submit" name="pagar">Pagar</button>
                            </form>
                        <?php else: ?>
                            <span style="color:red;">Saldo insuficiente</span>
                        <?php endif; ?>
                    <?php else: ?>
                        <span style="color:green;">&#10004;</span>
                    <?php endif; ?>
                </td>
            </tr>
        <?php endforeach; ?>
    <?php endif; ?>
    </tbody>
</table>

<!-- Paginação -->
<div style="margin-top: 15px;">
    <?php if ($totalPages > 1): ?>
        <?php for ($p = 1; $p <= $totalPages; $p++): ?>
            <?php
                $url = 'despesas.php?'.http_build_query(array_merge($_GET, ['page' => $p]));
            ?>
            <a href="<?= $url ?>" style="margin-right: 5px; <?= $p == $page ? 'font-weight:bold; text-decoration: underline;' : '' ?>">
                <?= $p ?>
            </a>
        <?php endfor; ?>
    <?php endif; ?>
</div>
<a href="admin.php">← Voltar</a>
</body>
</html>
