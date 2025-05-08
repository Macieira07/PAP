<?php
require '../conexao.php';

// Consulta para obter os dados das manutenções
$resultado = $conexao->query("SELECT * FROM manutencao INNER JOIN casas ON manutencao.M_id_casa = casas.C_id_casa");

// Consulta para calcular o total gasto em manutenções
$resultado_total = $conexao->query("SELECT SUM(M_custo) AS total_gasto FROM manutencao");
$total_gasto = $resultado_total->fetch_assoc()['total_gasto'];
?>

<!DOCTYPE html>
<html lang="pt">
<head>
    <meta charset="UTF-8">
    <title>Manutenções</title>
    <link rel="stylesheet" href="admin.css">
</head>
<body>
<div style="display: flex; align-items: center; gap: 10px;">
        <img src="https://img.icons8.com/?size=100&id=11151&format=png&color=000000" alt="Ícone Manutencao" style="height: 50px;">
        <h1>Lista de Manutenções</h1>
    </div>
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
    <a href="admin.php">← Voltar</a>

    <!-- Exibe o total gasto -->
    <div style="margin-top: 20px;">
        <strong>Total Gasto em Manutenções: <?= number_format($total_gasto, 2, ',', '.') ?>€</strong>
    </div>
</body>
</html>
            