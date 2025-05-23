<?php
require_once '../conexao.php';
require_once 'header.php';

// Buscar dados do usuário
$query = "SELECT * FROM hospedes WHERE H_id_hospede = ?";
$stmt = $conexao->prepare($query);
$stmt->bind_param("i", $_SESSION['id']);
$stmt->execute();
$resultado = $stmt->get_result();
$usuario = $resultado->fetch_assoc();
?>

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
    
    <div class="profile-section">
        <h2><i class="fas fa-history"></i> Histórico Recente</h2>
        <div class="recent-activity">
            <!-- Aqui você pode mostrar as últimas atividades -->
            <div class="activity-item">
                <i class="fas fa-calendar-check"></i>
                <span>Último login: 
  <?php
    if (!empty($usuario['H_ultimo_login'])) {
        echo date('d/m/Y H:i', strtotime($usuario['H_ultimo_login']));
    } else {
        echo "Sem dados de último login";
    }
  ?>
</span>
    
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