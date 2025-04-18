<?php
include('../conexao.php');
$id = isset($_GET['id']) ? intval($_GET['id']) : 0;
$hospedes = $conexao->query("SELECT H_id_hospede, H_nome FROM hospedes");
$casas = $conexao->query("SELECT C_id_casa, C_nome FROM casas");

$R_id_hospede = $R_id_casa = $R_data_checkin = $R_data_checkout = $R_estado = "";
if ($id > 0) {
    $res = $conexao->query("SELECT * FROM reservas WHERE R_id_reserva = $id")->fetch_assoc();
    $R_id_hospede = $res['R_id_hospede'];
    $R_id_casa = $res['R_id_casa'];
    $R_data_checkin = $res['R_data_checkin'];
    $R_data_checkout = $res['R_data_checkout'];
    $R_estado = $res['R_estado'];
}
?>

<!DOCTYPE html>
<html>
<head><meta charset="UTF-8"><title>Editar Reserva</title></head>
<body>
    <h2><?= $id > 0 ? "Editar" : "Nova" ?> Reserva</h2>
    <form method="POST" action="atualizar_reserva.php">
        <input type="hidden" name="id" value="<?= $id ?>">
        Hóspede:
        <select name="id_hospede">
            <?php while($h = $hospedes->fetch_assoc()) { ?>
                <option value="<?= $h['H_id_hospede'] ?>" <?= ($h['H_id_hospede'] == $R_id_hospede) ? "selected" : "" ?>>
                    <?= $h['H_nome'] ?>
                </option>
            <?php } ?>
        </select><br>

        Casa:
        <select name="id_casa">
            <?php while($c = $casas->fetch_assoc()) { ?>
                <option value="<?= $c['C_id_casa'] ?>" <?= ($c['C_id_casa'] == $R_id_casa) ? "selected" : "" ?>>
                    <?= $c['C_nome'] ?>
                </option>
            <?php } ?>
        </select><br>

        Check-in: <input type="date" name="checkin" value="<?= $R_data_checkin ?>"><br>
        Check-out: <input type="date" name="checkout" value="<?= $R_data_checkout ?>"><br>
        Estado: <input type="text" name="estado" value="<?= $R_estado ?>"><br>
        <input type="submit" value="Salvar">
    </form>
    <a href="admin_reservas.php">Voltar</a>
</body>
</html>
