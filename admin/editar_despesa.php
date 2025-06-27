<?php
require '../conexao.php';

$id = $_GET['id'];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nome = limparDados($_POST['nome']);
    $valor = limparDados($_POST['valor']);
    $data = limparDados($_POST['data']);
    $descricao = limparDados($_POST['descricao']);
    $recorrente = isset($_POST['recorrente']) ? 1 : 0;
    $periodicidade = $_POST['periodicidade'] ?? null;
    $data_fim_recorrencia = $_POST['data_fim_recorrencia'] ?? null;

    // Validação de valor
    if (!is_numeric($valor) || $valor <= 0) {
        $erro = "O valor deve ser um número positivo.";
    } else {
        $stmt = $conexao->prepare("UPDATE despesas SET D_nome=?, D_valor=?, D_data=?, D_descricao=?, recorrente=?, periodicidade=?, data_fim_recorrencia=? WHERE D_id_despeza=?");
        $stmt->bind_param("sdssissi", $nome, $valor, $data, $descricao, $recorrente, $periodicidade, $data_fim_recorrencia, $id);
        if ($stmt->execute()) {
            header("Location: despesas.php?sucesso=2");
            exit();
        } else {
            $erro = "Erro ao atualizar despesa: " . $conexao->error;
        }
    }
}

$stmt = $conexao->prepare("SELECT * FROM despesas WHERE D_id_despeza=?");
$stmt->bind_param("i", $id);
$stmt->execute();
$resultado = $stmt->get_result();
$despesa = $resultado->fetch_assoc();
?>
<!DOCTYPE html>
<html lang="pt">
<head>
        <link rel="icon" type="image/png" sizes="32x32" href="../assets/logos/favicon-32x32.png">
<link rel="icon" type="image/png" sizes="16x16" href="../assets/logos/favicon-16x16.png">
    <link rel="stylesheet" href="../public/css/admin.css">
    <meta charset="UTF-8">
    <title>Editar Despesa</title>
</head>
<body>
    <h2>Editar Despesa</h2>
    <form method="POST" action="">
        Nome da Despesa: <input type="text" name="nome" required value="<?= htmlspecialchars($despesa['D_nome']) ?>"><br><br>
        Valor (€): <input type="number" step="0.01" name="valor" required value="<?= htmlspecialchars($despesa['D_valor']) ?>"><br><br>
        Data: <input type="date" name="data" required value="<?= htmlspecialchars($despesa['D_data']) ?>"><br><br>
        Descrição: <textarea name="descricao"><?= htmlspecialchars($despesa['D_descricao']) ?></textarea><br><br>
        <label>
            <input type="checkbox" name="recorrente" value="1" id="recorrente_cb" onchange="document.getElementById('recorrencia_opts').style.display=this.checked?'block':'none'" <?= $despesa['recorrente'] ? 'checked' : '' ?>> Despesa recorrente
        </label>
        <div id="recorrencia_opts" style="display:<?= $despesa['recorrente'] ? 'block' : 'none' ?>; margin-top:10px;">
            <label>Periodicidade:
                <select name="periodicidade">
                    <option value="mensal" <?= $despesa['periodicidade']==='mensal'?'selected':'' ?>>Mensal</option>
                    <option value="semanal" <?= $despesa['periodicidade']==='semanal'?'selected':'' ?>>Semanal</option>
                    <option value="anual" <?= $despesa['periodicidade']==='anual'?'selected':'' ?>>Anual</option>
                </select>
            </label>
            <label>Data de término:
                <input type="date" name="data_fim_recorrencia" value="<?= htmlspecialchars($despesa['data_fim_recorrencia']) ?>">
            </label>
        </div>
        <button type="submit">Salvar</button>
    </form>
    <a href="despesas.php">← Voltar</a>
</body>
</html>
