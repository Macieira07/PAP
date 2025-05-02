<?php
require_once '../includes/auth.php';
require_once '../includes/conexao.php';
verificarAdmin();

if (!isset($_GET['id'])) {
    header('Location: listar.php');
    exit();
}

$id = $_GET['id'];

try {
    $pdo = conexao();
    
    // Verificar se é possível excluir (não pode excluir a si mesmo)
    if ($_SESSION['admin_id'] == $id) {
        $_SESSION['erro'] = "Você não pode excluir a si mesmo!";
        header('Location: listar.php');
        exit();
    }
    
    // Excluir funcionário
    $stmt = $pdo->prepare("DELETE FROM funcionarios WHERE F_id_funcionario = ?");
    $stmt->execute([$id]);
    
    $_SESSION['mensagem'] = "Funcionário excluído com sucesso!";
} catch (PDOException $e) {
    $_SESSION['erro'] = "Erro ao excluir funcionário: " . $e->getMessage();
}

header('Location: listar.php');
exit();
?>