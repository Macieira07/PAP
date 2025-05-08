<?php
require '../conexao.php';

$id = $_GET['id'];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nome = $_POST['nome'];
    $apelido = $_POST['apelido'];
    $email = $_POST['email'];
    $telefone = $_POST['telefone'];
    $documento = $_POST['documento'];
    $morada = $_POST['morada'];
    $verificado = $_POST['verificado'];
    $aceitou = $_POST['aceitou'];

    $stmt = $conexao->prepare("UPDATE hospedes SET 
        H_nome=?, H_apelido=?, H_email=?, H_telefone=?, H_documento_ident=?, 
        H_morada=?, H_verificado_email=?, H_aceitou_termos_uso=? 
        WHERE H_id_hospede=?");

    $stmt->bind_param("ssssssssi", $nome, $apelido, $email, $telefone, $documento, $morada, $verificado, $aceitou, $id);
    $stmt->execute();

    header("Location: hospedes.php?sucesso=Hóspede atualizado com sucesso");

    exit;
}

$stmt = $conexao->prepare("SELECT * FROM hospedes WHERE H_id_hospede=?");
$stmt->bind_param("i", $id);
$stmt->execute();
$resultado = $stmt->get_result();
$h = $resultado->fetch_assoc();
?>

<div style="display: flex; align-items: center; gap: 10px;">
        <img src="https://img.icons8.com/?size=100&id=60018&format=png&color=000000" alt="Ícone Hóspedes" style="height: 50px;">
        <h2>Editar Hóspede</h2>
    </div>
<link rel="stylesheet" href="admin.css">
<form method="post">
    Nome: <input type="text" name="nome" value="<?= $h['H_nome'] ?>" required><br><br>
    Apelido: <input type="text" name="apelido" value="<?= $h['H_apelido'] ?>"><br><br>
    Email: <input type="email" name="email" value="<?= $h['H_email'] ?>" required><br><br>
    Telefone: <input type="text" name="telefone" value="<?= $h['H_telefone'] ?>" required><br><br>
    Documento: <input type="text" name="documento" value="<?= $h['H_documento_ident'] ?>" required><br><br>
    Morada: <input type="text" name="morada" value="<?= $h['H_morada'] ?>"><br><br>
    Verificou Email?
    <select name="verificado">
        <option value="Não" <?= $h['H_verificado_email'] == 'Não' ? 'selected' : '' ?>>Não</option>
        <option value="Sim" <?= $h['H_verificado_email'] == 'Sim' ? 'selected' : '' ?>>Sim</option>
    </select><br><br>
    Aceitou os Termos?
    <select name="aceitou">
        <option value="Não" <?= $h['H_aceitou_termos_uso'] == 'Não' ? 'selected' : '' ?>>Não</option>
        <option value="Sim" <?= $h['H_aceitou_termos_uso'] == 'Sim' ? 'selected' : '' ?>>Sim</option>
    </select><br><br>
    <button type="submit">Atualizar</button>
</form>
<a href="hospedes.php">← Voltar</a>
