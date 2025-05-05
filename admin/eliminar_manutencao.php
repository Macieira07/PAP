<?php
require '../conexao.php';

$id = $_GET['id'];

$stmt = $conexao->prepare("DELETE FROM manutencao WHERE M_id_manutencao=?");
$stmt->bind_param("i", $id);
$stmt->execute();

header("Location: manutencao.php");
exit;
