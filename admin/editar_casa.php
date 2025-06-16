<?php
require '../conexao.php';

$id = $_GET['id'];

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

    $stmt = $conexao->prepare("UPDATE casas SET C_nome=?, C_descricao=?, C_capacidade=?, C_preco_noite=?, C_caracteristicas=?, C_estado=? WHERE C_id_casa=?");
    $stmt->bind_param("sssdssi", $nome, $descricao, $capacidade, $preco, $caracteristicas, $estado, $id);
    $stmt->execute();

    header("Location: casas.php");
    exit;
}

$stmt = $conexao->prepare("SELECT * FROM casas WHERE C_id_casa=?");
$stmt->bind_param("i", $id);
$stmt->execute();
$resultado = $stmt->get_result();
$casa = $resultado->fetch_assoc();
?>
<!DOCTYPE html>
<html lang="pt">
<head>
        <link rel="icon" type="image/png" sizes="32x32" href="../assets/logos/favicon-32x32.png">
<link rel="icon" type="image/png" sizes="16x16" href="../assets/logos/favicon-16x16.png">
    <link rel="stylesheet" href="../public/css/admin.css">
    <meta charset="UTF-8">
    <title>Editar Casa</title>
</head>
<body>
<div style="display: flex; align-items: center; gap: 10px;">
        <img src="https://img.icons8.com/?size=100&id=8BBH2HJBM6Nz&format=png&color=000000" alt="Ícone Casas" style="height: 50px;">
        <h2>Editar Alojamento</h2>
    </div>
    <form method="post">
        Nome: <input type="text" name="nome" value="<?= $casa['C_nome'] ?>" required><br><br>
        Descrição: <textarea name="descricao"><?= $casa['C_descricao'] ?></textarea><br><br>
        Capacidade: <input type="number" name="capacidade" value="<?= $casa['C_capacidade'] ?>" required><br><br>
        Preço por Noite (€): <input type="number" step="0.01" name="preco" value="<?= $casa['C_preco_noite'] ?>" required><br><br>
        Características: <textarea name="caracteristicas"><?= $casa['C_caracteristicas'] ?></textarea><br><br>
        Estado:
        <select name="estado">
            <option value="disponível" <?= $casa['C_estado'] == 'disponível' ? 'selected' : '' ?>>Disponível</option>
            <option value="ocupada" <?= $casa['C_estado'] == 'ocupada' ? 'selected' : '' ?>>Ocupada</option>
            <option value="manutenção" <?= $casa['C_estado'] == 'manutenção' ? 'selected' : '' ?>>Manutenção</option>
        </select><br><br>
        <button type="submit">Atualizar</button>
    </form>
    <a href="casas.php">← Voltar</a>
</body>
</html>
