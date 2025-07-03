<?php
include "../../conexao.php";
session_start();

// Flash message estilizada
function set_flash($msg, $type = 'success') {
    $_SESSION['flash'] = ['msg' => $msg, 'type' => $type];
}

function show_flash() {
    if (!empty($_SESSION['flash'])) {
        $type = $_SESSION['flash']['type'];
        $color = $type === 'error' ? '#f8d7da' : '#d4edda';
        $border = $type === 'error' ? '#f5c6cb' : '#c3e6cb';
        $text = $type === 'error' ? '#721c24' : '#155724';
        echo "<div style='background:$color;border:1px solid $border;padding:10px;margin:10px 0;color:$text;border-radius:5px;font-weight:bold'>{$_SESSION['flash']['msg']}</div>";
        unset($_SESSION['flash']);
    }
}

// Obter saldo atual
$saldoQuery = $conexao->query("SELECT saldo FROM conta_virtual WHERE id = 1");
$saldoAtual = $saldoQuery->fetch_assoc()['saldo'] ?? 0;

// Obter hóspedes
$hospedesResult = $conexao->query("SELECT H_id_hospede AS id, H_nome AS nome FROM hospedes ORDER BY H_nome ASC");
$hospedes = [];
while ($row = $hospedesResult->fetch_assoc()) {
    $hospedes[] = $row;
}

// Adicionar receita
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['adicionar_receita'])) {
    $descricao = trim($_POST['descricao']);
    $valor = floatval($_POST['valor']);
    $tipo = $_POST['tipo'] ?? '';
    $origem = $_POST['origem'] ?? '';
    $id_hospede = $_POST['hospede'] ?? null;
    $recorrente = isset($_POST['recorrente']) ? 1 : 0;
    $periodicidade = $_POST['periodicidade'] ?? null;
    $data_fim_recorrencia = $_POST['data_fim_recorrencia'] ?? null;
    if (empty($data_fim_recorrencia)) {
        $data_fim_recorrencia = null;
    }
    $tipos_validos = ['gorjeta', 'reembolso', 'outro'];
    $origens_validas = ['dinheiro', 'mbway', 'transferencia bancaria'];

    if ($descricao === '' || $valor <= 0) {
        set_flash("Por favor preencha a descrição e um valor válido.", 'error');
    } elseif (!in_array($tipo, $tipos_validos) || !in_array($origem, $origens_validas)) {
        set_flash("Tipo ou origem inválido(s).", 'error');
    } else {
        $stmt = $conexao->prepare("INSERT INTO receitas (R_descricao, R_valor, R_data, R_tipo, R_origem, R_origem_id, recorrente, periodicidade, data_fim_recorrencia, R_recebida) VALUES (?, ?, CURDATE(), ?, ?, ?, ?, ?, ?, 0)");
        $origem_id = is_numeric($id_hospede) ? intval($id_hospede) : 0;
        $stmt->bind_param('sdssiiss', $descricao, $valor, $tipo, $origem, $origem_id, $recorrente, $periodicidade, $data_fim_recorrencia);
        if ($stmt->execute()) {
            set_flash("Receita adicionada com sucesso! Aguarda recebimento.");
        } else {
            set_flash("Erro ao adicionar receita.", 'error');
        }
        $stmt->close();
        header("Location: receitas.php");
        exit;
    }
}

