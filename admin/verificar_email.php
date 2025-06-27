<?php
require '../conexao.php';
$email = strtolower(trim($_GET['email'] ?? ''));
if (!$email) { echo 'ok'; exit; }
$stmt = $conexao->prepare("SELECT H_bloqueado FROM hospedes WHERE H_email = ?");
$stmt->bind_param("s", $email);
$stmt->execute();
$stmt->bind_result($bloqueado);
if ($stmt->fetch()) {
    if ($bloqueado) echo 'bloqueado';
    else echo 'existe';
} else {
    echo 'ok';
}
$stmt->close(); 