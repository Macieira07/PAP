<?php
include('../conexao.php');

$id = isset($_GET['id']) ? intval($_GET['id']) : 0;

if ($id > 0) {
    $sql = "DELETE FROM funcionarios WHERE F_id_funcionario = $id";
    $conexao->query($sql);
}

header("Location: admin_funcionarios.php");
exit;
?>
