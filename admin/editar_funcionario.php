<?php
require '../conexao.php';

$id = $_GET['id'];

// Verificar se o formulário foi enviado
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nome = $_POST['nome'];
    $email = $_POST['email'];
    $cargo = $_POST['cargo'];
    $telefone = $_POST['telefone'];

    $stmt = $conexao->prepare("UPDATE funcionarios SET F_nome=?, F_email=?, F_cargo=?, F_telefone=? WHERE F_id_funcionario=?");
    $stmt->bind_param("ssssi", $nome, $email, $cargo, $telefone, $id);
    $stmt->execute();

    header("Location: funcionarios.php");
    exit;
}

// Buscar o funcionário atual
$stmt = $conexao->prepare("SELECT * FROM funcionarios WHERE F_id_funcionario=?");
$stmt->bind_param("i", $id);
$stmt->execute();
$resultado = $stmt->get_result();
$f = $resultado->fetch_assoc();
?>

<!DOCTYPE html>
<html lang="pt">
<head>
<link rel="stylesheet" href="admin.css">
    <meta charset="UTF-8">
    <title>Editar Funcionário</title>
</head>
<body>
    <h1>Editar Funcionário</h1>
    <a href="funcionarios.php">← Voltar</a>

    <!-- Formulário de Edição de Funcionário -->
    <form method="post">
        Nome: <input type="text" name="nome" value="<?= $f['F_nome'] ?>" required><br><br>
        Email: <input type="email" name="email" value="<?= $f['F_email'] ?>" required><br><br>
        Cargo: <input type="text" name="cargo" value="<?= $f['F_cargo'] ?>"><br><br>
        Telefone: <input type="text" name="telefone" value="<?= $f['F_telefone'] ?>"><br><br>
        <button type="submit">Atualizar</button>
    </form>
</body>
</html>
