<?php
require '../conexao.php';

// Pegando os parâmetros de filtro
$nomeFiltro = isset($_GET['nome']) ? $_GET['nome'] : '';
$cargoFiltro = isset($_GET['cargo']) ? $_GET['cargo'] : '';

// Configurar o número de registros por página
$porPagina = 10;
$paginaAtual = isset($_GET['pagina']) ? (int)$_GET['pagina'] : 1;
$offset = ($paginaAtual - 1) * $porPagina;

// Montando a query com base nos filtros
$sql = "SELECT * FROM funcionarios WHERE 1=1";
if ($nomeFiltro) {
    $sql .= " AND F_nome LIKE '%$nomeFiltro%'";
}
if ($cargoFiltro) {
    $sql .= " AND F_cargo LIKE '%$cargoFiltro%'";
}
$sql .= " LIMIT $offset, $porPagina"; // Adicionando limite para paginação

// Executar a consulta
$resultado = $conexao->query($sql);

// Contar o número total de registros para paginação
$sqlTotal = "SELECT COUNT(*) FROM funcionarios WHERE 1=1";
if ($nomeFiltro) {
    $sqlTotal .= " AND F_nome LIKE '%$nomeFiltro%'";
}
if ($cargoFiltro) {
    $sqlTotal .= " AND F_cargo LIKE '%$cargoFiltro%'";
}
$resultadoTotal = $conexao->query($sqlTotal);
$totalRegistros = $resultadoTotal->fetch_row()[0];
$totalPaginas = ceil($totalRegistros / $porPagina);
?>

<!DOCTYPE html>
<html lang="pt">
<head>
<link rel="stylesheet" href="admin.css">
    <meta charset="UTF-8">
    <title>Funcionários</title>
</head>
<body>
    <h1>Lista de Funcionários</h1>
    <a href="admin.php">← Voltar</a> | 
    <a href="adicionar_funcionario.php">+ Adicionar Funcionário</a>

    <!-- Formulário de Filtro -->
    <form method="get" action="funcionarios.php">
        <input type="text" name="nome" placeholder="Filtrar por nome" value="<?= $nomeFiltro ?>">
        <input type="text" name="cargo" placeholder="Filtrar por cargo" value="<?= $cargoFiltro ?>">
        <button type="submit">Filtrar</button>
    </form>

    <table border="1" cellpadding="10" style="margin-top: 20px;">
        <tr>
            <th>ID</th>
            <th>Nome</th>
            <th>Email</th>
            <th>Cargo</th>
            <th>Telefone</th>
            <th>Data de Contratação</th>
            <th>Ações</th>
        </tr>
        <?php while ($f = $resultado->fetch_assoc()): ?>
            <tr>
                <td><?= $f['F_id_funcionario'] ?></td>
                <td><?= $f['F_nome'] ?></td>
                <td><?= $f['F_email'] ?></td>
                <td><?= $f['F_cargo'] ?></td>
                <td><?= $f['F_telefone'] ?></td>
                <td><?= $f['F_data_contratacao'] ?></td>
                <td>
                    <a href="editar_funcionario.php?id=<?= $f['F_id_funcionario'] ?>">Editar</a> |
                    <a href="eliminar_funcionario.php?id=<?= $f['F_id_funcionario'] ?>" onclick="return confirm('Tem certeza?')">Eliminar</a>
                </td>
            </tr>
        <?php endwhile; ?>
    </table>

    <!-- Paginação -->
    <div>
        <?php if ($paginaAtual > 1): ?>
            <a href="?pagina=<?= $paginaAtual - 1 ?>&nome=<?= $nomeFiltro ?>&cargo=<?= $cargoFiltro ?>">Anterior</a>
        <?php endif; ?>
        <?php if ($paginaAtual < $totalPaginas): ?>
            <a href="?pagina=<?= $paginaAtual + 1 ?>&nome=<?= $nomeFiltro ?>&cargo=<?= $cargoFiltro ?>">Próximo</a>
        <?php endif; ?>
    </div>
</body>
</html>
