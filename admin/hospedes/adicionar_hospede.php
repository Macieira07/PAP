<?php
require '../../conexao.php';
session_start();

$erro = '';
$sucesso = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nome = trim($_POST['nome'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $telefone = trim($_POST['telefone'] ?? '');
    $tipo_documento = trim($_POST['tipo_documento'] ?? '');
    $numero_documento = trim($_POST['documento'] ?? '');
    $documento = $tipo_documento && $numero_documento ? ($tipo_documento . ': ' . $numero_documento) : $numero_documento;
    $pais = trim($_POST['pais'] ?? '');
    $verificado = isset($_POST['verificado']) ? 1 : 0;
    $aceitou = isset($_POST['aceitou']) ? 1 : 0;
    $senha = $_POST['senha'] ?? null;
    $morada = '';

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
            // Inserir hóspede
            $senha = $senha ?: substr(md5(uniqid()), 0, 8);
            $senha_hash = password_hash($senha, PASSWORD_DEFAULT);
            $stmt = $conexao->prepare("INSERT INTO hospedes (H_nome, H_email, H_senha, H_telefone, H_documento_ident, H_morada, H_pais, H_verificado_email, H_aceitou_termos_uso) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)");
            $stmt->bind_param("sssssssii", $nome, $email, $senha_hash, $telefone, $documento, $morada, $pais, $verificado, $aceitou);
            if ($stmt->execute()) {
                $sucesso = 'Hóspede adicionado com sucesso!';
            } else {
                $erro = 'Erro ao adicionar hóspede: ' . $stmt->error;
            }
        }
    }
}
?>
<h2>Adicionar Hóspede</h2>

<?php if ($erro): ?>
    <div class="alert alert-error"><?= $erro ?></div>
<?php endif; ?>

<?php if ($sucesso): ?>
    <div class="alert alert-success"><?= $sucesso ?></div>
<?php endif; ?>

<form method="post" id="formHospede">
    <div class="form-group">
        <label>Nome *</label>
        <input type="text" name="nome" value="<?= htmlspecialchars($_POST['nome'] ?? '') ?>" required>
    </div>
    <div class="form-group">
        <label>Email *</label>
        <input type="email" name="email" value="<?= htmlspecialchars($_POST['email'] ?? '') ?>" required>
    </div>
    <div class="form-group">
        <label>Nova Senha</label>
        <input type="text" name="senha" placeholder="Deixe em branco para gerar uma senha">
    </div>
    <div class="form-group">
        <label>País</label>
        <select name="pais" id="paisSelect" required>
            <option value="">Selecione o país</option>
            <option value="Portugal" data-prefix="351" <?= (($_POST['pais'] ?? '') == 'Portugal') ? 'selected' : '' ?>>Portugal</option>
            <option value="Espanha" data-prefix="34" <?= (($_POST['pais'] ?? '') == 'Espanha') ? 'selected' : '' ?>>Espanha</option>
            <option value="França" data-prefix="33" <?= (($_POST['pais'] ?? '') == 'França') ? 'selected' : '' ?>>França</option>
            <option value="Brasil" data-prefix="55" <?= (($_POST['pais'] ?? '') == 'Brasil') ? 'selected' : '' ?>>Brasil</option>
            <option value="Angola" data-prefix="244" <?= (($_POST['pais'] ?? '') == 'Angola') ? 'selected' : '' ?>>Angola</option>
            <option value="Cabo Verde" data-prefix="238" <?= (($_POST['pais'] ?? '') == 'Cabo Verde') ? 'selected' : '' ?>>Cabo Verde</option>
            <option value="Moçambique" data-prefix="258" <?= (($_POST['pais'] ?? '') == 'Moçambique') ? 'selected' : '' ?>>Moçambique</option>
            <option value="Outro" data-prefix="" <?= (!in_array(($_POST['pais'] ?? ''), ['Portugal','Espanha','França','Brasil','Angola','Cabo Verde','Moçambique'])) && !empty($_POST['pais']) ? 'selected' : '' ?>>Outro</option>
        </select>
    </div>
    <div class="form-group">
        <label>Telefone</label>
        <input type="text" name="telefone" id="telefoneInput" value="<?= htmlspecialchars($_POST['telefone'] ?? '') ?>" placeholder="Número de telefone" maxlength="15">
        <small id="telefoneHint" style="color:#888;"></small>
    </div>
    <div class="form-group">
        <label>Documento de Identificação *</label>
        <div style="display:flex; gap:8px;">
            <select name="tipo_documento" id="tipoDocumentoSelect" required>
                <option value="Passaporte" <?= (stripos($_POST['documento'] ?? '', 'passaporte') !== false) ? 'selected' : '' ?>>Passaporte</option>
                <option value="NIF" <?= (stripos($_POST['documento'] ?? '', 'nif') !== false) ? 'selected' : '' ?>>NIF</option>
                <option value="DNI" <?= (stripos($_POST['documento'] ?? '', 'dni') !== false) ? 'selected' : '' ?>>DNI</option>
                <option value="Outro" <?= (!preg_match('/passaporte|nif|dni/i', $_POST['documento'] ?? '')) ? 'selected' : '' ?>>Outro</option>
            </select>
            <input type="text" name="documento" id="numeroDocumentoInput" value="<?= htmlspecialchars(preg_replace('/^(Passaporte|NIF|DNI|Outro)\s*:?\s*/i', '', $_POST['documento'] ?? '')) ?>" required placeholder="Número">
        </div>
    </div>
    <div class="form-actions">
        <button type="submit" class="btn btn-primary">Adicionar</button>
        <button type="reset" class="btn">Limpar</button>
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
document.getElementById('formHospede').addEventListener('submit', function(e) {
    var pais = document.getElementById('paisSelect').value;
    var info = paises[pais] || paises['Outro'];
    var telInput = document.getElementById('telefoneInput');
    var prefix = info.prefix;
    var numero = telInput.value.replace(/\D/g, '');
    if (prefix && numero && numero.indexOf(prefix) !== 0) {
        numero = numero.replace(/^0+/, '');
    }
    if (info.regex && !info.regex.test(numero)) {
        alert('O número de telefone não corresponde ao formato esperado para ' + pais + ': +' + info.prefix + ' ' + info.placeholder);
        telInput.focus();
        e.preventDefault();
        return false;
    }
    if (prefix && numero.indexOf(prefix) !== 0) {
        telInput.value = prefix + numero;
    }
    var tipoDoc = document.getElementById('tipoDocumentoSelect').value;
    var numDoc = document.getElementById('numeroDocumentoInput').value;
    if (tipoDoc && numDoc) {
        document.getElementById('numeroDocumentoInput').value = tipoDoc+': '+numDoc;
    }
});
</script>
