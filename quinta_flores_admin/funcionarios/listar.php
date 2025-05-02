<?php
require_once '../auth.php';
require_once '../conexao.php';
verificarAdmin();

try {
    $pdo = conexao();
    $funcionarios = $pdo->query("SELECT * FROM funcionarios ORDER BY F_nome")->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    die("Erro ao carregar funcionários: " . $e->getMessage());
}

$titulo = "Funcionários - Quinta Flores";
?>
<?php include '../includes/header.php'; ?>

<div class="welcome-card">
    <h1>Gestão de Funcionários</h1>
    <p class="welcome-message">Gere todos os funcionários da Quinta Flores.</p>
</div>

<div class="table-container">
    <a href="adicionar.php" class="btn-adicionar">
        <i class="fas fa-plus"></i> Adicionar Funcionário
    </a>
    
    <table>
        <thead>
            <tr>
                <th>ID</th>
                <th>Nome</th>
                <th>E-mail</th>
                <th>Cargo</th>
                <th>Telefone</th>
                <th>Contratado em</th>
                <th>Ações</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($funcionarios as $func): ?>
            <tr>
                <td><?= $func['F_id_funcionario'] ?></td>
                <td><?= htmlspecialchars($func['F_nome']) ?></td>
                <td><?= htmlspecialchars($func['F_email']) ?></td>
                <td><?= htmlspecialchars($func['F_cargo']) ?></td>
                <td><?= htmlspecialchars($func['F_telefone']) ?></td>
                <td><?= date('d/m/Y', strtotime($func['F_data_contratacao'])) ?></td>
                <td>
                    <a href="editar.php?id=<?= $func['F_id_funcionario'] ?>" class="action-btn edit-btn" title="Editar">
                        <i class="fas fa-edit"></i>
                    </a>
                    <?php if ($func['F_id_funcionario'] != $_SESSION['admin_id']): ?>
                    <a href="excluir.php?id=<?= $func['F_id_funcionario'] ?>" class="action-btn delete-btn" title="Excluir" onclick="return confirm('Tem certeza que deseja excluir este funcionário?')">
                        <i class="fas fa-trash"></i>
                    </a>
                    <?php endif; ?>
                </td>
            </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</div>

<?php include '../includes/footer.php'; ?>