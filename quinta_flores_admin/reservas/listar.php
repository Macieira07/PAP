<?php
require_once '../includes/auth.php';
require_once '../includes/conexao.php';
verificarLogin();

$filtro_estado = $_GET['estado'] ?? 'todos';
$filtro_data = $_GET['data'] ?? '';

try {
    $pdo = conexao();
    
    // Construir query com filtros
    $query = "SELECT r.*, h.H_nome, c.C_nome 
              FROM reservas r 
              JOIN hospedes h ON r.R_id_hospede = h.H_id_hospede
              JOIN casas c ON r.R_id_casa = c.C_id_casa";
    
    $where = [];
    $params = [];
    
    if ($filtro_estado != 'todos') {
        $where[] = "r.R_estado = ?";
        $params[] = $filtro_estado;
    }
    
    if (!empty($filtro_data)) {
        $where[] = "r.R_data_checkin <= ? AND r.R_data_checkout >= ?";
        $params[] = $filtro_data;
        $params[] = $filtro_data;
    }
    
    if (!empty($where)) {
        $query .= " WHERE " . implode(" AND ", $where);
    }
    
    $query .= " ORDER BY r.R_data_checkin DESC";
    
    $stmt = $pdo->prepare($query);
    $stmt->execute($params);
    $reservas = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
} catch (PDOException $e) {
    die("Erro ao carregar reservas: " . $e->getMessage());
}

$titulo = "Reservas - Quinta Flores";
?>
<?php include '../includes/header.php'; ?>

<div class="welcome-card">
    <h1>Gestão de Reservas</h1>
    <p class="welcome-message">Gerencie todas as reservas da Quinta Flores.</p>
</div>

<div class="filtros-container">
    <form method="GET" class="filtros-form">
        <div class="form-group">
            <label for="estado">Estado:</label>
            <select id="estado" name="estado">
                <option value="todos" <?= $filtro_estado == 'todos' ? 'selected' : '' ?>>Todos</option>
                <option value="pendente" <?= $filtro_estado == 'pendente' ? 'selected' : '' ?>>Pendentes</option>
                <option value="confirmada" <?= $filtro_estado == 'confirmada' ? 'selected' : '' ?>>Confirmadas</option>
                <option value="cancelada" <?= $filtro_estado == 'cancelada' ? 'selected' : '' ?>>Canceladas</option>
                <option value="concluída" <?= $filtro_estado == 'concluída' ? 'selected' : '' ?>>Concluídas</option>
            </select>
        </div>
        
        <div class="form-group">
            <label for="data">Data específica:</label>
            <input type="date" id="data" name="data" value="<?= $filtro_data ?>">
        </div>
        
        <button type="submit" class="btn-filtrar">
            <i class="fas fa-filter"></i> Filtrar
        </button>
        <a href="listar.php" class="btn-limpar">
            <i class="fas fa-times"></i> Limpar
        </a>
    </form>
</div>

<div class="table-container">
    <table>
        <thead>
            <tr>
                <th>ID</th>
                <th>Hóspede</th>
                <th>Casa</th>
                <th>Check-in</th>
                <th>Check-out</th>
                <th>Estado</th>
                <th>Valor</th>
                <th>Ações</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($reservas as $reserva): ?>
            <tr>
                <td><?= $reserva['R_id_reserva'] ?></td>
                <td><?= htmlspecialchars($reserva['H_nome']) ?></td>
                <td><?= htmlspecialchars($reserva['C_nome']) ?></td>
                <td><?= date('d/m/Y', strtotime($reserva['R_data_checkin'])) ?></td>
                <td><?= date('d/m/Y', strtotime($reserva['R_data_checkout'])) ?></td>
                <td><span class="status-badge <?= $reserva['R_estado'] ?>"><?= ucfirst($reserva['R_estado']) ?></span></td>
                <td><?= number_format($reserva['R_preco_total'], 2, ',', '.') ?> €</td>
                <td>
                    <a href="detalhes.php?id=<?= $reserva['R_id_reserva'] ?>" class="action-btn view-btn" title="Ver detalhes">
                        <i class="fas fa-eye"></i>
                    </a>
                    <a href="editar.php?id=<?= $reserva['R_id_reserva'] ?>" class="action-btn edit-btn" title="Editar">
                        <i class="fas fa-edit"></i>
                    </a>
                </td>
            </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</div>

<?php include '../includes/footer.php'; ?>