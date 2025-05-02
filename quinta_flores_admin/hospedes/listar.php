<?php
require_once '../includes/auth.php';
require_once '../conexao.php';
verificarLogin();

try {
    $pdo = conexao();
    $hospedes = $pdo->query("SELECT * FROM hospedes ORDER BY H_nome")->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    die("Erro ao carregar hóspedes: " . $e->getMessage());
}

$titulo = "Hóspedes - Quinta Flores";
?>
<?php include 'includes/header.php'; ?>

<div class="welcome-card">
    <h1>Gestão de Hóspedes</h1>
    <p class="welcome-message">Gere todos os hóspedes da Quinta Flores.</p>
</div>

<div class="table-container">
    <table>
        <thead>
            <tr>
                <th>ID</th>
                <th>Nome</th>
                <th>E-mail</th>
                <th>Telefone</th>
                <th>Verificado</th>
                <th>Cadastrado em</th>
                <th>Ações</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($hospedes as $hospede): ?>
            <tr>
                <td><?= $hospede['H_id_hospede'] ?></td>
                <td><?= htmlspecialchars($hospede['H_nome']) ?></td>
                <td><?= htmlspecialchars($hospede['H_email']) ?></td>
                <td><?= htmlspecialchars($hospede['H_telefone']) ?></td>
                <td><?= $hospede['H_verificado_email'] == 'Sim' ? 'Sim' : 'Não' ?></td>
                <td><?= date('d/m/Y', strtotime($hospede['H_data_criacao'])) ?></td> 
                    <td> <a href="detalhes.php?id=<?= $hospede['H_id_hospede'] ?>" class="action-btn view-btn" title="Ver detalhes"> <i class="fas fa-eye"></i> </a> <a href="editar.php?id=<?= $hospede['H_id_hospede'] ?>" class="action-btn edit-btn" title="Editar"> <i class="fas fa-edit"></i> </a> </td> </tr> <?php endforeach; ?> </tbody> </table> </div><?php include '../includes/footer.php'; ?>