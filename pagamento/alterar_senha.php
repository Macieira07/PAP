<?php
session_start();
require_once 'i18n.php';
if (isset($_GET['lang']) && in_array($_GET['lang'], ['pt', 'en', 'fr','es'])) {
    I18n::setLanguage($_GET['lang']);
    header('Location: ' . strtok($_SERVER['REQUEST_URI'], '?'));
    exit();
}
require_once 'header.php';
$page_title = I18n::get('change_password');
if (isset($_SESSION['senha_erro'])) {
    $erro = $_SESSION['senha_erro'];
    unset($_SESSION['senha_erro']);
}
?>
<div class="profile-container">
    <h1><i class="fas fa-key"></i> <?= I18n::get('change_password') ?></h1>
    
    <?php if (isset($erro)): ?>
        <div class="error-message" style="display: block;">
            <i class="fas fa-exclamation-circle"></i> <?= $erro ?>
        </div>
    <?php endif; ?>
    
    <?php if (isset($_SESSION['senha_sucesso'])): ?>
        <div class="success-message" style="display: block;">
            <i class="fas fa-check-circle"></i> <?= $_SESSION['senha_sucesso'] ?>
        </div>
        <?php unset($_SESSION['senha_sucesso']); ?>
    <?php endif; ?>
    
    <form action="processar_alterar_senha.php" method="POST">
        <div class="form-group">
            <label for="senha_atual"><?= I18n::get('current_password') ?></label>
            <input type="password" id="senha_atual" name="senha_atual" class="form-control" required>
        </div>
        
        <div class="form-group">
            <label for="nova_senha"><?= I18n::get('new_password') ?></label>
            <input type="password" id="nova_senha" name="nova_senha" class="form-control" required
                   pattern="(?=.*\d)(?=.*[a-z])(?=.*[A-Z]).{8,}"
                   title="<?= I18n::get('password_requirements', 'Deve conter pelo menos 8 caracteres, incluindo uma maiúscula, uma minúscula e um número') ?>">
            <small class="form-text"><?= I18n::get('password_hint', 'Mínimo 8 caracteres, com pelo menos 1 letra maiúscula, 1 minúscula e 1 número') ?></small>
        </div>
        
        <div class="form-group">
            <label for="confirmar_senha"><?= I18n::get('confirm_password') ?></label>
            <input type="password" id="confirmar_senha" name="confirmar_senha" class="form-control" required>
        </div>
        
        <div class="form-actions">
            <a href="perfil.php" class="btn btn-secondary">
                <i class="fas fa-arrow-left"></i> <?= I18n::get('back') ?>
            </a>
            <button type="submit" class="btn btn-primary">
                <i class="fas fa-save"></i> <?= I18n::get('change_password') ?>
            </button>
        </div>
    </form>
</div>
<?php require_once 'footer.php'; ?>