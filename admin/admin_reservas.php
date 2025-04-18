<?php
include('../conexao.php');
$sql = "SELECT reservas.*, hospedes.H_nome FROM reservas JOIN hospedes ON reservas.R_id_hospede = hospedes.H_id_hospede";
$r = $conexao->query($sql);
?>

<!DOCTYPE html>
<html>
<head><meta charset="UTF-8"><title>Admin - Reservas</title></head>
<body>
    <h2>Lista de Reservas</h2>
    <a href="editar_reserva.php">Nova Reserva</a><br><br>
    <table border="1" cellpadding="5">
        <tr>
            <th>ID</th><th>Hóspede</th><th>Casa</th><th>Check-in</th><th>Check-out</th><th>Estado</th><th>Ações</th>
        </tr>
        <?php while($res = $r->fetch_assoc()) { ?>
        <tr>
            <td><?= $res['R_id_reserva'] ?></td>
            <td><?= $res['H_nome'] ?></td>
            <td><?= $res['R_id_casa'] ?></td>
            <td><?= $res['R_data_checkin'] ?></td>
            <td><?= $res['R_data_checkout'] ?></td>
            <td><?= $res['R_estado'] ?></td>
            <td>
                <a href="editar_reserva.php?id=<?= $res['R_id_reserva'] ?>">Editar</a> |
                <a href="excluir_reserva.php?id=<?= $res['R_id_reserva'] ?>" onclick="return confirm('Excluir reserva?')">Excluir</a>
            </td>
        </tr>
        <?php } ?>
    </table>
</body>
</html>
