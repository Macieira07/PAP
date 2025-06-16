<?php
include "../conexao.php";
session_start();

// Função flash
function set_flash($msg, $type = 'success') {
    $_SESSION['flash'] = ['msg' => $msg, 'type' => $type];
}
function show_flash() {
    if (!empty($_SESSION['flash'])) {
        $f = $_SESSION['flash'];
        $cls = $f['type'] === 'error' ? 'flash-error' : 'flash-success';
        echo "<div class='flash $cls'><i class='fas ".($f['type']==='error'?'fa-times-circle':'fa-check-circle')."'></i> {$f['msg']}</div>";
        unset($_SESSION['flash']);
    }
}

// Saldo
$saldoQuery = $conexao->query("SELECT saldo FROM conta_virtual WHERE id = 1");
$saldo = $saldoQuery->fetch_assoc()['saldo'] ?? 0;

// Filtros
$origemFilter = $_GET['origem'] ?? '';
$estadoFilter = $_GET['estado'] ?? '';
$searchTerm = trim($_GET['search'] ?? '');

$where = [];
if ($origemFilter && in_array($origemFilter, ['manutencao','servico','despesa'])) {
    $o = $conexao->real_escape_string($origemFilter);
    $where[] = "origem = '$o'";
}
if ($estadoFilter && in_array($estadoFilter, ['pago','por_pagar'])) {
    $where[] = $estadoFilter === 'pago' ? "pago=1" : "pago=0";
}
if ($searchTerm!=='') {
    $s = $conexao->real_escape_string($searchTerm);
    $where[] = "nome LIKE '%$s%'";
}
$whereSql = count($where)>0 ? 'WHERE '.implode(' AND ', $where) : '';

// Paginação
$itemsPerPage = 10;
$page = max(1, intval($_GET['page'] ?? 1));
$offset = ($page-1)*$itemsPerPage;

// Query principais e contagem
$sql = "
 SELECT * FROM (
  SELECT M_id_manutencao AS id, M_tipo AS nome, M_custo AS valor, 'manutencao' AS origem, M_pago AS pago, NULL AS data_pagamento FROM manutencao
  UNION ALL
  SELECT S_id_servico AS id, S_nome AS nome, S_preco AS valor, 'servico' AS origem, S_pago AS pago, NULL AS data_pagamento FROM servicos
  UNION ALL
  SELECT id, D_nome AS nome, D_valor AS valor, 'despesa' AS origem, D_pago AS pago, D_data_pagamento AS data_pagamento FROM despesas
 ) AS todas
 $whereSql ORDER BY pago ASC, nome ASC LIMIT $itemsPerPage OFFSET $offset";
$res = $conexao->query($sql);
$despesas = $res ? $res->fetch_all(MYSQLI_ASSOC):[];

$totRes = $conexao->query("SELECT COUNT(*) AS total FROM (
  SELECT M_id_manutencao AS id, M_tipo AS nome, M_custo AS valor, 'manutencao' AS origem, M_pago AS pago, NULL AS data_pagamento FROM manutencao
  UNION ALL
  SELECT S_id_servico AS id, S_nome AS nome, S_preco AS valor, 'servico' AS origem, S_pago AS pago, NULL AS data_pagamento FROM servicos
  UNION ALL
  SELECT id, D_nome AS nome, D_valor AS valor, 'despesa' AS origem, D_pago AS pago, D_data_pagamento AS data_pagamento FROM despesas
) AS todas $whereSql");
$totalItems = $totRes->fetch_assoc()['total'] ?? count($despesas);
$totalPages = ceil($totalItems/$itemsPerPage);

