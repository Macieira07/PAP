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
        <link rel="icon" type="image/png" sizes="32x32" href="../assets/logos/favicon-32x32.png">
<link rel="icon" type="image/png" sizes="16x16" href="../assets/logos/favicon-16x16.png">
    <link rel="stylesheet" href="admin.css">
    <meta charset="UTF-8">
    <title>Adicionar Nova Casa</title>
</head>
<body>
<div style="display: flex; align-items: center; gap: 10px;">
    <img src="https://img.icons8.com/?size=100&id=8BBH2HJBM6Nz&format=png&color=000000" alt="Ícone Casas" style="height: 50px;">
    <h2>Adicionar Novo Alojamento</h2>
</div>

<form method="post">
    <label>
        <i class="fa-solid fa-home"></i> Nome:
        <input type="text" name="nome" required>
    </label><br><br>

    <label>
        <i class="fa-solid fa-align-left"></i> Descrição:
        <textarea name="descricao"></textarea>
    </label><br><br>

    <label>
        <i class="fa-solid fa-users"></i> Capacidade:
        <input type="number" name="capacidade" required>
    </label><br><br>

    <label>
        <i class="fa-solid fa-euro-sign"></i> Preço por Noite (€):
        <input type="number" step="0.01" name="preco" required>
    </label><br><br>

    <label>
        <i class="fa-solid fa-cogs"></i> Características:
        <textarea name="caracteristicas"></textarea>
    </label><br><br>

    <label>
        <i class="fa-solid fa-clipboard-list"></i> Estado:
        <select name="estado">
            <option value="disponível">Disponível</option>
            <option value="ocupada">Ocupada</option>
            <option value="manutenção">Manutenção</option>
        </select>
    </label><br><br>

    <button type="submit">Salvar</button>
</form>

<a href="casas.php">← Voltar</a>

</body>
</html>
