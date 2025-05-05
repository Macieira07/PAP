<?php
require '../conexao.php';

$id = $_GET['id'];

$stmt = $conexao->prepare("DELETE FROM despesas WHERE D_id_despeza=?");
$stmt->bind_param("i", $id);
$stmt->execute();

header("Location: despesas.php");
exit;
