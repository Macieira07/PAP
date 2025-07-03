<?php
require '../../conexao.php';

// Verifica se o parâmetro id está definido e é numérico
if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    header("Location: ../reservas.php?erro=parametro_invalido");
    exit;
}

$id = (int)$_GET['id'];

$stmt = $conexao->prepare("DELETE FROM reservas WHERE R_id_reserva = ?");
if ($stmt) {
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $stmt->close();
    header("Location: ../reservas.php?sucesso=1");
    exit;
} else {
    // Erro ao preparar statement
    header("Location: ../reservas.php?erro=stmt");
    exit;
}
