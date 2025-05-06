<?php
require '../conexao.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $turno_id = $_POST['turno_id'];
    $turno_inicio = $_POST['turno_inicio'];
    $turno_fim = $_POST['turno_fim'];

    if ($turno_inicio && $turno_fim) {
        $stmt = $conexao->prepare("UPDATE turnos SET T_inicio=?, T_fim=? WHERE T_id_turno=?");
        $stmt->bind_param("ssi", $turno_inicio, $turno_fim, $turno_id);
        $stmt->execute();
        header("Location: funcionarios.php");
        exit;
    }
}

// Carregar dados do turno
$turno_id = $_GET['id'];
$stmt = $conexao->prepare("SELECT * FROM turnos WHERE T_id_turno=?");
$stmt->bind_param("i", $turno_id);
$stmt->execute();
$turno = $stmt->get_result()->fetch_assoc();
?>

<form method="post">
    <input type="hidden" name="turno_id" value="<?= $turno['T_id_turno'] ?>">
    Início: <input type="time" name="turno_inicio" value="<?= $turno['T_inicio'] ?>" required><br><br>
    Fim: <input type="time" name="turno_fim" value="<?= $turno['T_fim'] ?>" required><br><br>
    <button type="submit">Salvar</button>
</form>
