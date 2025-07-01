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
    $tipos_validos = ['gorjeta', 'reembolso', 'outro'];
    $origens_validas = ['dinheiro', 'mbway', 'transferencia bancaria'];

    if ($descricao === '' || $valor <= 0) {
        set_flash("Por favor preencha a descrição e um valor válido.", 'error');
    } elseif (!in_array($tipo, $tipos_validos) || !in_array($origem, $origens_validas)) {
        set_flash("Tipo ou origem inválido(s).", 'error');
    } else {
        $stmt = $conexao->prepare("INSERT INTO receitas (R_descricao, R_valor, R_data, R_tipo, R_origem, R_origem_id, recorrente, periodicidade, data_fim_recorrencia) VALUES (?, ?, CURDATE(), ?, ?, ?, ?, ?, ?)");
        $origem_id = is_numeric($id_hospede) ? intval($id_hospede) : 0;
        $stmt->bind_param('sdssiiss', $descricao, $valor, $tipo, $origem, $origem_id, $recorrente, $periodicidade, $data_fim_recorrencia);
        if ($stmt->execute()) {
            $stmt2 = $conexao->prepare("UPDATE conta_virtual SET saldo = saldo + ? WHERE id = 1");
            $stmt2->bind_param('d', $valor);
            $stmt2->execute();
            $stmt2->close();
            set_flash("Receita adicionada com sucesso!");
        } else {
            set_flash("Erro ao adicionar receita.", 'error');
        }
        $stmt->close();
        header("Location: receitas.php");
        exit;
    }
}

