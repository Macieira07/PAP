<?php
require '../conexao.php';

$id = $_GET['id'];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nome = $_POST['nome'];
    $email = $_POST['email'];
    $telefone = $_POST['telefone'];
    $documento = $_POST['documento'];
    $morada = $_POST['morada'];
    $verificado = $_POST['verificado'];
    $aceitou = $_POST['aceitou'];
    $notas = $_POST['notas'] ?? '';  // Garantir que as notas sejam sempre uma string
    $novaSenha = $_POST['nova_senha'];

    if (!empty($novaSenha)) {
        $senhaHash = password_hash($novaSenha, PASSWORD_DEFAULT);
        $stmt = $conexao->prepare("UPDATE hospedes SET 
            H_nome=?, H_email=?, H_telefone=?, H_documento_ident=?, 
            H_morada=?, H_verificado_email=?, H_aceitou_termos_uso=?, H_notas=?, H_senha=? 
            WHERE H_id_hospede=?");
        $stmt->bind_param("sssssssssi", $nome, $email, $telefone, $documento, $morada, $verificado, $aceitou, $notas, $senhaHash, $id);
    } else {
        $stmt = $conexao->prepare("UPDATE hospedes SET 
            H_nome=?, H_email=?, H_telefone=?, H_documento_ident=?, 
            H_morada=?, H_verificado_email=?, H_aceitou_termos_uso=?, H_notas=? 
            WHERE H_id_hospede=?");
        $stmt->bind_param("ssssssssi", $nome, $email, $telefone, $documento, $morada, $verificado, $aceitou, $notas, $id);
    }

    $stmt->execute();
    header("Location: hospedes.php?sucesso=Hóspede atualizado com sucesso");
    exit;
}

$stmt = $conexao->prepare("SELECT * FROM hospedes WHERE H_id_hospede=?");
$stmt->bind_param("i", $id);
$stmt->execute();
$resultado = $stmt->get_result();
$h = $resultado->fetch_assoc();


// Após atualizar o hóspede
if ($stmt->execute()) {
    // Registrar no histórico
    $detalhes = "Alterações: ";
    $campos = ['nome', 'email', 'telefone', 'documento', 'morada'];
    foreach ($campos as $campo) {
        if ($h["H_$campo"] != $_POST[$campo]) {
            $detalhes .= "$campo: {$h["H_$campo"]} → {$_POST[$campo]}, ";
        }
    }
    $detalhes = rtrim($detalhes, ', ');

    $stmt_hist = $conexao->prepare("INSERT INTO historico_hospedes 
        (H_id_hospede, acao, detalhes, usuario) 
        VALUES (?, 'Edição', ?, ?)");
    $stmt_hist->bind_param("iss", $id, $detalhes, $_SESSION['usuario_nome']);
    $stmt_hist->execute();
    
    header("Location: hospedes.php?sucesso=Atualizado");
}

?>

<h2>Editar Hóspede</h2>
<link rel="stylesheet" href="admin.css">

<form method="post">
    Nome: <input type="text" name="nome" value="<?= htmlspecialchars($h['H_nome'] ?? '') ?>" required><br><br>
    Email: <input type="email" name="email" value="<?= htmlspecialchars($h['H_email'] ?? '') ?>" required><br><br>
    Telefone: <input type="text" name="telefone" value="<?= htmlspecialchars($h['H_telefone'] ?? '') ?>" required><br><br>
    Documento: <input type="text" name="documento" value="<?= htmlspecialchars($h['H_documento_ident'] ?? '') ?>" required><br><br>
    Morada: <input type="text" name="morada" value="<?= htmlspecialchars($h['H_morada'] ?? '') ?>"><br><br>
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
    
    Notas:
    <textarea name="notas" rows="4" cols="50"><?= htmlspecialchars($h['H_notas'] ?? '') ?></textarea><br><br>

    Nova Senha (se quiser alterar):
    <input type="text" name="nova_senha" id="nova_senha" readonly style="width: 120px;">
    <button type="button" onclick="gerarNovaSenha()">Gerar Código</button>
    <br><br>

    <button type="submit">Atualizar</button>
</form>

<a href="hospedes.php">← Voltar</a>

<script>
function gerarNovaSenha() {
    const codigo = Math.floor(100000 + Math.random() * 900000);
    document.getElementById('nova_senha').value = codigo;
}
</script>
