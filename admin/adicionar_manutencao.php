<?php
require '../conexao.php';

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

    $stmt = $conexao->prepare("INSERT INTO manutencao (M_nome, M_data_inicio, M_data_fim, M_descricao, M_custo, M_id_casa)
                               VALUES (?, ?, ?, ?, ?, ?)");
    $stmt->bind_param("ssssdi", $nome, $data_inicio, $data_fim, $descricao, $custo, $id_casa);
    $stmt->execute();

    header("Location: manutencao.php");
    exit;
}
?>
<!DOCTYPE html>
<html lang="pt">
<head>
    <link rel="stylesheet" href="admin.css">
    <meta charset="UTF-8">
    <title>Adicionar Manutenção</title>
</head>
<body>
    <h2>Adicionar Nova Manutenção</h2>
    <form method="post">
        Nome da Manutenção: <input type="text" name="nome" required><br><br>
        Data Início: <input type="date" name="data_inicio" required><br><br>
        Data Fim: <input type="date" name="data_fim"><br><br>
        Descrição: <textarea name="descricao"></textarea><br><br>
        Custo (€): <input type="number" step="0.01" name="custo" required><br><br>
        Casa: 
        <select name="id_casa" required>
            <?php
                $resultado = $conexao->query("SELECT * FROM casas");
                while ($casa = $resultado->fetch_assoc()) {
                    echo "<option value='{$casa['C_id_casa']}'>{$casa['C_nome']}</option>";
                }
            ?>
        </select><br><br>
        <button type="submit">Salvar</button>
    </form>
    <a href="manutencao.php">← Voltar</a>
</body>
</html>
