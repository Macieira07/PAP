<?php
require '../conexao.php';

$id = $_GET['id'];

$stmt = $conexao->prepare("DELETE FROM casas WHERE C_id_casa=?");
$stmt->bind_param("i", $id);
$stmt->execute();

header("Location: casas.php");
exit;
