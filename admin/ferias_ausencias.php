<?php
require '../conexao.php';

// Consultar as férias/ausências
$sql = "SELECT FA.*, F.F_nome FROM ferias_ausencias FA JOIN funcionarios F ON FA.F_id_funcionario = F.F_id_funcionario";
$resultado = $conexao->query($sql);
?>

<h1>Férias e Ausências dos Funcionários</h1>
<table border="1">
    <tr>
        <th>Funcionário</th>
        <th>Início</th>
        <th>Fim</th>
        <th>Tipo</th>
        <th>Motivo</th>
    </tr>
    <?php while ($row = $resultado->fetch_assoc()): ?>
    <tr>
        <td><?= $row['F_nome'] ?></td>
        <td><?= $row['FA_inicio'] ?></td>
        <td><?= $row['FA_fim'] ?></td>
        <td><?= $row['FA_tipo'] ?></td>
        <td><?= $row['FA_motivo'] ?></td>
    </tr>
    <?php endwhile; ?>
</table>
