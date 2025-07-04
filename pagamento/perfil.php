<?php
require_once '../conexao.php';
require_once 'i18n.php';
if (isset($_GET['lang']) && in_array($_GET['lang'], ['pt', 'en', 'fr','es'])) {
    I18n::setLanguage($_GET['lang']);
    header('Location: ' . strtok($_SERVER['REQUEST_URI'], '?'));
    exit();
}
require_once 'header.php';
$page_title = I18n::get('profile');
$query = "SELECT * FROM hospedes WHERE H_id_hospede = ?";
$stmt = $conexao->prepare($query);
$stmt->bind_param("i", $_SESSION['id']);
$stmt->execute();
$resultado = $stmt->get_result();
$usuario = $resultado->fetch_assoc();
?>
<link rel="stylesheet" href="../index/chatbot.css">
<div class="profile-container">
    <h1><i class="fas fa-user"></i> <?= I18n::get('profile') ?></h1>
    <div class="profile-section">
        <h2><i class="fas fa-id-card"></i> <?= I18n::get('personal_info') ?></h2>
        <div class="profile-info">
            <div class="info-item">
                <span class="info-label"><?= I18n::get('full_name') ?>:</span>
                <span class="info-value"><?= htmlspecialchars($usuario['H_nome']) ?></span>
            </div>
            <div class="info-item">
                <span class="info-label">Email:</span>
                <span class="info-value"><?= htmlspecialchars($usuario['H_email']) ?></span>
            </div>
            <div class="info-item">
                <span class="info-label"><?= I18n::get('id_number') ?>:</span>
                <span class="info-value"><?= htmlspecialchars($usuario['H_documento_ident']) ?></span>
            </div>
            <button id="editProfileBtn" class="btn btn-secondary">
                <i class="fas fa-edit"></i> <?= I18n::get('edit_profile') ?>
            </button>
            <button onclick="history.back()" class="btn btn-danger" style="margin-left: 10px;">
                <i class="fas fa-arrow-left"></i> <?= I18n::get('back') ?>
            </button>
        </div>
    </div>

    <div id="editProfileForm" class="profile-edit-form" style="display:none;">
        <h2><i class="fas fa-edit"></i> <?= I18n::get('edit_profile') ?></h2>
        <form action="atualizar_perfil.php" method="POST">
            <div class="form-group">
                <label for="nome"><?= I18n::get('full_name') ?></label>
                <input type="text" id="nome" name="nome" class="form-control" 
                       value="<?= htmlspecialchars($usuario['H_nome']) ?>" required>
            </div>

            <div class="form-group">
                <label for="documento"><?= I18n::get('id_number') ?></label>
                <input type="text" id="documento" name="documento" class="form-control" 
                       value="<?= htmlspecialchars($usuario['H_documento_ident']) ?>"
                       pattern="\d{9}" maxlength="9" title="<?= I18n::get('invalid_document', 'Introduz 9 dígitos') ?>" required>
            </div>

            <div class="form-actions">
                <button type="button" id="cancelEditBtn" class="btn btn-secondary">
                    <i class="fas fa-times"></i> <?= I18n::get('cancel') ?>
                </button>
                <button type="submit" class="btn btn-primary">
                    <i class="fas fa-save"></i> <?= I18n::get('save_changes') ?>
                </button>
            </div>
        </form>
    </div>

    <div class="profile-section">
        <h2><i class="fas fa-lock"></i> <?= I18n::get('security') ?></h2>
        <div class="security-actions">
            <a href="alterar_senha.php" class="btn btn-secondary">
                <i class="fas fa-key"></i> <?= I18n::get('change_password') ?>
            </a>
        </div>
    </div>
</div>
<script>
document.getElementById('editProfileBtn').addEventListener('click', function() {
    document.querySelector('.profile-info').style.display = 'none';
    document.getElementById('editProfileForm').style.display = 'block';
});
document.getElementById('cancelEditBtn').addEventListener('click', function() {
    document.querySelector('.profile-info').style.display = 'block';
    document.getElementById('editProfileForm').style.display = 'none';
});
</script>
<?php require_once 'footer.php'; ?>
