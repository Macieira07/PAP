<?php
require '../conexao.php';

$resultado = $conexao->query("SELECT * FROM servicos");
?>
<!DOCTYPE html>
<html lang="pt">
<head>
    <link rel="stylesheet" href="admin.css">
    <meta charset="UTF-8">
    <title>Serviços</title>
</head>
<body>
<div style="display: flex; align-items: center; gap: 10px;">
        <img src="https://img.icons8.com/?size=100&id=rk8gMHQsBQHb&format=png&color=000000" alt="Ícone Serviços" style="height: 50px;">
        <h1>Todos os Serviços</h1>
    </div>
    <a href="admin.php">← Voltar</a> | 
    <a href="adicionar_servico.php">+ Adicionar Serviço</a>
    <table border="1" cellpadding="10" style="margin-top: 20px;">
        <tr>
            <th>ID</th>
            <th>Nome</th>
            <th>Preço</th>
            <th>Ações</th>
        </tr>
        <?php while ($servico = $resultado->fetch_assoc()): ?>
            <tr>
                <td><?= $servico['S_id_servico'] ?></td>
                <td><?= $servico['S_nome'] ?></td>
                <td><?= $servico['S_preco'] ?>€</td>
                <td>
                    <a href="editar_servico.php?id=<?= $servico['S_id_servico'] ?>">Editar</a> |
                    <a href="eliminar_servico.php?id=<?= $servico['S_id_servico'] ?>" onclick="return confirm('Tem certeza?')">Eliminar</a>
                </td>
            </tr>
        <?php endwhile; ?>
    </table>
</body>
</html>
