<?php
// Inclui a conexão com o banco de dados
require '../conexao.php';

// Verifica se o ID foi passado na URL
if (isset($_GET['id'])) {
    $id = $_GET['id'];

    // Consulta a receita para preencher o formulário
    $query = "SELECT * FROM receitas WHERE R_id_receita = ?";
    if ($stmt = $conexao->prepare($query)) {
        $stmt->bind_param("i", $id);
        $stmt->execute();
        $resultado = $stmt->get_result();

        if ($resultado->num_rows > 0) {
            $receita = $resultado->fetch_assoc();
        } else {
            echo "Receita não encontrada!";
            exit;
        }
    } else {
        echo "Erro na consulta: " . $conexao->error;
        exit;
    }
}

// Se o formulário for submetido
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $descricao = $_POST['descricao'];
    $valor = $_POST['valor'];
    $data = $_POST['data'];
    $tipo = $_POST['tipo'];
    $observacoes = $_POST['observacoes'];
    $metodo_pagamento = $_POST['metodo_pagamento'];
    $comprovativo = isset($_POST['comprovativo']) ? 1 : 0;

    // Atualiza a receita no banco de dados
    $sql = "UPDATE receitas SET R_descricao = ?, R_valor = ?, R_data = ?, R_tipo = ?, R_observacoes = ?, R_metodo_pagamento = ?, R_comprovativo_entregue = ? WHERE R_id_receita = ?";

    if ($stmt = $conexao->prepare($sql)) {
        $stmt->bind_param("sdssssii", $descricao, $valor, $data, $tipo, $observacoes, $metodo_pagamento, $comprovativo, $id);
        if ($stmt->execute()) {
            echo "Receita atualizada com sucesso!";
        } else {
            echo "Erro ao atualizar receita: " . $stmt->error;
        }
        $stmt->close();
    } else {
        echo "Erro na preparação da consulta: " . $conexao->error;
    }
}
?>

<!DOCTYPE html>
<html lang="pt">
<head>
        <link rel="icon" type="image/png" sizes="32x32" href="../assets/logos/favicon-32x32.png">
<link rel="icon" type="image/png" sizes="16x16" href="../assets/logos/favicon-16x16.png">
<link rel="stylesheet" href="admin.css">        
    <meta charset="UTF-8">
    <title>Editar Receita</title>
</head>
<body>
    <h1>Editar Receita</h1>
    
    <form action="editar_receita.php?id=<?php echo $id; ?>" method="POST">
        <input type="text" name="descricao" value="<?php echo $receita['R_descricao']; ?>" required><br>
        <input type="number" name="valor" value="<?php echo $receita['R_valor']; ?>" step="0.01" required><br>
        <input type="date" name="data" value="<?php echo $receita['R_data']; ?>" required><br>
        <input type="text" name="tipo" value="<?php echo $receita['R_tipo']; ?>"><br>
        <textarea name="observacoes"><?php echo $receita['R_observacoes']; ?></textarea><br>
        <input type="text" name="metodo_pagamento" value="<?php echo $receita['R_metodo_pagamento']; ?>"><br>
        <label for="comprovativo">Comprovativo entregue:</label>
        <input type="checkbox" name="comprovativo" <?php echo $receita['R_comprovativo_entregue'] ? 'checked' : ''; ?>><br>
        <button type="submit">Atualizar Receita</button>
    </form>

    <br>
    <label><a href="receitas.php">Voltar para a Lista de Receitas</a></label><br>
    <label><a href="admin.php">Voltar para o Painel de Administração</a></label>
</body>
</html>

<?php
// Fechar a conexão
$conexao->close();
?>
