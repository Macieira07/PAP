<?php
require '../conexao.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $funcionario_id = $_POST['funcionario_id'];
    $data = $_POST['data'];
    $inicio = $_POST['inicio'];
    $fim = $_POST['fim'];

    $stmt = $conexao->prepare("INSERT INTO turnos (F_id_funcionario, T_data, T_inicio, T_fim) VALUES (?, ?, ?, ?)");
    $stmt->bind_param("isss", $funcionario_id, $data, $inicio, $fim);
    $stmt->execute();
    header("Location: turnos.php");
    exit;
}
?>

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
    Data: <input type="date" name="data" required><br><br>
    Início: <input type="time" name="inicio" required><br><br>
    Fim: <input type="time" name="fim" required><br><br>
    <button type="submit">Salvar</button>
</form>