// Pagamento
if ($_SERVER['REQUEST_METHOD']==='POST' && isset($_POST['pagar'])) {
    $id = intval($_POST['id']);
    $valor = floatval($_POST['valor']);
    $origem = $_POST['origem'];
    if (!in_array($origem,['manutencao','servico','despesa']) || $saldo<$valor) {
        set_flash($saldo<$valor?'Saldo insuficiente.':'Origem inválida.','error');
        header("Location: despesas.php");
        exit;
    }
    $tbl = $origem==='manutencao' ? 'manutencao' : ($origem==='servico'?'servicos':'despesas');
    $pg = $origem==='despesa' ? "D_pago=1,D_data_pagamento=CURDATE()" : ($origem==='manutencao'?'M_pago=1':'S_pago=1');
    $idcol = $origem==='manutencao'?'M_id_manutencao':($origem==='servico'?'S_id_servico':'id');
    $stmt = $conexao->prepare("UPDATE $tbl SET $pg WHERE $idcol=?");
    $stmt->bind_param('i',$id); $stmt->execute(); $stmt->close();

    $desc="Pagamento de $origem: $id";
    $stmt = $conexao->prepare("INSERT INTO movimentacoes(tipo,descricao,valor,origem,origem_id) VALUES('despesa',?,?,?,?)");
    $stmt->bind_param('sdsi',$desc,$valor,$origem,$id);
    $stmt->execute(); $stmt->close();

    $stmt = $conexao->prepare("UPDATE conta_virtual SET saldo=saldo-? WHERE id=1");
    $stmt->bind_param('d',$valor);
    $stmt->execute(); $stmt->close();

    set_flash("Despesa paga com sucesso!","success");
    header("Location: despesas.php");
    exit;
}
?>
<!DOCTYPE html>
<html lang="pt-PT">
<head>
<meta charset="UTF-8"/>
<title>Despesas</title>
<link rel="stylesheet" href="../public/css/admin.css"/>
<script src="https://kit.fontawesome.com/a076d05399.js"></script>
<style>
.flash { padding:10px; margin:15px 0; border-radius:4px; }
.flash-success { background:#e0f5e9; color:#2e7d32; }
.flash-error { background:#fdecea; color:#c62828; }
.modal { display:none; position:fixed; top:0;left:0;width:100%;height:100%;background:rgba(0,0,0,0.5);
         justify-content:center;align-items:center; }
.modal .content { background:#fff; padding:20px; border-radius:5px; width:400px; }
.totalizador { margin-top:10px; font-weight:bold; }
</style>
<script>
function abrirDetalhes(id,nome,valor,origem,estado,datapag) {
    document.getElementById('det-id').innerText = id;
    document.getElementById('det-nome').innerText = nome;
    document.getElementById('det-valor').innerText = valor;
    document.getElementById('det-origem').innerText = origem;
    document.getElementById('det-estado').innerText = estado ? 'Pago' : 'Por pagar';
    document.getElementById('det-data').innerText = datapag || '-';
    document.getElementById('modal').style.display = 'flex';
}
function fecharModal() {
    document.getElementById('modal').style.display = 'none';
}
function confirmarPagar(id,valor,origem) {
    const saldoAtual = <?= $saldo ?>;
    const saldoRest = (saldoAtual - valor).toFixed(2);
    if (!confirm(`Vai pagar ${valor.toFixed(2)} € da despesa de ${origem} com ID ${id}?\nSaldo restante: ${saldoRest} €`)) {
        return false;
    }
    return true;
}
function atualizarTotal() {
    let total=0;
    document.querySelectorAll('.valordesp').forEach(e=>{
        total += parseFloat(e.innerText);
    });
    document.getElementById('totalizador').innerText = total.toFixed(2);
}
window.onload = atualizarTotal;
</script>
</head>
<body>

<div style="display: flex; align-items: center; gap: 10px;">
    <img src="https://img.icons8.com/?size=100&id=22462&format=png&color=000000" alt="Ícone Despesas " style="height: 50px;">
    <h1>Todos as Despesas</h1>
</div>

<h2>Saldo atual: <strong><?= number_format($saldo,2) ?> €</strong></h2>
<?php show_flash(); ?>
<form method="get" action="despesas.php" style="margin-bottom:20px;">
  <label>Origem:
    <select name="origem">
      <option value="">Todos</option>
      <option value="manutencao" <?= $origemFilter==='manutencao'?'selected':''?>>Manutenção</option>
      <option value="servico" <?= $origemFilter==='servico'?'selected':''?>>Serviço</option>
      <option value="despesa" <?= $origemFilter==='despesa'?'selected':''?>>Despesa</option>
    </select>
  </label>
  &nbsp;
  <label>Estado:
    <select name="estado">
      <option value="">Todos</option>
      <option value="pago" <?= $estadoFilter==='pago'?'selected':''?>>Pago</option>
      <option value="por_pagar" <?= $estadoFilter==='por_pagar'?'selected':''?>>Por pagar</option>
    </select>
  </label>
  &nbsp;
  <label>Pesquisar:
    <input type="text" name="search" value="<?= htmlspecialchars($searchTerm)?>" placeholder="Nome…">
  </label>
  &nbsp;
  <button type="submit">Filtrar</button>
</form>

<h3>Lista de Despesas</h3>
<table border="1" cellpadding="10" style="border-collapse:collapse; width:100%;">
<thead style="background:#eee;">
<tr>
  <th>ID</th><th>Origem</th><th>Nome</th><th>Valor (€)</th><th>Estado</th><th>Data de Pagamento</th><th>Ação</th>
</tr>
</thead>
<tbody>
<?php if(empty($despesas)): ?>
<tr><td colspan="7" style="text-align:center;">Nenhuma despesa encontrada.</td></tr>
<?php else: foreach($despesas as $d): ?>
<tr>
  <td><?= htmlspecialchars($d['id']) ?></td>
  <td><?= ucfirst(htmlspecialchars($d['origem'])) ?></td>
  <td><?= htmlspecialchars($d['nome']) ?></td>
  <td class="valordesp"><?= number_format($d['valor'],2) ?></td>
  <td><?= $d['pago'] ? '<span style="color:green;font-weight:bold">Pago</span>' : '<span style="color:red;font-weight:bold">Por pagar</span>' ?></td>
  <td><?= $d['pago'] ? htmlspecialchars($d['data_pagamento']??'-') : '-' ?></td>
  <td>
    <button onclick="abrirDetalhes('<?= $d['id']?>','<?= addslashes($d['nome'])?>','<?= $d['valor']?>','<?= $d['origem']?>','<?= $d['pago']?>','<?= $d['data_pagamento']?>')">Detalhes</button>
    <?php if(!$d['pago']): ?>
      <?php if($saldo>=$d['valor']): ?>
      <form method="post" style="display:inline;" onsubmit="return confirmarPagar('<?= $d['id']?>',<?= $d['valor']?>,'<?= $d['origem']?>');">
        <input type="hidden" name="id" value="<?= $d['id'] ?>">
        <input type="hidden" name="valor" value="<?= $d['valor'] ?>">
        <input type="hidden" name="origem" value="<?= htmlspecialchars($d['origem']) ?>">
        <button type="submit" name="pagar">Pagar</button>
      </form>
      <?php else: ?>
        <span style="color:red;">Saldo insuficiente</span>
      <?php endif; ?>
    <?php else: ?>
      <span style="color:green;">✔</span>
    <?php endif; ?>
  </td>
</tr>
<?php endforeach; endif; ?>
</tbody>
</table>

<div class="totalizador">Total despesas visíveis: <span id="totalizador">0.00</span> €</div>

<div style="margin-top:15px;">
<?php for($p=1;$p<=$totalPages;$p++):
    $u='despesas.php?'.http_build_query(array_merge($_GET,['page'=>$p]));
?>
  <a href="<?= $u ?>" style="margin-right:5px; <?= $p==$page?'font-weight:bold;text-decoration:underline':'' ?>"><?= $p ?></a>
<?php endfor; ?>
</div>

<!-- Modal -->
<div id="modal" class="modal" onclick="fecharModal()">
  <div class="content" onclick="event.stopPropagation()">
    <h3>Detalhes da despesa</h3>
    <p><strong>ID:</strong> <span id="det-id"></span></p>
    <p><strong>Nome:</strong> <span id="det-nome"></span></p>
    <p><strong>Valor:</strong> <span id="det-valor"></span> €</p>
    <p><strong>Origem:</strong> <span id="det-origem"></span></p>
    <p><strong>Estado:</strong> <span id="det-estado"></span></p>
    <p><strong>Data de Pagamento:</strong> <span id="det-data"></span></p>
    <button onclick="fecharModal()">Fechar</button>
  </div>
</div>

<a href="admin.php">← Voltar</a>
</body>
</html>
