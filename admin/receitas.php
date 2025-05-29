<?php
include "../conexao.php";
session_start();

// Função para mensagens flash
function set_flash($msg, $type = 'success') {
    $_SESSION['flash'] = ['msg' => $msg, 'type' => $type];
}

function show_flash() {
    if (!empty($_SESSION['flash'])) {
        $color = $_SESSION['flash']['type'] === 'error' ? 'red' : 'green';
        echo "<p style='color: $color; font-weight: bold;'>{$_SESSION['flash']['msg']}</p>";
        unset($_SESSION['flash']);
    }
}

// Obter saldo atual da conta_virtual
$saldoQuery = $conexao->query("SELECT saldo FROM conta_virtual WHERE id = 1");
$saldoAtual = $saldoQuery->fetch_assoc()['saldo'] ?? 0;

// Obter lista de hóspedes para dropdown
$hospedesResult = $conexao->query("SELECT H_id_hospede AS id, H_nome AS nome FROM hospedes ORDER BY H_nome ASC");
$hospedes = [];
if ($hospedesResult) {
    while ($row = $hospedesResult->fetch_assoc()) {
        $hospedes[] = $row;
    }
}

// --- Adicionar receita manual ---
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['adicionar_receita'])) {
    $descricao = trim($_POST['descricao']);
    $valor = floatval($_POST['valor']);
    $tipo = $_POST['tipo'] ?? '';
    $origem = $_POST['origem'] ?? '';
    $id_hospede = $_POST['hospede'] ?? null;

    // Validar inputs básicos
    $tipos_validos = ['gorjeta', 'reembolso', 'outro'];
    $origens_validas = ['dinheiro', 'mbway', 'transferencia bancaria'];

    if ($descricao === '' || $valor <= 0) {
        set_flash("Por favor preencha a descrição e um valor válido para a receita.", 'error');
    } elseif (!in_array($tipo, $tipos_validos)) {
        set_flash("Tipo de receita inválido.", 'error');
    } elseif (!in_array($origem, $origens_validas)) {
        set_flash("Origem da receita inválida.", 'error');
    } else {
        // Inserir receita na base de dados
        $stmt = $conexao->prepare("INSERT INTO receitas (R_descricao, R_valor, R_data, R_tipo, R_origem, R_origem_id) VALUES (?, ?, CURDATE(), ?, ?, ?)");
        // Se não escolheram hóspede, usar 0
        $origem_id = is_numeric($id_hospede) ? intval($id_hospede) : 0;
        $stmt->bind_param('sdssi', $descricao, $valor, $tipo, $origem, $origem_id);

        if ($stmt->execute()) {
            // Atualizar saldo (acrescentar valor da receita)
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

// --- Recolher dinheiro ---
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['recolher_dinheiro'])) {
    $valor = floatval($_POST['valor_recolher']);
    
    if ($valor <= 0) {
        set_flash("Por favor insira um valor válido para recolher.", 'error');
    } elseif ($valor > $saldoAtual) {
        set_flash("Não pode recolher mais dinheiro do que o saldo disponível.", 'error');
    } else {
        // Atualizar saldo (subtrair valor recolhido)
        $stmt = $conexao->prepare("UPDATE conta_virtual SET saldo = saldo - ? WHERE id = 1");
        $stmt->bind_param('d', $valor);
        
        if ($stmt->execute()) {
            // Registrar a movimentação
            $descricao = "Recolha de dinheiro";
            $stmt2 = $conexao->prepare("INSERT INTO movimentacoes (tipo, descricao, valor, origem, data) VALUES ('despesa', ?, ?, 'recolha', NOW())");
            $stmt2->bind_param('sd', $descricao, $valor);
            $stmt2->execute();
            $stmt2->close();
            
            set_flash("Recolheu €" . number_format($valor, 2, ',', '.') . " com sucesso!");
        } else {
            set_flash("Erro ao recolher dinheiro.", 'error');
        }
        $stmt->close();
        header("Location: receitas.php");
        exit;
    }
}

// --- Recolher tudo ---
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['recolher_tudo'])) {
    if ($saldoAtual <= 0) {
        set_flash("Não há dinheiro disponível para recolher.", 'error');
    } else {
        // Atualizar saldo (zerar)
        $stmt = $conexao->prepare("UPDATE conta_virtual SET saldo = 0 WHERE id = 1");
        
        if ($stmt->execute()) {
            // Registrar a movimentação
            $descricao = "Recolha total de dinheiro";
            $stmt2 = $conexao->prepare("INSERT INTO movimentacoes (tipo, descricao, valor, origem, data) VALUES ('despesa', ?, ?, 'recolha', NOW())");
            $stmt2->bind_param('sd', $descricao, $saldoAtual);
            $stmt2->execute();
            $stmt2->close();
            
            set_flash("Recolheu todo o dinheiro (€" . number_format($saldoAtual, 2, ',', '.') . ") com sucesso!");
        } else {
            set_flash("Erro ao recolher todo o dinheiro.", 'error');
        }
        $stmt->close();
        header("Location: receitas.php");
        exit;
    }
}

