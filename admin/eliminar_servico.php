<?php
require '../conexao.php';

$id = $_GET['id'];

$stmt = $conexao->prepare("DELETE FROM servicos WHERE S_id_servico=?");
$stmt->bind_param("i", $id);
$stmt->execute();

header("Location: servicos.php");
exit;
