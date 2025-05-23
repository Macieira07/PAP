<?php
session_start();
require_once '../conexao.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: alterar_senha.php');
    exit();
}

$id = $_SESSION['id'];
$senha_atual = $_POST['senha_atual'];
$nova_senha = $_POST['nova_senha'];
$confirmar_senha = $_POST['confirmar_senha'];

// Verificar se as novas senhas coincidem
if ($nova_senha !== $confirmar_senha) {
    $_SESSION['senha_erro'] = 'As novas senhas não coincidem';
    header('Location: alterar_senha.php');
    exit();
}

// Verificar a senha atual
$query = "SELECT H_senha FROM hospedes WHERE H_id_hospede = ?";
$stmt = $conexao->prepare($query);
$stmt->bind_param("i", $id);
$stmt->execute();
$resultado = $stmt->get_result();
$usuario = $resultado->fetch_assoc();

if (!password_verify($senha_atual, $usuario['H_senha'])) {
    $_SESSION['senha_erro'] = 'Senha atual incorreta';
    header('Location: alterar_senha.php');
    exit();
}

// Atualizar a senha
$senha_hash = password_hash($nova_senha, PASSWORD_DEFAULT);
$query = "UPDATE hospedes SET H_senha = ? WHERE H_id_hospede = ?";
$stmt = $conexao->prepare($query);
$stmt->bind_param("si", $senha_hash, $id);

if ($stmt->execute()) {
    $_SESSION['senha_sucesso'] = 'Senha alterada com sucesso!';
} else {
    $_SESSION['senha_erro'] = 'Erro ao alterar senha. Tente novamente.';
}

header('Location: alterar_senha.php');
exit();
?>