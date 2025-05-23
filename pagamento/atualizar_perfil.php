<?php
session_start();
require_once '../conexao.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: perfil.php');
    exit();
}

$id = $_SESSION['id'];
$nome = trim($_POST['nome']);
$telefone = trim($_POST['telefone']);
$documento = trim($_POST['documento']);

// Validação básica
if (empty($nome) || empty($telefone) || empty($documento)) {
    $_SESSION['erro_perfil'] = 'Todos os campos são obrigatórios';
    header('Location: perfil.php');
    exit();
}

$query = "UPDATE hospedes SET 
          H_nome = ?, 
          H_telefone = ?, 
          H_documento_ident = ? 
          WHERE H_id_hospede = ?";
$stmt = $conexao->prepare($query);
$stmt->bind_param("sssi", $nome, $telefone, $documento, $id);

if ($stmt->execute()) {
    $_SESSION['nome'] = $nome; // Atualiza o nome na sessão
    $_SESSION['sucesso_perfil'] = 'Perfil atualizado com sucesso!';
} else {
    $_SESSION['erro_perfil'] = 'Erro ao atualizar perfil. Tente novamente.';
}

header('Location: perfil.php');
exit();
?>