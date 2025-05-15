<?php
require '../conexao.php';

// Verifica se existe uma mensagem na URL (passada pelo redirecionamento)
if (isset($_GET['mensagem'])) {
    $mensagem = $_GET['mensagem'];
    $tipo = $_GET['tipo'];

    echo "
    <script>
        window.onload = function() {
            let tipo = '$tipo';
            let mensagem = '$mensagem';

            // Criando o elemento de notificação
            let notification = document.createElement('div');
            notification.classList.add('notification');
            notification.classList.add(tipo);
            notification.innerHTML = mensagem;

            // Adicionando a notificação ao corpo da página
            document.body.appendChild(notification);

            // Remover a notificação após 5 segundos
            setTimeout(function() {
                notification.remove();
            }, 5000);
        };
    </script>
    ";

    // Você pode incluir o estilo direto no HTML ou em um arquivo CSS separado
    echo "
    <style>
        .notification {
            position: fixed;
            top: 10px;
            right: 10px;
            padding: 15px;
            background-color: #4CAF50;
            color: white;
            border-radius: 5px;
            font-weight: bold;
            z-index: 9999;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
            transition: all 0.5s ease;
        }

        .notification.erro {
            background-color: #f44336; /* Cor para erro */
        }

        .notification.sucesso {
            background-color: #4CAF50; /* Cor para sucesso */
        }
    </style>
    ";
}


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
        <link rel="icon" type="image/png" sizes="32x32" href="../assets/logos/favicon-32x32.png">
<link rel="icon" type="image/png" sizes="16x16" href="../assets/logos/favicon-16x16.png">
    <link rel="stylesheet" href="admin.css">
    <meta charset="UTF-8">
    <title>Funcionários</title>
</head>
<body>
<div style="display: flex; align-items: center; gap: 10px;">
        <img src="https://img.icons8.com/?size=100&id=37174&format=png&color=000000" alt="Ícone Funcionários" style="height: 50px;">
        <h1>Todos os Funcionários</h1>
    </div>
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
            <th>Turno</th>
            <th>Férias/Ausência</th>
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
                
                <?php
                // Buscar turnos
                $stmt_turno = $conexao->prepare("SELECT * FROM turnos WHERE F_id_funcionario=?");
                $stmt_turno->bind_param("i", $f['F_id_funcionario']);
                $stmt_turno->execute();
                $turnos_result = $stmt_turno->get_result();
                $turnos = $turnos_result->fetch_assoc();

                // Buscar férias
                $stmt_ferias = $conexao->prepare("SELECT * FROM ferias_ausencias WHERE F_id_funcionario=?");
                $stmt_ferias->bind_param("i", $f['F_id_funcionario']);
                $stmt_ferias->execute();
                $ferias_result = $stmt_ferias->get_result();
                $ferias = $ferias_result->fetch_assoc();
                ?>

                <td>
                    <?php if ($turnos): ?>
                        <a href="editar_turno.php?id=<?= $turnos['T_id_turno'] ?>">Editar Turno</a><br>
                        <?= $turnos['T_inicio'] ?> - <?= $turnos['T_fim'] ?>
                    <?php else: ?>
                        Nenhum turno registrado
                    <?php endif; ?>
                </td>
                <td>
                    <?php if ($ferias && isset($ferias['FA_inicio']) && isset($ferias['FA_fim'])): ?>
                        <a href="editar_ferias.php?id=<?= $ferias['FA_id_ferias'] ?>">Editar Férias</a><br>
                        <?= $ferias['FA_inicio'] ?> a <?= $ferias['FA_fim'] ?>
                    <?php else: ?>
                        Nenhuma férias registrada
                    <?php endif; ?>
                </td>
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
    <a href="admin.php">← Voltar</a>
</body>
</html>
            