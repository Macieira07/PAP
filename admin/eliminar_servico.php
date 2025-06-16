<?php
require '../conexao.php';

$id = $_GET['id'] ?? null;

if (!$id) {
    header("Location: servicos.php");
    exit;
}

// Buscar o caminho da imagem para apagar
$stmt = $conexao->prepare("SELECT S_imagem FROM servicos WHERE S_id_servico = ?");
$stmt->bind_param("i", $id);
$stmt->execute();
$result = $stmt->get_result();
$servico = $result->fetch_assoc();

if ($servico && !empty($servico['S_imagem']) && file_exists("../" . $servico['S_imagem'])) {
    unlink("../" . $servico['S_imagem']);
}

// Apagar o serviço da BD
$stmtDelete = $conexao->prepare("DELETE FROM servicos WHERE S_id_servico = ?");
$stmtDelete->bind_param("i", $id);
$stmtDelete->execute();

header("Location: servicos.php");
exit;
