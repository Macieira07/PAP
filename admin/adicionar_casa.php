<?php
require '../conexao.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nome = $_POST['nome'];
    $descricao = $_POST['descricao'];
    $capacidade = $_POST['capacidade'];
    $preco = $_POST['preco'];
    $caracteristicas = $_POST['caracteristicas'];
    $estado = $_POST['estado'];

    // Validação
    if (!is_numeric($preco) || $preco <= 0) {
        echo "O preço por noite deve ser um valor positivo.";
        exit;
    }

    if (!is_numeric($capacidade) || $capacidade <= 0) {
        echo "A capacidade deve ser um valor positivo.";
        exit;
    }

    $stmt = $conexao->prepare("INSERT INTO casas (C_nome, C_descricao, C_capacidade, C_preco_noite, C_caracteristicas, C_estado)
                               VALUES (?, ?, ?, ?, ?, ?)");
    $stmt->bind_param("sssdss", $nome, $descricao, $capacidade, $preco, $caracteristicas, $estado);
    $stmt->execute();

    header("Location: casas.php");
    exit;
}
?>
<!DOCTYPE html>
<html lang="pt">
<head>
    <link rel="stylesheet" href="admin.css">
    <meta charset="UTF-8">
    <title>Adicionar Nova Casa</title>
</head>
<body>
    <h2>Adicionar Nova Casa</h2>
    <form method="post">
        Nome: <input type="text" name="nome" required><br><br>
        Descrição: <textarea name="descricao"></textarea><br><br>
        Capacidade: <input type="number" name="capacidade" required><br><br>
        Preço por Noite (€): <input type="number" step="0.01" name="preco" required><br><br>
        Características: <textarea name="caracteristicas"></textarea><br><br>
        Estado:
        <select name="estado">
            <option value="disponível">Disponível</option>
            <option value="ocupada">Ocupada</option>
            <option value="manutenção">Manutenção</option>
        </select><br><br>
        <button type="submit">Salvar</button>
    </form>
    <a href="casas.php">← Voltar</a>
</body>
</html>
