<?php
include('../conexao.php');
$id = intval($_GET['id']);
$conexao->query("DELETE FROM hospedes WHERE H_id_hospede = $id");
header("Location: admin_hospedes.php");
?>
