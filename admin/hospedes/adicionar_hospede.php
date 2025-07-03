<?php
require '../../conexao.php';
session_start();

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
    $senha = $_POST['senha'] ?: substr(md5(uniqid()), 0, 8);

    // Validações
    if (empty($nome) || empty($email) || empty($documento)) {
        $erro = 'Preencha todos os campos obrigatórios.';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $erro = 'Email inválido.';
    } else {
        // Verificar se email já existe
        $stmt = $conexao->prepare("SELECT H_id_hospede FROM hospedes WHERE H_email = ?");
        $stmt->bind_param("s", $email);
        $stmt->execute();
        
        if ($stmt->get_result()->num_rows > 0) {
            $erro = 'Já existe um hóspede com este email.';
        } else {
            // Inserir novo hóspede
            $senha_hash = password_hash($senha, PASSWORD_DEFAULT);
            $stmt = $conexao->prepare("INSERT INTO hospedes (H_nome, H_email, H_senha, H_telefone, H_documento_ident, H_morada, H_pais, H_verificado_email, H_aceitou_termos_uso) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)");
            $stmt->bind_param("sssssssii", $nome, $email, $senha_hash, $telefone, $documento, $morada, $pais, $verificado, $aceitou);
            
            if ($stmt->execute()) {
                $sucesso = 'Hóspede adicionado com sucesso!';
                if (isset($_GET['modal'])) {
                    echo "<script>window.parent.location.reload();</script>";
                    exit;
                }
            } else {
                $erro = 'Erro ao adicionar hóspede: ' . $stmt->error;
            }
        }
    }
}

// Se for modal, retorna apenas o conteúdo do formulário
if (isset($_GET['modal'])) {
    ?>
    <h2>Adicionar Novo Hóspede</h2>
    
    <?php if ($erro): ?>
        <div class="alert alert-error"><?= $erro ?></div>
    <?php endif; ?>
    
    <?php if ($sucesso): ?>
        <div class="alert alert-success"><?= $sucesso ?></div>
    <?php endif; ?>
    
    <form method="post" id="formHospede">
        <div class="form-group">
            <label>Nome *</label>
            <input type="text" name="nome" required>
        </div>
        
        <div class="form-group">
            <label>Email *</label>
            <input type="email" name="email" required>
        </div>
        
        <div class="form-group">
            <label>Senha</label>
            <input type="text" name="senha" placeholder="Deixe em branco para gerar automaticamente">
        </div>
        
        <div class="form-group">
            <label>Telefone</label>
            <input type="text" name="telefone">
        </div>
        
        <div class="form-group">
            <label>Documento de Identificação *</label>
            <input type="text" name="documento" required>
        </div>
        
        <div class="form-group">
            <label>Morada</label>
            <input type="text" name="morada">
        </div>
        
        <div class="form-group">
            <label>País</label>
            <input type="text" name="pais">
        </div>
        
        <div class="form-group">
            <label>
                <input type="checkbox" name="verificado" value="1">
                Email verificado
            </label>
        </div>
        
        <div class="form-group">
            <label>
                <input type="checkbox" name="aceitou" value="1">
                Aceitou os termos
            </label>
        </div>
        
        <div class="form-actions">
            <button type="submit" class="btn btn-primary">Adicionar</button>
            <button type="button" class="btn" onclick="window.parent.fecharModal()">Cancelar</button>
        </div>
    </form>
    <?php
    exit;
}
?>