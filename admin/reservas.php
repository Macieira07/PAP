<?php
require '../conexao.php';
session_start();
// Filtros
$where = "r.R_estado IN ('pendente', 'confirmada')";

if (isset($_GET['status']) && $_GET['status'] !== '') {
    $status = $conexao->real_escape_string($_GET['status']);
    $where .= " AND r.R_estado = '$status'";
}

if (isset($_GET['origem']) && $_GET['origem'] !== '') {
    $origem = $conexao->real_escape_string($_GET['origem']);
    $where .= " AND r.R_origem = '$origem'";
}

if (isset($_GET['busca']) && $_GET['busca'] !== '') {
    $busca = $conexao->real_escape_string($_GET['busca']);
    $where .= " AND (h.H_nome LIKE '%$busca%' OR c.C_nome LIKE '%$busca%')";
}

// Mensagem flash
if (isset($_SESSION['flash'])) {
    echo "<div class='flash-message {$_SESSION['flash']['type']}'>{$_SESSION['flash']['msg']}</div>";
    unset($_SESSION['flash']);
}

// Paginação
$por_pagina = 10;
$pagina_atual = isset($_GET['pagina']) ? (int)$_GET['pagina'] : 1;
$inicio = ($pagina_atual - 1) * $por_pagina;

// Consulta das reservas
$resultado = $conexao->query("
    SELECT r.*, h.H_nome, c.C_nome
    FROM reservas r
    JOIN hospedes h ON r.R_id_hospede = h.H_id_hospede
    JOIN casas c ON r.R_id_casa = c.C_id_casa
    WHERE $where
    ORDER BY r.R_data_checkin DESC
    LIMIT $inicio, $por_pagina
");

// Total para paginação
$total_reservas = $conexao->query("SELECT COUNT(*) FROM reservas r WHERE $where")->fetch_row()[0];
$paginas = ceil($total_reservas / $por_pagina);
?>
<!DOCTYPE html>
<html lang="pt">
<head>
    <link rel="icon" type="image/png" sizes="32x32" href="../assets/logos/favicon-32x32.png">
    <link rel="icon" type="image/png" sizes="16x16" href="../assets/logos/favicon-16x16.png">
    <meta charset="UTF-8">
    <link rel="stylesheet" href="global.css">
    <title>Gestão de Reservas</title>
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
<?php include __DIR__ . '/saldo_widget.php'; ?>
    <div style="display: flex; align-items: center; gap: 10px;">
        <img src="https://img.icons8.com/?size=100&id=MCnPOwFJpCvG&format=png&color=000000" alt="Ícone Hóspedes" style="height: 50px;">
        <h1>Todos as Reservas</h1>
    </div>
    <a href="admin.php">← Voltar</a>
    
    <div class="filter-form">
        <form method="get">
            <label for="status">Status:</label>
            <select name="status" id="status">
                <option value="">Todos</option>
                <option value="pendente" <?= (isset($_GET['status']) && $_GET['status'] == 'pendente') ? 'selected' : '' ?>>Pendente</option>
                <option value="confirmada" <?= (isset($_GET['status']) && $_GET['status'] == 'confirmada') ? 'selected' : '' ?>>Confirmada</option>
                <option value="cancelada" <?= (isset($_GET['status']) && $_GET['status'] == 'cancelada') ? 'selected' : '' ?>>Cancelada</option>
                <option value="concluída" <?= (isset($_GET['status']) && $_GET['status'] == 'concluída') ? 'selected' : '' ?>>Concluída</option>
            </select>

            <label for="origem">Origem:</label>
            <select name="origem" id="origem">
                <option value="">Todas</option>
                <option value="admin" <?= (isset($_GET['origem']) && $_GET['origem'] == 'admin') ? 'selected' : '' ?>>Admin</option>
                <option value="online" <?= (isset($_GET['origem']) && $_GET['origem'] == 'online') ? 'selected' : '' ?>>Online (Cliente)</option>
            </select>

            <label for="busca">Busca:</label>
            <input type="text" name="busca" id="busca" value="<?= isset($_GET['busca']) ? htmlspecialchars($_GET['busca']) : '' ?>">

            <button type="submit" class="button-small">Filtrar</button>
            <a href="reservas.php" class="button-small">Limpar</a>
        </form>
    </div>

    <a href="adicionar_reserva.php" class="button">+ Nova Reserva</a>

    <table>
        <thead>
            <tr>
                <th>ID</th>
                <th>Hóspede</th>
                <th>Casa</th>
                <th>Check-in</th>
                <th>Check-out</th>
                <th>Noites</th>
                <th>Total</th>
                <th>Status</th>
                <th>Origem</th>
                <th>Ações</th>
            </tr>
        </thead>
        <tbody>
            <?php while ($r = $resultado->fetch_assoc()):
                $noites = (new DateTime($r['R_data_checkout']))->diff(new DateTime($r['R_data_checkin']))->days;
            ?>
                <tr>
                    <td><?= $r['R_id_reserva'] ?></td>
                    <td><?= htmlspecialchars($r['H_nome']) ?></td>
                    <td><?= htmlspecialchars($r['C_nome']) ?></td>
                    <td><?= $r['R_data_checkin'] ?></td>
                    <td><?= $r['R_data_checkout'] ?></td>
                    <td><?= $noites ?></td>
                    <td><?= number_format($r['R_preco_total'], 2) ?>€</td>
                    <td><?= ucfirst($r['R_estado']) ?></td>
                    <td><?= ucfirst($r['R_origem']) ?></td>
                    <td>
                        <a href="editar_reserva.php?id=<?= $r['R_id_reserva'] ?>" class="button-small">Editar</a>
                        <a href="gerar_pdf_reserva.php?id=<?= $r['R_id_reserva'] ?>" class="button-small">PDF</a>
                    </td>
                </tr>
            <?php endwhile; ?>
        </tbody>
    </table>

    <div class="pagination">
        <?php for ($i = 1; $i <= $paginas; $i++): ?>
            <a href="?pagina=<?= $i ?>&status=<?= $_GET['status'] ?? '' ?>&origem=<?= $_GET['origem'] ?? '' ?>&busca=<?= $_GET['busca'] ?? '' ?>"
               class="<?= $i == $pagina_atual ? 'active' : '' ?>">
                <?= $i ?>
            </a>
        <?php endfor; ?>
    </div>
</body>
<a href="admin.php">← Voltar</a>
</html>
            