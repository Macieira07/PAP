<?php
require '../conexao.php';

$id = $_GET['id'];

$stmt = $conexao->prepare("DELETE FROM hospedes WHERE H_id_hospede=?");
$stmt->bind_param("i", $id);
$stmt->execute();

header("Location: hospedes.php");
exit;
