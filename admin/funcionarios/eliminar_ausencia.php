<?php
require '../../conexao.php';
session_start();

if (!isset($_GET['id']) || empty($_GET['id'])) {
    $_SESSION['mensagem'] = "ID do registro não fornecido.";
    $_SESSION['tipo_mensagem'] = "erro";
    header("Location: funcionarios.php");
    exit;
}

$id = $_GET['id'];

$stmt = $conexao->prepare("DELETE FROM ferias_ausencias WHERE F_id_ausencia = ?");
$stmt->bind_param("i", $id);

if ($stmt->execute()) {
    $_SESSION['mensagem'] = "Registro de ausência eliminado com sucesso!";
    $_SESSION['tipo_mensagem'] = "sucesso";
} else {
    $_SESSION['mensagem'] = "Erro ao eliminar registro de ausência.";
    $_SESSION['tipo_mensagem'] = "erro";
}

header("Location: funcionarios.php");
exit;
?>