<?php
include "../../conexao.php";
session_start();

// Função flash
function set_flash($msg, $type = 'success') {
    $_SESSION['flash'] = ['msg' => $msg, 'type' => $type];
}
function show_flash() {
    if (!empty($_SESSION['flash'])) {
        $f = $_SESSION['flash'];
        $cls = $f['type'] === 'error' ? 'flash-message error' : 'flash-message success';
        echo "<div class='$cls'><i class='fas ".($f['type']==='error'?'fa-times-circle':'fa-check-circle')."'></i> {$f['msg']}</div>";
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
$itemsPerPage = 3;
$page = max(1, intval($_GET['page'] ?? 1));
$offset = ($page-1)*$itemsPerPage;

// Query principais e contagem
$sql = "
 SELECT * FROM (
  SELECT M_id_manutencao AS id, M_tipo AS nome, M_custo AS valor, 'manutencao' AS origem, M_pago AS pago, NULL AS data_pagamento, NULL AS recorrente, NULL AS periodicidade, NULL AS data_fim_recorrencia, NULL AS pausada FROM manutencao
  UNION ALL
  SELECT S_id_servico AS id, S_nome AS nome, S_preco AS valor, 'servico' AS origem, S_pago AS pago, NULL AS data_pagamento, NULL AS recorrente, NULL AS periodicidade, NULL AS data_fim_recorrencia, NULL AS pausada FROM servicos
  UNION ALL
  SELECT id, D_nome AS nome, D_valor AS valor, 'despesa' AS origem, D_pago AS pago, D_data_pagamento AS data_pagamento, recorrente, periodicidade, data_fim_recorrencia, pausada FROM despesas
 ) AS todas
 $whereSql ORDER BY pago ASC, nome ASC LIMIT $itemsPerPage OFFSET $offset";
$res = $conexao->query($sql);
$despesas = $res ? $res->fetch_all(MYSQLI_ASSOC):[];

$totRes = $conexao->query("SELECT COUNT(*) AS total FROM (
  SELECT M_id_manutencao AS id, M_tipo AS nome, M_custo AS valor, 'manutencao' AS origem, M_pago AS pago, NULL AS data_pagamento, NULL AS recorrente, NULL AS periodicidade, NULL AS data_fim_recorrencia, NULL AS pausada FROM manutencao
  UNION ALL
  SELECT S_id_servico AS id, S_nome AS nome, S_preco AS valor, 'servico' AS origem, S_pago AS pago, NULL AS data_pagamento, NULL AS recorrente, NULL AS periodicidade, NULL AS data_fim_recorrencia, NULL AS pausada FROM servicos
  UNION ALL
  SELECT id, D_nome AS nome, D_valor AS valor, 'despesa' AS origem, D_pago AS pago, D_data_pagamento AS data_pagamento, recorrente, periodicidade, data_fim_recorrencia, pausada FROM despesas
) AS todas $whereSql");
$totalItems = $totRes->fetch_assoc()['total'] ?? count($despesas);
$totalPages = ceil($totalItems/$itemsPerPage);

// Processar ações
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Pagamento
    if (isset($_POST['pagar'])) {
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
    
    // Adicionar nova despesa
    if (isset($_POST['adicionar_despesa'])) {
        $nome = $_POST['nome'];
        $valor = $_POST['valor'];
        $data = $_POST['data'] ?? date('Y-m-d');
        $descricao = $_POST['descricao'] ?? '';
        $recorrente = isset($_POST['recorrente']) ? 1 : 0;
        $periodicidade = $_POST['periodicidade'] ?? null;
        $data_fim_recorrencia = $_POST['data_fim_recorrencia'] ?? null;

        if (!is_numeric($valor) || $valor <= 0) {
            set_flash("O valor deve ser um número positivo.", 'error');
            header("Location: despesas.php");
            exit;
        }

        $stmt = $conexao->prepare("INSERT INTO despesas (D_nome, D_valor, D_data, D_descricao, recorrente, periodicidade, data_fim_recorrencia) VALUES (?, ?, ?, ?, ?, ?, ?)");
        $stmt->bind_param("sdssiss", $nome, $valor, $data, $descricao, $recorrente, $periodicidade, $data_fim_recorrencia);
        
        if ($stmt->execute()) {
            set_flash("Despesa adicionada com sucesso!", "success");
            
            // Se for recorrente, criar previsões
            if ($recorrente) {
                criarPrevisoesDespesa($conexao, $conexao->insert_id, $nome, $valor, $periodicidade, $data_fim_recorrencia);
            }
        } else {
            set_flash("Erro ao adicionar despesa.", "error");
        }
        
        $stmt->close();
        header("Location: despesas.php");
        exit;
    }
    
    // Editar despesa
    if (isset($_POST['editar_despesa'])) {
        $id = intval($_POST['id']);
        $nome = $_POST['nome'];
        $valor = $_POST['valor'];
        $data = $_POST['data'];
        $descricao = $_POST['descricao'];
        $recorrente = isset($_POST['recorrente']) ? 1 : 0;
        $periodicidade = $_POST['periodicidade'] ?? null;
        $data_fim_recorrencia = $_POST['data_fim_recorrencia'] ?? null;
        $pausada = isset($_POST['pausada']) ? 1 : 0;

        if (!is_numeric($valor) || $valor <= 0) {
            set_flash("O valor deve ser um número positivo.", 'error');
            header("Location: despesas.php");
            exit;
        }

        $stmt = $conexao->prepare("UPDATE despesas SET D_nome=?, D_valor=?, D_data=?, D_descricao=?, recorrente=?, periodicidade=?, data_fim_recorrencia=?, pausada=? WHERE id=?");
        $stmt->bind_param("sdssissii", $nome, $valor, $data, $descricao, $recorrente, $periodicidade, $data_fim_recorrencia, $pausada, $id);
        
        if ($stmt->execute()) {
            set_flash("Despesa atualizada com sucesso!", "success");
            
            // Atualizar previsões se for recorrente
            if ($recorrente) {
                atualizarPrevisoesDespesa($conexao, $id, $nome, $valor, $periodicidade, $data_fim_recorrencia);
            }
        } else {
            set_flash("Erro ao atualizar despesa.", "error");
        }
        
        $stmt->close();
        header("Location: despesas.php");
        exit;
    }
    
    // Eliminar despesa
    if (isset($_POST['eliminar_despesa'])) {
        $id = intval($_POST['id']);
        
        // Verificar se a despesa já foi paga
        $check = $conexao->query("SELECT D_pago FROM despesas WHERE id=$id");
        if ($check && $check->fetch_assoc()['D_pago']) {
            set_flash("Não é possível eliminar uma despesa já paga.", 'error');
            header("Location: despesas.php");
            exit;
        }
        
        $stmt = $conexao->prepare("DELETE FROM despesas WHERE id=?");
        $stmt->bind_param("i", $id);
        
        if ($stmt->execute()) {
            set_flash("Despesa eliminada com sucesso!", "success");
            
            // Eliminar previsões associadas
            $conexao->query("DELETE FROM despesas_previsoes WHERE despesa_id=$id");
        } else {
            set_flash("Erro ao eliminar despesa.", "error");
        }
        
        $stmt->close();
        header("Location: despesas.php");
        exit;
    }
    
    // Pausar/Retomar despesa recorrente
    if (isset($_POST['pausar_despesa'])) {
        $id = intval($_POST['id']);
        $pausada = intval($_POST['pausada']);
        
        $stmt = $conexao->prepare("UPDATE despesas SET pausada=? WHERE id=?");
        $stmt->bind_param("ii", $pausada, $id);
        
        if ($stmt->execute()) {
            $acao = $pausada ? "pausada" : "retomada";
            set_flash("Despesa recorrente $acao com sucesso!", "success");
        } else {
            set_flash("Erro ao atualizar despesa.", "error");
        }
        
        $stmt->close();
        header("Location: despesas.php");
        exit;
    }
}

// Função para criar previsões de despesas recorrentes
function criarPrevisoesDespesa($conexao, $despesa_id, $nome, $valor, $periodicidade, $data_fim) {
    $data_atual = date('Y-m-d');
    $data_fim = $data_fim ?: date('Y-m-d', strtotime('+1 year'));
    
    $datas = [];
    $current = $data_atual;
    
    while (strtotime($current) <= strtotime($data_fim)) {
        $datas[] = $current;
        
        switch ($periodicidade) {
            case 'mensal':
                $current = date('Y-m-d', strtotime($current . ' +1 month'));
                break;
            case 'semanal':
                $current = date('Y-m-d', strtotime($current . ' +1 week'));
                break;
            case 'anual':
                $current = date('Y-m-d', strtotime($current . ' +1 year'));
                break;
            default:
                break 2;
        }
    }
    
    // Remover a primeira data (já é a despesa principal)
    array_shift($datas);
    
    // Inserir previsões
    foreach ($datas as $data) {
        $stmt = $conexao->prepare("INSERT INTO despesas_previsoes (despesa_id, nome, valor, data_prevista, periodicidade) VALUES (?, ?, ?, ?, ?)");
        $stmt->bind_param("isdss", $despesa_id, $nome, $valor, $data, $periodicidade);
        $stmt->execute();
        $stmt->close();
    }
}

// Função para atualizar previsões de despesas recorrentes
function atualizarPrevisoesDespesa($conexao, $despesa_id, $nome, $valor, $periodicidade, $data_fim) {
    // Primeiro eliminar previsões existentes não pagas
    $conexao->query("DELETE FROM despesas_previsoes WHERE despesa_id=$despesa_id AND paga=0");
    
    // Depois criar novas previsões
    criarPrevisoesDespesa($conexao, $despesa_id, $nome, $valor, $periodicidade, $data_fim);
}

// Obter previsões de despesas
function obterPrevisoesDespesa($conexao, $despesa_id) {
    $result = $conexao->query("SELECT * FROM despesas_previsoes WHERE despesa_id=$despesa_id ORDER BY data_prevista ASC");
    $previsoes = [];
    while ($row = $result->fetch_assoc()) {
        $previsoes[] = $row;
    }
    return $previsoes;
}

// Obter histórico de pagamentos de uma despesa recorrente
function obterHistoricoPagamentos($conexao, $despesa_id) {
    $result = $conexao->query("
        SELECT m.* FROM movimentacoes m
        WHERE m.origem = 'despesa' AND m.origem_id IN (
            SELECT id FROM despesas WHERE id = $despesa_id OR id IN (
                SELECT despesa_id FROM despesas_previsoes WHERE despesa_id = $despesa_id AND paga = 1
            )
        )
        ORDER BY m.data DESC
    ");
    $historico = [];
    while ($row = $result->fetch_assoc()) {
        $historico[] = $row;
    }
    return $historico;
}
?>
<!DOCTYPE html>
<html lang="pt-PT">
<head>
<meta charset="UTF-8"/>
<title>Despesas</title>
<link rel="stylesheet" href="../global.css"/>
<script src="https://kit.fontawesome.com/a076d05399.js"></script>
<style>
/* Custom badges para recorrente/pausada */
.badge-recorrente {
    background: #e3f2fd;
    color: #1565c0;
    padding: 2px 8px;
    border-radius: 12px;
    font-size: 12px;
    margin-left: 5px;
}
.badge-pausada {
    background: #fff3e0;
    color: #e65100;
    padding: 2px 8px;
    border-radius: 12px;
    font-size: 12px;
    margin-left: 5px;
}
</style>
<script>
function abrirDetalhes(id,nome,valor,origem,estado,datapag,recorrente,pausada) {
    document.getElementById('det-id').innerText = id;
    document.getElementById('det-nome').innerText = nome;
    document.getElementById('det-valor').innerText = valor;
    document.getElementById('det-origem').innerText = origem;
    document.getElementById('det-estado').innerText = estado ? 'Pago' : 'Por pagar';
    document.getElementById('det-data').innerText = datapag || '-';
    document.getElementById('det-recorrente').innerText = recorrente ? 'Sim' : 'Não';
    document.getElementById('det-pausada').innerText = pausada ? 'Sim' : 'Não';
    document.getElementById('modal-detalhes').style.display = 'flex';
}

function abrirEditar(id,nome,valor,data,descricao,recorrente,periodicidade,data_fim,pausada) {
    document.getElementById('edit-id').value = id;
    document.getElementById('edit-nome').value = nome;
    document.getElementById('edit-valor').value = valor;
    document.getElementById('edit-data').value = data;
    document.getElementById('edit-descricao').value = descricao || '';
    document.getElementById('edit-recorrente').checked = recorrente == 1;
    document.getElementById('edit-periodicidade').value = periodicidade || 'mensal';
    document.getElementById('edit-data-fim').value = data_fim || '';
    document.getElementById('edit-pausada').checked = pausada == 1;
    
    // Mostrar/ocultar campos de recorrencia
    document.getElementById('recorrencia-opts').style.display = 
        document.getElementById('edit-recorrente').checked ? 'block' : 'none';
    
    document.getElementById('modal-editar').style.display = 'flex';
}

function abrirEliminar(id, nome) {
    document.getElementById('delete-id').value = id;
    document.getElementById('delete-nome').innerText = nome;
    document.getElementById('modal-eliminar').style.display = 'flex';
}

function abrirPrevisoes(id) {
    fetch(`../obter_previsoes.php?despesa_id=${id}`)
        .then(response => response.text())
        .then(html => {
            document.getElementById('previsoes-content').innerHTML = html;
            document.getElementById('modal-previsoes').style.display = 'flex';
        });
}

function abrirHistorico(id) {
    fetch(`../obter_historico.php?despesa_id=${id}`)
        .then(response => response.text())
        .then(html => {
            document.getElementById('historico-content').innerHTML = html;
            document.getElementById('modal-historico').style.display = 'flex';
        });
}

function fecharModal(modalId) {
    document.getElementById(modalId).style.display = 'none';
}

function confirmarPagar(id,valor,origem) {
    const saldoAtual = <?= $saldo ?>;
    const saldoRest = (saldoAtual - valor).toFixed(2);
    if (!confirm(`Vai pagar ${valor.toFixed(2)} € da despesa de ${origem} com ID ${id}?\nSaldo restante: ${saldoRest} €`)) {
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

function toggleRecorrencia() {
    document.getElementById('recorrencia-opts').style.display = 
        document.getElementById('recorrente').checked ? 'block' : 'none';
}

function toggleRecorrenciaEdit() {
    document.getElementById('recorrencia-opts-edit').style.display = 
        document.getElementById('edit-recorrente').checked ? 'block' : 'none';
}

window.onload = function() {
    atualizarTotal();
    
    // Fechar modais ao clicar fora
    document.querySelectorAll('.modal').forEach(modal => {
        modal.addEventListener('click', function(e) {
            if (e.target === modal) {
                modal.style.display = 'none';
            }
        });
    });
}
</script>
</head>
<body>
<div class="admin-container">
    <div class="top-bar" style="justify-content:center;">
        <img src="https://img.icons8.com/?size=100&id=22462&format=png&color=000000" alt="Ícone Despesas " style="height: 50px;">
        <h1 style="margin: 0;">Despesas</h1>
    </div>
    <h3>Saldo atual: <span class="saldo-valor saldo-positivo">€<?= number_format($saldo,2,',','.') ?></span></h3>
    <?php show_flash(); ?>
    <!-- Formulário de filtros -->
    <form method="get" action="despesas.php" class="flex" style="gap:16px; max-width:900px; margin-bottom:24px;">
      <div class="form-group">
        <label>Origem:
          <select name="origem">
            <option value="">Todos</option>
            <option value="manutencao" <?= $origemFilter==='manutencao'?'selected':''?>>Manutenção</option>
            <option value="servico" <?= $origemFilter==='servico'?'selected':''?>>Serviço</option>
            <option value="despesa" <?= $origemFilter==='despesa'?'selected':''?>>Despesa</option>
          </select>
        </label>
      </div>
      <div class="form-group">
        <label>Estado:
          <select name="estado">
            <option value="">Todos</option>
            <option value="pago" <?= $estadoFilter==='pago'?'selected':''?>>Pago</option>
            <option value="por_pagar" <?= $estadoFilter==='por_pagar'?'selected':''?>>Por pagar</option>
          </select>
        </label>
      </div>
      <div class="form-group">
        <label>Pesquisar:
          <input type="text" name="search" value="<?= htmlspecialchars($searchTerm)?>" placeholder="Nome…">
        </label>
      </div>
      <div class="form-group" style="align-self:flex-end;">
        <button type="submit" class="btn">Filtrar</button>
      </div>
    </form>
    <!-- Formulário para adicionar nova despesa -->
    <button class="btn btn-add" onclick="document.getElementById('modalAdicionar').classList.add('active')">+ Adicionar Nova Despesa</button>
    <div class="table-responsive">
    <table class="table table-hover table-striped">
    <thead>
    <tr>
      <th>ID</th><th>Origem</th><th>Nome</th><th>Valor (€)</th><th>Estado</th><th>Data de Pagamento</th><th class="acao">Ação</th>
    </tr>
    </thead>
    <tbody>
    <?php if(empty($despesas)): ?>
    <tr><td colspan="7" class="text-center">Nenhuma despesa encontrada.</td></tr>
    <?php else: foreach($despesas as $d): ?>
    <tr>
      <td><?= htmlspecialchars($d['id']) ?></td>
      <td>
        <?= ucfirst(htmlspecialchars($d['origem'])) ?>
        <?php if($d['recorrente']): ?>
            <span class="badge badge-info badge-recorrente">Recorrente</span>
            <?php if($d['pausada']): ?>
                <span class="badge badge-warning badge-pausada">Pausada</span>
            <?php endif; ?>
        <?php endif; ?>
      </td>
      <td><?= htmlspecialchars($d['nome']) ?></td>
      <td class="valordesp"><?= number_format($d['valor'],2,',','.') ?></td>
      <td><?= $d['pago'] ? '<span class="badge badge-success">Pago</span>' : '<span class="badge badge-error">Por pagar</span>' ?></td>
      <td><?= $d['pago'] ? htmlspecialchars($d['data_pagamento']??'-') : '-' ?></td>
      <td class="acao">
        <div class="acao-btns">
          <button class="btn btn-view btn-small" onclick="abrirDetalhes('<?= $d['id']?>','<?= addslashes($d['nome'])?>','<?= $d['valor']?>','<?= $d['origem']?>','<?= $d['pago']?>','<?= $d['data_pagamento']?>','<?= $d['recorrente']?>','<?= $d['pausada']?>')">Detalhes</button>
          <?php if($d['origem'] === 'despesa'): ?>
              <button class="btn btn-view btn-small" onclick="abrirEditar('<?= $d['id']?>','<?= addslashes($d['nome'])?>','<?= $d['valor']?>','<?= $d['D_data'] ?? date('Y-m-d')?>','<?= addslashes($d['D_descricao'] ?? '')?>','<?= $d['recorrente']?>','<?= $d['periodicidade']?>','<?= $d['data_fim_recorrencia']?>','<?= $d['pausada']?>')">Editar</button>
              <?php if(!$d['pago']): ?>
                  <button class="btn button-danger btn-small" onclick="abrirEliminar('<?= $d['id']?>','<?= addslashes($d['nome'])?>')">Eliminar</button>
              <?php endif; ?>
              <?php if($d['recorrente']): ?>
                  <button class="btn btn-info btn-small" onclick="abrirPrevisoes('<?= $d['id']?>')">Previsões</button>
                  <button class="btn btn-info btn-small" onclick="abrirHistorico('<?= $d['id']?>')">Histórico</button>
                  <form method="post" style="display:inline;">
                      <input type="hidden" name="id" value="<?= $d['id'] ?>">
                      <input type="hidden" name="pausada" value="<?= $d['pausada'] ? 0 : 1 ?>">
                      <button type="submit" name="pausar_despesa" class="btn btn-warning btn-small"><?= $d['pausada'] ? 'Retomar' : 'Pausar' ?></button>
                  </form>
              <?php endif; ?>
          <?php endif; ?>
          <?php if(!$d['pago']): ?>
            <?php if($saldo>=$d['valor']): ?>
            <form method="post" style="display:inline;" onsubmit="return confirmarPagar('<?= $d['id']?>',<?= $d['valor']?>,'<?= $d['origem']?>');">
              <input type="hidden" name="id" value="<?= $d['id'] ?>">
              <input type="hidden" name="valor" value="<?= $d['valor'] ?>">
              <input type="hidden" name="origem" value="<?= htmlspecialchars($d['origem']) ?>">
              <button type="submit" name="pagar" class="btn button-success btn-small">Pagar</button>
            </form>
            <?php else: ?>
              <span class="badge badge-error">Saldo insuficiente</span>
            <?php endif; ?>
          <?php else: ?>
            <span class="badge badge-success">✔</span>
          <?php endif; ?>
        </div>
      </td>
    </tr>
    <?php endforeach; endif; ?>
    </tbody>
    </table>
    </div>
    <div class="totalizador" style="margin-bottom:18px;">Total despesas visíveis: <span id="totalizador">0.00</span> €</div>
    <div style="margin-bottom:24px; display: flex; justify-content: space-between; align-items: center;">
      <div>
        <?php if($page > 1): ?>
          <a href="despesas.php?<?= http_build_query(array_merge($_GET, ['page'=>$page-1])) ?>">← Anterior</a>
        <?php endif; ?>
      </div>
      <div>
        <?php if($page < $totalPages): ?>
          <a href="despesas.php?<?= http_build_query(array_merge($_GET, ['page'=>$page+1])) ?>">Próxima →</a>
        <?php endif; ?>
      </div>
    </div>
    <!-- Modal Adicionar Despesa -->
    <div id="modalAdicionar" class="modal" onclick="if(event.target.id==='modalAdicionar')this.classList.remove('active')">
      <div class="modal-content" onclick="event.stopPropagation()">
        <div class="modal-header" style="display: flex; justify-content: space-between; align-items: center; border-bottom: 1px solid var(--cor-borda); padding-bottom: 10px;">
            <h2>Adicionar Nova Despesa</h2>
            <button class="modal-close close-btn" onclick="document.getElementById('modalAdicionar').classList.remove('active')">×</button>
        </div>
        <form method="post" action="despesas.php" style="margin-top: 15px;">
            <div class="form-group">
                <label>Nome: <input type="text" name="nome" required></label>
            </div>
            <div class="form-group">
                <label>Valor (€): <input type="number" step="0.01" min="0.01" name="valor" required></label>
            </div>
            <div class="form-group">
                <label>Data: <input type="date" name="data" value="<?= date('Y-m-d') ?>"></label>
            </div>
            <div class="form-group">
                <label>Descrição: <input type="text" name="descricao"></label>
            </div>
            <div class="form-group">
                <label><input type="checkbox" name="recorrente" id="recorrente" value="1" onchange="toggleRecorrencia()"> Despesa Recorrente</label>
            </div>
            <div id="recorrencia-opts" style="display:none; margin-top:10px;">
                <div class="form-group">
                    <label>Periodicidade:
                        <select name="periodicidade">
                            <option value="mensal">Mensal</option>
                            <option value="semanal">Semanal</option>
                            <option value="anual">Anual</option>
                        </select>
                    </label>
                </div>
                <div class="form-group">
                    <label>Data de término:
                        <input type="date" name="data_fim_recorrencia">
                    </label>
                </div>
            </div>
            <button type="submit" name="adicionar_despesa" class="btn btn-view" style="width: 100%;">Adicionar Despesa</button>
        </form>
      </div>
    </div>
    <!-- Modal Detalhes -->
    <div id="modal-detalhes" class="modal">
      <div class="modal-content" onclick="event.stopPropagation()">
        <div class="modal-header" style="display: flex; justify-content: space-between; align-items: center; border-bottom: 1px solid var(--cor-borda); padding-bottom: 10px;">
            <h2>Detalhes da despesa</h2>
            <button class="modal-close close-btn" onclick="fecharModal('modal-detalhes')">×</button>
        </div>
        <div style="margin-top:10px;">
            <p><strong>ID:</strong> <span id="det-id"></span></p>
            <p><strong>Nome:</strong> <span id="det-nome"></span></p>
            <p><strong>Valor:</strong> <span id="det-valor"></span> €</p>
            <p><strong>Origem:</strong> <span id="det-origem"></span></p>
            <p><strong>Estado:</strong> <span id="det-estado"></span></p>
            <p><strong>Data de Pagamento:</strong> <span id="det-data"></span></p>
            <p><strong>Recorrente:</strong> <span id="det-recorrente"></span></p>
            <p><strong>Pausada:</strong> <span id="det-pausada"></span></p>
        </div>
      </div>
    </div>
    <!-- Modal Editar -->
    <div id="modal-editar" class="modal">
      <div class="modal-content" onclick="event.stopPropagation()">
        <div class="modal-header" style="display: flex; justify-content: space-between; align-items: center; border-bottom: 1px solid var(--cor-borda); padding-bottom: 10px;">
            <h2>Editar Despesa</h2>
            <button class="modal-close close-btn" onclick="fecharModal('modal-editar')">×</button>
        </div>
        <form method="post" action="despesas.php" id="formEditarDespesa">
            <input type="hidden" name="id" id="edit-id">
            <div class="form-group">
                <label>Nome: <input type="text" name="nome" id="edit-nome" required></label>
            </div>
            <div class="form-group">
                <label>Valor (€): <input type="number" step="0.01" min="0.01" name="valor" id="edit-valor" required></label>
            </div>
            <div class="form-group">
                <label>Data: <input type="date" name="data" id="edit-data" required></label>
            </div>
            <div class="form-group">
                <label>Descrição: <input type="text" name="descricao" id="edit-descricao"></label>
            </div>
            <div class="form-group">
                <label><input type="checkbox" name="recorrente" id="edit-recorrente" value="1" onchange="toggleRecorrenciaEdit()"> Despesa Recorrente</label>
            </div>
            <div id="recorrencia-opts-edit" style="display:none; margin-top:10px;">
                <div class="form-group">
                    <label>Periodicidade:
                        <select name="periodicidade" id="edit-periodicidade">
                            <option value="mensal">Mensal</option>
                            <option value="semanal">Semanal</option>
                            <option value="anual">Anual</option>
                        </select>
                    </label>
                </div>
                <div class="form-group">
                    <label>Data de término:
                        <input type="date" name="data_fim_recorrencia" id="edit-data-fim">
                    </label>
                </div>
                <div class="form-group">
                    <label><input type="checkbox" name="pausada" id="edit-pausada" value="1"> Pausar esta despesa recorrente</label>
                </div>
            </div>
            <button type="submit" name="editar_despesa" class="btn btn-view" style="width: 100%;">Guardar Alterações</button>
        </form>
      </div>
    </div>
    <!-- Modal Eliminar -->
    <div id="modal-eliminar" class="modal">
      <div class="modal-content" onclick="event.stopPropagation()">
        <div class="modal-header" style="display: flex; justify-content: space-between; align-items: center; border-bottom: 1px solid var(--cor-borda); padding-bottom: 10px;">
            <h2>Confirmar Eliminação</h2>
            <button class="modal-close close-btn" onclick="fecharModal('modal-eliminar')">×</button>
        </div>
        <form method="post" action="despesas.php">
            <input type="hidden" name="id" id="delete-id">
            <p>Tem a certeza que deseja eliminar a despesa "<span id="delete-nome"></span>"?</p>
            <button type="submit" name="eliminar_despesa" class="btn button-danger" style="width: 100%;">Eliminar</button>
            <button type="button" class="btn btn-outline" onclick="fecharModal('modal-eliminar')">Cancelar</button>
        </form>
      </div>
    </div>
    <!-- Modal Previsões Futuras -->
    <div id="modal-previsoes" class="modal" style="display:none;">
      <div class="modal-content" onclick="event.stopPropagation()">
        <div class="modal-header" style="display: flex; justify-content: space-between; align-items: center; border-bottom: 1px solid var(--cor-borda); padding-bottom: 10px;">
          <h2>Previsões Futuras</h2>
          <button class="modal-close close-btn" onclick="fecharModal('modal-previsoes')">×</button>
        </div>
        <div id="previsoes-content" style="margin-top:10px;"></div>
      </div>
    </div>
    <!-- Modal Histórico de Pagamentos -->
    <div id="modal-historico" class="modal" style="display:none;">
      <div class="modal-content" onclick="event.stopPropagation()">
        <div class="modal-header" style="display: flex; justify-content: space-between; align-items: center; border-bottom: 1px solid var(--cor-borda); padding-bottom: 10px;">
          <h2>Histórico de Pagamentos</h2>
          <button class="modal-close close-btn" onclick="fecharModal('modal-historico')">×</button>
        </div>
        <div id="historico-content" style="margin-top:10px;"></div>
      </div>
    </div>
    <a href="../admin.php">← Voltar</a>
</div>
</body>
</html>