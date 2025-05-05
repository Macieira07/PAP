<?php
require '../conexao.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nome = $_POST['nome'];
    $apelido = $_POST['apelido'];
    $email = $_POST['email'];
    $senha = password_hash($_POST['senha'], PASSWORD_DEFAULT);
    $telefone = $_POST['telefone'];
    $documento = $_POST['documento'];
    $morada = $_POST['morada'];
    $verificado = $_POST['verificado'];
    $aceitou = $_POST['aceitou'];
    $token = bin2hex(random_bytes(16)); // exemplo
    $token_expira = date('Y-m-d H:i:s', strtotime('+1 day'));

    $stmt = $conexao->prepare("INSERT INTO hospedes 
        (H_nome, H_apelido, H_email, H_senha, H_telefone, H_documento_ident, H_morada, 
         H_verificado_email, H_aceitou_termos_uso, H_token_verificacao, H_token_expira)
         VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");

    $stmt->bind_param("sssssssssss", $nome, $apelido, $email, $senha, $telefone, $documento, $morada, $verificado, $aceitou, $token, $token_expira);
    $stmt->execute();

    header("Location: hospedes.php");
    exit;
}
?>
<link rel="stylesheet" href="admin.css">
<h2>Adicionar Hóspede</h2>
<form method="post">
    Nome: <input type="text" name="nome" required><br><br>
    Apelido: <input type="text" name="apelido"><br><br>
    Email: <input type="email" name="email" required><br><br>
    Senha: <input type="password" name="senha" required><br><br>
    Telefone: <input type="text" name="telefone" required><br><br>
    Documento: <input type="text" name="documento" required><br><br>
    Morada: <input type="text" name="morada"><br><br>
    Verificou Email?
    <select name="verificado">
        <option value="Não">Não</option>
        <option value="Sim">Sim</option>
    </select><br><br>
    Aceitou os Termos?
    <select name="aceitou">
        <option value="Não">Não</option>
        <option value="Sim">Sim</option>
    </select><br><br>
    <button type="submit">Salvar</button>
</form>
<a href="hospedes.php">← Voltar</a>
