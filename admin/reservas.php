<?php
require '../conexao.php';

// Filtros
$where = "1=1";  // Condição inicial

if (isset($_GET['status']) && $_GET['status'] !== '') {
    $status = $conexao->real_escape_string($_GET['status']);
    $where .= " AND r.R_estado = '$status'";
}

if (isset($_GET['checkin']) && $_GET['checkin'] !== '') {
    $checkin = $conexao->real_escape_string($_GET['checkin']);
    $where .= " AND r.R_data_checkin >= '$checkin'";
}

if (isset($_GET['checkout']) && $_GET['checkout'] !== '') {
    $checkout = $conexao->real_escape_string($_GET['checkout']);
    $where .= " AND r.R_data_checkout <= '$checkout'";
}

$busca = isset($_GET['busca']) ? $conexao->real_escape_string($_GET['busca']) : '';
if ($busca) {
    $where .= " AND (h.H_nome LIKE '%$busca%' OR c.C_nome LIKE '%$busca%')";
}

$order = 'r.R_data_checkin DESC'; // Padrão: ordenar por data de check-in
if (isset($_GET['order_by'])) {
    $order = $conexao->real_escape_string($_GET['order_by']);
}

$por_pagina = 10;
$pagina_atual = isset($_GET['pagina']) ? (int)$_GET['pagina'] : 1;
$inicio = ($pagina_atual - 1) * $por_pagina;

// Consulta principal
$resultado = $conexao->query("
    SELECT r.*, h.H_nome, c.C_nome
    FROM reservas r
    JOIN hospedes h ON r.R_id_hospede = h.H_id_hospede
    JOIN casas c ON r.R_id_casa = c.C_id_casa
    WHERE $where
    ORDER BY $order
    LIMIT $inicio, $por_pagina
");

// Total de reservas para paginação
$total_reservas_resultado = $conexao->query("SELECT COUNT(*) AS total FROM reservas r WHERE $where");
$total_reservas = $total_reservas_resultado->fetch_assoc()['total'];
$paginas = ceil($total_reservas / $por_pagina);

// Total ganho
$resultado_total = $conexao->query("SELECT SUM(r.R_preco_total) AS total_ganho FROM reservas r WHERE $where");
$total_ganho = $resultado_total->fetch_assoc()['total_ganho'] ?? 0;
?>

<link rel="stylesheet" href="admin.css">
<div style="display: flex; align-items: center; gap: 10px;">
    <img src="https://img.icons8.com/?size=100&id=vTZ34gSDdvwJ&format=png&color=000000" alt="Ícone Reservas" style="height: 50px;">
    <h1>Todas as Reservas</h1>
</div>
<a href="admin.php">← Voltar</a> |
<a href="adicionar_reserva.php">+ Nova Reserva</a> |
<a href="?exportar=1">Exportar para CSV</a>

<form method="get">
    Status: 
    <select name="status">
        <option value="">Selecione</option>
        <option value="pendente" <?= isset($_GET['status']) && $_GET['status'] == 'pendente' ? 'selected' : '' ?>>Pendente</option>
        <option value="confirmada" <?= isset($_GET['status']) && $_GET['status'] == 'confirmada' ? 'selected' : '' ?>>Confirmada</option>
        <option value="cancelada" <?= isset($_GET['status']) && $_GET['status'] == 'cancelada' ? 'selected' : '' ?>>Cancelada</option>
        <option value="concluída" <?= isset($_GET['status']) && $_GET['status'] == 'concluída' ? 'selected' : '' ?>>Concluída</option>
    </select>
    Check-in: <input type="date" name="checkin" value="<?= isset($_GET['checkin']) ? $_GET['checkin'] : '' ?>">
    Check-out: <input type="date" name="checkout" value="<?= isset($_GET['checkout']) ? $_GET['checkout'] : '' ?>">
    Busca: <input type="text" name="busca" value="<?= htmlspecialchars($busca) ?>">
    Ordenar por:
    <select name="order_by">
        <option value="r.R_data_checkin DESC" <?= $order == 'r.R_data_checkin DESC' ? 'selected' : '' ?>>Data de Check-in</option>
        <option value="r.R_preco_total DESC" <?= $order == 'r.R_preco_total DESC' ? 'selected' : '' ?>>Preço Total</option>
    </select>
    <button type="submit">Filtrar</button>
</form>

<table border="1" cellpadding="10">
    <tr>
        <th>ID</th>
        <th>Hóspede</th>
        <th>Casa</th>
        <th>Check-in</th>
        <th>Check-out</th>
        <th>Nº Hóspedes</th>
        <th>Preço Total</th>
        <th>Estado</th>
        <th>Pagamento</th>
        <th>Ações</th>
    </tr>
    <?php while ($r = $resultado->fetch_assoc()): ?>
        <tr>
            <td><?= $r['R_id_reserva'] ?></td>
            <td><?= $r['H_nome'] ?></td>
            <td><?= $r['C_nome'] ?></td>
            <td><?= $r['R_data_checkin'] ?></td>
            <td><?= $r['R_data_checkout'] ?></td>
            <td><?= $r['R_num_hospedes'] ?></td>
            <td><?= $r['R_preco_total'] ?>€</td>
            <td>
                <form method="post" action="alterar_estado.php">
                    <input type="hidden" name="id" value="<?= $r['R_id_reserva'] ?>">
                    <select name="estado">
                        <option value="pendente" <?= $r['R_estado'] == 'pendente' ? 'selected' : '' ?>>Pendente</option>
                        <option value="confirmada" <?= $r['R_estado'] == 'confirmada' ? 'selected' : '' ?>>Confirmada</option>
                        <option value="cancelada" <?= $r['R_estado'] == 'cancelada' ? 'selected' : '' ?>>Cancelada</option>
                        <option value="concluída" <?= $r['R_estado'] == 'concluída' ? 'selected' : '' ?>>Concluída</option>
                    </select>
                    <button type="submit">Alterar</button>
                </form>
            </td>
            <td>
                <a href="editar_reserva.php?id=<?= $r['R_id_reserva'] ?>">Editar</a> |
                <a href="eliminar_reserva.php?id=<?= $r['R_id_reserva'] ?>" onclick="return confirm('Tem certeza?')">Eliminar</a>
            </td>
        </tr>
    <?php endwhile; ?>
</table>

<!-- Exibe o total ganho -->
<div style="margin-top: 20px;">
    <strong>Total Ganho nas Reservas: <?= number_format($total_ganho, 2, ',', '.') ?>€</strong>
</div>

<div>
    <?php for ($i = 1; $i <= $paginas; $i++): ?>
        <a href="?pagina=<?= $i ?>"><?= $i ?></a>
    <?php endfor; ?>
</div>
