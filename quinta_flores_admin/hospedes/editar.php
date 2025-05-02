<?php
require_once '../includes/auth.php';
require_once '../includes/conexao.php';
verificarLogin();

if (!isset($_GET['id'])) {
    header('Location: listar.php');
    exit();
}

$id = $_GET['id'];
$erro = '';
$hospede = null;

try {
    $pdo = conexao();
    
    // Carregar dados do hóspede
    $stmt = $pdo->prepare("SELECT * FROM hospedes WHERE H_id_hospede = ?");
    $stmt->execute([$id]);
    $hospede = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$hospede) {
        header('Location: listar.php');
        exit();
    }
    
    // Processar formulário
    if ($_SERVER['REQUEST_METHOD'] == 'POST') {
        $nome = $_POST['nome'];
        $email = $_POST['email'];
        $telefone = $_POST['telefone'];
        $documento = $_POST['documento'];
        $morada = $_POST['morada'];
        $verificado = isset($_POST['verificado']) ? 'Sim' : 'Não';
        
        // Verificar se email já existe (exceto para o próprio hóspede)
        $stmt = $pdo->prepare("SELECT COUNT(*) FROM hospedes WHERE H_email = ? AND H_id_hospede != ?");
        $stmt->execute([$email, $id]);
        if ($stmt->fetchColumn() > 0) {
            $erro = "Este e-mail já está em uso!";
        } else {
            // Atualizar hóspede
            $stmt = $pdo->prepare("UPDATE hospedes SET H_nome = ?, H_email = ?, H_telefone = ?, H_documento_ident = ?, H_morada = ?, H_verificado_email = ? WHERE H_id_hospede = ?");
            $stmt->execute([$nome, $email, $telefone, $documento, $morada, $verificado, $id]);
            
            // Se foi enviada uma nova senha
            if (!empty($_POST['senha'])) {
                $senha = password_hash($_POST['senha'], PASSWORD_DEFAULT);
                $stmt = $pdo->prepare("UPDATE hospedes SET H_senha = ? WHERE H_id_hospede = ?");
                $stmt->execute([$senha, $id]);
            }
            
            $_SESSION['mensagem'] = "Hóspede atualizado com sucesso!";
            header('Location: listar.php');
            exit();
        }
    }
} catch (PDOException $e) {
    $erro = "Erro ao atualizar hóspede: " . $e->getMessage();
}

$titulo = "Editar Hóspede - Quinta Flores";
?>
<?php include '../includes/header.php'; ?>

<div class="welcome-card">
    <h1>Editar Hóspede</h1>
</div>

<div class="form-container">
    <?php if ($erro): ?>
        <div class="error-message"><?= $erro ?></div>
    <?php endif; ?>
    
    <form method="POST">
        <div class="form-group">
            <label for="nome">Nome Completo *</label>
            <input type="text" id="nome" name="nome" value="<?= htmlspecialchars($hospede['H_nome']) ?>" required>
        </div>
        
        <div class="form-group">
            <label for="email">E-mail *</label>
            <input type="email" id="email" name="email" value="<?= htmlspecialchars($hospede['H_email']) ?>" required>
        </div>
        
        <div class="form-group">
            <label for="senha">Nova Senha (deixe em branco para não alterar)</label>
            <input type="password" id="senha" name="senha" minlength="6">
        </div>
        
        <div class="form-group">
            <label for="telefone">Telefone *</label>
            <input type="tel" id="telefone" name="telefone" value="<?= htmlspecialchars($hospede['H_telefone']) ?>" required>
        </div>
        
        <div class="form-group">
            <label for="documento">Documento de Identificação *</label>
            <input type="text" id="documento" name="documento" value="<?= htmlspecialchars($hospede['H_documento_ident']) ?>" required>
        </div>
        
        <div class="form-group">
            <label for="morada">Morada</label>
            <textarea id="morada" name="morada"><?= htmlspecialchars($hospede['H_morada']) ?></textarea>
        </div>
        
        <div class="form-group checkbox-group">
            <input type="checkbox" id="verificado" name="verificado" <?= $hospede['H_verificado_email'] == 'Sim' ? 'checked' : '' ?>>
            <label for="verificado">E-mail verificado</label>
        </div>
        
        <div class="form-actions">
            <button type="submit" class="btn-salvar">
                <i class="fas fa-save"></i> Atualizar Hóspede
            </button>
            <a href="listar.php" class="btn-cancelar">
                <i class="fas fa-times"></i> Cancelar
            </a>
        </div>
    </form>
</div>

<?php include '../includes/footer.php'; ?>