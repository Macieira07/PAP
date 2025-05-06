<?php
// Inclui a conexão com o banco de dados
require '../conexao.php';

// Verifica se o parâmetro 'id' foi passado na URL
if (isset($_GET['id'])) {
    $id = $_GET['id'];

    // Prepara a query para excluir a receita
    $sql = "DELETE FROM receitas WHERE R_id_receita = ?";

    if ($stmt = $conexao->prepare($sql)) {
        // Vincula o parâmetro (ID da receita) à query
        $stmt->bind_param("i", $id);

        if ($stmt->execute()) {
            echo "Receita excluída com sucesso!";
            echo "<br><a href='receitas.php'>Voltar para a lista de receitas</a>";
        } else {
            echo "Erro ao excluir a receita: " . $stmt->error;
        }
        $stmt->close();
    } else {
        echo "Erro na preparação da consulta: " . $conexao->error;
    }
} else {
    echo "ID da receita não fornecido!";
}

$conexao->close();
?>
