<?php
include '../conexao.php';

$mensagem = '';

// Apagar modelo
if (isset($_GET['apagar'])) {
    $id = intval($_GET['apagar']);
    $stmt = $conexao->prepare("DELETE FROM modelos_newsletter WHERE MN_id = ?");
    $stmt->bind_param("i", $id);
    if ($stmt->execute()) {
        $mensagem = "<p style='color:green;'>Modelo apagado com sucesso.</p>";
    } else {
        $mensagem = "<p style='color:red;'>Erro ao apagar modelo.</p>";
    }
    $stmt->close();
}

// Buscar modelos
$result = $conexao->query("SELECT * FROM modelos_newsletter ORDER BY MN_data_criacao DESC");
?>

<!DOCTYPE html>
<html lang="pt">
<head>
    <meta charset="UTF-8" />
    <title>Modelos Newsletter - Listar</title>
    <link rel="stylesheet" href="admin.css" />
</head>
<body>
    <h1>Modelos Newsletter</h1>

    <?= $mensagem ?>

    <a href="adicionar_modelo.php">+ Adicionar Modelo</a>
    <a href="admin.php">← Voltar</a>

    <table border="1" cellpadding="8" cellspacing="0" style="width:100%; max-width:900px; margin-top: 10px;">
        <thead>
            <tr>
                <th>ID</th>
                <th>Título</th>
                <th>Descrição</th>
                <th>Data Criação</th>
                <th>Ações</th>
            </tr>
        </thead>
        <tbody>
            <?php while ($row = $result->fetch_assoc()): ?>
            <tr>
                <td><?= htmlspecialchars($row['MN_id']) ?></td>
                <td><?= htmlspecialchars($row['MN_titulo']) ?></td>
                <td><?= nl2br(htmlspecialchars($row['MN_descricao'])) ?></td>
                <td><?= htmlspecialchars($row['MN_data_criacao']) ?></td>
                <td>
                    <a href="editar_modelo.php?id=<?= $row['MN_id'] ?>">Editar</a> |
                    <a href="modelos_newsletter.php?apagar=<?= $row['MN_id'] ?>" onclick="return confirm('Tem a certeza que quer apagar este modelo?');">Apagar</a>
                </td>
            </tr>
            <?php endwhile; ?>
        </tbody>
    </table>
</body>
</html>
