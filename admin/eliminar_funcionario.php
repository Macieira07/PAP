<?php
require '../conexao.php';

if (isset($_GET['id'])) {
    $id = $_GET['id'];

    // Excluindo registros dependentes na tabela ferias_ausencias
    $stmt1 = $conexao->prepare("DELETE FROM ferias_ausencias WHERE F_id_funcionario=?");
    $stmt1->bind_param("i", $id);
    $stmt1->execute();

    // Preparando a consulta para excluir o funcionário
    $stmt2 = $conexao->prepare("DELETE FROM funcionarios WHERE F_id_funcionario=?");
    $stmt2->bind_param("i", $id);
    
    if ($stmt2->execute()) {
        // Mensagem de sucesso
        $mensagem = "Funcionário excluído com sucesso!";
        $tipo = "sucesso";  // Sucesso
    } else {
        // Mensagem de erro
        $mensagem = "Erro ao excluir o funcionário. Tente novamente.";
        $tipo = "erro";  // Erro
    }

    // Redirecionando para a página de funcionários com a mensagem
    header("Location: funcionarios.php?mensagem=$mensagem&tipo=$tipo");
    exit;
}
?>
