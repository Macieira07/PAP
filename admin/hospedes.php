<?php
require '../conexao.php';
session_start();

// Obter parâmetro de pesquisa (sem filtro verificado)
$pesquisa = $_GET['pesquisa'] ?? '';

// Exibir flash message se existir
if (isset($_SESSION['flash'])) {
    echo "<div class='flash-message {$_SESSION['flash']['type']}'>{$_SESSION['flash']['msg']}</div>";
    unset($_SESSION['flash']);
}

// Montar SQL com filtro só para pesquisa
$sql = "SELECT * FROM hospedes WHERE 1=1";

if (!empty($pesquisa)) {
    $pesq = $conexao->real_escape_string($pesquisa);
    $sql .= " AND (H_nome LIKE '%$pesq%' OR H_email LIKE '%$pesq%' OR H_documento_ident LIKE '%$pesq%')";
}

$resultado = $conexao->query($sql);
?>
<!DOCTYPE html>
<html lang="pt">
<head>
    <link rel="icon" type="image/png" sizes="32x32" href="../assets/logos/favicon-32x32.png">
    <link rel="icon" type="image/png" sizes="16x16" href="../assets/logos/favicon-16x16.png">
    <link rel="stylesheet" href="../public/css/admin.css">
    <link rel="stylesheet" href="hospedes.css">
    <meta charset="UTF-8">
    <title>Hóspedes</title>
    <style>
        .flash-message {
            position: fixed;
            top: 20px;
            right: 20px;
            padding: 15px;
            border-radius: 5px;
            color: white;
            z-index: 1000;
            animation: fadeIn 0.5s, fadeOut 0.5s 2.5s;
        }
        .flash-message.success { background-color: #4CAF50; }
        .flash-message.error { background-color: #f44336; }
        @keyframes fadeIn { from { opacity: 0; } to { opacity: 1; } }
        @keyframes fadeOut { from { opacity: 1; } to { opacity: 0; } }
    </style>
</head>
<body>
    <div style="display: flex; align-items: center; gap: 10px;">
        <img src="https://img.icons8.com/?size=100&id=3Lghg94mD5Gd&format=png&color=000000" alt="Ícone Hóspedes" style="height: 50px;">
        <h1>Todos os Hóspedes</h1>
    </div>
    <a href="admin.php">← Voltar</a> | 
    <a href="adicionar_hospede.php">+ Adicionar Hóspede</a>

    <!-- Formulário de pesquisa -->
    <form method="get" style="margin-top: 20px; margin-bottom: 20px;">
        <input type="text" name="pesquisa" placeholder="Pesquisar por nome, email ou documento" value="<?= htmlspecialchars($pesquisa) ?>">
        <button type="submit">Filtrar</button>
        <a href="hospedes.php" style="margin-left: 10px;">Limpar Filtros</a>
    </form>

    <table border="1" cellpadding="10">
        <tr>
            <th>ID</th>
            <th>Nome Completo</th>
            <th>Email</th>
            <th>Telefone</th>
            <th>Documento</th>
            <th>Verificado</th>
            <th>Ações</th>
        </tr>
        <?php while ($h = $resultado->fetch_assoc()): ?>
        <tr class="hospede-row">
            <td><?= $h['H_id_hospede'] ?></td>
            <td><?= $h['H_nome'] ?></td>
            <td><?= $h['H_email'] ?></td>
            <td><?= $h['H_telefone'] ?></td>
            <td><?= $h['H_documento_ident'] ?></td>
            <td><?= $h['H_verificado_email'] ?></td>
            <td>
                <a href="editar_hospede.php?id=<?= $h['H_id_hospede'] ?>">Editar</a> |
                <a href="eliminar_hospede.php?id=<?= $h['H_id_hospede'] ?>" onclick="return confirm('Tem certeza?')">Eliminar</a>
            </td>
        </tr>
        <?php endwhile; ?>
    </table> 
    <a href="admin.php">← Voltar</a>
</body>
</html>
