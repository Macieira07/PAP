<?php
require '../../conexao.php';
require '../verificar_admin.php';

// Verificar se o ID foi passado
if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    header("Location: listar_receitas.php");
    exit();
}

$id = limparDados($_GET['id']);

// Verificar se a receita existe
$query = "SELECT R_id_receita FROM receitas WHERE R_id_receita = ?";
$stmt = $conexao->prepare($query);
$stmt->bind_param("i", $id);
$stmt->execute();
$resultado = $stmt->get_result();

if ($resultado->num_rows == 0) {
    header("Location: listar_receitas.php");
    exit();
}

// Eliminar a receita
$query = "DELETE FROM receitas WHERE R_id_receita = ?";
$stmt = $conexao->prepare($query);
$stmt->bind_param("i", $id);

if ($stmt->execute()) {
    header("Location: listar_receitas.php?sucesso=3");
} else {
    header("Location: listar_receitas.php?erro=1");
}

exit();
?>