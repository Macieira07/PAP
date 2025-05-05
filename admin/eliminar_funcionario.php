<?php
require '../conexao.php';

$id = $_GET['id'];

$stmt = $conexao->prepare("DELETE FROM funcionarios WHERE F_id_funcionario=?");
$stmt->bind_param("i", $id);
$stmt->execute();

header("Location: funcionarios.php");
exit;
