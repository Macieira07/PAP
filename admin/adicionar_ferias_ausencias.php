<?php
require '../conexao.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $funcionario_id = $_POST['funcionario_id'];
    $inicio = $_POST['inicio'];
    $fim = $_POST['fim'];
    $tipo = $_POST['tipo'];
    $motivo = $_POST['motivo'];

    $stmt = $conexao->prepare("INSERT INTO ferias_ausencias (F_id_funcionario, FA_inicio, FA_fim, FA_tipo, FA_motivo) VALUES (?, ?, ?, ?, ?)");
    $stmt->bind_param("issss", $funcionario_id, $inicio, $fim, $tipo, $motivo);
    $stmt->execute();
    header("Location: ferias_ausencias.php");
    exit;
}
?>

<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Cadastro de Férias/Ausências</title>
    <link rel="stylesheet" href="global.css">
</head>
<body>
    <form method="post">
        Funcionário:
        <select name="funcionario_id">
            <?php
            $result = $conexao->query("SELECT F_id_funcionario, F_nome FROM funcionarios");
            while ($row = $result->fetch_assoc()) {
                echo "<option value='{$row['F_id_funcionario']}'>{$row['F_nome']}</option>";
            }
            ?>
        </select><br><br>
        Início: <input type="date" name="inicio" required><br><br>
        Fim: <input type="date" name="fim" required><br><br>
        Tipo:
        <select name="tipo">
            <option value="Férias">Férias</option>
            <option value="Ausência">Ausência</option>
        </select><br><br>
        Motivo: <textarea name="motivo" required></textarea><br><br>
        <button type="submit">Salvar</button>
    </form>
</body>
</html>
