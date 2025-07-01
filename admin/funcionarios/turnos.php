<?php
require '../conexao.php';

// Consultar os turnos
$sql = "SELECT T.*, F.F_nome FROM turnos T JOIN funcionarios F ON T.F_id_funcionario = F.F_id_funcionario";
$resultado = $conexao->query($sql);
?>

<h1>Turnos dos Funcionários</h1>
<table border="1">
    <tr>
        <th>Funcionário</th>
        <th>Data</th>
        <th>Início</th>
        <th>Fim</th>
    </tr>
    <?php while ($row = $resultado->fetch_assoc()): ?>
    <tr>
        <td><?= $row['F_nome'] ?></td>
        <td><?= $row['T_data'] ?></td>
        <td><?= $row['T_inicio'] ?></td>
        <td><?= $row['T_fim'] ?></td>
    </tr>
    <?php endwhile; ?>
</table>
