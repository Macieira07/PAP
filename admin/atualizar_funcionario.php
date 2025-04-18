<?php
include('../conexao.php');

$id = intval($_POST['id']);
$nome = $_POST['nome'];
$email = $_POST['email'];
$cargo = $_POST['cargo'];
$telefone = $_POST['telefone'];

if ($id > 0) {
    // Atualizar
    $sql = "UPDATE funcionarios SET F_nome='$nome', F_email='$email', F_cargo='$cargo', F_telefone='$telefone' WHERE F_id_funcionario=$id";
} else {
    // Inserir
    $sql = "INSERT INTO funcionarios (F_nome, F_email, F_cargo, F_telefone) VALUES ('$nome', '$email', '$cargo', '$telefone')";
}

if ($conexao->query($sql) === TRUE) {
    header("Location: admin_funcionarios.php");
} else {
    echo "Erro: " . $conexao->error;
}
?>
