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
    $token = bin2hex(random_bytes(16));
    $token_expira = date('Y-m-d H:i:s', strtotime('+1 day'));

    // Verificar se o e-mail já existe
    $verifica = $conexao->prepare("SELECT COUNT(*) FROM hospedes WHERE H_email=?");
    $verifica->bind_param("s", $email);
    $verifica->execute();
    $verifica->bind_result($existe);
    $verifica->fetch();
    $verifica->close();

    if ($existe > 0) {
        header("Location: hospedes.php?erro=Já existe um hóspede com esse e-mail.");
        exit;
    }

    // Inserir novo hóspede
    $stmt = $conexao->prepare("INSERT INTO hospedes 
        (H_nome, H_apelido, H_email, H_senha, H_telefone, H_documento_ident, H_morada, 
         H_verificado_email, H_aceitou_termos_uso, H_token_verificacao, H_token_expira)
         VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");

    $stmt->bind_param("sssssssssss", $nome, $apelido, $email, $senha, $telefone, $documento, $morada, $verificado, $aceitou, $token, $token_expira);

    if ($stmt->execute()) {
        header("Location: hospedes.php?sucesso=Hóspede adicionado com sucesso.");
    } else {
        header("Location: hospedes.php?erro=Erro ao adicionar hóspede.");
    }
    exit;
}
?>

<link rel="stylesheet" href="admin.css">
<div style="display: flex; align-items: center; gap: 10px;">
    <img src="https://img.icons8.com/?size=100&id=60018&format=png&color=000000" alt="Ícone Hóspedes" style="height: 50px;">
    <h2>Adicionar um novo Hóspede</h2>
</div>
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
<form method="post">
    <label>
        <i class="fa-solid fa-user"></i> Nome:
        <input type="text" name="nome" required>
    </label><br><br>

    <label>
        <i class="fa-solid fa-user-tag"></i> Apelido:
        <input type="text" name="apelido">
    </label><br><br>

    <label>
        <i class="fa-solid fa-envelope"></i> Email:
        <input type="email" name="email" required>
    </label><br><br>

    <label>
        <i class="fa-solid fa-lock"></i> Senha:
        <input type="password" name="senha" required>
    </label><br><br>

    <label>
        <i class="fa-solid fa-phone"></i> Telefone:
        <input type="text" name="telefone" required>
    </label><br><br>

    <label>
        <i class="fa-solid fa-address-card"></i> Documento:
        <input type="text" name="documento" required>
    </label><br><br>

    <label>
        <i class="fa-solid fa-location-dot"></i> Morada:
        <input type="text" name="morada">
    </label><br><br>

    <label>
        <i class="fa-solid fa-check-circle"></i> Verificou Email?
        <select name="verificado">
            <option value="Não">Não</option>
            <option value="Sim">Sim</option>
        </select>
    </label><br><br>

    <label>
        <i class="fa-solid fa-file-contract"></i> Aceitou os Termos?
        <select name="aceitou">
            <option value="Não">Não</option>
            <option value="Sim">Sim</option>
        </select>
    </label><br><br>

    <button type="submit">Salvar</button>
</form>

<a href="hospedes.php">← Voltar</a>
