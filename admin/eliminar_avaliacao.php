<?php
require '../conexao.php';

$id = $_GET['id'] ?? null;

if (!$id) {
    header("Location: listar_avaliacoes.php?erro=ID inválido");
    exit;
}

$stmt = $conexao->prepare("DELETE FROM formulario_quinta_flores WHERE id=?");
$stmt->bind_param("i", $id);

if ($stmt->execute()) {
    header("Location: listar_avaliacoes.php?sucesso=Avaliação eliminada com sucesso");
} else {
    header("Location: listar_avaliacoes.php?erro=Erro ao eliminar avaliação");
}
exit; 