<?php
session_start(); // Assegura que a sessão está ativa para $_SESSION['usuario_nome']

require '../../conexao.php';

$id = $_GET['id'] ?? null;

if (!$id) {
    die("ID do hóspede não fornecido.");
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nome = $_POST['nome'] ?? '';
    $email = $_POST['email'] ?? '';
    $telefone = $_POST['telefone'] ?? '';
    $documento = $_POST['documento'] ?? '';
    $morada = $_POST['morada'] ?? '';
    $verificado = isset($_POST['verificado']) ? ($_POST['verificado'] === 'Sim' ? 1 : 0) : 0;
    $aceitou = isset($_POST['aceitou']) ? ($_POST['aceitou'] === 'Sim' ? 1 : 0) : 0;
    $notas = $_POST['notas'] ?? '';  // Garantir que as notas sejam sempre uma string
    $novaSenha = $_POST['nova_senha'] ?? '';

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

    if (!$stmt->execute()) {
        die("Erro ao atualizar hóspede: " . $stmt->error);
    }

    // Buscar dados antigos para comparação (antes da atualização)
    $stmt_select = $conexao->prepare("SELECT * FROM hospedes WHERE H_id_hospede=?");
    $stmt_select->bind_param("i", $id);
    $stmt_select->execute();
    $resultado = $stmt_select->get_result();
    $h = $resultado->fetch_assoc();

    // Registrar no histórico
    $detalhes = "Alterações: ";
    $campos = ['nome', 'email', 'telefone', 'documento_ident', 'morada'];
    foreach ($campos as $campo) {
        $campo_bd = "H_$campo";
        if (isset($_POST[$campo]) && $h[$campo_bd] != $_POST[$campo]) {
            $detalhes .= "$campo: {$h[$campo_bd]} → {$_POST[$campo]}, ";
        }
    }
    $detalhes = rtrim($detalhes, ', ');

    $usuario_nome = $_SESSION['usuario_nome'] ?? 'Desconhecido';
    $stmt_hist = $conexao->prepare("INSERT INTO historico_hospedes 
        (H_id_hospede, acao, detalhes, usuario) 
        VALUES (?, 'Edição', ?, ?)");
    $stmt_hist->bind_param("iss", $id, $detalhes, $usuario_nome);
    $stmt_hist->execute();

    header("Location: hospedes.php?sucesso=Hóspede atualizado com sucesso");
    exit;
}

// Se não for POST, busca os dados do hóspede para mostrar no formulário
$stmt = $conexao->prepare("SELECT * FROM hospedes WHERE H_id_hospede=?");
$stmt->bind_param("i", $id);
$stmt->execute();
$resultado = $stmt->get_result();
$h = $resultado->fetch_assoc();

?>

<link rel="icon" type="image/png" sizes="32x32" href="../assets/logos/favicon-32x32.png">
<link rel="icon" type="image/png" sizes="16x16" href="../assets/logos/favicon-16x16.png">

<h2>Editar Hóspede</h2>
<link rel="stylesheet" href="global.css">

<form method="post">
    Nome: <input type="text" name="nome" value="<?= htmlspecialchars($h['H_nome'] ?? '') ?>" required><br><br>
    Email: <input type="email" name="email" value="<?= htmlspecialchars($h['H_email'] ?? '') ?>" required><br><br>
    Telefone: <input type="text" name="telefone" value="<?= htmlspecialchars($h['H_telefone'] ?? '') ?>" required><br><br>
    Documento: <input type="text" name="documento" value="<?= htmlspecialchars($h['H_documento_ident'] ?? '') ?>" required><br><br>
    Morada: <input type="text" name="morada" value="<?= htmlspecialchars($h['H_morada'] ?? '') ?>"><br><br>
    Verificou Email?
    <select name="verificado">
        <option value="Não" <?= (isset($h['H_verificado_email']) && $h['H_verificado_email'] == 0) ? 'selected' : '' ?>>Não</option>
        <option value="Sim" <?= (isset($h['H_verificado_email']) && $h['H_verificado_email'] == 1) ? 'selected' : '' ?>>Sim</option>
    </select><br><br>
    Aceitou os Termos?
    <select name="aceitou">
        <option value="Não" <?= (isset($h['H_aceitou_termos_uso']) && $h['H_aceitou_termos_uso'] == 0) ? 'selected' : '' ?>>Não</option>
        <option value="Sim" <?= (isset($h['H_aceitou_termos_uso']) && $h['H_aceitou_termos_uso'] == 1) ? 'selected' : '' ?>>Sim</option>
    </select><br><br>

    Notas:<br>
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