// Receber receita (manual ou reserva)
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['receber_receita'])) {
    $id = intval($_POST['id_receita']);
    $tipo = $_POST['tipo_receita'] ?? '';
    if ($tipo === 'manual') {
        $q = $conexao->query("SELECT R_valor, R_recebida FROM receitas WHERE R_id_receita = $id");
        $row = $q && $q->num_rows ? $q->fetch_assoc() : null;
        if ($row && !$row['R_recebida']) {
            $valor = floatval($row['R_valor']);
            $conexao->query("UPDATE receitas SET R_recebida = 1 WHERE R_id_receita = $id");
            $conexao->query("UPDATE conta_virtual SET saldo = saldo + $valor WHERE id = 1");
            set_flash("Receita recebida e saldo atualizado!");
        } else {
            set_flash("Receita já recebida ou não encontrada.", 'error');
        }
    } elseif ($tipo === 'reserva') {
        $q = $conexao->query("SELECT R_preco_total, R_recebida FROM reservas WHERE R_id_reserva = $id");
        $row = $q && $q->num_rows ? $q->fetch_assoc() : null;
        if ($row && !$row['R_recebida']) {
            $valor = floatval($row['R_preco_total']);
            $conexao->query("UPDATE reservas SET R_recebida = 1 WHERE R_id_reserva = $id");
            $conexao->query("UPDATE conta_virtual SET saldo = saldo + $valor WHERE id = 1");
            set_flash("Receita de reserva recebida e saldo atualizado!");
        } else {
            set_flash("Receita de reserva já recebida ou não encontrada.", 'error');
        }
    } else {
        set_flash("Tipo de receita inválido.", 'error');
    }
    header("Location: receitas.php");
    exit;
}

// Editar receita
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['editar_receita'])) {
    $id = intval($_POST['id_receita']);
    $descricao = trim($_POST['descricao']);
    $valor = floatval($_POST['valor']);
    $tipo = $_POST['tipo'] ?? '';
    $origem = $_POST['origem'] ?? '';
    $id_hospede = $_POST['hospede'] ?? null;
    $recorrente = isset($_POST['recorrente']) ? 1 : 0;
    $periodicidade = $_POST['periodicidade'] ?? null;
    $data_fim_recorrencia = $_POST['data_fim_recorrencia'] ?? null;
    if (empty($data_fim_recorrencia)) {
        $data_fim_recorrencia = null;
    }
    $tipos_validos = ['gorjeta', 'reembolso', 'outro'];
    $origens_validas = ['dinheiro', 'mbway', 'transferencia bancaria'];
    if ($descricao === '' || $valor <= 0) {
        set_flash("Por favor preencha a descrição e um valor válido.", 'error');
    } elseif (!in_array($tipo, $tipos_validos) || !in_array($origem, $origens_validas)) {
        set_flash("Tipo ou origem inválido(s).", 'error');
    } else {
        $origem_id = is_numeric($id_hospede) ? intval($id_hospede) : 0;
        $stmt = $conexao->prepare("UPDATE receitas SET R_descricao=?, R_valor=?, R_tipo=?, R_origem=?, R_origem_id=?, recorrente=?, periodicidade=?, data_fim_recorrencia=? WHERE R_id_receita=?");
        $stmt->bind_param('sdssiissi', $descricao, $valor, $tipo, $origem, $origem_id, $recorrente, $periodicidade, $data_fim_recorrencia, $id);
        if ($stmt->execute()) {
            set_flash("Receita editada com sucesso!");
        } else {
            set_flash("Erro ao editar receita.", 'error');
        }
        $stmt->close();
        header("Location: receitas.php");
        exit;
    }
}

// Eliminar receita
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['eliminar_receita'])) {
    $id = intval($_POST['id_receita']);
    // Recupera valor para ajustar saldo
    $q = $conexao->query("SELECT R_valor FROM receitas WHERE R_id_receita = $id");
    $valor = $q && $q->num_rows ? floatval($q->fetch_assoc()['R_valor']) : 0;
    $conexao->query("DELETE FROM receitas WHERE R_id_receita = $id");
    if ($conexao->affected_rows > 0) {
        $conexao->query("UPDATE conta_virtual SET saldo = saldo - $valor WHERE id = 1");
        set_flash("Receita eliminada com sucesso!");
    } else {
        set_flash("Erro ao eliminar receita.", 'error');
    }
    header("Location: receitas.php");
    exit;
}

