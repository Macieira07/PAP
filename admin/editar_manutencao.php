<?php
require '../conexao.php';

$id = $_GET['id'];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nome = $_POST['nome'];
    $data_inicio = $_POST['data_inicio'];
    $data_fim = $_POST['data_fim'];
    $descricao = $_POST['descricao'];
    $custo = $_POST['custo'];
    $id_casa = $_POST['id_casa'];

    // Validação de custo
    if (!is_numeric($custo) || $custo <= 0) {
        echo "O custo deve ser um valor positivo.";
        exit;
    }

    $stmt = $conexao->prepare("UPDATE manutencao SET M_nome=?, M_data_inicio=?, M_data_fim=?, M_descricao=?, M_custo=?, M_id_casa=? WHERE M_id_manutencao=?");
    $stmt->bind_param("ssssdii", $nome, $data_inicio, $data_fim, $descricao, $custo, $id_casa, $id);
    $stmt->execute();

    header("Location: manutencao.php");
    exit;
}

$stmt = $conexao->prepare("SELECT * FROM manutencao WHERE M_id_manutencao=?");
$stmt->bind_param("i", $id);
$stmt->execute();
$resultado = $stmt->get_result();
$manutencao = $resultado->fetch_assoc();
?>
<!DOCTYPE html>
<html lang="pt">
<head>
    <link rel="stylesheet" href="admin.css">
    <meta charset="UTF-8">
    <title>Editar Manutenção</title>
</head>
<body>
    <h2>Editar Manutenção</h2>
    <form method="post">
        Nome da Manutenção: <input type="text" name="nome" value="<?= $manutencao['M_nome'] ?>" required><br><br>
        Data Início: <input type="date" name="data_inicio" value="<?= $manutencao['M_data_inicio'] ?>" required><br><br>
        Data Fim: <input type="date" name="data_fim" value="<?= $manutencao['M_data_fim'] ?>"><br><br>
        Descrição: <textarea name="descricao"><?= $manutencao['M_descricao'] ?></textarea><br><br>
        Custo (€): <input type="number" step="0.01" name="custo" value="<?= $manutencao['M_custo'] ?>" required><br><br>
        Casa: 
        <select name="id_casa" required>
            <?php
                $resultado = $conexao->query("SELECT * FROM casas");
                while ($casa = $resultado->fetch_assoc()) {
                    $selected = $casa['C_id_casa'] == $manutencao['M_id_casa'] ? 'selected' : '';
                    echo "<option value='{$casa['C_id_casa']}' $selected>{$casa['C_nome']}</option>";
                }
            ?>
        </select><br><br>
        <button type="submit">Atualizar</button>
    </form>
    <a href="manutencao.php">← Voltar</a>
</body>
</html>
