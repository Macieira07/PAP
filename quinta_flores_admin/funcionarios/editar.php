<?php
require_once '../includes/auth.php';
require_once '../includes/conexao.php';
verificarAdmin();

if (!isset($_GET['id'])) {
    header('Location: listar.php');
    exit();
}

$id = $_GET['id'];
$erro = '';
$funcionario = null;

try {
    $pdo = conexao();
    
    // Carregar dados do funcionário
    $stmt = $pdo->prepare("SELECT * FROM funcionarios WHERE F_id_funcionario = ?");
    $stmt->execute([$id]);
    $funcionario = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$funcionario) {
        header('Location: listar.php');
        exit();
    }
    
    // Processar formulário
    if ($_SERVER['REQUEST_METHOD'] == 'POST') {
        $nome = $_POST['nome'];
        $email = $_POST['email'];
        $cargo = $_POST['cargo'];
        $telefone = $_POST['telefone'];
        
        // Verificar se email já existe (exceto para o próprio funcionário)
        $stmt = $pdo->prepare("SELECT COUNT(*) FROM funcionarios WHERE F_email = ? AND F_id_funcionario != ?");
        $stmt->execute([$email, $id]);
        if ($stmt->fetchColumn() > 0) {
            $erro = "Este e-mail já está em uso!";
        } else {
            // Atualizar funcionário
            $stmt = $pdo->prepare("UPDATE funcionarios SET F_nome = ?, F_email = ?, F_cargo = ?, F_telefone = ? WHERE F_id_funcionario = ?");
            $stmt->execute([$nome, $email, $cargo, $telefone, $id]);
            
            // Se foi enviada uma nova senha
            if (!empty($_POST['senha'])) {
                $senha = password_hash($_POST['senha'], PASSWORD_DEFAULT);
                $stmt = $pdo->prepare("UPDATE funcionarios SET F_senha = ? WHERE F_id_funcionario = ?");
                $stmt->execute([$senha, $id]);
            }
            
            $_SESSION['mensagem'] = "Funcionário atualizado com sucesso!";
            header('Location: listar.php');
            exit();
        }
    }
} catch (PDOException $e) {
    $erro = "Erro ao atualizar funcionário: " . $e->getMessage();
}

$titulo = "Editar Funcionário - Quinta Flores";
?>
<?php include '../includes/header.php'; ?>

<div class="welcome-card">
    <h1>Editar Funcionário</h1>
</div>

<div class="form-container">
    <?php if ($erro): ?>
        <div class="error-message"><?= $erro ?></div>
    <?php endif; ?>
    
    <form method="POST">
        <div class="form-group">
            <label for="nome">Nome Completo *</label>
            <input type="text" id="nome" name="nome" value="<?= htmlspecialchars($funcionario['F_nome']) ?>" required>
        </div>
        
        <div class="form-group">
            <label for="email">E-mail *</label>
            <input type="email" id="email" name="email" value="<?= htmlspecialchars($funcionario['F_email']) ?>" required>
        </div>
        
        <div class="form-group">
            <label for="senha">Nova Senha (deixe em branco para não alterar)</label>
            <input type="password" id="senha" name="senha" minlength="6">
        </div>
        
        <div class="form-group">
            <label for="cargo">Cargo *</label>
            <select id="cargo" name="cargo" required>
                <option value="funcionário" <?= $funcionario['F_cargo'] == 'funcionário' ? 'selected' : '' ?>>Funcionário</option>
                <option value="gerente" <?= $funcionario['F_cargo'] == 'gerente' ? 'selected' : '' ?>>Gerente</option>
                <option value="administrador" <?= $funcionario['F_cargo'] == 'administrador' ? 'selected' : '' ?>>Administrador</option>
            </select>
        </div>
        
        <div class="form-group">
            <label for="telefone">Telefone</label>
            <input type="tel" id="telefone" name="telefone" value="<?= htmlspecialchars($funcionario['F_telefone']) ?>" pattern="[0-9]{9}">
        </div>
        
        <div class="form-actions">
            <button type="submit" class="btn-salvar">
                <i class="fas fa-save"></i> Atualizar Funcionário
            </button>
            <a href="listar.php" class="btn-cancelar">
                <i class="fas fa-times"></i> Cancelar
            </a>
        </div>
    </form>
</div>

<?php include '../includes/footer.php'; ?>