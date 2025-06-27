<?php
require '../conexao.php';
$id = $_GET['id'] ?? null;
$acao = $_GET['acao'] ?? null;
if (!$id || !in_array($acao, ['bloquear', 'desbloquear'])) {
    http_response_code(400); exit('Parâmetros inválidos');
}
$valor = $acao === 'bloquear' ? 1 : 0;
$stmt = $conexao->prepare("UPDATE hospedes SET H_bloqueado=? WHERE H_id_hospede=?");
$stmt->bind_param("ii", $valor, $id);
if ($stmt->execute()) {
    echo 'ok';
} else {
    http_response_code(500); echo 'erro';
} 