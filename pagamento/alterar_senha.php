<?php
session_start();
require_once 'header.php';

if (isset($_SESSION['senha_erro'])) {
    $erro = $_SESSION['senha_erro'];
    unset($_SESSION['senha_erro']);
}
?>

<div class="profile-container">
    <h1><i class="fas fa-key"></i> Alterar Senha</h1>
    
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
            <label for="senha_atual">Senha Atual</label>
            <input type="password" id="senha_atual" name="senha_atual" class="form-control" required>
        </div>
        
        <div class="form-group">
            <label for="nova_senha">Nova Senha</label>
            <input type="password" id="nova_senha" name="nova_senha" class="form-control" required
                   pattern="(?=.*\d)(?=.*[a-z])(?=.*[A-Z]).{8,}"
                   title="Deve conter pelo menos 8 caracteres, incluindo uma maiúscula, uma minúscula e um número">
            <small class="form-text">Mínimo 8 caracteres, com pelo menos 1 letra maiúscula, 1 minúscula e 1 número</small>
        </div>
        
        <div class="form-group">
            <label for="confirmar_senha">Confirmar Nova Senha</label>
            <input type="password" id="confirmar_senha" name="confirmar_senha" class="form-control" required>
        </div>
        
        <div class="form-actions">
            <a href="perfil.php" class="btn btn-secondary">
                <i class="fas fa-arrow-left"></i> Voltar
            </a>
            <button type="submit" class="btn btn-primary">
                <i class="fas fa-save"></i> Alterar Senha
            </button>
        </div>
    </form>
</div>

<?php require_once 'footer.php'; ?>