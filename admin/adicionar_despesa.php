<?php
require '../conexao.php';

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

    $stmt = $conexao->prepare("INSERT INTO despesas (D_nome, D_valor, D_data, D_descricao) VALUES (?, ?, ?, ?)");
    $stmt->bind_param("sdss", $nome, $valor, $data, $descricao);
    $stmt->execute();

    header("Location: despesas.php");
    exit;
}
?>
<!DOCTYPE html>
<html lang="pt">
<head>
    <link rel="stylesheet" href="admin.css">
    <meta charset="UTF-8">
    <title>Adicionar Despesa</title>
</head>
<body>
    <h2>Adicionar Nova Despesa</h2>
    <form method="post">
        Nome da Despesa: <input type="text" name="nome" required><br><br>
        Valor (€): <input type="number" step="0.01" name="valor" required><br><br>
        Data: <input type="date" name="data" required><br><br>
        Descrição: <textarea name="descricao"></textarea><br><br>
        <button type="submit">Salvar</button>
    </form>
    <a href="despesas.php">← Voltar</a>
</body>
</html>
