<?php
require_once 'includes/auth.php';
require_once '../conexao.php';
verificarAdmin();

$erro = '';
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $nome = $_POST['nome'];
    $email = $_POST['email'];
    $senha = password_hash($_POST['senha'], PASSWORD_DEFAULT);
    $cargo = $_POST['cargo'];
    $telefone = $_POST['telefone'];
    
    try {
        $pdo = conexao();
        
        // Verificar se email já existe
        $stmt = $pdo->prepare("SELECT COUNT(*) FROM funcionarios WHERE F_email = ?");
        $stmt->execute([$email]);
        if ($stmt->fetchColumn() > 0) {
            $erro = "Este e-mail já está em uso!";
        } else {
            $stmt = $pdo->prepare("INSERT INTO funcionarios (F_nome, F_email, F_senha, F_cargo, F_telefone) VALUES (?, ?, ?, ?, ?)");
            $stmt->execute([$nome, $email, $senha, $cargo, $telefone]);
            
            $_SESSION['mensagem'] = "Funcionário adicionado com sucesso!";
            header('Location: listar.php');
            exit();
        }
    } catch (PDOException $e) {
        $erro = "Erro ao cadastrar funcionário: " . $e->getMessage();
    }
}

$titulo = "Adicionar Funcionário - Quinta Flores";
?>
<?php include '../includes/header.php'; ?>

<div class="welcome-card">
    <h1>Adicionar Novo Funcionário</h1>
</div>

<div class="form-container">
    <?php if ($erro): ?>
        <div class="error-message"><?= $erro ?></div>
    <?php endif; ?>
    
    <form method="POST">
        <div class="form-group">
            <label for="nome">Nome Completo *</label>
            <input type="text" id="nome" name="nome" required>
        </div>
        
        <div class="form-group">
            <label for="email">E-mail *</label>
            <input type="email" id="email" name="email" required>
        </div>
        
        <div class="form-group">
            <label for="senha">Senha *</label>
            <input type="password" id="senha" name="senha" required minlength="6">
        </div>
        
        <div class="form-group">
            <label for="cargo">Cargo *</label>
            <select id="cargo" name="cargo" required>
                <option value="funcionário">Funcionário</option>
                <option value="gerente">Gerente</option>
                <option value="administrador">Administrador</option>
            </select>
        </div>
        
        <div class="form-group">
            <label for="telefone">Telefone</label>
            <input type="tel" id="telefone" name="telefone" pattern="[0-9]{9}">
        </div>
        
        <div class="form-actions">
            <button type="submit" class="btn-salvar">
                <i class="fas fa-save"></i> Salvar Funcionário
            </button>
            <a href="listar.php" class="btn-cancelar">
                <i class="fas fa-times"></i> Cancelar
            </a>
        </div>
    </form>
</div>

<?php include '../includes/footer.php'; ?>