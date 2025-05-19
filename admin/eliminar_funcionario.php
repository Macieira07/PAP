<?php
require '../conexao.php';

if (isset($_GET['id'])) {
    $id = $_GET['id'];

    // Excluindo registros dependentes na tabela turnos
    $stmt_turnos = $conexao->prepare("DELETE FROM turnos WHERE F_id_funcionario=?");
    $stmt_turnos->bind_param("i", $id);
    $stmt_turnos->execute();

    // Excluindo registros dependentes na tabela ferias_ausencias
    $stmt_ferias = $conexao->prepare("DELETE FROM ferias_ausencias WHERE F_id_funcionario=?");
    $stmt_ferias->bind_param("i", $id);
    $stmt_ferias->execute();

    // Preparando a consulta para excluir o funcionário
    $stmt_funcionario = $conexao->prepare("DELETE FROM funcionarios WHERE F_id_funcionario=?");
    $stmt_funcionario->bind_param("i", $id);
    
    if ($stmt_funcionario->execute()) {
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
