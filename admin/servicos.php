<?php
require '../conexao.php';
session_start();

// Mensagem flash
if (isset($_SESSION['flash'])) {
    echo "<div class='flash-message {$_SESSION['flash']['type']}'>{$_SESSION['flash']['msg']}</div>";
    unset($_SESSION['flash']);
}

// Buscar serviços com nome da categoria para exibir
$sql = "SELECT s.*, c.nome as categoria_nome 
        FROM servicos s 
        LEFT JOIN categorias_servico c ON s.S_categoria_id = c.id
        ORDER BY s.S_id_servico ASC";
$resultado = $conexao->query($sql);
?>
<!DOCTYPE html>
<html lang="pt">
<head>
    <meta charset="UTF-8">
    <title>Serviços</title>
    <link rel="stylesheet" href="admin.css">
    <link rel="icon" type="image/png" sizes="32x32" href="../assets/logos/favicon-32x32.png">
    <link rel="icon" type="image/png" sizes="16x16" href="../assets/logos/favicon-16x16.png">
</head>
<body>
    <div style="display: flex; align-items: center; gap: 10px;">
        <img src="https://img.icons8.com/?size=100&id=GtKvA4suLFWD&format=png&color=000000" alt="Ícone Serviços" style="height: 50px;">
        <h1>Todos os Serviços</h1>
    </div>
    <a href="adicionar_servico.php">+ Novo Serviço</a> | <a href="admin.php">← Voltar ao Painel</a>
    <table border="1" cellpadding="5" cellspacing="0">
        <thead>
            <tr>
                <th>ID</th>
                <th>Nome</th>
                <th>Descrição</th>
                <th>Preço (€)</th>
                <th>Categoria</th>
                <th>Ações</th>
            </tr>
        </thead>
        <tbody>
            <?php while($servico = $resultado->fetch_assoc()): ?>
                <tr>
                    <td><?= $servico['S_id_servico'] ?></td>
                    <td><?= htmlspecialchars($servico['S_nome']) ?></td>
                    <td><?= htmlspecialchars($servico['S_descricao']) ?></td>
                    <td><?= number_format($servico['S_preco'], 2) ?></td>
                    <td><?= htmlspecialchars($servico['categoria_nome']) ?></td>
                    <td>
                        <a href="editar_servico.php?id=<?= $servico['S_id_servico'] ?>">Editar</a> | 
                        <a href="eliminar_servico.php?id=<?= $servico['S_id_servico'] ?>" onclick="return confirm('Tem a certeza que deseja eliminar este serviço?')">Eliminar</a>
                    </td>
                </tr>
            <?php endwhile; ?>
            <?php if ($resultado->num_rows == 0): ?>
                <tr><td colspan="6">Não existem serviços registados.</td></tr>
            <?php endif; ?>
        </tbody>
    </table>
</body>
</html>
