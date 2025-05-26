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

// --- Adicionar gorjeta ---
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['adicionar_gorjeta'])) {
    $descricao = trim($_POST['descricao']);
    $valor = floatval($_POST['valor']);

    if ($descricao === '' || $valor <= 0) {
        set_flash("Por favor preencha a descrição e um valor válido para a gorjeta.", 'error');
    } else {
        // Inserir receita de gorjeta
        $stmt = $conexao->prepare("INSERT INTO receitas (R_descricao, R_valor, R_data, R_tipo, R_origem, R_origem_id) VALUES (?, ?, CURDATE(), 'gorjeta', 'manual', 0)");
        $stmt->bind_param('sd', $descricao, $valor);
        if ($stmt->execute()) {
            // Atualizar saldo (acrescentar gorjeta)
            $stmt2 = $conexao->prepare("UPDATE conta_virtual SET saldo = saldo + ? WHERE id = 1");
            $stmt2->bind_param('d', $valor);
            $stmt2->execute();
            $stmt2->close();

            set_flash("Gorjeta adicionada com sucesso!");
        } else {
            set_flash("Erro ao adicionar gorjeta.", 'error');
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
</head>
<body>

<h2>Saldo atual: <span class="<?= $saldoAtual >= 0 ? 'saldo-positivo' : 'saldo-negativo'; ?>">€<?= number_format($saldoAtual, 2, ',', '.'); ?></span></h2>

<?php show_flash(); ?>

<a href="admin.php">← Voltar</a>

<h3>Adicionar Gorjeta</h3>
<form method="post" action="receitas.php" style="margin-bottom: 20px;">
    <label>Descrição: <input type="text" name="descricao" required></label>
    &nbsp;&nbsp;
    <label>Valor (€): <input type="number" step="0.01" min="0.01" name="valor" required></label>
    &nbsp;&nbsp;
    <button type="submit" name="adicionar_gorjeta">Adicionar Gorjeta</button>
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
        </tr>
    </thead>
    <tbody>
        <?php if (empty($receitas)): ?>
            <tr><td colspan="6" style="text-align:center;">Nenhuma receita encontrada.</td></tr>
        <?php else: ?>
            <?php foreach ($receitas as $r): ?>
                <tr>
                    <td><?= htmlspecialchars($r['R_id_receita']) ?></td>
                    <td><?= htmlspecialchars($r['R_descricao']) ?></td>
                    <td><?= number_format($r['R_valor'], 2, ',', '.') ?></td>
                    <td><?= date('d/m/Y', strtotime($r['R_data'])) ?></td>
                    <td><?= htmlspecialchars($r['R_tipo']) ?></td>
                    <td><?= htmlspecialchars($r['R_origem']) ?></td>
                </tr>
            <?php endforeach; ?>
        <?php endif; ?>
    </tbody>
</table>

</body>
</html>
