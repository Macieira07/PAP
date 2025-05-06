<?php
// Inclui a conexão com o banco de dados
require '../conexao.php';

// Verifica se o formulário foi submetido
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['adicionar'])) {
    $descricao = $_POST['descricao'];
    $valor = $_POST['valor'];
    $data = $_POST['data'];
    $tipo = $_POST['tipo'];
    $observacoes = $_POST['observacoes'];
    $metodo_pagamento = $_POST['metodo_pagamento'];
    $comprovativo = isset($_POST['comprovativo']) ? 1 : 0;

    // Insere nova receita no banco de dados
    $sql = "INSERT INTO receitas (R_descricao, R_valor, R_data, R_tipo, R_observacoes, R_metodo_pagamento, R_comprovativo_entregue) 
            VALUES (?, ?, ?, ?, ?, ?, ?)";

    if ($stmt = $conexao->prepare($sql)) {
        // Corrigir os tipos para bind_param:
        // "s" para string (para texto)
        // "d" para decimal (para valores monetários)
        // "i" para int (para valores inteiros)
        $stmt->bind_param("sdssssi", $descricao, $valor, $data, $tipo, $observacoes, $metodo_pagamento, $comprovativo);
        
        if ($stmt->execute()) {
            echo "Receita registrada com sucesso!";
        } else {
            echo "Erro ao registrar receita: " . $stmt->error;
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
<link rel="stylesheet" href="admin.css"> 
    <meta charset="UTF-8">
    <title>Adicionar Receita</title>
</head>
<body>
    <h1>Adicionar Nova Receita</h1>
    
    <form action="adicionar_receita.php" method="POST">
        <input type="text" name="descricao" placeholder="Descrição" required><br>
        <input type="number" name="valor" placeholder="Valor" step="0.01" required><br>
        <input type="date" name="data" required><br>
        <input type="text" name="tipo" placeholder="Tipo"><br>
        <textarea name="observacoes" placeholder="Observações"></textarea><br>
        <input type="text" name="metodo_pagamento" placeholder="Método de Pagamento"><br>
        <label for="comprovativo">Comprovativo entregue:</label>
        <input type="checkbox" name="comprovativo"><br>
        <button type="submit" name="adicionar">Adicionar Receita</button>
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