// Obter receitas
$sql = "SELECT * FROM receitas ORDER BY R_data DESC, R_id_receita DESC";
$result = $conexao->query($sql);
$receitas = [];
if ($result) {
    while ($row = $result->fetch_assoc()) {
        $receitas[] = $row;
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
    <title>Receitas</title>
    <link rel="stylesheet" href="global.css" />
    <style>
        .saldo-positivo { color: green; }
        .saldo-negativo { color: red; }
        .modal {
            display: none; position: fixed; top: 0; left: 0;
            width: 100%; height: 100%; background: rgba(0,0,0,0.5);
            justify-content: center; align-items: center;
            z-index: 1000;
        }
        .modal-content {
            background: white; padding: 20px; border-radius: 8px; min-width: 320px;
            max-width: 90%; max-height: 80vh; overflow-y: auto;
        }
        .modal-header {
            font-weight: bold; margin-bottom: 10px;
        }
        .close-btn {
            float: right; cursor: pointer; font-size: 20px;
        }
        table {
            border-collapse: collapse; width: 100%;
        }
        table th, table td {
            border: 1px solid #ccc; padding: 8px; text-align: left;
        }
        table th {
            background-color: #eee;
        }
        button {
            cursor: pointer;
        }
    </style>
</head>
<body>
    <div style="display: flex; align-items: center; gap: 10px;">
        <img src="https://img.icons8.com/?size=100&id=p2scHNLP9nSb&format=png&color=000000" alt="Ícone Hóspedes" style="height: 50px;">
        <h1>Todos as Receitas </h1>
    </div>
<h3>Saldo atual: 
    <span class="<?= $saldoAtual >= 0 ? 'saldo-positivo' : 'saldo-negativo'; ?>">
        €<?= number_format($saldoAtual, 2, ',', '.'); ?>
    </span>
</h3>

<?php show_flash(); ?>

<!-- Adicionar Receita -->
<h3>Adicionar Receita Manual</h3>
<form method="post" action="receitas.php" style="margin-bottom: 20px;">
    <label>Descrição: <input type="text" name="descricao" required></label>
    <label>Valor (€): <input type="number" step="0.01" min="0.01" name="valor" required></label><br><br>
    <label>Tipo:
        <select name="tipo" required>
            <option value="">-- Selecionar --</option>
            <option value="gorjeta">Gorjeta</option>
            <option value="reembolso">Reembolso</option>
            <option value="outro">Outro</option>
        </select>
    </label>
    <label>Origem:
        <select name="origem" required>
            <option value="">-- Selecionar --</option>
            <option value="dinheiro">Dinheiro</option>
            <option value="mbway">MB Way</option>
            <option value="transferencia bancaria">Transferência Bancária</option>
        </select>
    </label><br><br>
    <label>Hóspede (opcional):
        <select name="hospede">
            <option value="0">Nenhum</option>
            <?php foreach ($hospedes as $h): ?>
                <option value="<?= $h['id'] ?>"><?= htmlspecialchars($h['nome']) ?></option>
            <?php endforeach; ?>
        </select>
    </label>
    <br><br>
    <label><input type="checkbox" name="recorrente" value="1" id="recorrente_cb" onchange="document.getElementById('recorrencia_opts').style.display=this.checked?'block':'none'"> Receita recorrente</label>
    <div id="recorrencia_opts" style="display:none; margin-top:10px;">
        <label>Periodicidade:
            <select name="periodicidade">
                <option value="mensal">Mensal</option>
                <option value="semanal">Semanal</option>
                <option value="anual">Anual</option>
            </select>
        </label>
        <label>Data de término:
            <input type="date" name="data_fim_recorrencia">
        </label>
    </div>
    <button type="submit" name="adicionar_receita">Adicionar Receita</button>
</form>
<a href="admin.php">← Voltar</a>

<!-- Tabela de Receitas -->
<table>
    <thead>
        <tr>
            <th>ID</th>
            <th>Descrição</th>
            <th>Data</th>
            <th>Detalhes</th>
        </tr>
    </thead>
    <tbody>
        <?php if (empty($receitas)): ?>
            <tr><td colspan="4" style="text-align:center;">Nenhuma receita encontrada.</td></tr>
        <?php else: ?>
            <?php foreach ($receitas as $r): ?>
                <tr>
                    <td><?= $r['R_id_receita'] ?></td>
                    <td><?= htmlspecialchars($r['R_descricao']) ?></td>
                    <td><?= date('d/m/Y', strtotime($r['R_data'])) ?></td>
                    <td><button onclick='abrirModal(<?= htmlspecialchars(json_encode($r)) ?>)'>Ver</button></td>
                </tr>
            <?php endforeach; ?>
        <?php endif; ?>
    </tbody>
</table>
<a href="admin.php">← Voltar
<!-- Modal -->
<div id="modal" class="modal" onclick="fecharModal(event)">
    <div class="modal-content" onclick="event.stopPropagation()">
        <span class="close-btn" onclick="fecharModal()">×</span>
        <div class="modal-header">Detalhes da Receita</div>
        <div id="modal-body"></div>
    </div>
</div>

<script>
function abrirModal(receita) {
    const body = document.getElementById('modal-body');
    const hospede = <?= json_encode($hospedesMap) ?>;
    body.innerHTML = `
        <p><strong>ID:</strong> ${receita.R_id_receita}</p>
        <p><strong>Descrição:</strong> ${receita.R_descricao}</p>
        <p><strong>Valor:</strong> €${parseFloat(receita.R_valor).toFixed(2).replace('.', ',')}</p>
        <p><strong>Data:</strong> ${new Date(receita.R_data).toLocaleDateString('pt-PT')}</p>
        <p><strong>Tipo:</strong> ${receita.R_tipo}</p>
        <p><strong>Origem:</strong> ${receita.R_origem}</p>
        <p><strong>Hóspede:</strong> ${hospede[receita.R_origem_id] || '-'}</p>
    `;
    document.getElementById('modal').style.display = 'flex';
}
function fecharModal(event) {
    if (!event || event.target.id === 'modal' || event.target.classList.contains('close-btn')) {
        document.getElementById('modal').style.display = 'none';
    }
}
</script>

</body>
</html>
