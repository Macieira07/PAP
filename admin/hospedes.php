<?php
require '../conexao.php';

// Obter parâmetros de pesquisa/filtro
$pesquisa = $_GET['pesquisa'] ?? '';
$filtro_verificado = $_GET['verificado'] ?? '';

// Montar SQL dinamicamente com filtros
$sql = "SELECT * FROM hospedes WHERE 1=1";

if (!empty($pesquisa)) {
    $pesq = $conexao->real_escape_string($pesquisa);
    $sql .= " AND (H_nome LIKE '%$pesq%' OR H_email LIKE '%$pesq%' OR H_documento_ident LIKE '%$pesq%')";
}

if ($filtro_verificado === 'Sim' || $filtro_verificado === 'Não') {
    $sql .= " AND H_verificado_email = '$filtro_verificado'";
}

$resultado = $conexao->query($sql);

?>
<!DOCTYPE html>
<html lang="pt">
<head>
        <link rel="icon" type="image/png" sizes="32x32" href="../assets/logos/favicon-32x32.png">
<link rel="icon" type="image/png" sizes="16x16" href="../assets/logos/favicon-16x16.png">
    <link rel="stylesheet" href="admin.css">
    <link rel="stylesheet" href="hospedes.css">
    <meta charset="UTF-8">
    <title>Hóspedes</title>
</head>
<body>
    <div style="display: flex; align-items: center; gap: 10px;">
        <img src="https://img.icons8.com/?size=100&id=60018&format=png&color=000000" alt="Ícone Hóspedes" style="height: 50px;">
        <h1>Todos os Hóspedes</h1>
    </div>
    <a href="admin.php">← Voltar</a> | 
    <a href="adicionar_hospede.php">+ Adicionar Hóspede</a>

    <!-- Formulário de pesquisa e filtro -->
    <form method="get" style="margin-top: 20px; margin-bottom: 20px;">
        <input type="text" name="pesquisa" placeholder="Pesquisar por nome, email ou documento" value="<?= htmlspecialchars($pesquisa) ?>">
        <select name="verificado">
            <option value="">Todos</option>
            <option value="Sim" <?= $filtro_verificado === 'Sim' ? 'selected' : '' ?>>Verificados</option>
            <option value="Não" <?= $filtro_verificado === 'Não' ? 'selected' : '' ?>>Não Verificados</option>
        </select>
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
            <!-- Detalhes ocultos que aparecem ao passar o mouse -->
            <td class="hospede-details">
                <div class="details-content">
                    <p><strong>Nome:</strong> <?= $h['H_nome'] ?></p>
                    <p><strong>Email:</strong> <?= $h['H_email'] ?></p>
                    <p><strong>Telefone:</strong> <?= $h['H_telefone'] ?></p>
                    <p><strong>Documento:</strong> <?= $h['H_documento_ident'] ?></p>
                    <p><strong>Verificado:</strong> <?= $h['H_verificado_email'] ?></p>
                </div>
            </td>
        </tr>
        <?php endwhile; ?>
    </table>
    <?php
if (isset($_GET['exportar'])) {
    header('Content-Type: text/csv');
    header('Content-Disposition: attachment; filename="hospedes.csv"');
    
    $output = fopen('php://output', 'w');
    fputcsv($output, ['ID', 'Nome', 'Email', 'Telefone', 'Documento', 'Verificado']);
    
    $result = $conexao->query("SELECT * FROM hospedes");
    while ($row = $result->fetch_assoc()) {
        fputcsv($output, [
            $row['H_id_hospede'],
            $row['H_nome'],
            $row['H_email'],
            $row['H_telefone'],
            $row['H_documento_ident'],
            $row['H_verificado_email']
        ]);
    }
    fclose($output);
    exit;

    
}
?>
<!-- Botão de exportação -->
<a href="hospedes.php?exportar=1" class="botao-exportar">Exportar CSV</a>
    
    <a href="admin.php">← Voltar</a>
</body>
</html>
