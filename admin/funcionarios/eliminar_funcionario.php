<?php
require '../../conexao.php';
session_start();

if (!isset($_GET['id']) || empty($_GET['id'])) {
    $_SESSION['mensagem'] = "ID do funcionário não fornecido.";
    $_SESSION['tipo_mensagem'] = "erro";
    header("Location: funcionarios.php");
    exit;
}

$id = $_GET['id'];

// Iniciar transação para garantir a integridade dos dados
$conexao->begin_transaction();

try {
    // Excluir registros dependentes
    $stmt = $conexao->prepare("DELETE FROM ferias_ausencias WHERE F_id_funcionario = ?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    
    $stmt = $conexao->prepare("DELETE FROM turnos WHERE F_id_funcionario = ?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    
    // Excluir o funcionário
    $stmt = $conexao->prepare("DELETE FROM funcionarios WHERE F_id_funcionario = ?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    
    $conexao->commit();
    
    $_SESSION['mensagem'] = "Funcionário eliminado com sucesso!";
    $_SESSION['tipo_mensagem'] = "sucesso";
} catch (Exception $e) {
    $conexao->rollback();
    
    $_SESSION['mensagem'] = "Erro ao eliminar funcionário: " . $e->getMessage();
    $_SESSION['tipo_mensagem'] = "erro";
}

header("Location: funcionarios.php");
exit;
?>