// Obter todas as receitas e reservas confirmadas
$sql = "SELECT 
            R_id_receita AS id,
            R_descricao AS descricao,
            R_valor AS valor,
            R_data AS data,
            R_tipo AS tipo,
            'manual' AS origem,
            NULL AS hospede_nome,
            R_recebida
        FROM receitas
        
        UNION ALL
        
        SELECT 
            R_id_reserva AS id,
            CONCAT('Reserva #', R_id_reserva) AS descricao,
            R_preco_total AS valor,
            R_data_checkin AS data,
            'reserva' AS tipo,
            'reserva' AS origem,
            (SELECT H_nome FROM hospedes WHERE H_id_hospede = reservas.R_id_hospede) AS hospede_nome,
            R_recebida
        FROM reservas
        WHERE R_estado = 'confirmada'
        
        ORDER BY data DESC, id DESC";

$result = $conexao->query($sql);
$transacoes = [];
if ($result) {
    while ($row = $result->fetch_assoc()) {
        $transacoes[] = $row;
    }
}

$hospedesMap = [];
foreach ($hospedes as $h) {
    $hospedesMap[$h['id']] = $h['nome'];
}
?>
<!DOCTYPE html>
<html lang="pt-PT">
<head>
    <meta charset="UTF-8" />
    <title>Receitas e Reservas</title>
    <link rel="stylesheet" href="../global.css" />
    <style>
        .receitas-table {
            width: 100%;
            border-collapse: collapse;
            background: white;
            border-radius: 10px;
            overflow: hidden;
            box-shadow: 0 2px 10px rgba(0,0,0,0.05);
            margin-top: 20px;
        }
        .receitas-table th {
            background: var(--cor-primaria);
            color: white;
            padding: 12px 15px;
            text-align: left;
            font-weight: 500;
        }
        .receitas-table td {
            padding: 12px 15px;
            border-bottom: 1px solid var(--cor-borda-clara);
            vertical-align: middle;
        }
        .receitas-table tr:last-child td {
            border-bottom: none;
        }
        .receitas-table tr:hover {
            background: var(--cor-table-row-hover);
        }
    </style>
