<?php
require '../../conexao.php';

$id = $_GET['id'] ?? null;

if (!$id) {
    header("Location: hospedes.php?erro=ID inválido");
    exit;
}

$stmt = $conexao->prepare("DELETE FROM hospedes WHERE H_id_hospede=?");
$stmt->bind_param("i", $id);

if ($stmt->execute()) {
    header("Location: hospedes.php?sucesso=Hóspede eliminado com sucesso");
} else {
    header("Location: hospedes.php?erro=Erro ao eliminar hóspede");
}
exit;
