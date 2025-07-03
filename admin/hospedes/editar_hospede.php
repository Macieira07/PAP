<?php
require '../../conexao.php';
session_start();

$id = $_GET['id'] ?? null;
if (!$id) die('ID do hóspede não especificado.');

// Buscar dados do hóspede
$stmt = $conexao->prepare("SELECT * FROM hospedes WHERE H_id_hospede = ?");
$stmt->bind_param("i", $id);
$stmt->execute();
$hospede = $stmt->get_result()->fetch_assoc();

if (!$hospede) die('Hóspede não encontrado.');

$erro = '';
$sucesso = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nome = trim($_POST['nome'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $telefone = trim($_POST['telefone'] ?? '');
    $documento = trim($_POST['documento'] ?? '');
    $morada = trim($_POST['morada'] ?? '');
    $pais = trim($_POST['pais'] ?? '');
    $verificado = isset($_POST['verificado']) ? 1 : 0;
    $aceitou = isset($_POST['aceitou']) ? 1 : 0;
    $senha = $_POST['senha'] ?? null;

    // Validações
    if (empty($nome) || empty($email) || empty($documento)) {
        $erro = 'Preencha todos os campos obrigatórios.';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $erro = 'Email inválido.';
    } else {
        // Verificar se email já existe (outro hóspede)
        $stmt = $conexao->prepare("SELECT H_id_hospede FROM hospedes WHERE H_email = ? AND H_id_hospede != ?");
        $stmt->bind_param("si", $email, $id);
        $stmt->execute();
        
        if ($stmt->get_result()->num_rows > 0) {
            $erro = 'Já existe outro hóspede com este email.';
        } else {
            // Atualizar hóspede
            if ($senha) {
                $senha_hash = password_hash($senha, PASSWORD_DEFAULT);
                $stmt = $conexao->prepare("UPDATE hospedes SET H_nome=?, H_email=?, H_senha=?, H_telefone=?, H_documento_ident=?, H_morada=?, H_pais=?, H_verificado_email=?, H_aceitou_termos_uso=? WHERE H_id_hospede=?");
                $stmt->bind_param("ssssssssii", $nome, $email, $senha_hash, $telefone, $documento, $morada, $pais, $verificado, $aceitou, $id);
            } else {
                $stmt = $conexao->prepare("UPDATE hospedes SET H_nome=?, H_email=?, H_telefone=?, H_documento_ident=?, H_morada=?, H_pais=?, H_verificado_email=?, H_aceitou_termos_uso=? WHERE H_id_hospede=?");
                $stmt->bind_param("sssssssii", $nome, $email, $telefone, $documento, $morada, $pais, $verificado, $aceitou, $id);
            }
            
            if ($stmt->execute()) {
                $sucesso = 'Hóspede atualizado com sucesso!';
                if (isset($_GET['modal'])) {
                    echo "<script>window.parent.location.reload();</script>";
                    exit;
                }
            } else {
                $erro = 'Erro ao atualizar hóspede: ' . $stmt->error;
            }
        }
    }
}

// Se for modal, retorna apenas o conteúdo do formulário
if (isset($_GET['modal'])) {
    ?>
    <h2>Editar Hóspede</h2>
    
    <?php if ($erro): ?>
        <div class="alert alert-error"><?= $erro ?></div>
    <?php endif; ?>
    
    <?php if ($sucesso): ?>
        <div class="alert alert-success"><?= $sucesso ?></div>
    <?php endif; ?>
    
    <form method="post" id="formHospede">
        <div class="form-group">
            <label>Nome *</label>
            <input type="text" name="nome" value="<?= htmlspecialchars($hospede['H_nome']) ?>" required>
        </div>
        
        <div class="form-group">
            <label>Email *</label>
            <input type="email" name="email" value="<?= htmlspecialchars($hospede['H_email']) ?>" required>
        </div>
        
        <div class="form-group">
            <label>Nova Senha</label>
            <input type="text" name="senha" placeholder="Deixe em branco para manter a atual">
        </div>
        
        <div class="form-group">
            <label>Telefone</label>
            <input type="text" name="telefone" value="<?= htmlspecialchars($hospede['H_telefone']) ?>">
        </div>
        
        <div class="form-group">
            <label>Documento de Identificação *</label>
            <input type="text" name="documento" value="<?= htmlspecialchars($hospede['H_documento_ident']) ?>" required>
        </div>
        
        <div class="form-group">
            <label>Morada</label>
            <input type="text" name="morada" value="<?= htmlspecialchars($hospede['H_morada']) ?>">
        </div>
        
        <div class="form-group">
            <label>País</label>
            <input type="text" name="pais" value="<?= htmlspecialchars($hospede['H_pais']) ?>">
        </div>
        
        <div class="form-group">
            <label>
                <input type="checkbox" name="verificado" value="1" <?= $hospede['H_verificado_email'] ? 'checked' : '' ?>>
                Email verificado
            </label>
        </div>
        
        <div class="form-group">
            <label>
                <input type="checkbox" name="aceitou" value="1" <?= $hospede['H_aceitou_termos_uso'] ? 'checked' : '' ?>>
                Aceitou os termos
            </label>
        </div>
        
        <div class="form-actions">
            <button type="submit" class="btn btn-primary">Atualizar</button>
            <button type="button" class="btn" onclick="window.parent.fecharModal()">Cancelar</button>
        </div>
    </form>
    <?php
    exit;
}
?>