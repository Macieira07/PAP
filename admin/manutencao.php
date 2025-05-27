<?php
require '../conexao.php';

// Consulta para obter os dados das manutenções
$resultado = $conexao->query("SELECT * FROM manutencao INNER JOIN casas ON manutencao.M_id_casa = casas.C_id_casa");

// Mensagem flash
if (isset($_SESSION['flash'])) {
    echo "<div class='flash-message {$_SESSION['flash']['type']}'>{$_SESSION['flash']['msg']}</div>";
    unset($_SESSION['flash']);
}

// Consulta para calcular o total gasto em manutenções
$resultado_total = $conexao->query("SELECT SUM(M_custo) AS total_gasto FROM manutencao");
$total_gasto = $resultado_total->fetch_assoc()['total_gasto'] ?? 0.0;
?>

<!DOCTYPE html>
<html lang="pt">
<head>
    <meta charset="UTF-8">
    <title>Manutenções</title>
    <link rel="stylesheet" href="admin.css">
    <link rel="icon" type="image/png" sizes="32x32" href="../assets/logos/favicon-32x32.png">
    <link rel="icon" type="image/png" sizes="16x16" href="../assets/logos/favicon-16x16.png">
    <style>
        .flash-message {
            position: fixed;
            top: 20px;
            right: 20px;
            padding: 15px;
            border-radius: 5px;
            color: white;
            z-index: 1000;
            animation: fadeIn 0.5s, fadeOut 0.5s 2.5s;
        }
        .flash-message.success { background-color: #4CAF50; }
        .flash-message.error { background-color: #f44336; }
        @keyframes fadeIn { from { opacity: 0; } to { opacity: 1; } }
        @keyframes fadeOut { from { opacity: 1; } to { opacity: 0; } }
    </style>
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
                <td><?= $manutencao['M_data_inicio'] ?></td>
                <td><?= $manutencao['M_data_fim'] ? $manutencao['M_data_fim'] : 'Não definida' ?></td>
                <td><?= number_format($manutencao['M_custo'], 2, ',', '.') ?>€</td>
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