</head>
<body>
    <div class="admin-container">
        <div style="display: flex; align-items: center; gap: 10px; margin-bottom: 10px;">
            <img src="https://img.icons8.com/?size=100&id=p2scHNLP9nSb&format=png&color=000000" alt="Ícone Hóspedes" style="height: 50px;">
            <h1>Receitas e Reservas</h1>
        </div>
        <h3>Saldo atual: 
            <span class="<?= $saldoAtual >= 0 ? 'saldo-positivo' : 'saldo-negativo'; ?> saldo-valor">
                €<?= number_format($saldoAtual, 2, ',', '.'); ?>
            </span>
        </h3>
        <?php show_flash(); ?>
        <button class="btn btn-view btn-add" onclick="abrirModalAdicionar()">+ Adicionar Receita Manual</button>
        <!-- Tabela de Receitas e Reservas -->
        <div class="table-responsive">
        <table class="receitas-table">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Descrição</th>
                    <th>Valor</th>
                    <th>Data</th>
                    <th>Tipo</th>
                    <th>Hóspede</th>
                    <th class="acao">Ações</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($transacoes)): ?>
                    <tr><td colspan="7" class="text-center">Nenhuma transação encontrada.</td></tr>
                <?php else: ?>
                    <?php foreach ($transacoes as $t): ?>
                        <tr>
                            <td><?= $t['id'] ?></td>
                            <td><?= htmlspecialchars($t['descricao']) ?></td>
                            <td>€<?= number_format($t['valor'], 2, ',', '.') ?></td>
                            <td><?= date('d/m/Y', strtotime($t['data'])) ?></td>
                            <td>
                                <span class="badge <?= $t['origem'] === 'reserva' ? 'badge-success' : 'badge-info' ?>">
                                    <?= $t['origem'] === 'reserva' ? 'Reserva' : 'Receita Manual' ?>
                                </span>
                            </td>
                            <td><?= htmlspecialchars($t['hospede_nome'] ?? '-') ?></td>
                            <td class="actions acao">
                                <button class="btn btn-view" onclick='abrirModal(<?= htmlspecialchars(json_encode($t)) ?>)'>Ver</button>
                                <?php if ($t['origem'] === 'manual'): ?>
                                    <button class="btn btn-view" onclick='abrirModalEditar(<?= htmlspecialchars(json_encode($t)) ?>)'>Editar</button>
                                <?php endif; ?>
                                <button class="btn button-danger" onclick='abrirModalEliminar(<?= $t['id'] ?>)'>Eliminar</button>
                                <?php if (empty($t['R_recebida']) || $t['R_recebida'] == 0): ?>
                                    <form method="post" style="display:inline;">
                                        <input type="hidden" name="id_receita" value="<?= $t['id'] ?>">
                                        <input type="hidden" name="tipo_receita" value="<?= $t['origem'] ?>">
                                        <button type="submit" name="receber_receita" class="btn button-success">Receber</button>
                                    </form>
                                <?php else: ?>
                                    <span class="badge badge-success">Recebida</span>
                                <?php endif; ?>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
        </div>
        <a href="../admin.php">← Voltar</a>
    </div>
    <!-- Modal para adicionar receita -->
    <div id="modalAdicionar" class="modal" onclick="fecharModalAdicionar(event)">
        <div class="modal-content" onclick="event.stopPropagation()">
            <div class="modal-header" style="display: flex; justify-content: space-between; align-items: center; border-bottom: 1px solid var(--cor-borda); padding-bottom: 10px;">
                <h2>Adicionar Receita Manual</h2>
                <button class="modal-close close-btn" onclick="fecharModalAdicionar()">×</button>
            </div>
            <form method="post" action="receitas.php" style="margin-top: 15px;">
                <div class="form-group">
                    <label>Descrição: 
                        <input type="text" name="descricao" required>
                    </label>
                </div>
                <div class="form-group">
                    <label>Valor (€): 
                        <input type="number" step="0.01" min="0.01" name="valor" required>
                    </label>
                </div>
                <div class="form-group" style="display: flex; gap: 15px;">
                    <div style="flex: 1;">
                        <label>Tipo:
                            <select name="tipo" required>
                                <option value="">-- Selecionar --</option>
                                <option value="gorjeta">Gorjeta</option>
                                <option value="reembolso">Reembolso</option>
                                <option value="outro">Outro</option>
                            </select>
                        </label>
                    </div>
                    <div style="flex: 1;">
                        <label>Origem:
                            <select name="origem" required>
                                <option value="">-- Selecionar --</option>
                                <option value="dinheiro">Dinheiro</option>
                                <option value="mbway">MB Way</option>
                                <option value="transferencia bancaria">Transferência Bancária</option>
                            </select>
                        </label>
                    </div>
                </div>
                <div class="form-group">
                    <label>Hóspede (opcional):
                        <select name="hospede">
                            <option value="0">Nenhum</option>
                            <?php foreach ($hospedes as $h): ?>
                                <option value="<?= $h['id'] ?>"><?= htmlspecialchars($h['nome']) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </label>
                </div>
                <div class="form-group">
                    <label>
                        <input type="checkbox" name="recorrente" value="1" id="recorrente_cb" onchange="document.getElementById('recorrencia_opts').style.display=this.checked?'block':'none'"> 
                        Receita recorrente
                    </label>
                    <div id="recorrencia_opts" style="display:none; margin-top:10px; background: #f5f5f5; padding: 10px; border-radius: 5px;">
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
                </div>
                <button type="submit" name="adicionar_receita" class="btn btn-view" style="width: 100%;">Adicionar Receita</button>
            </form>
        </div>
    </div>
    <!-- Modal para visualizar detalhes -->
    <div id="modal" class="modal" onclick="fecharModal(event)">
        <div class="modal-content" onclick="event.stopPropagation()">
            <div class="modal-header" style="display: flex; justify-content: space-between; align-items: center; border-bottom: 1px solid var(--cor-borda); padding-bottom: 10px;">
                <h2>Detalhes da Transação</h2>
                <button class="modal-close close-btn" onclick="fecharModal()">×</button>
            </div>
            <div id="modal-body"></div>
        </div>
    </div>
    <!-- Modal Editar Receita -->
    <div id="modalEditar" class="modal" onclick="fecharModalEditar(event)">
        <div class="modal-content" onclick="event.stopPropagation()">
            <div class="modal-header" style="display: flex; justify-content: space-between; align-items: center; border-bottom: 1px solid var(--cor-borda); padding-bottom: 10px;">
                <h2>Editar Receita</h2>
                <button class="modal-close close-btn" onclick="fecharModalEditar()">×</button>
            </div>
            <form method="post" action="receitas.php" id="formEditarReceita">
                <input type="hidden" name="id_receita" id="editar_id_receita">
                <div class="form-group">
                    <label>Descrição:
                        <input type="text" name="descricao" id="editar_descricao" required>
                    </label>
                </div>
                <div class="form-group">
                    <label>Valor (€):
                        <input type="number" step="0.01" min="0.01" name="valor" id="editar_valor" required>
                    </label>
                </div>
                <div class="form-group" style="display: flex; gap: 15px;">
                    <div style="flex: 1;">
                        <label>Tipo:
                            <select name="tipo" id="editar_tipo" required>
                                <option value="">-- Selecionar --</option>
                                <option value="gorjeta">Gorjeta</option>
                                <option value="reembolso">Reembolso</option>
                                <option value="outro">Outro</option>
                            </select>
                        </label>
                    </div>
                    <div style="flex: 1;">
                        <label>Origem:
                            <select name="origem" id="editar_origem" required>
                                <option value="">-- Selecionar --</option>
                                <option value="dinheiro">Dinheiro</option>
                                <option value="mbway">MB Way</option>
                                <option value="transferencia bancaria">Transferência Bancária</option>
                            </select>
                        </label>
                    </div>
                </div>
                <div class="form-group">
                    <label>Hóspede (opcional):
                        <select name="hospede" id="editar_hospede">
                            <option value="0">Nenhum</option>
                            <?php foreach ($hospedes as $h): ?>
                                <option value="<?= $h['id'] ?>"><?= htmlspecialchars($h['nome']) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </label>
                </div>
                <div class="form-group">
                    <label>
                        <input type="checkbox" name="recorrente" value="1" id="editar_recorrente_cb" onchange="document.getElementById('editar_recorrencia_opts').style.display=this.checked?'block':'none'">
                        Receita recorrente
                    </label>
                    <div id="editar_recorrencia_opts" style="display:none; margin-top:10px; background: #f5f5f5; padding: 10px; border-radius: 5px;">
                        <div class="form-group">
                            <label>Periodicidade:
                                <select name="periodicidade" id="editar_periodicidade">
                                    <option value="mensal">Mensal</option>
                                    <option value="semanal">Semanal</option>
                                    <option value="anual">Anual</option>
                                </select>
                            </label>
                        </div>
                        <div class="form-group">
                            <label>Data de término:
                                <input type="date" name="data_fim_recorrencia" id="editar_data_fim_recorrencia">
                            </label>
                        </div>
                    </div>
                </div>
                <button type="submit" name="editar_receita" class="btn btn-view" style="width: 100%;">Salvar Alterações</button>
            </form>
        </div>
    </div>
    <!-- Modal Eliminar Receita -->
    <div id="modalEliminar" class="modal" onclick="fecharModalEliminar(event)">
        <div class="modal-content" onclick="event.stopPropagation()">
            <div class="modal-header" style="display: flex; justify-content: space-between; align-items: center; border-bottom: 1px solid var(--cor-borda); padding-bottom: 10px;">
                <h2>Eliminar Receita</h2>
                <button class="modal-close close-btn" onclick="fecharModalEliminar()">×</button>
            </div>
            <form method="post" action="receitas.php">
                <input type="hidden" name="id_receita" id="eliminar_id_receita">
                <p>Tem certeza que deseja eliminar esta receita? Esta ação não pode ser desfeita.</p>
                <button type="submit" name="eliminar_receita" class="btn button-danger" style="width: 100%;">Eliminar</button>
            </form>
        </div>
    </div>
    <script>
    function abrirModalAdicionar() {
        document.getElementById('modalAdicionar').classList.add('active');
    }
    function fecharModalAdicionar(event) {
        if (!event || event.target.id === 'modalAdicionar' || event.target.classList.contains('close-btn')) {
            document.getElementById('modalAdicionar').classList.remove('active');
        }
    }
    function abrirModal(transacao) {
        const body = document.getElementById('modal-body');
        const hospede = <?= json_encode($hospedesMap) ?>;
        let tipo = transacao.origem === 'reserva' ? 'Reserva' : 'Receita Manual';
        let tipoDetalhe = transacao.tipo === 'reserva' ? 'Reserva' : 
                         transacao.tipo === 'gorjeta' ? 'Gorjeta' :
                         transacao.tipo === 'reembolso' ? 'Reembolso' : 'Outro';
        body.innerHTML = `
            <p><strong>ID:</strong> ${transacao.id}</p>
            <p><strong>Descrição:</strong> ${transacao.descricao}</p>
            <p><strong>Valor:</strong> €${parseFloat(transacao.valor).toFixed(2).replace('.', ',')}</p>
            <p><strong>Data:</strong> ${new Date(transacao.data).toLocaleDateString('pt-PT')}</p>
            <p><strong>Tipo:</strong> ${tipo} (${tipoDetalhe})</p>
            ${transacao.origem === 'manual' ? `<p><strong>Origem:</strong> ${transacao.R_origem || '-'}</p>` : ''}
            <p><strong>Hóspede:</strong> ${transacao.hospede_nome || '-'}</p>
        `;
        document.getElementById('modal').classList.add('active');
    }
    function fecharModal(event) {
        if (!event || event.target.id === 'modal' || event.target.classList.contains('close-btn')) {
            document.getElementById('modal').classList.remove('active');
        }
    }
    function abrirModalEditar(transacao) {
        document.getElementById('modalEditar').classList.add('active');
        document.getElementById('editar_id_receita').value = transacao.id;
        document.getElementById('editar_descricao').value = transacao.descricao || '';
        document.getElementById('editar_valor').value = transacao.valor || '';
        document.getElementById('editar_tipo').value = transacao.tipo || '';
        document.getElementById('editar_origem').value = transacao.R_origem || transacao.origem || '';
        document.getElementById('editar_hospede').value = transacao.R_origem_id || transacao.hospede_id || 0;
        let recorrente = transacao.recorrente == 1 || transacao.recorrente === '1';
        document.getElementById('editar_recorrente_cb').checked = recorrente;
        document.getElementById('editar_recorrencia_opts').style.display = recorrente ? 'block' : 'none';
        document.getElementById('editar_periodicidade').value = transacao.periodicidade || 'mensal';
        document.getElementById('editar_data_fim_recorrencia').value = transacao.data_fim_recorrencia || '';
    }
    function fecharModalEditar(event) {
        if (!event || event.target.id === 'modalEditar' || event.target.classList.contains('close-btn')) {
            document.getElementById('modalEditar').classList.remove('active');
        }
    }
    function abrirModalEliminar(id) {
        document.getElementById('modalEliminar').classList.add('active');
        document.getElementById('eliminar_id_receita').value = id;
    }
    function fecharModalEliminar(event) {
        if (!event || event.target.id === 'modalEliminar' || event.target.classList.contains('close-btn')) {
            document.getElementById('modalEliminar').classList.remove('active');
        }
    }
    </script>
</body>
</html>