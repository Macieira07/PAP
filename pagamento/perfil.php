<?php
require_once '../conexao.php';
require_once 'header.php';
// Prepara a consulta SQL para buscar os dados do hóspede atual (com base no ID da sessão)
$query = "SELECT * FROM hospedes WHERE H_id_hospede = ?";
$stmt = $conexao->prepare($query);
// Liga o parâmetro (ID do hóspede) à consulta
$stmt->bind_param("i", $_SESSION['id']);
// Executa a consulta
$stmt->execute();
// Obtém o resultado da consulta
$resultado = $stmt->get_result();
// Pega a primeira linha (dados do usuário)
$usuario = $resultado->fetch_assoc();
?>
<link rel="stylesheet" href="../index/chatbot.css">
<div class="profile-container">
    <h1><i class="fas fa-user"></i> Meu Perfil</h1>
    <div class="profile-section">
        <h2><i class="fas fa-id-card"></i> Informações Pessoais</h2>
        <div class="profile-info">
            <div class="info-item">
                <span class="info-label">Nome Completo:</span>
                <span class="info-value"><?= htmlspecialchars($usuario['H_nome']) ?></span>
            </div>
            <div class="info-item">
                <span class="info-label">Email:</span>
                <span class="info-value"><?= htmlspecialchars($usuario['H_email']) ?></span>
            </div>
            <div class="info-item">
                <span class="info-label">Telefone:</span>
                <span class="info-value"><?= htmlspecialchars($usuario['H_telefone']) ?></span>
            </div>
            <div class="info-item">
                <span class="info-label">Documento:</span>
                <span class="info-value"><?= htmlspecialchars($usuario['H_documento_ident']) ?></span>
            </div>
            <button id="editProfileBtn" class="btn btn-secondary">
                <i class="fas fa-edit"></i> Editar Perfil
            </button>
        </div>
    </div>

    <!-- Formulário de edição (inicialmente oculto) -->
    <div id="editProfileForm" class="profile-edit-form" style="display:none;">
        <h2><i class="fas fa-edit"></i> Editar Perfil</h2>
        <form action="atualizar_perfil.php" method="POST">
            <div class="form-group">
                <label for="nome">Nome Completo</label>
                <input type="text" id="nome" name="nome" class="form-control" 
                       value="<?= htmlspecialchars($usuario['H_nome']) ?>" required>
            </div>
            
            <div class="form-group">
                <label for="telefone">Telefone</label>
                <input type="tel" id="telefone" name="telefone" class="form-control" 
                       value="<?= htmlspecialchars($usuario['H_telefone']) ?>" required>
            </div>
            
            <div class="form-group">
                <label for="documento">Documento de Identificação</label>
                <input type="text" id="documento" name="documento" class="form-control" 
                       value="<?= htmlspecialchars($usuario['H_documento_ident']) ?>" required>
            </div>
            
            <div class="form-actions">
                <button type="button" id="cancelEditBtn" class="btn btn-secondary">
                    <i class="fas fa-times"></i> Cancelar
                </button>
                <button type="submit" class="btn btn-primary">
                    <i class="fas fa-save"></i> Salvar Alterações
                </button>
            </div>
        </form>
    </div>

    <div class="profile-section">
        <h2><i class="fas fa-lock"></i> Segurança</h2>
        <div class="security-actions">
            <a href="alterar_senha.php" class="btn btn-secondary">
                <i class="fas fa-key"></i> Alterar Senha
            </a>
        </div>
    </div>
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