<?php
require '../conexao.php';

$resultado = $conexao->query("SELECT * FROM manutencao INNER JOIN casas ON manutencao.M_id_casa = casas.C_id_casa");

?>

<!DOCTYPE html>
<html lang="pt">
<head>
    <meta charset="UTF-8">
    <title>Manutenções</title>
    <link rel="stylesheet" href="admin.css">
</head>
<body>
    <h1>Lista de Manutenções</h1>
    <a href="admin.php">← Voltar</a> | 
    <a href="adicionar_manutencao.php">+ Adicionar Manutenção</a>
    <table border="1" cellpadding="10" style="margin-top: 20px;">
        <tr>
            <th>ID</th>
            <th>Casa</th>
            <th>Tipo de Manutenção</th>
            <th>Descrição</th>
            <th>Data Início</th>
            <th>Data Fim</th>
            <th>Custo (€)</th>
            <th>Ações</th>
        </tr>
        <?php while ($manutencao = $resultado->fetch_assoc()): ?>
            <tr>
                <td><?= $manutencao['M_id_manutencao'] ?></td>
                <td><?= htmlspecialchars($manutencao['C_nome']) ?></td>
                <td><?= htmlspecialchars($manutencao['M_tipo']) ?></td>
                <td><?= htmlspecialchars($manutencao['M_descricao']) ?></td>
                <td><?= $manutencao['M_data_inicio'] ?></td>
                <td><?= $manutencao['M_data_fim'] ? $manutencao['M_data_fim'] : 'Não definida' ?></td> <!-- Exibe data de fim -->
                <td><?= $manutencao['M_custo'] ?>€</td>
                <td>
                    <a href="editar_manutencao.php?id=<?= $manutencao['M_id_manutencao'] ?>">Editar</a> |
                    <a href="eliminar_manutencao.php?id=<?= $manutencao['M_id_manutencao'] ?>" onclick="return confirm('Tem certeza?')">Eliminar</a>
                </td>
            </tr>
        <?php endwhile; ?>
    </table>
</body>
</html>