// --- Buscar receitas da tabela receitas ---
$sql = "SELECT * FROM receitas ORDER BY R_data DESC, R_id_receita DESC";
$result = $conexao->query($sql);
$receitas = [];
if ($result) {
    while ($row = $result->fetch_assoc()) {
        $receitas[] = $row;
    }
}
?>

<!DOCTYPE html>
<html lang="pt-PT">
<head>
    <meta charset="UTF-8" />
    <title>Receitas</title>
    <link rel="stylesheet" href="admin.css" />
    <style>
        .saldo-positivo { color: green; }
        .saldo-negativo { color: red; }
        .recolher-container {
            margin: 20px 0;
            padding: 15px;
            background: #f5f5f5;
            border-radius: 5px;
        }
        .recolher-container input {
            padding: 5px;
            margin-right: 10px;
        }
        .recolher-container button {
            padding: 5px 10px;
            margin-right: 10px;
        }
    </style>
</head>
<body>

<h2>Saldo atual: <span class="<?= $saldoAtual >= 0 ? 'saldo-positivo' : 'saldo-negativo'; ?>">€<?= number_format($saldoAtual, 2, ',', '.'); ?></span></h2>

<?php show_flash(); ?>

<a href="admin.php">← Voltar</a>

<!-- Formulário para recolher dinheiro -->
<div class="recolher-container">
    <h3>Recolher Dinheiro</h3>
    <form method="post" action="receitas.php">
        <label>Valor a recolher (€): 
            <input type="number" step="0.01" min="0.01" name="valor_recolher" value="<?= number_format($saldoAtual, 2, '.', '') ?>" required>
        </label>
        <button type="submit" name="recolher_dinheiro">Recolher</button>
        <button type="submit" name="recolher_tudo">Recolher Tudo</button>
    </form>
</div>

<h3>Adicionar Receita Manual</h3>
<form method="post" action="receitas.php" style="margin-bottom: 20px;">
    <label>Descrição: <input type="text" name="descricao" required></label>
    &nbsp;&nbsp;
    <label>Valor (€): <input type="number" step="0.01" min="0.01" name="valor" required></label>
    <br><br>
    <label>Tipo:
        <select name="tipo" required>
            <option value="">-- Selecionar --</option>
            <option value="gorjeta">Gorjeta</option>
            <option value="reembolso">Reembolso</option>
            <option value="outro">Outro</option>
        </select>
    </label>
    &nbsp;&nbsp;
    <label>Origem:
        <select name="origem" required>
            <option value="">-- Selecionar --</option>
            <option value="dinheiro">Dinheiro</option>
            <option value="mbway">MB Way</option>
            <option value="transferencia bancaria">Transferência Bancária</option>
        </select>
    </label>
    <br><br>
    <label>Hóspede (opcional):
        <select name="hospede">
            <option value="0">Nenhum</option>
            <?php foreach ($hospedes as $h): ?>
                <option value="<?= $h['id'] ?>"><?= htmlspecialchars($h['nome']) ?></option>
            <?php endforeach; ?>
        </select>
    </label>
    &nbsp;&nbsp;
    <button type="submit" name="adicionar_receita">Adicionar Receita</button>
</form>

<h3>Lista de Receitas</h3>
<table border="1" cellpadding="8" style="border-collapse: collapse; width: 100%;">
    <thead>
        <tr style="background: #eee;">
            <th>ID</th>
            <th>Descrição</th>
            <th>Valor (€)</th>
            <th>Data</th>
            <th>Tipo</th>
            <th>Origem</th>
            <th>Hóspede Associado</th>
        </tr>
    </thead>
    <tbody>
        <?php if (empty($receitas)): ?>
            <tr><td colspan="7" style="text-align:center;">Nenhuma receita encontrada.</td></tr>
        <?php else: ?>
            <?php
            // Para mostrar nome do hóspede associado, vamos buscar a lista dos hóspedes num array para acesso rápido
            $hospedesMap = [];
            foreach ($hospedes as $h) {
                $hospedesMap[$h['id']] = $h['nome'];
            }
            ?>
            <?php foreach ($receitas as $r): ?>
                <tr>
                    <td><?= htmlspecialchars($r['R_id_receita']) ?></td>
                    <td><?= htmlspecialchars($r['R_descricao']) ?></td>
                    <td><?= number_format($r['R_valor'], 2, ',', '.') ?></td>
                    <td><?= date('d/m/Y', strtotime($r['R_data'])) ?></td>
                    <td><?= htmlspecialchars($r['R_tipo']) ?></td>
                    <td><?= htmlspecialchars($r['R_origem']) ?></td>
                    <td>
                        <?= isset($hospedesMap[$r['R_origem_id']]) ? htmlspecialchars($hospedesMap[$r['R_origem_id']]) : '-' ?>
                    </td>
                </tr>
            <?php endforeach; ?>
        <?php endif; ?>
    </tbody>
</table>

</body>
</html>