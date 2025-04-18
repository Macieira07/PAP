<?php
include('../conexao.php');

$id = intval($_POST['id']);
$nome = $_POST['nome'];
$apelido = $_POST['apelido'];
$email = $_POST['email'];
$telefone = $_POST['telefone'];

if ($id > 0) {
    $sql = "UPDATE hospedes SET H_nome='$nome', H_apelido='$apelido', H_email='$email', H_telefone='$telefone' WHERE H_id_hospede=$id";
} else {
    $sql = "INSERT INTO hospedes (H_nome, H_apelido, H_email, H_telefone) VALUES ('$nome', '$apelido', '$email', '$telefone')";
}
$conexao->query($sql);
header("Location: admin_hospedes.php");
?>
