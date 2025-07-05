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
            <label>País</label>
            <select name="pais" id="paisSelect" required>
                <option value="">Selecione o país</option>
                <option value="Portugal" data-prefix="351" <?= $hospede['H_pais'] == 'Portugal' ? 'selected' : '' ?>>Portugal</option>
                <option value="Espanha" data-prefix="34" <?= $hospede['H_pais'] == 'Espanha' ? 'selected' : '' ?>>Espanha</option>
                <option value="França" data-prefix="33" <?= $hospede['H_pais'] == 'França' ? 'selected' : '' ?>>França</option>
                <option value="Brasil" data-prefix="55" <?= $hospede['H_pais'] == 'Brasil' ? 'selected' : '' ?>>Brasil</option>
                <option value="Angola" data-prefix="244" <?= $hospede['H_pais'] == 'Angola' ? 'selected' : '' ?>>Angola</option>
                <option value="Cabo Verde" data-prefix="238" <?= $hospede['H_pais'] == 'Cabo Verde' ? 'selected' : '' ?>>Cabo Verde</option>
                <option value="Moçambique" data-prefix="258" <?= $hospede['H_pais'] == 'Moçambique' ? 'selected' : '' ?>>Moçambique</option>
                <option value="Outro" data-prefix="" <?= !in_array($hospede['H_pais'], ['Portugal','Espanha','França','Brasil','Angola','Cabo Verde','Moçambique']) ? 'selected' : '' ?>>Outro</option>
            </select>
        </div>
        <div class="form-group">
            <label>Telefone</label>
            <input type="text" name="telefone" id="telefoneInput" value="<?= htmlspecialchars($hospede['H_telefone'] ?? '') ?>" placeholder="Número de telefone" maxlength="15">
            <small id="telefoneHint" style="color:#888;"></small>
        </div>
        <div class="form-group">
            <label>Documento de Identificação *</label>
            <div style="display:flex; gap:8px;">
                <select name="tipo_documento" id="tipoDocumentoSelect" required>
                    <option value="Passaporte" <?= (stripos($hospede['H_documento_ident'], 'passaporte') !== false) ? 'selected' : '' ?>>Passaporte</option>
                    <option value="NIF" <?= (stripos($hospede['H_documento_ident'], 'nif') !== false) ? 'selected' : '' ?>>NIF</option>
                    <option value="DNI" <?= (stripos($hospede['H_documento_ident'], 'cartão') !== false) ? 'selected' : '' ?>>DNI</option>
                    <option value="Outro" <?= (!preg_match('/passaporte|nif|cartão/i', $hospede['H_documento_ident'])) ? 'selected' : '' ?>>Outro</option>
                </select>
                <input type="text" name="documento" id="numeroDocumentoInput" value="<?= htmlspecialchars(preg_replace('/^(Passaporte|NIF|Cartão de Cidadão|Outro)\s*:?\s*/i', '', $hospede['H_documento_ident'])) ?>" required placeholder="Número">
            </div>
        </div>
        <div class="form-actions">
            <button type="submit" class="btn btn-primary">Atualizar</button>
            <button type="button" class="btn" onclick="window.parent.fecharModal()">Cancelar</button>
        </div>
    </form>
    <script>
    // Dados de países e formatos
    const paises = {
        'Portugal': {prefix: '351', placeholder: '9XXXXXXXX', regex: /^9\d{8}$/},
        'Espanha': {prefix: '34', placeholder: '6XXXXXXXX', regex: /^[6-7]\d{8}$/},
        'França': {prefix: '33', placeholder: '6XXXXXXXX', regex: /^[6-7]\d{8}$/},
        'Brasil': {prefix: '55', placeholder: '9XXXXXXXX', regex: /^9\d{8}$/},
        'Angola': {prefix: '244', placeholder: '9XXXXXXXX', regex: /^9\d{8}$/},
        'Cabo Verde': {prefix: '238', placeholder: '9XXXXXX', regex: /^9\d{6}$/},
        'Moçambique': {prefix: '258', placeholder: '8XXXXXXXX', regex: /^8\d{8}$/},
        'Outro': {prefix: '', placeholder: 'Número internacional', regex: /^\d{6,15}$/}
    };
    function atualizarTelefoneCampos(resetTelefone = false) {
        var pais = document.getElementById('paisSelect').value;
        var info = paises[pais] || paises['Outro'];
        document.getElementById('telefoneInput').placeholder = (info.prefix ? '+'+info.prefix+' ' : '') + info.placeholder;
        document.getElementById('telefoneHint').textContent = info.prefix ? `Formato: +${info.prefix} ${info.placeholder}` : 'Formato internacional';
        if (resetTelefone) {
            document.getElementById('telefoneInput').value = '';
        }
    }
    document.getElementById('paisSelect').addEventListener('change', function() {
        atualizarTelefoneCampos(true);
    });
    window.addEventListener('DOMContentLoaded', function() {
        atualizarTelefoneCampos(false);
        // Não remover prefixo do telefone ao carregar
    });
    // Validação ao submeter
    document.getElementById('formHospede').addEventListener('submit', function(e) {
        var pais = document.getElementById('paisSelect').value;
        var info = paises[pais] || paises['Outro'];
        var telInput = document.getElementById('telefoneInput');
        var prefix = info.prefix;
        var numero = telInput.value.replace(/\D/g, '');
        if (prefix && numero && numero.indexOf(prefix) !== 0) {
            numero = numero.replace(/^0+/, '');
        }
        // Validação de dígitos
        if (info.regex && !info.regex.test(numero)) {
            alert('O número de telefone não corresponde ao formato esperado para ' + pais + ': +' + info.prefix + ' ' + info.placeholder);
            telInput.focus();
            e.preventDefault();
            return false;
        }
        // Concatena prefixo ao telefone
        if (prefix && numero.indexOf(prefix) !== 0) {
            telInput.value = prefix + numero;
        }
        // Documento: concatena tipo + número
        var tipoDoc = document.getElementById('tipoDocumentoSelect').value;
        var numDoc = document.getElementById('numeroDocumentoInput').value;
        if (tipoDoc && numDoc) {
            document.getElementById('numeroDocumentoInput').value = tipoDoc+': '+numDoc;
        }
    });
    </script>
    <?php
    exit;
}
?>