<?php
require '../conexao.php';

$resultado = $conexao->query("SELECT * FROM manutencao");
?>
<!DOCTYPE html>
<html lang="pt">
<head>
    <link rel="stylesheet" href="admin.css">
    <meta charset="UTF-8">
    <title>Manutenções</title>
</head>
<body>
    <h1>Lista de Manutenções</h1>
    <a href="admin.php">← Voltar</a> | 
    <a href="adicionar_manutencao.php">+ Adicionar Manutenção</a>
    <table border="1" cellpadding="10" style="margin-top: 20px;">
        <tr>
            <th>ID</th>
            <th>Nome</th>
            <th>Data Início</th>
            <th>Data Fim</th>
            <th>Custo (€)</th>
            <th>Casa</th>
            <th>Ações</th>
        </tr>
        <?php while ($manutencao = $resultado->fetch_assoc()): ?>
            <tr>
                <td><?= $manutencao['M_id_manutencao'] ?></td>
                <td><?= $manutencao['M_nome'] ?></td>
                <td><?= $manutencao['M_data_inicio'] ?></td>
                <td><?= $manutencao['M_data_fim'] ?></td>
                <td><?= $manutencao['M_custo'] ?>€</td>
                <td>
                    <?php
                    $stmt = $conexao->prepare("SELECT C_nome FROM casas WHERE C_id_casa=?");
                    $stmt->bind_param("i", $manutencao['M_id_casa']);
                    $stmt->execute();
                    $resultado_casa = $stmt->get_result();
                    $casa = $resultado_casa->fetch_assoc();
                    echo $casa['C_nome'];
                    ?>
                </td>
                <td>
                    <a href="editar_manutencao.php?id=<?= $manutencao['M_id_manutencao'] ?>">Editar</a> |
                    <a href="eliminar_manutencao.php?id=<?= $manutencao['M_id_manutencao'] ?>" onclick="return confirm('Tem certeza?')">Eliminar</a>
                </td>
            </tr>
        <?php endwhile; ?>
    </table>
</body>
</html>
