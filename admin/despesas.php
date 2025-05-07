<?php
require '../conexao.php';

$resultado = $conexao->query("SELECT * FROM despesas");
?>
<!DOCTYPE html>
<html lang="pt">
<head>
    <link rel="stylesheet" href="admin.css">
    <meta charset="UTF-8">
    <title>Despesas</title>
</head>
<body>
<div style="display: flex; align-items: center; gap: 10px;">
        <img src="https://img.icons8.com/?size=100&id=2975&format=png&color=000000" alt="Ícone Despesas" style="height: 50px;">
        <h1>Todas as Despesas</h1>
    </div>
    <a href="admin.php">← Voltar</a> | 
    <a href="adicionar_despesa.php">+ Adicionar Despesa</a>
    <table border="1" cellpadding="10" style="margin-top: 20px;">
        <tr>
            <th>ID</th>
            <th>Nome</th>
            <th>Valor (€)</th>
            <th>Data</th>
            <th>Descrição</th>
            <th>Ações</th>
        </tr>
        <?php while ($despesa = $resultado->fetch_assoc()): ?>
            <tr>
                <td><?= $despesa['D_id_despeza'] ?></td>
                <td><?= $despesa['D_nome'] ?></td>
                <td><?= $despesa['D_valor'] ?>€</td>
                <td><?= $despesa['D_data'] ?></td>
                <td><?= $despesa['D_descricao'] ?></td>
                <td>
                    <a href="editar_despesa.php?id=<?= $despesa['D_id_despeza'] ?>">Editar</a> |
                    <a href="eliminar_despesa.php?id=<?= $despesa['D_id_despeza'] ?>" onclick="return confirm('Tem certeza?')">Eliminar</a>
                </td>
            </tr>
        <?php endwhile; ?>
    </table>
</body>
</html>
