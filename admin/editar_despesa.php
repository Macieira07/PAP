<?php
require '../conexao.php';

$id = $_GET['id'];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nome = $_POST['nome'];
    $valor = $_POST['valor'];
    $data = $_POST['data'];
    $descricao = $_POST['descricao'];

    // Validação de valor
    if (!is_numeric($valor) || $valor <= 0) {
        echo "O valor deve ser um valor positivo.";
        exit;
    }

    $stmt = $conexao->prepare("UPDATE despesas SET D_nome=?, D_valor=?, D_data=?, D_descricao=? WHERE D_id_despeza=?");
    $stmt->bind_param("sdssi", $nome, $valor, $data, $descricao, $id);
    $stmt->execute();

    header("Location: despesas.php");
    exit;
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
    <link rel="stylesheet" href="admin.css">
    <meta charset="UTF-8">
    <title>Editar Despesa</title>
</head>
<body>
    <h2>Editar Despesa</h2>
    <form method="post">
        Nome da Despesa: <input type="text" name="nome" value="<?= $despesa['D_nome'] ?>" required><br><br>
        Valor (€): <input type="number" step="0.01" name="valor" value="<?= $despesa['D_valor'] ?>" required><br><br>
        Data: <input type="date" name="data" value="<?= $despesa['D_data'] ?>" required><br><br>
        Descrição: <textarea name="descricao"><?= $despesa['D_descricao'] ?></textarea><br><br>
        <button type="submit">Atualizar</button>
    </form>
    <a href="despesas.php">← Voltar</a>
</body>
</html>
