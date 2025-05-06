<?php
require '../conexao.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $ferias_id = $_POST['ferias_id'];
    $ferias_inicio = $_POST['ferias_inicio'];
    $ferias_fim = $_POST['ferias_fim'];
    $motivo = $_POST['motivo_ferias'];

    if ($ferias_inicio && $ferias_fim && $motivo) {
        $stmt = $conexao->prepare("UPDATE ferias_ausencias SET FA_inicio=?, FA_fim=?, FA_motivo=? WHERE FA_id_ferias=?");
        $stmt->bind_param("sssi", $ferias_inicio, $ferias_fim, $motivo, $ferias_id);
        $stmt->execute();
        header("Location: funcionarios.php");
        exit;
    }
}

// Carregar dados de férias
$ferias_id = $_GET['id'];
$stmt = $conexao->prepare("SELECT * FROM ferias_ausencias WHERE FA_id_ferias=?");
$stmt->bind_param("i", $ferias_id);
$stmt->execute();
$ferias = $stmt->get_result()->fetch_assoc();
?>

<form method="post">
    <input type="hidden" name="ferias_id" value="<?= $ferias['FA_id_ferias'] ?>">
    Início: <input type="date" name="ferias_inicio" value="<?= $ferias['FA_inicio'] ?>" required><br><br>
    Fim: <input type="date" name="ferias_fim" value="<?= $ferias['FA_fim'] ?>" required><br><br>
    Motivo: <input type="text" name="motivo_ferias" value="<?= $ferias['FA_motivo'] ?>" required><br><br>
    <button type="submit">Salvar</button>
</form>
